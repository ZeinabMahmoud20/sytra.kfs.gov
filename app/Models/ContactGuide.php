<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactGuide extends Model
{
    protected $fillable = [
        'department_name',
        'manager_name',
        'phone_number',
        'landline_number',
        'additional_phone',
    ];
}