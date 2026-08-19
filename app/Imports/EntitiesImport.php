<?php

namespace App\Imports;

use App\Models\Entity;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class EntitiesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    public function model(array $row): ?Entity
    {
        // تجاهل لو الجهة موجودة بالفعل (منع التكرار عند إعادة الرفع)
        if (Entity::where('name', $row['name'])->exists()) {
            return null;
        }

        return new Entity([
            'name' => $row['name'],
            'main_location' => $row['main_location'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'main_location' => ['nullable', 'string', 'max:255'],
        ];
    }
}