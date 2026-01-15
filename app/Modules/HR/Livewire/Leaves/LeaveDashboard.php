<?php

namespace App\Modules\HR\Livewire\Leaves;

use Livewire\Component;

class LeaveDashboard extends Component
{
    public function render()
    {
        return view('hr.leaves.dashboard')->layout('layouts.app');
    }
}
