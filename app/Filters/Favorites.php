<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder,
    Spatie\QueryBuilder\Filters\Filter;

class Favorites implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        return $query->where('favorite', '=', true);
    }
}
