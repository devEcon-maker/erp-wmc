<?php

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Modules\CRM\Models\Contact;
use App\Modules\CRM\Models\Order;
use App\Modules\CRM\Models\Contract;
use App\Modules\CRM\Models\Proposal;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contact_id',
        'order_id',
        'proposal_id',
        'contract_id',
        'subscription_id',
        'reference',
        'type',
        'status',
        'order_date',
        'due_date',
        'paid_at',
        'total_amount',
        'tax_amount',
        'discount_amount',
        'total_amount_ttc',
        'paid_amount',
        'notes',
        'terms',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'date',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount_ttc' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getRemainingBalanceAttribute()
    {
        return $this->total_amount_ttc - $this->paid_amount;
    }

    // Scopes
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('due_date', '<', now());
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['sent', 'partial', 'overdue']);
    }
}
