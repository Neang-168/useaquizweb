<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionMatchingPair;
use App\Models\QuestionOption;
use App\Models\TeacherSubject;
use App\Support\QuestionImportExport\GiftCodec;
use App\Support\QuestionImportExport\QuestionRowMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Reader\Csv as CsvReader;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuestionImportExportController extends Controller
{
    /**
     * Download a blank template (a couple of example rows) to fill in and
     * re-upload via import().
     */
    public function template(Request $request): StreamedResponse
    {
        $format = $this->resolveFormat($request->input('format', 'xlsx'));

        if ($format === 'gift') {
            return $this->giftDownload($this->giftTemplateText(), 'question-import-template.gift');
        }

        return $this->spreadsheetDownload(QuestionRowMapper::sampleRows(), $format, 'question-import-template', withInstructions: true);
    }

    /**
     * Export this teacher's own question bank (optionally filtered) so it
     * can be edited and re-imported, or archived.
     */
    public function export(Request $request): StreamedResponse
    {
        $teacher = $request->user()->teacherProfile;
        $format = $this->resolveFormat($request->input('format', 'xlsx'));

        $questions = Question::query()
            ->when($teacher, fn ($q) => $q->where('teacher_profile_id', $teacher->id), fn ($q) => $q->whereRaw('1 = 0'))
            ->with(['subject', 'options', 'matchingPairs'])
            ->when($request->input('subject_id'), fn ($q, $id) => $q->where('subject_id', $id))
            ->when($request->input('type'), fn ($q, $type) => $q->where('type', $type))
            ->orderBy('subject_id')
            ->orderBy('created_at')
            ->get();

        if ($format === 'gift') {
            return $this->giftDownload(GiftCodec::export($questions), 'my-questions.gift');
        }

        $rows = $questions->map(fn (Question $question) => QuestionRowMapper::toRow($question))->all();

        return $this->spreadsheetDownload($rows, $format, 'my-questions', withInstructions: false);
    }

    /**
     * Bulk-create questions from an uploaded CSV/XLSX/GIFT file. Text only
     * — imported questions have no images; those can be added afterward in
     * the question editor. Each row/question is validated and created
     * independently, so one bad row doesn't sink the whole batch.
     */
    public function import(Request $request)
    {
        $teacher = $request->user()->teacherProfile;

        if (! $teacher) {
            abort(403);
        }

        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'format' => ['nullable', Rule::in(['csv', 'xlsx', 'gift'])],
            'preview' => ['nullable', 'boolean'],
        ]);

        $isPreview = $request->boolean('preview');

        $file = $request->file('file');
        $format = $this->resolveFormat($request->input('format') ?: $file->getClientOriginalExtension());

        $parsedQuestions = $format === 'gift'
            ? GiftCodec::parse(file_get_contents($file->getRealPath()))
            : $this->parseSpreadsheet($file->getRealPath(), $format);

        $subjectCodeMap = TeacherSubject::query()
            ->where('teacher_profile_id', $teacher->id)
            ->whereNotNull('subject_id')
            ->with('subject:id,code')
            ->get()
            ->pluck('subject')
            ->filter()
            ->unique('id')
            ->mapWithKeys(fn ($subject) => [strtolower($subject->code) => $subject->id]);

        $valid = [];
        $skipped = [];

        foreach ($parsedQuestions as $index => $parsed) {
            $source = $parsed['source'] ?? 'row '.($index + 2); // +2: header row + 1-indexing

            $errors = QuestionRowMapper::validate($parsed);
            $subjectId = $subjectCodeMap->get(strtolower($parsed['subject_code']));

            if ($parsed['subject_code'] !== '' && ! $subjectId) {
                $errors[] = "Unknown subject code \"{$parsed['subject_code']}\" — it must match one of your assigned subjects.";
            }

            if ($errors) {
                $skipped[] = ['source' => $source, 'errors' => $errors];

                continue;
            }

            $valid[] = ['source' => $source, 'parsed' => $parsed, 'subjectId' => $subjectId];
        }

        if ($isPreview) {
            return response()->json([
                'preview' => true,
                'imported' => count($valid),
                'skipped' => $skipped,
                'questions' => array_map(fn ($v) => $this->previewRow($v['source'], $v['parsed']), $valid),
            ]);
        }

        $created = [];

        foreach ($valid as $v) {
            $question = DB::transaction(fn () => $this->createQuestion($v['parsed'], $v['subjectId'], $teacher->id));
            $created[] = $this->transform($question);
        }

        return response()->json([
            'preview' => false,
            'message' => count($created).' question(s) imported, '.count($skipped).' skipped.',
            'imported' => count($created),
            'skipped' => $skipped,
            'questions' => $created,
        ]);
    }

    /**
     * A not-yet-persisted question's preview shape — same idea as
     * transform(), but built straight from the parsed row/GIFT block since
     * nothing has been saved yet.
     */
    private function previewRow(string $source, array $parsed): array
    {
        return [
            'source' => $source,
            'type' => $parsed['type'],
            'subjectCode' => $parsed['subject_code'],
            'difficulty' => $parsed['difficulty'],
            'points' => $parsed['points'],
            'title' => $parsed['title'],
            'options' => array_map(fn ($o) => ['text' => $o['text'], 'isCorrect' => $o['isCorrect']], $parsed['options']),
            'tfCorrect' => $parsed['tfCorrect'],
            'matchingPairs' => $parsed['matchingPairs'],
        ];
    }

    private function createQuestion(array $parsed, int $subjectId, int $teacherId): Question
    {
        $question = Question::create([
            'teacher_profile_id' => $teacherId,
            'subject_id' => $subjectId,
            'type' => $parsed['type'],
            'title' => $parsed['title'],
            'difficulty' => $parsed['difficulty'],
            'points' => $parsed['points'],
        ]);

        if ($parsed['type'] === 'multiple_choice') {
            foreach ($parsed['options'] as $index => $option) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'text' => $option['text'],
                    'is_correct' => $option['isCorrect'],
                    'position' => $index,
                ]);
            }
        } elseif ($parsed['type'] === 'true_false') {
            QuestionOption::create(['question_id' => $question->id, 'text' => 'True', 'is_correct' => $parsed['tfCorrect'] === 'True', 'position' => 0]);
            QuestionOption::create(['question_id' => $question->id, 'text' => 'False', 'is_correct' => $parsed['tfCorrect'] === 'False', 'position' => 1]);
        } elseif ($parsed['type'] === 'matching') {
            foreach ($parsed['matchingPairs'] as $index => $pair) {
                if (trim($pair['leftText']) === '' || trim($pair['rightText']) === '') {
                    continue;
                }

                QuestionMatchingPair::create([
                    'question_id' => $question->id,
                    'left_text' => $pair['leftText'],
                    'right_text' => $pair['rightText'],
                    'position' => $index,
                ]);
            }
        }

        return $question;
    }

    private function parseSpreadsheet(string $path, string $format): array
    {
        $reader = $format === 'csv' ? new CsvReader : new XlsxReader;

        if ($reader instanceof CsvReader) {
            $reader->setDelimiter(',');
        }

        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getSheetByName('Questions') ?? $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            return [];
        }

        $headers = array_map(fn ($h) => strtolower(trim((string) $h)), array_shift($rows));

        $parsed = [];

        foreach ($rows as $rowIndex => $row) {
            $assoc = [];

            foreach ($headers as $colIndex => $header) {
                $assoc[$header] = $row[$colIndex] ?? '';
            }

            $question = QuestionRowMapper::fromRow($assoc);

            if ($question === null) {
                continue;
            }

            $question['source'] = 'row '.($rowIndex + 2);
            $parsed[] = $question;
        }

        return $parsed;
    }

    private function resolveFormat(?string $format): string
    {
        $format = strtolower(trim((string) $format));
        $format = $format === 'xls' ? 'xlsx' : $format;
        $format = $format === 'txt' ? 'gift' : $format;

        return in_array($format, ['csv', 'xlsx', 'gift'], true) ? $format : 'xlsx';
    }

    private function spreadsheetDownload(array $rows, string $format, string $filenameBase, bool $withInstructions): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Questions');

        $headers = QuestionRowMapper::headers();
        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($rows, null, 'A2');
        $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);

        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if ($withInstructions && $format === 'xlsx') {
            $instructions = $spreadsheet->createSheet(0);
            $instructions->setTitle('Instructions');
            $instructions->fromArray($this->instructionLines(), null, 'A1');
            $instructions->getColumnDimension('A')->setWidth(100);
            $spreadsheet->setActiveSheetIndex(1);
        }

        $writer = $format === 'csv' ? new CsvWriter($spreadsheet) : new XlsxWriter($spreadsheet);
        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $contentType = $format === 'csv' ? 'text/csv' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, "{$filenameBase}.{$extension}", ['Content-Type' => $contentType]);
    }

    private function giftDownload(string $content, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, ['Content-Type' => 'text/plain']);
    }

    private function instructionLines(): array
    {
        return [
            ['How to fill in the Questions sheet'],
            ['type: multiple_choice, true_false, or matching'],
            ['subject_code: must match one of your assigned subjects (see My Subjects)'],
            ['difficulty: Easy, Medium, or Hard (defaults to Medium if left blank)'],
            ['points: how much this question is worth (defaults to 1 if left blank)'],
            ['title: the question text'],
            ['correct_options: multiple_choice -> letters of the correct option(s), e.g. "A" or "A,C". true_false -> "True" or "False". Leave blank for matching.'],
            ['option_1 .. option_8: multiple_choice answer options (true_false and matching ignore these)'],
            ['left_1/right_1 .. left_8/right_8: matching pairs (multiple_choice and true_false ignore these)'],
            ['Each question needs at least 2 filled options (multiple choice) or 2 complete pairs (matching), with at least one correct option for multiple choice.'],
            ['Delete the two example rows on the Questions sheet before importing, or leave them — rows with no type are skipped automatically.'],
        ];
    }

    private function giftTemplateText(): string
    {
        $lines = [
            '// How to fill in this file:',
            '// - One question per {...} block. Lines starting with "//" are comments.',
            '// - $CATEGORY: <subject_code> sets the subject for the questions that follow it (must match one of your assigned subjects).',
            '// - // Difficulty: Easy|Medium|Hard sets the difficulty for the next question (defaults to Medium).',
            '// - // Points: N sets how much the next question is worth (defaults to 1).',
            '// - Multiple choice: {=Correct answer ~Wrong answer ~Wrong answer}',
            '// - True/False: {TRUE} or {FALSE}',
            '// - Matching: {=Left -> Right =Left -> Right} (at least 2 pairs)',
            '',
            '$CATEGORY: MATH101',
            '',
            '// Difficulty: Medium',
            '// Points: 1',
            '::Q1:: What is 2 + 2? {',
            "\t=4",
            "\t~3",
            "\t~5",
            '}',
            '',
            '// Difficulty: Easy',
            '// Points: 1',
            '::Q2:: The sky is blue. {TRUE}',
            '',
            '// Difficulty: Hard',
            '// Points: 2',
            '::Q3:: Match each capital to its country. {',
            "\t=France -> Paris",
            "\t=Japan -> Tokyo",
            '}',
            '',
        ];

        return implode("\n", $lines);
    }

    private function transform(Question $question): array
    {
        $question->load('subject', 'options', 'matchingPairs');

        return [
            'id' => $question->id,
            'subject_id' => $question->subject_id,
            'subjectCode' => $question->subject?->code,
            'type' => $question->type,
            'difficulty' => $question->difficulty,
            'points' => $question->points,
            'title' => $question->title,
            'imageUrl' => null,
            'imageAlt' => null,
            'options' => $question->options->sortBy('position')->values()->map(fn (QuestionOption $option) => [
                'id' => $option->id,
                'text' => $option->text,
                'isCorrect' => $option->is_correct,
                'imageUrl' => null,
            ]),
            'matchingPairs' => $question->matchingPairs->sortBy('position')->values()->map(fn (QuestionMatchingPair $pair) => [
                'id' => $pair->id,
                'leftText' => $pair->left_text,
                'leftImageUrl' => null,
                'rightText' => $pair->right_text,
                'rightImageUrl' => null,
            ]),
        ];
    }
}
