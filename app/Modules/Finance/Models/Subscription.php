<?php

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Modules\CRM\Models\Contact;

class Subscription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contact_id',
        'name',
        'description',
        'reference',
        'frequency',
        'frequency_interval',
        'amount_ht',
        'tax_rate',
        'amount_ttc',
        'start_date',
        'end_date',
        'next_billing_date',
        'last_billed_date',
        'status',
        'auto_generate_invoice',
        'invoices_generated',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_billing_date' => 'date',
        'last_billed_date' => 'date',
        'amount_ht' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'amount_ttc' => 'decimal:2',
        'auto_generate_invoice' => 'boolean',
    ];

    public const FREQUENCIES = [
        'monthly' => 'Mensuel',
        'quarterly' => 'Trimestriel',
        'semi_annual' => 'Semestriel',
        'annual' => 'Annuel',
    ];

    public const STATUSES = [
        'active' => 'Actif',
        'paused' => 'En pause',
        'cancelled' => 'Annulé',
        'expired' => 'Expiré',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscription) {
            if (empty($subscription->reference)) {
                $subscription->reference = self::generateReference();
            }
            // Calculer le TTC si non défini
            if ($subscription->amount_ht > 0 && $subscription->amount_ttc == 0) {
                $subscription->amount_ttc = $subscription->amount_ht * (1 + $subscription->tax_rate / 100);
            }
        });

        static::updating(function ($subscription) {
            // Recalculer le TTC si le HT ou le taux change
            if ($subscription->isDirty(['amount_ht', 'tax_rate'])) {
                $subscription->amount_ttc = $subscription->amount_ht * (1 + $subscription->tax_rate / 100);
            }
        });
    }

    public static function generateReference(): string
    {
        $year = date('Y');
        $lastSubscription = self::withTrashed()
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastSubscription
            ? intval(substr($lastSubscription->reference, -4)) + 1
            : 1;

        return sprintf('ABN-%s-%04d', $year, $nextNumber);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'subscription_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SubscriptionLine::class);
    }

    // Recalculer les totaux depuis les lignes
    public function recalculateTotals(): void
    {
        $this->amount_ht = $this->lines()->sum('total_ht');
        $this->amount_ttc = $this->lines()->sum('total_ttc');

        // Calculer le taux de TVA moyen pondéré
        if ($this->amount_ht > 0) {
            $taxAmount = $this->amount_ttc - $this->amount_ht;
            $this->tax_rate = ($taxAmount / $this->amount_ht) * 100;
        }

        $this->saveQuietly(); // Éviter la boucle infinie
    }

    // Accesseurs
    public function getFrequencyLabelAttribute(): string
    {
        return self::FREQUENCIES[$this->frequency] ?? $this->frequency;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsDueSoonAttribute(): bool
    {
        if (!$this->is_active || !$this->next_billing_date) {
            return false;
        }
        return $this->next_billing_date->lte(now()->addDays(7));
    }

    public function getMonthsIntervalAttribute(): int
    {
        return match ($this->frequency) {
            'monthly' => 1 * $this->frequency_interval,
            'quarterly' => 3 * $this->frequency_interval,
            'semi_annual' => 6 * $this->frequency_interval,
            'annual' => 12 * $this->frequency_interval,
            default => 1,
        };
    }

    // Méthodes
    public function calculateNextBillingDate(): \Carbon\Carbon
    {
        $baseDate = $this->last_billed_date ?? $this->start_date;
        return $baseDate->copy()->addMonths($this->months_interval);
    }

    public function pause(): void
    {
        $this->update(['status' => 'paused']);
    }

    public function resume(): void
    {
        $this->update([
            'status' => 'active',
            'next_billing_date' => $this->calculateNextBillingDate(),
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDueBefore($query, $date)
    {
        return $query->where('next_billing_date', '<=', $date);
    }

    public function scopeDueToday($query)
    {
        return $query->active()->whereDate('next_billing_date', today());
    }

    public function scopeDueSoon($query, $days = 7)
    {
        return $query->active()
            ->where('next_billing_date', '<=', now()->addDays($days))
            ->where('next_billing_date', '>=', today());
    }
}
