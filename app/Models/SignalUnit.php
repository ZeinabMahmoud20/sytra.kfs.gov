<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignalUnit extends Model
{
    protected $table = 'SIGNAL_UNIT';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'ID',
        'UNIT_SIGNAL_DATE',
        'UNIT_SIGNAL_TIME',
        'UNIT_SIGNAL_SUBJECT',
        'UNIT_SIGNAL_CONTENT',
        'UNIT_AUTHORITY_ID',
        'MAIN_SEND_ID',
        'MAIN_SEND_CODE',
        'UNIT_SIGNAL_TYPE',
    ];

    public function authority()
    {
        return $this->belongsTo(Authority::class, 'UNIT_AUTHORITY_ID', 'ID');
    }

    public function mainSignal()
    {
        return $this->belongsTo(MainSignal::class, 'MAIN_SEND_ID', 'MainSignalID');
    }

}