<?php

namespace Eclipse\Core\View\Components;

use Eclipse\Core\Services\Registry;
use Exception;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

class BrandWithTenantSwitcher extends Component
{
    public function render()
    {
        return view('eclipse::filament.components.brand-with-tenant-switcher', [
            'shouldShowDropdown' => $this->shouldShowDropdown(),
            'getAppName' => $this->getAppName(),
            'hasSpaMode' => $this->hasSpaMode(),
            'getDashboardUrl' => $this->getDashboardUrl(),
            'getCurrentTenant' => $this->getCurrentTenant(),
            'getCurrentTenantName' => $this->getCurrentTenantName(),
            'canSwitchTenants' => $this->canSwitchTenants(),
            'getTenants' => $this->getTenants(),
            'hasFrontend' => $this->hasFrontend(),
            'getFrontendUrl' => $this->getFrontendUrl(),
        ]);
    }

    public function getAppName(): string
    {
        return Registry::getSite()->name ?? config('app.name');
    }

    public function hasSpaMode(): bool
    {
        return Filament::getCurrentPanel()->hasSpaMode();
    }

    public function getDashboardUrl(): string
    {
        return '/'.trim(Filament::getCurrentPanel()->getPath(), '/');
    }

    public function getCurrentTenant(): ?Model
    {
        return filament()->getTenant();
    }

    public function getCurrentTenantName(): ?string
    {
        $currentTenant = $this->getCurrentTenant();

        return $currentTenant ? filament()->getTenantName($currentTenant) : null;
    }

    public function getTenants(): array
    {
        if (! $this->isMultiSiteEnabled() || ! filament()->auth()->check()) {
            return [];
        }

        $currentTenant = $this->getCurrentTenant();

        return array_filter(
            filament()->getUserTenants(filament()->auth()->user()),
            fn (Model $tenant): bool => ! $tenant->is($currentTenant),
        );
    }

    public function canSwitchTenants(): bool
    {
        return count($this->getTenants()) > 0;
    }

    public function hasFrontend(): bool
    {
        return collect(Filament::getPanels())->has('frontend');
    }

    public function getFrontendUrl(): string
    {
        if (! $this->hasFrontend()) {
            return config('app.url');
        }

        try {
            $currentTenant = $this->getCurrentTenant();
            if ($currentTenant?->domain) {
                return "https://{$currentTenant->domain}";
            } else {
                return config('app.url');
            }
        } catch (Exception $e) {
            return config('app.url');
        }
    }

    public function shouldShowDropdown(): bool
    {
        return $this->canSwitchTenants() || $this->hasFrontend();
    }

    private function isMultiSiteEnabled(): bool
    {
        return config('eclipse.multi_site', false);
    }
}
