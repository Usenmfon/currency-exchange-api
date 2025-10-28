<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name', 'capital', 'region', 'population',
        'currency_code', 'exchange_rate', 'estimated_gdp',
        'flag_url', 'last_refreshed_at',
    ];

    protected $casts = [
        'population' => 'integer',
        'exchange_rate' => 'float',
        'estimated_gdp' => 'float',
    ];
}
