<?php

namespace App\Modules\HR\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'name',
        'file_path',
        'file_type',
        'file_size',
        'expiry_date',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public const TYPES = [
        'cni' => 'Carte Nationale d\'Identite',
        'passport' => 'Passeport',
        'diploma' => 'Diplome',
        'cv' => 'CV',
        'contract' => 'Contrat de travail',
        'certificate' => 'Certificat',
        'medical' => 'Certificat medical',
        'other' => 'Autre',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getUrlAttribute(): ?string
    {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        $units = ['o', 'Ko', 'Mo', 'Go'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2) . ' ' . $units[$index];
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isBetween(now(), now()->addDays(30));
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')->where('expiry_date', '<', now());
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays($days));
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function delete()
    {
        if ($this->file_path) {
            Storage::delete($this->file_path);
        }
        return parent::delete();
    }
}
