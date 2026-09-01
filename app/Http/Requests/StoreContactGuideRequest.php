<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactGuideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_name' => ['required', 'string', 'max:255', 'unique:contact_guides,department_name'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'landline_number' => ['nullable', 'string', 'max:50'],
            'additional_phone' => ['nullable', 'string', 'max:50'],
        ];
    }
}