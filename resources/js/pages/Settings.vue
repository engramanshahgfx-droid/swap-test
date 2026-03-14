<template>
  <div class="settings-page">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Settings</h1>
      <p class="text-gray-600">Manage your preferences and account settings</p>
    </div>

    <div class="space-y-6">
      <!-- Appearance -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Appearance</h2>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium text-gray-900">Theme</p>
              <p class="text-sm text-gray-600">Choose your preferred color scheme</p>
            </div>
            <select 
              v-model="settings.theme" 
              class="border-gray-300 rounded-lg text-sm"
              @change="saveSettings"
            >
              <option value="light">Light</option>
              <option value="dark">Dark</option>
              <option value="auto">Auto</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Language -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Language & Region</h2>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium text-gray-900">Language</p>
              <p class="text-sm text-gray-600">Select your preferred language</p>
            </div>
            <select 
              v-model="settings.language" 
              class="border-gray-300 rounded-lg text-sm"
              @change="saveSettings"
            >
              <option value="en">English</option>
              <option value="ar">العربية (Arabic)</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Notifications -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Notifications</h2>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium text-gray-900">Email Notifications</p>
              <p class="text-sm text-gray-600">Receive notifications via email</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input 
                v-model="settings.emailNotifications" 
                type="checkbox" 
                class="sr-only peer"
                @change="saveSettings"
              />
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>

          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium text-gray-900">Push Notifications</p>
              <p class="text-sm text-gray-600">Receive push notifications on your device</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input 
                v-model="settings.pushNotifications" 
                type="checkbox" 
                class="sr-only peer"
                @change="saveSettings"
              />
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>

          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium text-gray-900">SMS Notifications</p>
              <p class="text-sm text-gray-600">Receive notifications via SMS</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input 
                v-model="settings.smsNotifications" 
                type="checkbox" 
                class="sr-only peer"
                @change="saveSettings"
              />
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>
        </div>
      </div>

      <!-- Privacy -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Privacy</h2>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium text-gray-900">Profile Visibility</p>
              <p class="text-sm text-gray-600">Who can see your profile</p>
            </div>
            <select 
              v-model="settings.profileVisibility" 
              class="border-gray-300 rounded-lg text-sm"
              @change="saveSettings"
            >
              <option value="public">Everyone</option>
              <option value="crew">Crew Members Only</option>
              <option value="private">Private</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Account -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Account</h2>
        <div class="space-y-3">
          <button class="w-full text-left px-4 py-3 hover:bg-gray-50 rounded-lg transition">
            <p class="font-medium text-gray-900">Change Password</p>
            <p class="text-sm text-gray-600">Update your password</p>
          </button>
          <button class="w-full text-left px-4 py-3 hover:bg-gray-50 rounded-lg transition">
            <p class="font-medium text-gray-900">Two-Factor Authentication</p>
            <p class="text-sm text-gray-600">Add an extra layer of security</p>
          </button>
          <button 
            @click="handleLogout"
            class="w-full text-left px-4 py-3 hover:bg-red-50 rounded-lg transition text-red-600"
          >
            <p class="font-medium">Logout</p>
            <p class="text-sm">Sign out from your account</p>
          </button>
        </div>
      </div>

      <!-- About -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">About</h2>
        <div class="space-y-3 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-600">Version</span>
            <span class="text-gray-900 font-medium">1.0.0</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Build</span>
            <span class="text-gray-900 font-medium">2026.03.08</span>
          </div>
          <div class="pt-3 border-t space-x-4">
            <a href="#" class="text-blue-600 hover:underline">Terms of Service</a>
            <a href="#" class="text-blue-600 hover:underline">Privacy Policy</a>
            <a href="#" class="text-blue-600 hover:underline">Help Center</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

const router = useRouter();
const authStore = useAuthStore();

const settings = ref({
  theme: 'light',
  language: 'en',
  emailNotifications: true,
  pushNotifications: true,
  smsNotifications: false,
  profileVisibility: 'crew',
});

const saveSettings = () => {
  // Save settings to local storage or backend
  localStorage.setItem('user-settings', JSON.stringify(settings.value));
  console.log('Settings saved:', settings.value);
};

const handleLogout = async () => {
  if (confirm('Are you sure you want to logout?')) {
    await authStore.logout();
    router.push('/login');
  }
};

// Load settings from storage
const loadSettings = () => {
  const saved = localStorage.getItem('user-settings');
  if (saved) {
    settings.value = { ...settings.value, ...JSON.parse(saved) };
  }
};

loadSettings();
</script>
