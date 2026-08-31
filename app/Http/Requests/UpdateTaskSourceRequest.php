<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskSourceRequest extends FormRequest
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
                'unique:task_sources,name,' . $this->route('taskSource')->id,
            ],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
