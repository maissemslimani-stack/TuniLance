<?php

namespace App\Actions\Chat;

use App\Models\ChatRoom;
use App\Models\User;

class FindOrCreateRoomAction
{
    public function execute(User $client, User $freelancer): ChatRoom
    {
        return ChatRoom::firstOrCreate([
            'client_id' => $client->id,
            'freelancer_id' => $freelancer->id,
        ]);
    }
}