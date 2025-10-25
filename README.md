# Laravel Real-Time Chat with Reverb and Vue 3 🚀

WhatsApp benzeri kapsamlı bir gerçek zamanlı sohbet uygulaması. Laravel Reverb, Vue 3 ve Tailwind CSS ile geliştirilmiştir.

## ✨ Özellikler

### 💬 Mesajlaşma
- ✅ Gerçek zamanlı mesajlaşma (Laravel Reverb)
- ✅ Özel (1:1) sohbetler
- ✅ Grup sohbetleri
- ✅ "Yazıyor..." göstergesi
- ✅ Mesaj zaman damgaları

### 📊 Mesaj Durumları (WhatsApp Benzeri)
- ✅ ⟳ **Bekliyor** - Tek tık (sistem kapalı)
- ✅ ✓✓ **İletildi** - Çift gri tık (sistem açık, okunmadı)
- ✅ ✓✓ **Görüldü** - Çift mavi tık (mesaj okundu)
- ✅ Grup mesajlarında toplu durum kontrolü

### 👥 Grup Yönetimi
- ✅ Grup oluşturma
- ✅ Grup resmi yükleme ve düzenleme
- ✅ Grup açıklaması ekleme
- ✅ Üye ekleme/çıkarma
- ✅ İki seviyeli yetki sistemi (Yönetici/Üye)
- ✅ Grup bilgilerini görüntüleme

### 🎨 Kullanıcı Arayüzü
- ✅ WhatsApp benzeri modern tasarım
- ✅ Responsive (mobil uyumlu)
- ✅ Otomatik avatar oluşturma (ui-avatars.com)
- ✅ Okunmamış mesaj sayacı
- ✅ Son mesaj ve zaman gösterimi
- ✅ Arama fonksiyonu

### 🔔 Bildirimler ve Modal'lar
- ✅ Grup/Kişi bilgi modal'ı
- ✅ Grup oluşturma modal'ı
- ✅ Üye yönetimi modal'ı

## 🚀 Hızlı Başlangıç

### Otomatik Kurulum
```bash
./setup-chat.sh
```

### Manuel Kurulum

1. **Bağımlılıkları yükleyin:**
```bash
composer install
npm install
```

2. **Environment dosyasını hazırlayın:**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Veritabanını yapılandırın:**

`.env` dosyasında veritabanı ayarlarınızı düzenleyin.

4. **Migration'ları çalıştırın:**
```bash
php artisan migrate
```

5. **Storage link oluşturun:**
```bash
php artisan storage:link
```

6. **Frontend'i build edin:**
```bash
npm run build
# veya geliştirme modu için:
npm run dev:tailwind
```

7. **Reverb sunucusunu başlatın:**
```bash
php artisan reverb:start
```

8. **Laravel sunucusunu başlatın (yeni terminal):**
```bash
php artisan serve
```

9. Tarayıcınızda `http://localhost:8000` adresine gidin ve kayıt olun!

## 📖 Detaylı Kullanım Kılavuzu

Detaylı kurulum ve kullanım talimatları için [KURULUM.md](KURULUM.md) dosyasına bakın.

## 🗄️ Veritabanı Yapısı

### Yeni Tablolar
- **conversations** - Sohbet bilgileri (grup/özel)
- **conversation_members** - Sohbet üyeleri ve rolleri
- **messages** - Mesajlar
- **message_statuses** - Mesaj durumları (pending/delivered/read)

## 🛠️ Teknolojiler

- **Backend:** Laravel 11
- **Frontend:** Vue.js 3
- **Styling:** Tailwind CSS
- **Real-time:** Laravel Reverb (WebSockets)
- **Broadcasting:** Pusher Protocol
- **Avatar:** ui-avatars.com API

## 📱 Ekran Görüntüleri

### Dashboard
WhatsApp benzeri sohbet listesi, okunmamış mesaj sayaçları, son mesajlar

### Mesajlaşma
Gerçek zamanlı mesajlaşma, mesaj durumları (görüldü/iletildi), "yazıyor..." göstergesi

### Grup Yönetimi
Grup oluşturma, resim yükleme, üye yönetimi, yetki sistemi

## 🎯 Kullanım Senaryoları

1. **Özel Mesajlaşma:** İki kullanıcı arasında 1:1 sohbet
2. **Grup Sohbetleri:** Birden fazla kullanıcıyla grup sohbeti
3. **Mesaj Takibi:** Mesajların ne zaman iletildiği ve okunduğunu görme
4. **Grup Yönetimi:** Yöneticiler grubu düzenleyebilir, üye ekleyip çıkarabilir

## 🔐 Güvenlik

- Tüm sohbetler authentication gerektirir
- Broadcasting kanalları authorization kullanır
- Grup yönetimi yetki kontrolü ile korunur
- CSRF koruması aktif

## 📝 API Endpoints

Tüm API endpoint'lerinin detaylı listesi için [KURULUM.md](KURULUM.md) dosyasına bakın.

## 🐛 Sorun Giderme

Yaygın sorunlar ve çözümleri için [KURULUM.md](KURULUM.md) dosyasının "Sorun Giderme" bölümüne bakın.


## Want to Support My Work?

If you found this demo helpful and want to support my work, check out some of my other products:

<div style="display:flex;">
  <a href="https://qirolab.com/ctrl-alt-cheat" title="Ctrl+Alt+Cheat - The Ultimate Cheat Sheets at Your Fingertips">
    <img width="200" src="https://i.imgur.com/6igLwXU.png" alt="Ctrl+Alt+Cheat" />
  </a>
  &nbsp;&nbsp;&nbsp;
  <a href="https://qirolab.com/spec-coder" title="Spec Coder: AI-Powered VS Code Extension">
     <img width="200" src="https://i.imgur.com/zHSNlJu.png" alt="Spec Coder" />
  </a>
  &nbsp;&nbsp;&nbsp;
  <a href="https://qirolab.gumroad.com/l/javascript-from-es2015-to-es2023" title="JavaScript: A Comprehensive Guide from ES2015 to ES2023 - eBook">
      <img width="200" src="https://i.imgur.com/vXnJAul.png" alt="JavaScript Guide" />
  </a>
</div>



---

#### Get $200 free credit for DigitalOcean! (Use this link to sign up)

[![DigitalOcean Referral
Badge](https://web-platforms.sfo2.cdn.digitaloceanspaces.com/WWW/Badge%201.svg)](https://www.digitalocean.com/?refcode=e740238537d0&utm_campaign=Referral_Invite&utm_medium=Referral_Program&utm_source=badge)

