<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MainSignal extends Model
{
    protected $table = 'MainSignalTBL';
    protected $primaryKey = 'MainSignalID';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'MainSignalCode',
        'RECEIVER_ID',
    ];

    public function sendSignals()
    {
        return $this->hasMany(SendSignal::class, 'MAIN_SIGNAL_ID', 'MainSignalID');
    }

    public function signalAuths()
    {
        return $this->hasMany(SignalAuth::class, 'MAIN_SIGNAL_ID', 'MainSignalID');
    }

    public function signalUnits()
    {
        return $this->hasMany(SignalUnit::class, 'MAIN_SEND_ID', 'MainSignalID');
    }

}