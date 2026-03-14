/**
 * Firebase Register Component Example
 * Vue 3 Component for user registration with Firebase
 * 
 * Usage:
 * <FirebaseRegisterForm @register-success="handleRegisterSuccess" />
 */

<template>
  <div class="firebase-register-form">
    <h2>Create Account</h2>

    <!-- Error Messages -->
    <div v-if="error" class="error-alert">
      {{ error }}
    </div>

    <!-- Success Message -->
    <div v-if="successMessage" class="success-alert">
      {{ successMessage }}
    </div>

    <!-- Registration Form -->
    <form @submit.prevent="handleRegister">
      <div class="form-group">
        <label for="name">Full Name</label>
        <input
          id="name"
          v-model="formData.name"
          type="text"
          placeholder="Enter your full name"
          required
          :disabled="isLoading"
        />
      </div>

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
          placeholder="Enter a strong password"
          required
          :disabled="isLoading"
        />
        <small>Password must be at least 8 characters</small>
      </div>

      <div class="form-group">
        <label for="confirm-password">Confirm Password</label>
        <input
          id="confirm-password"
          v-model="formData.confirmPassword"
          type="password"
          placeholder="Confirm your password"
          required
          :disabled="isLoading"
        />
      </div>

      <div class="form-group checkbox">
        <input
          id="terms"
          v-model="formData.agreeToTerms"
          type="checkbox"
          required
          :disabled="isLoading"
        />
        <label for="terms">
          I agree to the <a href="/terms">Terms of Service</a>
        </label>
      </div>

      <button
        type="submit"
        class="btn-primary"
        :disabled="isLoading || !formData.agreeToTerms"
      >
        {{ isLoading ? 'Creating Account...' : 'Create Account' }}
      </button>
    </form>

    <!-- Login Link -->
    <div class="auth-options">
      <p>
        Already have an account?
        <router-link to="/login">Login here</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import firebaseAuthService from '../firebase-auth-service';
import axios from 'axios';

const formData = ref({
  name: '',
  email: '',
  password: '',
  confirmPassword: '',
  agreeToTerms: false
});

const isLoading = ref(false);
const error = ref('');
const successMessage = ref('');

const emit = defineEmits(['register-success']);

const passwordsMatch = computed(() => {
  return formData.value.password === formData.value.confirmPassword ||
         !formData.value.confirmPassword;
});

const handleRegister = async () => {
  error.value = '';
  successMessage.value = '';

  // Validate passwords match
  if (!passwordsMatch.value) {
    error.value = 'Passwords do not match';
    return;
  }

  if (formData.value.password.length < 8) {
    error.value = 'Password must be at least 8 characters';
    return;
  }

  isLoading.value = true;

  try {
    // Step 1: Register with Firebase
    const firebaseResult = await firebaseAuthService.register(
      formData.value.email,
      formData.value.password,
      formData.value.name
    );

    if (!firebaseResult.success) {
      error.value = firebaseResult.error;
      isLoading.value = false;
      return;
    }

    // Step 2: Send ID token to backend to create user in database
    const response = await axios.post('/api/firebase/register', {
      email: firebaseResult.user.email,
      name: firebaseResult.user.displayName,
      idToken: firebaseResult.idToken
    });

    // Step 3: Store Laravel token for API access
    localStorage.setItem('laravel_token', response.data.token);

    successMessage.value = 'Account created successfully! Redirecting...';

    // Emit success event with user data
    emit('register-success', {
      user: response.data.user,
      token: response.data.token
    });

    // Redirect after brief delay
    setTimeout(() => {
      window.location.href = '/dashboard';
    }, 1500);
  } catch (err) {
    console.error('Registration error:', err);
    error.value = err.response?.data?.message ||
                  err.response?.data?.errors?.email?.[0] ||
                  'Registration failed. Please try again.';
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.firebase-register-form {
  max-width: 450px;
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

input[type="text"],
input[type="email"],
input[type="password"] {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  transition: border-color 0.3s;
  box-sizing: border-box;
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="password"]:focus {
  outline: none;
  border-color: #4CAF50;
  box-shadow: 0 0 5px rgba(76, 175, 80, 0.2);
}

input:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

small {
  display: block;
  margin-top: 0.25rem;
  color: #999;
  font-size: 0.85rem;
}

.form-group.checkbox {
  display: flex;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}

.form-group.checkbox input[type="checkbox"] {
  width: auto;
  margin-right: 0.5rem;
  margin-top: 0.25rem;
  cursor: pointer;
}

.form-group.checkbox label {
  margin: 0;
  font-weight: normal;
}

.form-group.checkbox a {
  color: #4CAF50;
  text-decoration: none;
}

.form-group.checkbox a:hover {
  text-decoration: underline;
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
