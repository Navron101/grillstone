<template>
  <div class="min-h-screen gradient-bg">
    <header class="glass-effect shadow-lg">
      <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-layer-group text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-800">Combo Management</h1>
            <p class="text-sm text-gray-600">Create and manage product combos/bundles</p>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <button @click="sidebarOpen = !sidebarOpen" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
            <i class="fas" :class="sidebarOpen ? 'fa-angles-left' : 'fa-angles-right'"></i>
          </button>
          <div class="text-right mr-2"><p class="text-sm text-gray-600">{{ currentTime }}</p></div>
          <a href="/pos" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg"><i class="fas fa-cash-register text-lg"></i></a>
        </div>
      </div>
    </header>

    <div class="flex h-[calc(100vh-80px)]">
      <nav class="glass-effect m-4 rounded-2xl shadow-2xl flex flex-col transition-all duration-300 overflow-hidden" :class="sidebarOpen ? 'w-64' : 'w-20'">
        <div class="flex items-center justify-between px-3 py-3">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center"><i class="fas fa-fire text-white text-base"></i></div>
            <span v-if="sidebarOpen" class="font-semibold text-gray-800">Menu</span>
          </div>
        </div>
        <div class="px-2">
          <ul class="mt-1 space-y-1">
            <li><a href="/pos" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50"><i class="fas fa-cash-register text-lg text-gray-600"></i><span v-if="sidebarOpen" class="font-medium">POS</span></a></li>
            <li><a href="/inventory" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50"><i class="fas fa-boxes-stacked text-lg text-gray-600"></i><span v-if="sidebarOpen" class="font-medium">Inventory</span></a></li>
            <li><a href="/inventory/dishes" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50"><i class="fas fa-utensils text-lg text-gray-600"></i><span v-if="sidebarOpen" class="font-medium">Dishes</span></a></li>
            <li><a href="/inventory/combos" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors bg-orange-600 text-white"><i class="fas fa-layer-group text-lg text-white"></i><span v-if="sidebarOpen" class="font-medium">Combos</span></a></li>
            <li><a href="/inventory/grn" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50"><i class="fas fa-clipboard-check text-lg text-gray-600"></i><span v-if="sidebarOpen" class="font-medium">Receive Stock</span></a></li>
            <li><a href="/inventory/stocktake" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50"><i class="fas fa-clipboard-list text-lg text-gray-600"></i><span v-if="sidebarOpen" class="font-medium">Stocktake</span></a></li>
            <li><a href="/inventory/waste" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50"><i class="fas fa-trash-alt text-lg text-gray-600"></i><span v-if="sidebarOpen" class="font-medium">Waste</span></a></li>
            <li><a href="/reports/waste" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50"><i class="fas fa-chart-line text-lg text-gray-600"></i><span v-if="sidebarOpen" class="font-medium">Waste Reports</span></a></li>
          </ul>
        </div>
      </nav>

      <section class="flex-1 p-4 flex flex-col overflow-y-auto">
        <div class="glass-effect rounded-2xl p-4 mb-4 shadow-lg flex items-center gap-3">
          <button @click="openCreateModal" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg"><i class="fas fa-plus mr-2"></i> New Combo</button>
          <span class="text-sm text-gray-600">Create combos by bundling multiple products/ingredients.</span>
        </div>

        <!-- Combos List -->
        <div class="glass-effect rounded-2xl p-6 shadow-lg">
          <div v-if="loading" class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-3xl text-gray-400"></i>
            <p class="text-gray-500 mt-4">Loading combos...</p>
          </div>

          <div v-else-if="combos.length === 0" class="text-center py-12">
        <i class="fas fa-box-open text-5xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No combos yet</h3>
        <p class="text-gray-500 mb-6">Create your first combo to get started</p>
        <button
          @click="openCreateModal"
          class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700"
        >
          <i class="fas fa-plus mr-2"></i>
          Create Combo
        </button>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="combo in combos"
          :key="combo.id"
          class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-lg transition-shadow"
        >
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
              <h3 class="font-semibold text-gray-900">{{ combo.name }}</h3>
              <p v-if="combo.description" class="text-sm text-gray-600 mt-1 line-clamp-2">{{ combo.description }}</p>
              <p v-if="combo.category" class="text-xs text-gray-500 mt-1">
                <i class="fas fa-tag mr-1"></i>{{ combo.category.name }}
              </p>
            </div>
            <div class="ml-3">
              <span
                :class="combo.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                class="px-2 py-1 rounded-full text-xs font-medium"
              >
                {{ combo.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
          </div>

          <div class="mb-3">
            <p class="text-sm text-gray-500 mb-2">Contains {{ combo.items?.length || 0 }} items:</p>
            <div class="space-y-1">
              <div
                v-for="item in combo.items?.slice(0, 3)"
                :key="item.id"
                class="text-xs text-gray-600 flex items-center justify-between"
              >
                <span>{{ item.product?.name }}</span>
                <span class="font-medium">× {{ item.quantity }}</span>
              </div>
              <p v-if="combo.items && combo.items.length > 3" class="text-xs text-gray-500 italic">
                +{{ combo.items.length - 3 }} more...
              </p>
            </div>
          </div>

          <div class="pt-3 border-t border-gray-200 flex items-center justify-between">
            <p class="text-lg font-bold text-orange-600">JMD {{ formatPrice(combo.price) }}</p>
            <div class="flex gap-2">
              <button
                @click="editCombo(combo)"
                class="p-2 text-blue-600 hover:bg-blue-50 rounded"
                title="Edit"
              >
                <i class="fas fa-edit"></i>
              </button>
              <button
                @click="toggleActive(combo)"
                class="p-2 text-gray-600 hover:bg-gray-50 rounded"
                :title="combo.is_active ? 'Deactivate' : 'Activate'"
              >
                <i :class="combo.is_active ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
              <button
                @click="deleteCombo(combo)"
                class="p-2 text-red-600 hover:bg-red-50 rounded"
                title="Delete"
              >
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
        </div>
      </section>
    </div>

    <!-- Create/Edit Modal -->
      <div
        v-if="showModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        @click.self="closeModal"
      >
        <div class="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto">
          <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">
              {{ isEditing ? 'Edit Combo' : 'Create New Combo' }}
            </h2>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>

          <div class="p-6">
            <form @submit.prevent="saveCombo" class="space-y-4">
              <!-- Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Combo Name *</label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                  placeholder="e.g., Family Meal Deal"
                />
              </div>

              <!-- Description -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea
                  v-model="form.description"
                  rows="2"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                  placeholder="Describe this combo..."
                ></textarea>
              </div>

              <!-- Price & Category -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Price (JMD) *</label>
                  <input
                    v-model.number="form.price"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                    placeholder="0.00"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                  <select
                    v-model="form.category_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                  >
                    <option :value="null">No Category</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                  </select>
                </div>
              </div>

              <!-- Image URL -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                <input
                  v-model="form.image_url"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                  placeholder="https://..."
                />
              </div>

              <!-- Combo Items -->
              <div class="border-t border-gray-200 pt-4">
                <div class="flex items-center justify-between mb-3">
                  <label class="block text-sm font-medium text-gray-700">Combo Items *</label>
                  <button
                    type="button"
                    @click="addItem"
                    class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700"
                  >
                    <i class="fas fa-plus mr-1"></i>
                    Add Item
                  </button>
                </div>

                <div v-if="form.items.length === 0" class="text-center py-6 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                  <i class="fas fa-box-open text-3xl text-gray-300 mb-2"></i>
                  <p class="text-sm text-gray-500">No items added. Click "Add Item" to start.</p>
                </div>

                <div v-else class="space-y-2">
                  <div
                    v-for="(item, index) in form.items"
                    :key="index"
                    class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg"
                  >
                    <div class="flex-1">
                      <select
                        v-model="item.product_id"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                      >
                        <option :value="null">Select Product/Ingredient...</option>
                        <option v-for="product in availableProducts" :key="product.id" :value="product.id">
                          {{ product.name }} ({{ product.type }})
                        </option>
                      </select>
                    </div>
                    <div class="w-32">
                      <input
                        v-model.number="item.quantity"
                        type="number"
                        step="0.001"
                        min="0.001"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        placeholder="Qty"
                      />
                    </div>
                    <button
                      type="button"
                      @click="removeItem(index)"
                      class="p-2 text-red-600 hover:bg-red-50 rounded"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Active Status -->
              <div class="flex items-center">
                <input
                  v-model="form.is_active"
                  type="checkbox"
                  id="is_active"
                  class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500"
                />
                <label for="is_active" class="ml-2 text-sm text-gray-700">Active (visible in POS)</label>
              </div>

              <!-- Actions -->
              <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <button
                  type="button"
                  @click="closeModal"
                  class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="saving"
                  class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 disabled:opacity-50"
                >
                  <i v-if="saving" class="fas fa-spinner fa-spin mr-2"></i>
                  {{ saving ? 'Saving...' : (isEditing ? 'Update Combo' : 'Create Combo') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface Product {
  id: number
  name: string
  type: string
}

interface Category {
  id: number
  name: string
}

interface ComboItem {
  id?: number
  product_id: number | null
  quantity: number
  product?: Product
}

interface Combo {
  id?: number
  name: string
  description: string | null
  price: number
  price_cents?: number
  category_id: number | null
  image_url: string | null
  is_active: boolean
  items?: ComboItem[]
  category?: Category
}

const combos = ref<Combo[]>([])
const categories = ref<Category[]>([])
const availableProducts = ref<Product[]>([])
const loading = ref(true)
const showModal = ref(false)
const saving = ref(false)
const isEditing = ref(false)

const form = ref<Combo>({
  name: '',
  description: null,
  price: 0,
  category_id: null,
  image_url: null,
  is_active: true,
  items: []
})

onMounted(() => {
  loadCombos()
  loadCategories()
  loadProducts()
})

async function loadCombos() {
  loading.value = true
  try {
    const resp = await fetch('/api/combos', { credentials: 'same-origin' })
    if (resp.ok) {
      combos.value = await resp.json()
    }
  } catch (e) {
    console.error('Failed to load combos', e)
    alert('Failed to load combos')
  } finally {
    loading.value = false
  }
}

async function loadCategories() {
  try {
    const resp = await fetch('/api/categories', { credentials: 'same-origin' })
    if (resp.ok) {
      categories.value = await resp.json()
    }
  } catch (e) {
    console.error('Failed to load categories', e)
  }
}

async function loadProducts() {
  try {
    // Load both ingredients and products
    const [ingredientsResp, productsResp] = await Promise.all([
      fetch('/api/inventory/ingredients', { credentials: 'same-origin' }),
      fetch('/api/inventory/products', { credentials: 'same-origin' })
    ])

    const ingredients = ingredientsResp.ok ? await ingredientsResp.json() : []
    const products = productsResp.ok ? await productsResp.json() : []

    availableProducts.value = [...ingredients, ...products]
  } catch (e) {
    console.error('Failed to load products', e)
  }
}

function openCreateModal() {
  isEditing.value = false
  form.value = {
    name: '',
    description: null,
    price: 0,
    category_id: null,
    image_url: null,
    is_active: true,
    items: []
  }
  showModal.value = true
}

function editCombo(combo: Combo) {
  isEditing.value = true
  form.value = {
    id: combo.id,
    name: combo.name,
    description: combo.description,
    price: combo.price,
    category_id: combo.category_id,
    image_url: combo.image_url,
    is_active: combo.is_active,
    items: combo.items?.map(item => ({
      product_id: item.product_id,
      quantity: item.quantity
    })) || []
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  form.value = {
    name: '',
    description: null,
    price: 0,
    category_id: null,
    image_url: null,
    is_active: true,
    items: []
  }
}

function addItem() {
  form.value.items!.push({
    product_id: null,
    quantity: 1
  })
}

function removeItem(index: number) {
  form.value.items!.splice(index, 1)
}

async function saveCombo() {
  if (form.value.items!.length === 0) {
    alert('Please add at least one item to the combo')
    return
  }

  saving.value = true
  try {
    const method = isEditing.value ? 'PUT' : 'POST'
    const url = isEditing.value ? `/api/combos/${form.value.id}` : '/api/combos'

    const resp = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify(form.value)
    })

    if (!resp.ok) {
      const error = await resp.json()
      throw new Error(error.message || 'Failed to save combo')
    }

    await loadCombos()
    closeModal()
    alert(isEditing.value ? 'Combo updated successfully' : 'Combo created successfully')
  } catch (e: any) {
    console.error('Failed to save combo', e)
    alert(e.message || 'Failed to save combo')
  } finally {
    saving.value = false
  }
}

async function toggleActive(combo: Combo) {
  try {
    const resp = await fetch(`/api/combos/${combo.id}/toggle-active`, {
      method: 'POST',
      credentials: 'same-origin'
    })

    if (resp.ok) {
      await loadCombos()
    }
  } catch (e) {
    console.error('Failed to toggle status', e)
    alert('Failed to toggle status')
  }
}

async function deleteCombo(combo: Combo) {
  if (!confirm(`Are you sure you want to delete "${combo.name}"?`)) {
    return
  }

  try {
    const resp = await fetch(`/api/combos/${combo.id}`, {
      method: 'DELETE',
      credentials: 'same-origin'
    })

    if (resp.ok) {
      await loadCombos()
      alert('Combo deleted successfully')
    }
  } catch (e) {
    console.error('Failed to delete combo', e)
    alert('Failed to delete combo')
  }
}

function formatPrice(price: number): string {
  return price.toFixed(2)
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
