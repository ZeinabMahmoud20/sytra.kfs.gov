<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'attendance_time' => ['required', 'date_format:H:i'],
            'script' => ['required', 'string'],
            'daily_entities_count' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],

            'entity_ids' => ['required', 'array', 'min:1'],
            'entity_ids.*' => ['exists:entities,id'],
        ];
    }

    /**
     * التأكد إن daily_entities_count منطقي مقارنة بعدد الجهات المختارة للـ Template.
     * مينفعش نطلب اختيار 10 جهات يوميًا وإحنا مربوطين بـ 5 جهات بس.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $entityIds = $this->input('entity_ids', []);
            $dailyCount = (int) $this->input('daily_entities_count');

            if ($dailyCount > count($entityIds)) {
                $validator->errors()->add(
                    'daily_entities_count',
                    'عدد الجهات اليومية لا يمكن أن يكون أكبر من عدد الجهات المرتبطة بالتمام (' . count($entityIds) . ')'
                );
            }
        });
    }
}