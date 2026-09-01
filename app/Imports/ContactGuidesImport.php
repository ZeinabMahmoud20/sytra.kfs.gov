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
            'phone_number' => $row['phone_number'] ?? null,
            'landline_number' => $row['landline_number'] ?? null,
            'additional_phone' => $row['additional_phone'] ?? null,
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
            'department_name' => ['required', 'string', 'max:255'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'landline_number' => ['nullable', 'string', 'max:50'],
            'additional_phone' => ['nullable', 'string', 'max:50'],
        ];
    }
}