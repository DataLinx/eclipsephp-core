<?php

namespace Eclipse\Core\Filament\Pages\Tools;

class HealthCheckResults extends \ShuvroRoy\FilamentSpatieLaravelHealth\Pages\HealthCheckResults
{
    public static function getNavigationGroup(): ?string
    {
        return 'Tools';
    }
}
