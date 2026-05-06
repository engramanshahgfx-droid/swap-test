<template>
  <div class="dashboard-layout min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Top Navigation Bar -->
    <nav class="bg-white border-b border-gray-200 fixed w-full z-30 top-0 shadow-sm">
      <div class="px-4 lg:px-8 py-4">
        <div class="flex items-center justify-between">
          <!-- Left: Logo and Menu Toggle -->
          <div class="flex items-center gap-4">
            <button
              @click="toggleSidebar"
              class="lg:hidden p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>

            <router-link to="/dashboard" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
              <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
              </div>
              <span class="text-xl font-bold text-gray-900 hidden sm:block">CrewSwap</span>
            </router-link>
          </div>

          <!-- Right Section -->
          <div class="flex items-center gap-6">
            <!-- Language Switcher -->
            <div class="flex gap-2 items-center">
              <button
                v-for="lang in languages"
                :key="lang.code"
                @click="switchLanguage(lang.code)"
                :class="[
                  'px-3 py-1.5 rounded-full text-sm font-medium transition-all',
                  currentLanguage === lang.code
                    ? 'bg-blue-600 text-white shadow-md'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]"
              >
                {{ lang.name }}
              </button>
            </div>

            <!-- Notifications -->
            <button class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span v-if="unreadNotifications > 0"
                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-semibold">
                {{ unreadNotifications }}
              </span>
            </button>

            <!-- User Menu -->
            <div class="relative">
              <button
                @click="toggleUserMenu"
                class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded-lg transition-colors"
              >
                <div v-if="userImage" class="w-10 h-10 rounded-full border-2 border-blue-600 overflow-hidden">
                  <img :src="userImage" :alt="userName" class="w-full h-full object-cover">
                </div>
                <div v-else class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-white flex items-center justify-center font-semibold border-2 border-blue-600">
                  {{ userInitials }}
                </div>
                <div class="hidden sm:block text-left">
                  <p class="text-sm font-semibold text-gray-900">{{ userName }}</p>
                  <p class="text-xs text-gray-500">{{ userRole }}</p>
                </div>
              </button>

              <!-- Dropdown Menu -->
              <transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
              >
                <div
                  v-if="userMenuOpen"
                  class="absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-xl py-2 z-50 border border-gray-200"
                >
                  <div class="px-4 py-3 border-b border-gray-200">
                    <p class="text-sm font-semibold text-gray-900">{{ userName }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ userEmail }}</p>
                  </div>
                  <router-link
                    to="/profile"
                    class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors"
                    @click="userMenuOpen = false"
                  >
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profile
                  </router-link>
                  <router-link
                    to="/settings"
                    class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors"
                    @click="userMenuOpen = false"
                  >
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                  </router-link>
                  <div class="border-t border-gray-200 my-2"></div>
                  <button
                    @click="handleLogout"
                    class="w-full text-left flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors"
                  >
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                  </button>
                </div>
              </transition>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <div class="flex pt-20">
      <!-- Sidebar (Desktop) - Blue Design -->
      <aside
        :class="[
          'fixed lg:static inset-y-0 left-0 z-20 w-72 bg-[#2563eb] transform transition-transform duration-300 ease-in-out pt-20 lg:pt-0 shadow-xl',
          sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        ]"
      >
        <nav class="px-4 py-8 space-y-2 max-h-screen overflow-y-auto">
          <div class="px-4 pb-4 mb-4 border-b border-blue-600/30">
            <p class="text-blue-200 text-xs font-semibold uppercase tracking-wide">Main Menu</p>
          </div>

          <router-link
            v-for="item in menuItems"
            :key="item.name"
            :to="item.path"
            :class="[
              'flex items-center px-4 py-3.5 text-sm font-medium rounded-lg transition-all duration-200 group',
              $route.path === item.path
                ? 'bg-white/20 text-white shadow-lg'
                : 'text-blue-100 hover:bg-white/10 hover:text-white'
            ]"
            @click="sidebarOpen = false"
          >
            <component
              :is="item.icon"
              :class="[
                'w-5 h-5 mr-3 transition-transform duration-200',
                $route.path === item.path ? 'text-white' : 'text-blue-200 group-hover:text-white'
              ]"
            />
            <span>{{ item.label }}</span>
            <span v-if="item.badge" class="ml-auto bg-red-500 text-white text-xs px-2.5 py-1 rounded-full font-semibold">
              {{ item.badge }}
            </span>
          </router-link>

          <div class="px-4 pt-8 mt-6 pb-4 border-t border-blue-600/30">
            <p class="text-blue-200 text-xs font-semibold uppercase tracking-wide">Administration</p>
          </div>

          <router-link
            v-for="item in adminMenuItems"
            :key="item.name"
            :to="item.path"
            :class="[
              'flex items-center px-4 py-3.5 text-sm font-medium rounded-lg transition-all duration-200 group',
              $route.path === item.path
                ? 'bg-white/20 text-white shadow-lg'
                : 'text-blue-100 hover:bg-white/10 hover:text-white'
            ]"
            @click="sidebarOpen = false"
          >
            <component
              :is="item.icon"
              :class="[
                'w-5 h-5 mr-3 transition-transform duration-200',
                $route.path === item.path ? 'text-white' : 'text-blue-200 group-hover:text-white'
              ]"
            />
            <span>{{ item.label }}</span>
          </router-link>
        </nav>

        <!-- Logout Button at Bottom -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-blue-600/30 bg-[#2563eb]">
          <button
            @click="handleLogout"
            class="w-full flex items-center justify-center px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200 gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Log out
          </button>
        </div>
      </aside>

      <!-- Overlay (Mobile) -->
      <div
        v-if="sidebarOpen"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-black/50 z-10 lg:hidden"
      ></div>

      <!-- Main Content -->
      <main class="flex-1 p-4 lg:p-8 overflow-y-auto pb-20 lg:pb-8">
        <router-view />
      </main>
    </div>

    <!-- Bottom Navigation (Mobile) -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-30 shadow-2xl">
      <div class="grid grid-cols-5 gap-1">
        <router-link
          v-for="item in bottomNavItems"
          :key="item.name"
          :to="item.path"
          :class="[
            'flex flex-col items-center justify-center py-3 text-xs font-medium transition-colors',
            $route.path === item.path
              ? 'text-blue-600 bg-blue-50'
              : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50'
          ]"
        >
          <component :is="item.icon" class="w-6 h-6 mb-1" />
          <span>{{ item.label }}</span>
        </router-link>
      </div>
    </nav>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

