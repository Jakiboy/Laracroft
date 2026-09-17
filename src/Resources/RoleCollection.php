<?php

namespace Laracroft\Resources;

class RoleCollection extends Collection
{
    /**
     * @inheritdoc
     */
    public $collects = RoleResource::class;
    protected $withPagination = false;

    /**
     * @inheritdoc
     */
    public function toArray($request) : array
    {
        $array = parent::toArray($request);

        if ( isset($array['items']) ) {
            $array['items'] = collect($array['items'])->filter()->values()->all();
        }

        return $array;
    }
}
