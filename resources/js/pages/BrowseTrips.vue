<template>
  <div class="browse-trips-page">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Browse Trips</h1>
      <p class="text-gray-600">Find trips to swap with other crew members</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <input v-model="filters.departure_airport" @input="search" placeholder="From (e.g., DXB)" class="border-gray-300 rounded-lg text-sm" />
        <input v-model="filters.arrival_airport" @input="search" placeholder="To (e.g., JFK)" class="border-gray-300 rounded-lg text-sm" />
        <input v-model="filters.date" @change="search" type="date" class="border-gray-300 rounded-lg text-sm" />
        <button @click="resetFilters" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 border rounded-lg">Clear</button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
    </div>

    <!-- Trips Grid -->
    <div v-else-if="trips.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <div v-for="trip in trips" :key="trip.id" class="bg-white rounded-lg shadow hover:shadow-lg transition p-6">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h3 class="text-2xl font-bold text-blue-600 mb-1">{{ trip.flight_number }}</h3>
            <div class="flex items-center space-x-2 text-gray-600">
              <span class="font-semibold">{{ trip.departure_airport }}</span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
              <span class="font-semibold">{{ trip.arrival_airport }}</span>
            </div>
          </div>
        </div>

        <!-- User Info -->
        <div class="flex items-center mb-4 pb-4 border-b">
          <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-semibold">
            {{ getUserInitials(trip.user) }}
          </div>
          <div class="ml-3">
            <p class="font-medium text-gray-900">{{ trip.user.full_name }}</p>
            <p class="text-sm text-gray-500">{{ trip.user.position }}</p>
          </div>
        </div>

        <div class="space-y-2 mb-4 text-sm">
          <div class="flex items-center text-gray-600">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>{{ formatDate(trip.departure_date) }}</span>
          </div>
          <div class="flex items-center text-gray-600">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ trip.departure_time }}</span>
          </div>
        </div>

        <div class="flex space-x-2">
          <button @click="requestSwap(trip.id)" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
            Request Swap
          </button>
          <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white rounded-lg shadow text-center py-12">
      <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
      <p class="text-gray-500 text-lg">No trips available</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { apiService } from '@/services/api';

const trips = ref([]);
const loading = ref(true);
const filters = ref({
  departure_airport: '',
  arrival_airport: '',
  date: '',
});

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
    year: 'numeric',
  });
};

const search = async () => {
  loading.value = true;
  try {
    const response = await apiService.trips.browseTrips(filters.value);
    trips.value = response.data.trips || response.data;
  } catch (error) {
    console.error('Failed to fetch trips:', error);
  } finally {
    loading.value = false;
  }
};

const resetFilters = () => {
  filters.value = {
    departure_airport: '',
    arrival_airport: '',
    date: '',
  };
  search();
};

const requestSwap = async (tripId) => {
  try {
    await apiService.swaps.requestSwap({ published_trip_id: tripId });
    alert('Swap request sent successfully!');
  } catch (error) {
    console.error('Failed to request swap:', error);
    alert('Failed to send swap request');
  }
};

onMounted(() => {
  search();
});
</script>
