<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\TranslatesStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\StudentProfile;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClassroomController extends Controller
{
    use TranslatesStatus;

    /**
     * Get all classes.
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $classes = Classroom::query()
            ->with('major.degree.faculty', 'faculty', 'department', 'promotion', 'stage', 'shift', 'academicYear', 'semester', 'term', 'studySession')
            ->withCount(['students as students_count' => fn ($query) => $query->where('class_student.status', 'Active')])
            ->when($request->input('major_id'), fn ($query, $id) => $query->where('major_id', $id))
            ->when($request->input('faculty_id'), fn ($query, $id) => $query->where('faculty_id', $id))
            ->when($request->input('department_id'), fn ($query, $id) => $query->where('department_id', $id))
            ->when($request->input('promotion_id'), fn ($query, $id) => $query->where('promotion_id', $id))
            ->when($request->input('stage_id'), fn ($query, $id) => $query->where('stage_id', $id))
            ->when($request->input('shift_id'), fn ($query, $id) => $query->where('shift_id', $id))
            ->when($request->input('academic_year_id'), fn ($query, $id) => $query->where('academic_year_id', $id))
            ->when($request->input('study_session_id'), fn ($query, $id) => $query->where('study_session_id', $id))
            ->when($request->input('q'), function ($query, $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);

        $classes->getCollection()->transform(fn (Classroom $class) => $this->transform($class));

        return response()->json($classes);
    }

    /**
     * Create a new class.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $class = Classroom::create($data);
        $class->load('major.degree.faculty', 'faculty', 'department', 'promotion', 'stage', 'shift', 'academicYear', 'semester', 'term', 'studySession');
        $class->loadCount(['students as students_count' => fn ($query) => $query->where('class_student.status', 'Active')]);

        return response()->json([
            'message' => 'Class created successfully.',
            'class' => $this->transform($class),
        ], 201);
    }

    /**
     * Get one class.
     */
    public function show(Classroom $class)
    {
        $class->load('major.degree.faculty', 'faculty', 'department', 'promotion', 'stage', 'shift', 'academicYear', 'semester', 'term', 'studySession');
        $class->loadCount(['students as students_count' => fn ($query) => $query->where('class_student.status', 'Active')]);

        return response()->json([
            'class' => $this->transform($class),
        ]);
    }

    /**
     * Update class.
     */
    public function update(Request $request, Classroom $class)
    {
        $data = $this->validated($request, $class);

        $class->update($data);
        $class->load('major.degree.faculty', 'faculty', 'department', 'promotion', 'stage', 'shift', 'academicYear', 'semester', 'term', 'studySession');
        $class->loadCount(['students as students_count' => fn ($query) => $query->where('class_student.status', 'Active')]);

        return response()->json([
            'message' => 'Class updated successfully.',
            'class' => $this->transform($class),
        ]);
    }

    /**
     * The class's own detail plus its enrolled students, each with a quiz
     * completion/score summary for that class. Powers the admin's
     * per-class roster page.
     */
    public function roster(Classroom $class)
    {
        return response()->json($this->buildRoster($class));
    }

    /**
     * Download the class roster as CSV/XLSX, scoped to whichever Subject /
     * Teacher / Quiz filters are currently active on the roster page (all
     * quizzes in the class when none are set). Powers the "Export" action.
     */
    public function exportResults(Request $request, Classroom $class): StreamedResponse
    {
        $validated = $request->validate([
            'subject_id' => ['nullable', 'integer'],
            'teacher' => ['nullable', 'string'],
            'quiz_id' => ['nullable', 'integer'],
            'format' => ['nullable', Rule::in(['csv', 'xlsx'])],
        ]);

        $data = $this->buildRoster($class);

        $matchingQuizzes = collect($data['quizzes'])->filter(fn ($q) =>
            (empty($validated['subject_id']) || $q['subject_id'] == $validated['subject_id'])
            && (empty($validated['teacher']) || $q['teacher'] === $validated['teacher'])
            && (empty($validated['quiz_id']) || $q['id'] == $validated['quiz_id'])
        )->values();
        $matchingIds = $matchingQuizzes->pluck('id')->all();

        $rows = collect($data['students'])->map(function ($student) use ($matchingIds) {
            $results = collect($student['quiz_results'])->whereIn('quiz_id', $matchingIds);
            $points = $results->sum('points');
            $totalPoints = $results->sum('total_points');
            $score = $totalPoints > 0 ? (int) round($points / $totalPoints * 100) : null;

            return [
                $student['username'],
                $student['student_id'],
                $student['name'],
                $student['name_kh'],
                $student['gender'],
                $results->count(),
                count($matchingIds),
                $points,
                $totalPoints,
                $score !== null ? "{$score}%" : 'No Data',
                $score === null ? '-' : ($score >= 50 ? 'Passed' : 'Failed'),
            ];
        })->values()->all();

        // "Quizzes Completed"/"Quizzes Total" are kept as two separate number
        // columns rather than one "2/3" text column — Excel's own CSV/XLSX
        // cell-type guessing reads an "x/y" string as a date (e.g. "3-Feb").
        $headers = ['Username', 'Student ID', 'Name', 'Name (KH)', 'Gender', 'Quizzes Completed', 'Quizzes Total', 'Points', 'Total Points', 'Score', 'Result'];

        $labelParts = [$class->code];
        if (! empty($validated['subject_id'])) {
            $subjectName = collect($data['subjects'])->firstWhere('subject_id', $validated['subject_id'])['subject'] ?? null;
            if ($subjectName) {
                $labelParts[] = $subjectName;
            }
        }
        if (! empty($validated['teacher'])) {
            $labelParts[] = $validated['teacher'];
        }
        if (! empty($validated['quiz_id'])) {
            $quizTitle = collect($data['quizzes'])->firstWhere('id', $validated['quiz_id'])['title'] ?? null;
            if ($quizTitle) {
                $labelParts[] = $quizTitle;
            }
        }
        $labelParts[] = count($labelParts) > 1 ? 'results' : 'all-results';

        return $this->spreadsheetDownload(
            $headers,
            $rows,
            $validated['format'] ?? 'xlsx',
            Str::slug(implode('-', $labelParts)),
            textColumns: ['Username', 'Student ID'],
            percentColumns: ['Score'],
        );
    }

    /**
     * The class's own detail plus its enrolled students, each with a quiz
     * completion/score summary for that class. Shared by the roster page
     * and its export, so both stay in sync.
     */
    private function buildRoster(Classroom $class): array
    {
        $class->load('major.degree.faculty', 'faculty', 'department', 'promotion', 'stage', 'shift', 'academicYear', 'semester', 'term', 'studySession');

        $quizzes = Quiz::whereIn('status', ['Published', 'Closed'])
            ->where('class_id', $class->id)
            ->with('subject')
            ->get(['id', 'title', 'subject_id']);
        $quizIds = $quizzes->pluck('id');

        // One teacher per subject within this class, used to attribute each
        // quiz result to the teacher who actually teaches that subject here.
        $teacherBySubjectId = TeacherSubject::where('class_id', $class->id)
            ->with('teacherProfile.user')
            ->get()
            ->keyBy('subject_id')
            ->map(fn (TeacherSubject $assignment) => $assignment->teacherProfile?->user?->full_name);

        $students = $class->students()
            ->with([
                'user',
                'enrollments' => fn ($query) => $query->latest('enrollment_date')->with(
                    'faculty', 'department', 'major', 'promotion', 'stage', 'academicYear', 'semester', 'term', 'shift'
                ),
            ])
            ->get();

        $submissions = QuizSubmission::whereIn('quiz_id', $quizIds)
            ->whereIn('student_profile_id', $students->pluck('id'))
            ->where('status', 'submitted')
            ->get(['student_profile_id', 'quiz_id', 'attempt_number', 'mcq_score', 'essay_score', 'total_points']);

        $subjects = TeacherSubject::where('class_id', $class->id)
            ->with('subject', 'teacherProfile.user')
            ->get()
            ->map(fn (TeacherSubject $assignment) => [
                'subject_id' => $assignment->subject_id,
                'subject' => $assignment->subject?->name,
                'teacher' => $assignment->teacherProfile?->user?->full_name,
            ])
            ->unique(fn ($row) => $row['subject_id'].'-'.$row['teacher'])
            ->values();

        return [
            'class' => $this->transform($class->loadCount(['students as students_count' => fn ($query) => $query->where('class_student.status', 'Active')])) + [
                'total_quizzes' => $quizzes->count(),
            ],
            'subjects' => $subjects,
            'students' => $students->map(function (StudentProfile $student) use ($submissions, $quizzes, $teacherBySubjectId) {
                // A student can retake a quiz (several rows share the same quiz_id
                // with different attempt_number), so only the latest attempt per
                // quiz counts toward completion/score here.
                $mine = $submissions->where('student_profile_id', $student->id)
                    ->groupBy('quiz_id')
                    ->map(fn ($attempts) => $attempts->sortByDesc('attempt_number')->first());
                $earned = $mine->sum(fn ($s) => $s->mcq_score + $s->essay_score);
                $possible = $mine->sum('total_points');
                $user = $student->user;
                $enrollment = $student->enrollments->first();

                $quizResults = $mine->map(function ($submission, $quizId) use ($quizzes, $teacherBySubjectId) {
                    $quiz = $quizzes->firstWhere('id', $quizId);
                    $points = $submission->mcq_score + $submission->essay_score;

                    return [
                        'quiz_id' => $quizId,
                        'quiz_title' => $quiz?->title,
                        'subject_id' => $quiz?->subject_id,
                        'subject' => $quiz?->subject?->name,
                        'teacher' => $teacherBySubjectId->get($quiz?->subject_id),
                        'points' => $points,
                        'total_points' => $submission->total_points,
                        'score' => $submission->total_points > 0 ? (int) round($points / $submission->total_points * 100) : null,
                    ];
                })->values();

                return [
                    'id' => $student->id,
                    'student_id' => $student->student_code,
                    'username' => $user?->username,
                    'name' => $user?->full_name,
                    'name_kh' => $user?->name_kh,
                    'gender' => $user?->gender,
                    'dob' => $user?->dob?->toDateString(),
                    'phone' => $user?->phone,
                    'address' => $user?->address,
                    'avatar' => $user?->avatar,
                    'class_status' => $student->pivot->status,
                    'account_status' => $user?->status ? 'Active' : 'Inactive',
                    'faculty' => $enrollment?->faculty?->name,
                    'department' => $enrollment?->department?->name,
                    'major' => $enrollment?->major?->name,
                    'generation' => $enrollment?->promotion
                        ? "{$enrollment->promotion->year_start}-{$enrollment->promotion->year_end}"
                        : null,
                    'stage' => $enrollment?->stage?->name,
                    'academic_year' => $enrollment?->academicYear?->name,
                    'semester' => $enrollment?->semester?->name,
                    'term' => $enrollment?->term?->name,
                    'shift' => $enrollment?->shift?->name,
                    'admission_date' => $student->admission_date?->toDateString(),
                    'quiz_completed' => $mine->count(),
                    'total_quizzes' => $quizzes->count(),
                    'score' => $possible > 0 ? (int) round($earned / $possible * 100) : null,
                    'quiz_results' => $quizResults,
                ];
            })->values(),
            'quizzes' => $quizzes->map(fn (Quiz $quiz) => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'subject_id' => $quiz->subject_id,
                'subject' => $quiz->subject?->name,
                'teacher' => $teacherBySubjectId->get($quiz->subject_id),
            ])->values(),
        ];
    }

    /**
     * @param  string[]  $textColumns  Header names that must stay literal text (e.g. IDs
     *                                 that happen to look numeric) instead of letting
     *                                 PhpSpreadsheet's auto-detection store them as numbers,
     *                                 which would silently drop a leading zero.
     * @param  string[]  $percentColumns  Header names holding a "NN%" string; written as a
     *                                    real numeric percentage (not text) in XLSX so Excel
     *                                    sorts/aligns it correctly. Left as-is for CSV, which
     *                                    has no per-cell number format to apply it with.
     */
    private function spreadsheetDownload(
        array $headers,
        array $rows,
        string $format,
        string $filenameBase,
        array $textColumns = [],
        array $percentColumns = [],
    ): StreamedResponse {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);

        $textColumnIndexes = array_filter(array_map(fn ($name) => array_search($name, $headers, true), $textColumns), fn ($i) => $i !== false);
        $percentColumnIndexes = $format === 'xlsx'
            ? array_filter(array_map(fn ($name) => array_search($name, $headers, true), $percentColumns), fn ($i) => $i !== false)
            : [];

        foreach ($rows as $rowOffset => $row) {
            $excelRow = $rowOffset + 2; // header row + 1-indexing

            foreach (array_values($row) as $colIndex => $value) {
                $cell = $sheet->getCell([$colIndex + 1, $excelRow]);

                if (in_array($colIndex, $textColumnIndexes, true)) {
                    $cell->setValueExplicit((string) ($value ?? ''), DataType::TYPE_STRING);
                } elseif (in_array($colIndex, $percentColumnIndexes, true) && is_string($value) && str_ends_with($value, '%')) {
                    $cell->setValue(((float) rtrim($value, '%')) / 100);
                } else {
                    $cell->setValue($value);
                }
            }
        }

        foreach ($percentColumnIndexes as $colIndex) {
            $col = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->getStyle("{$col}2:{$col}".(count($rows) + 1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
        }

        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = $format === 'csv' ? new CsvWriter($spreadsheet) : new XlsxWriter($spreadsheet);
        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $contentType = $format === 'csv' ? 'text/csv; charset=UTF-8' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        if ($writer instanceof CsvWriter) {
            // Plain standard CSV (comma-delimited) with a UTF-8 BOM so Khmer
            // (and other non-Latin) text renders correctly instead of mojibake.
            // Deliberately NOT using setExcelCompatibility(true): it switches
            // the delimiter to ";" and injects an Excel-only "sep=;" hint line
            // that other tools (Google Sheets, LibreOffice, plain CSV parsers)
            // read as literal data, shifting every column down by one row.
            $writer->setUseBOM(true);
        }

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, "{$filenameBase}.{$extension}", ['Content-Type' => $contentType]);
    }

    /**
     * Delete class.
     */
    public function destroy(Classroom $class)
    {
        $class->delete();

        return response()->json([
            'message' => 'Class deleted successfully.',
        ]);
    }

    private function validated(Request $request, ?Classroom $class = null): array
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('classes', 'code')->ignore($class?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'major_id' => ['required', 'exists:majors,id'],
            'faculty_id' => ['nullable', 'exists:faculties,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
            'stage_id' => ['required', 'exists:stages,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'semester_id' => ['nullable', 'exists:semesters,id'],
            'term_id' => ['nullable', 'exists:terms,id'],
            'study_session_id' => ['nullable', 'exists:study_sessions,id'],
            'room' => ['nullable', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ]);

        return [
            'code' => $validated['code'],
            'name' => $validated['name'],
            'name_kh' => $validated['name_kh'] ?? null,
            'major_id' => $validated['major_id'],
            'faculty_id' => $validated['faculty_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'promotion_id' => $validated['promotion_id'] ?? null,
            'stage_id' => $validated['stage_id'],
            'shift_id' => $validated['shift_id'],
            'academic_year_id' => $validated['academic_year_id'] ?? $this->resolveCurrentAcademicYearId(),
            'semester_id' => $validated['semester_id'] ?? null,
            'term_id' => $validated['term_id'] ?? null,
            'study_session_id' => $validated['study_session_id'] ?? null,
            'room' => $validated['room'] ?? null,
            'capacity' => $validated['capacity'],
            'status' => $this->statusToBool($validated['status']),
        ];
    }

    private function resolveCurrentAcademicYearId(): int
    {
        $academicYear = AcademicYear::where('is_current', true)->first()
            ?? AcademicYear::orderByDesc('start_date')->first();

        if (! $academicYear) {
            throw ValidationException::withMessages([
                'academic_year_id' => ['No academic year is configured yet. Create one first.'],
            ]);
        }

        return $academicYear->id;
    }

    private function transform(Classroom $class): array
    {
        return [
            'id' => $class->id,
            'code' => $class->code,
            'name' => $class->name,
            'name_kh' => $class->name_kh,
            'major_id' => $class->major_id,
            'major_name' => $class->major?->name,
            // Legacy field kept for existing UI compatibility: historically held the major's
            // name under a misleading key, before `department_id` below was a real column.
            'department' => $class->major?->name,
            'department_id' => $class->department_id,
            'department_name' => $class->department?->name,
            'faculty_id' => $class->faculty_id ?? $class->major?->degree?->faculty_id,
            'faculty_name' => $class->faculty?->name ?? $class->major?->degree?->faculty?->name,
            'promotion_id' => $class->promotion_id,
            'promotion_name' => $class->promotion ? "{$class->promotion->year_start}-{$class->promotion->year_end}" : null,
            'study_session_id' => $class->study_session_id,
            'study_session_name' => $class->studySession?->name,
            'stage_id' => $class->stage_id,
            'stage' => $class->stage?->name,
            'shift_id' => $class->shift_id,
            'shift' => $class->shift?->name,
            'academic_year_id' => $class->academic_year_id,
            'academic_year' => $class->academicYear?->name,
            'semester_id' => $class->semester_id,
            'semester' => $class->semester?->name,
            'term_id' => $class->term_id,
            'term' => $class->term?->name,
            'room' => $class->room,
            'capacity' => $class->capacity,
            'students_count' => $class->students_count ?? 0,
            'status' => $this->statusToLabel($class->status),
        ];
    }
}
