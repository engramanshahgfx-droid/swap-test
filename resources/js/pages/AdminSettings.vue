<template>
  <div class="settings-page max-w-6xl mx-auto px-4 py-6">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-slate-900">Admin Settings</h1>
      <p class="mt-2 text-sm text-slate-600">Manage swap request cutoff hours and default flight time settings from the Vue dashboard.</p>
    </div>

    <div class="space-y-6">
      <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
          <div>
            <h2 class="text-xl font-semibold text-slate-900">Swap request configuration</h2>
            <p class="text-sm text-slate-500">This value controls how soon before departure crew can request a flight swap.</p>
          </div>
          <div class="text-sm text-slate-500">
            Current validation: <span class="font-semibold">{{ form.request_cutoff_hours }}</span> hours before departure.
          </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
          <div class="space-y-3">
            <label class="block text-sm font-medium text-slate-700">Swap request cutoff</label>
            <input
              type="number"
              min="0"
              max="168"
              v-model.number="form.request_cutoff_hours"
              class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
            />
            <p class="text-xs text-slate-500">Users cannot request a swap within this many hours of scheduled departure.</p>
          </div>

          <div class="space-y-3">
            <label class="block text-sm font-medium text-slate-700">Default departure time</label>
            <input
              type="time"
              v-model="form.default_departure_time"
              class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
            />
            <p class="text-xs text-slate-500">Used when flight departure time is not explicitly defined.</p>
          </div>

          <div class="space-y-3">
            <label class="block text-sm font-medium text-slate-700">Default arrival time</label>
            <input
              type="time"
              v-model="form.default_arrival_time"
              class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
            />
            <p class="text-xs text-slate-500">Used when flight arrival time is not explicitly defined.</p>
          </div>
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="space-y-2">
            <p v-if="message" class="text-sm text-green-700">{{ message }}</p>
            <p v-if="error" class="text-sm text-red-700">{{ error }}</p>
          </div>
          <button
            @click="saveSettings"
            :disabled="saving"
            class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-400"
          >
            <span v-if="!saving">Save changes</span>
            <span v-else>Saving...</span>
          </button>
        </div>
      </div>

      <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
        <h2 class="text-xl font-semibold text-slate-900">Swap request policy</h2>
        <p class="mt-3 text-sm text-slate-600">When users submit a swap request, the system checks this cutoff hour value before allowing the request. If the flight departs sooner than the configured window, the request is blocked.</p>
        <div class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm text-slate-700">
          <strong>Current cutoff:</strong> {{ form.request_cutoff_hours }} hours before departure
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { apiService } from '@/services/api';

const form = ref({
  request_cutoff_hours: 8,
  default_departure_time: '08:00',
  default_arrival_time: '10:30',
});
const loading = ref(false);
const saving = ref(false);
const message = ref('');
const error = ref('');

const loadSettings = async () => {
  loading.value = true;
  error.value = '';
  try {
    const response = await apiService.admin.getSettings();
    if (response?.data?.data) {
      form.value = {
        ...form.value,
        ...response.data.data,
      };
    }
  } catch (err) {
    error.value = 'Unable to load admin settings. Please refresh the page.';
  } finally {
    loading.value = false;
  }
};

const saveSettings = async () => {
  saving.value = true;
  message.value = '';
  error.value = '';
  try {
    const response = await apiService.admin.updateSettings({
      request_cutoff_hours: form.value.request_cutoff_hours,
      default_departure_time: form.value.default_departure_time,
      default_arrival_time: form.value.default_arrival_time,
    });
    if (response?.data?.data) {
      form.value = {
        ...form.value,
        ...response.data.data,
      };
    }
    message.value = 'Settings updated successfully.';
  } catch (err) {
    error.value = err?.response?.data?.message || 'Unable to save settings.';
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  loadSettings();
});
</script>

<style scoped>
.settings-page {
  min-height: calc(100vh - 3.5rem);
}
</style>
