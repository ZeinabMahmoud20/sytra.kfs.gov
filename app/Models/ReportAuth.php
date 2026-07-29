<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportAuth extends Model
{
    protected $table = 'REPORT_AUTHS';
    protected $primaryKey = 'ID';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'REPORT_ID',
        'AUTHORITY_ID',
    ];

    public function recieveReport()
    {
        return $this->belongsTo(RecieveReport::class, 'REPORT_ID', 'ID');
    }

}