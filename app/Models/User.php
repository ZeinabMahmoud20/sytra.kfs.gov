<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * الأعمدة المسموح تعبئتها جماعياً (Mass Assignment).
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_full_name',
        'national_id',
        'phone_number',
        'job_title',
        'job_grade',
        'user_type',
        'picture_path',
    ];

    /**
     * الأعمدة اللي متتخفيش وقت التحويل لـ Array/JSON.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * تحويل أنواع الأعمدة تلقائياً.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * علاقة: البلاغات اللي المستخدم ده هو متلقيها.
     */
    public function recieveReports()
    {
        return $this->hasMany(RecieveReport::class, 'REPORT_RECIPIENT', 'id');
    }
}