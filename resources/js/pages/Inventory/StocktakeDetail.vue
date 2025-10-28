<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar (same as other inventory pages) -->
    <nav class="w-20 hover:w-64 transition-all duration-300 ease-in-out bg-white border-r border-gray-200 flex flex-col group"
         @mouseenter="sidebarOpen = true"
         @mouseleave="sidebarOpen = false">
      <div class="px-3 py-4 flex items-center gap-3">
        <i class="fas fa-fire text-2xl text-orange-600"></i>
        <span v-if="sidebarOpen" class="font-bold text-xl text-gray-800 whitespace-nowrap">Grillstone</span>
      </div>

      <div class="flex-1 px-3 py-4 overflow-y-auto">
        <ul class="space-y-2">
          <li>
            <a href="/pos" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
              <i class="fas fa-cash-register text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">POS</span>
            </a>
          </li>
          <li>
            <a href="/inventory" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors bg-orange-50 text-orange-700">
              <i class="fas fa-boxes text-lg text-orange-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Inventory</span>
            </a>
          </li>
          <li>
            <a href="/inventory/dishes" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
              <i class="fas fa-utensils text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Dishes</span>
            </a>
          </li>
          <li>
            <a href="/inventory/grn" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
              <i class="fas fa-clipboard-check text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Receive Stock</span>
            </a>
          </li>
          <li>
            <a href="/inventory/stocktake" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
              <i class="fas fa-clipboard-list text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Stocktake</span>
            </a>
          </li>
          <li>
            <a href="/reports" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
              <i class="fas fa-chart-line text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Reports</span>
            </a>
          </li>
        </ul>
      </div>

      <div class="mt-auto px-3 py-3 text-xs text-gray-500">
        <div v-if="sidebarOpen">v0.1 • Grillstone</div>
        <div v-else class="text-center">v0.1</div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
          <div>
            <div class="flex items-center gap-3 mb-2">
              <a href="/inventory/stocktake" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left"></i>
              </a>
              <h1 class="text-3xl font-bold text-gray-900">{{ stocktake?.reference || 'Loading...' }}</h1>
              <span v-if="stocktake" class="px-3 py-1 text-sm rounded-full font-medium" :class="statusClass(stocktake.status)">
                {{ stocktake.status }}
              </span>
            </div>
            <p class="text-gray-600">Physical inventory count</p>
          </div>

          <div class="flex gap-2">
            <button v-if="stocktake?.status === 'completed'" @click="downloadVarianceReport"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium">
              <i class="fas fa-download mr-2"></i>Download Variance Report
            </button>
            <button v-if="stocktake?.status === 'draft'" @click="saveProgress"
                    :disabled="saving"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium disabled:opacity-50">
              <i class="fas fa-save mr-2"></i>{{ saving ? 'Saving...' : 'Save Progress' }}
            </button>
            <button v-if="stocktake?.status === 'draft'" @click="completeStocktake"
                    :disabled="!canComplete"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium disabled:opacity-50">
              <i class="fas fa-check mr-2"></i>Complete
            </button>
            <button v-if="stocktake?.status === 'draft'" @click="cancelStocktake"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium">
              <i class="fas fa-times mr-2"></i>Cancel
            </button>
          </div>
        </div>

        <div v-if="loading" class="text-center py-12 text-gray-500">
          <i class="fas fa-spinner fa-spin text-3xl mb-3"></i>
          <p>Loading stocktake...</p>
        </div>

        <!-- Stocktake Lines -->
        <div v-else-if="stocktake" class="bg-white rounded-lg shadow">
          <!-- Summary Stats -->
          <div class="p-6 border-b grid grid-cols-4 gap-4">
            <div class="text-center">
              <div class="text-2xl font-bold text-gray-900">{{ stocktake.lines?.length || 0 }}</div>
              <div class="text-sm text-gray-600">Total Items</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-blue-600">{{ countedItems }}</div>
              <div class="text-sm text-gray-600">Counted</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-orange-600">{{ itemsWithVariance }}</div>
              <div class="text-sm text-gray-600">Variances</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold" :class="totalVarianceValue >= 0 ? 'text-green-600' : 'text-red-600'">
                JMD {{ formatCurrency(Math.abs(totalVarianceValue)) }}
              </div>
              <div class="text-sm text-gray-600">Total Variance</div>
            </div>
          </div>

          <!-- Variance Summary (for completed stocktakes) -->
          <div v-if="stocktake.status === 'completed' && itemsWithVariance > 0" class="p-6 border-b bg-gradient-to-r from-orange-50 to-red-50">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
              <i class="fas fa-chart-bar mr-2 text-orange-600"></i>Variance Summary
            </h3>
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-gray-600">Overage (Surplus)</span>
                  <i class="fas fa-arrow-up text-green-600"></i>
                </div>
                <div class="text-2xl font-bold text-green-600">{{ positiveVariancesCount }}</div>
                <div class="text-sm text-gray-500">Items | JMD {{ formatCurrency(positiveVariancesValue) }}</div>
              </div>
              <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-gray-600">Shortage (Loss)</span>
                  <i class="fas fa-arrow-down text-red-600"></i>
                </div>
                <div class="text-2xl font-bold text-red-600">{{ negativeVariancesCount }}</div>
                <div class="text-sm text-gray-500">Items | JMD {{ formatCurrency(Math.abs(negativeVariancesValue)) }}</div>
              </div>
            </div>
          </div>

          <!-- Search/Filter -->
          <div class="p-4 border-b bg-gray-50">
            <div class="flex gap-3">
              <div class="flex-1">
                <input v-model="searchQuery" type="text" placeholder="Search products..."
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
              </div>
              <select v-model="filterVariance" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
                <option value="all">All Items</option>
                <option value="counted">Counted Only</option>
                <option value="uncounted">Uncounted Only</option>
                <option value="variance">With Variance</option>
              </select>
            </div>
          </div>

          <!-- Items Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">System Qty</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actual Count</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Variance</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Value Impact</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="line in filteredLines" :key="line.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ line.product?.name || 'Unknown' }}</td>
                  <td class="px-4 py-3 text-sm text-right text-gray-700">{{ formatQty(line.system_qty) }}</td>
                  <td class="px-4 py-3">
                    <input v-if="stocktake.status === 'draft'"
                           v-model.number="line.actual_qty"
                           type="number"
                           step="0.01"
                           placeholder="Enter count"
                           class="w-32 px-3 py-1 border rounded text-right focus:ring-2 focus:ring-orange-500 focus:outline-none"
                           @input="calculateVariance(line)">
                    <span v-else class="text-sm text-gray-700">{{ formatQty(line.actual_qty) }}</span>
                  </td>
                  <td class="px-4 py-3 text-sm text-right font-medium" :class="varianceClass(line.variance)">
                    {{ line.variance !== null && line.variance !== undefined ? formatQty(line.variance) : '-' }}
                  </td>
                  <td class="px-4 py-3 text-sm text-right font-medium" :class="varianceClass(line.variance)">
                    {{ line.variance !== null ? formatCurrency(Math.abs((line.variance || 0) * (line.unit_cost_cents || 0) / 100)) : '-' }}
                  </td>
                  <td class="px-4 py-3">
                    <input v-if="stocktake.status === 'draft'"
                           v-model="line.notes"
                           type="text"
                           placeholder="Add note..."
                           class="w-full px-3 py-1 border rounded text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    <span v-else class="text-sm text-gray-600">{{ line.notes || '-' }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Complete Confirmation Modal -->
    <div v-if="showCompleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Complete Stocktake</h3>
        <p class="text-gray-700 mb-4">You have counted {{ countedItems }} of {{ stocktake?.lines?.length }} items.</p>

        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <label class="flex items-center gap-2">
            <input v-model="applyAdjustments" type="checkbox" class="rounded">
            <span class="text-sm font-medium text-blue-900">Apply inventory adjustments automatically</span>
          </label>
          <p class="text-xs text-blue-700 mt-1 ml-6">
            This will adjust your inventory to match the actual counts.
          </p>
        </div>

        <div v-if="itemsWithVariance > 0" class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
          <p class="text-sm text-yellow-800">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            {{ itemsWithVariance }} items have variances. Total impact: JMD {{ formatCurrency(Math.abs(totalVarianceValue)) }}
          </p>
        </div>

        <div class="flex justify-end gap-2">
          <button @click="showCompleteModal = false" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
            Cancel
          </button>
          <button @click="confirmComplete" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            Complete Stocktake
          </button>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <div class="notification bg-white rounded-lg shadow-lg p-4 w-80" :class="{ show: toastShow }">
      <div class="flex items-center">
        <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3" :class="toastBg">
          <i :class="toastIcon" class="text-white"></i>
        </div>
        <div class="flex-1">
          <p class="font-medium text-gray-800">{{ toastTitle }}</p>
          <p class="text-sm text-gray-600">{{ toastMsg }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  id: string | number
}>()

