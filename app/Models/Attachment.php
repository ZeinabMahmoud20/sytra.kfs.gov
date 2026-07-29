<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $table = 'Attachment_Tbl';
    protected $primaryKey = 'AttachmentID';
    public $timestamps = false;

    protected $fillable = [
        'AttachmentName',
        'ReportID',
        'FilePath',
        'FileExtension',
    ];

    public function recieveReport()
    {
        return $this->belongsTo(RecieveReport::class, 'ReportID', 'ID');
    }

}