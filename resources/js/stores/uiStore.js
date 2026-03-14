import { defineStore } from 'pinia';

export const useUiStore = defineStore('ui', {
  state: () => ({
    sidebarCollapsed: false,
    language: 'en',
    darkMode: false,
    isRtl: false,
    unreadNotifications: 4,
    globalSearch: '',
  }),
  actions: {
    toggleSidebar() {
      this.sidebarCollapsed = !this.sidebarCollapsed;
    },
    toggleLanguage() {
      this.language = this.language === 'en' ? 'ar' : 'en';
    },
    toggleDarkMode() {
      this.darkMode = !this.darkMode;
    },
  },
});