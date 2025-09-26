<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('transactions.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
