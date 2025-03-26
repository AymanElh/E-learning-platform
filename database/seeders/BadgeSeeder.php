<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\BadgeRule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
//        Badge::create([
//            'name' => 'Course Creator',
//            'slug' => 'course-creator',
//            'description' => 'Create and publish at least 5 courses',
//            'type' => 'mentor',
//        ]);
//
//        $badge = Badge::where('slug', 'course-creator')->first();
//
//        BadgeRule::create([
//            'badge_id' => $badge->id,
//            'requirement_type' => 'mentor',
//            'requirement_key' => 'courses_published_count',
//            'operator' => '>=',
//            'value' => '5'
//        ]);

//        $badge = Badge::create([
//            'name' => 'Popular Mentor',
//            'slug' => 'popular-mentor',
//            'description' => 'Have at least 50 students enrolled in your courses',
//            'type' => 'mentor'
//        ]);
//
//        // Add the requirement for this badge
//        BadgeRule::create([
//            'badge_id' => $badge->id,
//            'requirement_type' => 'mentor',
//            'requirement_key' => 'total_students',
//            'operator' => '>=',
//            'value' => '50'
//        ]);

        $badge = Badge::create([
            'name' => 'Dedicated Student',
            'slug' => 'dedicated-student',
            'description' => 'Enrolled in at least 10 different courses',
            'type' => 'student'
        ]);

        // Add the requirement for this badge
        BadgeRule::create([
            'badge_id' => $badge->id,
            'requirement_type' => 'student',
            'requirement_key' => 'enrolled_courses_count',
            'operator' => '>=',
            'value' => '10'
        ]);
    }
}
