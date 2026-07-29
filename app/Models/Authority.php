<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Authority extends Model
{
    protected $table = 'AUTHORITY';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'NAME',
        'CODE',
    ];

    public function recieveSignals()
    {
        return $this->hasMany(RecieveSignal::class, 'AUTHORITY_ID', 'ID');
    }

}