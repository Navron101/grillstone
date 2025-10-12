<template>
  <main class="p-8">
    <h1 class="text-2xl font-bold mb-4">Tests</h1>
    <ul v-if="tests?.length">
      <li v-for="t in tests" :key="t.id">
        {{ t.id }} — {{ t.message }}
      </li>
    </ul>
    <p v-else>No rows yet.</p>
  </main>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'

// Strongly type the prop coming from Laravel
type TestRow = { id: number; message: string; created_at?: string; updated_at?: string }
const page = usePage<{ tests: TestRow[] }>()
const tests = page.props.tests
</script>
