<?php

namespace App\Modules\Core\Livewire;

use Livewire\Component;

class Help extends Component
{
    public $activeTab = 'overview';

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('core::livewire.help')->layout('layouts.app');
    }
}
