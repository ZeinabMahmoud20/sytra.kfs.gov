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
            'second_attendance_time' => ['nullable', 'date_format:H:i'],
            'script' => ['required', 'string'],
            'daily_entities_count' => ['required', 'integer', 'min:1'],
            'frequency' => ['required', 'string', 'in:daily,twice_daily,weekly,monthly,custom_dates'],
            'frequency_config' => ['nullable', 'array'],
            'frequency_config.days_of_week' => ['nullable', 'array'],
            'frequency_config.days_of_week.*' => ['integer', 'in:0,1,2,3,4,5,6'],
            'frequency_config.day_of_month' => ['nullable', 'integer', 'min:1', 'max:31'],
            'frequency_config.custom_dates' => ['nullable', 'array'],
            'frequency_config.custom_dates.*' => ['date'],
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

            $frequency = $this->input('frequency');

            if ($frequency === 'twice_daily' && empty($this->input('second_attendance_time'))) {
                $validator->errors()->add(
                    'second_attendance_time',
                    'يجب إدخال موعد التمام الثاني عند اختيار مرتين في اليوم'
                );
            }

            if ($frequency === 'weekly' && empty($this->input('frequency_config.days_of_week'))) {
                $validator->errors()->add(
                    'frequency_config.days_of_week',
                    'يجب اختيار يوم أو أكثر من أيام الأسبوع'
                );
            }

            if ($frequency === 'monthly' && empty($this->input('frequency_config.day_of_month'))) {
                $validator->errors()->add(
                    'frequency_config.day_of_month',
                    'يجب إدخال يوم التمام في الشهر'
                );
            }

            if ($frequency === 'custom_dates' && empty($this->input('frequency_config.custom_dates'))) {
                $validator->errors()->add(
                    'frequency_config.custom_dates',
                    'يجب اختيار تواريخ محددة'
                );
            }
        });
    }
}