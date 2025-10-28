<template>
  <AppLayout title="Invoices">
    <div class="bg-white shadow-sm border-b border-gray-200 -mt-6 -mx-6 px-6 py-4 mb-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-receipt text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-900">Invoices</h1>
            <p class="text-sm text-gray-600">Manage vendor invoices</p>
          </div>
        </div>
        <div class="flex gap-3">
          <a
            href="/inventory/invoices/scan"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2"
          >
            <i class="fas fa-camera"></i>
            Scan Invoice
          </a>
          <button
            @click="openCreateModal"
            class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg font-medium"
          >
            <i class="fas fa-plus mr-2"></i>New Invoice
          </button>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto">

      <!-- Invoices List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="invoices.length === 0">
              <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                No invoices found. Click "New Invoice" to create one.
              </td>
            </tr>
            <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ invoice.invoice_number }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">{{ invoice.supplier_name || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(invoice.invoice_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                JMD {{ (invoice.total_amount_cents / 100).toFixed(2) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="statusClass(invoice.status)" class="px-2 py-1 text-xs rounded-full">
                  {{ invoice.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <button @click="editInvoice(invoice)" class="text-orange-600 hover:text-orange-900 mr-3">
                  Edit
                </button>
                <button
                  v-if="invoice.status === 'pending'"
                  @click="approveInvoice(invoice.id)"
                  class="text-green-600 hover:text-green-900 mr-3"
                >
                  Approve
                </button>
                <button
                  v-if="invoice.status === 'approved' || invoice.status === 'pending'"
                  @click="markAsPaid(invoice.id)"
                  class="text-blue-600 hover:text-blue-900 mr-3"
                >
                  Mark Paid
                </button>
                <button @click="deleteInvoice(invoice.id)" class="text-red-600 hover:text-red-900">
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
            <h2 class="text-2xl font-bold">{{ editingInvoice ? 'Edit' : 'Create' }} Invoice</h2>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>

          <form @submit.prevent="saveInvoice">
            <div class="grid grid-cols-3 gap-4 mb-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Number *</label>
                <input v-model="form.invoice_number" required class="w-full px-4 py-2 border rounded-lg" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Vendor *</label>
                <select v-model.number="form.vendor_id" required class="w-full px-4 py-2 border rounded-lg">
                  <option value="">Select vendor...</option>
                  <option v-for="vendor in vendors" :key="vendor.id" :value="vendor.id">
                    {{ vendor.name }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Date *</label>
                <input v-model="form.invoice_date" type="date" required class="w-full px-4 py-2 border rounded-lg" />
              </div>
            </div>

            <!-- Items -->
            <div class="mb-4">
              <div class="flex items-center justify-between mb-3">
                <label class="block text-sm font-medium text-gray-700">Items *</label>
                <button type="button" @click="addItem" class="text-orange-600 text-sm font-medium">
                  <i class="fas fa-plus mr-1"></i>Add Item
                </button>
              </div>

              <div v-for="(item, index) in form.items" :key="index" class="flex gap-3 mb-3">
                <div class="flex-1 relative">
                  <input
                    v-model="item.product_search"
                    @input="searchProducts(item, index)"
                    @focus="openDropdown(item)"
                    @blur="closeDropdown(item)"
                    placeholder="Search product..."
                    class="w-full px-4 py-2 border rounded-lg"
                  />
                  <div
                    v-if="item.showDropdown && (item.searchResults.length || item.product_search?.length >= 2)"
                    class="absolute z-50 w-full mt-1 bg-white border rounded-lg shadow-xl max-h-60 overflow-y-auto"
                  >
                    <button
                      v-for="product in item.searchResults"
                      :key="product.id"
                      type="button"
                      @mousedown.prevent="selectProduct(item, product, index)"
                      class="w-full px-4 py-2 text-left hover:bg-orange-50 transition-colors"
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
                  class="w-24 px-4 py-2 border rounded-lg"
                />

                <input
                  v-model.number="item.unit_price"
                  type="number"
                  step="0.01"
                  placeholder="Price"
                  required
                  class="w-32 px-4 py-2 border rounded-lg"
                />

                <div class="w-32 px-4 py-2 bg-gray-100 rounded-lg flex items-center justify-end">
                  JMD {{ ((item.qty || 0) * (item.unit_price || 0)).toFixed(2) }}
                </div>

                <button type="button" @click="removeItem(index)" class="text-red-600">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>

            <!-- Notes -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
              <textarea v-model="form.notes" rows="3" class="w-full px-4 py-2 border rounded-lg"></textarea>
            </div>

            <!-- Total -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
              <div class="text-right">
                <span class="text-lg font-semibold">Total: JMD {{ calculateTotal().toFixed(2) }}</span>
              </div>
            </div>

            <div class="flex justify-end gap-3">
              <button type="button" @click="closeModal" class="px-6 py-2 border rounded-lg">Cancel</button>
              <button
                type="submit"
                :disabled="saving"
                class="bg-orange-600 text-white px-6 py-2 rounded-lg disabled:opacity-50"
              >
                {{ saving ? 'Saving...' : 'Save Invoice' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Toast -->
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
import { ref, onMounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'

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
