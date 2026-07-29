<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecieveReport extends Model
{
    protected $table = 'RECIEVE_REPORT';
    protected $primaryKey = 'ID';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'REPORTER_SSN',
        'REPORTER_NAME',
        'REPORTING_Auth',
        'REPORTING_SORT',
        'REPORT_START_DATE',
        'REPORT_START_TIME',
        'REPORT_RECIPIENT',
        'CITY',
        'VILLAGE',
        'X_AXIS',
        'Y_AXIS',
        'DAMAGE',
        'PLACE_Accident',
        'Deceased_Num',
        'INFECTED_NUM',
        'REPORT_END_DATE',
        'REPORT_END_TIME',
        'REQUEST_STATUS',
        'IS_LOCKED',
        'REPORT_REGISTER_NUMBER',
        'REPORT_FOLLOWUP_NUMBER',
        'NOTIFIED_AUTHORITIES',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'REPORT_RECIPIENT', 'USER_ID');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'CITY', 'CITY_ID');
    }

    public function village()
    {
        return $this->belongsTo(Village::class, 'VILLAGE', 'VILLAGE_ID');
    }

    public function reportingType()
    {
        return $this->belongsTo(ReportingType::class, 'REPORTING_SORT', 'REPORT_ID');
    }

    public function deaths()
    {
        return $this->hasMany(Death::class, 'REPORT_ID', 'ID');
    }

    public function injuries()
    {
        return $this->hasMany(Injured::class, 'REPORT_ID', 'ID');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'ReportID', 'ID');
    }

    public function reportAuths()
    {
        return $this->hasMany(ReportAuth::class, 'REPORT_ID', 'ID');
    }

}