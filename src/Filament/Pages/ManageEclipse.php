<?php

namespace Eclipse\Core\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Eclipse\Core\Settings\EclipseSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageEclipse extends SettingsPage
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = EclipseSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('email_verification')
                    ->label('Enable user email verification'),
            ]);
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Configuration';
    }

    public static function getNavigationLabel(): string
    {
        return 'System';
    }
}
