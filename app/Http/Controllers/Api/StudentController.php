<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Major;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Get all students.
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 15), 100);

        $students = StudentProfile::query()
            ->with([
                'user',
                'enrollments' => fn ($query) => $query->latest('enrollment_date')->with(
                    'faculty',
                    'department',
                    'major',
                    'promotion',
                    'stage',
                    'academicYear',
                    'semester',
                    'term',
                    'shift'
                ),
                'classes.shift',
            ])
            ->when($request->input('q'), function ($query, $q) {
                $query->where('student_code', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($query) use ($q) {
                        $query->where('first_name', 'like', "%{$q}%")
                            ->orWhere('last_name', 'like', "%{$q}%");
                    });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $students->getCollection()->transform(fn (StudentProfile $student) => $this->transform($student));

        return response()->json($students);
    }

    /**
     * Create a new student (User + StudentProfile + StudentEnrollment).
     */
    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $student = DB::transaction(function () use ($validated) {
            $role = Role::where('name', 'Student')->first();

            $user = User::create([
                'role_id' => $role?->id,
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'name_kh' => $validated['name_kh'],
                'gender' => $validated['gender'],
                'dob' => $validated['dob'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'status' => $validated['status'] !== 'Inactive',
            ]);

            $profile = StudentProfile::create([
                'user_id' => $user->id,
                'student_code' => $validated['student_id'],
                'admission_date' => now(),
            ]);

            $this->syncEnrollment($profile, $validated);

            return $profile;
        });

        $student->load(
            'user',
            'enrollments.faculty',
            'enrollments.department',
            'enrollments.major',
            'enrollments.promotion',
            'enrollments.stage',
            'enrollments.academicYear',
            'enrollments.semester',
            'enrollments.term',
            'enrollments.shift',
            'classes.shift'
        );

        return response()->json([
            'message' => 'Student created successfully.',
            'student' => $this->transform($student),
        ], 201);
    }

    /**
     * Get one student.
     */
    public function show(StudentProfile $student)
    {
        $student->load(
            'user',
            'enrollments.faculty',
            'enrollments.department',
            'enrollments.major',
            'enrollments.promotion',
            'enrollments.stage',
            'enrollments.academicYear',
            'enrollments.semester',
            'enrollments.term',
            'enrollments.shift',
            'classes.shift'
        );

        return response()->json([
            'student' => $this->transform($student),
        ]);
    }

    /**
     * Update student.
     */
    public function update(Request $request, StudentProfile $student)
    {
        $validated = $this->validated($request, $student);

        DB::transaction(function () use ($validated, $student) {
            $student->user->update([
                'username' => $validated['username'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'name_kh' => $validated['name_kh'],
                'gender' => $validated['gender'],
                'dob' => $validated['dob'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'status' => $validated['status'] !== 'Inactive',
                ...($validated['password'] ? ['password' => Hash::make($validated['password'])] : []),
            ]);

            $student->update([
                'student_code' => $validated['student_id'],
            ]);

            $this->syncEnrollment($student, $validated);
        });

        $student->load(
            'user',
            'enrollments.faculty',
            'enrollments.department',
            'enrollments.major',
            'enrollments.promotion',
            'enrollments.stage',
            'enrollments.academicYear',
            'enrollments.semester',
            'enrollments.term',
            'enrollments.shift',
            'classes.shift'
        );

        return response()->json([
            'message' => 'Student updated successfully.',
            'student' => $this->transform($student),
        ]);
    }

    /**
     * Quick action: (re)assign a student to one or more classes/generation
     * without touching the rest of their profile. Powers the "Assign"
     * button on the Student & Enrollment page, separate from the full edit
     * form. A student can be assigned to several classes at once, so this
     * replaces their full class list with the given `class_ids`.
     */
    public function assign(Request $request, StudentProfile $student)
    {
        $validated = $request->validate([
            'class_ids' => ['present', 'array'],
            'class_ids.*' => ['integer', 'exists:classes,id'],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
            'status' => ['required', Rule::in(['Active', 'Inactive', 'Suspended'])],
        ]);

        DB::transaction(function () use ($validated, $student) {
            $this->syncEnrollment($student, [
                'promotion_id' => $validated['promotion_id'] ?? null,
                'status' => $validated['status'],
            ]);

            $student->classes()->sync(
                collect($validated['class_ids'])
                    ->mapWithKeys(fn ($classId) => [$classId => ['status' => $validated['status']]])
                    ->all()
            );
        });

        $student->load(
            'user',
            'enrollments.faculty',
            'enrollments.department',
            'enrollments.major',
            'enrollments.promotion',
            'enrollments.stage',
            'enrollments.academicYear',
            'enrollments.semester',
            'enrollments.term',
            'enrollments.shift',
            'classes.shift'
        );

        return response()->json([
            'message' => 'Student assigned successfully.',
            'student' => $this->transform($student),
        ]);
    }

    /**
     * Delete student (soft-deletes the underlying user).
     */
    public function destroy(StudentProfile $student)
    {
        $student->user?->delete();
        $student->delete();

        return response()->json([
            'message' => 'Student deleted successfully.',
        ]);
    }

    /**
     * Create or refresh the student's current enrollment record (their
     * general academic placement: faculty/major/academic year/etc). Class
     * membership itself lives separately on the `classes()` pivot, since a
     * student can belong to more than one class at once.
     */
    private function syncEnrollment(StudentProfile $profile, array $validated): void
    {
        $enrollment = $profile->enrollments()->latest('enrollment_date')->first();

        $major = isset($validated['major_id']) ? Major::find($validated['major_id']) : null;

        $attributes = [
            'faculty_id' => $validated['faculty_id'] ?? $major?->degree?->faculty_id ?? $enrollment?->faculty_id,
            'department_id' => $validated['department_id'] ?? $enrollment?->department_id,
            'degree_id' => $validated['degree_id'] ?? $major?->degree_id ?? $enrollment?->degree_id,
            'major_id' => $validated['major_id'] ?? $enrollment?->major_id,
            'promotion_id' => $validated['promotion_id'] ?? $enrollment?->promotion_id ?? $this->resolveCurrentPromotionId(),
            'stage_id' => $validated['stage_id'] ?? $enrollment?->stage_id,
            'academic_year_id' => $validated['academic_year_id'] ?? $enrollment?->academic_year_id,
            'semester_id' => $validated['semester_id'] ?? $enrollment?->semester_id,
            'term_id' => $validated['term_id'] ?? $enrollment?->term_id,
            'shift_id' => $validated['shift_id'] ?? $enrollment?->shift_id,
            'status' => $validated['status'],
        ];

        if ($enrollment) {
            $enrollment->update($attributes);
        } else {
            $profile->enrollments()->create($attributes + ['enrollment_date' => now()]);
        }
    }

    private function resolveCurrentPromotionId(): ?int
    {
        // Fallback when the admin doesn't pick a generation explicitly: use
        // the latest active one if any exist, otherwise leave it unset.
        return Promotion::where('status', true)->orderByDesc('year_start')->first()?->id;
    }

    private function validated(Request $request, ?StudentProfile $student = null): array
    {
        $userId = $student?->user_id;

        $validated = $request->validate([
            'student_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('student_profiles', 'student_code')->ignore($student?->id),
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'name_en' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'dob' => ['nullable', 'date'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'password' => [$student ? 'nullable' : 'required', 'string', 'min:8'],
            'faculty_id' => ['required', 'exists:faculties,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'degree_id' => ['nullable', 'exists:degrees,id'],
            'major_id' => ['required', 'exists:majors,id'],
            'promotion_id' => ['required', 'exists:promotions,id'],
            'stage_id' => ['required', 'exists:stages,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'status' => ['required', Rule::in(['Active', 'Inactive', 'Suspended'])],
        ]);

        [$firstName, $lastName] = $this->splitName($validated['name_en']);

        return [
            'student_id' => $validated['student_id'],
            'username' => $validated['username'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name_kh' => $validated['name_kh'] ?? null,
            'gender' => $validated['gender'],
            'dob' => $validated['dob'] ?? null,
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'password' => $validated['password'] ?? null,
            'faculty_id' => $validated['faculty_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'degree_id' => $validated['degree_id'] ?? null,
            'major_id' => $validated['major_id'] ?? null,
            'promotion_id' => $validated['promotion_id'] ?? null,
            'stage_id' => $validated['stage_id'] ?? null,
            'academic_year_id' => $validated['academic_year_id'] ?? null,
            'semester_id' => $validated['semester_id'] ?? null,
            'term_id' => $validated['term_id'] ?? null,
            'shift_id' => $validated['shift_id'] ?? null,
            'status' => $validated['status'],
        ];
    }

    private function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName), 2);

        return [$parts[0], $parts[1] ?? ''];
    }

    private function transform(StudentProfile $student): array
    {
        $user = $student->user;
        $enrollment = $student->enrollments->first();
        $classes = $student->classes;

        return [
            'id' => $student->id,
            'student_id' => $student->student_code,
            'username' => $user->username,
            'name_en' => trim($user->first_name . ' ' . $user->last_name),
            'name_kh' => $user->name_kh,
            'gender' => $user->gender,
            'dob' => $user->dob?->toDateString(),
            'phone' => $user->phone,
            'address' => $user->address,
            // A student can belong to several classes at once; class_id /
            // class_name mirror the first one for older UI that only shows
            // a single class, while class_ids / classes carry the full list.
            'class_id' => $classes->first()?->id,
            'class_name' => $classes->pluck('name')->filter()->implode(', ') ?: null,
            'class_ids' => $classes->pluck('id')->values(),
            'classes' => $classes->map(fn ($class) => [
                'id' => $class->id,
                'name' => $class->name,
                'status' => $class->pivot->status,
            ])->values(),
            'faculty_id' => $enrollment?->faculty_id,
            'faculty_name' => $enrollment?->faculty?->name,
            'department_id' => $enrollment?->department_id,
            'department_name' => $enrollment?->department?->name,
            'major_id' => $enrollment?->major_id,
            'major' => $enrollment?->major?->name,
            'major_name' => $enrollment?->major?->name,
            'promotion_id' => $enrollment?->promotion_id,
            'generation' => $enrollment?->promotion
                ? "{$enrollment->promotion->year_start}-{$enrollment->promotion->year_end}"
                : null,
            'stage_id' => $enrollment?->stage_id,
            'stage_name' => $enrollment?->stage?->name,
            'academic_year_id' => $enrollment?->academic_year_id,
            'academic_year_name' => $enrollment?->academicYear?->name,
            'semester_id' => $enrollment?->semester_id,
            'semester_name' => $enrollment?->semester?->name,
            'term_id' => $enrollment?->term_id,
            'term_name' => $enrollment?->term?->name,
            'shift_id' => $enrollment?->shift_id,
            'shift' => $enrollment?->shift?->name ?? $classes->first()?->shift?->name,
            'status' => $enrollment?->status ?? ($user->status ? 'Active' : 'Inactive'),
            'avatar' => $user->avatar,
        ];
    }
}
