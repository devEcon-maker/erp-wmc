<?php

namespace App\Modules\HR\Livewire\Tasks;

use App\Modules\HR\Models\Task;
use App\Modules\HR\Models\TaskStatus;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class MyTaskEdit extends Component
{
    public Task $task;

    // Champs du formulaire
    public $title = '';
    public $description = '';
    public $status_id = '';
    public $priority = 'medium';
    public $due_date = '';
    public $estimated_hours = '';
    public $notes = '';

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'required|exists:task_statuses,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ];
    }

    protected $messages = [
        'title.required' => 'Le titre est obligatoire.',
        'status_id.required' => 'Le statut est obligatoire.',
    ];

    public function mount(Task $task)
    {
        // Verifier que l'utilisateur est assigne a cette tache
        $currentEmployee = Auth::user()?->employee;

        if (!$currentEmployee) {
            abort(403, 'Vous devez etre associe a un employe.');
        }

        $isAssigned = $task->employee_id == $currentEmployee->id
            || $task->assignees()->where('employees.id', $currentEmployee->id)->exists();

        if (!$isAssigned) {
            abort(403, 'Vous n\'etes pas assigne a cette tache.');
        }

        $this->task = $task;
        $this->fill([
            'title' => $task->title,
            'description' => $task->description,
            'status_id' => $task->status_id,
            'priority' => $task->priority,
            'due_date' => $task->due_date?->format('Y-m-d'),
            'estimated_hours' => $task->estimated_hours,
            'notes' => $task->notes,
        ]);
    }

    public function save()
    {
        $validated = $this->validate();

        // Convertir les chaines vides en null pour les champs optionnels
        $validated['description'] = $validated['description'] ?: null;
        $validated['due_date'] = $validated['due_date'] ?: null;
        $validated['estimated_hours'] = $validated['estimated_hours'] ?: null;
        $validated['notes'] = $validated['notes'] ?: null;

        $this->task->update($validated);

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Tache mise a jour avec succes.'
        ]);

        return $this->redirect(route('hr.my-tasks.show', $this->task), navigate: true);
    }

    public function render()
    {
        $statuses = TaskStatus::orderBy('order')->get();

        return view('hr::livewire.tasks.my-task-edit', [
            'statuses' => $statuses,
            'priorities' => Task::PRIORITIES,
        ])->layout('layouts.app');
    }
}
