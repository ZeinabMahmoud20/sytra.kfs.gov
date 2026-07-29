<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TmamAuth extends Model
{
    protected $table = 'TMAM_AUTH';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'TMAM_ID',
        'AUTH_ID',
    ];

    public function tmam()
    {
        return $this->belongsTo(Tmam::class, 'TMAM_ID', 'ID');
    }

}