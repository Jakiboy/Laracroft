<?php

namespace Laracroft\Objects;

class FilterObject
{
    public const string DEFAULT_SORT_FIELD    = 'id';
    public const array ALLOWED_SORT_FIELDS   = [];
    public const array ALLOWED_FILTER_FIELDS = [];
    public const bool ALLOW_FILTER_WILDCARD = true;

    public function __construct(
        public readonly int $items = 5,
        public readonly ?string $search = null,
        public readonly string $sortField = self::DEFAULT_SORT_FIELD,
        public readonly string $sortDir = 'asc',
        public readonly array $filters = [],
    ) {
    }

    public static function fromArray(
        array $query,
        array $allowed = [],
        array $allowedFilters = [],
        string $defaultSortField = self::DEFAULT_SORT_FIELD,
    ) : static {
        $sortField = $query['sField'] ?? $defaultSortField;
        $sortField = in_array($sortField, $allowed, true) ? $sortField : $defaultSortField;

        $sortDir = strtolower($query['sDir'] ?? 'asc');
        $sortDir = in_array($sortDir, ['asc', 'desc'], true) ? $sortDir : 'asc';

        $search = isset($query['search']) ? (trim($query['search']) ?: null) : null;

        $filters = [];
        foreach ($query as $k => $v) {
            if ( str_starts_with($k, 'rField-') && $v !== null && $v !== '' ) {
                $field = substr($k, 7);
                if ( empty($allowedFilters) || in_array($field, $allowedFilters, true) ) {
                    $filters[$field] = $v;
                }
            }
        }

        return new static(
            items: isset($query['items']) && $query['items'] !== '' ? (int)$query['items'] : 5,
            search: $search,
            sortField: $sortField,
            sortDir: $sortDir,
            filters: $filters,
        );
    }
}
