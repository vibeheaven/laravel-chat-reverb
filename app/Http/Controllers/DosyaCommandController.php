<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageStatus;
use App\Models\SymDosya\Dosyalar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DosyaCommandController extends Controller
{
    /**
     * Dosya arama - dosyano'ya göre
     */
    public function searchDosya(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|min:1',
        ]);

        try {
            $dosyalar = Dosyalar::where('dosyano', 'LIKE', '%' . $validated['query'] . '%')
                ->orWhere('adisoyadi', 'LIKE', '%' . $validated['query'] . '%')
                ->orWhere('tc', 'LIKE', '%' . $validated['query'] . '%')
                ->with(['dosyaSorumlusu', 'dosyaTemsilcisi', 'avukat'])
                ->limit(10)
                ->get()
                ->map(function ($dosya) {
                    return [
                        'id' => $dosya->id,
                        'dosyano' => $dosya->dosyano,
                        'adisoyadi' => $dosya->adisoyadi,
                        'tc' => $dosya->tc,
                        'telefon' => $dosya->telefon,
                        'il' => $dosya->il,
                        'ilce' => $dosya->ilce,
                        'durum' => $dosya->durum,
                        'dosya_sorumlusu' => $dosya?->dosyaSorumlusu->adi ?? 'Tanımsız',
                        'dosya_temsilcisi' => $dosya?->dosyaTemsilcisi->adi ?? 'Tanımsız',
                        'avukat' => $dosya->avukat->adi ?? 'Tanımsız',
                        'kaza_ozeti' => $dosya->kazaozeti,
                        'sigortasirketi' => $dosya->sigortasirketi,
                        'anlasma_tutari' => $dosya->anlasmatutari,
                    ];
                });

            return response()->json($dosyalar);
        } catch (\Exception $e) {
            Log::error('Dosya arama hatası: ' . $e->getMessage(). ' - ' . $e->getFile(). ' - ' . $e->getLine());
            return response()->json(['error' => 'Dosya arama sırasında hata oluştu'], 500);
        }
    }

    /**
     * Dosya bilgisini sohbete gönder
     */
    public function sendDosyaToChat(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'dosya_id' => 'required|integer',
        ]);

        // Kullanıcının bu konuşmada olup olmadığını kontrol et
        if (!$conversation->members()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            // Dosya bilgisini al
            $dosya = Dosyalar::with(['dosyaSorumlusu', 'dosyaTemsilcisi', 'avukat', 'bolgeKoordinatoru'])
                ->findOrFail($validated['dosya_id']);

            // Dosya bilgisini formatted text olarak hazırla
            $messageText = $this->formatDosyaMessage($dosya);

            DB::beginTransaction();

            // Mesajı oluştur
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $request->user()->id,
                'text' => $messageText,
                'type' => 'command',
                'metadata' => json_encode([
                    'command' => 'dosya',
                    'dosya_id' => $dosya->id,
                    'dosyano' => $dosya->dosyano,
                    'adisoyadi' => $dosya->adisoyadi,
                    'tc' => $dosya->tc,
                ])
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

            // Log'a yazdır
            Log::channel('single')->info('📁 DOSYA COMMAND: Dosya paylaşıldı', [
                'conversation_id' => $conversation->id,
                'sender_id' => $request->user()->id,
                'sender_name' => $request->user()->name,
                'dosya_id' => $dosya->id,
                'dosyano' => $dosya->dosyano,
                'adisoyadi' => $dosya->adisoyadi,
            ]);

            // Console'a yazdır
            info(sprintf(
                "📁 DOSYA COMMAND: User #%d (%s) shared dosya #%d (%s - %s) in conversation #%d",
                $request->user()->id,
                $request->user()->name,
                $dosya->id,
                $dosya->dosyano,
                $dosya->adisoyadi,
                $conversation->id
            ));

            // Mesajı yeniden yükle
            $message->load(['sender', 'statuses']);
            $message->loadMissing('conversation');

            // Broadcast olayını tetikle
            broadcast(new MessageSent($message, $conversation))->toOthers();

            return response()->json([
                'message' => 'Dosya başarıyla paylaşıldı',
                'data' => [
                    'id' => $message->id,
                    'text' => $message->text,
                    'type' => $message->type,
                    'metadata' => json_decode($message->metadata),
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
            Log::error('Dosya paylaşım hatası: ' . $e->getMessage(). ' - ' . $e->getFile(). ' - ' . $e->getLine());
            return response()->json(['error' => 'Dosya paylaşımı sırasında hata oluştu'], 500);
        }
    }

    /**
     * Dosya bilgisini formatla
     */
    private function formatDosyaMessage($dosya): string
    {
        $lines = [
            "━━━━━━━━━━━━━━━━━━━━━━",
            "📁 DOSYA BİLGİSİ",
            "━━━━━━━━━━━━━━━━━━━━━━",
            "",
            "🔢 Dosya No: {$dosya->dosyano}",
            "👤 Adı Soyadı: {$dosya->adisoyadi}",
            "🆔 TC: {$dosya->tc}",
        ];

        if ($dosya->telefon) {
            $lines[] = "📞 Telefon: {$dosya->telefon}";
        }

        if ($dosya->il) {
            $lines[] = "📍 Şehir: {$dosya->il}" . ($dosya->ilce ? " / {$dosya->ilce}" : "");
        }

        if (isset($dosya->dosyaSorumlusu) && isset($dosya->dosyaSorumlusu->adi)) {
            $lines[] = "👨‍💼 Dosya Sorumlusu: {$dosya->dosyaSorumlusu->adi}";
        }

        if (isset($dosya->dosyaTemsilcisi) && isset($dosya->dosyaTemsilcisi->adi)) {
            $lines[] = "👔 Dosya Temsilcisi: {$dosya->dosyaTemsilcisi->adi}";
        }

        if (isset($dosya->avukat) && isset($dosya->avukat->adi)) {
            $lines[] = "⚖️ Avukat: {$dosya->avukat->adi}";
        }

        if (isset($dosya->sigortasirketi)) {
            $lines[] = "🏢 Sigorta Şirketi: {$dosya->sigortasirketi}";
        }

        if (isset($dosya->anlasmatutari)) {
            $lines[] = "💰 Anlaşma Tutarı: " . number_format($dosya->anlasmatutari, 2, ',', '.') . " ₺";
        }

        if (isset($dosya->durum)) {
            $lines[] = "📊 Durum: {$dosya->durum}";
        }

        if (isset($dosya->kazaozeti)) {
            $lines[] = "";
            $lines[] = "📝 Kaza Özeti:";
            $lines[] = "─────────────────────";
            $lines[] = substr($dosya->kazaozeti, 0, 200) . (strlen($dosya->kazaozeti) > 200 ? '...' : '');
        }

        $lines[] = "";
        $lines[] = "━━━━━━━━━━━━━━━━━━━━━━";

        return implode("\n", $lines);
    }
}

