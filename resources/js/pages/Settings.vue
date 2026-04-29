<template>
  <div class="settings-page">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Settings</h1>
      <p class="text-gray-600">Manage user preferences and admin swap configuration.</p>
    </div>

    <div class="space-y-6">
      <!-- Swap Request Configuration -->
      <div class="bg-white rounded-xl shadow p-6 border border-slate-200">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
          <div>
            <h2 class="text-xl font-semibold text-slate-900">Swap request configuration</h2>
            <p class="text-sm text-slate-600">Control the cutoff window and default flight times used by swap validation.</p>
          </div>
          <div class="text-sm text-slate-500">
            Current validation window: <span class="font-semibold">{{ swapSettings.request_cutoff_hours }} hours</span>
          </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700">Request cutoff hours</label>
            <input
              type="number"
              min="0"
              max="168"
              v-model.number="swapSettings.request_cutoff_hours"
              class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
            />
            <p class="text-xs text-slate-500">Prevent swap requests within this many hours before departure.</p>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700">Default departure time</label>
            <input
              type="time"
              v-model="swapSettings.default_departure_time"
              class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
            />
            <p class="text-xs text-slate-500">Used if a flight has no explicit departure time.</p>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700">Default arrival time</label>
            <input
              type="time"
              v-model="swapSettings.default_arrival_time"
              class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
            />
            <p class="text-xs text-slate-500">Used if a flight has no explicit arrival time.</p>
          </div>
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p v-if="swapMessage" class="text-sm text-emerald-700">{{ swapMessage }}</p>
            <p v-if="swapError" class="text-sm text-red-700">{{ swapError }}</p>
          </div>
          <button
            @click="saveSwapSettings"
            :disabled="savingSwap"
            class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-400"
          >
            <span v-if="!savingSwap">Save swap settings</span>
            <span v-else>Saving...</span>
          </button>
        </div>
      </div>

      <!-- Personal Preferences -->
      <div class="bg-white rounded-xl shadow p-6 border border-slate-200">
        <h2 class="text-xl font-semibold text-slate-900 mb-4">Personal settings</h2>

        <div class="space-y-4">
          <div class="flex items-center justify-between gap-4">
            <div>
              <p class="font-medium text-slate-900">Theme</p>
              <p class="text-sm text-slate-500">Choose your preferred interface mode.</p>
            </div>
            <select v-model="userSettings.theme" @change="saveUserSettings" class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
              <option value="light">Light</option>
              <option value="dark">Dark</option>
              <option value="auto">Auto</option>
            </select>
          </div>

          <div class="flex items-center justify-between gap-4">
            <div>
              <p class="font-medium text-slate-900">Language</p>
              <p class="text-sm text-slate-500">Select the language for your dashboard.</p>
            </div>
            <select v-model="userSettings.language" @change="saveUserSettings" class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
              <option value="en">English</option>
              <option value="ar">العربية (Arabic)</option>
            </select>
          </div>

          <div class="grid gap-4 sm:grid-cols-3">
            <label class="block rounded-2xl border border-slate-300 bg-slate-50 p-4">
              <div class="flex items-center justify-between">
                <span class="font-medium text-slate-900">Email notifications</span>
                <input type="checkbox" v-model="userSettings.emailNotifications" @change="saveUserSettings" class="h-5 w-5 text-blue-600" />
              </div>
              <p class="mt-2 text-sm text-slate-500">Email alerts and updates.</p>
            </label>
            <label class="block rounded-2xl border border-slate-300 bg-slate-50 p-4">
              <div class="flex items-center justify-between">
                <span class="font-medium text-slate-900">Push notifications</span>
                <input type="checkbox" v-model="userSettings.pushNotifications" @change="saveUserSettings" class="h-5 w-5 text-blue-600" />
              </div>
              <p class="mt-2 text-sm text-slate-500">Browser and mobile notifications.</p>
            </label>
            <label class="block rounded-2xl border border-slate-300 bg-slate-50 p-4">
              <div class="flex items-center justify-between">
                <span class="font-medium text-slate-900">SMS notifications</span>
                <input type="checkbox" v-model="userSettings.smsNotifications" @change="saveUserSettings" class="h-5 w-5 text-blue-600" />
              </div>
              <p class="mt-2 text-sm text-slate-500">Receive messages by SMS.</p>
            </label>
          </div>
        </div>
      </div>

      <!-- Account -->
      <div class="bg-white rounded-xl shadow p-6 border border-slate-200">
        <h2 class="text-xl font-semibold text-slate-900 mb-4">Account</h2>
        <div class="space-y-3">
          <button class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-left text-slate-900 transition hover:bg-slate-50">
            <p class="font-medium">Change Password</p>
            <p class="text-sm text-slate-500">Update your account password.</p>
          </button>
          <button class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-left text-slate-900 transition hover:bg-slate-50">
            <p class="font-medium">Two-Factor Authentication</p>
            <p class="text-sm text-slate-500">Add extra login security.</p>
          </button>
          <button @click="handleLogout" class="w-full rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-left text-red-700 transition hover:bg-red-100">
            <p class="font-medium">Logout</p>
            <p class="text-sm">Sign out from your account.</p>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import { apiService } from '@/services/api';

const router = useRouter();
const authStore = useAuthStore();

const swapSettings = ref({
  request_cutoff_hours: 8,
  default_departure_time: '08:00',
  default_arrival_time: '10:30',
});
const userSettings = ref({
  theme: 'light',
  language: 'en',
  emailNotifications: true,
  pushNotifications: true,
  smsNotifications: false,
});
const savingSwap = ref(false);
const swapMessage = ref('');
const swapError = ref('');

const loadSwapSettings = async () => {
  swapError.value = '';
  try {
    const response = await apiService.admin.getSettings();
    if (response?.data?.data) {
      swapSettings.value = {
        ...swapSettings.value,
        ...response.data.data,
      };
    }
  } catch (err) {
    swapError.value = 'Unable to load swap settings. Refresh the page.';
  }
};

const saveSwapSettings = async () => {
  savingSwap.value = true;
  swapMessage.value = '';
  swapError.value = '';
  try {
    const response = await apiService.admin.updateSettings({
      request_cutoff_hours: swapSettings.value.request_cutoff_hours,
      default_departure_time: swapSettings.value.default_departure_time,
      default_arrival_time: swapSettings.value.default_arrival_time,
    });
    if (response?.data?.data) {
      swapSettings.value = {
        ...swapSettings.value,
        ...response.data.data,
      };
    }
    swapMessage.value = 'Swap settings saved successfully.';
  } catch (err) {
    swapError.value = err?.response?.data?.message || 'Unable to save swap settings.';
  } finally {
    savingSwap.value = false;
  }
};

const saveUserSettings = () => {
  localStorage.setItem('user-settings', JSON.stringify(userSettings.value));
};

const loadUserSettings = () => {
  const saved = localStorage.getItem('user-settings');
  if (saved) {
    userSettings.value = { ...userSettings.value, ...JSON.parse(saved) };
  }
};

const handleLogout = async () => {
  if (confirm('Are you sure you want to logout?')) {
    await authStore.logout();
    router.push('/login');
  }
};

onMounted(() => {
  loadSwapSettings();
  loadUserSettings();
});
</script>
