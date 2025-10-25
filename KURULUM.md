# Laravel Reverb Chat - Kurulum ve Kullanım Kılavuzu

Bu proje, Laravel Reverb kullanarak gerçek zamanlı WhatsApp benzeri bir chat uygulamasıdır.

## Özellikler

✅ **Grup Sohbeti Oluşturma** - Birden fazla kullanıcıyla grup sohbetleri oluşturun
✅ **Özel Sohbet** - 1:1 özel mesajlaşma
✅ **WhatsApp Benzeri Dashboard** - Toplam mesaj, okunmamış mesaj, son mesaj gösterimi
✅ **Mesaj Durumları** 
   - ✓ Tek tık: Bekliyor (sistem kapalıysa)
   - ✓✓ Çift gri tık: İletildi (sistem açıksa)
   - ✓✓ Çift mavi tık: Görüldü (mesaj okundu)
   - Gruplarda: Herkese iletildi/herkes gördü mantığı
✅ **Avatar Entegrasyonu** - ui-avatars.com otomatik avatar oluşturma
✅ **Grup Yönetimi**
   - Grup resmi yükleme ve düzenleme
   - Grup açıklaması ekleme
   - Üye ekleme/çıkarma
   - Yetki sistemi (Yönetici/Üye)
✅ **Grup/Kişi Bilgi Modal** - Detaylı bilgi görüntüleme
✅ **Gerçek Zamanlı Mesajlaşma** - Laravel Reverb ile anlık mesajlaşma

## Kurulum Adımları

### 1. Veritabanı Migration'larını Çalıştırın

Yeni veritabanı tablolarını oluşturmak için migration'ları çalıştırın:

```bash
php artisan migrate
```

Bu komut aşağıdaki tabloları oluşturacak:
- `conversations` - Sohbet bilgileri (grup/private)
- `conversation_members` - Sohbet üyeleri ve rolleri
- `messages` - Mesajlar
- `message_statuses` - Mesaj durumları (pending/delivered/read)

### 2. Storage Link Oluşturun

Grup resimleri için storage linkini oluşturun:

```bash
php artisan storage:link
```

### 3. NPM Paketlerini Yükleyin ve Build Edin

```bash
npm install
npm run build
```

veya geliştirme modunda çalıştırmak için:

```bash
npm run dev
```

### 4. Laravel Reverb'ü Başlatın

Reverb sunucusunu başlatın:

```bash
php artisan reverb:start
```

**Not:** Production ortamında Reverb'ü arka planda çalıştırmak için supervisor kullanabilirsiniz.

### 5. Laravel Uygulamasını Başlatın

```bash
php artisan serve
```

## Kullanım

### Dashboard

1. Giriş yaptıktan sonra `/dashboard` sayfasına yönlendirileceksiniz
2. Sol panelde tüm sohbetlerinizi görebilirsiniz
3. Her sohbet kartında:
   - Sohbet adı/kişi adı
   - Son mesaj
   - Mesaj zamanı
   - Okunmamış mesaj sayısı (yeşil rozet)

### Yeni Sohbet Başlatma

#### Kişi ile Sohbet
1. Sağ üst köşedeki "+" butonuna tıklayın
2. "Kişi ile Sohbet" sekmesini seçin
3. Kullanıcı listesinden bir kişi seçin
4. Otomatik olarak özel sohbet başlar

#### Grup Oluşturma
1. Sağ üst köşedeki "+" butonuna tıklayın
2. "Grup Oluştur" sekmesini seçin
3. Grup adını girin (zorunlu)
4. Grup açıklaması ekleyin (opsiyonel)
5. Grup resmi yükleyin (opsiyonel)
6. En az 1 üye seçin
7. "Grup Oluştur" butonuna tıklayın

### Mesajlaşma

1. Sol panelden bir sohbet seçin
2. Alt kısımdaki input alanından mesajınızı yazın
3. Enter tuşuna basın veya gönder butonuna tıklayın
4. Mesajlarınızın yanında durumunu görebilirsiniz:
   - Tek tık (⟳): Bekliyor
   - Çift gri tık (✓✓): İletildi
   - Çift mavi tık (✓✓): Görüldü

### Grup Yönetimi

#### Grup Bilgilerini Görüntüleme
1. Sohbet headerındaki bilgi (ℹ) butonuna tıklayın
2. Grup resmi, üyeler ve diğer bilgileri görüntüleyin

