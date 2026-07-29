<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotifiedAuth extends Model
{
    protected $table = 'NotifiedAuthTBL';
    protected $primaryKey = 'ID';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'Notified_Auth',
    ];
}