<script setup>
import { ref, reactive } from 'vue'
import Avatar from 'primevue/avatar'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputSwitch from 'primevue/inputswitch'
import Badge from 'primevue/badge'
import AppLayout from "@/Layouts/AppLayout.vue";
import Menu from 'primevue/menu';

// Reactive data
const selectedChat = ref(null)
const newMessage = ref('')
const activeTab = ref('Media')
const menu = ref();

const items = ref([
    {
        label: 'Options',
        items: [
            {
                label: 'Group Info',
                icon: 'pi pi-info-circle'
            },
            {
                label: 'Leave Group',
                icon: 'pi pi-sign-out'
            }
        ]
    }
]);

const toggle = (event) => {
    menu.value.toggle(event);
};

const settings = reactive({
    notifications: true,
    sound: false,
    saveDownloads: false
})

// Mock data
const chatList = ref([
    {
        id: 1,
        name: 'PrimeTek Team',
        avatar: '/placeholder.svg?height=48&width=48',
        lastMessage: "Let's implement PrimeVue...",
        time: '11:15',
        unreadCount: 0,
        online: true,
        members: 'Cody Fisher, Esther Howard, Jerome Bell, Kristin Watson...'
    },
    {
        id: 2,
        name: 'Jerome Bell',
        avatar: '/placeholder.svg?height=48&width=48',
        lastMessage: "PrimeVue's...",
        time: '11:15',
        unreadCount: 1,
        online: true,
        members: 'Jerome Bell'
    },
    {
        id: 3,
        name: 'Robert Fox',
        avatar: '/placeholder.svg?height=48&width=48',
        lastMessage: 'Interesting! PrimeVue sounds...',
        time: '11:15',
        unreadCount: 0,
        online: false,
        members: 'Robert Fox'
    },
    {
        id: 4,
        name: 'Esther Howard',
        avatar: '/placeholder.svg?height=48&width=48',
        lastMessage: 'Quick one, team! Anyone...',
        time: '11:15',
        unreadCount: 1,
        online: true,
        members: 'Esther Howard'
    }
])

const messages = ref([
    {
        id: 1,
        sender: 'OS',
        avatar: '/placeholder.svg?height=32&width=32',
        content: "Awesome! What's the standout feature?",
        time: '11:15'
    },
    {
        id: 2,
        sender: 'Jerome Bell',
        avatar: '/placeholder.svg?height=32&width=32',
        content: 'PrimeVue rocks! Simplifies UI dev with versatile components.',
        time: '11:16'
    },
    {
        id: 3,
        sender: 'Robert Fox',
        avatar: '/placeholder.svg?height=32&width=32',
        content: 'Intriguing! Tell us more about its impact.',
        time: '11:17'
    },
    {
        id: 4,
        sender: 'Esther Howard',
        avatar: '/placeholder.svg?height=32&width=32',
        content: "It's design-neutral and compatible with Tailwind. Features accessible, high-grade components!",
        time: '11:18',
        image: '/placeholder.svg?height=300&width=500'
    },
    {
        id: 4,
        sender: 'Esther Howard',
        avatar: '/placeholder.svg?height=32&width=32',
        content: "It's design-neutral and compatible with Tailwind. Features accessible, high-grade components!",
        time: '11:18',
        image: '/placeholder.svg?height=300&width=500'
    },
    {
        id: 4,
        sender: 'Esther Howard',
        avatar: '/placeholder.svg?height=32&width=32',
        content: "It's design-neutral and compatible with Tailwind. Features accessible, high-grade components!",
        time: '11:18',
        image: '/placeholder.svg?height=300&width=500'
    }
])

