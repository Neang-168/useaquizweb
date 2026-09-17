<?php

namespace App\Support\PeopleImport;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Csv as CsvReader;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Shared CSV/XLSX plumbing for the Teacher and Student bulk-import features
 * — reading an uploaded file into flat rows, and streaming a template
 * download. Mirrors QuestionImportExportController's spreadsheet handling,
 * generalized to take headers/instructions per entity.
 */
class SpreadsheetIO
{
    public static function resolveFormat(?string $format): string
    {
        $format = strtolower(trim((string) $format));
        $format = $format === 'xls' ? 'xlsx' : $format;

        return $format === 'csv' ? 'csv' : 'xlsx';
    }

    /**
     * Read an uploaded file into a list of ['source' => 'row N', 'data' => [header => value]].
     */
    public static function readRows(string $path, string $format, string $sheetName): array
    {
        $reader = $format === 'csv' ? new CsvReader : new XlsxReader;

        if ($reader instanceof CsvReader) {
            $reader->setDelimiter(',');
        }

        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getSheetByName($sheetName) ?? $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            return [];
        }

        $headers = array_map(fn ($h) => strtolower(trim((string) $h)), array_shift($rows));

        $entries = [];

        foreach ($rows as $rowIndex => $row) {
            $assoc = [];

            foreach ($headers as $colIndex => $header) {
                $assoc[$header] = $row[$colIndex] ?? '';
            }

            $entries[] = ['source' => 'row '.($rowIndex + 2), 'data' => $assoc];
        }

        return $entries;
    }

    /**
     * Stream a template/sample-data download: a header row plus a couple of
     * example rows, with an optional "Instructions" sheet (xlsx only).
     *
     * $textColumns names header columns (e.g. 'dob', 'hire_date') that must
     * stay plain text: without this, Excel auto-detects typed dates and
     * reformats them per the user's locale, which can silently swap the
     * day/month order before the file is re-uploaded.
     */
    public static function download(array $headers, array $rows, string $format, string $filenameBase, string $sheetName, array $instructionLines = [], array $textColumns = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetName);

        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray(
            array_map(fn ($row) => array_values(array_replace(array_fill_keys($headers, ''), $row)), $rows),
            null,
            'A2'
        );
        $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);

        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if ($format === 'xlsx') {
            foreach ($textColumns as $columnName) {
                $columnIndex = array_search($columnName, $headers, true);

                if ($columnIndex === false) {
                    continue;
                }

                $column = Coordinate::stringFromColumnIndex($columnIndex + 1);
                $sheet->getStyle("{$column}2:{$column}1000")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_TEXT);
            }
        }

        if ($instructionLines && $format === 'xlsx') {
            $instructions = $spreadsheet->createSheet(0);
            $instructions->setTitle('Instructions');
            $instructions->fromArray(array_map(fn ($line) => [$line], $instructionLines), null, 'A1');
            $instructions->getColumnDimension('A')->setWidth(100);
            $spreadsheet->setActiveSheetIndex(1);
        }

        if ($format === 'csv') {
            $writer = new CsvWriter($spreadsheet);
            // Without a BOM, Excel guesses the file's encoding from the
            // system codepage instead of UTF-8, so Khmer (and other
            // non-ASCII) text renders as garbled characters until the user
            // overwrites it by hand.
            $writer->setUseBOM(true);
        } else {
            $writer = new XlsxWriter($spreadsheet);
        }

        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $contentType = $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, "{$filenameBase}.{$extension}", ['Content-Type' => $contentType]);
    }
}
