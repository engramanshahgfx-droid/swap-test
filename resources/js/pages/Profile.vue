<template>
  <div class="profile-page">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">My Profile</h1>
      <p class="text-gray-600">View and edit your profile information</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- Profile Card -->
      <div class="bg-white rounded-lg shadow p-8">
        <div class="flex items-center mb-8">
          <div class="w-24 h-24 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-3xl">
            {{ userInitials }}
          </div>
          <div class="ml-6">
            <h2 class="text-2xl font-bold text-gray-900">{{ user.full_name }}</h2>
            <p class="text-gray-600">{{ user.email }}</p>
            <span class="mt-2 inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
              {{ user.status || 'Active' }}
            </span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Employee ID</label>
            <p class="text-gray-900">{{ user.employee_id || 'N/A' }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
            <p class="text-gray-900">{{ user.phone || 'N/A' }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Country/Base</label>
            <p class="text-gray-900">{{ user.country_base || 'N/A' }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Airline</label>
            <p class="text-gray-900">{{ user.airline?.name || 'N/A' }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Plane Type</label>
            <p class="text-gray-900">{{ user.plane_type?.name || 'N/A' }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Position</label>
            <p class="text-gray-900">{{ user.position?.name || 'N/A' }}</p>
          </div>
        </div>

        <div class="mt-8">
          <button 
            @click="editing = true" 
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition"
          >
            Edit Profile
          </button>
        </div>
      </div>

      <!-- Stats Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Activity Statistics</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="text-center">
            <p class="text-3xl font-bold text-blue-600">{{ stats.totalTrips }}</p>
            <p class="text-sm text-gray-600 mt-1">Total Trips</p>
          </div>
          <div class="text-center">
            <p class="text-3xl font-bold text-green-600">{{ stats.completedSwaps }}</p>
            <p class="text-sm text-gray-600 mt-1">Completed Swaps</p>
          </div>
          <div class="text-center">
            <p class="text-3xl font-bold text-purple-600">{{ stats.publishedTrips }}</p>
            <p class="text-sm text-gray-600 mt-1">Published Trips</p>
          </div>
          <div class="text-center">
            <p class="text-3xl font-bold text-yellow-600">{{ stats.pendingSwaps }}</p>
            <p class="text-sm text-gray-600 mt-1">Pending Swaps</p>
          </div>
        </div>
      </div>

      <!-- Account Info -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Account Information</h3>
        <div class="space-y-3">
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Member Since</span>
            <span class="text-gray-900 font-medium">{{ formatDate(user.created_at) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Last Login</span>
            <span class="text-gray-900 font-medium">{{ formatDate(user.last_login_at) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Account Type</span>
            <span class="text-gray-900 font-medium">{{ user.role || 'Crew Member' }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Profile Modal (Simplified, you can expand this) -->
    <div v-if="editing" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Edit Profile</h3>
        <p class="text-gray-600 mb-4">Profile editing feature coming soon!</p>
        <button 
          @click="editing = false" 
          class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/authStore';

const authStore = useAuthStore();

const user = computed(() => authStore.user || {});
const loading = ref(false);
const editing = ref(false);

const stats = ref({
  totalTrips: 0,
  completedSwaps: 0,
  publishedTrips: 0,
  pendingSwaps: 0,
});

const userInitials = computed(() => {
  const name = user.value.full_name || user.value.name || 'User';
  const parts = name.split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return name.substring(0, 2).toUpperCase();
});

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric',
  });
};

onMounted(async () => {
  if (!user.value.id) {
    await authStore.fetchUser();
  }
});
</script>
