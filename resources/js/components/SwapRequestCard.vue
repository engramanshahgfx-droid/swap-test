<template>
  <div class="swap-request-card bg-white rounded-lg shadow-md hover:shadow-lg transition p-5">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center space-x-3">
        <div class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">
          {{ userInitials }}
        </div>
        <div>
          <p class="font-bold text-gray-900">{{ userName }}</p>
          <p class="text-sm text-gray-600">{{ userAirline }}</p>
        </div>
      </div>
      <span :class="statusBadgeClass" class="px-3 py-1 text-xs font-medium rounded-full">
        {{ statusText }}
      </span>
    </div>

    <!-- Trip Details -->
    <div class="bg-gray-50 rounded-lg p-4 mb-4">
      <div class="flex items-center justify-between mb-2">
        <div class="flex items-center space-x-2">
          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
          </svg>
          <span class="font-bold text-gray-900">{{ swap.trip?.flight_number }}</span>
        </div>
        <span class="text-sm text-gray-600">{{ formatDate(swap.trip?.departure_date) }}</span>
      </div>

      <div class="flex items-center space-x-2 text-sm text-gray-700">
        <span class="font-medium">{{ swap.trip?.from_airport?.iata_code }}</span>
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
        </svg>
        <span class="font-medium">{{ swap.trip?.to_airport?.iata_code }}</span>
        <span class="text-gray-500 ml-auto">{{ swap.trip?.departure_time }}</span>
      </div>
    </div>

    <!-- Message -->
    <div v-if="swap.message" class="mb-4">
      <p class="text-sm text-gray-600 italic">"{{ swap.message }}"</p>
    </div>

    <!-- Requested Date -->
    <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
      <span>Requested {{ formatTimeAgo(swap.created_at) }}</span>
      <span v-if="swap.responded_at">Responded {{ formatTimeAgo(swap.responded_at) }}</span>
    </div>

    <!-- Actions Slot -->
    <div v-if="$slots.actions" class="flex space-x-3 pt-3 border-t border-gray-200">
      <slot name="actions"></slot>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  swap: {
    type: Object,
    required: true,
  },
  type: {
    type: String,
    default: 'received', // 'received' or 'sent'
  },
});

const userName = computed(() => {
  if (props.type === 'received') {
    return props.swap.requester?.full_name || props.swap.requester?.name || 'Unknown User';
  }
  return props.swap.trip?.user?.full_name || props.swap.trip?.user?.name || 'Unknown User';
});

const userAirline = computed(() => {
  if (props.type === 'received') {
    return props.swap.requester?.airline?.name || 'N/A';
  }
  return props.swap.trip?.user?.airline?.name || 'N/A';
});

const userInitials = computed(() => {
  const name = userName.value;
  const parts = name.split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return name.substring(0, 2).toUpperCase();
});

const statusText = computed(() => {
  const status = props.swap.status;
  if (status === 'pending') return 'Pending';
  if (status === 'approved') return 'Approved';
  if (status === 'rejected') return 'Rejected';
  if (status === 'completed') return 'Completed';
  return status || 'Unknown';
});

const statusBadgeClass = computed(() => {
  const status = props.swap.status;
  if (status === 'pending') return 'bg-yellow-100 text-yellow-700';
  if (status === 'approved') return 'bg-green-100 text-green-700';
  if (status === 'rejected') return 'bg-red-100 text-red-700';
  if (status === 'completed') return 'bg-purple-100 text-purple-700';
  return 'bg-gray-100 text-gray-700';
});

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};

const formatTimeAgo = (timestamp) => {
  if (!timestamp) return '';
  const date = new Date(timestamp);
  const now = new Date();
  const diffMs = now - date;
  const diffMins = Math.floor(diffMs / 60000);
  
  if (diffMins < 1) return 'just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  
  const diffHours = Math.floor(diffMins / 60);
  if (diffHours < 24) return `${diffHours}h ago`;
  
  const diffDays = Math.floor(diffHours / 24);
  if (diffDays < 7) return `${diffDays}d ago`;
  
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};
</script>
