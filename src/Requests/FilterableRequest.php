<?php

namespace Laracroft\Requests;

use Laracroft\Objects\FilterObject;

abstract class FilterableRequest extends Authentication
{
    /**
     * @inheritdoc
     */
    protected array $allowed = [];

    /**
     * @inheritdoc
     */
    protected array $allowedFilters = [];

    /**
     * @inheritdoc
     */
    protected const string DEFAULT_SORT_FIELD = 'id';

    /**
     * @inheritdoc
     */
    abstract protected function filterObjectClass() : string;

    /**
     * @inheritdoc
     */
    public function fromArray() : FilterObject
    {
        return $this->filterObjectClass()::fromArray(
            $this->query(),
            $this->allowed,
            $this->allowedFilters,
            static::DEFAULT_SORT_FIELD,
        );
    }

    /**
     * @inheritdoc
     */
    protected function prepareForValidation() : void
    {
        $this->merge([
            'items' => $this->filled('items') ? (int)$this->input('items') : 10,
            'page'  => $this->filled('page') ? (int)$this->input('page') : 1,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules() : array
    {
        return [
            'page'   => ['sometimes', 'integer', 'min:1'],
            'items'  => ['sometimes', 'integer', 'min:1', 'max:500'],
            'search' => ['sometimes', 'string', 'max:100'],
            'sField' => ['sometimes', 'string', 'in:' . implode(',', $this->allowed)],
            'sDir'   => ['sometimes', 'string', 'in:asc,desc'],
        ];
    }
}
