<template>
  <div class="trip-details-page">
    <!-- Back Button -->
    <button @click="$router.back()" class="mb-4 flex items-center text-gray-600 hover:text-gray-900">
      <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
      Back
    </button>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
    </div>

    <!-- Trip Details -->
    <div v-else-if="trip" class="space-y-6">
      <!-- Main Card -->
      <div class="bg-white rounded-lg shadow-lg p-8">
        <div class="flex items-start justify-between mb-6">
          <div>
            <h1 class="text-4xl font-bold text-blue-600 mb-2">{{ trip.flight_number }}</h1>
            <div class="flex items-center space-x-3 text-xl text-gray-700">
              <span class="font-semibold">{{ trip.departure_airport }}</span>
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
              <span class="font-semibold">{{ trip.arrival_airport }}</span>
            </div>
          </div>
          <span :class="['px-4 py-2 rounded-full text-sm font-medium', getStatusClass(trip.status)]">
            {{ trip.status }}
          </span>
        </div>

        <!-- Flight Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div class="space-y-4">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <div>
                <p class="text-sm text-gray-500">Date</p>
                <p class="font-medium text-gray-900">{{ formatDate(trip.departure_date) }}</p>
              </div>
            </div>

            <div class="flex items-center">
              <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div>
                <p class="text-sm text-gray-500">Departure Time</p>
                <p class="font-medium text-gray-900">{{ trip.departure_time }}</p>
              </div>
            </div>

            <div class="flex items-center">
              <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div>
                <p class="text-sm text-gray-500">Arrival Time</p>
                <p class="font-medium text-gray-900">{{ trip.arrival_time }}</p>
              </div>
            </div>
          </div>

          <div class="space-y-4">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
              <div>
                <p class="text-sm text-gray-500">Duration</p>
                <p class="font-medium text-gray-900">{{ trip.duration || 'N/A' }}</p>
              </div>
            </div>

            <div class="flex items-center">
              <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
              </svg>
              <div>
                <p class="text-sm text-gray-500">Aircraft Type</p>
                <p class="font-medium text-gray-900">{{ trip.aircraft_type || 'N/A' }}</p>
              </div>
            </div>

            <div class="flex items-center">
              <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
              </svg>
              <div>
                <p class="text-sm text-gray-500">Trip Type</p>
                <p class="font-medium text-gray-900">{{ trip.trip_type || 'N/A' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="trip.notes" class="bg-gray-50 rounded-lg p-4 mb-6">
          <p class="text-sm font-medium text-gray-700 mb-2">Notes</p>
          <p class="text-gray-600">{{ trip.notes }}</p>
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap gap-3">
          <button 
            v-if="!trip.is_published"
            @click="publishForSwap"
            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition"
          >
            Publish for Swap
          </button>
          <button 
            v-if="trip.is_published"
            @click="unpublishTrip"
            class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition"
          >
            Unpublish Trip
          </button>
        </div>
      </div>

      <!-- Crew Information (if available) -->
      <div v-if="trip.crew" class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Crew Information</h2>
        <div class="space-y-2">
          <p class="text-gray-600"><span class="font-medium">Position:</span> {{ trip.crew.position }}</p>
          <p class="text-gray-600"><span class="font-medium">Crew Size:</span> {{ trip.crew.size }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { apiService } from '@/services/api';

const route = useRoute();
const router = useRouter();

const trip = ref(null);
const loading = ref(true);

const getStatusClass = (status) => {
  const classes = {
    upcoming: 'bg-blue-100 text-blue-700',
    completed: 'bg-green-100 text-green-700',
    published: 'bg-purple-100 text-purple-700',
    cancelled: 'bg-red-100 text-red-700',
  };
  return classes[status?.toLowerCase()] || 'bg-gray-100 text-gray-700';
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    weekday: 'long',
    month: 'long',
    day: 'numeric',
    year: 'numeric',
  });
};

const fetchTripDetails = async () => {
  loading.value = true;
  try {
    const response = await apiService.trips.getTripDetails(route.params.id);
    trip.value = response.data.trip || response.data;
  } catch (error) {
    console.error('Failed to fetch trip details:', error);
    router.push('/my-trips');
  } finally {
    loading.value = false;
  }
};

const publishForSwap = async () => {
  try {
    await apiService.trips.publishTrip({ trip_id: trip.value.id });
    await fetchTripDetails();
  } catch (error) {
    console.error('Failed to publish trip:', error);
  }
};

const unpublishTrip = async () => {
  try {
    await apiService.trips.unpublishTrip(trip.value.id);
    await fetchTripDetails();
  } catch (error) {
    console.error('Failed to unpublish trip:', error);
  }
};

onMounted(() => {
  fetchTripDetails();
});
</script>
