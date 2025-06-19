<?php

namespace Eclipse\Core\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum AddressType: string implements HasColor, HasDescription, HasLabel
{
    case DEFAULT_ADDRESS = 'default_address';
    case COMPANY_ADDRESS = 'company_address';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DEFAULT_ADDRESS => 'Default',
            self::COMPANY_ADDRESS => 'Company'
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::DEFAULT_ADDRESS => 'This is the default address',
            self::COMPANY_ADDRESS => 'This is a company address'
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::DEFAULT_ADDRESS => 'primary',
            self::COMPANY_ADDRESS => 'warning'
        };
    }
}
