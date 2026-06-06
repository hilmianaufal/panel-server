<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CloudflareSetting extends Model
{
    protected $fillable = [
        'account_id',
        'tunnel_id',
        'api_token',
        'tunnel_name',

    ];
}