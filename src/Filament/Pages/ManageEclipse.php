<?php

namespace Eclipse\Core\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Eclipse\Common\CommonPlugin;
use Eclipse\Core\Settings\EclipseSettings;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class ManageEclipse extends SettingsPage
{
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = EclipseSettings::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('email_verification')
                    ->label('Enable user email verification'),
                Toggle::make('address_book')
                    ->label('Enable address book'),
            ]);
    }

    public static function getCluster(): ?string
    {
        return app(CommonPlugin::class)->getSettingsCluster();
    }

    public static function getNavigationLabel(): string
    {
        return 'System';
    }

    public function getTitle(): string|Htmlable
    {
        return $this->getNavigationLabel();
    }
}
