# 🎯 @ Mention ve /dosya Komut Özellikleri

## 📋 Genel Bakış

Laravel Reverb Chat uygulamasına iki yeni güçlü özellik eklendi:

1. **@ Mention Sistemi** - Kullanıcıları etiketleme
2. **/dosya Komutu** - SymDosya veritabanından dosya paylaşma

---

## 🏷️ @ Mention Sistemi

### Özellikler

- ✅ Mesaj yazarken `@` ile kullanıcı etiketleme
- ✅ Otomatik tamamlama önerileri
- ✅ Klavye navigasyonu (↑↓ ok tuşları, Enter, Escape)
- ✅ Filtreleme (kullanıcı adına göre arama)
- ✅ Console ve Laravel Log'a bildirim
- ✅ Metadata ile mention bilgilerini saklama

### Kullanım

1. Mesaj input alanına `@` yazın
2. Kullanıcı listesi otomatik açılır
3. Kullanıcı adı yazarak filtreleyin veya ok tuşlarıyla seçin
4. Enter veya tıklayarak kullanıcıyı etiketleyin
5. Mesajınızı gönderin

### Teknik Detaylar

#### API Endpoints

```php
GET  /conversations/{conversation}/mentionable-users
POST /conversations/{conversation}/mention
```

#### Veritabanı

Mesaj tablosuna eklenen kolonlar:
```sql
- type: enum('normal', 'mention', 'command')
- metadata: json (mentioned_users: [id1, id2, ...])
```

#### Log Formatı

```
🏷️ MENTION: {sender_name} mentioned {user1, user2} in conversation #{id}
```

**Laravel Log:**
```json
{
  "conversation_id": 1,
  "sender": "Caner Kırıcı",
  "mentioned_users": ["Ahmet", "Mehmet"],
  "message": "@Ahmet @Mehmet bu dosyayı inceleyin"
}
```

### Örnek Senaryolar

**Senaryo 1: Grup sohbetinde bildirim**
```
@Mehmet bu dosyayı kontrol eder misin?
```
→ Mehmet etiketlendi, log'a kaydedildi

**Senaryo 2: Çoklu etiketleme**
```
@Ahmet @Ayşe @Can toplantı saat 15:00'te
```
→ 3 kullanıcı etiketlendi

---

## 📁 /dosya Komutu

### Özellikler

