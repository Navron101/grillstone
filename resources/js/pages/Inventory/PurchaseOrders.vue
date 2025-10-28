<template>
  <AppLayout title="Purchase Orders">
    <div class="bg-white shadow-sm border-b border-gray-200 -mt-6 -mx-6 px-6 py-4 mb-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-file-invoice text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-900">Purchase Orders</h1>
            <p class="text-sm text-gray-600">Manage purchase orders to vendors</p>
          </div>
        </div>
        <button
          @click="openCreateModal"
          class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg font-medium"
        >
          <i class="fas fa-plus mr-2"></i>New Purchase Order
        </button>
      </div>
    </div>

    <div class="max-w-7xl mx-auto">
      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex gap-4">
          <select v-model="filterStatus" class="px-4 py-2 border rounded-lg">
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="sent">Sent</option>
            <option value="confirmed">Confirmed</option>
            <option value="received">Received</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
      </div>

      <!-- Purchase Orders List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PO #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="po in filteredOrders" :key="po.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                #{{ po.id }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ po.vendor?.name || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(po.ordered_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                JMD {{ (po.total_cents / 100).toFixed(2) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="statusClass(po.status)" class="px-2 py-1 text-xs rounded-full">
                  {{ po.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <button @click="viewPO(po)" class="text-blue-600 hover:text-blue-900 mr-3">
                  View
                </button>
                <button
                  v-if="po.status === 'draft'"
                  @click="editPO(po)"
                  class="text-orange-600 hover:text-orange-900 mr-3"
                >
                  Edit
                </button>
                <button
                  v-if="po.status === 'draft'"
                  @click="deletePO(po.id)"
                  class="text-red-600 hover:text-red-900"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold">
              {{ viewingPO ? 'View' : editingPO ? 'Edit' : 'Create' }} Purchase Order
              <span v-if="viewingPO" class="text-lg text-gray-600">#{{ viewingPO.id }}</span>
            </h2>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>

          <form @submit.prevent="savePO">
            <!-- Vendor Selection -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Vendor *</label>
              <select v-model="form.vendor_id" :disabled="!!viewingPO" required class="w-full px-4 py-2 border rounded-lg disabled:bg-gray-100">
                <option value="">Select Vendor</option>
                <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
                  {{ vendor.name }}
                </option>
              </select>
            </div>

            <!-- Reference & Dates -->
            <div class="grid grid-cols-3 gap-4 mb-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Reference</label>
                <input v-model="form.reference" :disabled="!!viewingPO" type="text" class="w-full px-4 py-2 border rounded-lg disabled:bg-gray-100" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Order Date</label>
                <input v-model="form.ordered_at" :disabled="!!viewingPO" type="date" class="w-full px-4 py-2 border rounded-lg disabled:bg-gray-100" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                <input v-model="form.due_at" :disabled="!!viewingPO" type="date" class="w-full px-4 py-2 border rounded-lg disabled:bg-gray-100" />
              </div>
            </div>

            <!-- Line Items -->
            <div class="mb-4">
              <div class="flex items-center justify-between mb-3">
                <label class="block text-sm font-medium text-gray-700">Items *</label>
                <button
                  v-if="!viewingPO"
                  type="button"
                  @click="addLine"
                  class="text-orange-600 hover:text-orange-700 text-sm font-medium"
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
                    class="w-full px-4 py-2 border rounded-lg disabled:bg-gray-100"
                  />
                  <div
                    v-show="line.showDropdown && line.searchResults && line.searchResults.length > 0"
                    class="absolute z-50 w-full mt-1 bg-white border rounded-lg shadow-xl max-h-60 overflow-y-auto"
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
                    class="absolute z-50 w-full mt-1 bg-white border rounded-lg shadow-xl p-4 text-center text-gray-500"
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
                  class="w-24 px-4 py-2 border rounded-lg disabled:bg-gray-100"
                />

                <input
                  v-model.number="line.unit_cost"
                  :disabled="!!viewingPO"
                  type="number"
                  step="0.01"
                  placeholder="Unit Cost"
                  required
                  class="w-32 px-4 py-2 border rounded-lg disabled:bg-gray-100"
                />

                <div class="w-32 px-4 py-2 bg-gray-100 rounded-lg flex items-center justify-end">
                  JMD {{ ((line.qty || 0) * (line.unit_cost || 0)).toFixed(2) }}
                </div>

                <button
                  v-if="!viewingPO"
                  type="button"
                  @click="removeLine(index)"
                  class="text-red-600 hover:text-red-900"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>

            <!-- Notes -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
              <textarea
                v-model="form.notes"
                :disabled="!!viewingPO"
                rows="3"
                class="w-full px-4 py-2 border rounded-lg disabled:bg-gray-100"
              ></textarea>
            </div>

            <!-- Total -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
              <div class="text-right">
                <span class="text-lg font-semibold">Total: JMD {{ calculateTotal().toFixed(2) }}</span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3">
              <button
                type="button"
                @click="closeModal"
                class="px-6 py-2 border rounded-lg hover:bg-gray-50"
              >
                {{ viewingPO ? 'Close' : 'Cancel' }}
              </button>
              <button
                v-if="!viewingPO"
                type="submit"
                :disabled="saving"
                class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg disabled:opacity-50"
              >
                {{ saving ? 'Saving...' : 'Save Purchase Order' }}
              </button>
            </div>
          </form>
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
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'

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
