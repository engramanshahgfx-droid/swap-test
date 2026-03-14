<template>
  <div :class="['chat-bubble', alignmentClass, 'mb-4']">
    <div :class="['max-w-xs md:max-w-md', bubbleClass, 'rounded-lg p-3 shadow-sm']">
      <!-- Message Text -->
      <p class="text-sm break-words whitespace-pre-wrap">{{ message.message }}</p>
      
      <!-- Timestamp -->
      <p :class="['text-xs mt-1', timeClass]">
        {{ formatTime(message.created_at) }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  message: {
    type: Object,
    required: true,
  },
  isOwn: {
    type: Boolean,
    default: false,
  },
});

const alignmentClass = computed(() => {
  return props.isOwn ? 'flex justify-end' : 'flex justify-start';
});

const bubbleClass = computed(() => {
  return props.isOwn
    ? 'bg-blue-600 text-white'
    : 'bg-gray-200 text-gray-900';
});

const timeClass = computed(() => {
  return props.isOwn ? 'text-blue-100' : 'text-gray-600';
});

const formatTime = (timestamp) => {
  if (!timestamp) return '';
  const date = new Date(timestamp);
  const now = new Date();
  const diffMs = now - date;
  const diffMins = Math.floor(diffMs / 60000);
  
  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  
  const diffHours = Math.floor(diffMins / 60);
  if (diffHours < 24) return `${diffHours}h ago`;
  
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>
