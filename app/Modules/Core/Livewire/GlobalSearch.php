<?php

namespace App\Modules\Core\Livewire;

use App\Modules\Core\Services\GlobalSearchService;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $query = '';
    public array $results = [];
    public bool $showDropdown = false;

    public function updatedQuery(): void
    {
        if (strlen($this->query) >= 2) {
            $this->results = app(GlobalSearchService::class)->search($this->query);
            $this->showDropdown = true;
        } else {
            $this->results = [];
            $this->showDropdown = false;
        }
    }

    public function closeDropdown(): void
    {
        $this->showDropdown = false;
    }

    public function openDropdown(): void
    {
        if (strlen($this->query) >= 2) {
            $this->showDropdown = true;
        }
    }

    public function clearSearch(): void
    {
        $this->query = '';
        $this->results = [];
        $this->showDropdown = false;
    }

    public function render()
    {
        return view('livewire.core.global-search');
    }
}
