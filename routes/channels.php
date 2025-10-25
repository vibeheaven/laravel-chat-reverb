<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

Broadcast::channel('chat.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);
    
    if (!$conversation) {
        return false;
    }
    
    // Kullanıcı bu konuşmanın üyesi mi?
    return $conversation->members()->where('user_id', $user->id)->exists();
});
