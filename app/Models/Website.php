<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    protected $fillable = [
        'name',
        'domain',
        'root_path',
        'php_version',
        'web_server',
        'status',
    ];
}