<?php

namespace Eclipse\Core\Filament\Pages;

use Eclipse\Core\Settings\UserSettings;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageUserSettings extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = UserSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Email settings')
                    ->schema([
                        Components\TextInput::make('outgoing_email_address')
                            ->email()
                            ->label('Outgoing email address'),
                        Components\RichEditor::make('outgoing_email_signature')
                            ->label('Outgoing email signature'),
                    ]),
            ]);
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Configuration';
    }

    public static function getNavigationLabel(): string
    {
        return 'My settings';
    }
}
