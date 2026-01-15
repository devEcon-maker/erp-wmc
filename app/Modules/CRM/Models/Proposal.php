<?php

namespace App\Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\User;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\InvoiceLine;

class Proposal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contact_id',
        'opportunity_id',
        'reference',
        'status',
        'valid_until',
        'sent_at',
        'accepted_at',
        'rejected_at',
        'notes',
        'terms',
        'total_amount',
        'tax_amount',
        'discount_amount',
        'total_amount_ttc',
        'created_by',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount_ttc' => 'decimal:2',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ProposalLine::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Convertir le devis en bon de commande
     */
    public function convertToOrder(): Order
    {
        // Vérifier que le devis est accepté
        if ($this->status !== 'accepted') {
            throw new \Exception("Le devis doit être accepté avant d'être converti en bon de commande.");
        }

        // Vérifier qu'une commande n'existe pas déjà
        if ($this->order()->exists()) {
            throw new \Exception("Ce devis a déjà été converti en bon de commande.");
        }

        // Créer le bon de commande
        $order = Order::create([
            'contact_id' => $this->contact_id,
            'proposal_id' => $this->id,
            'reference' => $this->generateOrderReference(),
            'status' => 'draft',
            'order_date' => now(),
            'notes' => "Commande générée depuis le devis: {$this->reference}",
            'total_amount' => $this->total_amount,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'total_amount_ttc' => $this->total_amount_ttc,
            'created_by' => auth()->id(),
        ]);

        // Copier les lignes du devis vers la commande
        foreach ($this->lines as $line) {
            OrderLine::create([
                'order_id' => $order->id,
                'product_id' => $line->product_id,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
                'tax_rate' => $line->tax_rate,
                'discount_rate' => $line->discount_rate,
                'total_amount' => $line->total_amount,
            ]);
        }

        return $order;
    }

    /**
     * Convertir le devis en facture (avec bon de commande si existant)
     */
    public function convertToInvoice(): Invoice
    {
        // Vérifier que le devis est accepté
        if ($this->status !== 'accepted') {
            throw new \Exception("Le devis doit être accepté avant d'être converti en facture.");
        }

        // Vérifier qu'une facture n'existe pas déjà
        if ($this->invoice()->exists()) {
            throw new \Exception("Ce devis a déjà été converti en facture.");
        }

        // Créer le bon de commande si non existant
        $order = $this->order;
        if (!$order) {
            $order = $this->convertToOrder();
        }

        // Créer la facture
        $invoice = Invoice::create([
            'contact_id' => $this->contact_id,
            'order_id' => $order->id,
            'proposal_id' => $this->id,
            'reference' => $this->generateInvoiceReference(),
            'type' => 'invoice',
            'status' => 'draft',
            'order_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => $this->total_amount,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'total_amount_ttc' => $this->total_amount_ttc,
            'paid_amount' => 0,
            'notes' => "Facture générée depuis le devis: {$this->reference}\nBon de commande: {$order->reference}",
            'created_by' => auth()->id(),
        ]);

        // Copier les lignes du devis vers la facture
        foreach ($this->lines as $line) {
            InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'product_id' => $line->product_id,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
                'tax_rate' => $line->tax_rate,
                'discount_rate' => $line->discount_rate,
                'total_amount' => $line->total_amount,
            ]);
        }

        return $invoice;
    }

    protected function generateOrderReference(): string
    {
        $year = date('Y');
        $lastOrder = Order::withTrashed()
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastOrder
            ? intval(substr($lastOrder->reference, -5)) + 1
            : 1;

        return sprintf('CMD-%s-%05d', $year, $nextNumber);
    }

    protected function generateInvoiceReference(): string
    {
        $year = date('Y');
        $lastInvoice = Invoice::withTrashed()
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastInvoice
            ? intval(substr($lastInvoice->reference, -5)) + 1
            : 1;

        return sprintf('FAC-%s-%05d', $year, $nextNumber);
    }

    // scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRefused($query)
    {
        return $query->where('status', 'refused');
    }
}
