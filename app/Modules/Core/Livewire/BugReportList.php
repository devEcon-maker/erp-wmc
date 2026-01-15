<?php

namespace App\Modules\Core\Livewire;

use App\Modules\Core\Models\BugReport;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class BugReportList extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $filterType = '';
    public $filterStatus = '';
    public $filterPriority = '';
    public $showMyReports = false;

    // Form Modal
    public $showModal = false;
    public $editingReport = null;
    public $type = 'bug';
    public $priority = 'normal';
    public $title = '';
    public $description = '';
    public $page_url = '';
    public $steps_to_reproduce = '';
    public $expected_behavior = '';
    public $actual_behavior = '';
    public $screenshot;

    // Response Modal (Admin only)
    public $showResponseModal = false;
    public $respondingReport = null;
    public $response_status = '';
    public $admin_response = '';

    // View Modal
    public $showViewModal = false;
    public $viewingReport = null;

    protected $queryString = ['search', 'filterType', 'filterStatus', 'filterPriority'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->reset(['editingReport', 'type', 'priority', 'title', 'description', 'page_url', 'steps_to_reproduce', 'expected_behavior', 'actual_behavior', 'screenshot']);
        $this->type = 'bug';
        $this->priority = 'normal';
        $this->page_url = url()->previous();
        $this->showModal = true;
    }

    public function edit(BugReport $report)
    {
        // Seul l'auteur ou un admin peut modifier
        if ($report->user_id !== auth()->id() && !auth()->user()->hasAnyRole(['super-admin', 'admin'])) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Vous ne pouvez pas modifier ce rapport.']);
            return;
        }

        $this->editingReport = $report;
        $this->type = $report->type;
        $this->priority = $report->priority;
        $this->title = $report->title;
        $this->description = $report->description;
        $this->page_url = $report->page_url;
        $this->steps_to_reproduce = $report->steps_to_reproduce;
        $this->expected_behavior = $report->expected_behavior;
        $this->actual_behavior = $report->actual_behavior;
        $this->showModal = true;
    }

    public function save()
    {
        $rules = [
            'type' => 'required|in:bug,improvement,feature,question',
            'priority' => 'required|in:low,normal,high,critical',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'page_url' => 'nullable|string|max:500',
            'steps_to_reproduce' => 'nullable|string',
            'expected_behavior' => 'nullable|string',
            'actual_behavior' => 'nullable|string',
            'screenshot' => 'nullable|image|max:5120',
        ];

        $this->validate($rules);

        $data = [
            'type' => $this->type,
            'priority' => $this->priority,
            'title' => $this->title,
            'description' => $this->description,
            'page_url' => $this->page_url,
            'steps_to_reproduce' => $this->steps_to_reproduce,
            'expected_behavior' => $this->expected_behavior,
            'actual_behavior' => $this->actual_behavior,
            'browser' => request()->header('User-Agent'),
        ];

        if ($this->screenshot) {
            $path = $this->screenshot->store('bug-reports', 'public');
            $data['screenshot_path'] = $path;
        }

        if ($this->editingReport) {
            $this->editingReport->update($data);
            $message = 'Rapport mis a jour avec succes.';
        } else {
            $data['user_id'] = auth()->id();
            BugReport::create($data);
            $message = 'Rapport soumis avec succes. Merci pour votre contribution !';
        }

        $this->showModal = false;
        $this->dispatch('toast', ['type' => 'success', 'message' => $message]);
    }

    public function view(BugReport $report)
    {
        $this->viewingReport = $report->load(['user', 'resolver']);
        $this->showViewModal = true;
    }

    public function openResponseModal(BugReport $report)
    {
        if (!auth()->user()->hasAnyRole(['super-admin', 'admin'])) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Action non autorisee.']);
            return;
        }

        $this->respondingReport = $report;
        $this->response_status = $report->status;
        $this->admin_response = $report->admin_response ?? '';
        $this->showResponseModal = true;
    }

    public function saveResponse()
    {
        $this->validate([
            'response_status' => 'required|in:open,in_progress,resolved,closed,wont_fix',
            'admin_response' => 'nullable|string',
        ]);

        $updateData = [
            'status' => $this->response_status,
            'admin_response' => $this->admin_response,
        ];

        if (in_array($this->response_status, ['resolved', 'closed', 'wont_fix'])) {
            $updateData['resolved_by'] = auth()->id();
            $updateData['resolved_at'] = now();
        }

        $this->respondingReport->update($updateData);
        $this->showResponseModal = false;
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Reponse enregistree avec succes.']);
    }

    public function delete(BugReport $report)
    {
        // Seul l'auteur ou un admin peut supprimer
        if ($report->user_id !== auth()->id() && !auth()->user()->hasAnyRole(['super-admin', 'admin'])) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Vous ne pouvez pas supprimer ce rapport.']);
            return;
        }

        if ($report->screenshot_path) {
            Storage::disk('public')->delete($report->screenshot_path);
        }

        $report->delete();
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Rapport supprime avec succes.']);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showResponseModal = false;
        $this->showViewModal = false;
    }

    public function getStatsProperty()
    {
        $baseQuery = BugReport::query();

        if ($this->showMyReports) {
            $baseQuery->where('user_id', auth()->id());
        }

        return [
            'total' => (clone $baseQuery)->count(),
            'open' => (clone $baseQuery)->where('status', 'open')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'resolved' => (clone $baseQuery)->where('status', 'resolved')->count(),
            'bugs' => (clone $baseQuery)->where('type', 'bug')->count(),
            'improvements' => (clone $baseQuery)->where('type', 'improvement')->count(),
            'critical' => (clone $baseQuery)->where('priority', 'critical')->count(),
        ];
    }

    public function render()
    {
        $query = BugReport::with(['user', 'resolver'])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%")
                        ->orWhere('reference', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterPriority, fn($q) => $q->where('priority', $this->filterPriority))
            ->when($this->showMyReports, fn($q) => $q->where('user_id', auth()->id()))
            ->orderByRaw("FIELD(priority, 'critical', 'high', 'normal', 'low')")
            ->orderByRaw("FIELD(status, 'open', 'in_progress', 'resolved', 'closed', 'wont_fix')")
            ->latest();

        return view('core::livewire.bug-report-list', [
            'reports' => $query->paginate(15),
            'stats' => $this->stats,
            'types' => [
                'bug' => 'Bug',
                'improvement' => 'Amelioration',
                'feature' => 'Nouvelle fonctionnalite',
                'question' => 'Question',
            ],
            'priorities' => [
                'low' => 'Faible',
                'normal' => 'Normale',
                'high' => 'Haute',
                'critical' => 'Critique',
            ],
            'statuses' => [
                'open' => 'Ouvert',
                'in_progress' => 'En cours',
                'resolved' => 'Resolu',
                'closed' => 'Ferme',
                'wont_fix' => 'Ne sera pas corrige',
            ],
            'isAdmin' => auth()->user()->hasAnyRole(['super-admin', 'admin']),
        ])->layout('layouts.app');
    }
}
