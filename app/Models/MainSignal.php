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

    /**
     * المستخدم اللي أنشأ/استلم الثريد ده (RECEIVER_ID بيشاور على users.id).
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'RECEIVER_ID', 'id');
    }

    public function units()
    {
        return $this->hasMany(SignalUnit::class, 'MAIN_SEND_ID', 'MainSignalID');
    }
}