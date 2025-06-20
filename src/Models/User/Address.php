<?php

namespace Eclipse\Core\Models\User;

use Eclipse\Core\Database\Factories\AddressFactory;
use Eclipse\Core\Enums\AddressType;
use Eclipse\Core\Models\User;
use Eclipse\World\Models\Country;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_addresses';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'recipient',
        'company_name',
        'company_vat_id',
        'street_address',
        'postal_code',
        'city',
        'type',
        'country_id',
        'user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'street_address' => 'array',
            'type' => 'array',
            'user_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    protected static function newFactory(): AddressFactory
    {
        return AddressFactory::new();
    }

    protected static function booted()
    {
        static::saving(function (self $address) {
            $hasDefaultAddress = self::where('user_id', $address->user_id)->whereJsonContains('type', AddressType::DEFAULT_ADDRESS->value)->exists();

            if ($hasDefaultAddress) {
                $address->type = array_diff($address->type, [AddressType::DEFAULT_ADDRESS->value]);
            }
        });
    }
}
