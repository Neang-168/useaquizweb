<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
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
     * Seed the real institution's lookup data (Faculty -> Department -> Major,
     * Stage, Shift, Academic Year -> Semester/Term, Promotion) so
     * Classes/Teachers/Students/Enrollments have real rows to reference.
     *
     * Majors attach directly to a Department (not a Degree) — Degree is a
     * legacy concept kept nullable on `majors` for backward compatibility,
     * but every major seeded here leaves `degree_id` unset.
     *
     * Idempotent: safe to re-run.
     */
    public function run(): void
    {
        $faculties = [
            'SCT' => Faculty::updateOrCreate(
                ['code' => 'SCT'],
                [
                    'name' => 'Science And Technology',
                    'name_kh' => 'វិទ្យាសាស្ត្រ និងបច្ចេកវិទ្យា',
                    'description' => null,
                    'status' => true,
                ]
            ),
            'EBT' => Faculty::updateOrCreate(
                ['code' => 'EBT'],
                [
                    'name' => 'Economics, Business and Tourism',
                    'name_kh' => 'សេដ្ឋកិច្ច ពាណិជ្ជកម្ម និងទេសចរណ៍',
                    'description' => null,
                    'status' => true,
                ]
            ),
            'SSL' => Faculty::updateOrCreate(
                ['code' => 'SSL'],
                [
                    'name' => 'Social Science And Law',
                    'name_kh' => 'សង្គមវិទ្យា និងនីតិសាស្ត្រ',
                    'description' => null,
                    'status' => true,
                ]
            ),
        ];

        $departmentDefs = [
            ['code' => 'ICT', 'faculty' => 'SCT', 'name' => 'Information and Communication Technology', 'name_kh' => 'បច្ចេកវិទ្យាព័ត៌មាន និងទំនាក់ទំនង'],
            ['code' => 'ARCH', 'faculty' => 'SCT', 'name' => 'Architecture', 'name_kh' => 'ស្ថាបត្យកម្ម'],
            ['code' => 'MATH', 'faculty' => 'SCT', 'name' => 'Mathematics', 'name_kh' => 'គណិតវិទ្យា'],
            ['code' => 'BUS', 'faculty' => 'EBT', 'name' => 'Business', 'name_kh' => 'ពាណិជ្ជកម្ម'],
            ['code' => 'TOU', 'faculty' => 'EBT', 'name' => 'Tourism', 'name_kh' => 'ទេសចរណ៍'],
            ['code' => 'ECO', 'faculty' => 'EBT', 'name' => 'Economic', 'name_kh' => 'សេដ្ឋកិច្ច'],
            ['code' => 'LAW', 'faculty' => 'SSL', 'name' => 'Law', 'name_kh' => 'នីតិសាស្ត្រ'],
        ];

        $departments = [];
        foreach ($departmentDefs as $def) {
            $departments[$def['code']] = Department::updateOrCreate(
                ['code' => $def['code']],
                [
                    'faculty_id' => $faculties[$def['faculty']]->id,
                    'name' => $def['name'],
                    'name_kh' => $def['name_kh'],
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

        $majorDefs = [
            ['code' => 'IT', 'department' => 'ICT', 'name' => 'Information Technology', 'name_kh' => 'បច្ចេកវិទ្យាព័ត៌មាន'],
            ['code' => 'ARC', 'department' => 'ARCH', 'name' => 'Architecture', 'name_kh' => 'ស្ថាបត្យកម្ម'],
            ['code' => 'CE', 'department' => 'ARCH', 'name' => 'Civil Engineering', 'name_kh' => 'វិស្វកម្មសំណង់'],
            ['code' => 'MATH', 'department' => 'MATH', 'name' => 'Mathematics', 'name_kh' => 'គណិតវិទ្យា'],
            ['code' => 'ACC', 'department' => 'BUS', 'name' => 'Accounting', 'name_kh' => 'គណនេយ្យ'],
            ['code' => 'FAB', 'department' => 'BUS', 'name' => 'Finance And Banking', 'name_kh' => 'ហិរញ្ញវត្ថុ និងធនាគារ'],
            ['code' => 'MGT', 'department' => 'BUS', 'name' => 'Management', 'name_kh' => 'គ្រប់គ្រង'],
            ['code' => 'THM', 'department' => 'TOU', 'name' => 'Tourism And Hospitality Management', 'name_kh' => 'ការគ្រប់គ្រងទេសចរណ៍ និងបដិសណ្ឋារកិច្ច'],
            ['code' => 'IEC', 'department' => 'ECO', 'name' => 'International Economic', 'name_kh' => 'សេដ្ឋកិច្ចអន្តរជាតិ'],
            ['code' => 'LAWS', 'department' => 'LAW', 'name' => 'Laws', 'name_kh' => 'នីតិសាស្ត្រ'],
            ['code' => 'PA', 'department' => 'LAW', 'name' => 'Public Administration', 'name_kh' => 'រដ្ឋបាលសាធារណៈ'],
        ];

        foreach ($majorDefs as $def) {
            Major::updateOrCreate(
                ['department_id' => $departments[$def['department']]->id, 'code' => $def['code']],
                [
                    'name' => $def['name'],
                    'name_kh' => $def['name_kh'],
                    'description' => null,
                    'status' => true,
                ]
            );
        }

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

        // Academic Years run Oct 1 -> Aug 31 the following year. Semester
        // 1-2 and Term 1-4 are only seeded for the current academic year —
        // both dates are approximate and editable later, there's no admin
        // UI for them yet.
        $academicYearDefs = [
            ['code' => 'AY2022-2023', 'name' => '2022-2023', 'name_kh' => 'ឆ្នាំសិក្សា ២០២២-២០២៣', 'start' => '2022-10-01', 'end' => '2023-08-31', 'is_current' => false],
            ['code' => 'AY2023-2024', 'name' => '2023-2024', 'name_kh' => 'ឆ្នាំសិក្សា ២០២៣-២០២៤', 'start' => '2023-10-01', 'end' => '2024-08-31', 'is_current' => false],
            ['code' => 'AY2024-2025', 'name' => '2024-2025', 'name_kh' => 'ឆ្នាំសិក្សា ២០២៤-២០២៥', 'start' => '2024-10-01', 'end' => '2025-08-31', 'is_current' => false],
            ['code' => 'AY2025-2026', 'name' => '2025-2026', 'name_kh' => 'ឆ្នាំសិក្សា ២០២៥-២០២៦', 'start' => '2025-10-01', 'end' => '2026-08-31', 'is_current' => true],
        ];

        $currentAcademicYear = null;

        foreach ($academicYearDefs as $def) {
            $academicYear = AcademicYear::updateOrCreate(
                ['code' => $def['code']],
                [
                    'name' => $def['name'],
                    'name_kh' => $def['name_kh'],
                    'start_date' => $def['start'],
                    'end_date' => $def['end'],
                    'is_current' => $def['is_current'],
                    'status' => true,
                ]
            );

            if ($def['is_current']) {
                $currentAcademicYear = $academicYear;
            }
        }

        AcademicYear::query()
            ->where('code', '!=', 'AY2025-2026')
            ->where('is_current', true)
            ->update(['is_current' => false]);

        // Remove any Semester/Term rows seeded for the non-current academic
        // years by an earlier version of this seeder, so re-running it
        // cleans up the old per-year duplicates.
        Semester::where('academic_year_id', '!=', $currentAcademicYear->id)->delete();
        Term::where('academic_year_id', '!=', $currentAcademicYear->id)->delete();

        $startYear = (int) $currentAcademicYear->start_date->format('Y');
        $endYear = (int) $currentAcademicYear->end_date->format('Y');
        $endDate = $currentAcademicYear->end_date->toDateString();

        Semester::updateOrCreate(
            ['academic_year_id' => $currentAcademicYear->id, 'order_no' => 1],
            [
                'name' => 'Semester 1',
                'name_kh' => 'ឆមាសទី ១',
                'start_date' => "{$startYear}-10-01",
                'end_date' => "{$endYear}-02-28",
                'status' => true,
            ]
        );

        Semester::updateOrCreate(
            ['academic_year_id' => $currentAcademicYear->id, 'order_no' => 2],
            [
                'name' => 'Semester 2',
                'name_kh' => 'ឆមាសទី ២',
                'start_date' => "{$endYear}-03-01",
                'end_date' => $endDate,
                'status' => true,
            ]
        );

        $terms = [
            ['order_no' => 1, 'name_kh' => 'វគ្គ១', 'start' => "{$startYear}-10-01", 'end' => "{$startYear}-12-31"],
            ['order_no' => 2, 'name_kh' => 'វគ្គ២', 'start' => "{$endYear}-01-01", 'end' => "{$endYear}-03-31"],
            ['order_no' => 3, 'name_kh' => 'វគ្គ៣', 'start' => "{$endYear}-04-01", 'end' => "{$endYear}-06-30"],
            ['order_no' => 4, 'name_kh' => 'វគ្គ៤', 'start' => "{$endYear}-07-01", 'end' => $endDate],
        ];

        foreach ($terms as $term) {
            Term::updateOrCreate(
                ['academic_year_id' => $currentAcademicYear->id, 'order_no' => $term['order_no']],
                [
                    'code' => "T{$term['order_no']}-{$currentAcademicYear->name}",
                    'name' => "Term {$term['order_no']}",
                    'name_kh' => $term['name_kh'],
                    'start_date' => $term['start'],
                    'end_date' => $term['end'],
                    'status' => true,
                ]
            );
        }

        $promotionDefs = [
            ['name' => 'Promotion 17', 'name_kh' => 'ជំនាន់ទី ១៧', 'year_start' => 2021, 'year_end' => 2025],
            ['name' => 'Promotion 18', 'name_kh' => 'ជំនាន់ទី ១៨', 'year_start' => 2022, 'year_end' => 2026],
            ['name' => 'Promotion 19', 'name_kh' => 'ជំនាន់ទី ១៩', 'year_start' => 2023, 'year_end' => 2027],
            ['name' => 'Promotion 20', 'name_kh' => 'ជំនាន់ទី ២០', 'year_start' => 2024, 'year_end' => 2028],
            ['name' => 'Promotion 21', 'name_kh' => 'ជំនាន់ទី ២១', 'year_start' => 2025, 'year_end' => 2029],
        ];

        foreach ($promotionDefs as $def) {
            Promotion::updateOrCreate(
                ['year_start' => $def['year_start'], 'year_end' => $def['year_end']],
                ['name' => $def['name'], 'name_kh' => $def['name_kh'], 'status' => true]
            );
        }
    }
}
