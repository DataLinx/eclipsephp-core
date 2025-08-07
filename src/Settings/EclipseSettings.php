<?php

namespace Eclipse\Core\Settings;

use Spatie\LaravelSettings\Settings;

class EclipseSettings extends Settings
{
    public bool $email_verification = false;

    public bool $address_book = false;

    public static function group(): string
    {
        return 'eclipse';
    }
}
