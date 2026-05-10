<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'key',
        'label',
        'price_display',
        'period_display',
        'amount_kobo',
        'is_popular',
        'description',
    ];

    protected $casts = [
        'is_popular' => 'boolean',
    ];
}
