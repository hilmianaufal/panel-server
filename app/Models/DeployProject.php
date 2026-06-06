<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeployProject extends Model
{
    protected $fillable = [
        'name',
        'repository',
        'branch',
        'project_path',
        'last_deployed_at',
    ];

    protected $casts = [
    'last_deployed_at' => 'datetime',
];
}