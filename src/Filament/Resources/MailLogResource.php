<?php

namespace Eclipse\Core\Filament\Resources;

use Eclipse\Core\Models\MailLog;
use Eclipse\Core\Services\Registry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class MailLogResource extends Resource
{
    protected static ?string $model = MailLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('eclipse::email.outbox');
    }

    public static function getModelLabel(): string
    {
        return __('eclipse::email.email_log');
    }

    public static function getPluralModelLabel(): string
    {
        return __('eclipse::email.outbox');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('eclipse::email.email_details'))
                ->schema([
                    Forms\Components\TextInput::make('from')
                        ->label(__('eclipse::email.from'))
                        ->disabled(),
                    Forms\Components\TextInput::make('to')
                        ->label(__('eclipse::email.to'))
                        ->disabled(),
                    Forms\Components\TextInput::make('cc')
                        ->label(__('eclipse::email.cc'))
                        ->disabled(),
                    Forms\Components\TextInput::make('bcc')
                        ->label(__('eclipse::email.bcc'))
                        ->disabled(),
                    Forms\Components\TextInput::make('subject')
                        ->label(__('eclipse::email.subject'))
                        ->disabled(),
                    Forms\Components\Textarea::make('body')
                        ->label(__('eclipse::email.email_body'))
                        ->rows(10)
                        ->disabled(),
                ])
                ->columns(2),

            Forms\Components\Section::make(__('eclipse::email.metadata'))
                ->schema([
                    Forms\Components\TextInput::make('status')
                        ->label(__('eclipse::email.status'))
                        ->disabled(),
                    Forms\Components\DateTimePicker::make('sent_at')
                        ->label(__('eclipse::email.sent_at'))
                        ->disabled(),
                    Forms\Components\TextInput::make('message_id')
                        ->label(__('eclipse::email.message_id'))
                        ->disabled(),
                ])
                ->columns(3),

            Forms\Components\Section::make(__('eclipse::email.headers'))
                ->schema([
                    Forms\Components\KeyValue::make('headers')
                        ->label('')
                        ->disabled(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('message_id')
                    ->label(__('eclipse::email.message_id'))
                    ->limit(20)
                    ->tooltip(fn (MailLog $record): ?string => $record->message_id)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label(__('eclipse::email.sent_at'))
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('from')
                    ->label(__('eclipse::email.from'))
                    ->limit(30)
                    ->tooltip(fn (MailLog $record): ?string => $record->from),

                Tables\Columns\TextColumn::make('to')
                    ->label(__('eclipse::email.to'))
                    ->limit(30)
                    ->tooltip(fn (MailLog $record): ?string => $record->to),

                Tables\Columns\TextColumn::make('cc')
                    ->label(__('eclipse::email.cc'))
                    ->limit(30)
                    ->tooltip(fn (MailLog $record): ?string => $record->cc)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('bcc')
                    ->label(__('eclipse::email.bcc'))
                    ->limit(30)
                    ->tooltip(fn (MailLog $record): ?string => $record->bcc)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('subject')
                    ->label(__('eclipse::email.subject'))
                    ->limit(50)
                    ->tooltip(fn (MailLog $record): ?string => $record->subject),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('eclipse::email.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sent' => 'success',
                        'sending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('sender.name')
                    ->label('Sender')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('recipient.name')
                    ->label('Recipient')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('opened')
                    ->label('Opened')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('delivered')
                    ->label('Delivered')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('complaint')
                    ->label('Complaint')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('bounced')
                    ->label('Bounced')
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-uturn-left')
                    ->falseIcon('heroicon-o-check')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('site.name')
                    ->label('Site')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('eclipse::email.status'))
                    ->options([
                        'sent' => __('eclipse::email.sent'),
                        'sending' => __('eclipse::email.sending'),
                        'failed' => __('eclipse::email.failed'),
                    ]),

                Tables\Filters\Filter::make('sent_today')
                    ->label(__('eclipse::email.sent_today'))
                    ->query(fn (Builder $query): Builder => $query->whereDate('sent_at', today())),

                Tables\Filters\Filter::make('sent_this_week')
                    ->label(__('eclipse::email.sent_this_week'))
                    ->query(fn (Builder $query): Builder => $query->whereBetween('sent_at', [now()->startOfWeek(), now()->endOfWeek()])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(__('eclipse::email.view_email'))
                    ->modalHeading(fn (MailLog $record): string => __('eclipse::email.view_email').': '.$record->subject)
                    ->modalWidth('7xl')
                    ->form([
                        Forms\Components\Placeholder::make('email_preview')
                            ->label('')
                            ->content(fn (MailLog $record) => static::buildEmailPreview($record))
                            ->columnSpanFull(),
                    ]),
            ])
            ->defaultSort('sent_at', 'desc')
            ->poll('30s')
            ->emptyStateHeading(__('eclipse::email.no_emails'))
            ->emptyStateDescription(__('eclipse::email.outbox_description'))
            ->emptyStateIcon('heroicon-o-envelope');
    }

    protected static function buildEmailPreview(MailLog $record): HtmlString
    {
        $subject = htmlspecialchars($record->subject ?? '', ENT_QUOTES, 'UTF-8');
        $body = htmlspecialchars($record->body ?? '', ENT_QUOTES, 'UTF-8');
        $showPreviewText = __('eclipse::email.show_preview');
        $showHtmlText = __('eclipse::email.show_html');

        return new HtmlString("
            <div x-data=\"{ showHtml: false }\" class=\"space-y-4\">
                <div class=\"flex justify-between items-center\">
                    <span class=\"text-lg font-semibold text-gray-900 dark:text-gray-100\">{$subject}</span>
                    <button type=\"button\" @click=\"showHtml = !showHtml\" class=\"text-sm px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-md transition-colors\">
                        <span x-text=\"showHtml ? '{$showPreviewText}' : '{$showHtmlText}'\"></span>
                    </button>
                </div>
                
                <div x-show=\"!showHtml\" class=\"border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden\">
                    <div class=\"bg-white dark:bg-gray-900\">
                        <iframe srcdoc=\"{$body}\" class=\"w-full border-0 rounded\" style=\"min-height: 500px;\" onload=\"this.style.height = Math.max(500, this.contentWindow.document.body.scrollHeight + 40) + 'px';\"></iframe>
                    </div>
                </div>
                
                <div x-show=\"showHtml\" class=\"border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden\">
                    <div class=\"bg-gray-900 p-4 overflow-x-auto\">
                        <pre class=\"text-sm text-green-400 whitespace-pre-wrap font-mono max-h-96 overflow-y-auto\"><code>{$body}</code></pre>
                    </div>
                </div>
            </div>
        ");
    }

    public static function getPages(): array
    {
        return [
            'index' => MailLogResource\Pages\ListMailLogs::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->latest('created_at');

        if ($currentSite = Registry::getSite()) {
            $query->where('site_id', $currentSite->id);
        }

        return $query;
    }
}
