<?php

namespace App\Modules\Core\Livewire;

use Livewire\Component;

class NotificationDropdown extends Component
{
    public $notifications;
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        $user = auth()->user();
        $this->notifications = $user->unreadNotifications->take(10);
        $this->unreadCount = $user->unreadNotifications->count();
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
