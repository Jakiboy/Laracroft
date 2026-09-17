<?php

namespace Laracroft\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;

class Activity extends SpatieActivity
{
    /**
     * @inheritdoc
     */
    public function hasUserData() : bool
    {
        return $this->causer !== null;
    }
}