const sidebarOpen = ref(false)
const loading = ref(true)
const saving = ref(false)
const searchQuery = ref('')
const filterVariance = ref('all')
const showCompleteModal = ref(false)
const applyAdjustments = ref(true)

type StocktakeLine = {
  id: number
  product_id: number
  product?: { name: string }
  system_qty: number
  actual_qty: number | null
  variance: number | null
  unit_cost_cents: number
  notes: string | null
}

type Stocktake = {
  id: number
  reference: string
  status: string
  location_id: number
  lines?: StocktakeLine[]
  created_at: string
}

const stocktake = ref<Stocktake | null>(null)

async function loadStocktake() {
  loading.value = true
  try {
    const resp = await fetch(`/api/stocktakes/${props.id}`)
    if (!resp.ok) throw new Error('Failed to load stocktake')
    stocktake.value = await resp.json()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load stocktake', 'error')
  } finally {
    loading.value = false
  }
}

function calculateVariance(line: StocktakeLine) {
  if (line.actual_qty !== null && line.actual_qty !== undefined) {
    line.variance = line.actual_qty - line.system_qty
  } else {
    line.variance = null
  }
}

const filteredLines = computed(() => {
  if (!stocktake.value?.lines) return []

  let lines = stocktake.value.lines

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    lines = lines.filter(line =>
      line.product?.name?.toLowerCase().includes(query)
    )
  }

  // Variance filter
  if (filterVariance.value === 'counted') {
    lines = lines.filter(line => line.actual_qty !== null && line.actual_qty !== undefined)
  } else if (filterVariance.value === 'uncounted') {
    lines = lines.filter(line => line.actual_qty === null || line.actual_qty === undefined)
  } else if (filterVariance.value === 'variance') {
    lines = lines.filter(line => line.variance !== null && line.variance !== 0)
  }

  return lines
})

