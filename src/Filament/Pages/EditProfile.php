<?php

namespace Eclipse\Core\Filament\Pages;

use Eclipse\Core\Filament\Resources\UserResource;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                UserResource::getFirstNameFormComponent(),
                UserResource::getLastNameFormComponent(),
                UserResource::getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
