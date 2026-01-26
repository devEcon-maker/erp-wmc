<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_number',
        'first_name',
        'last_name',
        'gender',
        'nationality',
        'marital_status',
        'dependents_count',
        'email',
        'phone',
        'cni_number',
        'cni_expiry_date',
        'social_security_number',
        'address',
        'city',
        'country',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'bank_name',
        'bank_account_number',
        'bank_rib',
        'birth_date',
        'hire_date',
        'end_date',
        'job_title',
        'department_id',
        'manager_id',
        'salary',
        'contract_type',
        'contract_start_date',
        'contract_end_date',
        'probation_end_date',
        'probation_completed',
        'status',
        'work_schedule_id',
        'photo_path',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'end_date' => 'date',
        'cni_expiry_date' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'probation_end_date' => 'date',
        'probation_completed' => 'boolean',
        'salary' => 'decimal:2',
    ];

    public const GENDERS = [
        'M' => 'Masculin',
        'F' => 'Feminin',
    ];

    public const MARITAL_STATUSES = [
        'single' => 'Celibataire',
        'married' => 'Marie(e)',
        'divorced' => 'Divorce(e)',
        'widowed' => 'Veuf/Veuve',
    ];

    public const CONTRACT_TYPES = [
        'cdi' => 'CDI',
        'cdd' => 'CDD',
        'interim' => 'Interim',
        'stage' => 'Stage',
        'alternance' => 'Alternance',
    ];

    public const STATUSES = [
        'active' => 'Actif',
        'inactive' => 'Inactif',
        'terminated' => 'Termine',
        'on_leave' => 'En conge',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class);
    }

    // Nouvelles relations
    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function careerHistory(): HasMany
    {
        return $this->hasMany(EmployeeCareerHistory::class)->orderBy('event_date', 'desc');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(EmployeeContract::class)->orderBy('start_date', 'desc');
    }

    public function activeContract()
    {
        return $this->hasOne(EmployeeContract::class)->where('status', 'active')->latest();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class);
    }

    public function permissionRequests(): HasMany
    {
        return $this->hasMany(PermissionRequest::class);
    }

    public function lateArrivals(): HasMany
    {
        return $this->hasMany(LateArrival::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function salaryAdvances(): HasMany
    {
        return $this->hasMany(SalaryAdvance::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(EmployeeLoan::class);
    }

    public function activeLoans(): HasMany
    {
        return $this->hasMany(EmployeeLoan::class)->where('status', 'active');
    }

    public function bonuses(): HasMany
    {
        return $this->hasMany(EmployeeBonus::class);
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(EmployeeObjective::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function developmentPlans(): HasMany
    {
        return $this->hasMany(DevelopmentPlan::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function pendingTasks(): HasMany
    {
        return $this->hasMany(Task::class)->whereHas('status', function ($q) {
            $q->where('is_completed', false);
        });
    }

    // Accesseurs
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getSeniorityYearsAttribute(): float
    {
        return round(Carbon::now()->diffInYears($this->hire_date ?? Carbon::now()), 1);
    }

    public function getGenderLabelAttribute(): ?string
    {
        return self::GENDERS[$this->gender] ?? $this->gender;
    }

    public function getMaritalStatusLabelAttribute(): ?string
    {
        return self::MARITAL_STATUSES[$this->marital_status] ?? $this->marital_status;
    }

    public function getContractTypeLabelAttribute(): ?string
    {
        return self::CONTRACT_TYPES[$this->contract_type] ?? $this->contract_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'green',
            'inactive' => 'gray',
            'terminated' => 'red',
            'on_leave' => 'amber',
            default => 'slate',
        };
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::url($this->photo_path) : null;
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    public function getIsProbationActiveAttribute(): bool
    {
        return !$this->probation_completed &&
            $this->probation_end_date &&
            $this->probation_end_date->isFuture();
    }

    public function getIsContractExpiringAttribute(): bool
    {
        return $this->contract_end_date &&
            $this->contract_end_date->isBetween(now(), now()->addDays(30));
    }

    public function getIsCniExpiringAttribute(): bool
    {
        return $this->cni_expiry_date &&
            $this->cni_expiry_date->isBetween(now(), now()->addDays(30));
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeContractExpiring($query, int $days = 30)
    {
        return $query->whereNotNull('contract_end_date')
            ->where('contract_end_date', '>=', now())
            ->where('contract_end_date', '<=', now()->addDays($days));
    }

    public function scopeProbationEnding($query, int $days = 30)
    {
        return $query->where('probation_completed', false)
            ->whereNotNull('probation_end_date')
            ->where('probation_end_date', '>=', now())
            ->where('probation_end_date', '<=', now()->addDays($days));
    }

    public function scopeByContractType($query, string $type)
    {
        return $query->where('contract_type', $type);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employee) {
            if (empty($employee->employee_number)) {
                $lastEmployee = static::withTrashed()->orderBy('id', 'desc')->first();
                $lastId = $lastEmployee ? $lastEmployee->id : 0;
                $employee->employee_number = 'EMP-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
