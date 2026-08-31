<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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


    public function manager(): BelongsTo
{
    return $this->belongsTo(User::class, 'manager_id');
}

public function subordinates(): HasMany
{
    return $this->hasMany(User::class, 'manager_id');
}

/** كل مرؤوسي هذا المستخدم بشكل متسلسل (مرؤوسين المرؤوسين... إلخ) */
public function allSubordinateIds(): array
{
    $ids = [];
    $queue = $this->subordinates()->pluck('id')->all();

    while (! empty($queue)) {
        $ids = array_merge($ids, $queue);
        $queue = User::whereIn('manager_id', $queue)->pluck('id')->all();
    }

    return $ids;
}

public function assignedTasks(): HasMany
{
    return $this->hasMany(TaskAssignment::class, 'assignee_id');
}

public function createdTasks(): HasMany
{
    return $this->hasMany(TaskAssignment::class, 'created_by');
}

// ---- Helpers للأدوار ----

public function isAdmin(): bool
{
    return $this->role === 'admin';
}

public function isAssignmentManager(): bool
{
    return $this->role === 'assignment_manager';
}

public function isManager(): bool
{
    return in_array($this->role, ['manager', 'director', 'admin']);
}

/**
 * الاستعلام الأساسي: أي التكليفات يقدر هذا المستخدم يشوفها،
 * حسب دوره في الهيكل الإداري.
 */
public function visibleTasksQuery()
{
    $query = TaskAssignment::query();

    return match ($this->role) {
        'admin', 'director', 'assignment_manager' => $query, // كل التكليفات
        'manager' => $query->whereIn('assignee_id', array_merge([$this->id], $this->allSubordinateIds())),
        'supervisor' => $query->where('entity_id', $this->entity_id),
        default => $query->where('assignee_id', $this->id), // staff: تكليفاته هو بس
    };
}
}