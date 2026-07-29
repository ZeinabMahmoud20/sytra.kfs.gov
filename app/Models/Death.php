<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Death extends Model
{
    protected $table = 'DEATHS';
    protected $primaryKey = 'Deceased_ID';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'Deceased_NAME',
        'Deceased_AGE',
        'Deceased_ADDRESS',
        'Deceased_FOLLOWUP',
        'REPORT_ID',
    ];

    public function recieveReport()
    {
        return $this->belongsTo(RecieveReport::class, 'REPORT_ID', 'ID');
    }

}