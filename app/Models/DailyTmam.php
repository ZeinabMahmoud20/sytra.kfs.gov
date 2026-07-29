<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyTmam extends Model
{
    protected $table = 'DAILY_TMAM';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'CODE',
        'TMAM_DATE',
        'TMAM_TIME',
        'TMAM_ID',
    ];

    public function tmam()
    {
        return $this->belongsTo(Tmam::class, 'TMAM_ID', 'ID');
    }

}