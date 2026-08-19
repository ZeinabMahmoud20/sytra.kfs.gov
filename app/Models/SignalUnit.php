<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignalUnit extends Model
{
    protected $table = 'SIGNAL_UNIT';
    protected $primaryKey = 'ID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'UNIT_SIGNAL_DATE',
        'UNIT_SIGNAL_TIME',
        'UNIT_SIGNAL_SUBJECT',
        'UNIT_SIGNAL_CONTENT',
        'UNIT_AUTHORITY_ID',
        'MAIN_SEND_ID',
        'MAIN_SEND_CODE',
        'UNIT_SIGNAL_TYPE',
    ];

    /**
     * الجهة اللي أرسلت الإشارة (SIGNAL_AUTHORITY)
     */
    public function sender()
    {
        return $this->belongsTo(SignalAuthority::class, 'UNIT_AUTHORITY_ID', 'ID');
    }

    /**
     * الثريد (الموضوع الرئيسي) اللي الكارت ده تابع له
     */
    public function mainSignal()
    {
        return $this->belongsTo(MainSignal::class, 'MAIN_SEND_ID', 'MainSignalID');
    }

    /**
     * حالات الجهات (Correct/X) الخاصة بالكارت ده تحديداً
     */
    public function authStates()
    {
        return $this->hasMany(SignalAuth::class, 'SIGNAL_ID', 'ID');
    }
}