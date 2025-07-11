<?php

namespace Eclipse\Core\Filament\Traits;

use Eclipse\Core\Mail\SendEmailToUser;
use Eclipse\Core\Models\User;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

trait SendEmailActionTrait
{
    protected static function getEmailFormSchema(): array
    {
        return [
            Section::make(__('eclipse::email.email_form_title'))
                ->description(__('eclipse::email.email_form_description'))
                ->schema([
                    Grid::make()
                        ->schema([
                            TextInput::make('sender_email')
                                ->label(__('eclipse::email.sender'))
                                ->default(fn () => auth()->user()->email)
                                ->disabled()
                                ->dehydrated(false)
                                ->helperText(__('eclipse::email.your_email')),

                            TextInput::make('recipient_email')
                                ->label(__('eclipse::email.recipient'))
                                ->default(fn ($record = null) => $record?->email)
                                ->disabled()
                                ->dehydrated(false)
                                ->helperText(__('eclipse::email.recipient_email')),
                        ])
                        ->columns(2),

                    Grid::make()
                        ->schema([
                            TagsInput::make('cc')
                                ->label(__('eclipse::email.cc'))
                                ->helperText(__('eclipse::email.cc_help'))
                                ->placeholder('email1@example.com')
                                ->splitKeys(['Enter', 'Tab', ' ', ','])
                                ->nestedRecursiveRules(['email']),

                            TagsInput::make('bcc')
                                ->label(__('eclipse::email.bcc'))
                                ->helperText(__('eclipse::email.bcc_help'))
                                ->placeholder('email1@example.com')
                                ->splitKeys(['Enter', 'Tab', ' ', ','])
                                ->nestedRecursiveRules(['email']),
                        ])
                        ->columns(2),

                    TextInput::make('subject')
                        ->label(__('eclipse::email.subject'))
                        ->required()
                        ->maxLength(255)
                        ->placeholder(__('eclipse::email.subject_placeholder')),

                    Textarea::make('message')
                        ->label(__('eclipse::email.message'))
                        ->required()
                        ->rows(8)
                        ->placeholder(__('eclipse::email.message_placeholder')),

                    Hidden::make('recipient_id')
                        ->default(fn ($record = null) => $record?->id),
                ])
                ->columns(1),
        ];
    }

    protected static function getEmailActionClosure(): \Closure
    {
        return function (array $data) {
            try {
                Validator::make($data, [
                    'cc' => ['nullable', 'array'],
                    'cc.*' => ['email'],
                    'bcc' => ['nullable', 'array'],
                    'bcc.*' => ['email'],
                ], [
                    'cc.*.email' => __('eclipse::email.invalid_cc_email'),
                    'bcc.*.email' => __('eclipse::email.invalid_bcc_email'),
                ])->validate();

                $recipient = User::findOrFail($data['recipient_id']);

                $ccEmails = ! empty($data['cc']) ? implode(',', $data['cc']) : null;
                $bccEmails = ! empty($data['bcc']) ? implode(',', $data['bcc']) : null;

                Mail::queue(new SendEmailToUser(
                    $recipient,
                    $data['subject'],
                    $data['message'],
                    $ccEmails,
                    $bccEmails,
                    auth()->user()
                ));

                Notification::make()
                    ->title(__('eclipse::email.email_queued'))
                    ->body(__('eclipse::email.email_queued_to', ['email' => $recipient->email]))
                    ->success()
                    ->sendToDatabase(auth()->user())
                    ->broadcast([auth()->user()]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                $errors = collect($e->errors())->flatten()->implode(' ');

                Notification::make()
                    ->title(__('eclipse::email.error'))
                    ->body(__('eclipse::email.send_error_message', ['error' => $errors]))
                    ->danger()
                    ->sendToDatabase(auth()->user())
                    ->broadcast([auth()->user()]);
            } catch (\Exception $e) {
                Notification::make()
                    ->title(__('eclipse::email.error'))
                    ->body(__('eclipse::email.send_error_message', ['error' => $e->getMessage()]))
                    ->danger()
                    ->sendToDatabase(auth()->user())
                    ->broadcast([auth()->user()]);
            }
        };
    }

    protected static function configureEmailAction($action)
    {
        return $action
            ->label(fn () => __('eclipse::email.send_email'))
            ->icon('heroicon-o-envelope')
            ->form(static::getEmailFormSchema())
            ->action(static::getEmailActionClosure())
            ->modalHeading(fn ($record) => __('eclipse::email.send_email_to', ['name' => $record->name]))
            ->modalSubmitActionLabel(__('eclipse::email.send'))
            ->modalCancelActionLabel(__('eclipse::email.cancel'))
            ->modalWidth('2xl')
            ->modalFooterActionsAlignment('end');
    }
}
