<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'Science' => ['Mathematics', 'English', 'Biology', 'Chemistry', 'Physics', 'Geography'],
            'Art' => ['Mathematics', 'English', 'Literature', 'Government', 'History'],
            'Commercial' => ['Mathematics', 'English', 'Economics', 'Geography', 'Government'],
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
