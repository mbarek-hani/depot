<?php

namespace App\Events;

use App\Models\User;
use App\Support\Present;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Queue\SerializesModels;

class StockAlertCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public DatabaseNotification $notification,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('users.'.$this->user->id)];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    public function broadcastWith(): array
    {
        return Present::notification($this->notification);
    }
}
