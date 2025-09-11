@php
    use Eclipse\Core\Services\Registry;
    use Filament\Facades\Filament;
    use Illuminate\Database\Eloquent\Model;
    use Eclipse\Core\Filament\Pages\Dashboard;

    $appName = Registry::getSite()->name ?? config('app.name');
    $hasSpaMode = Filament::getCurrentPanel()->hasSpaMode();
    $dashboardUrl = '/' . trim(Filament::getCurrentPanel()->getPath(), '/');

    $currentTenant = filament()->getTenant();
    $currentTenantName = $currentTenant ? filament()->getTenantName($currentTenant) : null;

    $tenants = [];
    $canSwitchTenants = false;

    if (config('eclipse.multi_site', false) && filament()->auth()->check()) {
        $tenants = array_filter(
            filament()->getUserTenants(filament()->auth()->user()),
            fn(Model $tenant): bool => !$tenant->is($currentTenant),
        );
        $canSwitchTenants = count($tenants) > 0;
    }

    $hasFrontend = collect(filament()->getPanels())->has('frontend');
    $frontendUrl = config('app.url');

    if ($hasFrontend) {
        try {
            if ($currentTenant && $currentTenant->domain) {
                $frontendUrl = "https://{$currentTenant->domain}";
            } else {
                $frontendUrl = Dashboard::getUrl();
            }
        } catch (Exception $e) {
            $frontendUrl = config('app.url');
        }
    }
@endphp

@if ($canSwitchTenants || $hasFrontend)
    <div class="flex items-center gap-x-2">
        <a @if ($hasSpaMode) wire:navigate @endif href="{{ $dashboardUrl }}" class="flex-1">
            <div class="fi-logo flex text-xl font-bold leading-5 tracking-tight text-gray-950 dark:text-white">
                {{ $appName }}
                @if ($currentTenant && $currentTenantName !== $appName)
                    <span class="text-gray-500 dark:text-gray-400 text-sm font-normal ml-2">
                        - {{ $currentTenantName }}
                    </span>
                @endif
            </div>
        </a>

        <x-filament::dropdown teleport>
            <x-slot name="trigger">
                <button type="button"
                    class="fi-tenant-menu-trigger group flex items-center justify-center rounded-lg p-1.5 text-sm font-medium outline-none transition duration-75 hover:bg-gray-100 focus-visible:bg-gray-100 dark:hover:bg-white/5 dark:focus-visible:bg-white/5">
                    <x-filament::icon icon="heroicon-m-chevron-down" alias="panels::brand-tenant-menu.toggle-button"
                        class="h-4 w-4 text-gray-400 transition duration-75 group-hover:text-gray-500 group-focus-visible:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-400 dark:group-focus-visible:text-gray-400" />
                </button>
            </x-slot>

            <x-filament::dropdown.list>
                @if ($canSwitchTenants)
                    @foreach ($tenants as $tenant)
                        <x-filament::dropdown.list.item :href="route('filament.admin.pages.dashboard', ['tenant' => $tenant])" :image="filament()->getTenantAvatarUrl($tenant)" tag="a">
                            {{ filament()->getTenantName($tenant) }}
                        </x-filament::dropdown.list.item>
                    @endforeach
                @endif

                @if ($hasFrontend)
                    @if ($canSwitchTenants)
                        <x-filament::dropdown.list.item tag="div"
                            class="border-t border-gray-200 dark:border-gray-700 my-1"></x-filament::dropdown.list.item>
                    @endif

                    <x-filament::dropdown.list.item :href="$frontendUrl" tag="a" target="_blank"
                        class="font-medium">
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-s-globe-alt"
                                class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                            Frontend
                            <x-filament::icon icon="heroicon-s-arrow-top-right-on-square"
                                class="h-3 w-3 text-gray-400 dark:text-gray-500 ml-auto" />
                        </div>
                    </x-filament::dropdown.list.item>
                @endif
            </x-filament::dropdown.list>
        </x-filament::dropdown>
    </div>
@else
    <div class="fi-logo flex text-xl font-bold leading-5 tracking-tight text-gray-950 dark:text-white">
        {{ $appName }}
        @if ($currentTenant && $currentTenantName !== $appName)
            <span class="text-gray-500 dark:text-gray-400 text-sm font-normal ml-2">
                - {{ $currentTenantName }}
            </span>
        @endif
    </div>
@endif
