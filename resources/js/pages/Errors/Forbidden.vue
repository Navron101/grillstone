<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'

const props = defineProps({
  message: {
    type: String,
    default: 'You do not have permission to access this page.',
  },
  module: {
    type: String,
    default: null,
  },
})

const page = usePage()
const user = page.props.auth?.user
</script>

<template>
  <Head title="Access Restricted" />

  <div class="min-h-screen gradient-bg flex items-center justify-center p-4">
    <div class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl overflow-hidden animate-fade-in">
      <!-- Header with Icon -->
      <div class="bg-gradient-to-br from-red-600 via-red-700 to-red-800 p-8 text-center relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
          <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full translate-x-32 -translate-y-32"></div>
        </div>
        <div class="relative z-10">
          <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
            <i class="fas fa-lock text-red-600 text-3xl"></i>
          </div>
          <h1 class="text-4xl font-bold text-white mb-2">Access Restricted</h1>
          <p class="text-red-100 text-lg">403 Forbidden</p>
        </div>
      </div>

      <!-- Content -->
      <div class="p-8 text-center">
        <div class="mb-6">
          <p class="text-gray-700 text-lg mb-4">
            {{ message }}
          </p>
          <p v-if="module" class="text-gray-600">
            You need access to the <span class="font-semibold text-red-600">{{ module }}</span> module to view this page.
          </p>
          <p v-if="user" class="text-gray-500 text-sm mt-4">
            Logged in as: <span class="font-medium">{{ user.name }}</span>
            <span v-if="user.role" class="text-gray-400">({{ user.role.display_name }})</span>
          </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
          <Link
            href="/"
            class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-orange-600 to-red-600 text-white font-semibold rounded-xl hover:from-orange-700 hover:to-red-700 transition-all duration-200 shadow-lg hover:shadow-xl"
          >
            <i class="fas fa-home"></i>
            Go to Home
          </Link>
          <button
            @click="$inertia.visit($inertia.page.url, { replace: false })"
            class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300 transition-all duration-200"
            onclick="window.history.back()"
          >
            <i class="fas fa-arrow-left"></i>
            Go Back
          </button>
        </div>

        <!-- Help Text -->
        <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-xl">
          <p class="text-sm text-blue-800">
            <i class="fas fa-info-circle mr-2"></i>
            If you believe you should have access to this page, please contact your system administrator.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.gradient-bg {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
  animation: fade-in 0.5s ease-out;
}
</style>
