<?php

namespace App\Livewire\Evaluations;

use App\Models\EvaluationEntity;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class EntityManager extends Component
{
    use WithPagination;

    public ?int $editingId = null;

    public string $name = '';
    public string $code = '';
    public string $notes = '';
    public bool $is_active = true;

    public bool $showForm = false;

    protected function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'code'      => 'nullable|string|max:50|unique:evaluation_entities,code,' . $this->editingId,
            'notes'     => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function mount(): void
    {
        $this->authorize('manage-evaluation-entities');
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $entity = EvaluationEntity::findOrFail($id);

        $this->editingId  = $entity->id;
        $this->name       = $entity->name;
        $this->code       = $entity->code ?? '';
        $this->notes      = $entity->notes ?? '';
        $this->is_active  = $entity->is_active;
        $this->showForm   = true;
    }

    public function save(): void
    {
        $this->authorize('manage-evaluation-entities');
        $validated = $this->validate();

        EvaluationEntity::updateOrCreate(
            ['id' => $this->editingId],
            $validated
        );

        session()->flash('success', $this->editingId ? 'تم تعديل الجهة بنجاح' : 'تم إضافة الجهة بنجاح');

        $this->resetForm();
        $this->showForm = false;
    }

    public function delete(int $id): void
    {
        $this->authorize('manage-evaluation-entities');

        EvaluationEntity::findOrFail($id)->delete();

        session()->flash('success', 'تم حذف الجهة');
    }

    public function toggleActive(int $id): void
    {
        $entity = EvaluationEntity::findOrFail($id);
        $entity->update(['is_active' => ! $entity->is_active]);
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'code', 'notes']);
        $this->is_active = true;
    }

    public function render()
    {
        return view('livewire.evaluations.entity-manager', [
            'entities' => EvaluationEntity::orderBy('name')->paginate(15),
        ]);
    }
}