// Icons as inline SVG components
const DashboardIcon = {
  template: `<svg fill="currentColor" viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>`
};

const AirlinesIcon = {
  template: `<svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2c-5.33 4.55-8 8.48-8 11.8 0 4.98 3.8 8.2 8 8.2s8-3.22 8-8.2c0-3.32-2.67-7.25-8-11.8zm0 18c-3.35 0-6-2.57-6-6.2 0-2.34 1.95-5.44 6-9.14 4.05 3.7 6 6.79 6 9.14 0 3.63-2.65 6.2-6 6.2z"/></svg>`
};

const PositionsIcon = {
  template: `<svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>`
};

const TripsIcon = {
  template: `<svg fill="currentColor" viewBox="0 0 24 24"><path d="M6 11h12v2h-12zm6-8C6.48 3 3 6.48 3 11.5S6.48 20 12 20s9-3.5 9-8.5S17.52 3 12 3zm0 15c-3.9 0-7-3.1-7-7s3.1-7 7-7 7 3.1 7 7-3.1 7-7 7zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 6 15.5 6 14 6.67 14 7.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 6 8.5 6 7 6.67 7 7.5 7.67 9 8.5 9z"/></svg>`
};

const SwapIcon = {
  template: `<svg fill="currentColor" viewBox="0 0 24 24"><path d="M7 13h10v-2H7v2zm0-6h10V5H7v2zm0 12h10v-2H7v2zm15-4l-4-4v3H8v2h10v3l4-4z"/></svg>`
};

const ReportIcon = {
  template: `<svg fill="currentColor" viewBox="0 0 24 24"><path d="M3 13h2v8H3zm4-8h2v16H7zm4-2h2v18h-2zm4 4h2v14h-2zm4-2h2v16h-2z"/></svg>`
};

const UsersIcon = {
  template: `<svg fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>`
};

const ServiceIcon = {
  template: `<svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>`
};

