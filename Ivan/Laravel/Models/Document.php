<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * App\Models\Document
 *
 * @property integer $id
 * @property string|null $owner_type
 * @property integer|null $owner_id
 * @property string $title
 * @property string $content
 * @property string $template
 * @property integer $file_id|null
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Model|null $owner
 * @property-read File|null $file
 * @mixin \Eloquent
 */
class Document extends Model
{
    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'file',
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
    public function file()
    {
        return $this->belongsTo(File::class);
    }
}
