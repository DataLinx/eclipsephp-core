<?php

use DataLinx\PhpUtils\Fluent\FluentString;

if (! function_exists('fstr')) {
    /**
     * Create a new FluentString object
     */
    function fstr(string $value): FluentString
    {
        return new FluentString($value);
    }
}
