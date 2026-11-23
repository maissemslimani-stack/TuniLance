<?php

use App\Models\ChatRoom;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat-room.{chatRoomId}', function ($user, $chatRoomId) {
    $room = ChatRoom::find($chatRoomId);

    if (! $room) {
        return false;
    }

    return $user->id === $room->client_id
        || $user->id === $room->freelancer_id
        || $user->isModerator(); // moderators can always listen in, per your dispute-review design
});