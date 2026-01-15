<?php

namespace App\Modules\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Modules\Finance\Models\Invoice;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'company_name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'postal_code',
        'country',
        'status',
        'source',
        'assigned_to',
        'notes',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'converted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'contact_id');
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    // Scopes
    public function scopeProspects($query)
    {
        return $query->where('type', 'prospect');
    }

    public function scopeClients($query)
    {
        return $query->where('type', 'client');
    }

    public function scopeFournisseurs($query)
    {
        return $query->where('type', 'fournisseur');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getDisplayNameAttribute()
    {
        return $this->company_name ?: $this->full_name;
    }

    protected static function newFactory()
    {
        return \App\Modules\CRM\Database\Factories\ContactFactory::new();
    }
}
