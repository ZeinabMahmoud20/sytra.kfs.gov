<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tmam extends Model
{
    protected $table = 'TMAM';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'TMAM_NAME',
    ];

    public function dailyTmams()
    {
        return $this->hasMany(DailyTmam::class, 'TMAM_ID', 'ID');
    }

    public function tmamAuths()
    {
        return $this->hasMany(TmamAuth::class, 'TMAM_ID', 'ID');
    }

}