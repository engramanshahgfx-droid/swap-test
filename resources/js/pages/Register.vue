<template>
  <div class="p-8">
    <!-- Header -->
    <div class="text-center mb-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Create Account</h1>
      <p class="text-gray-600">Join flightSwap  to manage your flights</p>
    </div>

    <!-- Error/Success Alert -->
    <div v-if="error" class="mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded text-sm">
      <p class="text-red-700">{{ error }}</p>
    </div>
    <div v-if="success" class="mb-4 bg-green-50 border-l-4 border-green-500 p-3 rounded text-sm">
      <p class="text-green-700">{{ success }}</p>
    </div>

    <!-- Registration Form -->
    <form @submit.prevent="handleRegister" class="space-y-4 max-h-[60vh] overflow-y-auto pr-2">
      <!-- Full Name -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
        <input
          v-model="form.full_name"
          type="text"
          required
          placeholder="John Smith"
          :disabled="loading"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
        />
      </div>

      <!-- Employee ID -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID *</label>
        <input
          v-model="form.employee_id"
          type="text"
          required
          placeholder="EMP12345"
          :disabled="loading"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
        />
      </div>

      <!-- Email -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
        <input
          v-model="form.email"
          type="email"
          required
          placeholder="john.smith@airline.com"
          :disabled="loading"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
        />
      </div>

      <!-- Phone -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
        <input
          v-model="form.phone"
          type="tel"
          required
          placeholder="+971501234567"
          :disabled="loading"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
        />
      </div>

      <!-- Country/Base -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Country/Base *</label>
        <input
          v-model="form.country_base"
          type="text"
          required
          placeholder="Dubai, UAE"
          :disabled="loading"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
        />
      </div>

      <!-- Airline -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Airline *</label>
        <select
          v-model="form.airline_id"
          required
          :disabled="loading"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
        >
          <option value="">Select Airline</option>
          <option v-for="airline in airlines" :key="airline.id" :value="airline.id">
            {{ airline.name }}
          </option>
        </select>
      </div>

      <!-- Plane Type -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Plane Type *</label>
        <select
          v-model="form.plane_type_id"
          required
          :disabled="loading"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
        >
          <option value="">Select Plane Type</option>
          <option v-for="plane in planeTypes" :key="plane.id" :value="plane.id">
            {{ plane.name }}
          </option>
        </select>
      </div>

      <!-- Position -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Job Position *</label>
        <select
          v-model="form.position_id"
          required
          :disabled="loading"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
        >
          <option value="">Select Position</option>
          <option v-for="position in positions" :key="position.id" :value="position.id">
            {{ position.name }}
          </option>
        </select>
      </div>

      <!-- Password -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
        <input
          v-model="form.password"
          type="password"
          required
          placeholder="Minimum 8 characters"
          :disabled="loading"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
        />
      </div>

      <!-- Confirm Password -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
        <input
          v-model="form.password_confirmation"
          type="password"
          required
          placeholder="Re-enter password"
          :disabled="loading"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
        />
      </div>

      <!-- Terms Agreement -->
      <div class="flex items-start">
        <input
          v-model="form.agree_terms"
          type="checkbox"
          required
          :disabled="loading"
          class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
        />
        <label class="ml-2 text-sm text-gray-600">
          I agree to the <a href="#" class="text-blue-600 hover:underline">Terms of Service</a>
          and <a href="#" class="text-blue-600 hover:underline">Privacy Policy</a>
        </label>
      </div>

      <!-- Submit Button -->
      <button
        type="submit"
        :disabled="loading || !form.agree_terms"
        class="w-full bg-blue-600 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition disabled:bg-gray-400 disabled:cursor-not-allowed text-sm"
      >
        <span v-if="!loading">Create Account</span>
        <span v-else class="flex items-center justify-center">
          <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Creating account...
        </span>
      </button>
    </form>

    <!-- Login Link -->
    <div class="mt-4 text-center">
      <p class="text-sm text-gray-600">
        Already have an account?
        <router-link to="/login" class="text-blue-600 hover:text-blue-700 font-medium">
          Sign in here
        </router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  employee_id: '',
  full_name: '',
  phone: '',
  email: '',
  country_base: '',
  airline_id: '',
  plane_type_id: '',
  position_id: '',
  password: '',
  password_confirmation: '',
  agree_terms: false,
});

const airlines = ref([
  { id: 1, name: 'Emirates' },
  { id: 2, name: 'Etihad Airways' },
  { id: 3, name: 'Qatar Airways' },
  { id: 4, name: 'Other' },
]);

const planeTypes = ref([
  { id: 1, name: 'Boeing 777' },
  { id: 2, name: 'Boeing 787' },
  { id: 3, name: 'Airbus A380' },
  { id: 4, name: 'Airbus A350' },
  { id: 5, name: 'Other' },
]);

const positions = ref([
  { id: 1, name: 'Flight Attendant' },
  { id: 2, name: 'Senior Flight Attendant' },
  { id: 3, name: 'Purser' },
  { id: 4, name: 'Senior Purser' },
]);

const loading = ref(false);
const error = ref('');
const success = ref('');

const handleRegister = async () => {
  error.value = '';
  success.value = '';

  // Validate password match
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match';
    return;
  }

  if (form.value.password.length < 8) {
    error.value = 'Password must be at least 8 characters';
    return;
  }

  loading.value = true;

  try {
    // Register with Firebase and backend
    const result = await authStore.firebaseRegister(
      form.value.email,
      form.value.password,
      form.value.full_name,
      {
        employee_id: form.value.employee_id,
        phone: form.value.phone,
        country_base: form.value.country_base,
        airline_id: form.value.airline_id,
        plane_type_id: form.value.plane_type_id,
        position_id: form.value.position_id,
      }
    );

    if (result.success) {
      success.value = 'Account created successfully! Redirecting...';

      setTimeout(() => {
        router.push('/dashboard');
      }, 1500);
    } else {
      error.value = result.error || 'Registration failed. Please try again.';
    }
  } catch (err) {
    console.error('Registration error:', err);
    error.value = err.response?.data?.message || 'Registration failed. Please try again.';
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
