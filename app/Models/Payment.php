<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'reference',
        'plan',
        'amount_kobo',
        'status',
        'paystack_response',
    ];

    protected $casts = [
        'paystack_response' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
