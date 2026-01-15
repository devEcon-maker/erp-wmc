<?php

namespace App\Modules\Productivity\Models;

use App\Modules\CRM\Models\Contact;
use App\Modules\HR\Models\Employee;
use App\Modules\Finance\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'contact_id',
        'manager_id',
        'start_date',
        'end_date',
        'budget',
        'supplier_cost',
        'status',
        'billing_type',
        'hourly_rate',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'supplier_cost' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
    ];

    // Relations
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'project_members')
            ->withPivot(['role', 'hourly_rate'])
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // Accessors
    public function getTotalHoursAttribute(): float
    {
        return $this->timeEntries()->sum('hours') ?? 0;
    }

    public function getBillableHoursAttribute(): float
    {
        return $this->timeEntries()->where('billable', true)->sum('hours') ?? 0;
    }

    public function getTotalCostAttribute(): float
    {
        $cost = 0;

        foreach ($this->timeEntries()->with('employee')->get() as $entry) {
            $memberRate = $this->members()
                ->where('employee_id', $entry->employee_id)
                ->first()?->pivot?->hourly_rate;

            $rate = $memberRate ?? $this->hourly_rate ?? 0;
            $cost += $entry->hours * $rate;
        }

        return $cost;
    }

    public function getRevenueAttribute(): float
    {
        if ($this->billing_type === 'fixed') {
            return (float) ($this->budget ?? 0);
        }

        // Pour hourly, calculer sur les heures facturables
        return $this->billable_hours * ($this->hourly_rate ?? 0);
    }

    public function getProfitAttribute(): float
    {
        return $this->revenue - $this->total_cost;
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->revenue <= 0) {
            return 0;
        }

        return ($this->profit / $this->revenue) * 100;
    }

    public function getInvoicedAmountAttribute(): float
    {
        return $this->invoices()->sum('total_amount_ttc') ?? 0;
    }

    public function getGapAttribute(): float
    {
        // Gap = Budget - (Déboursé sec + Coûts internes)
        return ($this->budget ?? 0) - (($this->supplier_cost ?? 0) + $this->total_cost);
    }

    public function getProgressAttribute(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        $totalDays = $this->start_date->diffInDays($this->end_date);
        if ($totalDays <= 0) {
            return 100;
        }

        $elapsedDays = $this->start_date->diffInDays(now());

        return min(100, max(0, (int) (($elapsedDays / $totalDays) * 100)));
    }

    public function getTasksProgressAttribute(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->tasks()->where('status', 'done')->count();

        return (int) (($completed / $total) * 100);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'planning' => 'Planification',
            'active' => 'Actif',
            'on_hold' => 'En pause',
            'completed' => 'Terminé',
            'cancelled' => 'Annulé',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'planning' => 'blue',
            'active' => 'green',
            'on_hold' => 'yellow',
            'completed' => 'emerald',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    public function getBillingTypeLabelAttribute(): string
    {
        return match ($this->billing_type) {
            'fixed' => 'Forfait',
            'hourly' => 'Régie',
            'non_billable' => 'Non facturable',
            default => ucfirst($this->billing_type),
        };
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePlanning($query)
    {
        return $query->where('status', 'planning');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByManager($query, $managerId)
    {
        return $query->where('manager_id', $managerId);
    }

    public function scopeByClient($query, $contactId)
    {
        return $query->where('contact_id', $contactId);
    }

    public function scopeBillable($query)
    {
        return $query->whereIn('billing_type', ['fixed', 'hourly']);
    }
}
