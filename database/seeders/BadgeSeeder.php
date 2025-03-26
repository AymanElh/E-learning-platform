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
        Badge::create([
            'name' => 'Course Creator',
            'slug' => 'course-creator',
            'description' => 'Create and publish at least 5 courses',
            'type' => 'mentor',
        ]);

        $badge = Badge::where('slug', 'course-creator')->first();

        BadgeRule::create([
            'badge_id' => $badge->id,
            'requirement_type' => 'mentor',
            'requirement_key' => 'courses_published_count',
            'operator' => '>=',
            'value' => '5'
        ]);
    }
}