const countedItems = computed(() => {
  return stocktake.value?.lines?.filter(line => line.actual_qty !== null && line.actual_qty !== undefined).length || 0
})

const itemsWithVariance = computed(() => {
  return stocktake.value?.lines?.filter(line => line.variance !== null && line.variance !== 0).length || 0
})

const totalVarianceValue = computed(() => {
  return stocktake.value?.lines?.reduce((sum, line) => {
    if (line.variance !== null && line.variance !== undefined) {
      return sum + (line.variance * (line.unit_cost_cents || 0) / 100)
    }
    return sum
  }, 0) || 0
})

const positiveVariancesCount = computed(() => {
  return stocktake.value?.lines?.filter(line => line.variance && line.variance > 0).length || 0
})

const negativeVariancesCount = computed(() => {
  return stocktake.value?.lines?.filter(line => line.variance && line.variance < 0).length || 0
})

const positiveVariancesValue = computed(() => {
  return stocktake.value?.lines?.reduce((sum, line) => {
    if (line.variance && line.variance > 0) {
      return sum + (line.variance * (line.unit_cost_cents || 0) / 100)
    }
    return sum
  }, 0) || 0
})

const negativeVariancesValue = computed(() => {
  return stocktake.value?.lines?.reduce((sum, line) => {
    if (line.variance && line.variance < 0) {
      return sum + (line.variance * (line.unit_cost_cents || 0) / 100)
    }
    return sum
  }, 0) || 0
})

