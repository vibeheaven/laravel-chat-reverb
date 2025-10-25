<template>
    <div class="relative">
        <!-- Mesaj Input -->
        <div class="flex items-center space-x-2 w-full">
            <div class="flex-1 relative">
                <input
                    ref="messageInput"
                    v-model="messageText"
                    @keydown="handleKeyDown"
                    @keyup.enter="handleSend"
                    @input="handleInput"
                    type="text"
                    placeholder="Bir mesaj yazın... (@ kullanıcı etiketle, /dosya dosya ara)"
                    class="w-full px-4 py-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 text-base"
                    :class="{ 'pr-10': showCommandIndicator }"
                />
                <!-- Komut Göstergesi -->
                <span
                    v-if="showCommandIndicator"
                    class="absolute right-3 top-2.5 text-blue-500 text-sm font-semibold"
                >
                    {{ commandType === 'mention' ? '@' : '/' }}
                </span>
            </div>
            <button
                @click="handleSend"
                :disabled="!messageText.trim()"
                class="p-3 bg-blue-500 text-white rounded-full hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition flex-shrink-0"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            </button>
        </div>

        <!-- Mention/Dosya Öner Listesi -->
        <div
            v-if="showSuggestions && suggestions.length > 0"
            class="absolute bottom-full left-0 right-0 mb-2 bg-white border border-gray-300 rounded-lg shadow-lg max-h-64 overflow-y-auto z-50"
        >
            <!-- Mention Önerileri -->
            <div
                v-if="commandType === 'mention'"
                v-for="(user, index) in suggestions"
                :key="user.id"
                @click="selectSuggestion(index)"
                class="flex items-center space-x-3 p-3 hover:bg-gray-50 cursor-pointer transition"
                :class="{ 'bg-blue-50': selectedIndex === index }"
            >
                <img
                    :src="user.avatar"
                    :alt="user.name"
                    class="w-8 h-8 rounded-full"
                />
                <div>
                    <p class="font-semibold text-gray-900">@{{ user.name }}</p>
                    <p class="text-xs text-gray-600">{{ user.email }}</p>
                </div>
            </div>

            <!-- Dosya Önerileri -->
            <div
                v-if="commandType === 'dosya'"
                v-for="(dosya, index) in suggestions"
                :key="dosya.id"
                @click="selectSuggestion(index)"
                class="p-3 hover:bg-gray-50 cursor-pointer transition border-b last:border-b-0"
                :class="{ 'bg-blue-50': selectedIndex === index }"
            >
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900">
                            📁 {{ dosya.dosyano }}
                        </p>
                        <p class="text-sm text-gray-700">{{ dosya.adisoyadi }}</p>
                        <p class="text-xs text-gray-500">TC: {{ dosya.tc }}</p>
                    </div>
                    <div class="text-right">
                        <p v-if="dosya.il" class="text-xs text-gray-600">{{ dosya.il }}</p>
                        <p v-if="dosya.durum" class="text-xs text-blue-600">{{ dosya.durum }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div
            v-if="loading"
            class="absolute bottom-full left-0 right-0 mb-2 bg-white border border-gray-300 rounded-lg shadow-lg p-4 text-center z-50"
        >
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500 mx-auto"></div>
            <p class="text-sm text-gray-600 mt-2">{{ commandType === 'mention' ? 'Kullanıcılar yükleniyor...' : 'Dosyalar aranıyor...' }}</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({
    conversationId: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(['send-message', 'send-dosya', 'typing']);

const messageText = ref('');
const suggestions = ref([]);
const showSuggestions = ref(false);
const selectedIndex = ref(0);
const commandType = ref(null); // 'mention' or 'dosya'
const loading = ref(false);
const messageInput = ref(null);
const mentionableUsers = ref([]);
const lastAtPosition = ref(-1);
const lastSlashPosition = ref(-1);

const showCommandIndicator = computed(() => {
    return commandType.value !== null;
});

// Input değişikliğini kontrol et
const handleInput = () => {
    const text = messageText.value;
    const cursorPosition = messageInput.value?.selectionStart || text.length;

    // @ mention kontrolü
    const atMatch = text.lastIndexOf('@', cursorPosition - 1);
    if (atMatch !== -1) {
        const textAfterAt = text.substring(atMatch + 1, cursorPosition);
        // Boşluk yoksa ve cursor @ işaretinden sonraysa
        if (!textAfterAt.includes(' ') && atMatch < cursorPosition) {
            commandType.value = 'mention';
            lastAtPosition.value = atMatch;
            loadMentionSuggestions(textAfterAt);
            return;
        }
    }

    // /dosya komut kontrolü
    if (text.startsWith('/dosya')) {
        const searchText = text.substring(6).trim(); // '/dosya' = 6 karakter
        commandType.value = 'dosya';
        lastSlashPosition.value = 0;
        if (searchText.length >= 1) {
            loadDosyaSuggestions(searchText);
        } else {
            suggestions.value = [];
            showSuggestions.value = commandType.value === 'dosya';
        }
        return;
    }

    // Hiçbir komut yoksa
    if (commandType.value) {
        commandType.value = null;
        showSuggestions.value = false;
        suggestions.value = [];
    }
    
    // Typing event gönder
    emit('typing');
};

// Klavye kontrolü
const handleKeyDown = (event) => {
    if (!showSuggestions.value || suggestions.value.length === 0) return;

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        selectedIndex.value = (selectedIndex.value + 1) % suggestions.value.length;
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        selectedIndex.value = selectedIndex.value === 0 ? suggestions.value.length - 1 : selectedIndex.value - 1;
    } else if (event.key === 'Enter' && commandType.value) {
        event.preventDefault();
        selectSuggestion(selectedIndex.value);
    } else if (event.key === 'Escape') {
        showSuggestions.value = false;
    }
};

// Mention önerilerini yükle
const loadMentionSuggestions = async (searchText) => {
    if (mentionableUsers.value.length === 0) {
        loading.value = true;
        try {
            const response = await axios.get(`/conversations/${props.conversationId}/mentionable-users`);
            mentionableUsers.value = response.data;
        } catch (error) {
            console.error('Kullanıcılar yüklenemedi:', error);
        } finally {
            loading.value = false;
        }
    }

    // Filtrele
    if (searchText) {
        suggestions.value = mentionableUsers.value.filter(user =>
            user.name.toLowerCase().includes(searchText.toLowerCase())
        );
    } else {
        suggestions.value = mentionableUsers.value;
    }

    showSuggestions.value = suggestions.value.length > 0;
    selectedIndex.value = 0;
};

// Dosya önerilerini yükle
const loadDosyaSuggestions = async (searchText) => {
    if (searchText.length < 1) return;

    loading.value = true;
    try {
        const response = await axios.get('/dosya/search', {
            params: { query: searchText }
        });
        suggestions.value = response.data;
        showSuggestions.value = suggestions.value.length > 0;
        selectedIndex.value = 0;
    } catch (error) {
        console.error('Dosya araması başarısız:', error);
        suggestions.value = [];
    } finally {
        loading.value = false;
    }
};

// Öneriyi seç
const selectSuggestion = (index) => {
    const selected = suggestions.value[index];

    if (commandType.value === 'mention') {
        // @ mention ekle
        const text = messageText.value;
        const beforeAt = text.substring(0, lastAtPosition.value);
        const afterCursor = text.substring(messageInput.value.selectionStart);
        messageText.value = `${beforeAt}@${selected.name} ${afterCursor}`;
        
        nextTick(() => {
            const newPosition = beforeAt.length + selected.name.length + 2;
            messageInput.value.setSelectionRange(newPosition, newPosition);
            messageInput.value.focus();
        });
    } else if (commandType.value === 'dosya') {
        // Dosya gönder
        emit('send-dosya', selected);
        messageText.value = '';
    }

    showSuggestions.value = false;
    commandType.value = null;
    suggestions.value = [];
};

// Mesaj gönder
const handleSend = () => {
    if (!messageText.value.trim()) return;

    emit('send-message', messageText.value);
    messageText.value = '';
    commandType.value = null;
    showSuggestions.value = false;
    suggestions.value = [];
};
</script>

