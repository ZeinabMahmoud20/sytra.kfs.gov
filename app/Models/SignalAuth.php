<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignalAuth extends Model
{
    protected $table = 'SIGNAL_AUTH';
    protected $primaryKey = 'ID';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'CONTACT_NAME',
        'STATE',
        'SIGNAL_ID',
        'MAIN_SIGNAL_ID',
        'MAIN_SIGNAL_CODE',
    ];

    public function recieveSignal()
    {
        return $this->belongsTo(RecieveSignal::class, 'SIGNAL_ID', 'ID');
    }

    public function mainSignal()
    {
        return $this->belongsTo(MainSignal::class, 'MAIN_SIGNAL_ID', 'MainSignalID');
    }

}