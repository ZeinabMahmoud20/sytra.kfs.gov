<?php

namespace App\Imports;

use App\Models\ContactGuide;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class ContactGuidesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithUpserts
{
    use SkipsFailures;

    private array $seenNames = [];

    public function uniqueBy(): array
    {
        return ['department_name'];
    }

    public function model(array $row): ?ContactGuide
    {
        $name = trim((string) ($row['department_name'] ?? ''));

        if ($this->isDuplicateWithinFile($name)) {
            return null;
        }

        return new ContactGuide([
            'department_name' => $name,
            'manager_name' => $row['manager_name'] ?? null,
            'phone_number' => isset($row['phone_number']) ? (string) $row['phone_number'] : null,
            'landline_number' => isset($row['landline_number']) ? (string) $row['landline_number'] : null,
            'additional_phone' => isset($row['additional_phone']) ? (string) $row['additional_phone'] : null,
        ]);
    }

    private function isDuplicateWithinFile(string $name): bool
    {
        if ($name === '' || in_array($name, $this->seenNames, true)) {
            return true;
        }

        $this->seenNames[] = $name;

        return false;
    }

    public function rules(): array
    {
        return [
            'department_name' => ['required', 'string'],
            'manager_name' => ['nullable', 'string'],
            'phone_number' => ['nullable'],
            'landline_number' => ['nullable'],
            'additional_phone' => ['nullable'],
        ];
    }
}