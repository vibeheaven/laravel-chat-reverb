<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\MessageStatusUpdated;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    /**
     * Konuşmanın mesajlarını getir
     */
    public function index(Request $request, Conversation $conversation)
    {
        // Kullanıcının bu konuşmada olup olmadığını kontrol et
        if (!$conversation->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()
            ->with(['sender', 'statuses.user'])
            ->get()
            ->map(function ($message) use ($request) {
                $status = $message->sender_id === $request->user()->id
                    ? $message->overall_status
                    : ($message->getStatusForUser($request->user()->id)?->status ?? 'pending');

                $messageData = [
                    'id' => $message->id,
                    'text' => $message->text,
                    'type' => $message->type ?? 'normal',
                    'metadata' => $message->metadata,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name,
                    'sender_avatar' => $message->sender->avatar_url,
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    'time' => $message->created_at->format('H:i'),
                    'date' => $message->created_at->format('d M Y'),
                    'status' => $status,
                    'is_mine' => $message->sender_id === $request->user()->id,
                ];

                // Dosya eki varsa ekle
                if ($message->attachment_path) {
                    $messageData['attachment'] = [
                        'url' => asset('storage/' . $message->attachment_path),
                        'name' => $message->attachment_name,
                        'type' => $message->attachment_type,
                        'size' => $message->attachment_size,
                    ];
                }

                return $messageData;
            });

        return response()->json($messages);
    }

    /**
     * Yeni mesaj gönder
     */
    public function store(Request $request, Conversation $conversation)
    {
        // Kullanıcının bu konuşmada olup olmadığını kontrol et
        if (!$conversation->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'text' => 'required|string|max:5000',
            'type' => 'sometimes|in:normal,mention,command',
            'metadata' => 'sometimes|array',
        ]);

        // Mention kontrolü yap
        $mentionedUserIds = [];
        if (preg_match_all('/@(\w+)/', $validated['text'], $matches)) {
            // @ ile başlayan kelimeleri bul ve kullanıcı adlarıyla eşleştir
            $mentionedNames = array_unique($matches[1]); // Tekrarları kaldır
            $mentionedUsers = $conversation->members()
                ->whereIn('users.name', $mentionedNames)
                ->get();
            
            if ($mentionedUsers->isNotEmpty()) {
                $validated['type'] = 'mention';
                $mentionedUserIds = $mentionedUsers->pluck('id')->toArray();
                
                // Detaylı log bilgisi hazırla
                $mentionDetails = $mentionedUsers->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ];
                })->toArray();
                
                // Laravel Log'a detaylı yazdır
                Log::info('🏷️ MENTION ALERT - Kullanıcı Etiketlendi', [
                    'type' => 'mention',
                    'conversation_id' => $conversation->id,
                    'conversation_type' => $conversation->type,
                    'conversation_name' => $conversation->name ?? 'Private Chat',
                    'sender_id' => $request->user()->id,
                    'sender_name' => $request->user()->name,
                    'sender_email' => $request->user()->email,
                    'mentioned_count' => count($mentionedUserIds),
                    'mentioned_users' => $mentionDetails,
                    'message_text' => $validated['text'],
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ]);
                
                // Console'a detaylı yazdır
                info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
                info("🏷️  MENTION ALERT - Kullanıcı Etiketlendi!");
                info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
                info("👤 Gönderen: {$request->user()->name} (ID: {$request->user()->id})");
                info("💬 Konuşma: #" . $conversation->id . " (" . ($conversation->type === 'group' ? $conversation->name : 'Özel Sohbet') . ")");
                info("📝 Mesaj: " . $validated['text']);
                info("🎯 Etiketlenenler (" . count($mentionedUserIds) . " kişi):");
                foreach ($mentionedUsers as $mentionedUser) {
                    info("   → {$mentionedUser->name} (ID: {$mentionedUser->id}, Email: {$mentionedUser->email})");
                }
                info("⏰ Zaman: " . now()->format('Y-m-d H:i:s'));
                info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            }
        }

        DB::beginTransaction();
        try {
            // Mesajı oluştur
            $messageData = [
                'conversation_id' => $conversation->id,
                'sender_id' => $request->user()->id,
                'text' => $validated['text'],
                'type' => $validated['type'] ?? 'normal',
            ];
            
            // Metadata ekle
            if (!empty($mentionedUserIds)) {
                $messageData['metadata'] = ['mentioned_users' => $mentionedUserIds];
            } elseif (isset($validated['metadata'])) {
                $messageData['metadata'] = $validated['metadata'];
            }
            
            $message = Message::create($messageData);

            // Diğer üyeler için mesaj durumları oluştur
            $otherMembers = $conversation->members()
                ->where('user_id', '!=', $request->user()->id)
                ->pluck('users.id');

            foreach ($otherMembers as $memberId) {
                MessageStatus::create([
                    'message_id' => $message->id,
                    'user_id' => $memberId,
                    'status' => 'pending',
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            // Mesajı ve conversation'ı yeniden yükle (ilişkilerle birlikte)
            $message->load(['sender', 'statuses']);
            $message->loadMissing('conversation');

            // Broadcast olayını tetikle
            broadcast(new MessageSent($message, $conversation))->toOthers();

            return response()->json([
                'message' => 'Message sent successfully',
                'data' => [
                    'id' => $message->id,
                    'text' => $message->text,
                    'type' => $message->type,
                    'metadata' => $message->metadata,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name,
                    'sender_avatar' => $message->sender->avatar_url,
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    'time' => $message->created_at->format('H:i'),
                    'date' => $message->created_at->format('d M Y'),
                    'status' => 'pending',
                    'is_mine' => true,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to send message', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mesaj durumunu güncelle (delivered/read)
     */
    public function updateStatus(Request $request, Message $message)
    {
        $validated = $request->validate([
            'status' => 'required|in:delivered,read',
        ]);

        // Mesajın göndereni durumunu güncelleyemez
        if ($message->sender_id === $request->user()->id) {
            return response()->json(['message' => 'Cannot update own message status'], 400);
        }

        $status = MessageStatus::updateOrCreate(
            [
                'message_id' => $message->id,
                'user_id' => $request->user()->id,
            ],
            [
                'status' => $validated['status'],
                'updated_at' => now(),
            ]
        );

        // Durum değişikliğini broadcast et
        broadcast(new MessageStatusUpdated($message, $request->user()))->toOthers();

        return response()->json([
            'message' => 'Message status updated successfully',
            'status' => $status
        ]);
    }

    /**
     * Konuşmadaki tüm mesajları okundu olarak işaretle
     */
    public function markAllAsRead(Request $request, Conversation $conversation)
    {
        // Kullanıcının bu konuşmada olup olmadığını kontrol et
        if (!$conversation->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Kullanıcının okunmamış tüm mesajlarını okundu olarak işaretle
        $messages = $conversation->messages()
            ->where('sender_id', '!=', $request->user()->id)
            ->whereHas('statuses', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->where('status', '!=', 'read');
            })
            ->get();

        foreach ($messages as $message) {
            MessageStatus::updateOrCreate(
                [
                    'message_id' => $message->id,
                    'user_id' => $request->user()->id,
                ],
                [
                    'status' => 'read',
                    'updated_at' => now(),
                ]
            );

            // Durum değişikliğini broadcast et
            broadcast(new MessageStatusUpdated($message, $request->user()))->toOthers();
        }

        return response()->json(['message' => 'All messages marked as read']);
    }
}

