<?php

namespace Eclipse\Core\Filament\Resources\LocaleResource\Pages;

use Eclipse\Core\Filament\Resources\LocaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditLocale extends EditRecord
{
    protected static string $resource = LocaleResource::class;

    public function getHeading(): string|Htmlable
    {
        return __('eclipse::locale.actions.edit.heading');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->modalHeading(__('eclipse::locale.actions.delete.heading')),
        ];
    }
}
