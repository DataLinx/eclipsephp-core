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

    protected static function booted(): void
    {
        static::saving(function (self $address): void {
            if (! in_array(AddressType::DEFAULT_ADDRESS->value, $address->type)) {
                return;
            }

            $otherAddresses = self::where('user_id', $address->user_id)
                ->where('id', '!=', $address->id ?? 0)
                ->get(['id', 'type']);

            $addressesToUpdate = $otherAddresses->filter(
                fn (Model $existingAddress): bool => in_array(AddressType::DEFAULT_ADDRESS->value, $existingAddress->type ?? [])
            );

            $addressesToUpdate->each(function (Model $existingAddress): void {
                $newType = array_values(array_diff($existingAddress->type, [AddressType::DEFAULT_ADDRESS->value]));

                $existingAddress->timestamps = false;
                $existingAddress->updateQuietly([
                    'type' => $newType,
                ]);
            });
        });

        static::deleted(function (self $address): void {
            if (! in_array(AddressType::DEFAULT_ADDRESS->value, $address->type)) {
                return;
            }

            self::where('user_id', $address->user_id)
                ->orderBy('created_at', 'asc')
                ->first(['id', 'type'])
                ?->updateQuietly([
                    'type' => array_merge($address->type ?? [], [AddressType::DEFAULT_ADDRESS->value]),
                ]);
        });
    }
}
