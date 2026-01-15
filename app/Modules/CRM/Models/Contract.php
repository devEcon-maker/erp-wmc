<?php

namespace App\Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contact_id',
        'reference',
        'type',
        'contract_category',
        'contract_subtype',
        'status',
        'start_date',
        'end_date',
        'billing_frequency',
        'next_billing_date',
        'notes',
        'terms',
        'document_path',
        'document_name',
        'document_type',
        'document_size',
        'document_uploaded_at',
        'signatory_name',
        'signature_date',
        'total_amount',
        'tax_amount',
        'discount_amount',
        'total_amount_ttc',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_billing_date' => 'date',
        'signature_date' => 'date',
        'document_uploaded_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount_ttc' => 'decimal:2',
    ];

    // Catégories de contrats
    public const CATEGORIES = [
        'client' => 'Client',
        'fournisseur' => 'Fournisseur',
        'prestataire' => 'Prestataire',
    ];

    // Sous-types de contrats
    public const SUBTYPES = [
        'prestation' => 'Contrat de prestation',
        'geolocalisation' => 'Contrat de géolocalisation',
        'maintenance' => 'Contrat de maintenance',
        'location' => 'Contrat de location',
        'partenariat' => 'Contrat de partenariat',
        'confidentialite' => 'Accord de confidentialité',
        'service' => 'Contrat de service',
        'fourniture' => 'Contrat de fourniture',
        'autre' => 'Autre',
    ];

    // Statuts
    public const STATUSES = [
        'draft' => 'Brouillon',
        'active' => 'Actif',
        'suspended' => 'Suspendu',
        'expired' => 'Expiré',
        'terminated' => 'Résilié',
    ];

    // Extensions de fichiers autorisées
    public const ALLOWED_EXTENSIONS = ['pdf', 'docx', 'doc'];

    // Accesseurs
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->contract_category] ?? $this->contract_category ?? '-';
    }

    public function getSubtypeLabelAttribute(): string
    {
        return self::SUBTYPES[$this->contract_subtype] ?? $this->contract_subtype ?? '-';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getHasDocumentAttribute(): bool
    {
        return !empty($this->document_path);
    }

    public function getDocumentUrlAttribute(): ?string
    {
        if (!$this->document_path) {
            return null;
        }
        return Storage::url($this->document_path);
    }

    public function getFormattedDocumentSizeAttribute(): string
    {
        if (!$this->document_size) {
            return '-';
        }

        $bytes = $this->document_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ContractLine::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDueForBilling($query)
    {
        return $query->where('status', 'active')
            ->where('next_billing_date', '<=', now());
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('contract_category', $category);
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<=', now()->addDays($days))
            ->where('end_date', '>=', today());
    }

    public function scopeWithDocument($query)
    {
        return $query->whereNotNull('document_path');
    }

    public function scopeWithoutDocument($query)
    {
        return $query->whereNull('document_path');
    }

    // Méthode pour supprimer le document
    public function deleteDocument(): bool
    {
        if ($this->document_path && Storage::exists($this->document_path)) {
            Storage::delete($this->document_path);
        }

        $this->update([
            'document_path' => null,
            'document_name' => null,
            'document_type' => null,
            'document_size' => null,
            'document_uploaded_at' => null,
        ]);

        return true;
    }
}
