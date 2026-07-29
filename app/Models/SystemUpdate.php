<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemUpdate extends Model
{
    protected $table = 'updates';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'version',
        'path',
    ];
}