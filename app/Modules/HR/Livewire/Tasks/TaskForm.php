<?php

namespace App\Modules\HR\Livewire\Tasks;

use App\Modules\HR\Models\Task;
use App\Modules\HR\Models\TaskStatus;
use App\Modules\HR\Models\Employee;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class TaskForm extends Component
{
    public ?Task $task = null;
    public bool $isEdit = false;

    // Champs du formulaire
    public $title = '';
    public $description = '';
    public $status_id = '';
    public $employee_id = '';
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
            'employee_id' => 'required|exists:employees,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ];
    }

    protected $messages = [
        'title.required' => 'Le titre est obligatoire.',
        'status_id.required' => 'Le statut est obligatoire.',
        'employee_id.required' => 'L\'employe est obligatoire.',
    ];

    public function mount(?Task $task = null)
    {
        if ($task && $task->exists) {
            $this->task = $task;
            $this->isEdit = true;
            $this->fill([
                'title' => $task->title,
                'description' => $task->description,
                'status_id' => $task->status_id,
                'employee_id' => $task->employee_id,
                'priority' => $task->priority,
                'due_date' => $task->due_date?->format('Y-m-d'),
                'estimated_hours' => $task->estimated_hours,
                'notes' => $task->notes,
            ]);
        } else {
            // Valeurs par defaut
            $defaultStatus = TaskStatus::getDefault();
            $this->status_id = $defaultStatus?->id ?? '';

            // Si l'utilisateur connecte est un employe, pre-selectionner
            $currentEmployee = Auth::user()?->employee;
            if ($currentEmployee) {
                $this->employee_id = $currentEmployee->id;
            }
        }
    }

    public function save()
    {
        $validated = $this->validate();

        // Ajouter assigned_by si c'est une nouvelle tache
        if (!$this->isEdit) {
            $validated['assigned_by'] = Auth::id();
        }

        if ($this->isEdit) {
            $this->task->update($validated);
            $message = 'Tache mise a jour avec succes.';
        } else {
            Task::create($validated);
            $message = 'Tache creee avec succes.';
        }

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => $message
        ]);

        return $this->redirect(route('hr.tasks.index'), navigate: true);
    }

    public function render()
    {
        $statuses = TaskStatus::orderBy('order')->get();
        $employees = Employee::active()->orderBy('last_name')->get();

        return view('hr::livewire.tasks.task-form', [
            'statuses' => $statuses,
            'employees' => $employees,
            'priorities' => Task::PRIORITIES,
        ])->layout('layouts.app');
    }
}