- ✅ `/dosya` komutu ile dosya arama
- ✅ SymDosya veritabanından gerçek zamanlı arama
- ✅ Dosyano, ad soyad, TC'ye göre arama
- ✅ Detaylı dosya bilgilerini sohbete gönderme
- ✅ Formatted mesaj (emoji'li, düzenli)
- ✅ Console ve Laravel Log'a kayıt

### Kullanım

1. Mesaj input alanına `/dosya` yazın
2. Ardından dosya no, ad soyad veya TC yazın
3. Otomatik arama sonuçları görünür
4. İstediğiniz dosyayı seçin (tıklayarak veya Enter)
5. Dosya bilgileri otomatik sohbete gönderilir

### Arama Örnekleri

```
/dosya 2024001    → Dosya no'ya göre ara
/dosya Ahmet      → Ad soyada göre ara
/dosya 12345      → TC'ye göre ara
```

### Dosya Bilgi Formatı

Seçilen dosya şu formatta paylaşılır:

```
📁 **DOSYA BİLGİSİ**

🔢 **Dosya No:** 2024001
👤 **Adı Soyadı:** Ahmet Yılmaz
🆔 **TC:** 12345678901
📞 **Telefon:** 0532 123 4567
📍 **Şehir:** İstanbul / Kadıköy
👨‍💼 **Dosya Sorumlusu:** Mehmet Demir
👨‍💼 **Dosya Temsilcisi:** Ayşe Kaya
⚖️ **Avukat:** Av. Fatma Şahin
🏢 **Sigorta Şirketi:** ABC Sigorta
💰 **Anlaşma Tutarı:** 50000 TL
📊 **Durum:** Devam Ediyor

📝 **Kaza Özeti:**
Kaza tarihi 15.05.2024...
```

### Teknik Detaylar

#### API Endpoints

```php
GET  /dosya/search?query={search_term}
POST /conversations/{conversation}/dosya
```

#### SymDosya Veritabanı Bağlantısı

```php
// config/database.php
'sc_mysql' => [
    'driver' => 'mysql',
    'host' => env('SC_DB_HOST', '127.0.0.1'),
    'database' => env('SC_DB_DATABASE'),
    'username' => env('SC_DB_USERNAME'),
    'password' => env('SC_DB_PASSWORD'),
],
```

#### Model İlişkileri

```php
Dosyalar::with([
    'dosyaSorumlusu',    // Dosya sorumlusu
    'dosyaTemsilcisi',   // Dosya temsilcisi
    'avukat',            // Avukat
    'bolgeKoordinatoru'  // Bölge koordinatörü
]);
```

#### Log Formatı

```
📁 DOSYA COMMAND: User #{id} ({name}) shared dosya #{dosya_id} ({dosyano} - {adisoyadi}) in conversation #{conversation_id}
```

### Örnek Senaryolar

**Senaryo 1: Dosya paylaşımı**
```
/dosya 2024001
```
→ Dosya detayları formatted olarak sohbete gönderilir

**Senaryo 2: Hızlı arama**
```
/dosya Ahmet
```
→ "Ahmet" içeren tüm dosyalar listelenir

---

## 🛠️ Kurulum ve Yapılandırma

### 1. Migration'ı Çalıştırın

```bash
php artisan migrate
```

Bu komut `messages` tablosuna `type` ve `metadata` kolonlarını ekler.

### 2. SymDosya Veritabanı Bağlantısı

`.env` dosyanıza ekleyin:

```env
SC_DB_HOST=127.0.0.1
SC_DB_PORT=3306
SC_DB_DATABASE=symdosya_db
SC_DB_USERNAME=root
SC_DB_PASSWORD=
```

### 3. Frontend'i Build Edin

```bash
npm run build
# veya geliştirme modu için:
npm run dev:tailwind
```

### 4. Reverb ve Laravel Sunucusunu Başlatın

```bash
# Terminal 1:
php artisan reverb:start

# Terminal 2:
php artisan serve
```

---

## 📝 Component Yapısı

### Yeni Bileşenler

1. **MentionCommandInput.vue**
   - @ mention ve /dosya komutlarını handle eder
   - Otomatik tamamlama UI'ı
   - Klavye navigasyonu

### Güncellenmiş Bileşenler

1. **ChatWindow.vue**
   - MentionCommandInput bileşenini kullanır
   - `sendMessage` ve `sendDosya` metodları

### Yeni Controller'lar

1. **MentionController.php**
   - `getMentionableUsers()` - Etiketlenebilir kullanıcıları listele
   - `sendMentionMessage()` - Mention mesajı gönder (şu an kullanılmıyor, otomatik tespit var)

2. **DosyaCommandController.php**
   - `searchDosya()` - Dosya ara
   - `sendDosyaToChat()` - Dosya bilgisini sohbete gönder

---

## 🔍 Debugging ve Loglama

### Console Log'ları

Tüm mention ve dosya işlemleri console'a yazılır:

```bash
# Laravel serve çalışırken göreceksiniz:
🏷️ MENTION: Caner Kırıcı mentioned Mehmet, Ahmet in conversation #1
📁 DOSYA COMMAND: User #1 (Caner Kırıcı) shared dosya #45 (2024001 - Ahmet Yılmaz) in conversation #1
```

### Laravel Log

Detaylı loglar için:

```bash
tail -f storage/logs/laravel.log
```

---

## 💡 İpuçları ve Best Practices

### Mention Sistemi

1. **Performans:** Mentionable users listesi ilk @ yazıldığında yüklenir ve cache'lenir
2. **Filtreleme:** Kullanıcı adı yazarken otomatik filtreleme yapılır
3. **Klavye Kısayolları:**
   - ↑↓: Liste içinde gezinme
   - Enter: Seçili kullanıcıyı etiketle
   - Escape: Listeyi kapat

### Dosya Komutu

1. **Hızlı Arama:** En az 1 karakter yazdıktan sonra arama başlar
2. **Çoklu Sonuç:** Maksimum 10 sonuç gösterilir
3. **Detaylı Bilgi:** Tüm dosya detayları otomatik formatted olarak paylaşılır

---

## 🐛 Sorun Giderme

### @ Mention Çalışmıyor

1. Konuşmada başka üye var mı kontrol edin
2. Console'da hata var mı kontrol edin
3. Migration çalıştırıldı mı kontrol edin

### /dosya Komutu Çalışmıyor

1. SymDosya veritabanı bağlantısını kontrol edin:
   ```bash
   php artisan tinker
   >>> App\Models\SymDosya\Dosyalar::count()
   ```

2. `.env` dosyasındaki SC_DB_* ayarlarını kontrol edin

3. Dosya modellerinin `app/Models/SymDosya/` klasöründe olduğundan emin olun

### Log'lar Görünmüyor

1. Laravel serve'in çalıştığından emin olun (console log'lar için)
2. `storage/logs/laravel.log` dosyası yazılabilir mi kontrol edin
3. Log level kontrolü: `config/logging.php`

