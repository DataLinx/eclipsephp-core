@php
    $currentTenant = filament()->getTenant();
    $currentTenantName = filament()->getTenantName($currentTenant);

    $canSwitchTenants = count(
        $tenants = array_filter(
            filament()->getUserTenants(filament()->auth()->user()),
            fn(\Illuminate\Database\Eloquent\Model $tenant): bool => !$tenant->is($currentTenant),
        ),
    );
@endphp
<x-filament::dropdown placement="bottom-start" size teleport>
    <x-slot name="trigger">
        <button type="button"
            class="fi-tenant-menu-trigger group flex w-full items-center justify-center gap-x-3 rounded-lg p-2 text-sm font-medium outline-none transition duration-75 hover:bg-gray-100 focus-visible:bg-gray-100 dark:hover:bg-white/5 dark:focus-visible:bg-white/5">
            <x-filament-panels::avatar.tenant :tenant="$currentTenant" class="shrink-0" />

            <x-filament::icon icon="heroicon-m-chevron-down" alias="panels::tenant-menu.toggle-button"
                class="ms-auto h-5 w-5 shrink-0 text-gray-400 transition duration-75 group-hover:text-gray-500 group-focus-visible:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-400 dark:group-focus-visible:text-gray-400" />
        </button>
    </x-slot>

    @if ($canSwitchTenants)
        <x-filament::dropdown.list>
            @foreach ($tenants as $tenant)
                <x-filament::dropdown.list.item :href="route('filament.admin.pages.dashboard', ['tenant' => $tenant])" :image="filament()->getTenantAvatarUrl($tenant)" tag="a">
                    {{ filament()->getTenantName($tenant) }}
                </x-filament::dropdown.list.item>
            @endforeach
        </x-filament::dropdown.list>
    @endif

</x-filament::dropdown>
