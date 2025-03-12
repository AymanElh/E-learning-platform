<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseTag;
use Database\Factories\CourseTagFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::factory(10)->create();

        CourseTag::factory(20)->create([
            'course_id' => $courses->random()->id
        ]);
    }
}
