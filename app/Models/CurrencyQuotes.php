<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyQuotes extends Model
{
    protected $fillable = [
        'id_from',
        'id_to',
        'bid',
        'ask'
    ];
}
