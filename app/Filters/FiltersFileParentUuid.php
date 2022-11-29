<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class FiltersFileParentUuid implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        return $query->when(empty($value), function ($q) {
            $q->whereDoesntHave('parent');
        })->when(empty($value), function ($q) use ($value) {
            $q->whereHas('parent', function ($query) use ($value) {
                $query->where('uuid', $value);
            });
        });
    }
}
