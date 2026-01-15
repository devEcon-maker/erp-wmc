<?php

namespace App\Modules\HR\Livewire\Employees;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\Department;
use App\Models\User;
use Livewire\Component;
use Illuminate\Validation\Rule;

class EmployeeForm extends Component
{
    public ?Employee $employee = null;

    // Personal Info
    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $phone = '';
    public $birth_date = '';

    // Job Info
    public $employee_number = ''; // Auto-generated
    public $hire_date = '';
    public $end_date = '';
    public $job_title = '';
    public $department_id = '';
    public $manager_id = '';
    public $contract_type = 'cdi';
    public $status = 'active';

    // Sensitive
    public $salary = '';

    // User Account
    public $user_id = '';
    public $user_action = 'none'; // none, link, create

    public function mount(Employee $employee = null)
    {
        if ($employee && $employee->exists) {
            $this->employee = $employee;
            $this->fill($employee->only([
                'first_name',
                'last_name',
                'email',
                'phone',
                'employee_number',
                'job_title',
                'department_id',
                'manager_id',
                'contract_type',
                'status',
                'salary'
            ]));
            $this->birth_date = $employee->birth_date?->format('Y-m-d');
            $this->hire_date = $employee->hire_date?->format('Y-m-d');
            $this->end_date = $employee->end_date?->format('Y-m-d');
            $this->user_id = $employee->user_id ?? '';
            $this->user_action = $employee->user_id ? 'linked' : 'none';
        } else {
            $this->hire_date = date('Y-m-d');
            $this->contract_type = 'cdi';
            $this->status = 'active';
        }
    }

    public function updatedUserAction($value)
    {
        if ($value !== 'link') {
            $this->user_id = '';
        }
        // Clear name/email when switching away from link mode
        if ($value === 'link') {
            $this->first_name = '';
            $this->last_name = '';
            $this->email = '';
        }
    }

    public function updatedUserId($value)
    {
        // Auto-fill name and email from selected user
        if ($value && $this->user_action === 'link') {
            $user = User::find($value);
            if ($user) {
                // Parse name into first and last name
                $nameParts = explode(' ', $user->name, 2);
                $this->first_name = $nameParts[0] ?? '';
                $this->last_name = $nameParts[1] ?? '';
                $this->email = $user->email;
            }
        }
    }

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('employees', 'email')->ignore($this->employee?->id)],
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'hire_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:hire_date',
            'job_title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'manager_id' => 'nullable|exists:employees,id',
            'contract_type' => 'required|in:cdi,cdd,interim,stage,alternance',
            'status' => 'required|in:active,inactive,terminated',
            'salary' => 'nullable|numeric|min:0',
        ];
    }

    public function save()
    {
        $this->validate();

        // Validate user linking if selected
        if ($this->user_action === 'link' && empty($this->user_id)) {
            $this->addError('user_id', 'Veuillez selectionner un utilisateur.');
            return;
        }

        // Sanitize data: Convert empty strings to null for nullable fields
        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'birth_date' => $this->birth_date ?: null,
            'hire_date' => $this->hire_date,
            'end_date' => $this->end_date ?: null,
            'job_title' => $this->job_title,
            'department_id' => $this->department_id,
            'manager_id' => $this->manager_id ?: null,
            'contract_type' => $this->contract_type,
            'status' => $this->status,
            'salary' => ($this->salary === '' || $this->salary === null) ? null : $this->salary,
        ];

        // Handle user linking
        if ($this->user_action === 'link' && $this->user_id) {
            $data['user_id'] = $this->user_id;
        } elseif ($this->user_action === 'none') {
            $data['user_id'] = null;
        }

        if ($this->employee) {
            $this->employee->update($data);

            // Update linked user email if exists
            if ($this->employee->user) {
                $this->employee->user->update(['email' => $data['email']]);
            }

            $message = 'Employe mis a jour avec succes.';
        } else {
            $employee = Employee::create($data);
            $this->employee = $employee;

            if ($this->user_action === 'create') {
                // Create a new user account
                $user = User::create([
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'email' => $data['email'],
                    'password' => bcrypt('password'), // Default password - should be changed
                ]);
                // Assign default employee role
                $user->assignRole('employe');

                $employee->user_id = $user->id;
                $employee->save();

                $message = 'Employe cree avec succes. Compte utilisateur cree (mot de passe: password).';
            } else {
                $message = 'Employe cree avec succes.';
            }
        }

        $this->dispatch('notify', type: 'success', message: $message);
        return redirect()->route('hr.employees.index');
    }

    public function render()
    {
        // Get users that are not already linked to an employee (except current employee's user)
        $availableUsers = User::whereDoesntHave('employee', function ($query) {
            if ($this->employee) {
                $query->where('id', '!=', $this->employee->id);
            }
        })->orderBy('name')->get();

        return view('hr.employees.employee-form', [
            'departments' => Department::orderBy('name')->get(),
            'managers' => Employee::active()->where('id', '!=', $this->employee?->id)->orderBy('last_name')->get(),
            'availableUsers' => $availableUsers,
        ])->layout('layouts.app');
    }
}
