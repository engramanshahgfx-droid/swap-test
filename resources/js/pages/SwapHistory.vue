<template>
  <div class="swap-history-page">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Swap History</h1>
      <p class="text-gray-600">Track your swap requests and responses</p>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow mb-6">
      <div class="border-b border-gray-200">
        <nav class="flex">
          <button @click="activeTab = 'sent'" :class="['px-6 py-4 text-sm font-medium border-b-2 transition', activeTab === 'sent' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700']">
            Sent Requests
          </button>
          <button @click="activeTab = 'received'" :class="['px-6 py-4 text-sm font-medium border-b-2 transition', activeTab === 'received' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700']">
            Received Requests
          </button>
        </nav>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
    </div>

    <!-- Swap Requests List -->
    <div v-else-if="swaps.length > 0" class="space-y-4">
      <div v-for="swap in swaps" :key="swap.id" class="bg-white rounded-lg shadow p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <div class="flex items-center space-x-2 mb-2">
              <span class="text-lg font-bold text-blue-600">{{ swap.flight_number }}</span>
              <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusClass(swap.status)]">
                {{ swap.status }}
              </span>
            </div>
            <div class="flex items-center text-gray-600 space-x-2 text-sm">
              <span>{{ swap.departure_airport }}</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
              <span>{{ swap.arrival_airport }}</span>
              <span>•</span>
              <span>{{ formatDate(swap.departure_date) }}</span>
            </div>
          </div>
        </div>

        <!-- User Info -->
        <div class="flex items-center mb-4">
          <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-semibold">
            {{ getUserInitials(activeTab === 'sent' ? swap.responder : swap.requester) }}
          </div>
          <div class="ml-3">
            <p class="font-medium text-gray-900">
              {{ activeTab === 'sent' ? swap.responder?.full_name : swap.requester?.full_name }}
            </p>
            <p class="text-sm text-gray-500">
              {{ activeTab === 'sent' ? 'Owner' : 'Requester' }}
            </p>
          </div>
        </div>

        <!-- Actions (for received tab) -->
        <div v-if="activeTab === 'received' && swap.status === 'pending'" class="flex space-x-2">
          <button @click="confirmSwap(swap.id)" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
            Approve
          </button>
          <button @click="rejectSwap(swap.id)" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium">
            Reject
          </button>
        </div>

        <!-- Status Info -->
        <div v-else class="text-sm text-gray-500">
          <span v-if="swap.status === 'approved'">✓ Approved on {{ formatDate(swap.updated_at) }}</span>
          <span v-else-if="swap.status === 'rejected'">✗ Rejected on {{ formatDate(swap.updated_at) }}</span>
          <span v-else-if="swap.status === 'manager_approved'">✓ Manager approved</span>
          <span v-else>⏳ Pending response</span>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white rounded-lg shadow text-center py-12">
      <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
      </svg>
      <p class="text-gray-500 text-lg">No swap requests</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { apiService } from '@/services/api';

const activeTab = ref('sent');
const swaps = ref([]);
const loading = ref(true);

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-700',
    approved: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
    manager_approved: 'bg-purple-100 text-purple-700',
  };
  return classes[status?.toLowerCase()] || 'bg-gray-100 text-gray-700';
};

const getUserInitials = (user) => {
  if (!user?.full_name) return 'U';
  const parts = user.full_name.split(' ');
  return parts.length >= 2 
    ? (parts[0][0] + parts[1][0]).toUpperCase()
    : user.full_name.substring(0, 2).toUpperCase();
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
  });
};

const fetchSwaps = async () => {
  loading.value = true;
  try {
    const response = await apiService.trips.getSwapHistory({ type: activeTab.value });
    swaps.value = response.data.swaps || response.data;
  } catch (error) {
    console.error('Failed to fetch swaps:', error);
  } finally {
    loading.value = false;
  }
};

const confirmSwap = async (swapId) => {
  try {
    await apiService.swaps.confirmSwap(swapId);
    await fetchSwaps();
  } catch (error) {
    console.error('Failed to confirm swap:', error);
  }
};

const rejectSwap = async (swapId) => {
  try {
    await apiService.swaps.rejectSwap(swapId);
    await fetchSwaps();
  } catch (error) {
    console.error('Failed to reject swap:', error);
  }
};

watch(activeTab, () => {
  fetchSwaps();
});

onMounted(() => {
  fetchSwaps();
});
</script>
