<?php

namespace Eclipse\Core\SettingsRepositories;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Models\SettingsProperty;
use Spatie\LaravelSettings\SettingsRepositories\DatabaseSettingsRepository;

/**
 * Repository class for site-specific settings.
 */
class SiteSettingsRepository extends DatabaseSettingsRepository
{
    public function updatePropertiesPayload(string $group, array $properties): void
    {
        $propertiesInBatch = collect($properties)->map(function ($payload, $name) use ($group) {
            return [
                'group' => $group,
                'name' => $name,
                'payload' => $this->encode($payload),
                'site_id' => Filament::getTenant()?->id,
            ];
        })->values()->toArray();

        $this->getBuilder(false)
            ->where('group', $group)
            ->upsert($propertiesInBatch, ['group', 'name', 'site_id'], ['payload']);
    }

    public function getBuilder(bool $fallback = true): Builder
    {
        $builder = parent::getBuilder();

        if ($fallback) {
            // Use default fallback
            $table = $this->table ?? (new SettingsProperty)->getTable();
            $builder->where(function (Builder $query) use ($table) {
                $query
                    // ... where site_id matches
                    ->where('site_id', Filament::getTenant()?->id)
                    // ... or where site_id is null and a record with a matching site_id does not exist
                    ->orWhere(function (Builder $query) use ($table) {
                        $query->whereNull('site_id')
                            ->whereNotExists(function (QueryBuilder $query) use ($table) {
                                $query->select(DB::raw(1))
                                    ->from($table, 't2')
                                    ->where('site_id', Filament::getTenant()?->id)
                                    ->whereColumn('t2.group', $table.'.group')
                                    ->whereColumn('t2.name', $table.'.name');
                            });
                    });
            });
        } else {
            // Don't use fallback, get only settings with exact site match
            $builder->where('site_id', Filament::getTenant()?->id);
        }

        return $builder;
    }
}
