<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Shift;
use App\Models\Stage;
use App\Models\StudentEnrollment;
use App\Models\StudentProfile;
use App\Models\Term;
use App\Models\User;
use App\Support\PeopleImport\SpreadsheetIO;
use App\Support\PeopleImport\StudentRowMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentImportController extends Controller
{
    private const SHEET_NAME = 'Students';

    private const INSTRUCTIONS = [
        'How to fill in the Students sheet',
        'code: unique student ID (e.g. STU-1001). Required.',
        'password: initial login password. Optional — leave blank to use the student ID as the password. If set, must be at least 8 characters.',
        'name_en: full name. Required.',
        'name_kh: optional Khmer name.',
        'gender: Male or Female. Required.',
        'dob: date of birth, e.g. 2002-03-10. Optional.',
        'phone: required.',
        'address: optional.',
        'faculty / department / major: must match existing names, department within that faculty and major within that department. All required.',
        'promotion: must match an existing promotion name (e.g. "Promotion 19"). Required.',
        'stage / academic_year / semester / term / shift: must match existing names. All required.',
        'status: Active, Inactive, or Suspended. Defaults to Active if left blank.',
        'Username is generated automatically. The initial password is either the "password" column value, or the student ID if that column is left blank — either way, it should be changed after first login.',
        'Class assignment is a separate step — use "Assign Class" on the Students page after importing.',
        'Delete the two example rows before importing, or leave them — rows with no code and no name are skipped automatically.',
    ];

    /**
     * Download a blank template (a couple of example rows) to fill in and
     * re-upload via import().
     */
    public function template(Request $request): StreamedResponse
    {
        $format = SpreadsheetIO::resolveFormat($request->input('format', 'xlsx'));

        return SpreadsheetIO::download(
            StudentRowMapper::headers(),
            StudentRowMapper::sampleRows(),
            $format,
            'student-import-template',
            self::SHEET_NAME,
            self::INSTRUCTIONS,
            ['dob']
        );
    }

    /**
     * Bulk-create students from an uploaded CSV/XLSX file. Each row is
     * validated against the same required fields as the manual "Add New
     * Student" dialog, and independently — one bad row doesn't sink the
     * whole batch. Pass ?preview=1 to parse and validate without saving.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'format' => ['nullable', Rule::in(['csv', 'xlsx'])],
            'preview' => ['nullable', 'boolean'],
        ]);

        $isPreview = $request->boolean('preview');
        $file = $request->file('file');
        $format = SpreadsheetIO::resolveFormat($request->input('format') ?: $file->getClientOriginalExtension());

        $entries = SpreadsheetIO::readRows($file->getRealPath(), $format, self::SHEET_NAME);

        $faculties = Faculty::all();
        $departments = Department::all();
        $majors = Major::all();
        $promotions = Promotion::all();
        $stages = Stage::all();
        $academicYears = AcademicYear::all();
        $semesters = Semester::all();
        $terms = Term::all();
        $shifts = Shift::all();

        $existingCodes = StudentProfile::pluck('student_code')
            ->map(fn ($code) => strtolower($code))
            ->flip();

        $seenCodes = [];
        $valid = [];
        $skipped = [];

        foreach ($entries as $entry) {
            $parsed = StudentRowMapper::fromRow($entry['data']);

            if ($parsed === null) {
                continue;
            }

            $errors = StudentRowMapper::validate($parsed);
            $ids = [
                'facultyId' => null, 'departmentId' => null, 'majorId' => null,
                'promotionId' => null, 'stageId' => null, 'academicYearId' => null,
                'semesterId' => null, 'termId' => null, 'shiftId' => null,
            ];

            if ($parsed['faculty'] !== '') {
                $faculty = $faculties->first(fn ($f) => strcasecmp($f->name, $parsed['faculty']) === 0);

                if ($faculty) {
                    $ids['facultyId'] = $faculty->id;
                } else {
                    $errors[] = "Unknown faculty \"{$parsed['faculty']}\".";
                }
            }

            if ($parsed['department'] !== '') {
                $department = $departments->first(fn ($d) => strcasecmp($d->name, $parsed['department']) === 0
                    && (! $ids['facultyId'] || $d->faculty_id === $ids['facultyId']));

                if ($department) {
                    $ids['departmentId'] = $department->id;
                } else {
                    $errors[] = "Unknown department \"{$parsed['department']}\" for the given faculty.";
                }
            }

            if ($parsed['major'] !== '') {
                $major = $majors->first(fn ($m) => strcasecmp($m->name, $parsed['major']) === 0
                    && (! $ids['departmentId'] || $m->department_id === $ids['departmentId']));

                if ($major) {
                    $ids['majorId'] = $major->id;
                } else {
                    $errors[] = "Unknown major \"{$parsed['major']}\" for the given department.";
                }
            }

            if ($parsed['promotion'] !== '') {
                $promotion = $promotions->first(fn ($p) => strcasecmp($p->name, $parsed['promotion']) === 0
                    || strcasecmp("{$p->year_start}-{$p->year_end}", $parsed['promotion']) === 0);

                if ($promotion) {
                    $ids['promotionId'] = $promotion->id;
                } else {
                    $errors[] = "Unknown promotion \"{$parsed['promotion']}\".";
                }
            }

            if ($parsed['stage'] !== '') {
                $stage = $stages->first(fn ($s) => strcasecmp($s->name, $parsed['stage']) === 0);

                if ($stage) {
                    $ids['stageId'] = $stage->id;
                } else {
                    $errors[] = "Unknown stage \"{$parsed['stage']}\".";
                }
            }

            if ($parsed['academic_year'] !== '') {
                $academicYear = $academicYears->first(fn ($a) => strcasecmp($a->name, $parsed['academic_year']) === 0);

                if ($academicYear) {
                    $ids['academicYearId'] = $academicYear->id;
                } else {
                    $errors[] = "Unknown academic year \"{$parsed['academic_year']}\".";
                }
            }

            if ($parsed['semester'] !== '') {
                $semester = $semesters->first(fn ($s) => strcasecmp($s->name, $parsed['semester']) === 0
                    && (! $ids['academicYearId'] || $s->academic_year_id === $ids['academicYearId']));

                if ($semester) {
                    $ids['semesterId'] = $semester->id;
                } else {
                    $errors[] = "Unknown semester \"{$parsed['semester']}\" for the given academic year.";
                }
            }

            if ($parsed['term'] !== '') {
                $term = $terms->first(fn ($t) => strcasecmp($t->name, $parsed['term']) === 0
                    && (! $ids['academicYearId'] || $t->academic_year_id === $ids['academicYearId']));

                if ($term) {
                    $ids['termId'] = $term->id;
                } else {
                    $errors[] = "Unknown term \"{$parsed['term']}\" for the given academic year.";
                }
            }

            if ($parsed['shift'] !== '') {
                $shift = $shifts->first(fn ($s) => strcasecmp($s->name, $parsed['shift']) === 0);

                if ($shift) {
                    $ids['shiftId'] = $shift->id;
                } else {
                    $errors[] = "Unknown shift \"{$parsed['shift']}\".";
                }
            }

            $codeLower = strtolower($parsed['code']);

            if ($parsed['code'] !== '') {
                if (isset($existingCodes[$codeLower])) {
                    $errors[] = "Student ID \"{$parsed['code']}\" already exists.";
                } elseif (isset($seenCodes[$codeLower])) {
                    $errors[] = "Duplicate student ID \"{$parsed['code']}\" within this file.";
                }
            }

            if ($errors) {
                $skipped[] = ['source' => $entry['source'], 'errors' => $errors];

                continue;
            }

            $seenCodes[$codeLower] = true;
            $valid[] = ['source' => $entry['source'], 'parsed' => $parsed, 'ids' => $ids];
        }

        if ($isPreview) {
            return response()->json([
                'preview' => true,
                'imported' => count($valid),
                'skipped' => $skipped,
                'rows' => array_map(fn ($v) => $this->previewRow($v), $valid),
            ]);
        }

        $created = [];

        foreach ($valid as $v) {
            $created[] = DB::transaction(fn () => $this->createStudent($v));
        }

        return response()->json([
            'preview' => false,
            'message' => count($created).' student(s) imported, '.count($skipped).' skipped.',
            'imported' => count($created),
            'skipped' => $skipped,
            'rows' => $created,
        ]);
    }

    private function previewRow(array $v): array
    {
        $parsed = $v['parsed'];

        return [
            'source' => $v['source'],
            'code' => $parsed['code'],
            'name' => $parsed['name_en'],
            'gender' => $parsed['gender'],
            'phone' => $parsed['phone'],
            'faculty' => $parsed['faculty'],
            'department' => $parsed['department'],
            'major' => $parsed['major'],
            'promotion' => $parsed['promotion'],
            'stage' => $parsed['stage'],
            'status' => $parsed['status'],
        ];
    }

    private function createStudent(array $v): array
    {
        $parsed = $v['parsed'];
        $ids = $v['ids'];
        [$firstName, $lastName] = $this->splitName($parsed['name_en']);
        $role = Role::where('name', 'Student')->first();
        $username = User::generateUsername('STU');

        $user = User::create([
            'role_id' => $role?->id,
            'username' => $username,
            // Initial password: whatever the row specified, or the
            // student's own ID if left blank. They're expected to change
            // it after first login (see the template instructions).
            'password' => Hash::make($parsed['password'] !== '' ? $parsed['password'] : $parsed['code']),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name_kh' => $parsed['name_kh'],
            'gender' => $parsed['gender'],
            'dob' => $parsed['dob'],
            'phone' => $parsed['phone'],
            'address' => $parsed['address'],
            'status' => $parsed['status'] !== 'Inactive',
        ]);

        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'student_code' => $parsed['code'],
            'admission_date' => now(),
        ]);

        StudentEnrollment::create([
            'student_profile_id' => $profile->id,
            'faculty_id' => $ids['facultyId'],
            'department_id' => $ids['departmentId'],
            'major_id' => $ids['majorId'],
            'promotion_id' => $ids['promotionId'],
            'stage_id' => $ids['stageId'],
            'academic_year_id' => $ids['academicYearId'],
            'semester_id' => $ids['semesterId'],
            'term_id' => $ids['termId'],
            'shift_id' => $ids['shiftId'],
            'status' => $parsed['status'],
            'enrollment_date' => now(),
        ]);

        return [
            'id' => $profile->id,
            'code' => $profile->student_code,
            'name' => trim("{$firstName} {$lastName}"),
            'username' => $username,
        ];
    }

    private function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName), 2);

        return [$parts[0], $parts[1] ?? ''];
    }
}
