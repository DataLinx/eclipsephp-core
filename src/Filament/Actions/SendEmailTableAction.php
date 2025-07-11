<?php

namespace Eclipse\Core\Filament\Actions;

use Eclipse\Core\Filament\Traits\SendEmailActionTrait;
use Eclipse\Core\Models\User;
use Filament\Tables\Actions\Action;

class SendEmailTableAction
{
    use SendEmailActionTrait;

    public static function make(): Action
    {
        return static::configureEmailAction(
            Action::make('sendEmail')
                ->authorize(fn () => auth()->user()->can('sendEmail', User::class))
                ->visible(fn ($record) => ! $record->trashed())
        );
    }
}
