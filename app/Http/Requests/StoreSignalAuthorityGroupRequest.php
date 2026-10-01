<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSignalAuthorityGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'GROUP_NAME' => ['required', 'string', 'max:100', Rule::unique('SIGNAL_AUTHORITY_GROUP', 'GROUP_NAME')],
            'authorities' => ['nullable', 'array'],
            'authorities.*' => ['integer', 'exists:SIGNAL_AUTHORITY,ID'],
        ];
    }

    public function messages(): array
    {
        return [
            'GROUP_NAME.unique' => 'اسم المجموعة مستخدم بالفعل',
            'authorities.*.exists' => 'إحدى الجهات المختارة غير موجودة',
        ];
    }
}
