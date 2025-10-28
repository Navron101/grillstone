<template>
  <div class="min-h-screen gradient-bg">
    <!-- Header -->
    <header class="glass-effect shadow-lg">
      <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-orange-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-trash-alt text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-800">Waste Management</h1>
            <p class="text-sm text-gray-600">Record and track inventory waste</p>
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <button @click="toggleSidebar" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg" title="Toggle menu">
            <i class="fas" :class="sidebarOpen ? 'fa-angles-left' : 'fa-angles-right'"></i>
          </button>

          <div class="text-right mr-2">
            <p class="text-sm text-gray-600">{{ currentTime }}</p>
          </div>

          <a href="/pos" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg" title="POS">
            <i class="fas fa-cash-register text-lg"></i>
          </a>
        </div>
      </div>
    </header>

    <div class="flex h-[calc(100vh-80px)]">
      <!-- Sidebar -->
      <nav class="glass-effect m-4 rounded-2xl shadow-2xl flex flex-col transition-all duration-300 overflow-hidden"
           :class="sidebarOpen ? 'w-64' : 'w-20'">
        <div class="flex items-center justify-between px-3 py-3">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
              <i class="fas fa-fire text-white text-base"></i>
            </div>
            <span v-if="sidebarOpen" class="font-semibold text-gray-800">Menu</span>
          </div>
          <button @click="toggleSidebar" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
            <i class="fas" :class="sidebarOpen ? 'fa-angles-left' : 'fa-angles-right'"></i>
          </button>
        </div>

        <div class="px-2">
          <ul class="mt-1 space-y-1">
            <li>
              <a href="/pos" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-cash-register text-lg text-gray-600"></i>
                <span v-if="sidebarOpen" class="font-medium">POS</span>
              </a>
            </li>
            <li>
              <a href="/inventory" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-boxes-stacked text-lg text-gray-600"></i>
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
              <a href="/inventory/categories" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-tags text-lg text-gray-600"></i>
                <span v-if="sidebarOpen" class="font-medium">Categories</span>
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
              <a href="/inventory/waste" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors bg-orange-600 text-white">
                <i class="fas fa-trash-alt text-lg text-white"></i>
                <span v-if="sidebarOpen" class="font-medium">Waste</span>
              </a>
            </li>
            <li>
              <a href="/reports/waste" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-chart-line text-lg text-gray-600"></i>
                <span v-if="sidebarOpen" class="font-medium">Waste Reports</span>
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
      <section class="flex-1 p-4 flex flex-col overflow-y-auto">
        <!-- Add Waste Form -->
        <div class="glass-effect rounded-2xl p-6 mb-4 shadow-lg">
          <h2 class="text-lg font-bold text-gray-800 mb-4">Record Waste</h2>

          <form @submit.prevent="submitWaste" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Product *</label>
              <select v-model="form.product_id" @change="onProductChange" required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                <option value="">Select a product...</option>
                <option v-for="product in products" :key="product.id" :value="product.id">
                  {{ product.name }} ({{ product.type }})
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
              <input v-model.number="form.quantity" type="number" step="0.001" min="0.001" required
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                     placeholder="Enter quantity">
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Unit *</label>
              <select v-model="form.unit" required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                <option value="">Select unit...</option>
                <option value="kg">kg (Kilogram)</option>
                <option value="g">g (Gram)</option>
                <option value="lbs">lbs (Pounds)</option>
                <option value="oz">oz (Ounces)</option>
                <option value="L">L (Liter)</option>
                <option value="ml">ml (Milliliter)</option>
                <option value="gal">gal (Gallon)</option>
                <option value="pcs">pcs (Pieces)</option>
                <option value="box">box (Box)</option>
                <option value="pack">pack (Pack)</option>
                <option value="unit">unit (Unit)</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
              <select v-model="form.reason"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                <option value="">Select reason...</option>
                <option value="Expired">Expired</option>
                <option value="Spoiled">Spoiled</option>
                <option value="Damaged">Damaged</option>
                <option value="Over-prepared">Over-prepared</option>
                <option value="Quality Control">Quality Control</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
              <textarea v-model="form.notes" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        placeholder="Additional details..."></textarea>
            </div>

            <div class="md:col-span-2 flex gap-3">
              <button type="submit" :disabled="submitting"
                      class="px-6 py-2 bg-gradient-to-r from-orange-500 to-red-600 text-white rounded-lg hover:from-orange-600 hover:to-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-save mr-2"></i>
                {{ submitting ? 'Recording...' : 'Record Waste' }}
              </button>
              <button type="button" @click="resetForm"
                      class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                <i class="fas fa-redo mr-2"></i>
                Reset
              </button>
            </div>
          </form>

          <div v-if="errorMessage" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ errorMessage }}
          </div>

          <div v-if="successMessage" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
            <i class="fas fa-check-circle mr-2"></i>
            {{ successMessage }}
          </div>
        </div>

        <!-- Recent Waste Records -->
        <div class="glass-effect rounded-2xl p-6 shadow-lg flex-1">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-800">Recent Waste Records</h2>
            <button @click="loadWasteRecords" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
              <i class="fas fa-sync-alt"></i>
            </button>
          </div>

          <div v-if="loading" class="text-center py-8 text-gray-500">
            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
            <p>Loading waste records...</p>
          </div>

          <div v-else-if="wasteRecords.length === 0" class="text-center py-8 text-gray-500">
            <i class="fas fa-inbox text-4xl mb-2"></i>
            <p>No waste records found</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-gray-200">
                  <th class="text-left py-3 px-4 font-semibold text-gray-700">Date</th>
                  <th class="text-left py-3 px-4 font-semibold text-gray-700">Product</th>
                  <th class="text-right py-3 px-4 font-semibold text-gray-700">Quantity</th>
                  <th class="text-right py-3 px-4 font-semibold text-gray-700">Cost</th>
                  <th class="text-left py-3 px-4 font-semibold text-gray-700">Reason</th>
                  <th class="text-left py-3 px-4 font-semibold text-gray-700">User</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="record in wasteRecords" :key="record.id" class="border-b border-gray-100 hover:bg-gray-50">
                  <td class="py-3 px-4 text-sm text-gray-600">
                    {{ formatDate(record.wasted_at) }}
                  </td>
                  <td class="py-3 px-4 text-sm font-medium text-gray-800">
                    {{ record.product?.name }}
                  </td>
                  <td class="py-3 px-4 text-sm text-right text-gray-600">
                    {{ record.quantity }} {{ record.unit }}
                  </td>
                  <td class="py-3 px-4 text-sm text-right font-medium text-red-600">
                    ${{ Number(record.cost).toFixed(2) }}
                  </td>
                  <td class="py-3 px-4 text-sm text-gray-600">
                    <span v-if="record.reason" class="px-2 py-1 bg-gray-100 rounded text-xs">
                      {{ record.reason }}
                    </span>
                    <span v-else class="text-gray-400">-</span>
                  </td>
                  <td class="py-3 px-4 text-sm text-gray-600">
                    {{ record.user?.name || '-' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

// State
const sidebarOpen = ref(true)
const currentTime = ref('')
const products = ref([])
const wasteRecords = ref([])
const loading = ref(false)
const submitting = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const form = ref({
  product_id: '',
  quantity: '',
  unit: '',
  reason: '',
  notes: '',
  location_id: 1, // Default location
})

// Methods
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const updateTime = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  })
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const onProductChange = () => {
  // Auto-populate unit based on selected product if available
  const selectedProduct = products.value.find(p => p.id === form.value.product_id)
  if (selectedProduct && selectedProduct.unit) {
    form.value.unit = selectedProduct.unit
  }
}

