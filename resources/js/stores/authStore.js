import { defineStore } from 'pinia';
import firebaseAuthService from '@/firebase-auth-service';
import { apiService } from '@/services/api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('laravel_token') || null,
    firebaseToken: null,
    firebaseUser: null,
    isAuthenticated: false,
    loading: false,
    error: null,
  }),

  getters: {
    isLoggedIn: (state) => state.isAuthenticated && (state.token || state.firebaseToken),
    currentUser: (state) => state.user,
    authToken: (state) => state.firebaseToken || state.token,
    userName: (state) => state.user?.full_name || state.user?.name || 'User',
    userEmail: (state) => state.user?.email || '',
    userRole: (state) => state.user?.role || 'crew_member',
  },

  actions: {
    // ==================== FIREBASE AUTH ====================
    async firebaseRegister(email, password, name, additionalData = {}) {
      this.loading = true;
      this.error = null;

      try {
        // Step 1: Register with Firebase
        console.log('🔥 Registering with Firebase...', { email, name });
        const firebaseResult = await firebaseAuthService.register(email, password, name);

        if (!firebaseResult.success) {
          this.error = firebaseResult.error;
          console.error('❌ Firebase registration failed:', firebaseResult.error);
          return { success: false, error: firebaseResult.error };
        }

        console.log('✅ Firebase registration successful', { uid: firebaseResult.user.uid });
        this.firebaseUser = firebaseResult.user;
        this.firebaseToken = firebaseResult.idToken;

        // Step 2: Register with backend
        console.log('📡 Sending to backend endpoint: /api/firebase/register');
        console.log('📝 Payload:', {
          email: firebaseResult.user.email,
          name: firebaseResult.user.displayName,
          idTokenLength: firebaseResult.idToken?.length,
          additionalData
        });

        const response = await apiService.auth.firebaseRegister({
          email: firebaseResult.user.email,
          name: firebaseResult.user.displayName,
          idToken: firebaseResult.idToken,
          ...additionalData,
        });

        console.log('✅ Backend registration successful', response.data);
        this.user = response.data.user;
        this.token = response.data.token;
        this.isAuthenticated = true;

        localStorage.setItem('laravel_token', this.token);

        return { success: true, user: this.user };
      } catch (error) {
        console.error('❌ Registration error:', {
          status: error.response?.status,
          message: error.response?.data?.message,
          fullError: error.response?.data,
          originalError: error.message
        });
        this.error = error.response?.data?.message || 'Registration failed';
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },

    async firebaseLogin(email, password) {
      this.loading = true;
      this.error = null;

      try {
        // Step 1: Login with Firebase
        const firebaseResult = await firebaseAuthService.login(email, password);

        if (!firebaseResult.success) {
          this.error = firebaseResult.error;
          return { success: false, error: firebaseResult.error };
        }

        this.firebaseUser = firebaseResult.user;
        this.firebaseToken = firebaseResult.idToken;

        // Step 2: Sync with backend
        const response = await apiService.auth.firebaseLogin({
          idToken: firebaseResult.idToken,
        });

        this.user = response.data.user;
        this.token = response.data.token;
        this.isAuthenticated = true;

        localStorage.setItem('laravel_token', this.token);

        return { success: true, user: this.user };
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed';
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },

    // ==================== LEGACY AUTH (OTP) ====================
    async register(userData) {
      this.loading = true;
      this.error = null;

      try {
        const response = await apiService.auth.register(userData);
        
        return { 
          success: true, 
          message: response.data.message,
          requiresOtp: true 
        };
      } catch (error) {
        this.error = error.response?.data?.message || 'Registration failed';
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },

    async verifyOtp(phone, otp) {
      this.loading = true;
      this.error = null;

      try {
        const response = await apiService.auth.verifyOtp({ phone, otp_code: otp });
        
        this.user = response.data.user;
        this.token = response.data.token;
        this.isAuthenticated = true;

        localStorage.setItem('laravel_token', this.token);

        return { success: true, user: this.user };
      } catch (error) {
        this.error = error.response?.data?.message || 'OTP verification failed';
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },

    async login(email, password) {
      this.loading = true;
      this.error = null;

      try {
        const response = await apiService.auth.login({ email, password });
        
        // Check if OTP is required
        if (response.data.requires_otp) {
          return { 
            success: true, 
            requiresOtp: true,
            phone: response.data.phone 
          };
        }

        this.user = response.data.user;
        this.token = response.data.token;
        this.isAuthenticated = true;

        localStorage.setItem('laravel_token', this.token);

        return { success: true, user: this.user };
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed';
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },

    // ==================== COMMON ACTIONS ====================
    async logout() {
      try {
        // Logout from backend
        await apiService.auth.logout();
      } catch (error) {
        console.error('Backend logout failed:', error);
      }

      try {
        // Logout from Firebase
        await firebaseAuthService.logout();
      } catch (error) {
        console.error('Firebase logout failed:', error);
      }

      // Clear state
      this.user = null;
      this.token = null;
      this.firebaseToken = null;
      this.firebaseUser = null;
      this.isAuthenticated = false;

      // Clear storage
      localStorage.removeItem('laravel_token');
      localStorage.removeItem('user-authenticated');
      localStorage.removeItem('user-email');
    },

    async fetchUser() {
      try {
        const response = await apiService.user.getProfile();
        this.user = response.data.user || response.data;
        this.isAuthenticated = true;
        return this.user;
      } catch (error) {
        console.error('Failed to fetch user:', error);
        this.logout();
        return null;
      }
    },

    async initializeAuth() {
      // Check if Firebase user is logged in
      const firebaseUser = await firebaseAuthService.getCurrentUser();
      
      if (firebaseUser) {
        this.firebaseUser = firebaseUser;
        this.firebaseToken = firebaseUser.idToken;
        this.isAuthenticated = true;
        
        // Fetch user data from backend
        await this.fetchUser();
      } else if (this.token) {
        // If we have a Laravel token, fetch user
        await this.fetchUser();
      }
    },

    setToken(token) {
      this.token = token;
      localStorage.setItem('laravel_token', token);
    },

    clearError() {
      this.error = null;
    },
  },
});
