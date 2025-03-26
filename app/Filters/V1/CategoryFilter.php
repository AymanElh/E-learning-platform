<?php

namespace App\Filters\V1;

/**
 *
 */
class CategoryFilter
{
    protected array $safeParams = [
        'name' => ['gte'],
        'description' => ['gte']
    ];

    protected array $columnMap = [];
    protected array $operatorMap = [
        'gte' => 'Like',
        'gt' => '='
    ];

    /**
     * Method to transform the params into conditions for filtering
     *
     * @param array $filters
     * @return array
     */
    public function transform (array $filters): array
    {
        $eloQuery = [];
        foreach($this->safeParams as $param => $operators) {
//            $query = $request->query($param);
            if(!isset($filters[$param])) {
                continue;
            }
            $column = $this->columnMap[$param] ?? $param;

            foreach($operators as $operator) {
                if(isset($filters[$param][$operator])) {
                    $eloQuery[] = [$column, $this->operatorMap[$operator], $filters[$param][$operator]];
                }
            }
        }
//        dd($eloQuery);
        return $eloQuery;
    }
}
