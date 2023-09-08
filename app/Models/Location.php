<?php

declare(strict_types=1);

namespace App\Models;

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
 * @property int $district_id
 * @property bool $is_physical
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\District $district
 * @method static Builder|Location isPhysical()
 * @method static \Database\Factories\LocationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Location newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Location newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Location query()
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereDistrictId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereUpdatedAt($value)
 * @method static Builder|Location whereIsPhysical($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $clientOrders
 * @property-read int|null $client_orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $destinationOrders
 * @property-read int|null $destination_orders_count
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
     * @param Builder<Location> $query
     * @return Builder<Location>
     */
    public function scopeIsPhysical(Builder $query): Builder
    {
        return $query->where('is_physical', true);
    }
}