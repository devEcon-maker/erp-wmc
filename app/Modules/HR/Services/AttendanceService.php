<?php

namespace App\Modules\HR\Services;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\Attendance;
use App\Modules\HR\Models\LateArrival;
use App\Modules\HR\Models\WorkSchedule;
use App\Modules\HR\Models\PublicHoliday;
use App\Modules\HR\Models\LeaveRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AttendanceService
{
    /**
     * Enregistrer un pointage d'entree
     */
    public function checkIn(Employee $employee, ?string $location = null): Attendance
    {
        $today = now()->toDateString();

        // Verifier si deja pointe
        $existing = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if ($existing && $existing->check_in) {
            throw new \Exception("Vous avez deja effectue votre pointage d'entree aujourd'hui");
        }

        $attendance = $existing ?? new Attendance([
            'employee_id' => $employee->id,
            'date' => $today,
        ]);

        $checkInTime = now()->format('H:i:s');
        $attendance->check_in = $checkInTime;
        $attendance->check_in_location = $location;
        $attendance->status = 'present';

        // Verifier le retard
        $this->checkLateArrival($employee, $attendance, $checkInTime);

        $attendance->save();

        return $attendance;
    }

    /**
     * Enregistrer un pointage de sortie
     */
    public function checkOut(Employee $employee, ?string $location = null): Attendance
    {
        $today = now()->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in) {
            throw new \Exception("Vous devez d'abord effectuer votre pointage d'entree");
        }

        if ($attendance->check_out) {
            throw new \Exception("Vous avez deja effectue votre pointage de sortie aujourd'hui");
        }

        $attendance->check_out = now()->format('H:i:s');
        $attendance->check_out_location = $location;
        $attendance->worked_hours = $attendance->calculateWorkedHours();

        // Verifier si depart anticipe
        $this->checkEarlyDeparture($employee, $attendance);

        // Calculer les heures sup
        $this->calculateOvertime($employee, $attendance);

        $attendance->save();

        return $attendance;
    }

    /**
     * Verifier et enregistrer un retard
     */
    private function checkLateArrival(Employee $employee, Attendance $attendance, string $checkInTime): void
    {
        $schedule = $employee->workSchedule ?? WorkSchedule::getDefault();

        if (!$schedule) {
            return;
        }

        $expectedStart = $schedule->getExpectedStartTime(Carbon::parse($attendance->date));

        if (!$expectedStart) {
            return;
        }

        $lateMinutes = $schedule->calculateLateMinutes($checkInTime, Carbon::parse($attendance->date));

        if ($lateMinutes > 0) {
            $attendance->is_late = true;
            $attendance->late_minutes = $lateMinutes;
            $attendance->status = 'late';

            // Creer un enregistrement de retard
            LateArrival::create([
                'employee_id' => $employee->id,
                'attendance_id' => $attendance->id,
                'date' => $attendance->date,
                'expected_time' => $expectedStart,
                'actual_time' => $checkInTime,
                'minutes_late' => $lateMinutes,
                'status' => 'pending',
            ]);
        }
    }

    /**
     * Verifier un depart anticipe
     */
    private function checkEarlyDeparture(Employee $employee, Attendance $attendance): void
    {
        $schedule = $employee->workSchedule ?? WorkSchedule::getDefault();

        if (!$schedule) {
            return;
        }

        $expectedEnd = $schedule->getExpectedEndTime(Carbon::parse($attendance->date));

        if (!$expectedEnd) {
            return;
        }

        $expected = Carbon::parse($expectedEnd);
        $actual = Carbon::parse($attendance->check_out);

        if ($actual->lt($expected)) {
            $earlyMinutes = $actual->diffInMinutes($expected);
            $attendance->left_early = true;
            $attendance->early_departure_minutes = $earlyMinutes;
        }
    }

    /**
     * Calculer les heures supplementaires
     */
    private function calculateOvertime(Employee $employee, Attendance $attendance): void
    {
        $schedule = $employee->workSchedule ?? WorkSchedule::getDefault();

        if (!$schedule) {
            return;
        }

        $dailyHours = $schedule->daily_hours;

        if ($attendance->worked_hours > $dailyHours) {
            $attendance->overtime_hours = $attendance->worked_hours - $dailyHours;
        }
    }

    /**
     * Initialiser les presences pour une periode
     */
    public function initializeAttendances(Carbon $startDate, Carbon $endDate): int
    {
        $employees = Employee::active()->get();
        $count = 0;

        $current = $startDate->copy();
        while ($current <= $endDate) {
            // Determiner le statut du jour
            $isWeekend = $current->isWeekend();
            $isHoliday = PublicHoliday::isHoliday($current);

            foreach ($employees as $employee) {
                // Verifier si un conge est approuve
                $onLeave = LeaveRequest::where('employee_id', $employee->id)
                    ->where('status', 'approved')
                    ->where('start_date', '<=', $current)
                    ->where('end_date', '>=', $current)
                    ->exists();

                $existing = Attendance::where('employee_id', $employee->id)
                    ->where('date', $current->toDateString())
                    ->exists();

                if (!$existing) {
                    $status = match (true) {
                        $onLeave => 'leave',
                        $isHoliday => 'holiday',
                        $isWeekend => 'weekend',
                        default => 'absent',
                    };

                    Attendance::create([
                        'employee_id' => $employee->id,
                        'date' => $current->toDateString(),
                        'status' => $status,
                    ]);
                    $count++;
                }
            }

            $current->addDay();
        }

        return $count;
    }

    /**
     * Obtenir le rapport de presence d'un employe
     */
    public function getEmployeeReport(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        return [
            'total_days' => $startDate->diffInDays($endDate) + 1,
            'present_days' => $attendances->whereIn('status', ['present', 'late'])->count(),
            'absent_days' => $attendances->where('status', 'absent')->count(),
            'late_count' => $attendances->where('is_late', true)->count(),
            'total_late_minutes' => $attendances->sum('late_minutes'),
            'leave_days' => $attendances->where('status', 'leave')->count(),
            'total_worked_hours' => $attendances->sum('worked_hours'),
            'total_overtime_hours' => $attendances->sum('overtime_hours'),
            'attendance_rate' => $this->calculateAttendanceRate($attendances, $startDate, $endDate),
        ];
    }

    /**
     * Calculer le taux de presence
     */
    private function calculateAttendanceRate(Collection $attendances, Carbon $startDate, Carbon $endDate): float
    {
        $workingDays = 0;
        $current = $startDate->copy();

        while ($current <= $endDate) {
            if (!$current->isWeekend() && !PublicHoliday::isHoliday($current)) {
                $workingDays++;
            }
            $current->addDay();
        }

        if ($workingDays === 0) {
            return 100;
        }

        $presentDays = $attendances->whereIn('status', ['present', 'late', 'remote'])->count();

        return round(($presentDays / $workingDays) * 100, 2);
    }

    /**
     * Obtenir les statistiques de presence par departement
     */
    public function getDepartmentStats(int $departmentId, Carbon $date): array
    {
        $employees = Employee::where('department_id', $departmentId)->active()->get();
        $employeeIds = $employees->pluck('id');

        $attendances = Attendance::whereIn('employee_id', $employeeIds)
            ->where('date', $date->toDateString())
            ->get();

        return [
            'total_employees' => $employees->count(),
            'present' => $attendances->whereIn('status', ['present', 'late', 'remote'])->count(),
            'absent' => $employees->count() - $attendances->whereIn('status', ['present', 'late', 'remote', 'leave'])->count(),
            'on_leave' => $attendances->where('status', 'leave')->count(),
            'late' => $attendances->where('is_late', true)->count(),
        ];
    }

    /**
     * Marquer manuellement une presence
     */
    public function markAttendance(
        Employee $employee,
        Carbon $date,
        string $status,
        ?string $checkIn = null,
        ?string $checkOut = null,
        ?string $notes = null
    ): Attendance {
        $attendance = Attendance::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => $date->toDateString(),
            ],
            [
                'status' => $status,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'notes' => $notes,
            ]
        );

        if ($checkIn && $checkOut) {
            $attendance->worked_hours = $attendance->calculateWorkedHours();
            $attendance->save();
        }

        return $attendance;
    }
}
