<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\User;

class BadgeService
{
    public function checkBadge(User $user, Badge $badge, array $data): bool
    {
        // check if the mentor has already the badge
        if($user->badges()->where('badge_id', $badge->id)->exists()) {
            return false;
        }

        // check badge rules
        if(!$this->checkBadgeRules($user, $badge, $data)) {
            return false;
        }
        return true;
    }

    protected function checkBadgeRules(User $user, Badge $badge, array $data): bool
    {
        $rules = $badge->rules;

        foreach($rules as $rule) {
            $actualValue = $this->getRuleValue($user, $rule, $data);
            if(!$rule->isMet($actualValue)) {
                return false;
            }
        }
        return true;
    }

    public function getRuleValue(User $user, $rule, array $data)
    {
        $key = $rule->requirement_key;

        if (array_key_exists($key, $data)) {
            return $data[$key];
        }
        return null;
    }


}
