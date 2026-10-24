<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'business_id', 'user_id', 'title', 'body', 'type', 'read_at',
    ];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }
}
