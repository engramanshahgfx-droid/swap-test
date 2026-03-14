<template>
  <div class="messages-page">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Messages</h1>
      <p class="text-gray-600">Chat with other crew members</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
    </div>

    <!-- Conversations List -->
    <div v-else-if="conversations.length > 0" class="bg-white rounded-lg shadow divide-y">
      <router-link
        v-for="conversation in conversations"
        :key="conversation.id"
        :to="`/chat/${conversation.id}`"
        class="flex items-center p-4 hover:bg-gray-50 transition"
      >
        <div class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center font-semibold text-lg flex-shrink-0">
          {{ getUserInitials(conversation.user) }}
        </div>
        <div class="ml-4 flex-1 min-w-0">
          <div class="flex items-center justify-between mb-1">
            <p class="font-semibold text-gray-900 truncate">
              {{ conversation.user.full_name }}
            </p>
            <span class="text-xs text-gray-500 ml-2 flex-shrink-0">
              {{ formatTime(conversation.last_message_at) }}
            </span>
          </div>
          <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600 truncate">
              {{ conversation.last_message }}
            </p>
            <span v-if="conversation.unread_count > 0" class="ml-2 bg-red-500 text-white text-xs font-medium px-2 py-1 rounded-full flex-shrink-0">
              {{ conversation.unread_count }}
            </span>
          </div>
        </div>
        <svg class="w-5 h-5 text-gray-400 ml-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </router-link>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white rounded-lg shadow text-center py-12">
      <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
      </svg>
      <p class="text-gray-500 text-lg mb-2">No messages yet</p>
      <p class="text-gray-400 text-sm">Start chatting with other crew members</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { apiService } from '@/services/api';

const conversations = ref([]);
const loading = ref(true);

const getUserInitials = (user) => {
  if (!user?.full_name) return 'U';
  const parts = user.full_name.split(' ');
  return parts.length >= 2 
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : user.full_name.substring(0, 2).toUpperCase();
};

const formatTime = (timestamp) => {
  const date = new Date(timestamp);
  const now = new Date();
  const diffMs = now - date;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;
  
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const fetchConversations = async () => {
  loading.value = true;
  try {
    const response = await apiService.chat.getConversations();
    conversations.value = response.data.conversations || response.data;
  } catch (error) {
    console.error('Failed to fetch conversations:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchConversations();
});
</script>
