<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportingTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'REPORT_SORT' => ['required', 'string', 'max:500'],
            'AUTHORITY' => ['required', 'string', 'max:500'],
            'IS_INTERNET' => ['sometimes', 'boolean'],
        ];
    }
}