<?php

namespace Eclipse\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
// in app/Models/WorldRegion.php

use Eclipse\Core\Database\Factories\WorldRegionFactory;


class WorldRegion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'parent_id',
        'is_special',
    ];

    /**
     * Sub-regions of this region.
     */
    public function subRegions()
    {
        return $this->hasMany(WorldRegion::class, 'parent_id');
    }

    /**
     * Parent region (if any).
     */
    public function parent()
    {
        return $this->belongsTo(WorldRegion::class, 'parent_id');
    }

    /**
     * Countries that belong to this region (geo).
     */
    public function countries()
    {
        return $this->hasMany(WorldCountry::class, 'region_id');
    }

    public static function newFactory(): WorldRegionFactory
{
    return WorldRegionFactory::new();
}

}
