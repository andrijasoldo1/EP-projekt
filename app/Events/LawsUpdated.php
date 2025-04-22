<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class LawsUpdated implements ShouldBroadcast
{
    use SerializesModels;

    public $laws;

    public function __construct(array $laws)
    {
        $this->laws = $laws;
    }

    public function broadcastOn()
    {
        return new Channel('laws');
    }

    public function broadcastAs()
    {
        return 'laws.updated';
    }
}
