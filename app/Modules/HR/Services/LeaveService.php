<?php

namespace App\Modules\HR\Services;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\LeaveRequest;
use App\Modules\HR\Models\LeaveBalance;
use App\Modules\HR\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class LeaveService
{
    /**
     * Calculate number of working days between two dates.
     * Excludes weekends (Sat, Sun). Holidays implementation pending.
     */
    public function calculateDaysCount($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $days = 0;
        while ($start->lte($end)) {
            if (!$start->isWeekend()) {
                $days++;
            }
            $start->addDay();
        }

        return $days;
    }

    /**
     * Submit a new leave request.
     */
    public function submitRequest(Employee $employee, array $data)
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        $daysCount = $this->calculateDaysCount($startDate, $endDate);
        $currentYear = $startDate->year;

        $leaveType = LeaveType::findOrFail($data['leave_type_id']);

        // Check overlapping requests
        $exists = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', '!=', 'rejected')
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'start_date' => 'Une demande de congé existe déjà sur cette période.'
            ]);
        }

        // Check balance if required
        if ($leaveType->requires_approval) {
            // Logic for balance check can be here or in canRequest.
            // For now, assuming basic check against allocated balance if tracking is implemented in future
        }

        $balance = LeaveBalance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'year' => $currentYear
            ],
            ['allocated' => 0, 'used' => 0]
        );

        // Optional: Check if (allocated - used) >= daysCount
        // Ignoring for now to allow negative balance/advance requests as per common practice unless strict mode

        return LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_count' => $daysCount,
            'reason' => $data['reason'] ?? null,
            'status' => $leaveType->requires_approval ? 'pending' : 'approved',
        ]);
    }

    public function approveRequest(LeaveRequest $request, Employee $approver)
    {
        if ($request->status !== 'pending') {
            return;
        }

        $request->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
        ]);

        // Update balance
        $balance = LeaveBalance::firstOrCreate(
            [
                'employee_id' => $request->employee_id,
                'leave_type_id' => $request->leave_type_id,
                'year' => $request->start_date->year
            ],
            ['allocated' => 0, 'used' => 0]
        );

        $balance->increment('used', $request->days_count);
    }

    public function rejectRequest(LeaveRequest $request, Employee $approver, string $reason)
    {
        if ($request->status !== 'pending') {
            return;
        }

        $request->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
            'rejection_reason' => $reason,
        ]);
    }
}
