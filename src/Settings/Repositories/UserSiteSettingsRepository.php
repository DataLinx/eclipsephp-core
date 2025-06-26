<?php

namespace Eclipse\Core\Settings\Repositories;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Models\SettingsProperty;
use Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository;

class UserSiteSettingsRepository extends DatabaseSettingsRepository
{
    protected ?int $userId = null;

    public function updatePropertiesPayload(string $group, array $properties): void
    {
        $propertiesInBatch = collect($properties)->map(function ($payload, $name) use ($group) {
            return [
                'group' => $group,
                'name' => $name,
                'payload' => $this->encode($payload),
                'site_id' => Filament::getTenant()?->id,
                'user_id' => auth()->user()?->id,
            ];
        })->values()->toArray();

        $this->getBuilder(false)
            ->where('group', $group)
            ->upsert($propertiesInBatch, ['group', 'name', 'site_id', 'user_id'], ['payload']);
    }

    public function forUser(int $userId): self
    {
        $clone = clone $this;
        $clone->userId = $userId;
        return $clone;
    }

    public function getBuilder(bool $fallback = true): Builder
    {
        $builder = parent::getBuilder();
        $userId = $this->userId ?? auth()->user()?->id;

        if ($fallback) {
            // Use default fallback
            $table = $this->table ?? (new SettingsProperty)->getTable();
            $builder
                ->where(function (Builder $query) use ($table, $userId) {
                $query
                    ->where(function (Builder $query) use ($table, $userId) {
                        $query
                            // ... where site_id matches
                            ->where('site_id', Filament::getTenant()?->id)
                            // ... where user_id matches
                            ->where('user_id', $userId);
                    })
                    // ... or where site_id is null and a record with a matching site_id does not exist
                    ->orWhere(function (Builder $query) use ($table, $userId) {
                        $query
                            ->whereNull('site_id')
                            ->whereNull('user_id')
                            ->whereNotExists(function (QueryBuilder $query) use ($table, $userId) {
                                $query->select(DB::raw(1))
                                    ->from($table, 't2')
                                    ->where('site_id', Filament::getTenant()?->id)
                                    ->where('user_id', $userId)
                                    ->whereColumn('t2.group', $table.'.group')
                                    ->whereColumn('t2.name', $table.'.name');
                            });
                    });
            });
        } else {
            // Don't use fallback, get only settings with the exact site/user match
            $builder
                ->where('site_id', Filament::getTenant()?->id)
                ->where('user_id', $userId);
        }

        return $builder;
    }
}
