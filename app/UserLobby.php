<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLobby extends Model
{
    protected $fillable = [
        'user_id',
        'lobby_name',
        'is_present',
        'is_notification_sent',
    ];

    public static function insertOrUpdate(int $userId, string $lobbyName, bool $isPresent, bool $isNotificationSent = false)
    {
        static::query()
            ->updateOrCreate([
                'user_id' => $userId,
                'lobby_name' => $lobbyName,
            ], [
                'is_present' => $isPresent,
                'is_notification_sent' => $isNotificationSent,
            ]);
    }

    public static function needsToBeNotified(int $userId, string $lobbyName)
    {
        $record = static::query()
            ->where('user_id', $userId)
            ->where('lobby_name', $lobbyName)
            ->first();
        return !$record || !($record->is_present || $record->is_notification_sent);
    }
}
