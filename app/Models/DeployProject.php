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
    'auto_database',
    'db_name',
    'db_username',
    'db_password',
    'last_deployed_at',
];

protected $casts = [
    'last_deployed_at' => 'datetime',
    'auto_database' => 'boolean',
];
}