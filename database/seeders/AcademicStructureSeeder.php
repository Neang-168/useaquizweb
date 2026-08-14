<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Degree;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\Promotion;
use App\Models\Semester;
use App\Models\Shift;
use App\Models\Stage;
use App\Models\Term;
use Illuminate\Database\Seeder;

class AcademicStructureSeeder extends Seeder
{
    /**
     * Seed default lookup data (Faculty -> Department, Faculty -> Degree ->
     * Major, Stage, Shift, Academic Year -> Semester/Term, Promotion) so
     * Classes/Teachers/Students/Enrollments have real rows to reference.
     * Idempotent: safe to re-run.
     */
    public function run(): void
    {
        $it = Faculty::updateOrCreate(
            ['code' => 'IT'],
            [
                'name' => 'Information Technology',
                'name_kh' => 'បច្ចេកវិទ្យាព័ត៌មាន',
                'description' => null,
                'status' => true,
            ]
        );

        $bus = Faculty::updateOrCreate(
            ['code' => 'BUS'],
            [
                'name' => 'Business Administration',
                'name_kh' => 'រដ្ឋបាលពាណិជ្ជកម្ម',
                'description' => null,
                'status' => true,
            ]
        );

        $departments = [
            ['faculty_id' => $it->id, 'code' => 'SE', 'name' => 'Software Engineering', 'name_kh' => 'វិស្វកម្មសូហ្វវែរ'],
            ['faculty_id' => $it->id, 'code' => 'NIS', 'name' => 'Network & Information Systems', 'name_kh' => 'បណ្តាញ និងប្រព័ន្ធព័ត៌មាន'],
            ['faculty_id' => $bus->id, 'code' => 'MGT', 'name' => 'Management', 'name_kh' => 'គ្រប់គ្រង'],
            ['faculty_id' => $bus->id, 'code' => 'ACF', 'name' => 'Accounting & Finance', 'name_kh' => 'គណនេយ្យ និងហិរញ្ញវត្ថុ'],
        ];
        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['code' => $department['code']],
                [
                    'faculty_id' => $department['faculty_id'],
                    'name' => $department['name'],
                    'name_kh' => $department['name_kh'],
                    'description' => null,
                    'status' => true,
                ]
            );
        }

        // Guarantee every faculty has at least one department, including
        // ones created outside this seeder (e.g. through the API/UI), so
        // the Teachers page never shows an empty Department dropdown.
        Faculty::all()->each(function (Faculty $faculty) {
            if (Department::where('faculty_id', $faculty->id)->exists()) {
                return;
            }

            Department::create([
                'faculty_id' => $faculty->id,
                'code' => strtoupper($faculty->code) . '-GEN',
                'name' => 'General Department',
                'name_kh' => null,
                'description' => null,
                'status' => true,
            ]);
        });

        $itBachelor = Degree::updateOrCreate(
            ['faculty_id' => $it->id, 'code' => 'BA'],
            [
                'name' => 'Bachelor of Science',
                'name_kh' => 'បរិញ្ញាបត្រវិទ្យាសាស្ត្រ',
                'duration_years' => 4,
                'description' => null,
                'status' => true,
            ]
        );

        $busBachelor = Degree::updateOrCreate(
            ['faculty_id' => $bus->id, 'code' => 'BBA'],
            [
                'name' => 'Bachelor of Business Administration',
                'name_kh' => 'បរិញ្ញាបត្ររដ្ឋបាលពាណិជ្ជកម្ម',
                'duration_years' => 4,
                'description' => null,
                'status' => true,
            ]
        );

        Major::updateOrCreate(
            ['degree_id' => $itBachelor->id, 'code' => 'CS'],
            ['name' => 'Computer Science', 'name_kh' => 'វិទ្យាសាស្ត្រកុំព្យូទ័រ', 'description' => null, 'status' => true]
        );

        Major::updateOrCreate(
            ['degree_id' => $itBachelor->id, 'code' => 'IT'],
            ['name' => 'Information Technology', 'name_kh' => 'បច្ចេកវិទ្យាព័ត៌មាន', 'description' => null, 'status' => true]
        );

        Major::updateOrCreate(
            ['degree_id' => $busBachelor->id, 'code' => 'BA'],
            ['name' => 'Business Administration', 'name_kh' => 'រដ្ឋបាលពាណិជ្ជកម្ម', 'description' => null, 'status' => true]
        );

        Major::updateOrCreate(
            ['degree_id' => $busBachelor->id, 'code' => 'ACC'],
            ['name' => 'Accounting', 'name_kh' => 'គណនេយ្យ', 'description' => null, 'status' => true]
        );

        $stages = [
            ['code' => 'Y1', 'name' => 'Year 1', 'name_kh' => 'ឆ្នាំទី១', 'order_no' => 1],
            ['code' => 'Y2', 'name' => 'Year 2', 'name_kh' => 'ឆ្នាំទី២', 'order_no' => 2],
            ['code' => 'Y3', 'name' => 'Year 3', 'name_kh' => 'ឆ្នាំទី៣', 'order_no' => 3],
            ['code' => 'Y4', 'name' => 'Year 4', 'name_kh' => 'ឆ្នាំទី៤', 'order_no' => 4],
        ];
        foreach ($stages as $stage) {
            Stage::updateOrCreate(
                ['code' => $stage['code']],
                ['name' => $stage['name'], 'name_kh' => $stage['name_kh'], 'order_no' => $stage['order_no'], 'status' => true]
            );
        }

        $shifts = [
            ['code' => 'M', 'name' => 'Morning', 'name_kh' => 'ព្រឹក', 'start_time' => '07:00:00', 'end_time' => '11:30:00'],
            ['code' => 'A', 'name' => 'Afternoon', 'name_kh' => 'រសៀល', 'start_time' => '13:00:00', 'end_time' => '17:30:00'],
            ['code' => 'E', 'name' => 'Evening', 'name_kh' => 'ល្ងាច', 'start_time' => '17:30:00', 'end_time' => '20:30:00'],
        ];
        foreach ($shifts as $shift) {
            Shift::updateOrCreate(
                ['code' => $shift['code']],
                [
                    'name' => $shift['name'],
                    'name_kh' => $shift['name_kh'],
                    'start_time' => $shift['start_time'],
                    'end_time' => $shift['end_time'],
                    'status' => true,
                ]
            );
        }

        $academicYear = AcademicYear::updateOrCreate(
            ['code' => 'AY2025-2026'],
            [
                'name' => '2025-2026',
                'name_kh' => 'ឆ្នាំសិក្សា ២០២៥-២០២៦',
                'start_date' => '2025-09-01',
                'end_date' => '2026-08-31',
                'is_current' => true,
                'status' => true,
            ]
        );

        AcademicYear::query()
            ->where('id', '!=', $academicYear->id)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        Semester::updateOrCreate(
            ['academic_year_id' => $academicYear->id, 'order_no' => 1],
            ['name' => 'Semester 1', 'start_date' => '2025-09-01', 'end_date' => '2026-01-31', 'status' => true]
        );

        Semester::updateOrCreate(
            ['academic_year_id' => $academicYear->id, 'order_no' => 2],
            ['name' => 'Semester 2', 'start_date' => '2026-02-01', 'end_date' => '2026-08-31', 'status' => true]
        );

        $terms = [
            ['order_no' => 1, 'name' => 'Term 1', 'name_kh' => 'ឆមាសទី១', 'code' => 'T1-2025-2026', 'start_date' => '2025-09-01', 'end_date' => '2025-12-31'],
            ['order_no' => 2, 'name' => 'Term 2', 'name_kh' => 'ឆមាសទី២', 'code' => 'T2-2025-2026', 'start_date' => '2026-01-01', 'end_date' => '2026-04-30'],
            ['order_no' => 3, 'name' => 'Term 3', 'name_kh' => 'ឆមាសទី៣', 'code' => 'T3-2025-2026', 'start_date' => '2026-05-01', 'end_date' => '2026-08-31'],
        ];
        foreach ($terms as $term) {
            Term::updateOrCreate(
                ['academic_year_id' => $academicYear->id, 'order_no' => $term['order_no']],
                [
                    'code' => $term['code'],
                    'name' => $term['name'],
                    'name_kh' => $term['name_kh'],
                    'start_date' => $term['start_date'],
                    'end_date' => $term['end_date'],
                    'status' => true,
                ]
            );
        }

        Promotion::firstOrCreate(['year_start' => 2025, 'year_end' => 2029], ['status' => true]);
        Promotion::firstOrCreate(['year_start' => 2026, 'year_end' => 2030], ['status' => true]);
    }
}
