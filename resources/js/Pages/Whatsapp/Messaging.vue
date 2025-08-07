<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Avatar from 'primevue/avatar'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputSwitch from 'primevue/inputswitch'
import Badge from 'primevue/badge'
import AppLayout from "@/Layouts/AppLayout.vue";
import Menu from 'primevue/menu';
import AddContact from './Partials/AddContact.vue';
import { router } from '@inertiajs/vue3';
import { useConfirm } from "primevue/useconfirm";
import { push } from 'notivue';
import EditContact from './Partials/EditContact.vue';

// Get props from Inertia
const page = usePage()
const props = computed(() => page.props)
const confirm = useConfirm();


// Reactive data
const selectedChat = ref(null)
const newMessage = ref('')
const activeTab = ref('Media')
const menu = ref();
const showAddContact = ref(false);
const loadingMessages = ref(false);
const expandedMessages = ref(new Set());
const contactMenu = ref();
const selectedContact = ref(null);
const showEditContact = ref(false);

const items = ref([
    {
        label: 'Options',
        items: [
            {
                label: 'Edit Contact',
                icon: 'pi pi-pencil',
                command: () => {
                    handleEditContact();
                }
            },
            {
                label: 'Delete Contact',
                icon: 'pi pi-trash',
                command: () => {
                    handleDeleteContact();
                }
            }
        ]
    }
]);

const toggle = (event, chat) => {
    selectedContact.value = chat;
    contactMenu.value.toggle(event);
};

const settings = reactive({
    notifications: true,
    sound: false,
    saveDownloads: false
})

// Use real contacts from backend instead of mock data
const chatList = ref(props.value.contacts || [])
const messages = ref([])
const recipientPhone = ref('')

// Members for the right sidebar (can also be made dynamic if needed)
const members = ref([
    { id: 1, name: 'Robin Jonas', avatar: '/placeholder.svg?height=32&width=32' },
    { id: 2, name: 'Cameron Williamson', avatar: '/placeholder.svg?height=32&width=32' },
    { id: 3, name: 'Eleanor Pena', avatar: '/placeholder.svg?height=32&width=32' },
    { id: 4, name: 'Arlene McCoy', avatar: '/placeholder.svg?height=32&width=32' },
    { id: 5, name: 'Dianne Russell', avatar: '/placeholder.svg?height=32&width=32' }
])

// Methods
const selectChat = async (chat) => {
    selectedChat.value = chat
    recipientPhone.value = chat.phone || ''
    expandedMessages.value.clear()

    // Fetch messages for the selected chat
    await fetchMessages(chat.phone)
}

