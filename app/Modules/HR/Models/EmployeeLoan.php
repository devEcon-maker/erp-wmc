<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class EmployeeLoan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'reference',
        'amount',
        'interest_rate',
        'duration_months',
        'monthly_payment',
        'start_date',
        'end_date',
        'reason',
        'status',
        'total_paid',
        'remaining_balance',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'document_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public const STATUSES = [
        'pending' => 'En attente',
        'approved' => 'Approuve',
        'rejected' => 'Rejete',
        'active' => 'En cours',
        'completed' => 'Rembourse',
        'cancelled' => 'Annule',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($loan) {
            if (empty($loan->reference)) {
                $year = now()->format('Y');
                $lastLoan = static::withTrashed()
                    ->where('reference', 'like', "PRET-{$year}-%")
                    ->orderBy('id', 'desc')
                    ->first();
                $lastNum = $lastLoan ? (int) substr($lastLoan->reference, -4) : 0;
                $loan->reference = 'PRET-' . $year . '-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
            }

            // Calculer le solde initial
            if (empty($loan->remaining_balance)) {
                $totalWithInterest = $loan->amount * (1 + ($loan->interest_rate / 100));
                $loan->remaining_balance = $totalWithInterest;
            }
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'amber',
            'approved' => 'blue',
            'rejected' => 'red',
            'active' => 'cyan',
            'completed' => 'green',
            'cancelled' => 'gray',
            default => 'slate',
        };
    }

    public function getTotalWithInterestAttribute(): float
    {
        return $this->amount * (1 + ($this->interest_rate / 100));
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_with_interest <= 0) {
            return 0;
        }
        return round(($this->total_paid / $this->total_with_interest) * 100, 2);
    }

    public function getRemainingPaymentsAttribute(): int
    {
        return $this->payments()->where('status', 'pending')->count();
    }

    public function getDocumentUrlAttribute(): ?string
    {
        return $this->document_path ? Storage::url($this->document_path) : null;
    }

    public function canApprove(): bool
    {
        return $this->status === 'pending';
    }

    public function approve(User $approver): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }

    public function reject(User $approver, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function activate(): void
    {
        $this->update(['status' => 'active']);
        $this->generatePaymentSchedule();
    }

    public function generatePaymentSchedule(): void
    {
        $totalWithInterest = $this->total_with_interest;
        $monthlyPrincipal = $this->amount / $this->duration_months;
        $monthlyInterest = ($totalWithInterest - $this->amount) / $this->duration_months;

        $currentDate = $this->start_date->copy();

        for ($i = 1; $i <= $this->duration_months; $i++) {
            LoanPayment::create([
                'employee_loan_id' => $this->id,
                'payment_number' => $i,
                'due_date' => $currentDate->addMonth()->copy(),
                'principal_amount' => $monthlyPrincipal,
                'interest_amount' => $monthlyInterest,
                'total_amount' => $monthlyPrincipal + $monthlyInterest,
                'status' => 'pending',
            ]);
        }
    }

    public function recordPayment(float $amount, int $payslipId): void
    {
        $this->increment('total_paid', $amount);
        $this->decrement('remaining_balance', $amount);

        // Marquer l'echeance comme payee
        $pendingPayment = $this->payments()
            ->where('status', 'pending')
            ->orderBy('payment_number')
            ->first();

        if ($pendingPayment) {
            $pendingPayment->update([
                'status' => 'paid',
                'paid_date' => now(),
                'payslip_id' => $payslipId,
            ]);
        }

        // Verifier si le pret est entierement rembourse
        if ($this->remaining_balance <= 0) {
            $this->update(['status' => 'completed']);
        }
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
}
