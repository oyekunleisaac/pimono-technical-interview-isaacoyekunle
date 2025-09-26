<?php

namespace App\Events;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class TransactionCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction;
    public $userId;
    public $balance;

    public function __construct(Transaction $transaction, $user)
    {
        $this->transaction = $transaction->load('sender', 'receiver');
        $this->userId = $user->id;

        // compute balance fresh for the user
        $this->balance = User::find($user->id)->balance;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('transactions.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'TransactionCreated';
    }

    public function broadcastWith()
    {
        return [
            'transaction' => [
                'id' => $this->transaction->id,
                'sender' => [
                    'id' => $this->transaction->sender->id,
                    'name' => $this->transaction->sender->name,
                ],
                'receiver' => [
                    'id' => $this->transaction->receiver->id,
                    'name' => $this->transaction->receiver->name,
                ],
                'amount' => $this->transaction->amount,
                'created_at' => $this->transaction->created_at,
            ],
            'balance' => $this->balance, 
        ];
    }
}
