import { auth } from './firebase-config';
import {
  createUserWithEmailAndPassword,
  signInWithEmailAndPassword,
  signOut,
  onAuthStateChanged,
  setPersistence,
  browserLocalPersistence,
  sendPasswordResetEmail,
  updateProfile
} from 'firebase/auth';

/**
 * Firebase Authentication Service
 * Provides methods for user authentication with Firebase
 */

class FirebaseAuthService {
  constructor() {
    this.currentUser = null;
    this.initPersistence();
  }

  /**
   * Initialize Firebase persistence to localStorage
   */
  async initPersistence() {
    try {
      await setPersistence(auth, browserLocalPersistence);
    } catch (error) {
      console.error('Error setting persistence:', error);
    }
  }

  /**
   * Register a new user with Firebase
   * @param {string} email - User email
   * @param {string} password - User password
   * @param {string} name - User display name
   * @returns {Promise<Object>} Firebase user object and ID token
   */
  async register(email, password, name) {
    try {
      const userCredential = await createUserWithEmailAndPassword(auth, email, password);
      const user = userCredential.user;

      // Set display name
      await updateProfile(user, {
        displayName: name
      });

      // Get ID token for backend verification
      const idToken = await user.getIdToken();

      return {
        success: true,
        user: {
          uid: user.uid,
          email: user.email,
          displayName: user.displayName
        },
        idToken
      };
    } catch (error) {
      return {
        success: false,
        error: this.getErrorMessage(error.code)
      };
    }
  }

  /**
   * Sign in user with email and password
   * @param {string} email - User email
   * @param {string} password - User password
   * @returns {Promise<Object>} Firebase user object and ID token
   */
  async login(email, password) {
    try {
      const userCredential = await signInWithEmailAndPassword(auth, email, password);
      const user = userCredential.user;

      // Get ID token for backend verification
      const idToken = await user.getIdToken();

      return {
        success: true,
        user: {
          uid: user.uid,
          email: user.email,
          displayName: user.displayName
        },
        idToken
      };
    } catch (error) {
      return {
        success: false,
        error: this.getErrorMessage(error.code)
      };
    }
  }

  /**
   * Sign out current user
   * @returns {Promise<void>}
   */
  async logout() {
    try {
      await signOut(auth);
      this.currentUser = null;
      return { success: true };
    } catch (error) {
      return {
        success: false,
        error: error.message
      };
    }
  }

  /**
   * Send password reset email
   * @param {string} email - User email
   * @returns {Promise<Object>} Success status
   */
  async resetPassword(email) {
    try {
      await sendPasswordResetEmail(auth, email);
      return { success: true };
    } catch (error) {
      return {
        success: false,
        error: this.getErrorMessage(error.code)
      };
    }
  }

  /**
   * Get current Firebase user
   * @returns {Promise<Object|null>} Current user or null
   */
  async getCurrentUser() {
    return new Promise((resolve) => {
      const unsubscribe = onAuthStateChanged(auth, async (user) => {
        if (user) {
          const idToken = await user.getIdToken();
          this.currentUser = {
            uid: user.uid,
            email: user.email,
            displayName: user.displayName,
            idToken
          };
        } else {
          this.currentUser = null;
        }
        resolve(this.currentUser);
        unsubscribe();
      });
    });
  }

  /**
   * Listen for authentication state changes
   * @param {function} callback - Callback function on state change
   * @returns {function} Unsubscribe function
   */
  onAuthStateChanged(callback) {
    return onAuthStateChanged(auth, async (user) => {
      if (user) {
        const idToken = await user.getIdToken();
        this.currentUser = {
          uid: user.uid,
          email: user.email,
          displayName: user.displayName,
          idToken
        };
        callback(this.currentUser);
      } else {
        this.currentUser = null;
        callback(null);
      }
    });
  }

  /**
   * Get current Firebase Auth instance
   * @returns {Firebase Auth Object}
   */
  getAuth() {
    return auth;
  }

  /**
   * Get user's ID token with refresh
   * @returns {Promise<string>} ID token
   */
  async getIdToken(forceRefresh = false) {
    if (auth.currentUser) {
      return await auth.currentUser.getIdToken(forceRefresh);
    }
    return null;
  }

  /**
   * Map Firebase auth error codes to user-friendly messages
   * @param {string} errorCode - Firebase error code
   * @returns {string} User-friendly error message
   */
  getErrorMessage(errorCode) {
    const errorMessages = {
      'auth/email-already-in-use': 'Email address is already in use',
      'auth/invalid-email': 'Invalid email address',
      'auth/operation-not-allowed': 'Operation not allowed',
      'auth/weak-password': 'Password is too weak',
      'auth/user-disabled': 'User account is disabled',
      'auth/user-not-found': 'User account not found',
      'auth/wrong-password': 'Incorrect password',
      'auth/invalid-login-credentials': 'Invalid email or password',
      'auth/too-many-requests': 'Too many login attempts. Please try again later',
      'auth/network-request-failed': 'Network error. Please check your connection'
    };

    return errorMessages[errorCode] || 'Authentication failed. Please try again.';
  }
}

// Export singleton instance
export default new FirebaseAuthService();
