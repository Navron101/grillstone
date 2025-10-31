<template>
  <div class="min-h-screen gradient-bg">
    <!-- Header -->
    <header class="glass-effect shadow-lg">
      <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 bg-gradient-to-r from-orange-600 to-red-700 rounded-lg flex items-center justify-center">
            <i class="fas fa-receipt text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-800">Invoices</h1>
            <p class="text-sm text-gray-600">Manage vendor invoices</p>
          </div>
        </div>

        <div class="flex items-center space-x-3">
          <a
            href="/inventory/invoices/scan"
            class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-medium flex items-center gap-2 transition-colors"
          >
            <i class="fas fa-camera"></i>
            Scan Invoice
          </a>
          <button
            @click="openCreateModal"
            class="px-6 py-2 bg-gradient-to-r from-orange-600 to-red-700 hover:from-orange-700 hover:to-red-800 text-white rounded-lg font-medium transition-colors"
          >
            <i class="fas fa-plus mr-2"></i>New Invoice
          </button>
        </div>
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
              <a href="/inventory/invoices" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors bg-orange-600 text-white">
                <i class="fas fa-receipt text-lg text-white"></i>
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
        <!-- Invoices List -->
        <div class="glass-effect rounded-2xl shadow-lg overflow-hidden">
          <table class="min-w-full">
            <thead class="bg-gradient-to-r from-orange-100 to-red-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Invoice #</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Supplier</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-if="invoices.length === 0">
                <td colspan="6" class="px-6 py-12 text-center">
                  <i class="fas fa-receipt text-6xl text-gray-300 mb-4"></i>
                  <p class="text-xl font-medium text-gray-600">No invoices found</p>
                  <p class="text-gray-500 mt-2">Click "New Invoice" to create one</p>
                </td>
              </tr>
              <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-white hover:bg-opacity-50 transition-colors">
                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ invoice.invoice_number }}</td>
                <td class="px-6 py-4 text-sm text-gray-700">{{ invoice.supplier_name || '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-700">
                  {{ formatDate(invoice.invoice_date) }}
                </td>
                <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                  JMD {{ (invoice.total_amount_cents / 100).toFixed(2) }}
                </td>
                <td class="px-6 py-4">
                  <span :class="statusClass(invoice.status)" class="px-3 py-1 text-xs font-bold rounded-full">
                    {{ invoice.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm">
                  <button @click="editInvoice(invoice)" class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg text-xs font-medium mr-2 transition-colors">
                    <i class="fas fa-edit mr-1"></i>Edit
                  </button>
                  <button
                    v-if="invoice.status === 'pending'"
                    @click="approveInvoice(invoice.id)"
                    class="px-3 py-1 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg text-xs font-medium mr-2 transition-colors"
                  >
                    <i class="fas fa-check mr-1"></i>Approve
                  </button>
                  <button
                    v-if="invoice.status === 'approved' || invoice.status === 'pending'"
                    @click="markAsPaid(invoice.id)"
                    class="px-3 py-1 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white rounded-lg text-xs font-medium mr-2 transition-colors"
                  >
                    <i class="fas fa-dollar-sign mr-1"></i>Mark Paid
                  </button>
                  <button @click="deleteInvoice(invoice.id)" class="px-3 py-1 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-lg text-xs font-medium transition-colors">
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
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-[60] p-4">
      <div class="glass-effect rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-orange-600 to-red-700 text-white px-6 py-4 rounded-t-2xl">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold">{{ editingInvoice ? 'Edit' : 'Create' }} Invoice</h2>
            <button @click="closeModal" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2">
              <i class="fas fa-times text-lg"></i>
            </button>
          </div>
        </div>

        <form @submit.prevent="saveInvoice" class="p-6">
          <div class="grid grid-cols-3 gap-4 mb-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Invoice Number *</label>
              <input v-model="form.invoice_number" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500" />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Vendor *</label>
              <select v-model.number="form.vendor_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                <option value="">Select vendor...</option>
                <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
                  {{ vendor.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Invoice Date *</label>
              <input v-model="form.invoice_date" type="date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500" />
            </div>
          </div>

          <!-- Items -->
          <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
              <label class="block text-sm font-semibold text-gray-700">Items *</label>
              <button type="button" @click="addItem" class="px-3 py-1 bg-gradient-to-r from-orange-600 to-red-700 hover:from-orange-700 hover:to-red-800 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-plus mr-1"></i>Add Item
              </button>
            </div>

            <div v-for="(item, index) in form.items" :key="index" class="flex gap-3 mb-3 items-start">
              <div class="flex-1 relative">
                <input
                  v-model="item.product_search"
                  @input="searchProducts(item, index)"
                  @focus="openDropdown(item)"
                  @blur="closeDropdown(item)"
                  placeholder="Search product..."
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                />
                <div
                  v-if="item.showDropdown && (item.searchResults.length || item.product_search?.length >= 2)"
                  class="absolute z-50 w-full mt-1 glass-effect border border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto"
                >
                  <button
                    v-for="product in item.searchResults"
                    :key="product.id"
                    type="button"
                    @mousedown.prevent="selectProduct(item, product, index)"
                    class="w-full px-4 py-2 text-left hover:bg-orange-50 transition-colors text-sm"
                  >
                    {{ product.name }}
                  </button>
                  <div
                    v-if="!item.searchResults.length && item.product_search?.length >= 2"
                    class="px-4 py-3 text-sm text-gray-500 text-center"
                  >
                    No products found
                  </div>
                </div>
              </div>

              <input
                v-model.number="item.qty"
                type="number"
                step="0.01"
                placeholder="Qty"
                required
                class="w-24 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
              />

              <input
                v-model.number="item.unit_price"
                type="number"
                step="0.01"
                placeholder="Price"
                required
                class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
              />

              <div class="w-32 px-4 py-2 bg-gradient-to-r from-gray-100 to-gray-200 rounded-lg flex items-center justify-end font-semibold text-sm text-gray-900">
                JMD {{ ((item.qty || 0) * (item.unit_price || 0)).toFixed(2) }}
              </div>

              <button type="button" @click="removeItem(index)" class="px-3 py-2 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-lg transition-colors">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>

          <!-- Notes -->
          <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Notes</label>
            <textarea v-model="form.notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"></textarea>
          </div>

          <!-- Total -->
          <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-lg p-4 mb-6">
            <div class="text-right">
              <span class="text-lg font-bold text-green-900">Total: JMD {{ calculateTotal().toFixed(2) }}</span>
            </div>
          </div>

          <div class="flex justify-end gap-3">
            <button type="button" @click="closeModal" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition-colors">Cancel</button>
            <button
              type="submit"
              :disabled="saving"
              class="px-6 py-2 bg-gradient-to-r from-orange-600 to-red-700 hover:from-orange-700 hover:to-red-800 text-white rounded-lg font-medium disabled:opacity-50 transition-colors"
            >
              {{ saving ? 'Saving...' : 'Save Invoice' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Toast -->
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
import { ref, onMounted } from 'vue'

const invoices = ref([])
const allProducts = ref([])
const vendors = ref([])
const showModal = ref(false)
const editingInvoice = ref(null)
const saving = ref(false)

const form = ref({
  invoice_number: '',
  vendor_id: null,
  supplier_name: '',
  invoice_date: new Date().toISOString().split('T')[0],
  notes: '',
  items: []
})

async function loadInvoices() {
  try {
    const resp = await fetch('/api/invoices-manual')
    const data = await resp.json()
    invoices.value = data.invoices || []
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load invoices', 'error')
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

async function loadVendors() {
  try {
    const resp = await fetch('/api/vendors')
    const data = await resp.json()
    vendors.value = data.vendors || []
  } catch (e) {
    console.error('Failed to load vendors:', e)
    toast('Error', 'Failed to load vendors', 'error')
  }
}

function openCreateModal() {
  editingInvoice.value = null
  form.value = {
    invoice_number: '',
    vendor_id: null,
    supplier_name: '',
    invoice_date: new Date().toISOString().split('T')[0],
    notes: '',
    items: []
  }
  addItem()
  showModal.value = true
}

function editInvoice(invoice) {
  editingInvoice.value = invoice
  // Find vendor by supplier_name for backwards compatibility
  const matchingVendor = vendors.value.find(v => v.name === invoice.supplier_name)

  form.value = {
    invoice_number: invoice.invoice_number,
    vendor_id: matchingVendor ? matchingVendor.id : null,
    supplier_name: invoice.supplier_name,
    invoice_date: invoice.invoice_date?.split('T')[0] || '',
    notes: invoice.notes || '',
    items: (invoice.extracted_items || []).map(i => ({
      product_id: i.product_id,
      product_name: i.product_name,
      product_search: i.product_name,
      qty: i.qty,
      unit_price: i.unit_price_cents / 100,
      searchResults: [],
      showDropdown: false
    }))
  }
  showModal.value = true
}

function addItem() {
  form.value.items.push({
    product_id: null,
    product_name: '',
    product_search: '',
    qty: 1,
    unit_price: 0,
    searchResults: [],
    showDropdown: false
  })
}

function removeItem(index) {
  form.value.items.splice(index, 1)
}

function searchProducts(item, index) {
  const query = (item.product_search || '').toLowerCase().trim()
  console.log('Searching for:', query)
  console.log('All products count:', allProducts.value.length)

  if (query.length < 2) {
    item.searchResults = []
    item.showDropdown = false
    return
  }

  const results = allProducts.value.filter(p =>
    p.name.toLowerCase().includes(query)
  ).slice(0, 15)

  console.log('Search results:', results.length)

  item.searchResults = results
  item.showDropdown = true
  form.value.items[index] = { ...item } // Force reactivity
}

function openDropdown(item) {
  if (item.product_search && item.product_search.length >= 2) {
    item.showDropdown = true
  }
}

function closeDropdown(item) {
  // Delay to allow click event to fire first
  setTimeout(() => {
    item.showDropdown = false
  }, 200)
}

function selectProduct(item, product, index) {
  console.log('Selected product:', product.name)
  item.product_id = product.id
  item.product_name = product.name
  item.product_search = product.name
  item.searchResults = []
  item.showDropdown = false
  form.value.items[index] = { ...item } // Force reactivity
}

function calculateTotal() {
  return form.value.items.reduce((sum, item) => {
    return sum + ((item.qty || 0) * (item.unit_price || 0))
  }, 0)
}

async function saveInvoice() {
  if (!form.value.vendor_id) {
    toast('Error', 'Please select a vendor', 'error')
    return
  }

  if (form.value.items.length === 0 || !form.value.items.every(i => i.product_id)) {
    toast('Error', 'Please add at least one valid item', 'error')
    return
  }

  saving.value = true
  try {
    // Get vendor name from selected vendor
    // Use == instead of === to handle string/number comparison
    const selectedVendor = vendors.value.find(v => v.id == form.value.vendor_id)
    const supplierName = selectedVendor ? selectedVendor.name : ''

    if (!supplierName) {
      console.error('No supplier name found for vendor_id:', form.value.vendor_id)
      console.error('Available vendors:', vendors.value)
      toast('Error', 'Could not find vendor. Please select a vendor again.', 'error')
      saving.value = false
      return
    }

    const payload = {
      invoice_number: form.value.invoice_number,
      supplier_name: supplierName,
      invoice_date: form.value.invoice_date,
      notes: form.value.notes,
      total_amount_cents: Math.round(calculateTotal() * 100),
      items: form.value.items.map(i => ({
        product_id: i.product_id,
        product_name: i.product_name,
        qty: i.qty,
        unit_price_cents: Math.round(i.unit_price * 100)
      }))
    }

    console.log('=== INVOICE SAVE DEBUG ===')
    console.log('Payload:', JSON.stringify(payload, null, 2))
    console.log('Vendor ID:', form.value.vendor_id)
    console.log('Selected Vendor:', selectedVendor)
    console.log('Supplier Name:', supplierName)

    const url = editingInvoice.value
      ? `/api/invoices-manual/${editingInvoice.value.id}`
      : '/api/invoices-manual'

    const method = editingInvoice.value ? 'PUT' : 'POST'

    console.log('Request URL:', url)
    console.log('Request Method:', method)

    const resp = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })

    console.log('Response Status:', resp.status)
    console.log('Response OK:', resp.ok)

    if (!resp.ok) {
      const responseText = await resp.text()
      console.error('Response Text:', responseText)

      let errorData = {}
      try {
        errorData = JSON.parse(responseText)
      } catch (e) {
        console.error('Could not parse error response as JSON')
      }

      console.error('Error Data:', errorData)
      console.error('Validation Errors:', errorData.errors)

      const errorMsg = errorData.message || errorData.error || `Server returned ${resp.status}`
      throw new Error(errorMsg)
    }

    toast('Success', 'Invoice saved successfully', 'success')
    closeModal()
    loadInvoices()
  } catch (e) {
    console.error('Save error:', e)
    toast('Error', e.message || 'Failed to save invoice', 'error')
  } finally {
    saving.value = false
  }
}

async function approveInvoice(id) {
  try {
    const resp = await fetch(`/api/invoices-manual/${id}/approve`, { method: 'POST' })
    if (!resp.ok) throw new Error('Failed')

    toast('Success', 'Invoice approved', 'success')
    loadInvoices()
  } catch (e) {
    toast('Error', 'Failed to approve invoice', 'error')
  }
}

async function markAsPaid(id) {
  try {
    const resp = await fetch(`/api/invoices-manual/${id}/mark-paid`, { method: 'POST' })
    if (!resp.ok) throw new Error('Failed')

    toast('Success', 'Invoice marked as paid', 'success')
    loadInvoices()
  } catch (e) {
    toast('Error', 'Failed to mark invoice as paid', 'error')
  }
}

async function deleteInvoice(id) {
  if (!confirm('Delete this invoice?')) return

  try {
    const resp = await fetch(`/api/invoices-manual/${id}`, { method: 'DELETE' })
    if (!resp.ok) throw new Error('Failed')

    toast('Success', 'Invoice deleted', 'success')
    loadInvoices()
  } catch (e) {
    toast('Error', 'Failed to delete', 'error')
  }
}

function closeModal() {
  showModal.value = false
  editingInvoice.value = null
}

function formatDate(date) {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
}

function statusClass(status) {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    processed: 'bg-blue-100 text-blue-800',
    paid: 'bg-purple-100 text-purple-800'
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
  loadInvoices()
  loadProducts()
  loadVendors()
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
