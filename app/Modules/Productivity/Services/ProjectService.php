<?php

namespace App\Modules\Productivity\Services;

use App\Modules\Productivity\Models\Project;
use App\Modules\Productivity\Models\ProjectMember;

class ProjectService
{
    public function create(array $data, array $members = []): Project
    {
        $project = Project::create($data);

        if (!empty($members)) {
            $this->syncMembers($project, $members);
        }

        return $project;
    }

    public function update(Project $project, array $data, array $members = []): Project
    {
        $project->update($data);

        if (isset($members)) {
            $this->syncMembers($project, $members);
        }

        return $project->fresh();
    }

    public function syncMembers(Project $project, array $members): void
    {
        $syncData = [];

        foreach ($members as $member) {
            $employeeId = $member['employee_id'] ?? $member;
            $syncData[$employeeId] = [
                'role' => $member['role'] ?? null,
                'hourly_rate' => $member['hourly_rate'] ?? null,
            ];
        }

        $project->members()->sync($syncData);
    }

    public function addMember(Project $project, int $employeeId, ?string $role = null, ?float $hourlyRate = null): void
    {
        $project->members()->attach($employeeId, [
            'role' => $role,
            'hourly_rate' => $hourlyRate,
        ]);
    }

    public function removeMember(Project $project, int $employeeId): void
    {
        $project->members()->detach($employeeId);
    }

    public function updateMember(Project $project, int $employeeId, array $data): void
    {
        $project->members()->updateExistingPivot($employeeId, $data);
    }

    public function activate(Project $project): Project
    {
        $project->update([
            'status' => 'active',
            'start_date' => $project->start_date ?? now(),
        ]);

        return $project;
    }

    public function complete(Project $project): Project
    {
        $project->update([
            'status' => 'completed',
            'end_date' => $project->end_date ?? now(),
        ]);

        return $project;
    }

    public function putOnHold(Project $project): Project
    {
        $project->update(['status' => 'on_hold']);

        return $project;
    }

    public function cancel(Project $project): Project
    {
        $project->update(['status' => 'cancelled']);

        return $project;
    }

    public function resume(Project $project): Project
    {
        $project->update(['status' => 'active']);

        return $project;
    }

    public function duplicate(Project $project): Project
    {
        $newProject = $project->replicate();
        $newProject->name = $project->name . ' (copie)';
        $newProject->status = 'planning';
        $newProject->start_date = null;
        $newProject->end_date = null;
        $newProject->save();

        // Dupliquer les membres
        foreach ($project->members as $member) {
            $newProject->members()->attach($member->id, [
                'role' => $member->pivot->role,
                'hourly_rate' => $member->pivot->hourly_rate,
            ]);
        }

        return $newProject;
    }

    public function getActiveProjectsForEmployee(int $employeeId)
    {
        return Project::active()
            ->whereHas('members', function ($query) use ($employeeId) {
                $query->where('employee_id', $employeeId);
            })
            ->orWhere('manager_id', $employeeId)
            ->get();
    }

    public function calculateProjectCost(Project $project): float
    {
        return $project->total_cost;
    }

    public function calculateProjectRevenue(Project $project): float
    {
        return $project->revenue;
    }
}