const canComplete = computed(() => {
  return countedItems.value > 0 && stocktake.value?.status === 'draft'
})

async function saveProgress() {
  if (!stocktake.value) return

  saving.value = true
  try {
    const lines = stocktake.value.lines?.map(line => ({
      id: line.id,
      actual_qty: line.actual_qty,  // Send null/undefined as-is, backend will default to system_qty
      notes: line.notes
    })) || []

    const resp = await fetch(`/api/stocktakes/${stocktake.value.id}/lines`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ lines })
    })

    if (!resp.ok) throw new Error('Failed to save progress')

    const result = await resp.json()
    stocktake.value = result.stocktake

    toast('Success', 'Progress saved', 'success')
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to save progress', 'error')
  } finally {
    saving.value = false
  }
}

function completeStocktake() {
  showCompleteModal.value = true
}

async function confirmComplete() {
  if (!stocktake.value) return

  try {
    // First save current progress
    await saveProgress()

    // Then complete
    const resp = await fetch(`/api/stocktakes/${stocktake.value.id}/complete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ apply_adjustments: applyAdjustments.value })
    })

    if (!resp.ok) throw new Error('Failed to complete stocktake')

    const result = await resp.json()
    stocktake.value = result.stocktake

    toast('Success', 'Stocktake completed! View variance summary below.', 'success')
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to complete stocktake', 'error')
  } finally {
    showCompleteModal.value = false
  }
}

async function cancelStocktake() {
  if (!confirm('Are you sure you want to cancel this stocktake?')) return

  try {
    const resp = await fetch(`/api/stocktakes/${props.id}/cancel`, {
      method: 'POST'
    })

    if (!resp.ok) throw new Error('Failed to cancel stocktake')

    toast('Success', 'Stocktake cancelled', 'success')

    setTimeout(() => {
      window.location.href = '/inventory/stocktake'
    }, 1500)
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to cancel stocktake', 'error')
  }
}

function downloadVarianceReport() {
  window.location.href = `/api/stocktakes/${props.id}/variance-report/download`
}

function statusClass(status: string) {
  if (status === 'completed') return 'bg-green-100 text-green-800'
  if (status === 'cancelled') return 'bg-red-100 text-red-800'
  return 'bg-yellow-100 text-yellow-800'
}

function varianceClass(variance: number | null) {
  if (variance === null || variance === 0) return 'text-gray-600'
  return variance > 0 ? 'text-green-600' : 'text-red-600'
}

function formatQty(qty: number | null | undefined): string {
  if (qty === null || qty === undefined) return '-'
  return qty.toFixed(2)
}

function formatCurrency(amount: number): string {
  return amount.toLocaleString('en-JM', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

// Toast
const toastShow = ref(false)
const toastTitle = ref('')
const toastMsg = ref('')
const toastIcon = ref('fas fa-check')
const toastBg = ref('bg-green-500')

function toast(title: string, msg: string, type: 'success' | 'error' | 'warning' = 'success') {
  toastTitle.value = title
  toastMsg.value = msg
  toastIcon.value = type === 'success' ? 'fas fa-check' : type === 'error' ? 'fas fa-times' : 'fas fa-exclamation'
  toastBg.value = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-yellow-500'
  toastShow.value = true
  setTimeout(() => toastShow.value = false, 3000)
}

onMounted(loadStocktake)
</script>

<style scoped>
.notification {
  position: fixed;
  top: 20px;
  right: 20px;
  opacity: 0;
  transform: translateX(400px);
  transition: all 0.3s ease;
  z-index: 9999;
}

.notification.show {
  opacity: 1;
  transform: translateX(0);
}
</style>
