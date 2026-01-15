<?php

namespace App\Modules\HR\Livewire\Leaves;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\LeaveBalance;
use App\Modules\HR\Models\LeaveType;
use Livewire\Component;

class LeaveBalances extends Component
{
    public function render()
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        $balances = [];

        if ($employee) {
            $currentYear = date('Y');

            // Get all types that are tracking balances
            $leaveTypes = LeaveType::all();

            foreach ($leaveTypes as $type) {
                if ($type->days_per_year > 0) {
                    $balance = LeaveBalance::firstOrCreate(
                        ['employee_id' => $employee->id, 'leave_type_id' => $type->id, 'year' => $currentYear],
                        ['allocated' => $type->days_per_year, 'used' => 0]
                    );
                    $balances[] = $balance;
                }
            }
        }

        return view('hr.leaves.leave-balances', [
            'balances' => $balances,
            'hasEmployeeProfile' => !!$employee
        ]);
    }
}
