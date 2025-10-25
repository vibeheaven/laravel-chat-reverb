<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    /**
     * Dosya yükle ve mesaj olarak gönder
     */
    public function upload(Request $request, Conversation $conversation)
    {
        // Kullanıcının bu konuşmada olup olmadığını kontrol et
        if (!$conversation->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB
            'caption' => 'nullable|string|max:500',
        ]);

        $file = $request->file('file');
        
        // Dosya tipini belirle
        $mimeType = $file->getMimeType();
        $extension = $file->getClientOriginalExtension();
        
        $attachmentType = $this->determineAttachmentType($mimeType, $extension);
        
        // İzin verilen dosya tipleri kontrolü
        if (!$this->isAllowedFileType($attachmentType, $mimeType, $extension)) {
            return response()->json([
                'message' => 'Bu dosya tipi desteklenmiyor',
                'allowed_types' => 'PDF, resim (jpg, png, gif, webp), Word, Excel, PowerPoint'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Dosyayı kaydet
            $path = $file->store('conversation-files', 'public');
            $originalName = $file->getClientOriginalName();
            $size = $file->getSize();

            // Mesaj metnini hazırla
            $messageText = $validated['caption'] ?? '';
            if (empty($messageText)) {
                $messageText = "📎 {$originalName}";
            }

            // Mesajı oluştur
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $request->user()->id,
                'text' => $messageText,
                'type' => 'normal',
                'attachment_path' => $path,
                'attachment_name' => $originalName,
                'attachment_type' => $attachmentType,
                'attachment_size' => $size,
            ]);

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

            // Log
            Log::info('📎 FILE UPLOAD', [
                'conversation_id' => $conversation->id,
                'sender' => $request->user()->name,
                'file_name' => $originalName,
                'file_type' => $attachmentType,
                'file_size' => $this->formatBytes($size)
            ]);

            info(sprintf(
                "📎 FILE: %s uploaded %s (%s) to conversation #%d",
                $request->user()->name,
                $originalName,
                $this->formatBytes($size),
                $conversation->id
            ));

            // Mesajı yeniden yükle
            $message->load(['sender', 'statuses']);
            $message->loadMissing('conversation');

            // Broadcast
            broadcast(new MessageSent($message, $conversation))->toOthers();

            return response()->json([
                'message' => 'Dosya başarıyla yüklendi',
                'data' => [
                    'id' => $message->id,
                    'text' => $message->text,
                    'type' => $message->type,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name,
                    'sender_avatar' => $message->sender->avatar_url,
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    'time' => $message->created_at->format('H:i'),
                    'date' => $message->created_at->format('d M Y'),
                    'status' => 'pending',
                    'is_mine' => true,
                    'attachment' => [
                        'url' => asset('storage/' . $path),
                        'name' => $originalName,
                        'type' => $attachmentType,
                        'size' => $size,
                        'size_formatted' => $this->formatBytes($size),
                    ],
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Hata durumunda dosyayı sil
            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            
            Log::error('Dosya yükleme hatası: ' . $e->getMessage());
            return response()->json([
                'message' => 'Dosya yüklenirken hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dosya tipini belirle
     */
    private function determineAttachmentType($mimeType, $extension)
    {
        // Resim
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        // PDF
        if ($mimeType === 'application/pdf' || $extension === 'pdf') {
            return 'pdf';
        }

        // Word
        if (in_array($extension, ['doc', 'docx']) || 
            str_contains($mimeType, 'word') ||
            str_contains($mimeType, 'msword')) {
            return 'word';
        }

        // Excel
        if (in_array($extension, ['xls', 'xlsx']) || 
            str_contains($mimeType, 'excel') ||
            str_contains($mimeType, 'spreadsheet')) {
            return 'excel';
        }

        // PowerPoint
        if (in_array($extension, ['ppt', 'pptx']) || 
            str_contains($mimeType, 'powerpoint') ||
            str_contains($mimeType, 'presentation')) {
            return 'powerpoint';
        }

        // Diğer
        return 'document';
    }

    /**
     * İzin verilen dosya tipi mi kontrol et
     */
    private function isAllowedFileType($type, $mimeType, $extension)
    {
        $allowedTypes = ['image', 'pdf', 'word', 'excel', 'powerpoint', 'document'];
        
        if (!in_array($type, $allowedTypes)) {
            return false;
        }

        // Güvenlik için bazı dosya tiplerini engelle
        $blockedExtensions = ['exe', 'bat', 'cmd', 'sh', 'php', 'js', 'html', 'htm'];
        if (in_array(strtolower($extension), $blockedExtensions)) {
            return false;
        }

        return true;
    }

    /**
     * Byte'ları okunabilir formata çevir
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Dosyayı indir
     */
    public function download(Message $message)
    {
        if (!$message->attachment_path) {
            return response()->json(['message' => 'Bu mesajda dosya yok'], 404);
        }

        if (!Storage::disk('public')->exists($message->attachment_path)) {
            return response()->json(['message' => 'Dosya bulunamadı'], 404);
        }

        return Storage::disk('public')->download(
            $message->attachment_path,
            $message->attachment_name
        );
    }
}

