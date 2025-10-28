<script setup>
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({ username: '', password: '', remember: false })
function submit() {
  form.post('/login', { onFinish: () => form.reset('password') })
}
</script>

<template>
  <Head title="Sign in to Grillstone" />

  <div class="min-h-screen gradient-bg flex items-center justify-center p-4">
    <!-- Login Container -->
    <div class="w-full max-w-6xl flex rounded-3xl overflow-hidden shadow-2xl animate-fade-in">
      <!-- Left Side - Branding -->
      <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-orange-600 via-red-600 to-red-700 p-12 flex-col justify-between relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
          <div class="absolute top-0 left-0 w-64 h-64 bg-white rounded-full -translate-x-32 -translate-y-32"></div>
          <div class="absolute bottom-0 right-0 w-96 h-96 bg-white rounded-full translate-x-32 translate-y-32"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10">
          <div class="flex items-center gap-3 mb-8">
            <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center">
              <i class="fas fa-fire text-orange-600 text-2xl"></i>
            </div>
            <div>
              <h1 class="text-3xl font-bold text-white">Grillstone</h1>
              <p class="text-orange-100 text-sm">Restaurant Management System</p>
            </div>
          </div>

          <div class="space-y-6 text-white">
            <h2 class="text-4xl font-bold leading-tight">Welcome Back!</h2>
            <p class="text-xl text-orange-100">Sign in to access your restaurant dashboard</p>
          </div>
        </div>

        <!-- Features -->
        <div class="relative z-10 space-y-4">
          <div class="flex items-center gap-3 text-white">
            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur">
              <i class="fas fa-cash-register"></i>
            </div>
            <span class="text-orange-50">Point of Sale System</span>
          </div>
          <div class="flex items-center gap-3 text-white">
            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur">
              <i class="fas fa-boxes-stacked"></i>
            </div>
            <span class="text-orange-50">Inventory Management</span>
          </div>
          <div class="flex items-center gap-3 text-white">
            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur">
              <i class="fas fa-chart-line"></i>
            </div>
            <span class="text-orange-50">Real-time Reports</span>
          </div>
        </div>
      </div>

      <!-- Right Side - Login Form -->
      <div class="w-full lg:w-1/2 bg-white p-8 md:p-12 flex flex-col justify-center">
        <!-- Mobile Logo -->
        <div class="lg:hidden flex items-center gap-3 mb-8">
          <div class="w-12 h-12 bg-gradient-to-br from-orange-600 to-red-600 rounded-xl flex items-center justify-center">
            <i class="fas fa-fire text-white text-2xl"></i>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Grillstone</h1>
            <p class="text-gray-600 text-sm">Restaurant Management</p>
          </div>
        </div>

        <div class="mb-8">
          <h2 class="text-3xl font-bold text-gray-900 mb-2">Sign In</h2>
          <p class="text-gray-600">Enter your credentials to access your account</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <!-- Username/Email Input -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Username or Email
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fas fa-user text-gray-400"></i>
              </div>
              <input
                v-model="form.username"
                type="text"
                required
                autofocus
                class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all"
                placeholder="Enter your username or email"
              />
            </div>
            <div v-if="form.errors.username" class="text-red-600 text-sm mt-2 flex items-center gap-1">
              <i class="fas fa-exclamation-circle"></i>
              {{ form.errors.username }}
            </div>
          </div>

          <!-- Password Input -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Password
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fas fa-lock text-gray-400"></i>
              </div>
              <input
                v-model="form.password"
                type="password"
                required
                class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all"
                placeholder="Enter your password"
              />
            </div>
            <div v-if="form.errors.password" class="text-red-600 text-sm mt-2 flex items-center gap-1">
              <i class="fas fa-exclamation-circle"></i>
              {{ form.errors.password }}
            </div>
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="flex items-center justify-between">
            <label class="flex items-center cursor-pointer group">
              <input
                id="remember"
                v-model="form.remember"
                type="checkbox"
                class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500 cursor-pointer"
              />
              <span class="ml-2 text-sm text-gray-700 group-hover:text-gray-900">Remember me</span>
            </label>
            <a href="/forgot-password" class="text-sm font-semibold text-orange-600 hover:text-orange-700 transition-colors">
              Forgot password?
            </a>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white font-semibold py-3 rounded-xl transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
          >
            <span v-if="!form.processing" class="flex items-center justify-center gap-2">
              <i class="fas fa-sign-in-alt"></i>
              Sign In
            </span>
            <span v-else class="flex items-center justify-center gap-2">
              <i class="fas fa-spinner fa-spin"></i>
              Signing in...
            </span>
          </button>
        </form>

        <!-- Footer -->
        <div class="mt-8 text-center">
          <p class="text-sm text-gray-600">
            Don't have an account?
            <a href="/register" class="font-semibold text-orange-600 hover:text-orange-700 transition-colors">
              Contact Administrator
            </a>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.gradient-bg {
  background: linear-gradient(135deg, #fff5f0 0%, #ffe8d9 50%, #ffd4b8 100%);
  background-attachment: fixed;
}

@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.6s ease-out;
}
</style>
