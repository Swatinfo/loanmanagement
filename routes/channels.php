<?php

use Illuminate\Support\Facades\Broadcast;

/*
 * Private channel authorization. Only the user themselves can subscribe to
 * their own notification channel.
 */
Broadcast::channel('users.{userId}', function ($user, int $userId) {
    return (int) $user->id === $userId;
});
