<?php

namespace Laracroft\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class Collection extends ResourceCollection
{
    public function toArray(Request $request) : array
    {
        // Check if the resource has pagination methods
        $hasPagination = method_exists($this->resource, 'currentPage');

        $result = [
            'items' => $this->collection,
        ];

        // Only add pagination if the resource supports it
        if ( $hasPagination ) {
            $result['pagination'] = [
                'currentPage' => $this->currentPage(),
                'lastPage'    => $this->lastPage(),
                'perPage'     => $this->perPage(),
                'total'       => $this->total(),
                'from'        => $this->firstItem(),
                'to'          => $this->lastItem()
            ];

        } else {
            // Fallback pagination for non-paginated collections
            $itemCount = is_countable($this->collection) ? count($this->collection) : 0;
            $result['pagination'] = [
                'currentPage' => 1,
                'lastPage'    => 1,
                'perPage'     => $itemCount,
                'total'       => $itemCount,
                'from'        => $itemCount > 0 ? 1 : null,
                'to'          => $itemCount
            ];
        }

        return $result;
    }
}
