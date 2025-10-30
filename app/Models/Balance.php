<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    protected $fillable = ['user_id', 'amount'];

    protected $casts = [
        'amount' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
