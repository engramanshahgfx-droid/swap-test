/**
 * Firebase Login Component Example
 * Vue 3 Component for user login with Firebase
 * 
 * Usage:
 * <FirebaseLoginForm @login-success="handleLoginSuccess" />
 */

<template>
  <div class="firebase-login-form">
    <h2>Login</h2>

    <!-- Error Messages -->
    <div v-if="error" class="error-alert">
      {{ error }}
    </div>

    <!-- Success Message -->
    <div v-if="successMessage" class="success-alert">
      {{ successMessage }}
    </div>

    <!-- Login Form -->
    <form @submit.prevent="handleLogin">
      <div class="form-group">
        <label for="email">Email Address</label>
        <input
          id="email"
          v-model="formData.email"
          type="email"
          placeholder="Enter your email"
          required
          :disabled="isLoading"
        />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input
          id="password"
          v-model="formData.password"
          type="password"
          placeholder="Enter your password"
          required
          :disabled="isLoading"
        />
      </div>

      <button
        type="submit"
        class="btn-primary"
        :disabled="isLoading"
      >
        {{ isLoading ? 'Logging in...' : 'Login' }}
      </button>
    </form>

    <!-- Additional Options -->
    <div class="auth-options">
      <p>
        Don't have an account?
        <router-link to="/register">Register here</router-link>
      </p>
      <p>
        <router-link to="/forgot-password">Forgot password?</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import firebaseAuthService from '../firebase-auth-service';
import axios from 'axios';

const formData = ref({
  email: '',
  password: ''
});

const isLoading = ref(false);
const error = ref('');
const successMessage = ref('');

const emit = defineEmits(['login-success']);

const handleLogin = async () => {
  error.value = '';
  successMessage.value = '';
  isLoading.value = true;

  try {
    // Step 1: Authenticate with Firebase
    const firebaseResult = await firebaseAuthService.login(
      formData.value.email,
      formData.value.password
    );

    if (!firebaseResult.success) {
      error.value = firebaseResult.error;
      isLoading.value = false;
      return;
    }

    // Step 2: Send ID token to backend to create session
    const response = await axios.post('/api/firebase/login', {
      idToken: firebaseResult.idToken
    });

    // Step 3: Store Laravel token for API access
    localStorage.setItem('laravel_token', response.data.token);

    successMessage.value = 'Login successful! Redirecting...';

    // Emit success event with user data
    emit('login-success', {
      user: response.data.user,
      token: response.data.token
    });

    // Redirect after brief delay
    setTimeout(() => {
      window.location.href = '/dashboard';
    }, 1000);
  } catch (err) {
    console.error('Login error:', err);
    error.value = err.response?.data?.message || 'Login failed. Please try again.';
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.firebase-login-form {
  max-width: 400px;
  margin: 0 auto;
  padding: 2rem;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  background: #fff;
}

h2 {
  text-align: center;
  margin-bottom: 1.5rem;
  color: #333;
}

.form-group {
  margin-bottom: 1rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #555;
}

input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  transition: border-color 0.3s;
}

input:focus {
  outline: none;
  border-color: #4CAF50;
  box-shadow: 0 0 5px rgba(76, 175, 80, 0.2);
}

input:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

.btn-primary {
  width: 100%;
  padding: 0.75rem;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-primary:hover:not(:disabled) {
  background-color: #45a049;
}

.btn-primary:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.error-alert {
  background-color: #ffebee;
  color: #c62828;
  padding: 1rem;
  border-radius: 4px;
  margin-bottom: 1rem;
  border-left: 4px solid #c62828;
}

.success-alert {
  background-color: #e8f5e9;
  color: #2e7d32;
  padding: 1rem;
  border-radius: 4px;
  margin-bottom: 1rem;
  border-left: 4px solid #2e7d32;
}

.auth-options {
  text-align: center;
  margin-top: 1.5rem;
  font-size: 0.9rem;
}

.auth-options p {
  margin: 0.5rem 0;
}

.auth-options a {
  color: #4CAF50;
  text-decoration: none;
}

.auth-options a:hover {
  text-decoration: underline;
}
</style>
