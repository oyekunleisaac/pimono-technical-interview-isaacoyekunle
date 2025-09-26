<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    protected static function booted()
    {
        static::creating(function ($transaction) {
            if (!$transaction->uuid) {
                $transaction->uuid = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'uuid',
        'sender_id',
        'receiver_id',
        'amount',
        'commission_fee',
        'status',
        'meta',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
