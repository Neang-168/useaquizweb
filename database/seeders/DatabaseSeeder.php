<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Degree;
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
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);
        $this->call(AcademicStructureSeeder::class);

        $adminRole = Role::where('name', 'Admin')->first();
        $teacherRole    = Role::where('name', 'Teacher')->first();
        $studentRole    = Role::where('name', 'Student')->first();

        User::factory()->create([
            'username' => 'admin.user',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'first_name' => 'Admin',
            'last_name' => 'User',
            'role_id' => $adminRole?->id,
        ]);

        $teacherUser = User::factory()->create([
            'username'   => 'teacher.user',
            'email'      => 'teacher@example.com',
            'password'   => Hash::make('password'),
            'first_name' => 'Teacher',
            'last_name'  => 'User',
            'gender'     => 'Male',
            'phone'      => '012345678',
            'role_id'    => $teacherRole?->id,
        ]);

        $studentUser = User::factory()->create([
            'username'   => 'student',
            'email'      => 'student@example.com',
            'password'   => Hash::make('password'), // ពាក្យសម្ងាត់គឺ password
            'first_name' => 'Student',
            'last_name'  => 'User',
            'gender'     => 'Female',
            'phone'      => '098765432',
            'role_id'    => $studentRole?->id,
        ]);

        // Give the teacher/student demo users a matching TeacherProfile /
        // StudentProfile (+ enrollment) so they show up on the Teacher and
        // Student & Enrollment pages, not just User Management — those
        // pages list teacher_profiles/student_profiles rows, not users.
        $faculty = Faculty::where('code', 'IT')->first();
        $department = Department::where('faculty_id', $faculty?->id)->first();
        $degree = Degree::where('faculty_id', $faculty?->id)->where('code', 'BA')->first();
        $major = Major::where('degree_id', $degree?->id)->where('code', 'CS')->first();
        $stage = Stage::where('code', 'Y1')->first();
        $shift = Shift::where('code', 'M')->first();
        $academicYear = AcademicYear::where('is_current', true)->first();
        $semester = Semester::where('academic_year_id', $academicYear?->id)->where('order_no', 1)->first();
        $promotion = Promotion::orderByDesc('year_start')->first();

        TeacherProfile::create([
            'user_id' => $teacherUser->id,
            'employee_code' => 'EMP-0001',
            'faculty_id' => $faculty?->id,
            'department_id' => $department?->id,
            'degree_id' => $degree?->id,
            'employment_type' => 'full_time',
            'hire_date' => now(),
        ]);

        $class = Classroom::firstOrCreate(
            ['code' => 'SE-Y1-M'],
            [
                'name' => 'Software Engineering Year 1 (Morning)',
                'major_id' => $major?->id,
                'stage_id' => $stage?->id,
                'shift_id' => $shift?->id,
                'academic_year_id' => $academicYear?->id,
                'semester_id' => $semester?->id,
                'room' => 'A101',
                'capacity' => 40,
                'status' => true,
            ]
        );

        $studentProfile = StudentProfile::create([
            'user_id' => $studentUser->id,
            'student_code' => 'STU-0001',
            'admission_date' => now(),
        ]);

        StudentEnrollment::create([
            'student_profile_id' => $studentProfile->id,
            'class_id' => $class->id,
            'faculty_id' => $faculty?->id,
            'degree_id' => $degree?->id,
            'major_id' => $major?->id,
            'promotion_id' => $promotion?->id,
            'stage_id' => $stage?->id,
            'academic_year_id' => $academicYear?->id,
            'semester_id' => $semester?->id,
            'shift_id' => $shift?->id,
            'enrollment_date' => now(),
            'status' => 'Active',
        ]);
    }
}
