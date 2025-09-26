<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\User;
use App\Events\TransactionCreated;

class TransactionController extends Controller
{

    // public function index(Request $request)
    // {
    //     $user = $request->user();

    //     $transactions = Transaction::with(['sender', 'receiver'])
    //         ->where('sender_id', $user->id)
    //         ->orWhere('receiver_id', $user->id)
    //         ->latest()
    //         ->get();

    //     return response()->json([
    //         'balance' => $user->balance,
    //         'transactions' => $transactions,
    //     ]);
    // }

    public function index()
    {
        $user = auth()->user();

        $transactions = Transaction::with(['sender', 'receiver'])
            ->where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'sender' => [
                        'id' => $tx->sender->id,
                        'name' => $tx->sender->name,
                    ],
                    'receiver' => [
                        'id' => $tx->receiver->id,
                        'name' => $tx->receiver->name,
                    ],
                    'amount' => $tx->amount,
                    'created_at' => $tx->created_at,
                ];
            });

        return response()->json([
            'balance' => $user->balance,
            'transactions' => $transactions,
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value == $request->user()->id) {
                        $fail('You cannot send money to yourself.');
                    }
                },
            ],
            'amount' => 'required|numeric|min:0.01',
        ]);

        $sender = $request->user();
        $receiver = User::findOrFail($validated['receiver_id']);
        $amount = $validated['amount'];

        // Commission fee (1.5%)
        $commission = round($amount * 0.015, 2);
        $totalDebit = $amount + $commission;

        if ($sender->balance < $totalDebit) {
            return response()->json(['message' => 'Insufficient balance'], 422);
        }

        $transaction = DB::transaction(function () use ($sender, $receiver, $amount, $commission) {
            $sender->decrement('balance', $amount + $commission);
            $receiver->increment('balance', $amount);

            $transaction = Transaction::create([
                'sender_id'      => $sender->id,
                'receiver_id'    => $receiver->id,
                'amount'         => $amount,
                'commission_fee' => $commission,
            ]);

            // Broadcast for both users
            broadcast(new TransactionCreated($transaction, $sender));
            broadcast(new TransactionCreated($transaction, $receiver));

            return $transaction;
        });

        return response()->json([
            'message' => 'Transfer successful',
            'transaction' => $transaction->load('sender', 'receiver'),
        ], 201);
    }
}
