import axios from 'axios';
import firebaseAuthService from './firebase-auth-service';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Firebase Token Interceptor for Axios
// Automatically adds Firebase ID token to Authorization header if authenticated
let firebaseTokenRefreshPromise = null;

window.axios.interceptors.request.use(
  async (config) => {
    try {
      // Get current Firebase auth state
      const user = firebaseAuthService.currentUser;

      if (user && user.idToken) {
        // Add Firebase token to the request
        config.headers.Authorization = `Bearer ${user.idToken}`;
      } else if (localStorage.getItem('laravel_token')) {
        // Fallback to Laravel Sanctum token if available
        config.headers.Authorization = `Bearer ${localStorage.getItem('laravel_token')}`;
      }
    } catch (error) {
      console.error('Error adding auth token to request:', error);
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response Interceptor for Token Refresh
window.axios.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;

    // If we get a 401 Unauthorized error, try to refresh the Firebase token
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;

      try {
        // If refreshing token is already in progress, wait for it
        if (firebaseTokenRefreshPromise) {
          await firebaseTokenRefreshPromise;
        } else {
          // Get new Firebase ID token
          const newToken = await firebaseAuthService.getIdToken(true);

          if (newToken) {
            firebaseAuthService.currentUser.idToken = newToken;
            originalRequest.headers.Authorization = `Bearer ${newToken}`;
          }
        }

        // Retry the original request with new token
        return window.axios(originalRequest);
      } catch (refreshError) {
        console.error('Token refresh failed:', refreshError);
        // If token refresh fails, redirect to login
        window.location.href = '/login';
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  }
);

// Store Laravel token interceptor
window.axios.interceptors.response.use(
  (response) => {
    // If response contains a Laravel token, store it for fallback auth
    if (response.data?.token) {
      localStorage.setItem('laravel_token', response.data.token);
    }
    return response;
  },
  (error) => Promise.reject(error)
);
