<?php

namespace App\Modules\HR\Livewire\Leaves;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\LeaveRequest;
use Livewire\Component;
use Livewire\WithPagination;

class LeaveRequestsList extends Component
{
    use WithPagination;

    public function cancel($id)
    {
        $request = LeaveRequest::findOrFail($id);

        if ($request->status !== 'pending') {
            return;
        }

        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if ($employee && $request->employee_id === $employee->id) {
            $request->update(['status' => 'cancelled']);
            session()->flash('success', 'Demande annulée avec succès.');
        }
    }

    public function render()
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        $requests = $employee
            ? LeaveRequest::with('leaveType')
                ->where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10)
            : [];

        return view('hr.leaves.leave-requests-list', [
            'requests' => $requests
        ])->layout('layouts.app');
    }
}
