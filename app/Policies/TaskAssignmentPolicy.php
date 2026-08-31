<?php

namespace App\Policies;

use App\Models\TaskAssignment;
use App\Models\User;

class TaskAssignmentPolicy
{
    /** مين يقدر يشوف قائمة التكليفات (كلهم، لكن كل واحد بيشوف نطاقه بس عبر visibleTasksQuery) */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TaskAssignment $task): bool
    {
        if (in_array($user->role, ['admin', 'director', 'assignment_manager'])) {
            return true;
        }

        if ($user->role === 'manager') {
            return $task->assignee_id === $user->id
                || in_array($task->assignee_id, $user->allSubordinateIds());
        }

        if ($user->role === 'supervisor') {
            return $task->entity_id === $user->entity_id;
        }

        return $task->assignee_id === $user->id;
    }

    /** إضافة تكليف جديد - مسؤول التكليفات والإدارة العليا فقط */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'assignment_manager', 'director']);
    }

    /**
     * تعديل بيانات التكليف بالكامل (الأولوية/المسؤول/الموعد النهائي/الجهة).
     * حسب اتفاقنا: الموظف + مسؤول التكليفات + المسؤول الإداري.
     */
    public function update(User $user, TaskAssignment $task): bool
    {
        if (in_array($user->role, ['admin', 'assignment_manager', 'director'])) {
            return true;
        }

        // الموظف صاحب التكليف يقدر يعدّل (الحالة/النسبة/الملاحظات - محكوم في الـ Request)
        return $task->assignee_id === $user->id;
    }

    /** تحديث الحالة/النسبة/الملاحظات فقط - متاح لصاحب التكليف */
    public function updateStatus(User $user, TaskAssignment $task): bool
    {
        return $this->update($user, $task);
    }

    public function delete(User $user, TaskAssignment $task): bool
    {
        return in_array($user->role, ['admin', 'assignment_manager']);
    }
}
