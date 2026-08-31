<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // updateStatus بتغطي الحالتين: صاحب التعديل الكامل (creator/admin)
        // والموظف المكلف اللي بيحدّث حالة تنفيذه بس.
        return $this->user()->can('updateStatus', $this->route('task'));
    }

    public function rules(): array
    {
        $user = $this->user();
        $task = $this->route('task');

        // الموظف المكلف بالتنفيذ (مش صاحب صلاحية التعديل الكامل) يعدّل
        // الحالة/النسبة/موعد الرد/الملاحظات بس.
        if (! $user->can('update', $task)) {
            return [
                'status'                 => ['required', 'in:لم يبدأ,جاري التنفيذ,تم التنفيذ,متأخر,متوقف'],
                'completion_percentage'  => ['required', 'integer', 'min:0', 'max:100'],
                'response_date'          => ['nullable', 'date'],
                'notes'                  => ['nullable', 'string', 'max:2000'],
            ];
        }

        // صاحب التكليف (اللي أضافه) أو الـ admin: يقدروا يعدّلوا كل حاجة
        return [
            'document_type'          => ['required', 'in:وارد,صادر'],
            'source_id'              => ['required', 'exists:task_sources,id'],
            'entity_id'              => ['required', 'exists:task_entities,id'],
            'assignee_id'            => ['required', 'exists:users,id'],
            'description'            => ['required', 'string', 'max:5000'],
            'priority'               => ['required', 'in:عالية,متوسطة,منخفضة'],
            'deadline'               => ['required', 'date'],
            'response_date'          => ['nullable', 'date'],
            'status'                 => ['required', 'in:لم يبدأ,جاري التنفيذ,تم التنفيذ,متأخر,متوقف'],
            'completion_percentage'  => ['required', 'integer', 'min:0', 'max:100'],
            'notes'                  => ['nullable', 'string', 'max:2000'],
            'edit_note'              => ['nullable', 'string', 'max:500'],
        ];
    }
}