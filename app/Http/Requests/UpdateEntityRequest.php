<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntityRequest extends FormRequest
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
                'unique:entities,name,' . $this->route('entity')->id,
            ],
            'main_location' => ['nullable', 'string', 'max:255'],
        ];
    }
}