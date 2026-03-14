<template>
  <div class="my-trips-page">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">My Trips</h1>
        <p class="text-gray-600">Manage your flight schedule</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.status" @change="fetchTrips" class="w-full border-gray-300 rounded-lg text-sm">
            <option value="">All Status</option>
            <option value="upcoming">Upcoming</option>
            <option value="completed">Completed</option>
            <option value="published">Published</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
          <select v-model="filters.month" @change="fetchTrips" class="w-full border-gray-300 rounded-lg text-sm">
            <option value="">All Months</option>
            <option v-for="month in 12" :key="month" :value="month">{{ getMonthName(month) }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input v-model="filters.search" @input="fetchTrips" type="text" placeholder="Flight number..." class="w-full border-gray-300 rounded-lg text-sm" />
        </div>
        <div class="flex items-end">
          <button @click="resetFilters" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Clear Filters</button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
    </div>

    <!-- Trips List -->
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
          <span :class="['px-3 py-1 rounded-full text-xs font-medium', getStatusClass(trip.status)]">
            {{ trip.status }}
          </span>
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
            <span>{{ trip.departure_time }} - {{ trip.arrival_time }}</span>
          </div>
          <div class="flex items-center text-gray-600">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <span>{{ trip.duration || 'N/A' }}</span>
          </div>
        </div>

        <div class="flex space-x-2">
          <router-link :to="`/trip/${trip.id}`" class="flex-1 px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 text-center text-sm font-medium transition">
            View Details
          </router-link>
          <button v-if="!trip.is_published" @click="publishTrip(trip.id)" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">
            Publish for Swap
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white rounded-lg shadow text-center py-12">
      <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
      </svg>
      <p class="text-gray-500 text-lg mb-2">No trips found</p>
      <p class="text-gray-400 text-sm">Your flight schedule will appear here</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { apiService } from '@/services/api';

const trips = ref([]);
const loading = ref(true);
const filters = ref({
  status: '',
  month: '',
  search: '',
});

const getStatusClass = (status) => {
  const classes = {
    upcoming: 'bg-blue-100 text-blue-700',
    completed: 'bg-green-100 text-green-700',
    published: 'bg-purple-100 text-purple-700',
    cancelled: 'bg-red-100 text-red-700',
  };
  return classes[status?.toLowerCase()] || 'bg-gray-100 text-gray-700';
};

const getMonthName = (month) => {
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  return months[month - 1];
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};

const fetchTrips = async () => {
  loading.value = true;
  try {
    const response = await apiService.trips.getMyTrips(filters.value);
    trips.value = response.data.trips || response.data;
  } catch (error) {
    console.error('Failed to fetch trips:', error);
  } finally {
    loading.value = false;
  }
};

const resetFilters = () => {
  filters.value = {
    status: '',
    month: '',
    search: '',
  };
  fetchTrips();
};

const publishTrip = async (tripId) => {
  try {
    await apiService.trips.publishTrip({ trip_id: tripId });
    await fetchTrips();
  } catch (error) {
    console.error('Failed to publish trip:', error);
  }
};

onMounted(() => {
  fetchTrips();
});
</script>
