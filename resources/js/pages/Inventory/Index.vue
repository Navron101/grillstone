<template>
  <div class="min-h-screen gradient-bg">
    <!-- Header -->
    <header class="glass-effect shadow-lg">
      <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-boxes-stacked text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-800">Inventory Management</h1>
            <p class="text-sm text-gray-600">Manage ingredients, dishes, and stock</p>
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <button @click="toggleSidebar" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg" title="Toggle menu">
            <i class="fas" :class="sidebarOpen ? 'fa-angles-left' : 'fa-angles-right'"></i>
          </button>

          <div class="text-right mr-2">
            <p class="text-sm text-gray-600">{{ currentTime }}</p>
          </div>

          <a :href="posHref" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg" title="POS">
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
              <a :href="posHref" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors"
                 :class="isActive('/pos') ? 'bg-orange-600 text-white' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-700'">
                <i :class="['fas fa-cash-register text-lg', isActive('/pos') ? 'text-white' : 'text-gray-600']"></i>
                <span v-if="sidebarOpen" class="font-medium">POS</span>
              </a>
            </li>
            <li>
              <a href="/inventory" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors bg-orange-600 text-white">
                <i class="fas fa-boxes-stacked text-lg text-white"></i>
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
          </ul>
        </div>

        <div class="mt-auto px-3 py-3 text-xs text-gray-500">
          <div v-if="sidebarOpen">v0.1 • Grillstone</div>
          <div v-else class="text-center">v0.1</div>
        </div>
      </nav>

      <!-- Main Content -->
      <section class="flex-1 p-4 flex flex-col overflow-y-auto">
        <!-- Tab Navigation -->
        <div class="glass-effect rounded-2xl p-4 mb-4 shadow-lg">
          <div class="flex gap-2">
            <button @click="activeTab = 'ingredients'"
                    class="px-4 py-2 rounded-lg font-medium transition-colors"
                    :class="activeTab === 'ingredients' ? 'bg-orange-600 text-white' : 'bg-white text-gray-700 hover:bg-orange-50'">
              <i class="fas fa-carrot mr-2"></i>Ingredients
            </button>
            <button @click="activeTab = 'stock'"
                    class="px-4 py-2 rounded-lg font-medium transition-colors"
                    :class="activeTab === 'stock' ? 'bg-orange-600 text-white' : 'bg-white text-gray-700 hover:bg-orange-50'">
              <i class="fas fa-warehouse mr-2"></i>Stock Levels
            </button>
          </div>
        </div>

        <!-- Ingredients Tab -->
        <div v-if="activeTab === 'ingredients'" class="glass-effect rounded-2xl p-6 shadow-lg">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-800">Ingredients</h2>
            <button @click="showAddModal = true"
                    class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium">
              <i class="fas fa-plus mr-2"></i>Add Ingredient
            </button>
          </div>

          <div v-if="loadingIngredients" class="text-center py-8 text-gray-500">Loading ingredients...</div>

          <div v-else-if="!ingredients.length" class="text-center py-8 text-gray-400">
            <i class="fas fa-carrot text-4xl mb-3"></i>
            <p>No ingredients yet. Add one to get started.</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full">
              <thead>
                <tr class="text-left text-gray-600 border-b">
                  <th class="py-3 px-4">Name</th>
                  <th class="py-3 px-4">Unit</th>
                  <th class="py-3 px-4">Avg Cost</th>
                  <th class="py-3 px-4">Low Stock Threshold</th>
                  <th class="py-3 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ing in ingredients" :key="ing.id" class="border-b hover:bg-gray-50">
                  <td class="py-3 px-4 font-medium text-gray-900">{{ ing.name }}</td>
                  <td class="py-3 px-4 text-gray-600">{{ ing.unit_name || 'unit' }}</td>
                  <td class="py-3 px-4 text-gray-600">JMD {{ formatPrice(ing.price_cents) }}</td>
                  <td class="py-3 px-4 text-gray-600">{{ ing.low_stock_threshold ?? 5 }}</td>
                  <td class="py-3 px-4 text-right space-x-2">
                    <button @click="editIngredient(ing)"
                            class="text-blue-600 hover:text-blue-800">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button @click="deleteIngredient(ing.id)"
                            class="text-red-600 hover:text-red-800">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Stock Levels Tab -->
        <div v-if="activeTab === 'stock'" class="glass-effect rounded-2xl p-6 shadow-lg">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-800">Current Stock Levels</h2>
            <button @click="loadStockLevels" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
              <i class="fas fa-sync mr-2"></i>Refresh
            </button>
          </div>

          <div v-if="loadingStock" class="text-center py-8 text-gray-500">Loading stock levels...</div>

          <div v-else-if="!stockLevels.length" class="text-center py-8 text-gray-400">
            <i class="fas fa-warehouse text-4xl mb-3"></i>
            <p>No stock data available.</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full">
              <thead>
                <tr class="text-left text-gray-600 border-b">
                  <th class="py-3 px-4">Product</th>
                  <th class="py-3 px-4">Type</th>
                  <th class="py-3 px-4 text-right">On Hand</th>
                  <th class="py-3 px-4 text-right">Threshold</th>
                  <th class="py-3 px-4">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="stock in stockLevels" :key="stock.product_id" class="border-b hover:bg-gray-50">
                  <td class="py-3 px-4 font-medium text-gray-900">{{ stock.product_name }}</td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-1 text-xs rounded-full"
                          :class="stock.type === 'ingredient' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'">
                      {{ stock.type }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right font-medium">{{ stock.qty_on_hand }}</td>
                  <td class="py-3 px-4 text-right text-gray-600">{{ stock.low_stock_threshold ?? 5 }}</td>
                  <td class="py-3 px-4">
                    <span v-if="stock.qty_on_hand <= 0" class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 font-medium">
                      OUT OF STOCK
                    </span>
                    <span v-else-if="stock.qty_on_hand <= (stock.low_stock_threshold ?? 5)" class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800 font-medium">
                      LOW STOCK
                    </span>
                    <span v-else class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-medium">
                      IN STOCK
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>

    <!-- Add/Edit Ingredient Modal -->
    <div v-if="showAddModal || editingIngredient" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeModal">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">{{ editingIngredient ? 'Edit' : 'Add' }} Ingredient</h3>

        <form @submit.prevent="saveIngredient" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
            <input v-model="ingredientForm.name" type="text" required
                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                   placeholder="e.g., Beef, Tomatoes, Olive Oil">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Unit Name *</label>
            <select v-model="ingredientForm.unit_name" required
                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
              <option value="">Select unit...</option>
              <option>kg</option>
              <option>lb</option>
              <option>g</option>
              <option>oz</option>
              <option>L</option>
              <option>ml</option>
              <option>pcs</option>
              <option>unit</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Default Price (JMD) - Optional</label>
            <input v-model.number="ingredientForm.price" type="number" step="0.01" min="0"
                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                   placeholder="0.00">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Low Stock Threshold</label>
            <input v-model.number="ingredientForm.low_stock_threshold" type="number" step="0.01" min="0"
                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                   placeholder="5">
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <button type="button" @click="closeModal"
                    class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit"
                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
              {{ editingIngredient ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
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
import { ref, onMounted, watch } from 'vue'

// Sidebar
const sidebarOpen = ref(true)
function toggleSidebar() { sidebarOpen.value = !sidebarOpen.value }
onMounted(() => {
  const saved = localStorage.getItem('sidebarOpen')
  if (saved !== null) sidebarOpen.value = saved === '1'
})
watch(sidebarOpen, v => localStorage.setItem('sidebarOpen', v ? '1' : '0'))

// Routes
function isActive(path: string) { return window.location.pathname.startsWith(path) }
function routeUrl(name: string, fallback: string) {
  try { if (typeof (window as any).route === 'function') return (window as any).route(name) } catch {}
  return fallback
}
const posHref = routeUrl('pos.index', '/pos')

// Clock
const currentTime = ref('')
function tick() { currentTime.value = new Date().toLocaleTimeString() }
onMounted(() => { tick(); setInterval(tick, 1000) })

// Tabs
const activeTab = ref<'ingredients' | 'stock'>('ingredients')

// Types
type Ingredient = {
  id: number
  name: string
  unit_name: string
  price_cents: number
  low_stock_threshold: number | null
  type: string
}

type StockLevel = {
  product_id: number
  product_name: string
  type: string
  qty_on_hand: number
  low_stock_threshold: number | null
}

// Data
const ingredients = ref<Ingredient[]>([])
const stockLevels = ref<StockLevel[]>([])
const loadingIngredients = ref(false)
const loadingStock = ref(false)

// Modal
const showAddModal = ref(false)
const editingIngredient = ref<Ingredient | null>(null)
const ingredientForm = ref({
  name: '',
  unit_name: '',
  price: 0,
  low_stock_threshold: 5
})

// Functions
async function loadIngredients() {
  loadingIngredients.value = true
  try {
    const resp = await fetch('/api/inventory/ingredients')
    if (!resp.ok) throw new Error('Failed to load ingredients')
    ingredients.value = await resp.json()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load ingredients', 'error')
  } finally {
    loadingIngredients.value = false
  }
}

async function loadStockLevels() {
  loadingStock.value = true
  try {
    const resp = await fetch('/api/stock/summary?location_id=1')
    if (!resp.ok) throw new Error('Failed to load stock')
    const data = await resp.json()

    // Combine with product info
    const productsResp = await fetch('/api/inventory/ingredients')
    const products = await productsResp.json()

    stockLevels.value = data.map((item: any) => {
      const product = products.find((p: any) => p.id === item.product_id)
      return {
        product_id: item.product_id,
        product_name: product?.name ?? 'Unknown',
        type: product?.type ?? 'ingredient',
        qty_on_hand: parseFloat(item.on_hand ?? 0),
        low_stock_threshold: product?.low_stock_threshold ?? 5
      }
    })
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load stock levels', 'error')
  } finally {
    loadingStock.value = false
  }
}

async function saveIngredient() {
  try {
    const payload = {
      name: ingredientForm.value.name,
      unit_name: ingredientForm.value.unit_name,
      price_cents: Math.round((ingredientForm.value.price ?? 0) * 100),
      low_stock_threshold: ingredientForm.value.low_stock_threshold,
      type: 'ingredient'
    }

    if (editingIngredient.value) {
      const resp = await fetch(`/api/inventory/ingredients/${editingIngredient.value.id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      if (!resp.ok) throw new Error('Failed to update ingredient')
      toast('Success', 'Ingredient updated successfully', 'success')
    } else {
      const resp = await fetch('/api/inventory/ingredients', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      if (!resp.ok) throw new Error('Failed to create ingredient')
      toast('Success', 'Ingredient created successfully', 'success')
    }

    closeModal()
    await loadIngredients()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to save ingredient', 'error')
  }
}

function editIngredient(ing: Ingredient) {
  editingIngredient.value = ing
  ingredientForm.value = {
    name: ing.name,
    unit_name: ing.unit_name,
    price: ing.price_cents / 100,
    low_stock_threshold: ing.low_stock_threshold ?? 5
  }
}

async function deleteIngredient(id: number) {
  if (!confirm('Delete this ingredient? This cannot be undone.')) return

  try {
    const resp = await fetch(`/api/inventory/ingredients/${id}`, { method: 'DELETE' })
    if (!resp.ok) throw new Error('Failed to delete ingredient')
    toast('Success', 'Ingredient deleted', 'success')
    await loadIngredients()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to delete ingredient', 'error')
  }
}

function closeModal() {
  showAddModal.value = false
  editingIngredient.value = null
  ingredientForm.value = { name: '', unit_name: '', price: 0, low_stock_threshold: 5 }
}

function formatPrice(cents: number) {
  return (cents / 100).toFixed(2)
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

// Init
onMounted(() => {
  loadIngredients()
  loadStockLevels()
})
</script>

<style scoped>
.gradient-bg {
  background: linear-gradient(135deg, #f97316 0%, #ea580c 25%, #dc2626 50%, #92400e 75%, #451a03 100%);
}

.glass-effect {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.notification {
  position: fixed;
  bottom: 20px;
  right: 20px;
  opacity: 0;
  transform: translateY(20px);
  transition: all 0.3s ease;
  pointer-events: none;
  z-index: 9999;
}

.notification.show {
  opacity: 1;
  transform: translateY(0);
  pointer-events: all;
}
</style>
