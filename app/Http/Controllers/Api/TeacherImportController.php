<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Support\PeopleImport\SpreadsheetIO;
use App\Support\PeopleImport\TeacherRowMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherImportController extends Controller
{
    private const SHEET_NAME = 'Teachers';

    private const INSTRUCTIONS = [
        'How to fill in the Teachers sheet',
        'code: unique teacher code (e.g. T-101). Required.',
        'password: initial login password. Optional — leave blank to use the teacher code as the password. If set, must be at least 8 characters.',
        'name_en: full name. Required.',
        'name_kh: optional Khmer name.',
        'gender: Male or Female. Required.',
        'dob: date of birth, e.g. 1990-05-12. Optional.',
        'phone: required.',
        'address: optional.',
        'faculty: must match an existing faculty name exactly. Required.',
        'department: must match an existing department name within that faculty. Optional.',
        'major: must match an existing major name within that department. Optional.',
        'qualification / specialization: optional free text.',
        'type: Full-Time or Part-Time. Defaults to Full-Time if left blank.',
        'hire_date: optional date.',
        'status: Active or Inactive. Defaults to Active if left blank.',
        'Username is generated automatically. The initial password is either the "password" column value, or the teacher code if that column is left blank — either way, it should be changed after first login.',
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
            TeacherRowMapper::headers(),
            TeacherRowMapper::sampleRows(),
            $format,
            'teacher-import-template',
            self::SHEET_NAME,
            self::INSTRUCTIONS,
            ['dob', 'hire_date']
        );
    }

    /**
     * Bulk-create teachers from an uploaded CSV/XLSX file. Each row is
     * validated against the same required fields as the manual "Add New
     * Teacher" dialog, and independently — one bad row doesn't sink the
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

        $existingCodes = TeacherProfile::pluck('employee_code')
            ->map(fn ($code) => strtolower($code))
            ->flip();

        $seenCodes = [];
        $valid = [];
        $skipped = [];

        foreach ($entries as $entry) {
            $parsed = TeacherRowMapper::fromRow($entry['data']);

            if ($parsed === null) {
                continue;
            }

            $errors = TeacherRowMapper::validate($parsed);

            $facultyId = null;
            $departmentId = null;
            $majorId = null;

            if ($parsed['faculty'] !== '') {
                $faculty = $faculties->first(fn ($f) => strcasecmp($f->name, $parsed['faculty']) === 0);

                if ($faculty) {
                    $facultyId = $faculty->id;
                } else {
                    $errors[] = "Unknown faculty \"{$parsed['faculty']}\".";
                }
            }

            if ($parsed['department'] !== '') {
                $department = $departments->first(fn ($d) => strcasecmp($d->name, $parsed['department']) === 0
                    && (! $facultyId || $d->faculty_id === $facultyId));

                if ($department) {
                    $departmentId = $department->id;
                } else {
                    $errors[] = "Unknown department \"{$parsed['department']}\" for the given faculty.";
                }
            }

            if ($parsed['major'] !== '') {
                $major = $majors->first(fn ($m) => strcasecmp($m->name, $parsed['major']) === 0
                    && (! $departmentId || $m->department_id === $departmentId));

                if ($major) {
                    $majorId = $major->id;
                } else {
                    $errors[] = "Unknown major \"{$parsed['major']}\" for the given department.";
                }
            }

            $codeLower = strtolower($parsed['code']);

            if ($parsed['code'] !== '') {
                if (isset($existingCodes[$codeLower])) {
                    $errors[] = "Teacher code \"{$parsed['code']}\" already exists.";
                } elseif (isset($seenCodes[$codeLower])) {
                    $errors[] = "Duplicate teacher code \"{$parsed['code']}\" within this file.";
                }
            }

            if ($errors) {
                $skipped[] = ['source' => $entry['source'], 'errors' => $errors];

                continue;
            }

            $seenCodes[$codeLower] = true;
            $valid[] = [
                'source' => $entry['source'],
                'parsed' => $parsed,
                'facultyId' => $facultyId,
                'departmentId' => $departmentId,
                'majorId' => $majorId,
            ];
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
            $created[] = DB::transaction(fn () => $this->createTeacher($v));
        }

        return response()->json([
            'preview' => false,
            'message' => count($created).' teacher(s) imported, '.count($skipped).' skipped.',
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
            'faculty' => $parsed['faculty'] ?: null,
            'department' => $parsed['department'] ?: null,
            'major' => $parsed['major'] ?: null,
            'type' => $parsed['type'],
            'status' => $parsed['status'],
        ];
    }

    private function createTeacher(array $v): array
    {
        $parsed = $v['parsed'];
        [$firstName, $lastName] = $this->splitName($parsed['name_en']);
        $role = Role::where('name', 'Teacher')->first();
        $username = User::generateUsername('TCH');

        $user = User::create([
            'role_id' => $role?->id,
            'username' => $username,
            // Initial password: whatever the row specified, or the
            // teacher's own code if left blank. They're expected to change
            // it after first login (see the template instructions).
            'password' => Hash::make($parsed['password'] !== '' ? $parsed['password'] : $parsed['code']),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name_kh' => $parsed['name_kh'],
            'gender' => $parsed['gender'],
            'dob' => $parsed['dob'],
            'phone' => $parsed['phone'],
            'address' => $parsed['address'],
            'status' => $parsed['status'] === 'Active',
        ]);

        $teacher = TeacherProfile::create([
            'user_id' => $user->id,
            'employee_code' => $parsed['code'],
            'faculty_id' => $v['facultyId'],
            'department_id' => $v['departmentId'],
            'major_id' => $v['majorId'],
            'qualification' => $parsed['qualification'],
            'specialization' => $parsed['specialization'],
            'employment_type' => $parsed['type'] === 'Full-Time' ? 'full_time' : 'part_time',
            'hire_date' => $parsed['hire_date'],
        ]);

        return [
            'id' => $teacher->id,
            'code' => $teacher->employee_code,
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
