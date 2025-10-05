<?php

namespace Eclipse\Core\Filament\Resources;

use Eclipse\Core\Filament\Resources\MailLogResource\Pages\ListMailLogs;
use Eclipse\Core\Models\MailLog;
use Eclipse\Core\Services\Registry;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class MailLogResource extends Resource
{
    protected static ?string $model = MailLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

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

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('eclipse::email.email_details'))
                ->schema([
                    TextInput::make('from')
                        ->label(__('eclipse::email.from'))
                        ->disabled(),
                    TextInput::make('to')
                        ->label(__('eclipse::email.to'))
                        ->disabled(),
                    TextInput::make('cc')
                        ->label(__('eclipse::email.cc'))
                        ->disabled(),
                    TextInput::make('bcc')
                        ->label(__('eclipse::email.bcc'))
                        ->disabled(),
                    TextInput::make('subject')
                        ->label(__('eclipse::email.subject'))
                        ->disabled(),
                    Textarea::make('body')
                        ->label(__('eclipse::email.email_body'))
                        ->rows(10)
                        ->disabled(),
                ])
                ->columns(2),

            Section::make(__('eclipse::email.metadata'))
                ->schema([
                    TextInput::make('status')
                        ->label(__('eclipse::email.status'))
                        ->disabled(),
                    DateTimePicker::make('sent_at')
                        ->label(__('eclipse::email.sent_at'))
                        ->disabled(),
                    TextInput::make('message_id')
                        ->label(__('eclipse::email.message_id'))
                        ->disabled(),
                ])
                ->columns(3),

            Section::make(__('eclipse::email.headers'))
                ->schema([
                    KeyValue::make('headers')
                        ->label('')
                        ->disabled(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('message_id')
                    ->label(__('eclipse::email.message_id'))
                    ->limit(20)
                    ->tooltip(fn (MailLog $record): ?string => $record->message_id)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sent_at')
                    ->label(__('eclipse::email.sent_at'))
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('from')
                    ->label(__('eclipse::email.from'))
                    ->limit(30)
                    ->tooltip(fn (MailLog $record): ?string => $record->from),

                TextColumn::make('to')
                    ->label(__('eclipse::email.to'))
                    ->limit(30)
                    ->tooltip(fn (MailLog $record): ?string => $record->to),

                TextColumn::make('cc')
                    ->label(__('eclipse::email.cc'))
                    ->limit(30)
                    ->tooltip(fn (MailLog $record): ?string => $record->cc)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('bcc')
                    ->label(__('eclipse::email.bcc'))
                    ->limit(30)
                    ->tooltip(fn (MailLog $record): ?string => $record->bcc)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('subject')
                    ->label(__('eclipse::email.subject'))
                    ->limit(50)
                    ->tooltip(fn (MailLog $record): ?string => $record->subject),

                TextColumn::make('status')
                    ->label(__('eclipse::email.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sent' => 'success',
                        'sending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('sender.name')
                    ->label('Sender')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('recipient.name')
                    ->label('Recipient')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('opened')
                    ->label('Opened')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('delivered')
                    ->label('Delivered')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('complaint')
                    ->label('Complaint')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('bounced')
                    ->label('Bounced')
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-uturn-left')
                    ->falseIcon('heroicon-o-check')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('site.name')
                    ->label('Site')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('eclipse::email.status'))
                    ->options([
                        'sent' => __('eclipse::email.sent'),
                        'sending' => __('eclipse::email.sending'),
                        'failed' => __('eclipse::email.failed'),
                    ]),

                Filter::make('sent_today')
                    ->label(__('eclipse::email.sent_today'))
                    ->query(fn (Builder $query): Builder => $query->whereDate('sent_at', today())),

                Filter::make('sent_this_week')
                    ->label(__('eclipse::email.sent_this_week'))
                    ->query(fn (Builder $query): Builder => $query->whereBetween('sent_at', [now()->startOfWeek(), now()->endOfWeek()])),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label(__('eclipse::email.view_email'))
                    ->modalHeading(fn (MailLog $record): string => __('eclipse::email.view_email').': '.$record->subject)
                    ->modalWidth('7xl')
                    ->schema([
                        Placeholder::make('email_preview')
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
            'index' => ListMailLogs::route('/'),
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
