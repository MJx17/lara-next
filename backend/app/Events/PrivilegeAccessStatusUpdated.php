<?php

namespace App\Events;

use App\Models\PrivilegeAccessRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrivilegeAccessStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $request;

    public function __construct(PrivilegeAccessRequest $request)
    {
        $this->request = $request;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('privilege-requests');
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->request->id,
            'status' => $this->request->status,
            'user' => $this->request->user->name,
            'updated_at' => $this->request->updated_at,
        ];
    }

    public function broadcastAs(): string
    {
        return 'PrivilegeRequestUpdated';
    }
}
