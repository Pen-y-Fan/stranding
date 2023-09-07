<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Location
 *
 * @property int $id
 * @property string $name
 * @property int $district_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\District $district
 * @method static \Database\Factories\LocationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Location newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Location newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Location query()
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereDistrictId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'district_id',
    ];

    /**
     * Get the Team associated with the location. Thoughts: will probably be better as a pivot table - in future
     *
     * @return BelongsTo<District, Location>
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
