<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\DeliveryCategory
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @method static \Database\Factories\DeliveryCategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DeliveryCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * @return HasMany<Order>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
