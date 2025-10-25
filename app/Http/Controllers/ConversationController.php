<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ConversationController extends Controller
{
    /**
     * Kullanıcının tüm konuşmalarını listele
     */
    public function index(Request $request)
    {
        $conversations = $request->user()
            ->conversations()
            ->with(['lastMessage.sender', 'members'])
            ->get()
            ->map(function ($conversation) use ($request) {
                $unreadCount = $conversation->unreadMessagesCount($request->user()->id);
                $lastMessage = $conversation->lastMessage;

                // Private konuşma ise diğer kullanıcının bilgilerini ekle
                if ($conversation->type === 'private') {
                    $otherUser = $conversation->getOtherUser($request->user()->id);
                    $conversation->display_name = $otherUser->name;
                    $conversation->display_image = $otherUser->avatar_url;
                } else {
                    $conversation->display_name = $conversation->name;
                    $conversation->display_image = $conversation->image_url;
                }

                return [
                    'id' => $conversation->id,
                    'type' => $conversation->type,
                    'name' => $conversation->display_name,
                    'image' => $conversation->display_image,
                    'description' => $conversation->description,
                    'unread_count' => $unreadCount,
                    'last_message' => $lastMessage ? [
                        'text' => $lastMessage->text,
                        'sender_name' => $lastMessage->sender->name,
                        'created_at' => $lastMessage->created_at->diffForHumans(),
                        'time' => $lastMessage->created_at->format('H:i'),
                    ] : null,
                    'members_count' => $conversation->members->count(),
                    'created_at' => $conversation->created_at,
                ];
            })
            ->sortByDesc(function ($conversation) {
                return $conversation['last_message']['created_at'] ?? $conversation['created_at'];
            })
            ->values();

        return response()->json($conversations);
    }

    /**
     * Belirli bir konuşmanın detaylarını getir
     */
    public function show(Request $request, Conversation $conversation)
    {
        // Kullanıcının bu konuşmada olup olmadığını kontrol et
        if (!$conversation->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $conversation->load(['members', 'creator']);

        $data = [
            'id' => $conversation->id,
            'type' => $conversation->type,
            'name' => $conversation->name,
            'description' => $conversation->description,
            'image' => $conversation->image_url,
            'created_by' => $conversation->created_by,
            'created_at' => $conversation->created_at->format('d M Y, H:i'),
            'members' => $conversation->members->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'avatar' => $member->avatar_url,
                    'role' => $member->pivot->role,
                    'is_admin' => $member->pivot->role === 1,
                    'joined_at' => $member->pivot->joined_at,
                ];
            }),
            'is_admin' => $conversation->isAdmin($request->user()->id),
        ];

        // Private konuşma ise diğer kullanıcının bilgilerini ekle
        if ($conversation->type === 'private') {
            $otherUser = $conversation->getOtherUser($request->user()->id);
            $data['other_user'] = [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'email' => $otherUser->email,
                'avatar' => $otherUser->avatar_url,
            ];
        }

        return response()->json($data);
    }

    /**
     * Yeni grup konuşması oluştur
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:private,group',
            'name' => 'required_if:type,group|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'members' => 'required|array|min:1',
            'members.*' => 'exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            // Eğer private konuşma ise, zaten var mı kontrol et
            if ($validated['type'] === 'private' && count($validated['members']) === 1) {
                $existingConversation = $request->user()->getPrivateConversationWith($validated['members'][0]);
                if ($existingConversation) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Conversation already exists',
                        'conversation' => $existingConversation
                    ], 200);
                }
            }

            $conversationData = [
                'type' => $validated['type'],
                'created_by' => $request->user()->id,
            ];

            if ($validated['type'] === 'group') {
                $conversationData['name'] = $validated['name'];
                $conversationData['description'] = $validated['description'] ?? null;

                // Grup resmi varsa kaydet
                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('conversation-images', 'public');
                    $conversationData['image'] = $path;
                }
            }

            $conversation = Conversation::create($conversationData);

            // Konuşmayı oluşturanı yönetici olarak ekle
            $conversation->members()->attach($request->user()->id, [
                'role' => 1,
                'joined_at' => now(),
            ]);

            // Diğer üyeleri ekle
            foreach ($validated['members'] as $memberId) {
                if ($memberId != $request->user()->id) {
                    $conversation->members()->attach($memberId, [
                        'role' => 2,
                        'joined_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Conversation created successfully',
                'conversation' => $conversation->load('members')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create conversation', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Grup bilgilerini güncelle
     */
    public function update(Request $request, Conversation $conversation)
    {
        // Sadece yönetici güncelleyebilir
        if (!$conversation->isAdmin($request->user()->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if (isset($validated['name'])) {
            $conversation->name = $validated['name'];
        }

        if (isset($validated['description'])) {
            $conversation->description = $validated['description'];
        }

        if ($request->hasFile('image')) {
            // Eski resmi sil
            if ($conversation->image) {
                Storage::disk('public')->delete($conversation->image);
            }
            $path = $request->file('image')->store('conversation-images', 'public');
            $conversation->image = $path;
        }

        $conversation->save();

        return response()->json([
            'message' => 'Conversation updated successfully',
            'conversation' => $conversation
        ]);
    }

    /**
     * Konuşmayı sil (sadece grup için)
     */
    public function destroy(Request $request, Conversation $conversation)
    {
        if ($conversation->type !== 'group' || !$conversation->isAdmin($request->user()->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Grup resmini sil
        if ($conversation->image) {
            Storage::disk('public')->delete($conversation->image);
        }

        $conversation->delete();

        return response()->json(['message' => 'Conversation deleted successfully']);
    }

    /**
     * Gruba üye ekle
     */
    public function addMember(Request $request, Conversation $conversation)
    {
        if (!$conversation->isAdmin($request->user()->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Zaten üye mi kontrol et
        if ($conversation->members()->where('user_id', $validated['user_id'])->exists()) {
            return response()->json(['message' => 'User is already a member'], 400);
        }

        $conversation->members()->attach($validated['user_id'], [
            'role' => 2,
            'joined_at' => now(),
        ]);

        return response()->json(['message' => 'Member added successfully']);
    }

    /**
     * Gruptan üye çıkar
     */
    public function removeMember(Request $request, Conversation $conversation, User $user)
    {
        if (!$conversation->isAdmin($request->user()->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Kendini çıkaramaz
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot remove yourself'], 400);
        }

        $conversation->members()->detach($user->id);

        return response()->json(['message' => 'Member removed successfully']);
    }

    /**
     * Üye rolünü güncelle
     */
    public function updateMemberRole(Request $request, Conversation $conversation, User $user)
    {
        if (!$conversation->isAdmin($request->user()->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'role' => 'required|in:1,2',
        ]);

        $conversation->members()->updateExistingPivot($user->id, [
            'role' => $validated['role'],
        ]);

        return response()->json(['message' => 'Member role updated successfully']);
    }
}

