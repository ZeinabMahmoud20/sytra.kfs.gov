<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportingType extends Model
{
    protected $table = 'REPORTING_TYPES';
    protected $primaryKey = 'REPORT_ID';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'REPORT_SORT',
        'AUTHORITY',
        'IS_INTERNET',
    ];

    public function recieveReports()
    {
        return $this->hasMany(RecieveReport::class, 'REPORTING_SORT', 'REPORT_ID');
    }

}