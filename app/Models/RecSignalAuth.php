<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecSignalAuth extends Model
{
    protected $table = 'REC_SIGNAL_AUTH';
    protected $primaryKey = 'ID';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'CONTACT_NAME',
        'STATE',
        'SIGNAL_ID',
    ];

    public function recieveSignal()
    {
        return $this->belongsTo(RecieveSignal::class, 'SIGNAL_ID', 'ID');
    }

}