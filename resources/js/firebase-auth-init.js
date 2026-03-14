/**
 * Firebase Authentication Initialization
 * This file should be imported in your main app.js
 * It sets up authentication state monitoring and automatic redirects
 */

import firebaseAuthService from './firebase-auth-service';

/**
 * Initialize Firebase Authentication Monitoring
 * Call this function in your main app.js to set up auth state listeners
 */
export function initializeFirebaseAuth() {
  // Listen to authentication state changes
  const unsubscribe = firebaseAuthService.onAuthStateChanged((user) => {
    if (user) {
      console.log('User authenticated:', user.email);
      // Store user in global state (Vue/Pinia/Vuex)
      updateAuthState(user);
    } else {
      console.log('User not authenticated');
      // Clear user from global state
      clearAuthState();
      // Redirect to login if on protected page
      redirectToLoginIfNeeded();
    }
  });

  // Return unsubscribe function in case you need to stop listening
  return unsubscribe;
}

/**
 * Update global authentication state
 * Integrate with your state management (Pinia, Vuex, etc.)
 */
function updateAuthState(user) {
  // Example for Pinia store
  // import { useAuthStore } from './stores/auth';
  // const authStore = useAuthStore();
  // authStore.setUser(user);

  // Or for localStorage
  localStorage.setItem('user-authenticated', 'true');
  localStorage.setItem('user-email', user.email);
}

/**
 * Clear global authentication state
 */
function clearAuthState() {
  // Example for Pinia store
  // import { useAuthStore } from './stores/auth';
  // const authStore = useAuthStore();
  // authStore.clearUser();

  // Or for localStorage
  localStorage.removeItem('user-authenticated');
  localStorage.removeItem('user-email');
  localStorage.removeItem('laravel_token');
}

/**
 * Redirect to login if user is on a protected page
 */
function redirectToLoginIfNeeded() {
  const protectedPages = ['/dashboard', '/trips', '/swap'];
  const currentPath = window.location.pathname;

  if (protectedPages.some(page => currentPath.includes(page))) {
    window.location.href = '/login';
  }
}

/**
 * Logout user from Firebase
 */
export async function logoutUser() {
  try {
    await firebaseAuthService.logout();
    clearAuthState();
    window.location.href = '/login';
  } catch (error) {
    console.error('Logout error:', error);
  }
}

/**
 * Check if user is authenticated
 */
export function isUserAuthenticated() {
  return firebaseAuthService.currentUser !== null;
}

/**
 * Get current user
 */
export function getCurrentUser() {
  return firebaseAuthService.currentUser;
}

/**
 * Usage in main app.js:
 * 
 * import './firebase-config';
 * import { initializeFirebaseAuth } from './firebase-auth-init';
 * 
 * // Initialize Firebase auth monitoring when app starts
 * initializeFirebaseAuth();
 * 
 * // Create your Vue app
 * const app = createApp(App);
 * app.mount('#app');
 */
