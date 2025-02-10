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

if (! function_exists('template_path')) {
    /**
     * Generate the full path to a frontend template file (view or asset).
     *
     * @param string $path Relative path within the template directory.
     * @param string|null $template Optional template name to be used instead of the config/default.
     * @return string The constructed template path.
     */
    function template_path(string $path = '', ?string $template = null): string
    {
        if (empty($template)) {
            // Use template from config, if set
            $template = config('frontend.template') ?: 'default';
        }

        return resource_path('frontend/' . $template . ($path ? "/$path" : ''));
    }
}
