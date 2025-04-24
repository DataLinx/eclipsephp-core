<?php

namespace Eclipse\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Eclipse\Core\Database\Factories\WorldCountryFactory;

class WorldCountry extends Model
{
    use HasFactory;

    protected $table = 'world_countries';
    // public $incrementing = true;
    protected $keyType = 'int';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $guarded = ['id']; // Don't allow mass assignment of ID

    protected $fillable = [
        'name',
        'code',
        'region_id',
    ];

    /**
     * Geographical region the country belongs to.
     */
    public function geoRegion()
    {
        return $this->belongsTo(WorldRegion::class, 'region_id');
    }

    /**
     * Special regions (e.g., EU, EEA) the country is part of.
     */
    public function specialRegions()
    {
        return $this->belongsToMany(
            WorldRegion::class,
            'world_country_in_special_region',
            'country_id',
            'region_id'
        )->withPivot('start_date', 'end_date')
         ->withTimestamps();
    }

    /**
     * Check if country is currently in a special region based on dates.
     */
    public function isInSpecialRegion(string $regionCode): bool
    {
        return $this->specialRegions()
            ->where('code', $regionCode)
            ->where(function ($query) {
                $query->whereNull('end_date')->orWhere('end_date', '>', now());
            })
            ->where('start_date', '<=', now())
            ->exists();
    }

    public static function newFactory(): WorldCountryFactory
{
    return WorldCountryFactory::new();
}
}
