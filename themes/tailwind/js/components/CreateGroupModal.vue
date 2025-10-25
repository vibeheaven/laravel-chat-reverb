<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-500 text-white p-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold">Yeni Sohbet Başlat</h2>
                <button
                    @click="$emit('close')"
                    class="p-1 hover:bg-blue-600 rounded-full transition"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Tabs -->
            <div class="flex border-b">
                <button
                    @click="activeTab = 'private'"
                    class="flex-1 py-3 font-semibold transition"
                    :class="activeTab === 'private'
                        ? 'text-blue-500 border-b-2 border-blue-500'
                        : 'text-gray-600 hover:text-gray-800'"
                >
                    Kişi ile Sohbet
                </button>
                <button
                    @click="activeTab = 'group'"
                    class="flex-1 py-3 font-semibold transition"
                    :class="activeTab === 'group'
                        ? 'text-blue-500 border-b-2 border-blue-500'
                        : 'text-gray-600 hover:text-gray-800'"
                >
                    Grup Oluştur
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 overflow-y-auto max-h-[60vh]">
                <!-- Private Chat -->
                <div v-if="activeTab === 'private'">
                    <div class="mb-4">
                        <input
                            v-model="searchUser"
                            type="text"
                            placeholder="Kullanıcı ara..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div class="space-y-2">
                        <div
                            v-for="user in filteredUsers"
                            :key="user.id"
                            @click="startPrivateChat(user)"
                            class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition"
                        >
                            <img
                                :src="user.avatar"
                                :alt="user.name"
                                class="w-12 h-12 rounded-full"
                            />
                            <div class="ml-3">
                                <p class="font-semibold text-gray-900">{{ user.name }}</p>
                                <p class="text-sm text-gray-600">{{ user.email }}</p>
                            </div>
                        </div>

                        <div v-if="filteredUsers.length === 0" class="text-center py-8 text-gray-500">
                            Kullanıcı bulunamadı
                        </div>
                    </div>
                </div>

                <!-- Group Creation -->
                <div v-if="activeTab === 'group'">
                    <form @submit.prevent="createGroup" class="space-y-4">
                        <!-- Grup Adı -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Grup Adı *
                            </label>
                            <input
                                v-model="groupForm.name"
                                type="text"
                                required
                                placeholder="Grup adını girin"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <!-- Grup Açıklaması -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Grup Açıklaması
                            </label>
                            <textarea
                                v-model="groupForm.description"
                                rows="3"
                                placeholder="Grup açıklaması (opsiyonel)"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            ></textarea>
                        </div>

                        <!-- Grup Resmi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Grup Resmi
                            </label>
                            <div class="flex items-center space-x-4">
                                <div v-if="imagePreview" class="relative">
                                    <img
                                        :src="imagePreview"
                                        alt="Grup resmi önizleme"
                                        class="w-20 h-20 rounded-full object-cover"
                                    />
                                    <button
                                        type="button"
                                        @click="removeImage"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <label class="cursor-pointer">
                                    <div class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                        Resim Seç
                                    </div>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        @change="handleImageUpload"
                                        class="hidden"
                                    />
                                </label>
                            </div>
                        </div>

                        <!-- Üye Seçimi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Üyeler * (En az 1 üye seçilmeli)
                            </label>
                            <div class="mb-2">
                                <input
                                    v-model="searchUser"
                                    type="text"
                                    placeholder="Kullanıcı ara..."
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <!-- Seçili Üyeler -->
                            <div v-if="groupForm.members.length > 0" class="flex flex-wrap gap-2 mb-3">
                                <div
                                    v-for="member in selectedMembers"
                                    :key="member.id"
                                    class="flex items-center space-x-2 bg-blue-100 text-blue-800 px-3 py-1 rounded-full"
                                >
                                    <span class="text-sm">{{ member.name }}</span>
                                    <button
                                        type="button"
                                        @click="removeMember(member.id)"
                                        class="hover:text-blue-900"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Kullanıcı Listesi -->
                            <div class="border border-gray-300 rounded-lg max-h-48 overflow-y-auto">
                                <div
                                    v-for="user in filteredUsers"
                                    :key="user.id"
                                    @click="toggleMember(user)"
                                    class="flex items-center p-3 hover:bg-gray-50 cursor-pointer transition"
                                    :class="{ 'bg-blue-50': groupForm.members.includes(user.id) }"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="groupForm.members.includes(user.id)"
                                        class="mr-3"
                                        @click.stop
                                    />
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
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end space-x-3 pt-4">
                            <button
                                type="button"
                                @click="$emit('close')"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                            >
                                İptal
                            </button>
                            <button
                                type="submit"
                                :disabled="!groupForm.name || groupForm.members.length === 0 || loading"
                                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition"
                            >
                                {{ loading ? 'Oluşturuluyor...' : 'Grup Oluştur' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const emit = defineEmits(['close', 'created']);

const activeTab = ref('private');
const users = ref([]);
const searchUser = ref('');
const loading = ref(false);

const groupForm = ref({
    name: '',
    description: '',
    image: null,
    members: [],
});

const imagePreview = ref(null);

// Filtrelenmiş kullanıcılar
const filteredUsers = computed(() => {
    if (!searchUser.value) return users.value;
    
    const query = searchUser.value.toLowerCase();
    return users.value.filter(user =>
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query)
    );
});

// Seçili üyeler
const selectedMembers = computed(() => {
    return users.value.filter(user => groupForm.value.members.includes(user.id));
});

// Kullanıcıları yükle
const loadUsers = async () => {
    try {
        const response = await axios.get('/users');
        users.value = response.data;
    } catch (error) {
        console.error('Kullanıcılar yüklenemedi:', error);
    }
};

// Private sohbet başlat
const startPrivateChat = async (user) => {
    loading.value = true;
    try {
        const response = await axios.post('/conversations', {
            type: 'private',
            members: [user.id],
        });
        emit('created', response.data);
    } catch (error) {
        console.error('Sohbet oluşturulamadı:', error);
        alert('Sohbet oluşturulurken bir hata oluştu.');
    } finally {
        loading.value = false;
    }
};

// Grup oluştur
const createGroup = async () => {
    loading.value = true;
    try {
        const formData = new FormData();
        formData.append('type', 'group');
        formData.append('name', groupForm.value.name);
        if (groupForm.value.description) {
            formData.append('description', groupForm.value.description);
        }
        if (groupForm.value.image) {
            formData.append('image', groupForm.value.image);
        }
        groupForm.value.members.forEach(memberId => {
            formData.append('members[]', memberId);
        });

        const response = await axios.post('/conversations', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        emit('created', response.data);
    } catch (error) {
        console.error('Grup oluşturulamadı:', error);
        alert('Grup oluşturulurken bir hata oluştu.');
    } finally {
        loading.value = false;
    }
};

// Resim yükleme
const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        groupForm.value.image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

// Resmi kaldır
const removeImage = () => {
    groupForm.value.image = null;
    imagePreview.value = null;
};

// Üye ekle/çıkar
const toggleMember = (user) => {
    const index = groupForm.value.members.indexOf(user.id);
    if (index > -1) {
        groupForm.value.members.splice(index, 1);
    } else {
        groupForm.value.members.push(user.id);
    }
};

// Üye çıkar
const removeMember = (userId) => {
    const index = groupForm.value.members.indexOf(userId);
    if (index > -1) {
        groupForm.value.members.splice(index, 1);
    }
};

onMounted(() => {
    loadUsers();
});
</script>

