<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Stock Take</h1>
        <p class="text-gray-600 mt-1">Physical inventory counting and variance tracking</p>
      </div>

      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold">Stocktakes</h2>
          <button @click="createNewStocktake" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
            <i class="fas fa-plus mr-2"></i>New Stocktake
          </button>
        </div>

        <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>

        <div v-else-if="!stocktakes.length" class="text-center py-8 text-gray-400">
          <i class="fas fa-clipboard-list text-4xl mb-3"></i>
          <p>No stocktakes yet. Create one to get started.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead>
              <tr class="bg-gray-50">
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Counter</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="st in stocktakes" :key="st.id">
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ st.reference }}</td>
                <td class="px-4 py-3 text-sm">
                  <span class="px-2 py-1 text-xs rounded-full font-medium" :class="statusClass(st.status)">
                    {{ st.status }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ formatDate(st.created_at) }}</td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ st.counter?.name ?? 'N/A' }}</td>
                <td class="px-4 py-3 text-sm text-right space-x-2">
                  <button @click="viewStocktake(st.id)" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button v-if="st.status === 'draft'" @click="deleteStocktake(st.id)" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

const locationId = 1

type Stocktake = { id: number; reference: string; status: string; created_at: string; counter?: { name: string } }

const stocktakes = ref<Stocktake[]>([])
const loading = ref(false)

async function loadStocktakes() {
  loading.value = true
  try {
    const resp = await fetch(`/api/stocktakes?location_id=${locationId}`)
    stocktakes.value = await resp.json()
  } finally {
    loading.value = false
  }
}

async function createNewStocktake() {
  const resp = await fetch('/api/stocktakes', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ location_id: locationId }),
  })
  await loadStocktakes()
}

async function viewStocktake(id: number) {
  window.location.href = `/inventory/stocktake/${id}`
}

async function deleteStocktake(id: number) {
  if (confirm('Delete this stocktake?')) {
    await fetch(`/api/stocktakes/${id}`, { method: 'DELETE' })
    await loadStocktakes()
  }
}

function statusClass(status: string) {
  if (status === 'completed') return 'bg-green-100 text-green-800'
  if (status === 'cancelled') return 'bg-red-100 text-red-800'
  return 'bg-yellow-100 text-yellow-800'
}

function formatDate(dateStr: string) {
  return new Date(dateStr).toLocaleDateString()
}

onMounted(loadStocktakes)
</script>
