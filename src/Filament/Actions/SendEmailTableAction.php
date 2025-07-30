<?php

namespace Eclipse\Core\Filament\Actions;

use Eclipse\Core\Models\User;
use Filament\Tables\Actions\Action;

class SendEmailTableAction extends SendEmailAction
{
    public static function makeAction(): Action
    {
        return static::configureEmailAction(
            Action::make('sendEmail')
                ->authorize(fn () => auth()->user()->can('sendEmail', User::class))
                ->visible(fn ($record) => ! $record->trashed())
        );
    }
}
