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
            @click="showPublishDialog = true"
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

      <!-- Publish Dialog -->
      <div
        v-if="showPublishDialog"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        @click.self="showPublishDialog = false"
      >
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
          <h3 class="text-xl font-bold text-gray-900 mb-4">Publish Trip for Swap</h3>

          <!-- Image Upload -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Add Trip Image (Optional)</label>
            <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition">
              <input
                ref="fileInput"
                type="file"
                accept="image/*"
                @change="handleImageSelected"
                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
              />
              <div v-if="!selectedImage" class="text-gray-500">
                <svg class="mx-auto h-12 w-12 mb-2" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                  <path d="M28 8H12a4 4 0 00-4 4v20a4 4 0 004 4h24a4 4 0 004-4V20m-14-2l-3.172-3.172a4 4 0 00-5.656 0L2 28m26-20l4 4m6-6v20a4 4 0 01-4 4H12a4 4 0 01-4-4V12a4 4 0 014-4h16a4 4 0 014 4z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <p class="text-sm">Click to upload or drag and drop</p>
              </div>
              <div v-else class="text-green-600">
                <svg class="mx-auto h-12 w-12 mb-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm font-medium">{{ selectedImage.name }}</p>
              </div>
            </div>
          </div>

          <!-- Buttons -->
          <div class="flex gap-3">
            <button
              @click="showPublishDialog = false"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition"
            >
              Cancel
            </button>
            <button
              @click="publishForSwap"
              :disabled="publishing"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition disabled:opacity-50"
            >
              {{ publishing ? 'Publishing...' : 'Publish' }}
            </button>
          </div>
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
const publishing = ref(false);
const showPublishDialog = ref(false);
const selectedImage = ref(null);
const fileInput = ref(null);

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

const handleImageSelected = (event) => {
  const file = event.target.files?.[0];
  if (file) {
    selectedImage.value = file;
  }
};

const publishForSwap = async () => {
  try {
    publishing.value = true;

    // Create FormData to support multipart/form-data for file upload
    const formData = new FormData();
    formData.append('trip_id', trip.value.id);

    if (selectedImage.value) {
      formData.append('image', selectedImage.value);
    }

    await apiService.trips.publishTrip(formData);
    showPublishDialog.value = false;
    selectedImage.value = null;
    await fetchTripDetails();
  } catch (error) {
    console.error('Failed to publish trip:', error);
    alert(error.response?.data?.message || 'Failed to publish trip');
  } finally {
    publishing.value = false;
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
