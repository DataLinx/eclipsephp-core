<?php

namespace Eclipse\Core\Models;

use Eclipse\Common\Foundation\Models\Scopes\ActiveScope;
use Eclipse\Core\Database\Factories\LocaleFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([ActiveScope::class])]
class Locale extends Model
{
    use HasFactory;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'native_name',
        'system_locale',
        'is_active',
        'is_available_in_panel',
        'datetime_format',
        'date_format',
        'time_format',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'is_available_in_panel' => 'boolean',
        ];
    }

    public static function getAvailableLocales(): Collection
    {
        return self::where('is_active', true)
            ->where('is_available_in_panel', true)
            ->get();
    }

    protected static function newFactory(): LocaleFactory
    {
        return LocaleFactory::new();
    }
}
