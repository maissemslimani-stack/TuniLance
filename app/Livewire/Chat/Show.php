<?php

namespace App\Livewire\Chat;

use App\Actions\Chat\SendMessageAction;
use App\Models\ChatRoom;
use Livewire\Component;

class Show extends Component
{
    public ?ChatRoom $activeRoom = null;
    public string $newMessage = '';

    public function mount(?ChatRoom $chatRoom = null): void
    {
        $user = auth()->user();

        $rooms = ChatRoom::where('client_id', $user->id)
            ->orWhere('freelancer_id', $user->id)
            ->orderByDesc('last_message_at')
            ->get();

        $this->activeRoom = $chatRoom ?? $rooms->first();

        if ($this->activeRoom) {
            $this->dispatch('room-changed', roomId: $this->activeRoom->id);
        }
    }

    public function selectRoom(int $roomId): void
    {
        $this->activeRoom = ChatRoom::findOrFail($roomId);
        $this->dispatch('room-changed', roomId: $this->activeRoom->id);
    }

    public function addProposalTag(): void
    {
        $this->newMessage = trim($this->newMessage).' @proposal';
    }

    public function send(): void
    {
        $this->validate(['newMessage' => ['required', 'string', 'min:1']]);

        app(SendMessageAction::class)->execute($this->activeRoom, auth()->user(), $this->newMessage);

        $this->newMessage = '';
        $this->activeRoom->refresh();
    }
    public function receiveMessage(array $payload): void
    {
        $this->activeRoom->refresh();
    }
    public function render()
    {
        $user = auth()->user();

        $rooms = ChatRoom::where('client_id', $user->id)
            ->orWhere('freelancer_id', $user->id)
            ->with(['client', 'freelancer', 'lastMessage'])
            ->orderByDesc('last_message_at')
            ->get();

        $messages = $this->activeRoom
            ? $this->activeRoom->messages()->with('sender')->get()
            : collect();

        return view('livewire.chat.show', [
            'rooms' => $rooms,
            'messages' => $messages,
        ]);
    }
}