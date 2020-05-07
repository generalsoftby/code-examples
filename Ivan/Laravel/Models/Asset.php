<?php

namespace App\Models;

use App\Enums\Parameters;
use App\Services\SteamInventoryService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Asset
 * @package App\Models
 * @mixin \Illuminate\Database\Eloquent\
 *
 * @property int $id
 * @property string $owner_type
 * @property int $owner_id
 * @property int $item_id
 * @property string $assetid
 * @property string $image
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at|null
 * @property-read string $hash
 * @property-read float $price
 * @property-read float|null $priceWoDiscount
 * @property-read string|null $restricted
 * @property-read Bot|User $owner
 * @property-read Item $item
 * @property-read AssetRecord|null $record
 * @property-read AssetGameRecord|null $gameRecord
 */
class Asset extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates =
        [
            'deleted_at',
        ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends =
        [
            'hash',
            'price',
            'priceWoDiscount',
            'restricted',
        ];

    /**
     * @var array
     */
    protected $with =
        [
            'item',
            'record',
            'gameRecord',
        ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden =
        [
            'owner',
            'item',
            'record',
            'gameRecord',
        ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function record()
    {
        return $this->hasOne(AssetRecord::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function gameRecord()
    {
        return $this->hasOne(AssetGameRecord::class);
    }

    /**
     * @return string
     */
    public function getHashAttribute()
    {
        return $this->item->hash;
    }

    /**
     * @return float
     */
    public function getPriceAttribute()
    {
        return SteamInventoryService::getAssetPrice($this);
    }

    /**
     * @return float
     */
    public function getPriceWoDiscountAttribute()
    {
        return SteamInventoryService::getAssetPrice($this, true);
    }

    /**
     * @return null|string
     */
    public function getRestrictedAttribute()
    {
        return SteamInventoryService::getAssetRestrict($this);
    }
}
