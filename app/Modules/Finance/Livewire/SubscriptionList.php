<?php

namespace App\Modules\Finance\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Modules\Finance\Models\Subscription;
use App\Modules\Finance\Models\SubscriptionLine;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Finance\Models\InvoiceLine;
use App\Modules\CRM\Models\Contact;
use App\Modules\Inventory\Models\Product;

class SubscriptionList extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $frequency = '';

    // Modal pour création/édition
    public $showModal = false;
    public $editingSubscription = null;

    // Formulaire
    public $contact_id = '';
    public $name = '';
    public $description = '';
    public $frequency_form = 'monthly';
    public $frequency_interval = 1;
    public $start_date = '';
    public $end_date = '';
    public $auto_generate_invoice = true;
    public $notes = '';

    // Lignes de produits/services
    public $lines = [];
    public $selectedProduct = '';

    protected $queryString = ['search', 'status', 'frequency'];

    protected function rules()
    {
        return [
            'contact_id' => 'required|exists:contacts,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frequency_form' => 'required|in:monthly,quarterly,semi_annual,annual',
            'frequency_interval' => 'required|integer|min:1|max:12',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'auto_generate_invoice' => 'boolean',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.description' => 'required|string',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.tax_rate' => 'required|numeric|min:0|max:100',
        ];
    }

    protected $messages = [
        'lines.required' => 'Vous devez ajouter au moins un produit ou service.',
        'lines.min' => 'Vous devez ajouter au moins un produit ou service.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getSubscriptionsProperty()
    {
        return Subscription::query()
            ->with(['contact', 'creator', 'lines.product'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('reference', 'like', '%' . $this->search . '%')
                        ->orWhereHas('contact', function ($cq) {
                            $cq->where('first_name', 'like', '%' . $this->search . '%')
                                ->orWhere('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('company_name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->frequency, fn($q) => $q->where('frequency', $this->frequency))
            ->orderBy('next_billing_date', 'asc')
            ->paginate(15);
    }

    public function getStatsProperty()
    {
        return [
            'active_count' => Subscription::active()->count(),
            'monthly_revenue' => $this->calculateMonthlyRevenue(),
            'due_soon_count' => Subscription::dueSoon(7)->count(),
            'total_subscriptions' => Subscription::count(),
        ];
    }

    protected function calculateMonthlyRevenue(): float
    {
        $subscriptions = Subscription::active()->get();
        $monthlyTotal = 0;

        foreach ($subscriptions as $sub) {
            $monthlyAmount = match ($sub->frequency) {
                'monthly' => $sub->amount_ttc / $sub->frequency_interval,
                'quarterly' => $sub->amount_ttc / (3 * $sub->frequency_interval),
                'semi_annual' => $sub->amount_ttc / (6 * $sub->frequency_interval),
                'annual' => $sub->amount_ttc / (12 * $sub->frequency_interval),
                default => $sub->amount_ttc,
            };
            $monthlyTotal += $monthlyAmount;
        }

        return $monthlyTotal;
    }

    public function getContactsProperty()
    {
        return Contact::orderBy('company_name')
            ->orderBy('last_name')
            ->get();
    }

    public function getProductsProperty()
    {
        return Product::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getTotalHtProperty()
    {
        return collect($this->lines)->sum(function ($line) {
            $subtotal = ($line['quantity'] ?? 0) * ($line['unit_price'] ?? 0);
            $discount = $subtotal * (($line['discount_rate'] ?? 0) / 100);
            return $subtotal - $discount;
        });
    }

    public function getTotalTtcProperty()
    {
        return collect($this->lines)->sum(function ($line) {
            $subtotal = ($line['quantity'] ?? 0) * ($line['unit_price'] ?? 0);
            $discount = $subtotal * (($line['discount_rate'] ?? 0) / 100);
            $ht = $subtotal - $discount;
            return $ht * (1 + ($line['tax_rate'] ?? 19.25) / 100);
        });
    }

    public function create()
    {
        $this->resetForm();
        $this->start_date = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function edit(Subscription $subscription)
    {
        $this->editingSubscription = $subscription;
        $this->contact_id = $subscription->contact_id;
        $this->name = $subscription->name;
        $this->description = $subscription->description;
        $this->frequency_form = $subscription->frequency;
        $this->frequency_interval = $subscription->frequency_interval;
        $this->start_date = $subscription->start_date->format('Y-m-d');
        $this->end_date = $subscription->end_date?->format('Y-m-d');
        $this->auto_generate_invoice = $subscription->auto_generate_invoice;
        $this->notes = $subscription->notes;

        // Charger les lignes existantes
        $this->lines = $subscription->lines->map(function ($line) {
            return [
                'id' => $line->id,
                'product_id' => $line->product_id,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
                'tax_rate' => $line->tax_rate,
                'discount_rate' => $line->discount_rate,
            ];
        })->toArray();

        $this->showModal = true;
    }

    public function addProduct()
    {
        if (!$this->selectedProduct) {
            // Ajouter une ligne vide (service personnalisé)
            $this->lines[] = [
                'product_id' => null,
                'description' => '',
                'quantity' => 1,
                'unit_price' => 0,
                'tax_rate' => 19.25,
                'discount_rate' => 0,
            ];
        } else {
            $product = Product::find($this->selectedProduct);
            if ($product) {
                $this->lines[] = [
                    'product_id' => $product->id,
                    'description' => $product->name,
                    'quantity' => 1,
                    'unit_price' => $product->price,
                    'tax_rate' => $product->tax_rate ?? 19.25,
                    'discount_rate' => 0,
                ];
            }
        }
        $this->selectedProduct = '';
    }

    public function addCustomLine()
    {
        $this->lines[] = [
            'product_id' => null,
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'tax_rate' => 19.25,
            'discount_rate' => 0,
        ];
    }

    public function removeLine($index)
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function save()
    {
        $this->validate();

        $data = [
            'contact_id' => $this->contact_id,
            'name' => $this->name,
            'description' => $this->description,
            'frequency' => $this->frequency_form,
            'frequency_interval' => $this->frequency_interval,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date ?: null,
            'auto_generate_invoice' => $this->auto_generate_invoice,
            'notes' => $this->notes,
            'amount_ht' => 0,
            'amount_ttc' => 0,
            'tax_rate' => 19.25,
        ];

        if ($this->editingSubscription) {
            $this->editingSubscription->update($data);
            $subscription = $this->editingSubscription;

            // Supprimer les anciennes lignes
            $subscription->lines()->delete();
        } else {
            $data['next_billing_date'] = $this->start_date;
            $data['created_by'] = auth()->id();
            $subscription = Subscription::create($data);
        }

        // Créer les nouvelles lignes
        foreach ($this->lines as $line) {
            SubscriptionLine::create([
                'subscription_id' => $subscription->id,
                'product_id' => $line['product_id'] ?: null,
                'description' => $line['description'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'tax_rate' => $line['tax_rate'],
                'discount_rate' => $line['discount_rate'] ?? 0,
                'total_ht' => 0,
                'total_ttc' => 0,
            ]);
        }

        // Les totaux seront recalculés automatiquement via les events du modèle

        $message = $this->editingSubscription
            ? "Abonnement '{$this->name}' mis à jour avec succès."
            : "Abonnement '{$this->name}' créé avec succès.";

        session()->flash('success', $message);
        $this->closeModal();
    }

    public function toggleStatus(Subscription $subscription)
    {
        if ($subscription->status === 'active') {
            $subscription->pause();
            session()->flash('success', "Abonnement '{$subscription->name}' mis en pause.");
        } elseif ($subscription->status === 'paused') {
            $subscription->resume();
            session()->flash('success', "Abonnement '{$subscription->name}' réactivé.");
        }
    }

    public function cancelSubscription(Subscription $subscription)
    {
        $subscription->cancel();
        session()->flash('success', "Abonnement '{$subscription->name}' annulé.");
    }

    public function generateInvoice(Subscription $subscription)
    {
        if ($subscription->status !== 'active') {
            session()->flash('error', "Impossible de générer une facture pour un abonnement inactif.");
            return;
        }

        if ($subscription->lines->isEmpty()) {
            session()->flash('error', "Cet abonnement n'a pas de lignes de produits/services.");
            return;
        }

        // Créer la facture
        $invoice = Invoice::create([
            'contact_id' => $subscription->contact_id,
            'subscription_id' => $subscription->id,
            'reference' => $this->generateInvoiceReference(),
            'type' => 'invoice',
            'status' => 'draft',
            'order_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => $subscription->amount_ht,
            'tax_amount' => $subscription->amount_ttc - $subscription->amount_ht,
            'total_amount_ttc' => $subscription->amount_ttc,
            'notes' => "Facture générée automatiquement pour l'abonnement: {$subscription->name} ({$subscription->reference})",
            'created_by' => auth()->id(),
        ]);

        // Créer les lignes de facture depuis les lignes d'abonnement
        foreach ($subscription->lines as $subLine) {
            InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'product_id' => $subLine->product_id,
                'description' => $subLine->description,
                'quantity' => $subLine->quantity,
                'unit_price' => $subLine->unit_price,
                'tax_rate' => $subLine->tax_rate,
                'discount_rate' => $subLine->discount_rate,
                'total_amount' => $subLine->total_ht,
            ]);
        }

        // Mettre à jour l'abonnement
        $subscription->update([
            'last_billed_date' => now(),
            'next_billing_date' => $subscription->calculateNextBillingDate(),
            'invoices_generated' => $subscription->invoices_generated + 1,
        ]);

        session()->flash('success', "Facture {$invoice->reference} générée avec succès pour l'abonnement '{$subscription->name}'.");
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

    public function processAllDue()
    {
        $dueSubscriptions = Subscription::dueToday()->where('auto_generate_invoice', true)->get();
        $count = 0;

        foreach ($dueSubscriptions as $subscription) {
            $this->generateInvoice($subscription);
            $count++;
        }

        if ($count > 0) {
            session()->flash('success', "{$count} facture(s) générée(s) pour les abonnements échus.");
        } else {
            session()->flash('info', "Aucun abonnement à facturer aujourd'hui.");
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->editingSubscription = null;
        $this->contact_id = '';
        $this->name = '';
        $this->description = '';
        $this->frequency_form = 'monthly';
        $this->frequency_interval = 1;
        $this->start_date = '';
        $this->end_date = '';
        $this->auto_generate_invoice = true;
        $this->notes = '';
        $this->lines = [];
        $this->selectedProduct = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('finance::livewire.subscription-list', [
            'subscriptions' => $this->subscriptions,
            'stats' => $this->stats,
            'contacts' => $this->contacts,
            'products' => $this->products,
            'frequencies' => Subscription::FREQUENCIES,
            'statuses' => Subscription::STATUSES,
            'totalHt' => $this->totalHt,
            'totalTtc' => $this->totalTtc,
        ])->layout('layouts.app');
    }
}
