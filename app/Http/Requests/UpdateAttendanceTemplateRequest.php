<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceTemplateRequest extends FormRequest
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