---

## 📊 Veritabanı Şeması

### messages Tablosu (Güncellenmiş)

| Kolon         | Tip                              | Açıklama                           |
|---------------|----------------------------------|------------------------------------|
| id            | bigint                           | Primary key                        |
| conversation_id| bigint                          | Konuşma ID                         |
| sender_id     | bigint                           | Gönderen kullanıcı                 |
| text          | text                             | Mesaj içeriği                      |
| **type**      | enum('normal','mention','command')| **YENİ:** Mesaj tipi              |
| **metadata**  | json                             | **YENİ:** Ek bilgiler (mentions, dosya vb.) |
| created_at    | timestamp                        | Oluşturma tarihi                   |
| updated_at    | timestamp                        | Güncelleme tarihi                  |

### metadata JSON Yapısı

**Mention mesajı için:**
```json
{
  "mentioned_users": [1, 3, 5]
}
```

**Dosya komutu için:**
```json
{
  "command": "dosya",
  "dosya_id": 45,
  "dosyano": "2024001",
  "adisoyadi": "Ahmet Yılmaz",
  "tc": "12345678901"
}
```

---

## 🚀 Gelecek Özellikler (Öneriler)

- [ ] Mention bildirim sistemi (email/push)
- [ ] Mention'a tıklayınca kullanıcı profiline git
- [ ] /dosya detay modal'ı (daha fazla bilgi)
- [ ] Dosya güncelleme bildirimleri
- [ ] /komut listesi (help komutu)
- [ ] Özel komutlar (/rapor, /tarih vb.)
- [ ] Mention istatistikleri

---

## 📚 API Dokümantasyonu

### GET /conversations/{id}/mentionable-users

Konuşmadaki etiketlenebilir kullanıcıları listeler.

**Response:**
```json
[
  {
    "id": 2,
    "name": "Mehmet Demir",
    "email": "mehmet@example.com",
    "avatar": "https://ui-avatars.com/api/..."
  }
]
```

### GET /dosya/search

Dosya arama.

**Parameters:**
- `query` (string, required): Arama terimi

**Response:**
```json
[
  {
    "id": 45,
    "dosyano": "2024001",
    "adisoyadi": "Ahmet Yılmaz",
    "tc": "12345678901",
    "telefon": "0532 123 4567",
    "il": "İstanbul",
    "ilce": "Kadıköy",
    ...
  }
]
```

### POST /conversations/{id}/dosya

Dosya bilgisini sohbete gönderir.

**Request:**
```json
{
  "dosya_id": 45
}
```

**Response:**
```json
{
  "message": "Dosya başarıyla paylaşıldı",
  "data": {
    "id": 123,
    "text": "📁 **DOSYA BİLGİSİ**\n\n...",
    "type": "command",
    "metadata": {...},
    ...
  }
}
```

---

## ✅ Test Senaryoları

### Test 1: @ Mention

1. Bir grup sohbetine girin
2. `@` yazın
3. Kullanıcı listesini görün
4. Bir kullanıcı seçin
5. Mesajı gönderin
6. Console ve log'u kontrol edin

### Test 2: /dosya Komutu

1. Bir sohbete girin
2. `/dosya 2024` yazın
3. Arama sonuçlarını görün
4. Bir dosya seçin
5. Formatted dosya bilgisinin paylaşıldığını görün
6. Log'u kontrol edin

### Test 3: Karışık Kullanım

1. `/dosya 2024001` ile dosya paylaş
2. Ardından `@Mehmet bu dosyayı incele` yazın
3. Her ikisinin de doğru çalıştığını kontrol edin

---

## 📄 Lisans

Bu özellikler MIT lisansı altında lisanslanmıştır.

---

**🎉 Tebrikler! @ Mention ve /dosya özellikleri başarıyla entegre edildi!**

