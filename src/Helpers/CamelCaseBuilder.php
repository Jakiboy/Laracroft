<?php

namespace Laracroft\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Custom Eloquent builder converts camelCase column names to snake.
 */
class CamelCaseBuilder extends Builder
{
    /**
     * Convert a plain-string column name.
     */
    protected function normalizeColumn(mixed $column) : mixed
    {
        if ( !is_string($column) ) {
            return $column;
        }

        // Keep aliased/raw expressions intact (e.g. "table.col as pivot_col").
        if ( preg_match('/\s+as\s+/i', $column) || str_contains($column, ' ') ) {
            return $column;
        }

        if ( str_contains($column, '.') ) {
            [$prefix, $col] = explode('.', $column, 2);
            return $prefix . '.' . Str::snake($col);
        }

        return Str::snake($column);
    }

    /**
     * Convert every key in the given array from camelCase to snake_case.
     * Already-snake_case keys are left unchanged (Str::snake is idempotent).
     */
    protected function normalizeArrayKeys(array $columns) : array
    {
        $result = [];

        foreach ($columns as $key => $value) {
            $result[is_string($key) ? $this->normalizeColumn($key) : $key] = $value;
        }

        return $result;
    }

    /**
     * Override where query.
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and') : static
    {
        if ( is_string($column) ) {
            $column = $this->normalizeColumn($column);
        } elseif ( is_array($column) ) {
            $column = $this->normalizeArrayKeys($column);
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    /**
     * Override orWhere query.
     */
    public function orWhere($column, $operator = null, $value = null) : static
    {
        if ( is_string($column) ) {
            $column = $this->normalizeColumn($column);
        } elseif ( is_array($column) ) {
            $column = $this->normalizeArrayKeys($column);
        }

        return parent::orWhere($column, $operator, $value);
    }

    /**
     * Override orderBy query.
     */
    public function orderBy($column, $direction = 'asc') : static
    {
        if ( is_string($column) ) {
            $column = $this->normalizeColumn($column);
        }

        return parent::orderBy($column, $direction);
    }

    /**
     * Override orderByDesc query.
     */
    public function orderByDesc($column) : static
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Override whereAny query.
     */
    public function whereAny(array $columns, $operator = null, $value = null, $boolean = 'and') : static
    {
        $columns = array_map(fn($c) => $this->normalizeColumn($c), $columns);

        return parent::whereAny($columns, $operator, $value, $boolean);
    }

    /**
     * Override whereIn query.
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false) : static
    {
        return parent::whereIn($this->normalizeColumn($column), $values, $boolean, $not);
    }

    /**
     * Override whereNotIn query.
     */
    public function whereNotIn($column, $values, $boolean = 'and') : static
    {
        return parent::whereNotIn($this->normalizeColumn($column), $values, $boolean);
    }

    /**
     * Override whereNull query.
     */
    public function whereNull($columns, $boolean = 'and', $not = false) : static
    {
        $columns = is_array($columns)
            ? array_map(fn($c) => $this->normalizeColumn($c), $columns)
            : $this->normalizeColumn($columns);

        return parent::whereNull($columns, $boolean, $not);
    }

    /**
     * Override whereNotNull query.
     */
    public function whereNotNull($columns, $boolean = 'and') : static
    {
        $columns = is_array($columns)
            ? array_map(fn($c) => $this->normalizeColumn($c), $columns)
            : $this->normalizeColumn($columns);

        return parent::whereNotNull($columns, $boolean);
    }

    /**
     * Override select query.
     */
    public function select($columns = ['*']) : static
    {
        $columns = is_array($columns) ? $columns : func_get_args();
        $columns = array_map(fn($c) => $this->normalizeColumn($c), $columns);

        return parent::select($columns);
    }

    /**
     * Override addSelect query.
     */
    public function addSelect($column) : static
    {
        $columns = is_array($column) ? $column : func_get_args();
        $columns = array_map(fn($c) => $this->normalizeColumn($c), $columns);

        return parent::addSelect($columns);
    }

    /**
     * Override pluck query.
     */
    public function pluck($column, $key = null) : \Illuminate\Support\Collection
    {
        return parent::pluck($this->normalizeColumn($column), $key !== null ? $this->normalizeColumn($key) : $key);
    }

    /**
     * Override update query.
     */
    public function update(array $values) : int
    {
        return parent::update($this->normalizeArrayKeys($values));
    }
}
