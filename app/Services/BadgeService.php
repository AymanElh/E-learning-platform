<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use App\Notifications\BadgeEarned;

class BadgeService
{
    /**
     * Check if a user meets the requirements for a badge and award it if they do
     *
     * @param User $user The user to check against badge requirements
     * @param Badge $badge The badge to check requirements for
     * @param array $context Additional context data for requirement checking
     * @return bool Whether the badge was awarded
     */
    public function checkAndAwardBadge(User $user, Badge $badge, array $context = []): bool
    {
        if ($user->badges()->where('badge_id', $badge->id)->exists()) {
            \Log::info("User {$user->id} already has badge {$badge->name}");
            return false;
        }

        // Check if all requirements for this badge are met
        if (!$this->meetsAllRequirements($user, $badge, $context)) {
            \Log::info("User {$user->id} does not meet all requirements for badge {$badge->name}");
            return false;
        }

        // User meets all requirements - award the badge
        $this->awardBadge($user, $badge);
        return true;
    }

    /**
     * Check if a user meets all requirements for a badge
     *
     * @param User $user The user to check
     * @param Badge $badge The badge to check against
     * @param array $context Additional context data for requirement checking
     * @return bool Whether all requirements are met
     */
    protected function meetsAllRequirements(User $user, Badge $badge, array $context = []): bool
    {
        $requirements = $badge->rules;

        if ($requirements->isEmpty()) {
            \Log::warning("Badge {$badge->name} has no requirements");
            return false;
        }

        foreach ($requirements as $requirement) {
            // Get the actual value for this requirement
            $actualValue = $this->getRequirementValue($user, $requirement, $context);

            // Debug log for each requirement check
            \Log::info("Badge requirement check", [
                'user_id' => $user->id,
                'badge_name' => $badge->name,
                'requirement_key' => $requirement->requirement_key,
                'operator' => $requirement->operator,
                'required_value' => $requirement->value,
                'actual_value' => $actualValue,
                'is_met' => $requirement->isMet($actualValue)
            ]);

            // Check if this requirement is met
            if (!$requirement->isMet($actualValue)) {
                \Log::info("User {$user->id} does not meet requirement {$requirement->requirement_key} for badge {$badge->name}. Required: {$requirement->operator} {$requirement->value}, Actual: {$actualValue}");
                return false;
            }
        }

        return true;
    }
    /**
     * Get the actual value for a requirement from user data or context
     *
     * @param User $user The user to check
     * @param BadgeRequirement $requirement The requirement to evaluate
     * @param array $context Additional context data
     * @return mixed The actual value for this requirement
     */
    protected function getRequirementValue(User $user, $requirement, array $context): mixed
    {
        $key = $requirement->requirement_key;

        // First check if the value is in the context
        if (array_key_exists($key, $context)) {
            return $context[$key];
        }

        // Otherwise get the value based on the requirement type
        switch ($requirement->requirement_type) {
            case 'mentor':
                return $this->getMentorRequirementValue($user, $key);

            case 'student':
                return $this->getStudentRequirementValue($user, $key);

            case 'profile':
                return $this->getProfileRequirementValue($user, $key);

            case 'activity':
                return $this->getActivityRequirementValue($user, $key);


            default:
                \Log::warning("Unknown requirement type: {$requirement->requirement_type}");
                return null;
        }
    }

    /**
     * Get values for mentor-related requirements
     */
    protected function getMentorRequirementValue(User $user, string $key): mixed
    {
        switch ($key) {
            case 'courses_published_count':
                return $user->courses()->where('status', 'published')->count();

            case 'total_students':
                return $user->enrollments()->distinct('user_id')->count();

            default:
                return null;
        }
    }

    /**
     * Get values for student-related requirements
     */
    protected function getStudentRequirementValue(User $user, string $key): mixed
    {
        switch ($key) {
            case 'completed_courses_count':
                return $user->enrollments()->where('status', 'completed')->count();

            case 'enrolled_courses_count':
                return $user->enrollments()->count();

            default:
                return null;
        }
    }

    /**
     * Get values for profile-related requirements
     */
    protected function getProfileRequirementValue(User $user, string $key): mixed
    {
        switch ($key) {
            case 'profile_completion':
                // Calculate profile completion percentage
                // This is a simplified example - you'd implement actual logic
                $fields = ['name', 'bio', 'avatar', 'phone', 'website'];
                $completed = 0;

                foreach ($fields as $field) {
                    if (!empty($user->$field)) {
                        $completed++;
                    }
                }

                return ($completed / count($fields)) * 100;

            default:
                return null;
        }
    }

    /**
     * Get values for activity-related requirements
     */
    protected function getActivityRequirementValue(User $user, string $key): mixed
    {
        switch ($key) {
            case 'account_age_months':
                return $user->created_at->diffInMonths(now());

            case 'is_active':
                // Simplified - you'd implement actual activity tracking
                return $user->last_login_at && $user->last_login_at->diffInDays(now()) < 30;

            default:
                return null;
        }
    }

    /**
     * Award a badge to a user
     *
     * @param User $user The user to award the badge to
     * @param Badge $badge The badge to award
     * @return UserBadge The created user_badge record
     */
    protected function awardBadge(User $user, Badge $badge): UserBadge
    {
        $userBadge = UserBadge::create([
            'user_id' => $user->id,
            'badge_id' => $badge->id,
            'notes' => "Automatically awarded based on user activity",
            'is_displayed' => true,
            'awarded_at' => now(),
        ]);

        \Log::info("Badge {$badge->name} awarded to user {$user->id}");

        // Notify the user
        $user->notify(new BadgeEarned($badge));

        return $userBadge;
    }
}
