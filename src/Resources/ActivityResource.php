<?php

namespace Laracroft\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Laracroft\Helpers\Sanitizer;

class ActivityResource extends JsonResource
{
    public function toArray($request) : array
    {
        $data = [
            'id'        => $this->id,
            'action'    => $this->description,
            'timestamp' => Sanitizer::date($this->created_at, 'd/m/Y H:i'),
        ];

        if ( $this->hasUserData() ) {
            $data['user'] = [
                'id'    => $this->causer->id,
                'name'  => $this->causer->name,
                'email' => $this->causer->email,
                'role'  => method_exists($this->causer, 'getRoleNames')
                    ? $this->causer->getRoleNames()->first() ?? 'undefined'
                    : 'undefined',
            ];
        }

        return $data;
    }
}