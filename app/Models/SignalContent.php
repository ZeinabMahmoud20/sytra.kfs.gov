<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignalContent extends Model
{
    protected $table = 'SIGNAL_CONTENT';
    protected $primaryKey = 'SIGNALCONTENT_ID';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'SIGNALCONTENT',
    ];
}