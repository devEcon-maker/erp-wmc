<?php

namespace App\Modules\CRM\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Modules\CRM\Models\Contract;
use App\Modules\CRM\Models\Contact;
use Illuminate\Support\Facades\Storage;

class ContractList extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $status = '';
    public $category = '';
    public $hasDocument = '';

    // Modal création/édition
    public $showModal = false;
    public $editingContract = null;

    // Formulaire
    public $contact_id = '';
    public $contract_category = 'client';
    public $contract_subtype = '';
    public $start_date = '';
    public $end_date = '';
    public $signatory_name = '';
    public $signature_date = '';
    public $notes = '';
    public $document;

    // Modal visualisation document
    public $showDocumentModal = false;
    public $viewingContract = null;

    protected $queryString = ['search', 'status', 'category', 'hasDocument'];

    protected function rules()
    {
        return [
            'contact_id' => 'required|exists:contacts,id',
            'contract_category' => 'required|in:client,fournisseur,prestataire',
            'contract_subtype' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'signatory_name' => 'nullable|string|max:255',
            'signature_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB max
        ];
    }

    protected $messages = [
        'document.mimes' => 'Le document doit être un fichier PDF ou Word (docx, doc).',
        'document.max' => 'Le document ne doit pas dépasser 10 Mo.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getContractsProperty()
    {
        return Contract::query()
            ->with(['contact', 'creator'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('reference', 'like', '%' . $this->search . '%')
                        ->orWhere('signatory_name', 'like', '%' . $this->search . '%')
                        ->orWhereHas('contact', function ($cq) {
                            $cq->where('first_name', 'like', '%' . $this->search . '%')
                                ->orWhere('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('company_name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->category, fn($q) => $q->where('contract_category', $this->category))
            ->when($this->hasDocument === 'yes', fn($q) => $q->withDocument())
            ->when($this->hasDocument === 'no', fn($q) => $q->withoutDocument())
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    public function getStatsProperty()
    {
        return [
            'total' => Contract::count(),
            'active' => Contract::where('status', 'active')->count(),
            'clients' => Contract::where('contract_category', 'client')->count(),
            'fournisseurs' => Contract::where('contract_category', 'fournisseur')->count(),
            'prestataires' => Contract::where('contract_category', 'prestataire')->count(),
            'with_document' => Contract::withDocument()->count(),
            'expiring_soon' => Contract::expiringSoon(30)->count(),
        ];
    }

    public function getContactsProperty()
    {
        return Contact::orderBy('company_name')
            ->orderBy('last_name')
            ->get();
    }

    public function create()
    {
        $this->resetForm();
        $this->start_date = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function edit(Contract $contract)
    {
        $this->editingContract = $contract;
        $this->contact_id = $contract->contact_id;
        $this->contract_category = $contract->contract_category ?? 'client';
        $this->contract_subtype = $contract->contract_subtype;
        $this->start_date = $contract->start_date?->format('Y-m-d');
        $this->end_date = $contract->end_date?->format('Y-m-d');
        $this->signatory_name = $contract->signatory_name;
        $this->signature_date = $contract->signature_date?->format('Y-m-d');
        $this->notes = $contract->notes;
        $this->document = null;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'contact_id' => $this->contact_id,
            'contract_category' => $this->contract_category,
            'contract_subtype' => $this->contract_subtype ?: null,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date ?: null,
            'signatory_name' => $this->signatory_name ?: null,
            'signature_date' => $this->signature_date ?: null,
            'notes' => $this->notes,
            'status' => 'active',
            'type' => 'contract',
        ];

        if ($this->editingContract) {
            $this->editingContract->update($data);
            $contract = $this->editingContract;
        } else {
            $data['reference'] = $this->generateReference();
            $data['created_by'] = auth()->id();
            $contract = Contract::create($data);
        }

        // Upload du document
        if ($this->document) {
            $this->uploadDocument($contract);
        }

        $message = $this->editingContract
            ? "Contrat '{$contract->reference}' mis à jour avec succès."
            : "Contrat '{$contract->reference}' créé avec succès.";

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => $message
        ]);

        $this->closeModal();
    }

    protected function uploadDocument(Contract $contract)
    {
        // Supprimer l'ancien document s'il existe
        if ($contract->document_path) {
            Storage::disk('public')->delete($contract->document_path);
        }

        $originalName = $this->document->getClientOriginalName();
        $extension = $this->document->getClientOriginalExtension();
        $size = $this->document->getSize();

        // Stocker le fichier
        $path = $this->document->store('contracts/' . date('Y/m'), 'public');

        $contract->update([
            'document_path' => $path,
            'document_name' => $originalName,
            'document_type' => $extension,
            'document_size' => $size,
            'document_uploaded_at' => now(),
        ]);
    }

    public function removeDocument(Contract $contract)
    {
        $contract->deleteDocument();
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Document supprimé du contrat '{$contract->reference}'."
        ]);
    }

    public function viewDocument(Contract $contract)
    {
        $this->viewingContract = $contract;
        $this->showDocumentModal = true;
    }

    public function downloadDocument(Contract $contract)
    {
        if (!$contract->document_path || !Storage::disk('public')->exists($contract->document_path)) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => "Le document n'existe pas."
            ]);
            return;
        }

        return Storage::disk('public')->download(
            $contract->document_path,
            $contract->document_name
        );
    }

    public function toggleStatus(Contract $contract)
    {
        $newStatus = match ($contract->status) {
            'active' => 'suspended',
            'suspended' => 'active',
            'draft' => 'active',
            default => $contract->status,
        };

        $contract->update(['status' => $newStatus]);

        $statusLabel = Contract::STATUSES[$newStatus] ?? $newStatus;
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Contrat '{$contract->reference}' passé en statut: {$statusLabel}."
        ]);
    }

    public function terminateContract(Contract $contract)
    {
        $contract->update(['status' => 'terminated']);
        $this->dispatch('toast', [
            'type' => 'warning',
            'message' => "Contrat '{$contract->reference}' résilié."
        ]);
    }

    protected function generateReference(): string
    {
        $year = date('Y');
        $lastContract = Contract::withTrashed()
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastContract
            ? intval(substr($lastContract->reference, -5)) + 1
            : 1;

        return sprintf('CTR-%s-%05d', $year, $nextNumber);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showDocumentModal = false;
        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->editingContract = null;
        $this->viewingContract = null;
        $this->contact_id = '';
        $this->contract_category = 'client';
        $this->contract_subtype = '';
        $this->start_date = '';
        $this->end_date = '';
        $this->signatory_name = '';
        $this->signature_date = '';
        $this->notes = '';
        $this->document = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('crm::livewire.contract-list', [
            'contracts' => $this->contracts,
            'stats' => $this->stats,
            'contacts' => $this->contacts,
            'categories' => Contract::CATEGORIES,
            'subtypes' => Contract::SUBTYPES,
            'statuses' => Contract::STATUSES,
        ])->layout('layouts.app');
    }
}
