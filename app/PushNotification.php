<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PushNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'image',
        'resource_id',
        'payload',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'payload' => 'array',
    ];

    protected $appends = [
        'sent_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return mixed
     */
    public function getSentAtAttribute()
    {
        $createdAt = $this->created_at;
        $timezone = request()->header('timezone');
        if ($timezone) {
            $minutes = convertTimeToMinutes($timezone);
            return $createdAt->addMinutes($minutes);
        }
        return $createdAt;
    }
}
