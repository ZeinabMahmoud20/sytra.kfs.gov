<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SendSignal extends Model
{
    protected $table = 'SEND_SIGNAL';
    protected $primaryKey = 'ID';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'U_SIGNAL_DATE',
        'U_SIGNAL_TIME',
        'U_SIGNAL_SUBJECT',
        'U_SIGNAL_CONTENT',
        'AUTHORITY_ID',
        'MAIN_SIGNAL_ID',
        'MAIN_SIGNAL_CODE',
    ];

    public function authority()
    {
        return $this->belongsTo(Authority::class, 'AUTHORITY_ID', 'ID');
    }

    public function mainSignal()
    {
        return $this->belongsTo(MainSignal::class, 'MAIN_SIGNAL_ID', 'MainSignalID');
    }

}