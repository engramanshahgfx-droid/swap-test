<template>
  <div class="flight-card bg-white rounded-lg shadow-md hover:shadow-lg transition p-5 cursor-pointer" @click="handleClick">
    <!-- Flight Header -->
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center space-x-2">
        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
        </svg>
        <span class="font-bold text-gray-900">{{ trip.flight_number }}</span>
      </div>
      <span :class="statusClass" class="px-3 py-1 text-xs font-medium rounded-full">
        {{ statusText }}
      </span>
    </div>

    <!-- Route -->
    <div class="flex items-center justify-between mb-4">
      <div class="text-center">
        <p class="text-sm text-gray-500 mb-1">{{ formatTime(trip.departure_time) }}</p>
        <p class="text-xl font-bold text-gray-900">{{ trip.from_airport?.iata_code }}</p>
        <p class="text-xs text-gray-600">{{ trip.from_airport?.city || 'Departure' }}</p>
      </div>

      <div class="flex-1 px-4">
        <div class="flex items-center">
          <div class="flex-1 h-0.5 bg-gray-300"></div>
          <svg class="w-6 h-6 text-gray-400 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
          </svg>
          <div class="flex-1 h-0.5 bg-gray-300"></div>
        </div>
        <p class="text-center text-xs text-gray-500 mt-1">{{ duration }}</p>
      </div>

      <div class="text-center">
        <p class="text-sm text-gray-500 mb-1">{{ formatTime(trip.arrival_time) }}</p>
        <p class="text-xl font-bold text-gray-900">{{ trip.to_airport?.iata_code }}</p>
        <p class="text-xs text-gray-600">{{ trip.to_airport?.city || 'Arrival' }}</p>
      </div>
    </div>

    <!-- Date -->
    <div class="flex items-center justify-between text-sm text-gray-600 mb-3">
      <div class="flex items-center space-x-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <span>{{ formatDate(trip.departure_date) }}</span>
      </div>
      <div v-if="showPlaneType" class="flex items-center space-x-1">
        <span class="text-gray-500">✈️</span>
        <span>{{ trip.plane_type?.name || 'N/A' }}</span>
      </div>
    </div>

    <!-- Footer Slot -->
    <div v-if="$slots.footer" class="pt-3 border-t border-gray-200">
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  trip: {
    type: Object,
    required: true,
  },
  showStatus: {
    type: Boolean,
    default: true,
  },
  showPlaneType: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['click']);

const handleClick = () => {
  emit('click', props.trip);
};

const statusText = computed(() => {
  if (props.trip.status === 'published') return 'Published';
  if (props.trip.status === 'unpublished') return 'Not Published';
  if (props.trip.status === 'swapped') return 'Swapped';
  if (props.trip.status === 'completed') return 'Completed';
  return props.trip.status || 'N/A';
});

const statusClass = computed(() => {
  const status = props.trip.status;
  if (status === 'published') return 'bg-green-100 text-green-700';
  if (status === 'unpublished') return 'bg-gray-100 text-gray-700';
  if (status === 'swapped') return 'bg-blue-100 text-blue-700';
  if (status === 'completed') return 'bg-purple-100 text-purple-700';
  return 'bg-gray-100 text-gray-700';
});

const duration = computed(() => {
  if (!props.trip.departure_time || !props.trip.arrival_time) return 'N/A';
  // Calculate duration (simplified)
  const departure = new Date(`2000-01-01 ${props.trip.departure_time}`);
  const arrival = new Date(`2000-01-01 ${props.trip.arrival_time}`);
  const diff = (arrival - departure) / 1000 / 60; // minutes
  const hours = Math.floor(diff / 60);
  const mins = Math.floor(diff % 60);
  return `${hours}h ${mins}m`;
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

const formatTime = (timeString) => {
  if (!timeString) return 'N/A';
  return timeString.substring(0, 5); // HH:MM
};
</script>
