<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserShortcut extends Model
{
    protected $fillable = ['user_id', 'item_key', 'sort_order'];

    public static function keysForUser(int $userId): array
    {
        return self::where('user_id', $userId)
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->pluck('item_key')
            ->all();
    }
}
