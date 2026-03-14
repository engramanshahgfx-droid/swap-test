<template>
  <div class="p-8">
    <!-- Header -->
    <div class="text-center mb-8">
      <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
      </div>
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Verify Your Phone</h1>
      <p class="text-gray-600">
        We sent a code to <span class="font-semibold">{{ phone }}</span>
      </p>
    </div>

    <!-- Error Alert -->
    <div v-if="error" class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
      <p class="text-sm text-red-700">{{ error }}</p>
    </div>

    <!-- OTP Form -->
    <form @submit.prevent="handleVerify" class="space-y-6">
      <!-- OTP Input -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-3 text-center">
          Enter 6-digit verification code
        </label>
        <div class="flex justify-center space-x-2">
          <input
            v-for="(digit, index) in otpDigits"
            :key="index"
            :ref="el => otpInputs[index] = el"
            v-model="otpDigits[index]"
            type="text"
            maxlength="1"
            inputmode="numeric"
            pattern="[0-9]"
            :disabled="loading"
            @input="handleInput(index, $event)"
            @keydown="handleKeyDown(index, $event)"
            @paste="handlePaste($event)"
            class="w-12 h-14 text-center text-2xl font-semibold border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition disabled:bg-gray-100"
          />
        </div>
      </div>

      <!-- Submit Button -->
      <button
        type="submit"
        :disabled="loading || !isOtpComplete"
        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition disabled:bg-gray-400 disabled:cursor-not-allowed"
      >
        <span v-if="!loading">Verify Code</span>
        <span v-else class="flex items-center justify-center">
          <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Verifying...
        </span>
      </button>
    </form>

    <!-- Resend Code -->
    <div class="mt-6 text-center">
      <p class="text-sm text-gray-600 mb-2">
        Didn't receive the code?
      </p>
      <button
        v-if="canResend"
        @click="handleResend"
        :disabled="resending"
        class="text-blue-600 hover:text-blue-700 font-medium text-sm disabled:text-gray-400"
      >
        {{ resending ? 'Sending...' : 'Resend Code' }}
      </button>
      <span v-else class="text-sm text-gray-500">
        Resend in {{ countdown }}s
      </span>
    </div>

    <!-- Back to Login -->
    <div class="mt-8 text-center">
      <router-link to="/login" class="text-sm text-gray-600 hover:text-gray-800">
        ← Back to Login
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const phone = ref(route.query.phone || '');
const otpDigits = ref(['', '', '', '', '', '']);
const otpInputs = ref([]);
const loading = ref(false);
const error = ref('');
const resending = ref(false);
const countdown = ref(60);
const canResend = ref(false);

let countdownInterval = null;

const isOtpComplete = computed(() => {
  return otpDigits.value.every(digit => digit !== '');
});

const otpCode = computed(() => {
  return otpDigits.value.join('');
});

const handleInput = (index, event) => {
  const value = event.target.value;
  
  // Only allow numbers
  if (value && !/^\d$/.test(value)) {
    otpDigits.value[index] = '';
    return;
  }

  // Move to next input
  if (value && index < 5) {
    otpInputs.value[index + 1]?.focus();
  }
};

const handleKeyDown = (index, event) => {
  // Handle backspace
  if (event.key === 'Backspace' && !otpDigits.value[index] && index > 0) {
    otpInputs.value[index - 1]?.focus();
  }

  // Handle left/right arrow keys
  if (event.key === 'ArrowLeft' && index > 0) {
    otpInputs.value[index - 1]?.focus();
  }
  if (event.key === 'ArrowRight' && index < 5) {
    otpInputs.value[index + 1]?.focus();
  }
};

const handlePaste = (event) => {
  event.preventDefault();
  const pastedData = event.clipboardData.getData('text').trim();
  
  // Check if pasted data is 6 digits
  if (/^\d{6}$/.test(pastedData)) {
    const digits = pastedData.split('');
    otpDigits.value = digits;
    otpInputs.value[5]?.focus();
  }
};

const handleVerify = async () => {
  if (!isOtpComplete.value) {
    error.value = 'Please enter the complete verification code';
    return;
  }

  error.value = '';
  loading.value = true;

  try {
    const result = await authStore.verifyOtp(phone.value, otpCode.value);

    if (result.success) {
      router.push('/dashboard');
    } else {
      error.value = result.error || 'Invalid verification code';
      // Clear OTP on error
      otpDigits.value = ['', '', '', '', '', ''];
      otpInputs.value[0]?.focus();
    }
  } catch (err) {
    console.error('OTP verification error:', err);
    error.value = 'Verification failed. Please try again.';
  } finally {
    loading.value = false;
  }
};

const handleResend = async () => {
  resending.value = true;
  error.value = '';

  try {
    // Call resend OTP API
    // await authStore.resendOtp(phone.value);
    
    // Reset countdown
    countdown.value = 60;
    canResend.value = false;
    startCountdown();
  } catch (err) {
    error.value = 'Failed to resend code. Please try again.';
  } finally {
    resending.value = false;
  }
};

const startCountdown = () => {
  countdownInterval = setInterval(() => {
    countdown.value--;
    if (countdown.value <= 0) {
      clearInterval(countdownInterval);
      canResend.value = true;
    }
  }, 1000);
};

onMounted(() => {
  // Focus first input
  otpInputs.value[0]?.focus();
  
  // Start countdown
  startCountdown();

  // Redirect if no phone provided
  if (!phone.value) {
    router.push('/login');
  }
});

onUnmounted(() => {
  if (countdownInterval) {
    clearInterval(countdownInterval);
  }
});
</script>

<style scoped>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type=number] {
  -moz-appearance: textfield;
}
</style>
