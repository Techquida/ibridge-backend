<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class DepartmentSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'Science' => [
                'Mathematics',
                'English Language',
                'Biology',
                'Chemistry',
                'Physics',
                'Geography',
                'Agricultural Science',
                'Further Mathematics',
                'Health Science',
                'Computer Studies/ICT',
                'Technical Drawing',
                'Civic Education',
            ],

            'Art' => [
                'Mathematics',
                'English Language',
                'Literature-in-English',
                'Government',
                'History',
                'Christian Religious Studies (CRS) / Islamic Religious Studies (IRS)',
                'Fine Arts',
                'Music',
                'French',
                'Nigerian Language (Yoruba/Igbo/Hausa)',
                'Geography',
                'Civic Education',
            ],

            'Commercial' => [
                'Mathematics',
                'English Language',
                'Economics',
                'Financial Accounting',
                'Commerce',
                'Government',
                'Geography',
                'Business Studies',
                'Insurance',
                'Civic Education',
            ],
        ];

        foreach ($departments as $deptName => $subjectNames) {
            $department = Department::firstOrCreate(['name' => $deptName]);

            foreach ($subjectNames as $subName) {
                $subject = Subject::firstOrCreate(['name' => $subName]);
                $department->subjects()->syncWithoutDetaching([$subject->id]);
            }
        }
    }
}
