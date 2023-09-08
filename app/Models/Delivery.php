<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\DeliveryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Delivery
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon $started_at
 * @property \Illuminate\Support\Carbon|null $ended_at
 * @property DeliveryStatus $status
 * @property string|null $comment
 * @property int $order_id
 * @property int $user_id
 * @property int $location_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Location $location
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\DeliveryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery query()
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereUserId($value)
 * @mixin \Eloquent
 */
class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'started_at',
        'ended_at',
        'status',
        'location_id',
        'comment',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'ended_at'    => 'datetime',
        'status'      => DeliveryStatus::class,
        'order_id'    => 'int',
        'user_id'     => 'int',
        'location_id' => 'int',
    ];

    /**
     * Get the current Location associated with the Delivery.
     *
     * @return BelongsTo<Location, Delivery>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the Order associated with the Delivery.
     *
     * @return BelongsTo<Order, Delivery>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the User associated with the Delivery.
     *
     * @return BelongsTo<User, Delivery>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
