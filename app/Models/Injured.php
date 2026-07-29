<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Injured extends Model
{
    protected $table = 'INJURED';
    protected $primaryKey = 'INJURED_ID';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'INJURED_NAME',
        'INJURED_AGE',
        'INJURED_DIAGNOSIS',
        'INJURED_FOLLOWUP',
        'REPORT_ID',
    ];

    public function recieveReport()
    {
        return $this->belongsTo(RecieveReport::class, 'REPORT_ID', 'ID');
    }

}