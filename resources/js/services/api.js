import axios from 'axios';
import { useAuthStore } from '@/stores/authStore';

// Create axios instance with base configuration
const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
});

// Request interceptor - Add auth token to all requests
api.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore();

    // Add Firebase token if available
    if (authStore.firebaseToken) {
      config.headers.Authorization = `Bearer ${authStore.firebaseToken}`;
    }
    // Fallback to Laravel token
    else if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`;
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor - Handle errors globally
api.interceptors.response.use(
  (response) => {
    // Store Laravel token if returned
    if (response.data?.token) {
      const authStore = useAuthStore();
      authStore.setToken(response.data.token);
    }
    return response;
  },
  async (error) => {
    const authStore = useAuthStore();

    // Handle 401 Unauthorized
    if (error.response?.status === 401) {
      authStore.logout();
      if (window.location.pathname !== '/login') {
        window.location.href = '/login';
      }
    }

    // Handle 403 Forbidden
    if (error.response?.status === 403) {
      console.error('Access forbidden');
    }

    // Handle 404 Not Found
    if (error.response?.status === 404) {
      console.error('Resource not found');
    }

    // Handle 500 Server Error
    if (error.response?.status === 500) {
      console.error('Server error occurred');
    }

    return Promise.reject(error);
  }
);

// API Service Methods
export const apiService = {
  // ==================== AUTH ====================
  auth: {
    // Firebase Authentication
    firebaseRegister: (data) => api.post('/firebase/register', data),
    firebaseLogin: (data) => api.post('/firebase/login', data),
    firebaseVerify: (data) => api.post('/firebase/verify', data),
    firebaseLinkAccount: (data) => api.post('/firebase/link-account', data),

    // Legacy Authentication
    register: (data) => api.post('/register', data),
    login: (data) => api.post('/login', data),
    verifyOtp: (data) => api.post('/verify-otp', data),
    resendOtp: (data) => api.post('/resend-otp', data),
    logout: () => api.post('/logout'),
  },

  // ==================== LANGUAGE ====================
  language: {
    getSupportedLanguages: () => api.get('/languages'),
    getCurrentLanguage: () => api.get('/current-language'),
    setLanguage: (lang) => api.post(`/set-language/${lang}`),
  },

  // ==================== USER ====================
  user: {
    getProfile: () => api.get('/user'),
    updateProfile: (data) => api.put('/user', data),
    changePassword: (data) => api.post('/user/change-password', data),
  },

  // ==================== TRIPS ====================
  trips: {
    getMyTrips: (params) => api.get('/my-trips', { params }),
    getTripDetails: (id) => api.get(`/trip-details/${id}`),
    browseTrips: (params) => api.get('/browse-trips', { params }),
    publishTrip: (data) => api.post('/publish-trip', data),
    unpublishTrip: (id) => api.post(`/unpublish-trip/${id}`),
    getSwapHistory: (params) => api.get('/swap-history', { params }),
  },

  // ==================== SWAPS ====================
  swaps: {
    requestSwap: (data) => api.post('/request-swap', data),
    confirmSwap: (swapRequestId) => api.post(`/confirm-swap/${swapRequestId}`),
    rejectSwap: (swapRequestId) => api.post(`/reject-swap/${swapRequestId}`),
    cancelSwap: (swapRequestId) => api.post(`/cancel-swap/${swapRequestId}`),
  },

  // ==================== CHAT / MESSAGES ====================
  chat: {
    getConversations: (params) => api.get('/conversations', { params }),
    getMessages: (conversationId, params) => api.get(`/messages/${conversationId}`, { params }),
    sendMessage: (data) => api.post('/send-message', data),
    markAsRead: (conversationId) => api.post(`/messages/${conversationId}/read`),
    deleteMessage: (messageId) => api.delete(`/messages/${messageId}`),
  },

  // ==================== REPORTS ====================
  reports: {
    reportUser: (data) => api.post('/report-user', data),
    getMyReports: () => api.get('/my-reports'),
  },

  // ==================== ADMIN ====================
  admin: {
    getSettings: () => api.get('/admin/settings'),
    updateSettings: (data) => api.post('/admin/settings', data),
  },

  // ==================== NOTIFICATIONS ====================
  notifications: {
    getNotifications: (params) => api.get('/notifications', { params }),
    markAsRead: (id) => api.post(`/notifications/${id}/read`),
    markAllAsRead: () => api.post('/notifications/read-all'),
    deleteNotification: (id) => api.delete(`/notifications/${id}`),
  },
};

export default api;
