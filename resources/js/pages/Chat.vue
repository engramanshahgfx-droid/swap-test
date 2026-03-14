<template>
  <div class="chat-page flex flex-col h-[calc(100vh-8rem)]">
    <!-- Chat Header -->
    <div class="bg-white border-b px-6 py-4 flex items-center">
      <button @click="$router.back()" class="mr-4 lg:hidden">
        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-semibold">
        {{ getUserInitials(otherUser) }}
      </div>
      <div class="ml-3">
        <p class="font-semibold text-gray-900">{{ otherUser?.full_name || 'User' }}</p>
        <p class="text-sm text-gray-500">{{ otherUser?.position || 'Crew Member'}}</p>
      </div>
    </div>

    <!-- Messages Container -->
    <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50">
      <div v-for="message in messages" :key="message.id" :class="['flex', message.is_mine ? 'justify-end' : 'justify-start']">
        <div :class="['max-w-xs lg:max-w-md px-4 py-2 rounded-lg', message.is_mine ? 'bg-blue-600 text-white' : 'bg-white text-gray-900']">
          <p class="text-sm">{{ message.message }}</p>
          <p :class="['text-xs mt-1', message.is_mine ? 'text-blue-100' : 'text-gray-500']">
            {{ formatMessageTime(message.created_at) }}
          </p>
        </div>
      </div>
      <div v-if="loading" class="text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
      </div>
    </div>

    <!-- Message Input -->
    <div class="bg-white border-t px-6 py-4">
      <form @submit.prevent="sendMessage" class="flex space-x-2">
        <input
          v-model="newMessage"
          type="text"
          placeholder="Type a message..."
          :disabled="sending"
          class="flex-1 border-gray-300 rounded-full px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        />
        <button
          type="submit"
          :disabled="!newMessage.trim() || sending"
          class="px-6 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition"
        >
          <svg v-if="!sending" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
          </svg>
          <div v-else class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import { apiService } from '@/services/api';

const route = useRoute();
const conversationId = route.params.conversationId;

const messages = ref([]);
const otherUser = ref(null);
const newMessage = ref('');
const loading = ref(true);
const sending = ref(false);
const messagesContainer = ref(null);

const getUserInitials = (user) => {
  if (!user?.full_name) return 'U';
  const parts = user.full_name.split(' ');
  return parts.length >= 2 
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : user.full_name.substring(0, 2).toUpperCase();
};

const formatMessageTime = (timestamp) => {
  const date = new Date(timestamp);
  return date.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  });
};

const scrollToBottom = async () => {
  await nextTick();
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

const fetchMessages = async () => {
  loading.value = true;
  try {
    const response = await apiService.chat.getMessages(conversationId);
    messages.value = response.data.messages || response.data;
    otherUser.value = response.data.user || {};
    await scrollToBottom();
    
    // Mark as read
    await apiService.chat.markAsRead(conversationId);
  } catch (error) {
    console.error('Failed to fetch messages:', error);
  } finally {
    loading.value = false;
  }
};

const sendMessage = async () => {
  if (!newMessage.value.trim()) return;

  const messageText = newMessage.value;
  newMessage.value = '';
  sending.value = true;

  try {
    await apiService.chat.sendMessage({
      conversation_id: conversationId,
      message: messageText,
    });

    await fetchMessages();
  } catch (error) {
    console.error('Failed to send message:', error);
    newMessage.value = messageText; // Restore message on error
  } finally {
    sending.value = false;
  }
};

onMounted(() => {
  fetchMessages();
  
  // Poll for new messages every 5 seconds (can be replaced with WebSockets)
  const interval = setInterval(fetchMessages, 5000);
  
  // Cleanup
  return () => clearInterval(interval);
});
</script>
