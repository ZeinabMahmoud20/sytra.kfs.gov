<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permession extends Model
{
    protected $table = 'PERMESSION_TBL';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'PER_NAME',
        'USER_ID',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'USER_ID', 'USER_ID');
    }

}