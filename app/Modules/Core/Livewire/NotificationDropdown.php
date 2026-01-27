<?php

namespace App\Modules\Core\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class NotificationDropdown extends Component
{
    public $notifications;
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->loadNotifications();
    }

    #[On('refresh-notifications')]
    public function loadNotifications(): void
    {
        $user = auth()->user();
        if ($user) {
            $this->notifications = $user->unreadNotifications()->take(10)->get();
            $this->unreadCount = $user->unreadNotifications()->count();
        }
    }

    public function openDropdown(): void
    {
        // Marquer toutes les notifications comme lues quand on ouvre le dropdown
        $user = auth()->user();
        if ($user && $user->unreadNotifications()->count() > 0) {
            $user->unreadNotifications->markAsRead();
            $this->unreadCount = 0;
        }
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = auth()->user()->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.core.notification-dropdown');
    }
}
