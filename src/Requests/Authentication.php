<?php

namespace Laracroft\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Authentication extends FormRequest
{
    /**
     * @inheritdoc
     */
    public function authorize() : bool
    {
        return true;
    }
}
