<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\StudentEnrollment;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
                    'classroom.shift',
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
                'username' => $validated['student_id'],
                'email' => $validated['email'] ?? "{$validated['student_id']}@usea.edu.kh",
                'password' => Hash::make(Str::random(16)),
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
            'enrollments.classroom.shift',
            'enrollments.faculty',
            'enrollments.department',
            'enrollments.major',
            'enrollments.promotion',
            'enrollments.stage',
            'enrollments.academicYear',
            'enrollments.semester',
            'enrollments.term',
            'enrollments.shift'
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
            'enrollments.classroom.shift',
            'enrollments.faculty',
            'enrollments.department',
            'enrollments.major',
            'enrollments.promotion',
            'enrollments.stage',
            'enrollments.academicYear',
            'enrollments.semester',
            'enrollments.term',
            'enrollments.shift'
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
                'email' => $validated['email'] ?? $student->user->email,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'name_kh' => $validated['name_kh'],
                'gender' => $validated['gender'],
                'dob' => $validated['dob'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'status' => $validated['status'] !== 'Inactive',
            ]);

            $student->update([
                'student_code' => $validated['student_id'],
            ]);

            $this->syncEnrollment($student, $validated);
        });

        $student->load(
            'user',
            'enrollments.classroom.shift',
            'enrollments.faculty',
            'enrollments.department',
            'enrollments.major',
            'enrollments.promotion',
            'enrollments.stage',
            'enrollments.academicYear',
            'enrollments.semester',
            'enrollments.term',
            'enrollments.shift'
        );

        return response()->json([
            'message' => 'Student updated successfully.',
            'student' => $this->transform($student),
        ]);
    }

    /**
     * Quick action: (re)assign a student to a class/generation without
     * touching the rest of their profile. Powers the "Assign" button on
     * the Student & Enrollment page, separate from the full edit form.
     */
    public function assign(Request $request, StudentProfile $student)
    {
        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
            'status' => ['required', Rule::in(['Active', 'Inactive', 'Suspended'])],
        ]);

        $this->syncEnrollment($student, $validated);

        $student->load(
            'user',
            'enrollments.classroom.shift',
            'enrollments.faculty',
            'enrollments.department',
            'enrollments.major',
            'enrollments.promotion',
            'enrollments.stage',
            'enrollments.academicYear',
            'enrollments.semester',
            'enrollments.term',
            'enrollments.shift'
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
     * Create or refresh the student's current enrollment record to match
     * the class they've been assigned to.
     */
    private function syncEnrollment(StudentProfile $profile, array $validated): void
    {
        $class = Classroom::with('major.degree')->findOrFail($validated['class_id']);

        $enrollment = $profile->enrollments()->latest('enrollment_date')->first();

        $attributes = [
            'class_id' => $class->id,
            'faculty_id' => $validated['faculty_id'] ?? $class->major->degree->faculty_id,
            'department_id' => $validated['department_id'] ?? $class->department_id,
            'degree_id' => $validated['degree_id'] ?? $class->major->degree_id,
            'major_id' => $validated['major_id'] ?? $class->major_id,
            'promotion_id' => $validated['promotion_id'] ?? $this->resolveCurrentPromotionId(),
            'stage_id' => $validated['stage_id'] ?? $class->stage_id,
            'academic_year_id' => $validated['academic_year_id'] ?? $class->academic_year_id,
            'semester_id' => $validated['semester_id'] ?? $class->semester_id,
            'term_id' => $validated['term_id'] ?? $class->term_id,
            'shift_id' => $validated['shift_id'] ?? $class->shift_id,
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
            'name_en' => ['required', 'string', 'max:255'],
            'name_kh' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'dob' => ['nullable', 'date'],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'class_id' => ['required', 'exists:classes,id'],
            'faculty_id' => ['nullable', 'exists:faculties,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'degree_id' => ['nullable', 'exists:degrees,id'],
            'major_id' => ['nullable', 'exists:majors,id'],
            'promotion_id' => ['nullable', 'exists:promotions,id'],
            'stage_id' => ['nullable', 'exists:stages,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'semester_id' => ['nullable', 'exists:semesters,id'],
            'term_id' => ['nullable', 'exists:terms,id'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'status' => ['required', Rule::in(['Active', 'Inactive', 'Suspended'])],
        ]);

        [$firstName, $lastName] = $this->splitName($validated['name_en']);

        return [
            'student_id' => $validated['student_id'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name_kh' => $validated['name_kh'] ?? null,
            'gender' => $validated['gender'],
            'dob' => $validated['dob'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'class_id' => $validated['class_id'],
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

        return [
            'id' => $student->id,
            'student_id' => $student->student_code,
            'name_en' => trim($user->first_name . ' ' . $user->last_name),
            'name_kh' => $user->name_kh,
            'gender' => $user->gender,
            'dob' => $user->dob?->toDateString(),
            'phone' => $user->phone,
            'email' => $user->email,
            'address' => $user->address,
            'class_id' => $enrollment?->class_id,
            'class_name' => $enrollment?->classroom?->name,
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
            'shift' => $enrollment?->shift?->name ?? $enrollment?->classroom?->shift?->name,
            'status' => $enrollment?->status ?? ($user->status ? 'Active' : 'Inactive'),
            'avatar' => $user->avatar,
        ];
    }
}