const fetchMessages = async (phone) => {
    if (!phone) return;

    loadingMessages.value = true;

    try {
        const response = await fetch(`/whatsapp/messages/${encodeURIComponent(phone)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': usePage().props.csrf,
            }
        });

        const result = await response.json();

        if (result.success) {
            messages.value = result.data.messages || [];
            console.log('Messages loaded successfully:', result.data.messages.length);
        } else {
            console.error('Failed to fetch messages:', result.message);
            messages.value = [];
        }
    } catch (error) {
        console.error('Error fetching messages:', error);
        messages.value = [];
    } finally {
        loadingMessages.value = false;
    }
}

const sendMessage = async () => {
    if (!newMessage.value.trim()) return

    // Check if we have a recipient phone number
    if (!recipientPhone.value) {
        alert('Please select a contact or enter a phone number.')
        return
    }

    // Add message to UI immediately for better UX
    const message = {
        id: messages.value.length + 1,
        sender: 'You',
        avatar: '/placeholder.svg?height=32&width=32',
        content: newMessage.value,
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        status: 'sending',
        outgoing: true,
        timestamp: new Date().toISOString(),
        messageId: null
    }

    messages.value.push(message)
    const messageContent = newMessage.value
    newMessage.value = ''

    try {
        const response = await fetch('/whatsapp/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                "X-CSRF-TOKEN": usePage().props.csrf,
            },
            body: JSON.stringify({
                recipient: recipientPhone.value,
                message: messageContent
            })
        })

        const result = await response.json()

        if (result.success) {
            // Update message with server data
            message.status = 'sent'
            message.messageId = result.data.message_id
            message.timestamp = result.data.timestamp

            console.log('Message sent successfully:', {
                messageId: result.data.message_id,
                recipient: result.data.recipient,
                storedMessage: result.data.stored_message
            })
        } else {
            throw new Error(result.message || 'Failed to send message')
        }

    } catch (error) {
        console.error('Failed to send message:', error)

        // Update message status on error
        message.status = 'failed'

        // Show error message to user
        alert(`Failed to send message: ${error.message}`)
    }
}

// Handle contact saved from AddContact component
const handleContactSaved = (contact) => {
    if (contact) {
        // Add the new contact to the chat list
        const newChat = {
            id: chatList.value.length + 1,
            name: contact.name,
            phone: contact.phone,
            avatar: '/placeholder.svg?height=48&width=48',
            lastMessage: 'No messages yet',
            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            unreadCount: 0,
            online: false,
            members: contact.name
        }

        chatList.value.unshift(newChat)

        // Optionally select the new contact
        selectChat(newChat)
    }
}

// Select first chat by default
onMounted(() => {
    if (chatList.value.length > 0) {
        selectChat(chatList.value[0])
    }
})

const toggleMessageExpansion = (messageId) => {
    if (expandedMessages.value.has(messageId)) {
        expandedMessages.value.delete(messageId)
    } else {
        expandedMessages.value.add(messageId)
    }
}

const isMessageExpanded = (messageId) => {
    return expandedMessages.value.has(messageId)
}

const getStatusIcon = (status) => {
    switch (status) {
        case 'sending':
            return 'pi pi-clock'
        case 'sent':
            return 'pi pi-check'
        case 'delivered':
            return 'pi pi-check-circle'
        case 'read':
            return 'pi pi-eye'
        case 'failed':
            return 'pi pi-times-circle'
        default:
            return 'pi pi-question-circle'
    }
}

const getStatusColor = (status) => {
    switch (status) {
        case 'sending':
            return 'text-yellow-500'
        case 'sent':
            return 'text-gray-500'
        case 'delivered':
            return 'text-blue-500'
        case 'read':
            return 'text-green-500'
        case 'failed':
            return 'text-red-500'
        default:
            return 'text-gray-400'
    }
}

const formatFullTimestamp = (timestamp) => {
    if (!timestamp) return 'Unknown'
    const date = new Date(timestamp)
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    })
}

const getInitials = (name) => {
    if (!name) return '?'
    return name.charAt(0).toUpperCase()
}

// Contact Edit and Delete Functions
const handleEditContact = () => {
    if (!selectedContact.value) return;

    // Show the edit dialog with the selected contact data
    showEditContact.value = true;
}

const handleContactUpdated = (updatedContact) => {
    // Find and update the contact in the chatList
    const index = chatList.value.findIndex(chat => chat.id === updatedContact.id);
    if (index !== -1) {
        chatList.value[index] = {
            ...chatList.value[index],
            name: updatedContact.name,
            phone: updatedContact.phone,
            avatar: updatedContact.profile_pic || chatList.value[index].avatar
        };
    }

    // If the updated contact is currently selected, update the selected chat too
    if (selectedChat.value && selectedChat.value.id === updatedContact.id) {
        selectedChat.value = {
            ...selectedChat.value,
            name: updatedContact.name,
            phone: updatedContact.phone,
            avatar: updatedContact.profile_pic || selectedChat.value.avatar
        };
    }

    push.success('Contact updated successfully');
}


const handleDeleteContact = () => {
    if (!selectedContact.value) return;

    confirm.require({
        message: `Are you sure you want to delete ${selectedContact.value.name}?`,
        header: 'Delete Confirmation',
        icon: 'pi pi-info-circle',
        rejectLabel: 'Cancel',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Delete',
            severity: 'danger'
        },
        accept: () => {
            deleteContact(selectedContact.value.id);
        },
        reject: () => {
            // Do nothing
        }
    });
}

const deleteContact = async (contactId) => {
    try {
        const response = await fetch(`/whatsapp/contacts/${contactId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': usePage().props.csrf,
            }
        });

        const result = await response.json();

        if (result.success) {
            // Remove contact from chatList
            const index = chatList.value.findIndex(chat => chat.id === contactId);
            if (index !== -1) {
                chatList.value.splice(index, 1);
            }

            // If deleted contact was selected, clear selection
            if (selectedChat.value && selectedChat.value.id === contactId) {
                selectedChat.value = null;
                messages.value = [];
                recipientPhone.value = '';
            }

            push.success('Contact deleted successfully');
        } else {
            throw new Error(result.message || 'Failed to delete contact');
        }
    } catch (error) {
        console.error('Failed to delete contact:', error);
        push.error(`Failed to delete contact: ${error.message}`);
    }
}
</script>

