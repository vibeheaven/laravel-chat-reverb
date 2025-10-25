<template>
    <div class="flex h-screen bg-gray-100">
        <!-- Sol Panel - Konuşma Listesi -->
        <div class="w-full md:w-1/3 bg-white border-r border-gray-300 flex flex-col">
            <!-- Header -->
            <div class="bg-gray-100 p-4 border-b border-gray-300">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <img
                            :src="currentUser.avatar"
                            :alt="currentUser.name"
                            class="w-10 h-10 rounded-full"
                        />
                        <h2 class="text-xl font-semibold text-gray-800">
                            {{ currentUser.name }}
                        </h2>
                    </div>
                    <button
                        @click="showCreateGroupModal = true"
                        class="p-2 rounded-full hover:bg-gray-200 transition"
                        title="Yeni Grup Oluştur"
                    >
                        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </button>
                </div>
                <!-- Arama -->
                <div class="mt-3">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Sohbet ara..."
                        class="w-full px-4 py-2 rounded-lg bg-white border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
            </div>

            <!-- Konuşma Listesi -->
            <div class="flex-1 overflow-y-auto">
                <div
                    v-for="conversation in filteredConversations"
                    :key="conversation.id"
                    @click="selectConversation(conversation)"
                    class="flex items-center p-4 border-b border-gray-200 hover:bg-gray-50 cursor-pointer transition"
                    :class="{ 'bg-blue-50': selectedConversation?.id === conversation.id }"
                >
                    <!-- Avatar -->
                    <div class="relative">
                        <img
                            :src="conversation.image"
                            :alt="conversation.name"
                            class="w-12 h-12 rounded-full object-cover"
                        />
                        <span
                            v-if="conversation.unread_count > 0"
                            class="absolute -top-1 -right-1 bg-green-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-semibold"
                        >
                            {{ conversation.unread_count > 9 ? '9+' : conversation.unread_count }}
                        </span>
                    </div>

                    <!-- Konuşma Bilgisi -->
                    <div class="flex-1 ml-3 min-w-0">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900 truncate">
                                {{ conversation.name }}
                            </h3>
                            <span
                                v-if="conversation.last_message"
                                class="text-xs text-gray-500"
                            >
                                {{ conversation.last_message.time }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between mt-1">
                            <p class="text-sm text-gray-600 truncate">
                                <span v-if="conversation.last_message">
                                    <span v-if="conversation.type === 'group'" class="font-medium">
                                        {{ conversation.last_message.sender_name }}:
                                    </span>
                                    {{ conversation.last_message.text }}
                                </span>
                                <span v-else class="text-gray-400 italic">
                                    Henüz mesaj yok
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Boş Durum -->
                <div v-if="conversations.length === 0" class="flex flex-col items-center justify-center h-64 text-gray-500">
                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <p class="text-lg">Henüz sohbetiniz yok</p>
                    <button
                        @click="showCreateGroupModal = true"
                        class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition"
                    >
                        Yeni Grup Oluştur
                    </button>
                </div>
            </div>
        </div>

        <!-- Sağ Panel - Mesajlaşma -->
        <div class="flex-1 flex flex-col">
            <ChatWindow
                v-if="selectedConversation"
                :conversation="selectedConversation"
                :current-user="currentUser"
                @conversation-updated="loadConversations"
                @show-info="showConversationInfo"
            />
            <div v-else class="flex flex-col items-center justify-center h-full bg-gray-50">
                <svg class="w-32 h-32 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <p class="text-xl text-gray-500">Bir sohbet seçin</p>
            </div>
        </div>

        <!-- Grup Oluştur Modal -->
        <CreateGroupModal
            v-if="showCreateGroupModal"
            @close="showCreateGroupModal = false"
            @created="onGroupCreated"
        />

        <!-- Konuşma Bilgi Modal -->
        <ConversationInfoModal
            v-if="showInfoModal && selectedConversation"
            :conversation="selectedConversation"
            :current-user="currentUser"
            @close="showInfoModal = false"
            @updated="onConversationUpdated"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import ChatWindow from './ChatWindow.vue';
import CreateGroupModal from './CreateGroupModal.vue';
import ConversationInfoModal from './ConversationInfoModal.vue';

const props = defineProps({
    currentUser: {
        type: Object,
        required: true,
    },
});

const conversations = ref([]);
const selectedConversation = ref(null);
const searchQuery = ref('');
const showCreateGroupModal = ref(false);
const showInfoModal = ref(false);

// Filtrelenmiş konuşmalar
const filteredConversations = computed(() => {
    if (!searchQuery.value) return conversations.value;
    
    const query = searchQuery.value.toLowerCase();
    return conversations.value.filter(conv =>
        conv.name.toLowerCase().includes(query)
    );
});

// Konuşmaları yükle
const loadConversations = async () => {
    try {
        const response = await axios.get('/conversations');
        conversations.value = response.data;

        // Seçili konuşma varsa, güncelle
        if (selectedConversation.value) {
            const updated = conversations.value.find(
                c => c.id === selectedConversation.value.id
            );
            if (updated) {
                selectedConversation.value = updated;
            }
        }
    } catch (error) {
        console.error('Konuşmalar yüklenemedi:', error);
    }
};

// Konuşma seç
const selectConversation = (conversation) => {
    selectedConversation.value = conversation;
};

// Grup oluşturuldu
const onGroupCreated = (newConversation) => {
    showCreateGroupModal.value = false;
    loadConversations();
    // Yeni oluşturulan konuşmayı seç
    setTimeout(() => {
        const conv = conversations.value.find(c => c.id === newConversation.conversation.id);
        if (conv) {
            selectConversation(conv);
        }
    }, 100);
};

// Konuşma bilgilerini göster
const showConversationInfo = () => {
    showInfoModal.value = true;
};

// Konuşma güncellendi
const onConversationUpdated = () => {
    loadConversations();
    showInfoModal.value = false;
};

// Real-time güncellemeler için Echo listener'ları
const setupEchoListeners = () => {
    // Tüm konuşmaları dinle
    conversations.value.forEach(conversation => {
        Echo.private(`conversation.${conversation.id}`)
            .listen('.message.sent', (event) => {
                // Yeni mesaj geldi, konuşma listesini güncelle
                loadConversations();
            });
    });
};

onMounted(() => {
    loadConversations();
    
    // Her 30 saniyede bir konuşmaları güncelle
    setInterval(loadConversations, 30000);
});
</script>