const members = ref([
    { id: 1, name: 'Robin Jonas', avatar: '/placeholder.svg?height=32&width=32' },
    { id: 2, name: 'Cameron Williamson', avatar: '/placeholder.svg?height=32&width=32' },
    { id: 3, name: 'Eleanor Pena', avatar: '/placeholder.svg?height=32&width=32' },
    { id: 4, name: 'Arlene McCoy', avatar: '/placeholder.svg?height=32&width=32' },
    { id: 5, name: 'Dianne Russell', avatar: '/placeholder.svg?height=32&width=32' }
])

// Methods
const selectChat = (chat) => {
    selectedChat.value = chat
}

const sendMessage = () => {
    if (!newMessage.value.trim()) return

    messages.value.push({
        id: messages.value.length + 1,
        sender: 'You',
        avatar: '/placeholder.svg?height=32&width=32',
        content: newMessage.value,
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    })

    newMessage.value = ''
}

// Select first chat by default
selectedChat.value = chatList.value[0]
</script>

<template>
    <AppLayout title="Whatsapp">
        <div class="flex h-screen bg-gray-50 border">
        <!-- Left Sidebar - Chat List -->
        <div class="w-80 bg-white border-r border-gray-200 flex flex-col">
            <!-- Header -->
            <div class="p-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Chats</h2>
                <Button class="p-button-text p-button-sm" icon="pi pi-plus" />
            </div>

            <div class="p-4 border-b border-gray-200">
                <InputText class="w-full" placeholder="Search" type="text" />
            </div>

            <!-- Chat List -->
            <div class="flex-1 overflow-y-auto">
                <div
                    v-for="chat in chatList"
                    :key="chat.id"
                    :class="[
            'p-4 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors',
            { 'bg-blue-50 border-l-4 border-l-green-500': selectedChat?.id === chat.id }
          ]"
                    @click="selectChat(chat)"
                >
                    <div class="flex items-start space-x-3">
                        <div class="relative">
                            <Avatar
                                :image="chat.avatar"
                                :label="chat.name.charAt(0)"
                                class="w-12 h-12 border"
                                shape="circle"
                            />
                            <span
                                v-if="chat.online"
                                class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"
                            ></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ chat.name }}</p>
                                <span class="text-xs text-gray-500">{{ chat.time }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-gray-600 truncate mt-1">{{ chat.lastMessage }}</p>
                                <Badge
                                    v-if="chat.unreadCount"
                                    :value="chat.unreadCount"
                                    size="small"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="flex-1 flex flex-col">
            <!-- Chat Header -->
            <div class="bg-white border-b border-gray-200 p-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <Avatar
                        :image="selectedChat?.avatar"
                        :label="selectedChat?.name?.charAt(0)"
                        class="w-10 h-10 border"
                        shape="circle"
                    />
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ selectedChat?.name || 'Select a chat' }}</h3>
                        <p class="text-sm text-gray-500">{{ selectedChat?.members || '' }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <Button class="p-button-text" icon="pi pi-phone" />
                    <Button class="p-button-text" icon="pi pi-search" />
                    <Button aria-controls="overlay_menu" aria-haspopup="true" class="p-button-text" icon="pi pi-ellipsis-h" @click="toggle" />
                    <Menu id="overlay_menu" ref="menu" :model="items" :popup="true" />
                </div>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
                <div v-for="message in messages" :key="message.id" class="flex items-start space-x-3">
                    <Avatar
                        :image="message.avatar"
                        :label="message.sender.charAt(0)"
                        class="w-8 h-8 border"
                        shape="circle"
                    />
                    <div class="flex items-start gap-2.5">
                        <div class="flex flex-col w-full max-w-[320px] leading-1.5 p-4 bg-white rounded-lg shadow-sm border border-gray-200border-gray-200 rounded-e-xl rounded-es-xl dark:bg-gray-700">
                            <div class="flex items-center space-x-2 rtl:space-x-reverse">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ message.sender }}</span>
                                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">{{ message.time }}</span>
                            </div>
                            <p class="text-sm font-normal py-2.5 text-gray-900 dark:text-white">{{ message.content }}</p>
                            <div class="group relative my-2.5">
                                <div class="absolute w-full h-full bg-gray-900/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-lg flex items-center justify-center">
                                    <button class="inline-flex items-center justify-center rounded-full h-10 w-10 bg-white/30 hover:bg-white/50 focus:ring-4 focus:outline-none dark:text-white focus:ring-gray-50" data-tooltip-target="download-image">
                                        <svg aria-hidden="true" class="w-5 h-5 text-white" fill="none" viewBox="0 0 16 18" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8 1v11m0 0 4-4m-4 4L4 8m11 4v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                        </svg>
                                    </button>
                                    <div id="download-image" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-xs opacity-0 tooltip dark:bg-gray-700" role="tooltip">
                                        Download image
                                        <div class="tooltip-arrow" data-popper-arrow></div>
                                    </div>
                                </div>
                                <img v-if="message.image" :src="message.image" alt="Shared image" class="rounded-lg" />
                            </div>
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">Delivered</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Message Input -->
            <div class="bg-white border-t border-gray-200 p-4">
                <div class="flex items-center space-x-3">
                    <Button class="p-button-text" icon="pi pi-face-smile" />
                    <Button class="p-button-text" icon="pi pi-paperclip" />
                    <InputText
                        v-model="newMessage"
                        class="flex-1"
                        placeholder="Write your message..."
                        @keyup.enter="sendMessage"
                    />
                    <Button
                        :disabled="!newMessage.trim()"
                        icon="pi pi-send"
                        @click="sendMessage"
                    />
                </div>
            </div>
        </div>

        <!-- Right Sidebar - Members & Settings -->
        <div class="w-80 bg-white border-l border-gray-200 flex flex-col">
            <!-- Settings -->
            <div class="p-4 border-b border-gray-200">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Notification</span>
                        <InputSwitch v-model="settings.notifications" />
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Sound</span>
                        <InputSwitch v-model="settings.sound" />
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Save to downloads</span>
                        <InputSwitch v-model="settings.saveDownloads" />
                    </div>
                </div>
            </div>

            <!-- Members -->
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-medium text-gray-900">Members</h4>
                    <span class="text-sm text-blue-600 cursor-pointer">See All</span>
                </div>
                <div class="space-y-3">
                    <div
                        v-for="member in members"
                        :key="member.id"
                        class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 cursor-pointer"
                    >
                        <div class="flex items-center space-x-3">
                            <Avatar
                                :image="member.avatar"
                                :label="member.name.charAt(0)"
                                class="w-8 h-8"
                                shape="circle"
                            />
                            <span class="text-sm font-medium text-gray-900">{{ member.name }}</span>
                        </div>
                        <Button class="p-button-text p-button-sm" icon="pi pi-chevron-right" />
                    </div>
                </div>
            </div>

            <!-- Media & Docs -->
            <div class="p-4">
                <div class="flex space-x-4 mb-4">
                    <button
                        v-for="tab in ['Media', 'Link', 'Docs']"
                        :key="tab"
                        :class="[
              'px-3 py-1 text-sm font-medium rounded-lg transition-colors',
              activeTab === tab
                ? 'bg-blue-100 text-blue-700'
                : 'text-gray-600 hover:text-gray-900'
            ]"
                        @click="activeTab = tab"
                    >
                        {{ tab }}
                    </button>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div
                        v-for="i in 6"
                        :key="i"
                        class="aspect-square bg-gray-200 rounded-lg cursor-pointer hover:opacity-80 transition-opacity"
                    >
                        <img
                            alt="Media thumbnail"
                            class="w-full h-full object-cover rounded-lg"
                            src="/placeholder.svg?height=80&width=80"
                        />
                    </div>
                </div>
                <button class="w-full mt-4 text-sm text-blue-600 hover:text-blue-700 font-medium">
                    Show more →
                </button>
            </div>
        </div>
    </div>
    </AppLayout>
</template>

<style scoped>
/* Custom scrollbar styles */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
