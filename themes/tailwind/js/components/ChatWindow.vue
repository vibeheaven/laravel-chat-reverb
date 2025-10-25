<template>
    <div class="flex flex-col h-full">
        <!-- Header -->
        <div class="bg-gray-100 p-4 border-b border-gray-300 flex items-center justify-between">
            <div class="flex items-center space-x-3 cursor-pointer" @click="$emit('show-info')">
                <img
                    :src="conversation.image"
                    :alt="conversation.name"
                    class="w-10 h-10 rounded-full object-cover"
                />
                <div>
                    <h3 class="font-semibold text-gray-900">{{ conversation.name }}</h3>
                    <p v-if="conversation.type === 'group'" class="text-xs text-gray-600">
                        {{ conversation.members_count }} üye
                    </p>
                    <p v-if="typing" class="text-xs text-green-600">yazıyor...</p>
                </div>
            </div>
            <button
                @click="$emit('show-info')"
                class="p-2 rounded-full hover:bg-gray-200 transition"
                title="Konuşma Bilgileri"
            >
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>
        </div>

        <!-- Mesajlar -->
        <div
            ref="messagesContainer"
            class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50"
            style="background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48cGF0dGVybiBpZD0iYSIgd2lkdGg9IjIwIiBoZWlnaHQ9IjIwIiBwYXR0ZXJuVW5pdHM9InVzZXJTcGFjZU9uVXNlIj48Y2lyY2xlIGN4PSIxMCIgY3k9IjEwIiByPSIxLjUiIGZpbGw9IiNkZGQiLz48L3BhdHRlcm4+PC9kZWZzPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjYSkiLz48L3N2Zz4='); min-height: 400px;"
        >
            <div
                v-for="(group, date) in groupedMessages"
                :key="date"
                class="space-y-4"
            >
                <!-- Tarih Ayırıcı -->
                <div class="flex justify-center">
                    <span class="bg-white px-3 py-1 rounded-full text-xs text-gray-600 shadow">
                        {{ date }}
                    </span>
                </div>

                <!-- Mesajlar -->
                <div
                    v-for="message in group"
                    :key="message.id"
                    class="flex"
                    :class="message.is_mine ? 'justify-end' : 'justify-start'"
                >
                    <!-- Avatar (solda, başkasının mesajı için) -->
                    <img
                        v-if="!message.is_mine && conversation.type === 'group'"
                        :src="message.sender_avatar"
                        :alt="message.sender_name"
                        :title="message.sender_name"
                        class="w-8 h-8 rounded-full mr-2 self-end"
                    />

                    <!-- Mesaj Balonu -->
                    <div
                        class="max-w-xs lg:max-w-md xl:max-w-lg rounded-lg px-4 py-2 shadow"
                        :class="message.is_mine
                            ? 'bg-green-500 text-white rounded-br-none'
                            : 'bg-white text-gray-900 rounded-bl-none'"
                    >
                        <!-- Gönderen Adı (grup mesajlarında, tüm mesajlar için) -->
                        <p
                            v-if="conversation.type === 'group' && !message.is_mine"
                            class="text-xs font-semibold mb-1 text-blue-600"
                        >
                            {{ message.sender_name }}
                        </p>

                        <!-- Mesaj Metni -->
                        <div v-if="message.type === 'command' && message.metadata?.command === 'dosya'">
                            <!-- Dosya Bilgisi Card -->
                            <div class="bg-white bg-opacity-20 rounded-lg p-3 border-l-4 border-blue-500">
                                <div class="space-y-1.5">
                                    <div class="flex items-center space-x-2 border-b pb-2 mb-2" :class="message.is_mine ? 'border-green-300' : 'border-gray-300'">
                                        <svg class="w-5 h-5" :class="message.is_mine ? 'text-green-100' : 'text-blue-600'" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                                        </svg>
                                        <span class="font-bold text-sm" :class="message.is_mine ? 'text-white' : 'text-gray-900'">DOSYA BİLGİSİ</span>
                                    </div>
                                    <div v-for="line in message.text.split('\n')" :key="line" class="text-sm">
                                        <span v-if="line && !line.includes('━') && !line.includes('─')" class="leading-relaxed" :class="message.is_mine ? 'text-green-50' : 'text-gray-800'">
                                            {{ line }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="message.type === 'mention'" class="break-words whitespace-pre-line">
                            <!-- Mention Mesajı - Özel Stil -->
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 rounded-lg p-3 shadow-sm">
                                <div class="flex items-center space-x-2 mb-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-blue-800">@ Etiketleme</span>
                                </div>
                                <div class="text-gray-800 leading-relaxed">
                                    <span v-for="(part, index) in parseMentionText(message.text)" :key="index">
                                        <span v-if="part.type === 'mention'" class="bg-blue-200 text-blue-800 px-2 py-1 rounded-full text-sm font-medium">
                                            @{{ part.text }}
                                        </span>
                                        <span v-else>{{ part.text }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <p v-else class="break-words whitespace-pre-line">{{ message.text }}</p>

                        <!-- Dosya Eki -->
                        <div v-if="message.attachment" class="mt-2">
                            <div
                                class="border rounded-lg p-3 bg-opacity-10"
                                :class="message.is_mine ? 'border-green-200 bg-green-100' : 'border-gray-300 bg-gray-50'"
                            >
                                <!-- Resim Önizlemesi -->
                                <div v-if="message.attachment.type === 'image'" class="mb-2">
                                    <img
                                        :src="message.attachment.url"
                                        :alt="message.attachment.name"
                                        class="max-w-full h-auto rounded cursor-pointer"
                                        @click="openImage(message.attachment.url)"
                                        style="max-height: 300px;"
                                    />
                                </div>

                                <!-- Dosya Bilgisi -->
                                <div class="flex items-center space-x-2">
                                    <!-- Dosya İkonu -->
                                    <div class="flex-shrink-0">
                                        <svg v-if="message.attachment.type === 'pdf'" class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 18h12V6h-4V2H4v16zm-2 1V0h12l4 4v16H2v-1z"/>
                                        </svg>
                                        <svg v-else-if="message.attachment.type === 'word'" class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 2h12a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2z"/>
                                        </svg>
                                        <svg v-else-if="message.attachment.type === 'excel'" class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 2h12a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2z"/>
                                        </svg>
                                        <svg v-else class="w-8 h-8 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                                        </svg>
                                    </div>

                                    <!-- Dosya Detayları -->
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium truncate" :class="message.is_mine ? 'text-white' : 'text-gray-900'">
                                            {{ message.attachment.name }}
                                        </p>
                                        <p class="text-xs" :class="message.is_mine ? 'text-green-100' : 'text-gray-500'">
                                            {{ formatBytes(message.attachment.size) }}
                                        </p>
                                    </div>

                                    <!-- İndirme Butonu -->
                                    <a
                                        :href="message.attachment.url"
                                        :download="message.attachment.name"
                                        target="_blank"
                                        class="flex-shrink-0 p-2 rounded-full transition"
                                        :class="message.is_mine ? 'hover:bg-green-600' : 'hover:bg-gray-200'"
                                    >
                                        <svg class="w-5 h-5" :class="message.is_mine ? 'text-white' : 'text-gray-700'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Zaman ve Durum -->
                        <div class="flex items-center justify-end space-x-1 mt-1">
                            <span
                                class="text-xs"
                                :class="message.is_mine ? 'text-green-100' : 'text-gray-500'"
                            >
                                {{ message.time }}
                            </span>
                            <!-- Mesaj Durumu (sadece kendi mesajlarımız için) -->
                            <span v-if="message.is_mine">
                                <!-- Bekliyor (tek tık) -->
                                <svg
                                    v-if="message.status === 'pending'"
                                    class="w-4 h-4 text-gray-300"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg>
                                <!-- İletildi (çift gri tık) -->
                                <svg
                                    v-else-if="message.status === 'delivered'"
                                    class="w-4 h-4 text-gray-300"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                    <path d="M18.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0" opacity="0.5" />
                                </svg>
                                <!-- Görüldü (çift mavi tık) -->
                                <svg
                                    v-else-if="message.status === 'read'"
                                    class="w-4 h-4 text-blue-400"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <g>
                                        <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                        <path d="M18.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0" transform="translate(2, 0)" />
                                    </g>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boş Durum -->
            <div v-if="messages.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400">
                <svg class="w-20 h-20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <p>Henüz mesaj yok. İlk mesajı siz gönderin!</p>
            </div>
        </div>

        <!-- Mesaj Gönderme -->
        <div class="bg-white p-4 border-t border-gray-300">
            <!-- Dosya Önizleme (eğer seçiliyse) -->
            <div v-if="selectedFile" class="mb-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 flex-1 min-w-0">
                        <div class="flex-shrink-0">
                            <img
                                v-if="filePreview && selectedFileType === 'image'"
                                :src="filePreview"
                                class="w-16 h-16 object-cover rounded"
                            />
                            <div v-else class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ selectedFile.name }}</p>
                            <p class="text-xs text-gray-500">{{ formatBytes(selectedFile.size) }}</p>
                        </div>
                    </div>
                    <button
                        @click="clearFile"
                        class="ml-2 p-2 text-red-500 hover:bg-red-50 rounded-full transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <!-- Caption Input -->
                <input
                    v-model="fileCaption"
                    type="text"
                    placeholder="Açıklama ekle (opsiyonel)"
                    class="mt-2 w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div class="flex items-center space-x-2">
                <!-- Dosya Yükleme Butonu -->
                <label class="cursor-pointer p-2 text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-full transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                    <input
                        ref="fileInput"
                        type="file"
                        @change="handleFileSelect"
                        accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                        class="hidden"
                    />
                </label>

                <!-- Mesaj Input veya Gönderme -->
                <template v-if="selectedFile">
                    <button
                        @click="uploadFile"
                        :disabled="uploading"
                        class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-full hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition flex items-center justify-center"
                    >
                        <svg v-if="uploading" class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ uploading ? 'Yükleniyor...' : 'Dosyayı Gönder' }}
                    </button>
                </template>
                <template v-else>
                    <MentionCommandInput
                        :conversation-id="conversation.id"
                        @send-message="sendMessage"
                        @send-dosya="sendDosya"
                        @typing="sendTypingEvent"
                    />
                </template>
            </div>
        </div>

        <!-- Resim Önizleme Modal -->
        <div
            v-if="showImagePreview"
            @click="showImagePreview = false"
            class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 p-4"
        >
            <img
                :src="previewImageUrl"
                class="max-w-full max-h-full object-contain"
                @click.stop
            />
            <button
                @click="showImagePreview = false"
                class="absolute top-4 right-4 text-white hover:text-gray-300 transition"
            >
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import MentionCommandInput from './MentionCommandInput.vue';

