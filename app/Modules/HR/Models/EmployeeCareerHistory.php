<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeCareerHistory extends Model
{
    protected $table = 'employee_career_history';

    protected $fillable = [
        'employee_id',
        'event_type',
        'event_date',
        'old_value',
        'new_value',
        'old_department',
        'new_department',
        'old_job_title',
        'new_job_title',
        'old_salary',
        'new_salary',
        'reason',
        'notes',
        'document_path',
        'created_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'old_salary' => 'decimal:2',
        'new_salary' => 'decimal:2',
    ];

    public const EVENT_TYPES = [
        'hire' => 'Embauche',
        'promotion' => 'Promotion',
        'mutation' => 'Mutation',
        'salary_change' => 'Changement de salaire',
        'contract_renewal' => 'Renouvellement de contrat',
        'contract_change' => 'Changement de type de contrat',
        'sanction' => 'Sanction',
        'warning' => 'Avertissement',
        'award' => 'Distinction/Prime',
        'training' => 'Formation',
        'departure' => 'Depart',
        'title_change' => 'Changement de poste',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getEventTypeLabelAttribute(): string
    {
        return self::EVENT_TYPES[$this->event_type] ?? $this->event_type;
    }

    public function getEventColorAttribute(): string
    {
        return match ($this->event_type) {
            'hire' => 'green',
            'promotion', 'award' => 'blue',
            'salary_change' => 'cyan',
            'mutation', 'title_change' => 'purple',
            'contract_renewal', 'contract_change' => 'indigo',
            'sanction', 'warning' => 'red',
            'training' => 'amber',
            'departure' => 'gray',
            default => 'slate',
        };
    }

    public function getEventIconAttribute(): string
    {
        return match ($this->event_type) {
            'hire' => 'person_add',
            'promotion' => 'trending_up',
            'mutation' => 'swap_horiz',
            'salary_change' => 'payments',
            'contract_renewal', 'contract_change' => 'description',
            'sanction', 'warning' => 'warning',
            'award' => 'emoji_events',
            'training' => 'school',
            'departure' => 'exit_to_app',
            'title_change' => 'badge',
            default => 'event',
        };
    }

    public function getSalaryChangeAttribute(): ?float
    {
        if ($this->old_salary && $this->new_salary) {
            return $this->new_salary - $this->old_salary;
        }
        return null;
    }

    public function getSalaryChangePercentageAttribute(): ?float
    {
        if ($this->old_salary && $this->new_salary && $this->old_salary > 0) {
            return round((($this->new_salary - $this->old_salary) / $this->old_salary) * 100, 2);
        }
        return null;
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('event_date', '>=', now()->subDays($days));
    }
}
