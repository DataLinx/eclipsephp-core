@php
    use Eclipse\Core\Services\Registry;
    use Filament\Facades\Filament;

    $appName = Registry::getSite()->name ?? config('app.name');
    $hasSpaMode = Filament::getCurrentPanel()->hasSpaMode();
    
    $dashboardUrl = '/' . trim(Filament::getCurrentPanel()->getPath(), '/');
@endphp

<a @if ($hasSpaMode) wire:navigate @endif href="{{ $dashboardUrl }}">
    <div class="fi-logo flex text-xl font-bold leading-5 tracking-tight text-gray-950 dark:text-white">
        {{ $appName }}
    </div>
</a>