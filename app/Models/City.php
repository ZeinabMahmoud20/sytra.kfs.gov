<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'CITY';
    protected $primaryKey = 'CITY_ID';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'CITY_NAME',
    ];

    public function villages()
    {
        return $this->hasMany(Village::class, 'CITY_ID', 'CITY_ID');
    }

    public function recieveReports()
    {
        return $this->hasMany(RecieveReport::class, 'CITY', 'CITY_ID');
    }

}