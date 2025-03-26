<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 */
class BadgeRule extends Model
{
    protected $fillable = [
        'badge_id',
        'requirement_type',
        'requirement_key',
        'operator',
        'value'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function badge(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    /**
     * @param $actualValue
     * @return bool
     */
    public function isMet($actualValue) : bool
    {
        $operator = $this->operator;

        switch ($operator) {
            case '=':
                return $actualValue === $this->value;
            case '<':
                return $actualValue < $this->value;
            case '>':
                return $actualValue > $this->value;
            case '>=':
                return $actualValue >= $this->value;
            case '<=':
                return $actualValue <= $this->value;
            default:
                return false;
        }
    }
}
