<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\OrderStatus;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\OrderUser
 *
 * @property int $id
 * @property int $order_id
 * @property int $user_id
 * @property string $status OrderStatus Enum is cast to a string
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderUser whereUserId($value)
 * @mixin \Eloquent
 */
class OrderUser extends Pivot
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    protected $fillable = [
        'status',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
    ];
}
