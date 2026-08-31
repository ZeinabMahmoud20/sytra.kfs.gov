<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\TaskAssignment::class);
    }

    public function rules(): array
    {
        return [
            'received_date' => ['required', 'date'],
            'document_type' => ['required', 'in:وارد,صادر'],
            'document'      => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:102400'],
            'source_id'     => ['required', 'exists:task_sources,id'],
            'entity_id'     => ['required', 'exists:task_entities,id'],
            'assignee_id'   => ['required', 'exists:users,id'],
            'description'   => ['required', 'string', 'max:5000'],
            'priority'      => ['required', 'in:عالية,متوسطة,منخفضة'],
            'deadline'      => ['required', 'date', 'after_or_equal:received_date'],
            'notes'         => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'received_date.required' => 'تاريخ الورود مطلوب',
            'document_type.required' => 'نوع المستند مطلوب',
            'document.mimes'         => 'صيغة المستند غير مدعومة (المسموح: pdf, doc, docx, xls, xlsx, jpg, jpeg, png)',
            'document.max'           => 'حجم المستند يجب ألا يتجاوز 100 ميجابايت',
            'source_id.required'     => 'مصدر التكليف مطلوب',
            'entity_id.required'     => 'الجهة المختصة مطلوبة',
            'assignee_id.required'   => 'يجب اختيار المسؤول عن التنفيذ',
            'description.required'  => 'وصف التكليف مطلوب',
            'deadline.after_or_equal' => 'الموعد النهائي لا يمكن أن يكون قبل تاريخ الورود',
        ];
    }
}