<?php

namespace App\Modules\Core\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class NotificationsList extends Component
{
    use WithPagination;

    public string $filter = 'all'; // all, unread, read
    public string $type = '';

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = auth()->user()->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function deleteNotification(string $notificationId): void
    {
        auth()->user()->notifications()->where('id', $notificationId)->delete();
    }

    public function render()
    {
        $query = auth()->user()->notifications();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        if ($this->type) {
            $query->where('type', 'like', "%{$this->type}%");
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('livewire.core.notifications-list', [
            'notifications' => $notifications,
        ]);
    }
}
