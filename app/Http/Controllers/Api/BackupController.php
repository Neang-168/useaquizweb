<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;
use PDOException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Whole-database backup/restore for Admin. SQLite (this app's default
 * driver) is backed up/restored as a plain file copy. MySQL is dumped and
 * restored through PDO directly (not the mysqldump/mysql shell binaries) —
 * those turned out to be unreliable to shell out to from the web server's
 * process on this Windows/WAMP setup (socket errors, PATH differences from
 * an interactive shell), where a plain PDO connection works fine since
 * Laravel already has one open.
 *
 * Restore is destructive by nature, so every restore first takes a fresh
 * "safety backup" of the live database before overwriting it — a bad
 * restore can be undone by restoring that safety backup in turn.
 */
class BackupController extends Controller
{
    /** Rows per INSERT statement when dumping a table. */
    private const INSERT_CHUNK_SIZE = 200;

    /** Tables that must appear in an uploaded file for it to be trusted as a real backup of this app. */
    private const EXPECTED_TABLES = ['users', 'roles', 'migrations'];

    private function safetyBackupDir(): string
    {
        return storage_path('app/backups');
    }

    /**
     * Stream a full database backup for download.
     */
    public function download(): StreamedResponse
    {
        $driver = config('database.default');
        $timestamp = now()->format('Y-m-d_His');

        if ($driver === 'sqlite') {
            $path = config('database.connections.sqlite.database');

            if (! is_file($path)) {
                abort(404, 'Database file not found.');
            }

            return response()->streamDownload(function () use ($path) {
                readfile($path);
            }, "backup-{$timestamp}.sqlite", ['Content-Type' => 'application/octet-stream']);
        }

        if ($driver === 'mysql') {
            return response()->streamDownload(function () {
                $this->streamMysqlDump();
            }, "backup-{$timestamp}.sql", ['Content-Type' => 'application/sql']);
        }

        abort(422, "Backups aren't supported for the \"{$driver}\" database driver.");
    }

