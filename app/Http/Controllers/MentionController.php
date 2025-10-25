<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MentionController extends Controller
{
    /**
     * Mention içeren mesaj gönder
     */
    public function sendMentionMessage(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:5000',
            'mentioned_users' => 'required|array',
            'mentioned_users.*' => 'exists:users,id',
        ]);

        // Kullanıcının bu konuşmada olup olmadığını kontrol et
        if (!$conversation->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Mention edilen kullanıcıların bilgilerini al
        $mentionedUsers = User::whereIn('id', $validated['mentioned_users'])->get();

        // Console ve Log'a yazdır
        $logMessage = sprintf(
            "🏷️ MENTION ALERT: User #%d (%s) mentioned %d user(s) in conversation #%d: %s",
            $request->user()->id,
            $request->user()->name,
            count($mentionedUsers),
            $conversation->id,
            $mentionedUsers->pluck('name')->implode(', ')
        );

        // Console'a yazdır (artisan serve çalışıyorsa görünür)
        info($logMessage);

        // Laravel Log'a yazdır
        Log::channel('single')->info($logMessage, [
            'conversation_id' => $conversation->id,
            'sender_id' => $request->user()->id,
            'sender_name' => $request->user()->name,
            'mentioned_user_ids' => $validated['mentioned_users'],
            'mentioned_user_names' => $mentionedUsers->pluck('name')->toArray(),
            'message_text' => $validated['text'],
            'type' => 'mention'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mention logged successfully',
            'mentioned_users' => $mentionedUsers->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar_url,
                ];
            })
        ]);
    }

    /**
     * Konuşmadaki mention edilebilir kullanıcıları listele
     */
    public function getMentionableUsers(Request $request, Conversation $conversation)
    {
        // Kullanıcının bu konuşmada olup olmadığını kontrol et
        if (!$conversation->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Kendi dışındaki tüm üyeleri getir
        $members = $conversation->members()
            ->where('users.id', '!=', $request->user()->id)
            ->select('users.id', 'users.name', 'users.email')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar_url,
                ];
            });

        return response()->json($members);
    }
}