const props = defineProps({
    conversation: {
        type: Object,
        required: true,
    },
    currentUser: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['conversation-updated', 'show-info']);

const messages = ref([]);
const messagesContainer = ref(null);
const typing = ref(false);
const typingTimer = ref(null);

// Dosya yükleme için
const selectedFile = ref(null);
const filePreview = ref(null);
const selectedFileType = ref(null);
const fileCaption = ref('');
const uploading = ref(false);
const fileInput = ref(null);
const showImagePreview = ref(false);
const previewImageUrl = ref('');

// Mesajları tarihe göre grupla
const groupedMessages = computed(() => {
    const groups = {};
    messages.value.forEach(message => {
        const date = message.date;
        if (!groups[date]) {
            groups[date] = [];
        }
        groups[date].push(message);
    });
    return groups;
});

// Mesajları yükle
const loadMessages = async () => {
    try {
        const response = await axios.get(`/conversations/${props.conversation.id}/messages`);
        messages.value = response.data;
        scrollToBottom();

        // Mesajları okundu olarak işaretle
        await markAllAsRead();
    } catch (error) {
        console.error('Mesajlar yüklenemedi:', error);
    }
};

// Mention metnini parse et
const parseMentionText = (text) => {
    const parts = [];
    const mentionRegex = /@(\w+)/g;
    let lastIndex = 0;
    let match;

    while ((match = mentionRegex.exec(text)) !== null) {
        // Mention'dan önceki metin
        if (match.index > lastIndex) {
            parts.push({
                type: 'text',
                text: text.substring(lastIndex, match.index)
            });
        }
        
        // Mention
        parts.push({
            type: 'mention',
            text: match[1]
        });
        
        lastIndex = match.index + match[0].length;
    }
    
    // Son kısım
    if (lastIndex < text.length) {
        parts.push({
            type: 'text',
            text: text.substring(lastIndex)
        });
    }
    
    return parts;
};

// Mesaj gönder
const sendMessage = async (messageText) => {
    if (!messageText.trim()) return;

    try {
        const response = await axios.post(
            `/conversations/${props.conversation.id}/messages`,
            { text: messageText }
        );
        messages.value.push(response.data.data);
        scrollToBottom();
        emit('conversation-updated');
    } catch (error) {
        console.error('Mesaj gönderilemedi:', error);
    }
};

// Dosya gönder
const sendDosya = async (dosya) => {
    try {
        const response = await axios.post(
            `/conversations/${props.conversation.id}/dosya`,
            { dosya_id: dosya.id }
        );
        messages.value.push(response.data.data);
        scrollToBottom();
        emit('conversation-updated');
    } catch (error) {
        console.error('Dosya gönderilemedi:', error);
    }
};

// Yazıyor eventi gönder
const sendTypingEvent = () => {
    Echo.private(`conversation.${props.conversation.id}`)
        .whisper('typing', {
            userId: props.currentUser.id,
            userName: props.currentUser.name,
        });
};

// Dosya seçimi
const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // 10MB limit
    if (file.size > 10 * 1024 * 1024) {
        alert('Dosya boyutu 10MB\'dan küçük olmalıdır');
        return;
    }

    selectedFile.value = file;
    
    // Dosya tipini belirle
    if (file.type.startsWith('image/')) {
        selectedFileType.value = 'image';
        // Resim önizlemesi oluştur
        const reader = new FileReader();
        reader.onload = (e) => {
            filePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        selectedFileType.value = 'document';
        filePreview.value = null;
    }
};

// Dosya temizle
const clearFile = () => {
    selectedFile.value = null;
    filePreview.value = null;
    selectedFileType.value = null;
    fileCaption.value = '';
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

// Dosya yükle
const uploadFile = async () => {
    if (!selectedFile.value || uploading.value) return;

    uploading.value = true;

    try {
        const formData = new FormData();
        formData.append('file', selectedFile.value);
        if (fileCaption.value) {
            formData.append('caption', fileCaption.value);
        }

        const response = await axios.post(
            `/conversations/${props.conversation.id}/upload`,
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            }
        );

        messages.value.push(response.data.data);
        scrollToBottom();
        emit('conversation-updated');
        
        // Dosyayı temizle
        clearFile();
    } catch (error) {
        console.error('Dosya yüklenemedi:', error);
        alert('Dosya yüklenirken bir hata oluştu');
    } finally {
        uploading.value = false;
    }
};

// Resim önizleme
const openImage = (url) => {
    previewImageUrl.value = url;
    showImagePreview.value = true;
};

// Byte formatla
const formatBytes = (bytes, decimals = 2) => {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
};

// Tüm mesajları okundu olarak işaretle
const markAllAsRead = async () => {
    try {
        await axios.post(`/conversations/${props.conversation.id}/mark-all-read`);
        emit('conversation-updated');
    } catch (error) {
        console.error('Mesajlar okundu olarak işaretlenemedi:', error);
    }
};

// Scroll en alta
const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

// Echo listener'ları kur
let echoChannel = null;

const setupEchoListeners = () => {
    echoChannel = Echo.private(`conversation.${props.conversation.id}`)
        .listen('.message.sent', (event) => {
            // Kendi mesajımız değilse ve daha önce eklenmemişse ekle
            if (event.message.sender_id !== props.currentUser.id) {
                // Mesaj zaten listede var mı kontrol et
                const exists = messages.value.some(m => m.id === event.message.id);
                if (!exists) {
                    messages.value.push({
                        ...event.message,
                        is_mine: false,
                    });
                    scrollToBottom();
                    markAllAsRead();
                }
            }
            emit('conversation-updated');
        })
        .listen('.message.status.updated', (event) => {
            // Mesaj durumu güncelle
            const message = messages.value.find(m => m.id === event.message_id);
            if (message && message.is_mine) {
                message.status = event.overall_status;
            }
        })
        .listenForWhisper('typing', (event) => {
            if (event.userId !== props.currentUser.id) {
                typing.value = true;
                if (typingTimer.value) {
                    clearTimeout(typingTimer.value);
                }
                typingTimer.value = setTimeout(() => {
                    typing.value = false;
                }, 2000);
            }
        });
};

const cleanupEchoListeners = () => {
    if (echoChannel) {
        Echo.leave(`conversation.${props.conversation.id}`);
        echoChannel = null;
    }
};

// Konuşma değiştiğinde mesajları yeniden yükle
watch(() => props.conversation.id, () => {
    cleanupEchoListeners();
    loadMessages();
    setupEchoListeners();
}, { immediate: true });

onMounted(() => {
    setupEchoListeners();
});

onUnmounted(() => {
    cleanupEchoListeners();
});
</script>

