<?php

namespace App\Models;

use App\Classes\ReviewStatusEnum;
use App\Scopes\SetLanguageScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Review
 * @package App\Models
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $showcase_id
 * @property integer $buyer_id
 * @property string $ip_address
 * @property string $status
 * @property integer $avatar_id
 * @property string $customer_name
 * @property string $company_position_etc
 * @property integer $language_id
 * @property integer $rating
 * @property string $review
 * @property boolean $show_in_widgets
 * @property string $reply
 * @property-read array $ipAddressCoords
 */
class Review extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'reviews';

    /**
     * @var array
     */
    protected $appends =
        [
            'ratingStars',
            'statusColor',
            'transStatus',
            'avatarUrl',
            'categoriesAndProductsWithTrans',
            'ipAddressCoords',
        ];

    /**
     * @var array
     */
    protected $with =
        [
            'language',
            'buyer',
            'products',
            'categories'
        ];

    /**
     * @return array
     */
    public function getCategoriesAndProductsWithTransAttribute()
    {
        $language = \AdminConfigurationService::getCurrentLanguage();

        $productsAndCategories = array();

        foreach ($this->categories as $category) {
            $productsAndCategories[] = ['title' => $category
                ->descriptionsL10n()
                ->withoutGlobalScopes([SetLanguageScope::class])
                ->where('language_id', $language->id)
                ->pluck('name')
            ];
        }

        foreach ($this->products as $product) {
            $productsAndCategories[] = ['title' => $product
                ->descriptionsL10n()
                ->withoutGlobalScopes([SetLanguageScope::class])
                ->where('language_id', $language->id)
                ->pluck('name')
            ];
        }

        return $productsAndCategories;
    }

    /**
     * @return array
     */
    public function getRatingStarsAttribute()
    {
        $ratingStars = array();

        for ($i = 1; $i <= 5; $i++) {
            if ($this->rating >= $i) {
                $ratingStars[] = ['icon' => 'icon-star-full2'];
            } else {
                $ratingStars[] = ['icon' => 'icon-star-empty3'];
            }
        }

        return $ratingStars;
    }

    /**
     * @return string
     */
    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case ReviewStatusEnum::NEW :
                $statusColor = "bg-warning";
                break;
            case ReviewStatusEnum::APPROVED :
                $statusColor = "bg-success";
                break;
            case ReviewStatusEnum::NOT_APPROVED :
                $statusColor = "bg-grey";
                break;
        }

        return $statusColor;
    }

    /**
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    public function getTransStatusAttribute()
    {
        return trans('admin/reviews.status.' . $this->status);
    }

    /**
     * @return string
     */
    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? $this->avatar->url : '/assets/images/placeholder.jpg';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function showcase()
    {
        return $this->belongsTo(Showcase::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function avatar()
    {
        return $this->belongsTo(Attachment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'reviews_products');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(ProductCategory::class, 'reviews_product_categories');
    }

    /**
     * @return array
     */
    public function getIpAddressCoordsAttribute()
    {
        if (empty($this->ip_address))
        {
            return ['lat' => '', 'lon' => '', ];
        }

        $coords = geoip($this->ip_address);

        return ['lat' => $coords->lat, 'lon' => $coords->lon];
    }
}
