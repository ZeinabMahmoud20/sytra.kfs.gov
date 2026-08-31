<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:task_entities,name,' . $this->route('taskEntity')->id,
            ],
            'type' => ['required', 'string', 'in:مركز,مدينة,إدارة,مديرية,أخرى'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
