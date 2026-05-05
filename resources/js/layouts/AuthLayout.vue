<template>
  <div class="auth-layout min-h-screen bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-pattern opacity-10"></div>

    <!-- Navigation Header -->
    <nav class="relative z-10 px-4 py-6">
      <div class="container mx-auto flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
          </svg>
          <span class="text-2xl font-bold text-white">flightSwap </span>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="relative z-10 container mx-auto px-4 py-8 flex items-center justify-center min-h-[80vh]">
      <div class="w-full max-w-md">
        <!-- Content Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
          <router-view />
        </div>

        <!-- Footer Links -->
        <div class="mt-6 text-center text-white text-sm">
          <p>&copy; 2026 flightSwap . All rights reserved.</p>
          <div class="mt-2 space-x-4">
            <a href="#" class="hover:underline">Terms of Service</a>
            <span>•</span>
            <a href="#" class="hover:underline">Privacy Policy</a>
            <span>•</span>
            <a href="#" class="hover:underline">Help Center</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Decorative Elements -->
    <div class="fixed top-0 left-0 w-64 h-64 bg-blue-400 rounded-full filter blur-3xl opacity-20 -translate-x-1/2 -translate-y-1/2"></div>
    <div class="fixed bottom-0 right-0 w-96 h-96 bg-blue-300 rounded-full filter blur-3xl opacity-20 translate-x-1/2 translate-y-1/2"></div>
  </div>
</template>

<script setup>
// No additional logic needed for auth layout
</script>

<style scoped>
.bg-pattern {
  background-image:
    linear-gradient(30deg, rgba(255, 255, 255, 0.1) 12%, transparent 12.5%, transparent 87%, rgba(255, 255, 255, 0.1) 87.5%, rgba(255, 255, 255, 0.1)),
    linear-gradient(150deg, rgba(255, 255, 255, 0.1) 12%, transparent 12.5%, transparent 87%, rgba(255, 255, 255, 0.1) 87.5%, rgba(255, 255, 255, 0.1)),
    linear-gradient(30deg, rgba(255, 255, 255, 0.1) 12%, transparent 12.5%, transparent 87%, rgba(255, 255, 255, 0.1) 87.5%, rgba(255, 255, 255, 0.1)),
    linear-gradient(150deg, rgba(255, 255, 255, 0.1) 12%, transparent 12.5%, transparent 87%, rgba(255, 255, 255, 0.1) 87.5%, rgba(255, 255, 255, 0.1));
  background-size: 80px 140px;
  background-position: 0 0, 0 0, 40px 70px, 40px 70px;
}
</style>
