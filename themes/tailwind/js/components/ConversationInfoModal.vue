<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
            <!-- Header -->
            <div class="bg-gray-100 p-4 flex items-center justify-between border-b">
                <h2 class="text-xl font-semibold text-gray-800">
                    {{ conversationData.type === 'group' ? 'Grup Bilgileri' : 'Kişi Bilgileri' }}
                </h2>
                <button
                    @click="$emit('close')"
                    class="p-1 hover:bg-gray-200 rounded-full transition"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 overflow-y-auto max-h-[75vh]">
                <div v-if="loading" class="flex justify-center items-center h-64">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
                </div>

                <div v-else>
                    <!-- Grup Bilgileri -->
                    <div v-if="conversationData.type === 'group'">
                        <!-- Grup Resmi ve İsmi -->
                        <div class="text-center mb-6">
                            <div class="relative inline-block">
                                <img
                                    :src="conversationData.image"
                                    :alt="conversationData.name"
                                    class="w-32 h-32 rounded-full object-cover mx-auto border-4 border-gray-200"
                                />
                                <button
                                    v-if="conversationData.is_admin && editMode"
                                    @click="$refs.imageInput.click()"
                                    class="absolute bottom-0 right-0 bg-blue-500 text-white p-2 rounded-full hover:bg-blue-600 transition"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </button>
                                <input
                                    ref="imageInput"
                                    type="file"
                                    accept="image/*"
                                    @change="handleImageChange"
                                    class="hidden"
                                />
                            </div>

                            <!-- Grup Adı -->
                            <div v-if="editMode && conversationData.is_admin" class="mt-4">
                                <input
                                    v-model="editForm.name"
                                    type="text"
                                    class="text-2xl font-semibold text-center w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
                            <h3 v-else class="text-2xl font-semibold mt-4">
                                {{ conversationData.name }}
                            </h3>

                            <p class="text-gray-600 mt-1">
                                {{ conversationData.members.length }} üye
                            </p>
                        </div>

                        <!-- Grup Açıklaması -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Grup Açıklaması
                            </label>
                            <div v-if="editMode && conversationData.is_admin">
                                <textarea
                                    v-model="editForm.description"
                                    rows="3"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                ></textarea>
                            </div>
                            <p v-else class="text-gray-700">
                                {{ conversationData.description || 'Açıklama yok' }}
                            </p>
                        </div>

                        <!-- Grup Oluşturulma Tarihi -->
                        <div class="mb-6">
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Oluşturulma:</span> {{ conversationData.created_at }}
                            </p>
                        </div>

                        <!-- Düzenle/Kaydet Butonları -->
                        <div v-if="conversationData.is_admin" class="mb-6 flex space-x-3">
                            <button
                                v-if="!editMode"
                                @click="editMode = true"
                                class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition"
                            >
                                Düzenle
                            </button>
                            <template v-else>
                                <button
                                    @click="cancelEdit"
                                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                                >
                                    İptal
                                </button>
                                <button
                                    @click="saveChanges"
                                    class="flex-1 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition"
                                >
                                    Kaydet
                                </button>
                            </template>
                        </div>

                        <!-- Üyeler -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-semibold text-gray-800">Üyeler</h4>
                                <button
                                    v-if="conversationData.is_admin"
                                    @click="showAddMember = true"
                                    class="text-blue-500 hover:text-blue-600 text-sm font-medium"
                                >
                                    + Üye Ekle
                                </button>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="member in conversationData.members"
                                    :key="member.id"
                                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                                >
                                    <div class="flex items-center space-x-3">
                                        <img
                                            :src="member.avatar"
                                            :alt="member.name"
                                            class="w-12 h-12 rounded-full"
                                        />
                                        <div>
                                            <p class="font-semibold text-gray-900">
                                                {{ member.name }}
                                                <span v-if="member.id === currentUser.id" class="text-sm text-gray-500">(Sen)</span>
                                            </p>
                                            <p class="text-sm text-gray-600">{{ member.email }}</p>
                                            <p
                                                v-if="member.is_admin"
                                                class="text-xs text-blue-600 font-medium"
                                            >
                                                Yönetici
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Yönetici İşlemleri -->
                                    <div v-if="conversationData.is_admin && member.id !== currentUser.id" class="flex items-center space-x-2">
                                        <button
                                            @click="toggleMemberRole(member)"
                                            class="px-3 py-1 text-sm border border-blue-500 text-blue-500 rounded hover:bg-blue-50 transition"
                                        >
                                            {{ member.is_admin ? 'Üye Yap' : 'Yönetici Yap' }}
                                        </button>
                                        <button
                                            @click="removeMember(member)"
                                            class="px-3 py-1 text-sm bg-red-500 text-white rounded hover:bg-red-600 transition"
                                        >
                                            Çıkar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kişi Bilgileri (Private Chat) -->
                    <div v-else class="text-center">
                        <img
                            :src="conversationData.other_user?.avatar"
                            :alt="conversationData.other_user?.name"
                            class="w-32 h-32 rounded-full object-cover mx-auto border-4 border-gray-200"
                        />
                        <h3 class="text-2xl font-semibold mt-4">
                            {{ conversationData.other_user?.name }}
                        </h3>
                        <p class="text-gray-600 mt-1">
                            {{ conversationData.other_user?.email }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Üye Ekleme Modal -->
        <div v-if="showAddMember" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-60">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold mb-4">Üye Ekle</h3>
                <input
                    v-model="searchNewMember"
                    type="text"
                    placeholder="Kullanıcı ara..."
                    class="w-full px-4 py-2 mb-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <div class="max-h-64 overflow-y-auto space-y-2 mb-4">
                    <div
                        v-for="user in availableUsers"
                        :key="user.id"
                        @click="addMember(user)"
                        class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition"
                    >
                        <img
                            :src="user.avatar"
                            :alt="user.name"
                            class="w-10 h-10 rounded-full"
                        />
                        <div class="ml-3">
                            <p class="font-semibold text-gray-900">{{ user.name }}</p>
                            <p class="text-sm text-gray-600">{{ user.email }}</p>
                        </div>
                    </div>
                </div>
                <button
                    @click="showAddMember = false"
                    class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                >
                    Kapat
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

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

const emit = defineEmits(['close', 'updated']);

const conversationData = ref({});
const loading = ref(true);
const editMode = ref(false);
const editForm = ref({
    name: '',
    description: '',
    image: null,
});
const showAddMember = ref(false);
const searchNewMember = ref('');
const allUsers = ref([]);

// Kullanılabilir kullanıcılar (henüz üye olmayanlar)
const availableUsers = computed(() => {
    if (!searchNewMember.value) {
        return allUsers.value.filter(user =>
            !conversationData.value.members?.some(member => member.id === user.id)
        );
    }
    
    const query = searchNewMember.value.toLowerCase();
    return allUsers.value.filter(user =>
        !conversationData.value.members?.some(member => member.id === user.id) &&
        (user.name.toLowerCase().includes(query) || user.email.toLowerCase().includes(query))
    );
});

// Konuşma detaylarını yükle
const loadConversationDetails = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/conversations/${props.conversation.id}`);
        conversationData.value = response.data;
        editForm.value.name = response.data.name;
        editForm.value.description = response.data.description;
    } catch (error) {
        console.error('Konuşma detayları yüklenemedi:', error);
    } finally {
        loading.value = false;
    }
};

// Kullanıcıları yükle
const loadUsers = async () => {
    try {
        const response = await axios.get('/users');
        allUsers.value = response.data;
    } catch (error) {
        console.error('Kullanıcılar yüklenemedi:', error);
    }
};

// Resim değişikliği
const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        editForm.value.image = file;
    }
};

// Düzenlemeyi iptal et
const cancelEdit = () => {
    editMode.value = false;
    editForm.value.name = conversationData.value.name;
    editForm.value.description = conversationData.value.description;
    editForm.value.image = null;
};

// Değişiklikleri kaydet
const saveChanges = async () => {
    try {
        const formData = new FormData();
        formData.append('name', editForm.value.name);
        formData.append('description', editForm.value.description || '');
        if (editForm.value.image) {
            formData.append('image', editForm.value.image);
        }
        formData.append('_method', 'PUT');

        await axios.post(`/conversations/${props.conversation.id}`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        editMode.value = false;
        await loadConversationDetails();
        emit('updated');
    } catch (error) {
        console.error('Değişiklikler kaydedilemedi:', error);
        alert('Değişiklikler kaydedilirken bir hata oluştu.');
    }
};

// Üye rolünü değiştir
const toggleMemberRole = async (member) => {
    try {
        const newRole = member.is_admin ? 2 : 1;
        await axios.put(
            `/conversations/${props.conversation.id}/members/${member.id}/role`,
            { role: newRole }
        );
        await loadConversationDetails();
        emit('updated');
    } catch (error) {
        console.error('Üye rolü güncellenemedi:', error);
        alert('Üye rolü güncellenirken bir hata oluştu.');
    }
};

// Üye çıkar
const removeMember = async (member) => {
    if (!confirm(`${member.name} kişisini gruptan çıkarmak istediğinize emin misiniz?`)) {
        return;
    }

    try {
        await axios.delete(`/conversations/${props.conversation.id}/members/${member.id}`);
        await loadConversationDetails();
        emit('updated');
    } catch (error) {
        console.error('Üye çıkarılamadı:', error);
        alert('Üye çıkarılırken bir hata oluştu.');
    }
};

// Üye ekle
const addMember = async (user) => {
    try {
        await axios.post(`/conversations/${props.conversation.id}/members`, {
            user_id: user.id,
        });
        showAddMember.value = false;
        searchNewMember.value = '';
        await loadConversationDetails();
        emit('updated');
    } catch (error) {
        console.error('Üye eklenemedi:', error);
        alert('Üye eklenirken bir hata oluştu.');
    }
};

onMounted(() => {
    loadConversationDetails();
    loadUsers();
});
</script>

