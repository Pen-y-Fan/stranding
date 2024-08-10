<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\DeliveryStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property int $number
 * @property string $name
 * @property string|null $premium
 * @property int $max_likes
 * @property float $weight
 * @property int $client_id
 * @property int $destination_id
 * @property int $delivery_category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Location $client
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Delivery> $completeDeliveries
 * @property-read int|null $complete_deliveries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Delivery> $deliveries
 * @property-read int|null $deliveries_count
 * @property-read \App\Models\DeliveryCategory $deliveryCategory
 * @property-read \App\Models\Location $destination
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\District> $districts
 * @property-read int|null $districts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Delivery> $userDeliveries
 * @property-read int|null $user_deliveries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\OrderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDeliveryCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDestinationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereMaxLikes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePremium($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereWeight($value)
 * @mixin \Eloquent
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'name',
        'premium',
        'client_id',
        'destination_id',
        'delivery_category_id',
        'max_likes',
        'weight',
    ];

    protected $casts = [
        'number'               => 'int',
        'client_id'            => 'int',
        'destination_id'       => 'int',
        'delivery_category_id' => 'int',
        'max_likes'            => 'int',
    ];

    /**
     * Get the Client Location associated with the Order.
     *
     * @return BelongsTo<Location, Order>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'client_id');
    }

    /**
     * Get the Destination location associated with the Order.
     *
     * @return BelongsTo<Location, Order>
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_id');
    }

    /**
     * Get the DeliveryCategory associated with the Order.
     *
     * @return BelongsTo<DeliveryCategory, Order>
     */
    public function deliveryCategory(): BelongsTo
    {
        return $this->belongsTo(DeliveryCategory::class);
    }

    /**
     * The Districts that belong to the Order (through Location).
     * Equivalent to $order->client()->district
     *
     * @return BelongsToMany<District>
     */
    public function districts(): BelongsToMany
    {
        return $this->belongsToMany(
            District::class,
            Location::class,
            'id',
            'district_id',
            'client_id',
            'id'
        );
    }

    /**
     * The Users that belong to the Order.
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['status', 'id'])
            ->withTimestamps();
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
     * @return HasMany<Delivery>
     */
    public function userDeliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'order_id', 'id')
            ->whereUserId(auth()->id());
    }

    /**
     * weight is cast to a float e.g. 2.4 kg, but stored (set) as an int (24) in the database.
     * When retrieved (get) from the database it will be cast back to a float.
     * $order->weight will be a float (e.g. 2.4).
     *
     * @return Attribute<int, float>
     */
    protected function weight(): Attribute
    {
        return Attribute::make(
            get: static fn (int $value): float => $value / 10,
            set: static fn (float $value): int => (int) (10 * $value),
        );
    }
}
