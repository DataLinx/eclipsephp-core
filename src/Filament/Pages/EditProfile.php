<?php

namespace Eclipse\Core\Filament\Pages;

use Eclipse\Core\Filament\Resources\UserResource;
use Filament\Schemas\Schema;

class EditProfile extends \Filament\Auth\Pages\EditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                UserResource::getFirstNameFormComponent(),
                UserResource::getLastNameFormComponent(),
                UserResource::getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
