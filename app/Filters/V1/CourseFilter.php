<?php

namespace App\Filters\V1;

class CourseFilter
{
    protected array $safeParams = [
        'category' => ['eq'],
        'tag' => ['eq'],
        'difficulty_level' => ['eq']
    ];

    protected array $columnMap = [
        'category' => 'category_id',
        'tag' => 'tags',
        'difficulty_level' => 'difficulty_level'
    ];
    protected array $operatorMap = [
        'gt' => '='
    ];

    public function transform(array $filter) : array
    {
        $eloQuery = [];

        foreach ($this->safeParams as $param => $operators) {
            if (!isset($filter[$param])) {
                continue;
            }

            $column = $this->columnMap[$param] ?? $param;

            foreach ($operators as $operator) {
                if (isset($filter[$param][$operator])) {
                    if ($operator === 'eq') {
                        if ($param === 'category') {
                            $eloQuery[] = ['category_id', '=', $filter[$param][$operator]];
                        } elseif ($param === 'tag') {
                            $eloQuery[] = function ($queryBuilder) use ($filter, $param, $operator) {
                                $queryBuilder->whereHas('tags', function ($q) use ($filter, $param, $operator) {
                                    $q->where('tags.id', $filter[$param][$operator]);
                                });
                            };
                        } else {
                            // For other fields like difficulty_level, apply directly
                            $eloQuery[] = [$column, '=', $filter[$param][$operator]];
                        }
                    }
                }
            }
        }

        return $eloQuery;
    }
}
