<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\DeliveryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
