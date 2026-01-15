<?php

namespace App\Modules\HR\Livewire\Leaves;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\HR\Services\LeaveService;
use Livewire\Component;
use Livewire\WithPagination;

class LeaveApproval extends Component
{
    use WithPagination;

    public function approve($id)
    {
        $request = LeaveRequest::findOrFail($id);
        $user = auth()->user();
        $approver = Employee::where('user_id', $user->id)->first();

        if ($approver) {
            $service = new LeaveService();
            $service->approveRequest($request, $approver);

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Demande de {$request->employee->full_name} approuvée."
            ]);
        }
    }

    public function reject($id)
    {
        $request = LeaveRequest::findOrFail($id);
        $user = auth()->user();
        $approver = Employee::where('user_id', $user->id)->first();

        if ($approver) {
            $service = new LeaveService();
            $service->rejectRequest($request, $approver, 'Rejected by manager');

            $this->dispatch('toast', [
                'type' => 'warning',
                'message' => "Demande de {$request->employee->full_name} refusée."
            ]);
        }
    }

    public function render()
    {
        $user = auth()->user();
        $currentEmployee = Employee::where('user_id', $user->id)->first();

        $query = LeaveRequest::with(['employee', 'leaveType'])
            ->pending()
            ->orderBy('created_at', 'asc');

        // Si l'utilisateur a la permission leaves.approve (admin/RH), il voit toutes les demandes
        // Sinon, il ne voit que les demandes de ses subordonnes
        if (!$user->can('leaves.approve') && $currentEmployee) {
            // Manager: voir seulement les demandes de ses subordonnes
            $query->whereHas('employee', function ($q) use ($currentEmployee) {
                $q->where('manager_id', $currentEmployee->id);
            });
        } elseif ($currentEmployee) {
            // Admin/RH: exclure ses propres demandes
            $query->where('employee_id', '!=', $currentEmployee->id);
        }

        $pendingRequests = $query->paginate(10);

        return view('livewire.hr.leaves.leave-approval', [
            'pendingRequests' => $pendingRequests
        ])->layout('layouts.app');
    }
}
