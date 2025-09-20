<?php

namespace Eclipse\Core\Filament\Pages;

use Eclipse\Core\Settings\UserSettings;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ManageUserSettings extends SettingsPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = UserSettings::class;

    protected static bool $shouldRegisterNavigation = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Email settings')
                    ->schema([
                        TextInput::make('outgoing_email_address')
                            ->email()
                            ->label('Outgoing email address'),
                        RichEditor::make('outgoing_email_signature')
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
