<template>
  <div class="min-h-screen gradient-bg">
    <!-- Header -->
    <header class="glass-effect shadow-lg">
      <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 bg-gradient-to-r from-orange-600 to-red-700 rounded-lg flex items-center justify-center">
            <i class="fas fa-file-invoice text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-800">Purchase Orders</h1>
            <p class="text-sm text-gray-600">Manage purchase orders to vendors</p>
          </div>
        </div>

        <button @click="openCreateModal" class="px-6 py-2 bg-gradient-to-r from-orange-600 to-red-700 hover:from-orange-700 hover:to-red-800 text-white rounded-lg font-medium transition-colors">
          <i class="fas fa-plus mr-2"></i>New Purchase Order
        </button>
      </div>
    </header>

    <div class="flex h-[calc(100vh-80px)]">
      <!-- Left nav -->
      <nav class="glass-effect m-4 rounded-2xl shadow-2xl w-20 flex flex-col">
        <div class="flex items-center justify-center px-3 py-3">
          <div class="w-9 h-9 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-fire text-white text-base"></i>
          </div>
        </div>

        <div class="px-2">
          <ul class="mt-1 space-y-1">
            <li>
              <a href="/pos" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-cash-register text-lg text-gray-600"></i>
              </a>
            </li>
            <li>
              <a href="/inventory" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-boxes-stacked text-lg text-gray-600"></i>
              </a>
            </li>
            <li>
              <a href="/inventory/purchase-orders" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors bg-orange-600 text-white">
                <i class="fas fa-file-invoice text-lg text-white"></i>
              </a>
            </li>
            <li>
              <a href="/reports" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-chart-line text-lg text-gray-600"></i>
              </a>
            </li>
          </ul>
        </div>
      </nav>

      <!-- Main Content -->
      <section class="flex-1 p-4 flex flex-col overflow-y-auto">
        <!-- Filters -->
        <div class="glass-effect rounded-2xl p-4 mb-4 shadow-lg">
          <div class="flex items-center gap-4 flex-wrap">
            <div class="flex items-center gap-2">
              <i class="fas fa-filter text-orange-600"></i>
              <span class="text-sm font-semibold text-gray-700">Filter by Status:</span>
            </div>

            <div class="flex items-center gap-2">
              <button @click="filterStatus = ''"
                      :class="filterStatus === '' ? 'bg-gradient-to-r from-orange-600 to-red-700 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                      class="px-3 py-1.5 rounded-lg text-sm font-medium border border-gray-300 transition-colors">
                All Statuses
              </button>
              <button @click="filterStatus = 'draft'"
                      :class="filterStatus === 'draft' ? 'bg-gradient-to-r from-orange-600 to-red-700 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                      class="px-3 py-1.5 rounded-lg text-sm font-medium border border-gray-300 transition-colors">
                Draft
              </button>
              <button @click="filterStatus = 'sent'"
                      :class="filterStatus === 'sent' ? 'bg-gradient-to-r from-orange-600 to-red-700 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                      class="px-3 py-1.5 rounded-lg text-sm font-medium border border-gray-300 transition-colors">
                Sent
              </button>
              <button @click="filterStatus = 'confirmed'"
                      :class="filterStatus === 'confirmed' ? 'bg-gradient-to-r from-orange-600 to-red-700 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                      class="px-3 py-1.5 rounded-lg text-sm font-medium border border-gray-300 transition-colors">
                Confirmed
              </button>
              <button @click="filterStatus = 'received'"
                      :class="filterStatus === 'received' ? 'bg-gradient-to-r from-orange-600 to-red-700 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                      class="px-3 py-1.5 rounded-lg text-sm font-medium border border-gray-300 transition-colors">
                Received
              </button>
              <button @click="filterStatus = 'cancelled'"
                      :class="filterStatus === 'cancelled' ? 'bg-gradient-to-r from-orange-600 to-red-700 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                      class="px-3 py-1.5 rounded-lg text-sm font-medium border border-gray-300 transition-colors">
                Cancelled
              </button>
            </div>
          </div>

          <!-- Active Filter Display -->
          <div v-if="filterStatus" class="mt-3 flex items-center gap-2 text-sm text-gray-700">
            <i class="fas fa-info-circle text-orange-600"></i>
            <span class="font-medium">Showing {{ filterStatus }} purchase orders</span>
            <span class="ml-2 px-2 py-0.5 bg-orange-100 text-orange-800 rounded-full text-xs font-semibold">
              {{ filteredOrders.length }} {{ filteredOrders.length === 1 ? 'order' : 'orders' }}
            </span>
          </div>
        </div>

        <!-- Purchase Orders Table -->
        <div class="glass-effect rounded-2xl shadow-lg overflow-hidden">
          <table class="min-w-full">
            <thead class="bg-gradient-to-r from-orange-100 to-red-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">PO #</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Vendor</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Total</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-if="filteredOrders.length === 0">
                <td colspan="6" class="px-6 py-12 text-center">
                  <i class="fas fa-file-invoice text-6xl text-gray-300 mb-4"></i>
                  <p class="text-xl font-medium text-gray-600">No purchase orders found</p>
                  <p class="text-gray-500 mt-2">Click "New Purchase Order" to create one</p>
                </td>
              </tr>
              <tr v-for="po in filteredOrders" :key="po.id" class="hover:bg-white hover:bg-opacity-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                  #{{ po.id }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                  {{ po.vendor?.name || 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                  {{ formatDate(po.ordered_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                  JMD {{ (po.total_cents / 100).toFixed(2) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="statusClass(po.status)" class="px-3 py-1 text-xs font-bold rounded-full">
                    {{ po.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <button @click="viewPO(po)" class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg text-xs font-medium mr-2 transition-colors">
                    <i class="fas fa-eye mr-1"></i>View
                  </button>
                  <button
                    v-if="po.status === 'draft'"
                    @click="editPO(po)"
                    class="px-3 py-1 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white rounded-lg text-xs font-medium mr-2 transition-colors"
                  >
                    <i class="fas fa-edit mr-1"></i>Edit
                  </button>
                  <button
                    v-if="po.status === 'draft'"
                    @click="deletePO(po.id)"
                    class="px-3 py-1 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-lg text-xs font-medium transition-colors"
                  >
                    <i class="fas fa-trash mr-1"></i>Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-[60] p-4" @click.self="closeModal">
      <div class="glass-effect rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-orange-600 to-red-700 text-white px-6 py-4 rounded-t-2xl">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <i class="fas fa-file-invoice text-2xl"></i>
              <h2 class="text-xl font-bold">
                {{ viewingPO ? 'View' : editingPO ? 'Edit' : 'Create' }} Purchase Order
                <span v-if="viewingPO" class="text-lg">#{{ viewingPO.id }}</span>
              </h2>
            </div>
            <button @click="closeModal" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2">
              <i class="fas fa-times text-lg"></i>
            </button>
          </div>
        </div>

        <form @submit.prevent="savePO" class="p-6">
          <!-- Vendor Selection -->
          <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Vendor *</label>
            <select v-model="form.vendor_id" :disabled="!!viewingPO" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 disabled:bg-gray-100">
              <option value="">Select Vendor</option>
              <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
                {{ vendor.name }}
              </option>
            </select>
          </div>

          <!-- Reference & Dates -->
          <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Reference</label>
              <input v-model="form.reference" :disabled="!!viewingPO" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 disabled:bg-gray-100" />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Order Date</label>
              <input v-model="form.ordered_at" :disabled="!!viewingPO" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 disabled:bg-gray-100" />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Due Date</label>
              <input v-model="form.due_at" :disabled="!!viewingPO" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 disabled:bg-gray-100" />
            </div>
          </div>

          <!-- Line Items -->
          <div class="mb-4">
            <div class="flex items-center justify-between mb-3">
              <label class="block text-sm font-semibold text-gray-700">Items *</label>
              <button
                v-if="!viewingPO"
                type="button"
                @click="addLine"
                class="px-3 py-1 bg-gradient-to-r from-orange-600 to-red-700 hover:from-orange-700 hover:to-red-800 text-white rounded-lg text-sm font-medium transition-colors"
              >
                <i class="fas fa-plus mr-1"></i>Add Item
              </button>
            </div>

            <div v-for="(line, index) in form.lines" :key="index" class="flex gap-3 mb-3">
              <!-- Product Autocomplete -->
              <div class="flex-1 relative">
                <input
                  v-model="line.product_search"
                  @input="searchProducts(line, index)"
                  @focus="openDropdown(line)"
                  @blur="closeDropdown(line)"
                  :disabled="!!viewingPO"
                  type="text"
                  placeholder="Search product/ingredient..."
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 disabled:bg-gray-100"
                />
                <div
                  v-show="line.showDropdown && line.searchResults && line.searchResults.length > 0"
                  class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto"
                >
                  <button
                    v-for="product in line.searchResults"
                    :key="product.id"
                    type="button"
                    @mousedown.prevent="selectProduct(line, product, index)"
                    class="w-full px-4 py-2 text-left hover:bg-orange-50 flex justify-between border-b last:border-b-0"
                  >
                    <span class="font-medium">{{ product.name }}</span>
                    <span class="text-gray-500 text-sm">{{ product.unit }}</span>
                  </button>
                </div>
                <div
                  v-show="line.showDropdown && line.product_search && line.product_search.length >= 2 && (!line.searchResults || line.searchResults.length === 0)"
                  class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl p-4 text-center text-gray-500"
                >
                  No products found
                </div>
              </div>

              <input
                v-model.number="line.qty"
                :disabled="!!viewingPO"
                type="number"
                step="0.01"
                placeholder="Qty"
                required
                class="w-24 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 disabled:bg-gray-100"
              />

              <input
                v-model.number="line.unit_cost"
                :disabled="!!viewingPO"
                type="number"
                step="0.01"
                placeholder="Unit Cost"
                required
                class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 disabled:bg-gray-100"
              />

              <div class="w-32 px-4 py-2 bg-gradient-to-r from-gray-100 to-gray-200 rounded-lg flex items-center justify-end font-semibold text-gray-900">
                JMD {{ ((line.qty || 0) * (line.unit_cost || 0)).toFixed(2) }}
              </div>

              <button
                v-if="!viewingPO"
                type="button"
                @click="removeLine(index)"
                class="px-3 py-2 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-lg transition-colors"
              >
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>

          <!-- Notes -->
          <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Notes</label>
            <textarea
              v-model="form.notes"
              :disabled="!!viewingPO"
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 disabled:bg-gray-100"
            ></textarea>
          </div>

          <!-- Total -->
          <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-lg p-4 mb-6">
            <div class="text-right">
              <span class="text-lg font-bold text-green-800">Total: JMD {{ calculateTotal().toFixed(2) }}</span>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-3">
            <button
              type="button"
              @click="closeModal"
              class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition-colors"
            >
              {{ viewingPO ? 'Close' : 'Cancel' }}
            </button>
            <button
              v-if="!viewingPO"
              type="submit"
              :disabled="saving"
              class="px-6 py-2 bg-gradient-to-r from-orange-600 to-red-700 hover:from-orange-700 hover:to-red-800 text-white rounded-lg font-medium disabled:opacity-50 transition-colors"
            >
              {{ saving ? 'Saving...' : 'Save Purchase Order' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Toast Notification -->
    <div class="notification glass-effect rounded-lg shadow-2xl p-4 w-80" :class="{ show: toastShow }">
      <div class="flex items-center">
        <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3" :class="toastBg">
          <i :class="toastIcon" class="text-white"></i>
        </div>
        <div class="flex-1">
          <p class="font-semibold text-gray-800">{{ toastTitle }}</p>
          <p class="text-sm text-gray-600">{{ toastMsg }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

const purchaseOrders = ref([])
const vendors = ref([])
const allProducts = ref([])
const filterStatus = ref('')
const showModal = ref(false)
const editingPO = ref(null)
const viewingPO = ref(null)
const saving = ref(false)

const form = ref({
  vendor_id: '',
  reference: '',
  ordered_at: new Date().toISOString().split('T')[0],
  due_at: '',
  notes: '',
  lines: []
})

const filteredOrders = computed(() => {
  if (!filterStatus.value) return purchaseOrders.value
  return purchaseOrders.value.filter(po => po.status === filterStatus.value)
})

async function loadPurchaseOrders() {
  try {
    const resp = await fetch('/api/purchase-orders')
    const data = await resp.json()
    purchaseOrders.value = data.purchase_orders || []
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load purchase orders', 'error')
  }
}

async function loadVendors() {
  try {
    const resp = await fetch('/api/vendors')
    const data = await resp.json()
    vendors.value = data.vendors || []
  } catch (e) {
    console.error(e)
  }
}

async function loadProducts() {
  try {
    // Load both ingredients and products
    const [ingredientsResp, productsResp] = await Promise.all([
      fetch('/api/inventory/ingredients'),
      fetch('/api/inventory/products')
    ])

    const ingredientsData = await ingredientsResp.json()
    const productsData = await productsResp.json()

    // Combine both arrays (API returns plain arrays, not objects)
    allProducts.value = [
      ...(Array.isArray(ingredientsData) ? ingredientsData : []),
      ...(Array.isArray(productsData) ? productsData : [])
    ]

    console.log('Loaded products:', allProducts.value.length)
  } catch (e) {
    console.error('Failed to load products:', e)
    toast('Error', 'Failed to load products', 'error')
  }
}

function openCreateModal() {
  editingPO.value = null
  form.value = {
    vendor_id: '',
    reference: '',
    ordered_at: new Date().toISOString().split('T')[0],
    due_at: '',
    notes: '',
    lines: []
  }
  addLine()
  showModal.value = true
}

function editPO(po) {
  editingPO.value = po
  form.value = {
    vendor_id: po.vendor_id,
    reference: po.reference || '',
    ordered_at: po.ordered_at?.split('T')[0] || '',
    due_at: po.due_at?.split('T')[0] || '',
    notes: po.notes || '',
    lines: po.lines.map(l => ({
      product_id: l.product_id,
      product_search: l.product?.name || '',
      qty: l.qty,
      unit_cost: l.unit_cost_cents / 100,
      searchResults: [],
      showDropdown: false
    }))
  }
  showModal.value = true
}

async function viewPO(po) {
  try {
    // Load full PO details from API
    const resp = await fetch(`/api/purchase-orders/${po.id}`)
    const data = await resp.json()
    const fullPO = data.purchase_order

    viewingPO.value = fullPO
    editingPO.value = null

    // Populate form with PO data for viewing
    form.value = {
      vendor_id: fullPO.vendor_id,
      reference: fullPO.reference || '',
      ordered_at: fullPO.ordered_at?.split('T')[0] || '',
      due_at: fullPO.due_at?.split('T')[0] || '',
      notes: fullPO.notes || '',
      lines: (fullPO.lines || []).map(line => ({
        product_id: line.product_id,
        product_name: line.product?.name || '',
        product_search: line.product?.name || '',
        qty: line.qty,
        unit_cost: line.unit_cost_cents / 100,
        searchResults: [],
        showDropdown: false
      }))
    }

    showModal.value = true
  } catch (e) {
    console.error('Failed to load PO:', e)
    toast('Error', 'Failed to load purchase order', 'error')
  }
}

function addLine() {
  form.value.lines.push({
    product_id: null,
    product_search: '',
    qty: 1,
    unit_cost: 0,
    searchResults: [],
    showDropdown: false
  })
}

function removeLine(index) {
  form.value.lines.splice(index, 1)
}

function searchProducts(line, index) {
  const query = (line.product_search || '').toLowerCase().trim()

  console.log('Searching for:', query)
  console.log('All products count:', allProducts.value.length)

  if (query.length < 2) {
    line.searchResults = []
    line.showDropdown = false
    return
  }

  const results = allProducts.value.filter(p =>
    p.name.toLowerCase().includes(query)
  ).slice(0, 15)

  console.log('Search results:', results.length)

  line.searchResults = results
  line.showDropdown = true

  // Force reactivity
  form.value.lines[index] = { ...line }
}

function openDropdown(line) {
  if (line.product_search && line.product_search.length >= 2) {
    line.showDropdown = true
  }
}

function closeDropdown(line) {
  // Delay to allow click event to fire
  setTimeout(() => {
    line.showDropdown = false
  }, 200)
}

function selectProduct(line, product, index) {
  console.log('Selected product:', product.name)

  line.product_id = product.id
  line.product_search = product.name
  line.searchResults = []
  line.showDropdown = false

  // Force reactivity
  form.value.lines[index] = { ...line }
}

function calculateTotal() {
  return form.value.lines.reduce((sum, line) => {
    return sum + ((line.qty || 0) * (line.unit_cost || 0))
  }, 0)
}

async function savePO() {
  if (!form.value.vendor_id) {
    toast('Error', 'Please select a vendor', 'error')
    return
  }

  if (form.value.lines.length === 0 || !form.value.lines.every(l => l.product_id)) {
    toast('Error', 'Please add at least one valid item', 'error')
    return
  }

  saving.value = true
  try {
    const payload = {
      vendor_id: form.value.vendor_id,
      reference: form.value.reference,
      ordered_at: form.value.ordered_at,
      due_at: form.value.due_at,
      notes: form.value.notes,
      lines: form.value.lines.map(l => ({
        product_id: l.product_id,
        qty: l.qty,
        unit_cost_cents: Math.round(l.unit_cost * 100)
      }))
    }

    const url = editingPO.value
      ? `/api/purchase-orders/${editingPO.value.id}`
      : '/api/purchase-orders'

    const method = editingPO.value ? 'PUT' : 'POST'

    const resp = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })

    if (!resp.ok) throw new Error('Failed to save')

    toast('Success', 'Purchase order saved successfully', 'success')
    closeModal()
    loadPurchaseOrders()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to save purchase order', 'error')
  } finally {
    saving.value = false
  }
}

async function deletePO(id) {
  if (!confirm('Are you sure you want to delete this purchase order?')) return

  try {
    const resp = await fetch(`/api/purchase-orders/${id}`, { method: 'DELETE' })
    if (!resp.ok) throw new Error('Failed to delete')

    toast('Success', 'Purchase order deleted', 'success')
    loadPurchaseOrders()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to delete purchase order', 'error')
  }
}

function closeModal() {
  showModal.value = false
  editingPO.value = null
  viewingPO.value = null
}

function formatDate(date) {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
}

function statusClass(status) {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    sent: 'bg-blue-100 text-blue-800',
    confirmed: 'bg-green-100 text-green-800',
    received: 'bg-purple-100 text-purple-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
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

onMounted(() => {
  loadPurchaseOrders()
  loadVendors()
  loadProducts()
})
</script>

<style scoped>
.gradient-bg {
  background: linear-gradient(135deg, #ea580c 0%, #dc2626 100%);
  background-attachment: fixed;
}

.glass-effect {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

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
