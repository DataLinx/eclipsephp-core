<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('eclipse.email_verification', false);
        $this->migrator->add('eclipse.address_book', false);
    }
};
