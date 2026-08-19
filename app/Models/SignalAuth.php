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

    /**
     * الكارت (SIGNAL_UNIT) اللي الحالة دي خاصة بيه.
     * ملحوظة: SIGNAL_ID هنا بيشاور على SIGNAL_UNIT.ID (مسار الإشارات الجديد)
     */
    public function signalUnit()
    {
        return $this->belongsTo(SignalUnit::class, 'SIGNAL_ID', 'ID');
    }

    /**
     * الثريد (MainSignalTBL) اللي الحالة دي تابعة له
     */
    public function mainSignal()
    {
        return $this->belongsTo(MainSignal::class, 'MAIN_SIGNAL_ID', 'MainSignalID');
    }
}