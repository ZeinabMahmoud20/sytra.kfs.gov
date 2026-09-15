<?php

namespace App\Livewire\Evaluations;

use App\Models\DailyScript;
use App\Models\Evaluation;
use App\Models\EvaluationEntity;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DailyEvaluationForm extends Component
{
    public string $date;

    /** @var array<int, string> entity_id => response_type المختار */
    public array $responses = [];

    public bool $isFriday = false;

    public string $script = '';

    public bool $editingScript = false;

    public function mount(): void
    {
        $this->authorize('evaluations.evaluate');
        $this->date = now()->toDateString();
        $this->script = DailyScript::query()->value('content') ?? '';
        $this->loadDay();
    }

    public function startEditingScript(): void
    {
        $this->editingScript = true;
    }

    public function cancelEditingScript(): void
    {
        $this->editingScript = false;
        $this->script = DailyScript::query()->value('content') ?? '';
    }

    public function saveScript(): void
    {
        $this->authorize('evaluations.evaluate');

        $this->validate([
            'script' => 'nullable|string',
        ]);

        $record = DailyScript::query()->first() ?? new DailyScript;
        $record->content = $this->script;
        $record->save();

        $this->editingScript = false;
        session()->flash('success', 'تم حفظ النص');
    }

    public function updatedDate(): void
    {
        $this->loadDay();
    }

    private function loadDay(): void
    {
        $this->isFriday = Carbon::parse($this->date)->isFriday();

        $existing = Evaluation::query()
            ->where('evaluation_date', $this->date)
            ->pluck('response_type', 'evaluation_entity_id');

        $this->responses = [];
        foreach ($existing as $entityId => $type) {
            $this->responses[$entityId] = $type;
        }
    }

    public function save(int $entityId, string $responseType): void
    {
        $this->authorize('evaluations.evaluate');

        if ($this->isFriday) {
            $this->addError('date', 'لا يوجد  تقييم يوم الجمعة');
            return;
        }

        // لو فيه تقييم مسجل بالفعل لنفس الجهة في نفس اليوم، منسمحش بالتكرار
        $already = Evaluation::where('evaluation_entity_id', $entityId)
            ->where('evaluation_date', $this->date)
            ->exists();

        if ($already) {
            session()->flash('error', 'تم تقييم هذه الجهة بالفعل من قبل، يتم تسجيل أول تقييم فقط ');
            return;
        }

        Evaluation::create([
            'evaluation_entity_id' => $entityId,
            'evaluated_by'         => auth()->id(),
            'evaluation_date'      => $this->date,
            'response_type'        => $responseType,
        ]);

        $this->responses[$entityId] = $responseType;

        session()->flash('success', 'تم تسجيل التقييم');
    }

    public function render()
    {
        return view('livewire.evaluations.daily-evaluation-form', [
            'entities'   => EvaluationEntity::active()->orderBy('name')->get(),
            'responseTypes' => Evaluation::LABELS,
        ]);
    }
}
