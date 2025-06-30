<?php

namespace Eclipse\Core\Settings;

use Eclipse\Core\Foundation\Settings\IsUserSiteScoped;
use Spatie\LaravelSettings\Settings;

class UserSettings extends Settings
{
    use IsUserSiteScoped;

    public string $outgoing_email_address;

    public string $outgoing_email_signature;

    public static function group(): string
    {
        return 'site';
    }
}
