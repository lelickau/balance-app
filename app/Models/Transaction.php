<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['user_id', 'type', 'amount', 'comment'];

    protected $casts = [
        'amount' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
