<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  canResetPassword?: boolean
  status?: string | null
}>()

const form = useForm({
  username: '',
  password: '',
  remember: false,
})

function submit() {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Log in" />

  <div class="min-h-screen gradient-bg flex items-center justify-center p-6">
    <div class="glass-effect w-full max-w-md rounded-2xl shadow-xl p-8">
      <div class="mb-6 text-center">
        <div class="mx-auto w-12 h-12 rounded-xl bg-gradient-to-r from-orange-500 to-red-600 flex items-center justify-center text-white">
          <i class="fas fa-utensils"></i>
        </div>
        <h1 class="mt-3 text-xl font-semibold text-gray-800">Sign in</h1>
        <p class="text-sm text-gray-600">Use your username and password</p>
      </div>

      <div v-if="props.status" class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg p-3">
        {{ props.status }}
      </div>

      <form @submit.prevent="submit" novalidate class="space-y-5">
        <div>
          <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
          <input
            id="username"
            name="username"
            type="text"
            autocomplete="username"
            v-model="form.username"
            class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
            autofocus
          />
          <div v-if="form.errors.username" class="mt-1 text-sm text-red-600">{{ form.errors.username }}</div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <input
            id="password"
            name="password"
            type="password"
            autocomplete="current-password"
            v-model="form.password"
            class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
          />
          <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
        </div>

        <div class="flex items-center justify-between">
          <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" v-model="form.remember" class="rounded border-gray-300" />
            Remember me
          </label>

          <Link
            v-if="props.canResetPassword"
            :href="route('password.request')"
            class="text-sm text-amber-700 hover:text-amber-800 hover:underline"
          >
            Forgot password?
          </Link>
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full h-10 rounded-md bg-amber-600 text-white font-medium hover:bg-amber-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="!form.processing">Log in</span>
          <span v-else>Signing in…</span>
        </button>
      </form>
    </div>
  </div>
</template>