<template>
    <AppLayout title="Whatsapp">
        <div class="flex h-screen bg-gray-50 border">
            <!-- Left Sidebar - Chat List -->
            <div class="w-80 bg-white border-r border-gray-200 flex flex-col">
                <!-- Header -->
                <div class="p-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Chats</h2>
                    <Button class="p-button-text p-button-sm" icon="pi pi-plus" @click="showAddContact = true" />
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
                                    :label="getInitials(chat.name)"
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
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ chat.name || 'Unknown Contact' }}</p>
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
                            <Button
                                icon="pi pi-ellipsis-v"
                                class="p-button-text p-button-sm"
                                @click.stop="toggle($event, chat)"
                            />
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
                            :label="getInitials(selectedChat?.name)"
                            class="w-10 h-10 border"
                            shape="circle"
                        />
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ selectedChat?.name || 'Select a chat' }}</h3>
                            <p class="text-sm text-gray-500">{{ selectedChat?.members || '' }}</p>
                            <p v-if="selectedChat?.phone" class="text-xs text-gray-400">{{ selectedChat.phone }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <Button class="p-button-text" icon="pi pi-phone" />
                        <Button class="p-button-text" icon="pi pi-search" />
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="flex-1 overflow-y-auto p-4 bg-gray-50">
                    <div v-if="loadingMessages" class="flex justify-center items-center py-8">
                        <div class="text-gray-500">Loading messages...</div>
                    </div>
                    <div v-else-if="messages.length === 0" class="flex justify-center items-center py-8">
                        <div class="text-gray-500">No messages yet. Start a conversation!</div>
                    </div>
                    <div v-else class="space-y-6">
                        <div
                            v-for="message in messages"
                            :key="message.id"
                            :class="[
                                'flex',
                                message.outgoing ? 'justify-end' : 'justify-start'
                            ]"
                        >
                            <div
                                :class="[
                                    'max-w-xs lg:max-w-md px-4 py-3 rounded-lg cursor-pointer transition-all duration-300 shadow-sm',
                                    message.outgoing
                                        ? 'bg-green-500 text-white hover:bg-green-600'
                                        : 'bg-white text-gray-900 border border-gray-200 hover:bg-gray-50'
                                ]"
                                @click="toggleMessageExpansion(message.id)"
                            >
                                <div class="flex items-start space-x-2">
                                    <Avatar
                                        v-if="!message.outgoing"
                                        :image="message.avatar"
                                        :label="getInitials(message.sender)"
                                        class="w-6 h-6"
                                        shape="circle"
                                    />
                                    <div class="flex-1">
                                        <p class="text-sm leading-relaxed">{{ message.content }}</p>
                                        <div class="flex items-center justify-between mt-2">
                                            <span class="text-xs opacity-70">{{ message.time }}</span>
                                            <div class="flex items-center space-x-1">
                                                <span v-if="message.outgoing && message.status"
                                                      :class="['text-xs opacity-70', getStatusColor(message.status)]">
                                                    <i :class="getStatusIcon(message.status)"></i>
                                                </span>
                                                <i class="pi pi-chevron-down text-xs opacity-50 transition-transform duration-300"
                                                   :class="{ 'rotate-180': isMessageExpanded(message.id) }"></i>
                                            </div>
                                        </div>

                                        <!-- Expandable Meta Information -->
                                        <transition name="fade">
                                            <div
                                                v-if="isMessageExpanded(message.id)"
                                                class="mt-2 pt-2 border-t border-opacity-20 rounded-lg p-3 text-xs"
                                                :class="[
                                                    message.outgoing ? 'bg-green-100 border-green-200' : 'bg-gray-100 border-gray-300',
                                                    'text-gray-700'
                                                ]"
                                            >
                                                <div class="flex flex-col gap-1">
                                                    <div class="flex items-center gap-1">
                                                        <span class="font-semibold">Status:</span>
                                                        <i :class="[getStatusIcon(message.status), getStatusColor(message.status)]"></i>
                                                        <span class="capitalize">{{ message.status || 'Unknown' }}</span>
                                                    </div>

                                                    <div class="flex items-center gap-1">
                                                        <span class="font-semibold">Timestamp:</span>
                                                        <span>{{ formatFullTimestamp(message.timestamp) }}</span>
                                                    </div>

                                                    <div class="flex items-center gap-1">
                                                        <span class="font-semibold">Type:</span>
                                                        <span>{{ message.outgoing ? 'Outgoing' : 'Incoming' }}</span>
                                                    </div>

                                                    <div
                                                        v-if="!message.outgoing"
                                                        class="flex items-center gap-1"
                                                    >
                                                        <span class="font-semibold">From:</span>
                                                        <span>{{ message.sender || 'Unknown' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </transition>
                                    </div>
                                </div>
                                <img
                                    v-if="message.image"
                                    :src="message.image"
                                    alt="Shared image"
                                    class="mt-3 rounded-lg max-w-full h-auto shadow-sm"
                                />
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
                            :disabled="!newMessage.trim() || !recipientPhone"
                            icon="pi pi-send"
                            @click="sendMessage"
                        />
                    </div>
                    <div v-if="recipientPhone" class="text-xs text-gray-500 mt-1">
                        Sending to: {{ recipientPhone }}
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
                                    :label="getInitials(member.name)"
                                    class="w-8 h-8"
                                    shape="circle"
                                />
                                <span class="text-sm font-medium text-gray-900">{{ member.name || 'Unknown Member' }}</span>
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

        <!-- Contact Options Menu -->
        <Menu ref="contactMenu" :model="items" :popup="true" />

        <EditContact
            v-model:visible="showEditContact"
            :contact="selectedContact"
            @contact-updated="handleContactUpdated"
        />

        <AddContact v-model:visible="showAddContact" @contact-saved="handleContactSaved" />
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
.fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
