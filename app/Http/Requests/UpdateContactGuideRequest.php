<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactGuideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_name' => [
                'required',
                'string',
                'unique:contact_guides,department_name,' . $this->route('contactGuide')->id,
            ],
            'manager_name' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'landline_number' => ['nullable', 'string', 'max:50'],
            'additional_phone' => ['nullable', 'string', 'max:50'],
        ];
    }
}