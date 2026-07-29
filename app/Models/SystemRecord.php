<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemRecord extends Model
{
    protected $table = 'SYSTEM_RECORD';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'USER_FULL_NAME',
        'DEVICE_NAME',
        'MACHINE_IP',
        'TITLE',
        'DESCRIBTION',
        'CREATED_DATE',
        'ISACTIVE',
        'USER_ID',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'USER_ID', 'USER_ID');
    }

}