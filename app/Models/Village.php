<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    protected $table = 'VILLAGE';
    protected $primaryKey = 'VILLAGE_ID';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'VILLAGE_NAME',
        'LOCAL_UNIT',
        'CITY_ID',
        'FOREIGN_VILLAGE_ID',
        'X_AXIS',
        'Y_AXIS',
        'VILLAGE_SORT',
    ];

    public function city()
    {
        return $this->belongsTo(City::class, 'CITY_ID', 'CITY_ID');
    }

    public function recieveReports()
    {
        return $this->hasMany(RecieveReport::class, 'VILLAGE', 'VILLAGE_ID');
    }

}