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
                'enrollments' => fn ($query) => $query->latest('enrollment_date')->with('classroom.shift', 'major'),
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
                'phone' => $validated['phone'],
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

        $student->load('user', 'enrollments.classroom.shift', 'enrollments.major');

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
        $student->load('user', 'enrollments.classroom.shift', 'enrollments.major');

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
                'phone' => $validated['phone'],
                'status' => $validated['status'] !== 'Inactive',
            ]);

            $student->update([
                'student_code' => $validated['student_id'],
            ]);

            $this->syncEnrollment($student, $validated);
        });

        $student->load('user', 'enrollments.classroom.shift', 'enrollments.major');

        return response()->json([
            'message' => 'Student updated successfully.',
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
            'faculty_id' => $class->major->degree->faculty_id,
            'degree_id' => $class->major->degree_id,
            'major_id' => $class->major_id,
            'promotion_id' => $this->resolveCurrentPromotionId(),
            'stage_id' => $class->stage_id,
            'academic_year_id' => $class->academic_year_id,
            'semester_id' => $class->semester_id,
            'shift_id' => $class->shift_id,
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
        // No UI manages promotions/cohorts yet, so this is best-effort: use
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
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => ['required', 'string', 'max:30'],
            'class_id' => ['required', 'exists:classes,id'],
            'status' => ['required', Rule::in(['Active', 'Inactive', 'Suspended'])],
        ]);

        [$firstName, $lastName] = $this->splitName($validated['name_en']);

        return [
            'student_id' => $validated['student_id'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name_kh' => $validated['name_kh'] ?? null,
            'gender' => $validated['gender'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'class_id' => $validated['class_id'],
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
            'phone' => $user->phone,
            'email' => $user->email,
            'class_id' => $enrollment?->class_id,
            'class_name' => $enrollment?->classroom?->name,
            'shift' => $enrollment?->classroom?->shift?->name,
            'major_id' => $enrollment?->major_id,
            'major' => $enrollment?->major?->name,
            'status' => $enrollment?->status ?? ($user->status ? 'Active' : 'Inactive'),
            'avatar' => $user->avatar,
        ];
    }
}
