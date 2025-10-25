#!/bin/bash

echo "🚀 Laravel Reverb Chat Kurulum Başlatılıyor..."
echo ""

# Migration'ları çalıştır
echo "📦 Veritabanı migration'ları çalıştırılıyor..."
php artisan migrate

# Storage link oluştur
echo "🔗 Storage link oluşturuluyor..."
php artisan storage:link

# NPM paketlerini yükle
echo "📦 NPM paketleri yükleniyor..."
npm install

# Build
echo "🏗️  Frontend build ediliyor..."
npm run build

echo ""
echo "✅ Kurulum tamamlandı!"
echo ""
echo "📝 Uygulamayı başlatmak için:"
echo "   1. Terminal 1: php artisan reverb:start"
echo "   2. Terminal 2: php artisan serve"
echo ""
echo "🌐 Tarayıcınızda http://localhost:8000 adresine gidin"
echo ""

