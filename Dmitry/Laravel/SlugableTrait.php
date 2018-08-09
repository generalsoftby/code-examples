<?php

namespace App\Repositories\Slug;

use App\Models;

/**
 * Trait SlugableTrait
 * @package App\Repositories\Slug
 */
trait SlugableTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function slug()
    {
        return $this->morphMany(Models\Slug::class, 'entity');
    }

    /**
     * @param $languageId
     * @return mixed
     */
    public function getSlug($languageId)
    {
        $obj = $this->slug()
            ->where('showcase_id', $this->showcase->id)
            ->where('language_id', $languageId)
            ->first();

        return $obj ? $obj->slug : null;
    }

    /**
     * @param $langId
     * @param array $extraOptions
     * @return null|string
     */
    public function getRoute($langId, $extraOptions = [])
    {
        $slug = $this->getSlug($langId);

        $options = count($extraOptions) ? array_merge([$slug], $extraOptions) : $slug;

        return $slug ? route('slug.index', $options) : null;
    }

    /**
     * @param Models\Showcase $showcase
     * @param Models\Language $language
     * @return string
     */
    public function getShowcaseUrl(Models\Showcase $showcase, Models\Language $language)
    {
        return 'http://' .  $showcase->domain . '/' .  $language->slug . '/' . $this->getSlug($language->id);
    }
}
