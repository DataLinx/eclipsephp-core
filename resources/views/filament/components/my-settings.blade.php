@php
    use Eclipse\Core\Filament\Pages\ManageUserSettings;
    use Filament\Facades\Filament;

    $navigationIcon = ManageUserSettings::getNavigationIcon();
    $navigationLabel = ManageUserSettings::getNavigationLabel();

    $hasSpaMode = Filament::getCurrentPanel()->hasSpaMode();
@endphp

<div class="fi-dropdown-list p-1">
    <a @if ($hasSpaMode) wire:navigate @endif
       href="{{ ManageUserSettings::getUrl(['tenant' => Filament::getTenant() ?? \Eclipse\Core\Models\Site::firstWhere('domain', request()->getHost())]) }}"
       class="fi-dropdown-list-item flex w-full items-center gap-2 whitespace-nowrap rounded-md p-2 text-sm transition-colors duration-75 outline-none disabled:pointer-events-none disabled:opacity-70 hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus-visible:bg-white/5 fi-dropdown-list-item-color-gray fi-color-gray">
        <x-filament::icon class="fi-dropdown-list-item-icon h-5 w-5 text-gray-400 dark:text-gray-500" icon="{{ $navigationIcon }}" />
        {{ __($navigationLabel) }}
    </a>
</div>