const SettingsIcon = {
  template: `<svg fill="currentColor" viewBox="0 0 24 24"><path d="M19.14,12.94c.04-.3.06-.61.06-.94c0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.62l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94L14.4,2.81c-.04-.24-.24-.41-.48-.41h-3.84c-.24,0-.43.17-.47.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-.22-.08-.47,0-.59.22L2.74,8.87C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s.02.64.07.94L2.86,14.52c-.18.14-.23.41-.12.62l1.92,3.32c.12.22.37.29.59.22l2.39-.96c.5.38,1.03.7,1.62.94l.36,2.54c.05.24.24.41.48.41h3.84c.24,0,.44-.17.47-.41l.36-2.54c.59-.24,1.13-.56,1.62-.94l2.39.96c.22.08.47,0,.59-.22l1.92-3.32c.12-.22.07-.48-.12-.62L19.14,12.94zM12,15.6c-1.98,0-3.6-1.62-3.6-3.6s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/></svg>`
};

const MessagesIcon = {
  template: `<svg fill="currentColor" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/></svg>`
};

const BrowseIcon = {
  template: `<svg fill="currentColor" viewBox="0 0 24 24"><path d="M15.5 1h-8C6.12 1 5 2.12 5 3.5v17C5 21.88 6.12 23 7.5 23h8c1.38 0 2.5-1.12 2.5-2.5v-17C18 2.12 16.88 1 15.5 1zm-4 21c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm4.5-4H7V4h9v14z"/></svg>`
};

const router = useRouter();
const authStore = useAuthStore();

// State
const sidebarOpen = ref(false);
const userMenuOpen = ref(false);
const unreadNotifications = ref(0);
const currentLanguage = ref('English');
const userImage = ref('');
const userName = ref('');
const userRole = ref('');
const userEmail = ref('');

// Languages
const languages = [
  { code: 'English', name: 'English', dir: 'ltr' },
  { code: 'ar', name: 'Ar' },
];

// Menu Items
const menuItems = [
  { name: 'dashboard', label: 'Dashboard', path: '/dashboard', icon: DashboardIcon },
  { name: 'swap-operation', label: 'Swap operation', path: '/swap-operation', icon: SwapIcon },
  { name: 'report', label: 'Report', path: '/report', icon: ReportIcon },
];

const adminMenuItems = [
  { name: 'airlines', label: 'Airlines', path: '/airlines', icon: AirlinesIcon },
  { name: 'positions', label: 'Positions', path: '/positions', icon: PositionsIcon },
  { name: 'users', label: 'Users', path: '/users', icon: UsersIcon },
  { name: 'service', label: 'Service', path: '/service', icon: ServiceIcon },
  { name: 'settings', label: 'Settings', path: '/settings', icon: SettingsIcon },
];

const bottomNavItems = [
  { name: 'dashboard', label: 'Home', path: '/dashboard', icon: DashboardIcon },
  { name: 'trips', label: 'Trips', path: '/my-trips', icon: TripsIcon },
  { name: 'browse', label: 'Browse', path: '/browse-trips', icon: BrowseIcon },
  { name: 'swap', label: 'Swaps', path: '/swap-operation', icon: SwapIcon },
  { name: 'messages', label: 'Messages', path: '/messages', icon: MessagesIcon },
];

// Computed
const userInitials = computed(() => {
  const name = userName.value || authStore.userName;
  if (!name) return 'U';
  const parts = name.split(' ');
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return name.substring(0, 2).toUpperCase();
});

// Methods
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const toggleUserMenu = () => {
  userMenuOpen.value = !userMenuOpen.value;
};

const switchLanguage = (lang) => {
  currentLanguage.value = lang;
  // Emit or dispatch language change event
  localStorage.setItem('selectedLanguage', lang);
  window.location.reload();
};

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};

// Lifecycle
onMounted(() => {
  // Load user data from auth store
  if (authStore.user) {
    userName.value = authStore.user.name || authStore.userName;
    userEmail.value = authStore.user.email || '';
    userRole.value = authStore.user.role || 'System Admin';
  }

  // Load language preference
  const savedLanguage = localStorage.getItem('selectedLanguage');
  if (savedLanguage) {
    currentLanguage.value = savedLanguage;
  }
});
</script>

<style scoped>
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: rgb(186, 194, 203);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgb(156, 166, 176);
}
</style>

