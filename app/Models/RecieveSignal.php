<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecieveSignal extends Model
{
    protected $table = 'RECIEVE_SIGNAL';
    protected $primaryKey = 'ID';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'SIGNAL_DATE',
        'SIGNAL_TIME',
        'SIGNAL_SUBJECT',
        'SIGNAL_CONTENT',
        'RECIEVE_CODE',
        'AUTHORITY_ID',
    ];

    public function authority()
    {
        return $this->belongsTo(Authority::class, 'AUTHORITY_ID', 'ID');
    }

    public function recSignalAuths()
    {
        return $this->hasMany(RecSignalAuth::class, 'SIGNAL_ID', 'ID');
    }

    public function signalAuths()
    {
        return $this->hasMany(SignalAuth::class, 'SIGNAL_ID', 'ID');
    }

}