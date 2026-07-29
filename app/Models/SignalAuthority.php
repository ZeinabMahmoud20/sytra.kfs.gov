<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignalAuthority extends Model
{
    protected $table = 'SIGNAL_AUTHORITY';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'SIGNAL_NAME',
    ];
}