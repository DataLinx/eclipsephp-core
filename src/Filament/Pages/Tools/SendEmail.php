<?php

namespace Eclipse\Core\Filament\Pages\Tools;

use Eclipse\Core\Mail\SendRawEmail;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Mail;

/**
 * @property-read Schema $form
 */
class SendEmail extends Page
{
    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-paper-airplane';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'tools/send-email';

    public static function getNavigationLabel(): string
    {
        return __('eclipse::email.send_email');
    }

    public function getTitle(): string
    {
        return __('eclipse::email.send_email');
    }

    public function mount(): void
    {
        $this->form->fill([
            'from' => static::getFromAddress(),
        ]);
    }

    public static function getFromAddress(): ?string
    {
        return config('mail.from.address');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('from')
                    ->label(__('eclipse::email.from'))
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('to')
                    ->label(__('eclipse::email.to'))
                    ->placeholder('one@domain.com, two@domain.com')
                    ->required()
                    ->rules([
                        fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                            foreach (static::parseEmails((string) $value) as $email) {
                                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                    $fail(__('validation.email', ['attribute' => __('eclipse::email.to')]));

                                    return;
                                }
                            }
                        },
                    ]),
                TextInput::make('subject')
                    ->label(__('eclipse::email.subject'))
                    ->required(),
                RichEditor::make('message')
                    ->label(__('eclipse::email.message'))
                    ->required()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('send'),
        ]);
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label(__('eclipse::email.send'))
                ->icon('heroicon-o-check-circle')
                ->action('send'),
        ];
    }

    public function send(): void
    {
        $data = $this->form->getState();

        try {
            Mail::to(static::parseEmails($data['to']))
                ->send(new SendRawEmail($data['subject'], $data['message']));
        } catch (\Throwable $e) {
            Notification::make()
                ->title(__('eclipse::email.error'))
                ->body(__('eclipse::email.send_error_message', ['error' => $e->getMessage()]))
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title(__('eclipse::email.email_sent'))
            ->body(__('eclipse::email.email_sent_to', ['email' => implode(', ', static::parseEmails($data['to']))]))
            ->success()
            ->send();

        $this->form->fill([
            'from' => static::getFromAddress(),
        ]);
    }

    /**
     * @return array<int, string>
     */
    public static function parseEmails(string $emails): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[,;]/', $emails))));
    }
}
