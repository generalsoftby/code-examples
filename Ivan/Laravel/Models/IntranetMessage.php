<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class IntranetMessage
 * @package App\Models
 *
 * @property integer $id
 * @property integer $sender_id
 * @property string $receiver_type
 * @property integer $receiver_id
 * @property string $content
 * @property integer|null $quoted_id
 * @property string|null $url
 * @property Carbon|null $read_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Admin $sender
 * @property-read Group|User $receiver
 * @property-read IntranetMessage|null $quoted
 * @property-read Collection|IntranetMessageDelivery[] $deliveries
 * @property-read Collection|Attachment[] $attachments
 * @property-read Collection|IntranetMessageImage[] $images
 * @property-read bool $isReadable
 */
class IntranetMessage extends Model
{
    protected $table = 'intranet_messages';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'read_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'isReadable',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'sender',
        'quoted',
        'attachments',
        'images',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sender()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function receiver()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function quoted()
    {
        return $this->belongsTo(IntranetMessage::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deliveries()
    {
        return $this->hasMany(IntranetMessageDelivery::class, 'message_id');
    }

    /**
     * Attachments
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function attachments()
    {
        return $this->belongsToMany(Attachment::class, 'intranet_messages_attachments', 'message_id'/*, 'attachment_id'*/);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images()
    {
        return $this->hasMany(IntranetMessageImage::class, 'message_id');
    }

    /**
     * @return bool
     */
    public function getIsReadableAttribute()
    {
        return $this->attributes['receiver_type'] == User::class;
    }
}
