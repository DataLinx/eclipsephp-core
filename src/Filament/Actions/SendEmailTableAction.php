<?php

namespace Eclipse\Core\Filament\Actions;

use Eclipse\Core\Models\User;
use Filament\Actions\Action;
use Filament\Tables\Actions\Action as TableAction;

class SendEmailTableAction extends SendEmailAction
{
    public static function make(): Action
    {
        return static::configureEmailAction(
            TableAction::make('sendEmail')
                ->authorize(fn () => auth()->user()->can('sendEmail', User::class))
                ->visible(fn ($record) => ! $record->trashed())
        );
    }
}
