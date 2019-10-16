<?php

namespace Framework\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as BaseTrimmer;

class TrimStrings extends BaseTrimmer
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array
     */
    protected $except = [
        'password',
        'password_confirmation',
    ];
    protected function transform($key, $value)
    {
        if( in_array( $key, $this->except, true ) ){
            return $value;
        }
        return is_string( $value ) ? htmlspecialchars(trim($value)) : $value;
    }
}
