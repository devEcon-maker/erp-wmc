<?php

namespace App\Modules\HR\Livewire\Employees;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeesList extends Component
{
    use WithPagination;

    public $search = '';
    public $departmentId = '';
    public $status = '';
    public $contractType = '';

    // Pour la suppression
    public $showDeleteModal = false;
    public $employeeToDelete = null;

    public function confirmDelete($employeeId)
    {
        $this->employeeToDelete = Employee::find($employeeId);
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->employeeToDelete = null;
    }

    public function deleteEmployee()
    {
        if ($this->employeeToDelete) {
            $name = $this->employeeToDelete->full_name;
            $this->employeeToDelete->delete();

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Employé {$name} supprimé avec succès."
            ]);
        }

        $this->showDeleteModal = false;
        $this->employeeToDelete = null;
    }

    public function render()
    {
        $employees = Employee::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('employee_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->departmentId, function ($query) {
                $query->where('department_id', $this->departmentId);
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->contractType, function ($query) {
                $query->where('contract_type', $this->contractType);
            })
            ->with(['department', 'manager'])
            ->orderBy('last_name')
            ->paginate(25);

        $departments = Department::orderBy('name')->get();

        return view('hr.employees.employees-list', [
            'employees' => $employees,
            'departments' => $departments,
        ])->layout('layouts.app');
    }
}
