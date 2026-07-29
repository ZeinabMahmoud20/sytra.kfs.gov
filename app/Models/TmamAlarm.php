<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TmamAlarm extends Model
{
    protected $table = 'TMAM_ALARM';
    protected $primaryKey = 'ID';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'Tmam_Name',
        'Tmam_Time',
        'Tmam_State',
    ];

    public function tmamalarmAuths()
    {
        return $this->hasMany(TmamalarmAuth::class, 'TMAM_ID', 'ID');
    }

}