#### Grup Düzenleme (Sadece Yöneticiler)
1. Grup bilgileri modalında "Düzenle" butonuna tıklayın
2. Grup adını, açıklamasını değiştirin
3. Grup resmini güncelleyin
4. "Kaydet" butonuna tıklayın

#### Üye Yönetimi (Sadece Yöneticiler)
1. Grup bilgileri modalında üyeleri görüntüleyin
2. "Üye Ekle" butonu ile yeni üye ekleyin
3. Üye rollerini değiştirin (Yönetici ⟷ Üye)
4. Üyeleri gruptan çıkarın

### Mesaj Durumları

#### Bireysel Sohbetlerde:
- **Bekliyor (⟳)**: Karşı taraf offline
- **İletildi (✓✓)**: Karşı tarafa ulaştı ama okunmadı
- **Görüldü (✓✓ mavi)**: Karşı taraf mesajı gördü

#### Grup Sohbetlerinde:
- **Bekliyor**: Henüz kimseye ulaşmadı
- **İletildi (✓✓)**: En az bir kişiye ulaştı, tüm üyelere iletildi
- **Görüldü (✓✓ mavi)**: Tüm üyeler tarafından görüldü

## Önemli Notlar

### Avatar Sistemi
- Profil fotoğrafları ui-avatars.com servisi kullanılarak otomatik oluşturulur
- Kullanıcı adına göre renkli avatarlar üretilir
- Gruplar için de aynı sistem kullanılır (özel resim yüklenmediyse)

### Grup Resimleri
- Grup resimlerini yüklediğinizde `storage/app/public/conversation-images/` klasörüne kaydedilir
- Maksimum dosya boyutu: 2MB
- Desteklenen formatlar: jpg, jpeg, png, gif, webp

### Yetki Sistemi
- **Yönetici (role: 1)**: Grup düzenleme, üye ekleme/çıkarma, rol değiştirme
- **Üye (role: 2)**: Sadece mesajlaşma

### Real-time Güncellemeler
- Laravel Reverb sayesinde tüm mesajlar gerçek zamanlı olarak iletilir
- Mesaj durumları otomatik güncellenir
- "Yazıyor..." göstergesi çalışır
- Yeni mesaj geldiğinde konuşma listesi otomatik güncellenir

## API Endpoints

### Konuşmalar
- `GET /conversations` - Tüm konuşmaları listele
- `POST /conversations` - Yeni konuşma oluştur
- `GET /conversations/{id}` - Konuşma detayları
- `PUT /conversations/{id}` - Konuşma güncelle
- `DELETE /conversations/{id}` - Konuşma sil

### Mesajlar
- `GET /conversations/{id}/messages` - Konuşmanın mesajlarını getir
- `POST /conversations/{id}/messages` - Yeni mesaj gönder
- `PUT /messages/{id}/status` - Mesaj durumunu güncelle
- `POST /conversations/{id}/mark-all-read` - Tüm mesajları okundu işaretle

### Grup Üyeleri
- `POST /conversations/{id}/members` - Üye ekle
- `DELETE /conversations/{id}/members/{userId}` - Üye çıkar
- `PUT /conversations/{id}/members/{userId}/role` - Üye rolünü değiştir

## Teknolojiler

- **Backend**: Laravel 11
- **Frontend**: Vue.js 3 + Tailwind CSS
- **Real-time**: Laravel Reverb (WebSockets)
- **Broadcasting**: Pusher Protocol
- **Avatar**: ui-avatars.com API

## Sorun Giderme

### Mesajlar gerçek zamanlı gelmiyor
1. Reverb sunucusunun çalıştığından emin olun: `php artisan reverb:start`
2. `.env` dosyasında Reverb ayarlarını kontrol edin
3. Browser console'da WebSocket hatalarını kontrol edin

### Grup resimleri görünmüyor
1. Storage linkinin oluşturulduğundan emin olun: `php artisan storage:link`
2. `storage/app/public` klasörüne yazma yetkisi olduğundan emin olun

### NPM build hataları
1. Node.js ve NPM versiyonlarını kontrol edin (Node 18+ önerilir)
2. `node_modules` klasörünü silin ve `npm install` çalıştırın
3. Cache'i temizleyin: `npm cache clean --force`

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

