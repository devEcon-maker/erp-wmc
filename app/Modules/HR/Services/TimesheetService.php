<?php

namespace App\Modules\HR\Services;

use App\Modules\HR\Models\Timesheet;
use App\Modules\HR\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TimesheetService
{
    /**
     * Récupère le total des heures pour un jour donné
     */
    public function getTotalHoursForDay(int $employeeId, $date): float
    {
        return Timesheet::getTotalHoursForDay($employeeId, $date);
    }

    /**
     * Récupère le rapport hebdomadaire d'un employé
     */
    public function getWeeklyReport(int $employeeId, Carbon $weekStart): array
    {
        $weekEnd = $weekStart->copy()->addDays(6);

        $timesheets = Timesheet::where('employee_id', $employeeId)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->with(['employee'])
            ->get();

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $dayTimesheets = $timesheets->filter(fn($t) => $t->date->isSameDay($date));

            $days[$date->format('Y-m-d')] = [
                'date' => $date,
                'day_name' => $date->locale('fr')->dayName,
                'timesheets' => $dayTimesheets,
                'total_hours' => $dayTimesheets->sum('hours'),
                'billable_hours' => $dayTimesheets->where('billable', true)->sum('hours'),
            ];
        }

        return [
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'days' => $days,
            'total_hours' => $timesheets->sum('hours'),
            'billable_hours' => $timesheets->where('billable', true)->sum('hours'),
            'non_billable_hours' => $timesheets->where('billable', false)->sum('hours'),
        ];
    }

    /**
     * Enregistre ou met à jour une feuille de temps
     */
    public function upsert(int $employeeId, array $data): Timesheet
    {
        // Validation: pas de saisie future
        if (Timesheet::isDateInFuture($data['date'])) {
            throw new \Exception("Impossible de saisir des heures pour une date future.");
        }

        // Vérifier le total max 24h
        $existingHours = $this->getTotalHoursForDay($employeeId, $data['date']);
        $existingEntry = Timesheet::where('employee_id', $employeeId)
            ->where('project_id', $data['project_id'])
            ->where('task_id', $data['task_id'] ?? null)
            ->whereDate('date', $data['date'])
            ->first();

        $currentHours = $existingEntry ? $existingEntry->hours : 0;
        $newTotal = $existingHours - $currentHours + $data['hours'];

        if ($newTotal > 24) {
            throw new \Exception("Le total des heures pour cette journée ne peut pas dépasser 24h.");
        }

        return Timesheet::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'project_id' => $data['project_id'],
                'task_id' => $data['task_id'] ?? null,
                'date' => $data['date'],
            ],
            [
                'hours' => $data['hours'],
                'description' => $data['description'] ?? null,
                'billable' => $data['billable'] ?? true,
            ]
        );
    }

    /**
     * Approuve une feuille de temps
     */
    public function approve(Timesheet $timesheet, User $approver): bool
    {
        if ($timesheet->approved) {
            return false;
        }

        $timesheet->update([
            'approved' => true,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        return true;
    }

    /**
     * Approuve toutes les feuilles de temps d'un employé pour une période
     */
    public function approveRange(int $employeeId, Carbon $start, Carbon $end, User $approver): int
    {
        return Timesheet::where('employee_id', $employeeId)
            ->whereBetween('date', [$start, $end])
            ->where('approved', false)
            ->update([
                'approved' => true,
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);
    }

    /**
     * Rapport des heures par projet
     */
    public function getProjectTimeReport(?int $projectId = null, ?Carbon $start = null, ?Carbon $end = null): Collection
    {
        $query = Timesheet::query();

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        if ($start) {
            $query->whereDate('date', '>=', $start);
        }

        if ($end) {
            $query->whereDate('date', '<=', $end);
        }

        return $query->with('employee')
            ->get()
            ->groupBy('project_id')
            ->map(function ($timesheets, $projectId) {
                return [
                    'project_id' => $projectId,
                    'total_hours' => $timesheets->sum('hours'),
                    'billable_hours' => $timesheets->where('billable', true)->sum('hours'),
                    'by_employee' => $timesheets->groupBy('employee_id')->map(fn($t) => $t->sum('hours')),
                ];
            });
    }

    /**
     * Récupère les feuilles de temps en attente d'approbation pour un manager
     */
    public function getPendingApprovalForManager(int $managerId): Collection
    {
        $employeeIds = Employee::where('manager_id', $managerId)->pluck('id');

        return Timesheet::whereIn('employee_id', $employeeIds)
            ->where('approved', false)
            ->with('employee')
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Statistiques globales
     */
    public function getStats(?int $employeeId = null, ?Carbon $month = null): array
    {
        $month = $month ?? now();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $query = Timesheet::whereBetween('date', [$start, $end]);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $timesheets = $query->get();

        return [
            'total_hours' => $timesheets->sum('hours'),
            'billable_hours' => $timesheets->where('billable', true)->sum('hours'),
            'non_billable_hours' => $timesheets->where('billable', false)->sum('hours'),
            'approved_hours' => $timesheets->where('approved', true)->sum('hours'),
            'pending_hours' => $timesheets->where('approved', false)->sum('hours'),
            'days_worked' => $timesheets->pluck('date')->unique()->count(),
        ];
    }
}
