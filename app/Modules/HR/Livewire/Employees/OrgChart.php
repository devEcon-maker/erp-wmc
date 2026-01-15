<?php

namespace App\Modules\HR\Livewire\Employees;

use App\Modules\HR\Models\Department;
use Livewire\Component;

class OrgChart extends Component
{
    public function render()
    {
        // Fetch departments with employees, nested structure if parent_id exists
        // Implementing a simple visualization grouping by Department for now
        $departments = Department::with([
            'employees' => function ($q) {
                $q->where('status', 'active');
            },
            'manager'
        ])->get();

        return view('hr.employees.org-chart', [
            'departments' => $departments
        ])->layout('layouts.app');
    }
}