const loadProducts = async () => {
  try {
    const response = await axios.get('/api/inventory/ingredients')
    products.value = response.data
  } catch (error) {
    console.error('Error loading products:', error)
    errorMessage.value = 'Failed to load products'
  }
}

const loadWasteRecords = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const response = await axios.get('/api/waste', {
      params: { per_page: 50 }
    })
    wasteRecords.value = response.data.data || response.data
  } catch (error) {
    console.error('Error loading waste records:', error)
    errorMessage.value = 'Failed to load waste records'
  } finally {
    loading.value = false
  }
}

const submitWaste = async () => {
  submitting.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const response = await axios.post('/api/waste', form.value)
    successMessage.value = 'Waste recorded successfully!'
    resetForm()
    await loadWasteRecords()

    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  } catch (error) {
    console.error('Error submitting waste:', error)
    errorMessage.value = error.response?.data?.message || 'Failed to record waste. Please check if there is sufficient stock.'
  } finally {
    submitting.value = false
  }
}

const resetForm = () => {
  form.value = {
    product_id: '',
    quantity: '',
    unit: '',
    reason: '',
    notes: '',
    location_id: 1,
  }
  errorMessage.value = ''
  successMessage.value = ''
}

// Lifecycle
onMounted(() => {
  updateTime()
  setInterval(updateTime, 1000)
  loadProducts()
  loadWasteRecords()
})
</script>

<style scoped>
.gradient-bg {
  background: linear-gradient(135deg, #fef3e2 0%, #fce4d0 100%);
}

.glass-effect {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
}
</style>
