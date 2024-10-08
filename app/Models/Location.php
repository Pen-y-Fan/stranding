<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\DeliveryStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Location
 *
 * @property int $id
 * @property string $name
 * @property bool $is_physical
 * @property int $district_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $clientOrders
 * @property-read int|null $client_orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Delivery> $completeDeliveries
 * @property-read int|null $complete_deliveries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $completeOrders
 * @property-read int|null $complete_orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Delivery> $deliveries
 * @property-read int|null $deliveries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $destinationOrders
 * @property-read int|null $destination_orders_count
 * @property-read \App\Models\District $district
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $incompleteOrders
 * @property-read int|null $incomplete_orders_count
 * @method static \Database\Factories\LocationFactory factory($count = null, $state = [])
 * @method static Builder|Location isPhysical()
 * @method static Builder|Location newModelQuery()
 * @method static Builder|Location newQuery()
 * @method static Builder|Location query()
 * @method static Builder|Location whereCreatedAt($value)
 * @method static Builder|Location whereDistrictId($value)
 * @method static Builder|Location whereId($value)
 * @method static Builder|Location whereIsPhysical($value)
 * @method static Builder|Location whereName($value)
 * @method static Builder|Location whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'district_id',
        'is_physical',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_physical' => 'boolean',
    ];

    /**
     * Get the District associated with the Location.
     *
     * @return BelongsTo<District, Location>
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * @return HasMany<Order>
     */
    public function clientOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'client_id');
    }

    /**
     * @return HasMany<Order>
     */
    public function destinationOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'destination_id');
    }

    /**
     * @return HasMany<Delivery>
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    /**
     * @return HasMany<Delivery>
     */
    public function completeDeliveries(): HasMany
    {
        return $this->hasMany(Delivery::class)
            ->where('status', DeliveryStatus::COMPLETE);
    }

    /**
     * @return HasMany<Order>
     */
    public function completeOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'client_id')
            ->whereHas('completeDeliveries');
    }

    /**
     * @return HasMany<Order>
     */
    public function incompleteOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'client_id')
            ->whereDoesntHave(
                'completeDeliveries'
            );
    }

    /**
     * @param Builder<Location> $query
     * @return Builder<Location>
     */
    public function scopeIsPhysical(Builder $query): Builder
    {
        return $query->where('is_physical', true);
    }
}
