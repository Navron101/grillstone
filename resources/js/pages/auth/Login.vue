<script setup>
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({ username: '', password: '', remember: false })
function submit() {
  form.post('/login', { onFinish: () => form.reset('password') })
}
</script>

<template>

  <Head title="Sign in" />
  <div class="min-h-screen bg-gray-100 flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white rounded-xl shadow p-6">
      <h1 class="text-2xl font-semibold mb-1">Sign in</h1>
      <p class="text-sm text-gray-500 mb-6">Use your username or email and password.</p>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Username or Email</label>
          <input v-model="form.username" type="text" required autofocus
                 class="w-full border rounded px-3 py-2 focus:outline-none focus:ring" />
          <div v-if="form.errors.username" class="text-red-600 text-sm mt-1">{{ form.errors.username }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Password</label>
          <input v-model="form.password" type="password" required
                 class="w-full border rounded px-3 py-2 focus:outline-none focus:ring" />
          <div v-if="form.errors.password" class="text-red-600 text-sm mt-1">{{ form.errors.password }}</div>
        </div>

        <div class="flex items-center">
          <input id="remember" v-model="form.remember" type="checkbox" class="mr-2" />
          <label for="remember" class="text-sm">Remember me</label>
        </div>

        <button type="submit" :disabled="form.processing"
                class="w-full rounded bg-black text-white py-2 disabled:opacity-60">
          <span v-if="!form.processing">Sign in</span>
          <span v-else>Signing in…</span>
        </button>
      </form>
    </div>
  </div>
</template>
