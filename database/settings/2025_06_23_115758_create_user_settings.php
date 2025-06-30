<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->repository('user_tenant');

        $this->migrator->add('site.outgoing_email_address', '');
        $this->migrator->add('site.outgoing_email_signature', '');
    }
};
