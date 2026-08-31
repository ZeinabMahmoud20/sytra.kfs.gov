<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:task_entities,name'],
            'type' => ['required', 'string', 'in:مركز,مدينة,إدارة,مديرية,أخرى'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