    /**
     * List the safety backups automatically taken before each restore, most
     * recent first, so the admin can see the paper trail (and download one
     * if they need to undo a restore).
     */
    public function history(): JsonResponse
    {
        $dir = $this->safetyBackupDir();
        File::ensureDirectoryExists($dir);

        $files = collect(File::files($dir))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'created_at' => date('c', $file->getMTime()),
            ])
            ->values();

        return response()->json(['data' => $files]);
    }

    /**
     * Download one previously-taken safety backup by filename.
     */
    public function downloadHistoryFile(string $filename): StreamedResponse
    {
        // basename() strips any path segments, so this can't escape the
        // backups directory via "../" or similar.
        $path = $this->safetyBackupDir().'/'.basename($filename);

        if (! is_file($path)) {
            abort(404, 'Backup file not found.');
        }

        return response()->streamDownload(function () use ($path) {
            readfile($path);
        }, basename($path), ['Content-Type' => 'application/octet-stream']);
    }

    /**
     * Replace the live database with an uploaded backup file. Requires the
     * admin to type the literal confirmation phrase "RESTORE".
     */
    public function restore(Request $request): JsonResponse
    {
        $driver = config('database.default');

        $request->validate([
            'file' => ['required', 'file', 'max:512000'], // 500MB ceiling
            'confirm' => ['required', 'in:RESTORE'],
        ]);

        if ($driver === 'sqlite') {
            return $this->restoreSqlite($request);
        }

        if ($driver === 'mysql') {
            return $this->restoreMysql($request);
        }

        abort(422, "Restore isn't supported for the \"{$driver}\" database driver.");
    }

    private function restoreSqlite(Request $request): JsonResponse
    {
        $livePath = config('database.connections.sqlite.database');
        $uploadedPath = $request->file('file')->getRealPath();

        // Sanity-check the upload is actually a SQLite database with the
        // tables this app expects, before touching the live database.
        $this->assertValidSqliteBackup($uploadedPath);

        $safetyPath = $this->takeSafetySnapshot('pre-restore', 'sqlite', fn () => file_get_contents($livePath));

        // Release this request's own connection to the database file before
        // overwriting it, so the copy isn't fighting an open handle.
        DB::disconnect();

        try {
            File::copy($uploadedPath, $livePath);
        } catch (\Throwable $e) {
            abort(500, 'Could not overwrite the live database (it may be in use). Nothing was changed — the last known-good copy is still at '.basename($safetyPath).'. Error: '.$e->getMessage());
        }

        return response()->json([
            'message' => 'Database restored successfully. A safety backup of the previous database was saved before restoring.',
        ]);
    }

    private function assertValidSqliteBackup(string $path): void
    {
        try {
            $pdo = new PDO('sqlite:'.$path);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $integrity = $pdo->query('PRAGMA integrity_check')->fetchColumn();

            if ($integrity !== 'ok') {
                abort(422, 'The uploaded file failed a database integrity check.');
            }

            $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")
                ->fetchAll(PDO::FETCH_COLUMN);

            foreach (self::EXPECTED_TABLES as $expected) {
                if (! in_array($expected, $tables, true)) {
                    abort(422, "The uploaded file doesn't look like a backup of this application (missing the \"{$expected}\" table).");
                }
            }
        } catch (PDOException $e) {
            abort(422, 'The uploaded file is not a valid SQLite database.');
        }
    }

    /**
     * Write "SHOW CREATE TABLE" + "INSERT INTO" statements for every table
     * in the current MySQL database straight to the output buffer, so the
     * caller can stream it to a download or capture it with ob_start().
     * Table/row data is escaped with PDO::quote() against the live
     * connection — no mysqldump binary involved.
     */
    private function streamMysqlDump(): void
    {
        $pdo = DB::connection('mysql')->getPdo();
        $database = config('database.connections.mysql.database');
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        echo "-- Backup of `{$database}` generated ".now()->toDateTimeString()."\n";
        echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $createRow = $pdo->query('SHOW CREATE TABLE `'.$table.'`')->fetch(PDO::FETCH_ASSOC);

            echo 'DROP TABLE IF EXISTS `'.$table."`;\n";
            echo ($createRow['Create Table'] ?? '').";\n\n";

            $statement = $pdo->query('SELECT * FROM `'.$table.'`');
            $columns = null;
            $buffer = [];

            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                if ($columns === null) {
                    $columns = array_keys($row);
                }

                $values = array_map(fn ($value) => $value === null ? 'NULL' : $pdo->quote((string) $value), $row);
                $buffer[] = '('.implode(', ', $values).')';

                if (count($buffer) >= self::INSERT_CHUNK_SIZE) {
                    $this->writeInsertStatement($table, $columns, $buffer);
                    $buffer = [];
                }
            }

            if ($buffer) {
                $this->writeInsertStatement($table, $columns, $buffer);
            }

            echo "\n";
        }

        echo "SET FOREIGN_KEY_CHECKS=1;\n";
    }

    private function writeInsertStatement(string $table, array $columns, array $valueRows): void
    {
        $columnList = implode(', ', array_map(fn ($column) => "`{$column}`", $columns));
        echo 'INSERT INTO `'.$table."` ({$columnList}) VALUES\n".implode(",\n", $valueRows).";\n";
    }

    private function restoreMysql(Request $request): JsonResponse
    {
        $uploadedPath = $request->file('file')->getRealPath();
        $sql = file_get_contents($uploadedPath);

        $this->assertValidMysqlBackup($sql);

        $safetyPath = $this->takeSafetySnapshot('pre-restore', 'sql', function () {
            ob_start();
            $this->streamMysqlDump();

            return ob_get_clean();
        });

        $statements = array_filter(
            array_map('trim', $this->splitSqlStatements($sql)),
            fn ($statement) => $statement !== '' && ! str_starts_with($statement, '--')
        );

        $pdo = DB::connection('mysql')->getPdo();

        try {
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

            foreach ($statements as $statement) {
                $pdo->exec($statement);
            }

            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        } catch (\Throwable $e) {
            abort(500, 'Restore failed partway through, so the database may now be partially updated. A safety backup of the previous database was saved as '.basename($safetyPath).' before restoring — download it from the Safety Backups list below and restore it to undo this. Error: '.$e->getMessage());
        }

        return response()->json([
            'message' => 'Database restored successfully. A safety backup of the previous database was saved before restoring.',
        ]);
    }

    private function assertValidMysqlBackup(string $sql): void
    {
        foreach (self::EXPECTED_TABLES as $expected) {
            if (! str_contains($sql, '`'.$expected.'`')) {
                abort(422, "The uploaded file doesn't look like a backup of this application (missing a reference to the \"{$expected}\" table).");
            }
        }
    }

    /**
     * Split a SQL script into individual statements on unquoted semicolons,
     * so a semicolon inside a string value (e.g. a quiz question's text)
     * doesn't get mistaken for a statement terminator. Handles the
     * doubled-quote and backslash escaping PDO::quote() (and mysqldump)
     * produce for embedded quotes.
     */
    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $current .= $char;

            if ($inString) {
                if ($char === '\\' && $i + 1 < $length) {
                    $current .= $sql[$i + 1];
                    $i++;
                } elseif ($char === $stringChar) {
                    if ($i + 1 < $length && $sql[$i + 1] === $stringChar) {
                        $current .= $sql[$i + 1];
                        $i++;
                    } else {
                        $inString = false;
                    }
                }
            } elseif ($char === "'" || $char === '"') {
                $inString = true;
                $stringChar = $char;
            } elseif ($char === ';') {
                $statements[] = $current;
                $current = '';
            }
        }

        if (trim($current) !== '') {
            $statements[] = $current;
        }

        return $statements;
    }

    /**
     * Save a timestamped snapshot of the current database into the safety
     * backups directory, right before a restore overwrites it, by writing
     * whatever the given callback returns (raw file bytes for sqlite, dump
     * text for mysql).
     */
    private function takeSafetySnapshot(string $prefix, string $extension, callable $contents): string
    {
        $dir = $this->safetyBackupDir();
        File::ensureDirectoryExists($dir);
        $path = $dir.'/'.$prefix.'-'.now()->format('Y-m-d_His').'.'.$extension;

        File::put($path, $contents());

        return $path;
    }
}
