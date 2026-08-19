<?php

namespace Laracroft\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class CamelCaseBuilder extends Builder
{
    /**
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function orderBy($column, $direction = 'asc') : static
    {
        if ( is_string($column) ) {
            $column = $this->normalizeColumn($column);
        }

        return parent::orderBy($column, $direction);
    }

    /**
     * @inheritdoc
     */
    public function orderByDesc($column) : static
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * @inheritdoc
     */
    public function whereAny(array $columns, $operator = null, $value = null, $boolean = 'and') : static
    {
        $columns = array_map(fn($c) => $this->normalizeColumn($c), $columns);

        return parent::whereAny($columns, $operator, $value, $boolean);
    }

    /**
     * @inheritdoc
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false) : static
    {
        return parent::whereIn($this->normalizeColumn($column), $values, $boolean, $not);
    }

    /**
     * @inheritdoc
     */
    public function whereNotIn($column, $values, $boolean = 'and') : static
    {
        return parent::whereNotIn($this->normalizeColumn($column), $values, $boolean);
    }

    /**
     * @inheritdoc
     */
    public function whereNull($columns, $boolean = 'and', $not = false) : static
    {
        $columns = is_array($columns)
            ? array_map(fn($c) => $this->normalizeColumn($c), $columns)
            : $this->normalizeColumn($columns);

        return parent::whereNull($columns, $boolean, $not);
    }

    /**
     * @inheritdoc
     */
    public function whereNotNull($columns, $boolean = 'and') : static
    {
        $columns = is_array($columns)
            ? array_map(fn($c) => $this->normalizeColumn($c), $columns)
            : $this->normalizeColumn($columns);

        return parent::whereNotNull($columns, $boolean);
    }

    /**
     * @inheritdoc
     */
    public function select($columns = ['*']) : static
    {
        $columns = is_array($columns) ? $columns : func_get_args();
        $columns = array_map(fn($c) => $this->normalizeColumn($c), $columns);

        return parent::select($columns);
    }

    /**
     * @inheritdoc
     */
    public function addSelect($column) : static
    {
        $columns = is_array($column) ? $column : func_get_args();
        $columns = array_map(fn($c) => $this->normalizeColumn($c), $columns);

        return parent::addSelect($columns);
    }

    /**
     * @inheritdoc
     */
    public function pluck($column, $key = null) : Collection
    {
        return parent::pluck($this->normalizeColumn($column), $key !== null ? $this->normalizeColumn($key) : $key);
    }

    /**
     * @inheritdoc
     */
    public function update(array $values) : int
    {
        return parent::update($this->normalizeArrayKeys($values));
    }
}
