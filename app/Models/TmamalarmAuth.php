<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TmamalarmAuth extends Model
{
    protected $table = 'TMAMALARM_AUTH';
    protected $primaryKey = 'ID';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'TMAM_ID',
        'USER_ID',
    ];

    public function tmamAlarm()
    {
        return $this->belongsTo(TmamAlarm::class, 'TMAM_ID', 'ID');
    }

}