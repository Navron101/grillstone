<template>
  <div class="min-h-screen gradient-bg">
    <!-- Header -->
    <header class="glass-effect shadow-lg">
      <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-file-invoice text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-800">Invoice Scanner</h1>
            <p class="text-sm text-gray-600">Upload & process supplier invoices with AI</p>
          </div>
        </div>

        <div class="flex items-center space-x-3">
          <button @click="refreshInvoices" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg" title="Refresh">
            <i class="fas fa-sync-alt text-lg"></i>
          </button>
          <a href="/inventory" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg" title="Back to Inventory">
            <i class="fas fa-arrow-left text-lg"></i>
          </a>
        </div>
      </div>
    </header>

    <div class="max-w-7xl mx-auto px-6 py-8">
      <!-- Upload Section -->
      <div class="glass-effect rounded-2xl p-8 mb-6 shadow-lg">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
          <i class="fas fa-cloud-upload-alt text-purple-600"></i>
          Upload Invoice
        </h2>

        <div class="border-2 border-dashed border-gray-300 rounded-xl p-12 text-center hover:border-purple-500 transition-colors cursor-pointer"
             @click="$refs.fileInput.click()"
             @dragover.prevent="dragOver = true"
             @dragleave.prevent="dragOver = false"
             @drop.prevent="handleDrop"
             :class="dragOver ? 'border-purple-500 bg-purple-50' : ''">
          <input ref="fileInput" type="file" accept="image/*,.pdf" @change="handleFileSelect" class="hidden">

          <div v-if="!selectedFile">
            <i class="fas fa-file-upload text-6xl text-gray-400 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Drop invoice here or click to browse</h3>
            <p class="text-gray-500">Supports JPG, PNG, PDF (max 10MB)</p>
          </div>

          <div v-else class="space-y-4">
            <i class="fas fa-file-image text-6xl text-purple-600 mb-4"></i>
            <p class="text-lg font-medium text-gray-800">{{ selectedFile.name }}</p>
            <p class="text-sm text-gray-600">{{ formatFileSize(selectedFile.size) }}</p>

            <div class="flex gap-3 justify-center mt-4">
              <button @click.stop="uploadInvoice" :disabled="uploading" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-lg font-semibold disabled:opacity-50">
                <i v-if="uploading" class="fas fa-spinner fa-spin mr-2"></i>
                <i v-else class="fas fa-magic mr-2"></i>
                {{ uploading ? 'Processing...' : 'Process with AI' }}
              </button>
              <button @click.stop="clearFile" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-semibold">
                <i class="fas fa-times mr-2"></i>Cancel
              </button>
            </div>
          </div>
        </div>

        <div v-if="uploadProgress" class="mt-4 bg-purple-50 border border-purple-200 rounded-lg p-4">
          <div class="flex items-center gap-3">
            <i class="fas fa-circle-notch fa-spin text-purple-600 text-2xl"></i>
            <div class="flex-1">
              <p class="font-semibold text-purple-900">{{ uploadProgress }}</p>
              <p class="text-sm text-purple-700">AI is analyzing your invoice...</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Invoices List -->
      <div class="glass-effect rounded-2xl p-8 shadow-lg">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
          <i class="fas fa-list text-indigo-600"></i>
          Recent Invoices
        </h2>

        <div v-if="loading" class="text-center py-12">
          <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
          <p class="text-gray-600">Loading invoices...</p>
        </div>

        <div v-else-if="invoices.length === 0" class="text-center py-12">
          <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
          <p class="text-gray-600">No invoices uploaded yet</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b-2 border-gray-200">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Invoice #</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Supplier</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Date</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Amount</th>
                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Items</th>
                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Status</th>
                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                  <span class="font-medium text-gray-900">{{ invoice.invoice_number || 'N/A' }}</span>
                </td>
                <td class="px-6 py-4 text-gray-700">{{ invoice.supplier_name || 'N/A' }}</td>
                <td class="px-6 py-4 text-gray-700">{{ formatDate(invoice.invoice_date) }}</td>
                <td class="px-6 py-4 text-right font-semibold text-gray-900">
                  {{ invoice.total_amount_cents ? formatMoney(invoice.total_amount_cents) : 'N/A' }}
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                    {{ invoice.items_count }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="px-3 py-1 rounded-full text-sm font-medium" :class="getStatusClass(invoice.status)">
                    {{ getStatusLabel(invoice.status) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-center gap-2">
                    <button @click="viewInvoice(invoice)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="View & Edit">
                      <i class="fas fa-eye"></i>
                    </button>
                    <a :href="invoice.file_url" target="_blank" class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="View File">
                      <i class="fas fa-file-image"></i>
                    </a>
                    <button v-if="invoice.status !== 'approved'" @click="deleteInvoice(invoice)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Invoice Review Modal -->
    <div v-if="reviewingInvoice" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" @click.self="reviewingInvoice = null">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden flex">
        <!-- Left Side: Image Preview -->
        <div class="w-1/2 bg-gray-100 p-6 overflow-y-auto border-r border-gray-200">
          <h3 class="text-lg font-bold text-gray-800 mb-4">Original Invoice</h3>
          <img :src="reviewingInvoice.file_url" alt="Invoice" class="w-full rounded-lg shadow-lg">
        </div>

        <!-- Right Side: Extracted Data -->
        <div class="w-1/2 flex flex-col">
          <!-- Header -->
          <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-4">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-xl font-bold">Review Invoice</h2>
                <p class="text-sm opacity-90">Verify and edit extracted data</p>
              </div>
              <button @click="reviewingInvoice = null" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2">
                <i class="fas fa-times text-lg"></i>
              </button>
            </div>
          </div>

          <!-- Content -->
          <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <!-- Invoice Details -->
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Invoice Number</label>
                <input v-model="editForm.invoice_number" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Supplier Name</label>
                <input v-model="editForm.supplier_name" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Invoice Date</label>
                  <input v-model="editForm.invoice_date" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>

                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Total Amount (JMD)</label>
                  <input v-model.number="editForm.total_amount" type="number" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
              </div>
            </div>

            <!-- Line Items -->
            <div>
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Line Items</h3>
                <button @click="addLineItem" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-semibold">
                  <i class="fas fa-plus mr-2"></i>Add Item
                </button>
              </div>

              <div class="space-y-3">
                <div v-for="(item, index) in editForm.extracted_items" :key="index" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                  <div class="flex items-start gap-3">
                    <div class="flex-1 space-y-3">
                      <input v-model="item.product_name" type="text" placeholder="Product name" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">

                      <div class="grid grid-cols-3 gap-2">
                        <input v-model.number="item.quantity" type="number" step="0.01" placeholder="Qty" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <input v-model="item.unit" type="text" placeholder="Unit" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <input v-model.number="item.unit_price" type="number" step="0.01" placeholder="Price" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                      </div>

                      <div class="text-sm font-semibold text-gray-700">
                        Line Total: JMD {{ formatNumber((item.quantity || 0) * (item.unit_price || 0)) }}
                      </div>
                    </div>

                    <button @click="removeLineItem(index)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Notes</label>
              <textarea v-model="editForm.notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Any additional notes..."></textarea>
            </div>
          </div>

          <!-- Footer Actions -->
          <div class="border-t border-gray-200 px-6 py-4 flex gap-3">
            <button @click="saveInvoice" :disabled="saving" class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-lg font-semibold disabled:opacity-50">
              <i v-if="saving" class="fas fa-spinner fa-spin mr-2"></i>
              <i v-else class="fas fa-save mr-2"></i>
              {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
            <button @click="createGRN" :disabled="creatingGRN" class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold disabled:opacity-50">
              <i v-if="creatingGRN" class="fas fa-spinner fa-spin mr-2"></i>
              <i v-else class="fas fa-check-circle mr-2"></i>
              {{ creatingGRN ? 'Creating...' : 'Create GRN' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

const fileInput = ref<HTMLInputElement | null>(null)
const selectedFile = ref<File | null>(null)
const dragOver = ref(false)
const uploading = ref(false)
const uploadProgress = ref('')
const loading = ref(false)
const invoices = ref<any[]>([])
const reviewingInvoice = ref<any>(null)
const saving = ref(false)
const creatingGRN = ref(false)

const editForm = ref({
  invoice_number: '',
  supplier_name: '',
  invoice_date: '',
  total_amount: 0,
  extracted_items: [] as any[],
  notes: '',
})

function handleFileSelect(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    selectedFile.value = target.files[0]
  }
}

function handleDrop(event: DragEvent) {
  dragOver.value = false
  if (event.dataTransfer?.files && event.dataTransfer.files[0]) {
    selectedFile.value = event.dataTransfer.files[0]
  }
}

function clearFile() {
  selectedFile.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

function formatFileSize(bytes: number): string {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}

async function uploadInvoice() {
  if (!selectedFile.value) return

  uploading.value = true
  uploadProgress.value = 'Uploading invoice...'

  const formData = new FormData()
  formData.append('invoice', selectedFile.value)

  try {
    uploadProgress.value = 'Processing with AI...'
    const response = await fetch('/api/invoices/upload', {
      method: 'POST',
      body: formData,
    })

    if (!response.ok) {
      throw new Error('Upload failed')
    }

    const result = await response.json()

    uploadProgress.value = 'Success! Invoice processed.'
    clearFile()

    setTimeout(() => {
      uploadProgress.value = ''
      refreshInvoices()
    }, 2000)
  } catch (error) {
    console.error('Upload error:', error)
    alert('Failed to upload invoice. Please try again.')
    uploadProgress.value = ''
  } finally {
    uploading.value = false
  }
}

async function refreshInvoices() {
  loading.value = true
  try {
    const response = await fetch('/api/invoices')
    invoices.value = await response.json()
  } catch (error) {
    console.error('Failed to load invoices:', error)
  } finally {
    loading.value = false
  }
}

function formatDate(dateStr: string | null): string {
  if (!dateStr) return 'N/A'
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

function formatMoney(cents: number): string {
  return 'JMD ' + (cents / 100).toLocaleString('en-JM', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatNumber(num: number): string {
  return num.toLocaleString('en-JM', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function getStatusClass(status: string): string {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-700',
    processed: 'bg-blue-100 text-blue-700',
    approved: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
  }
  return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-700'
}

function getStatusLabel(status: string): string {
  const labels = {
    pending: 'Pending',
    processed: 'Processed',
    approved: 'Approved',
    rejected: 'Rejected',
  }
  return labels[status as keyof typeof labels] || status
}

async function viewInvoice(invoice: any) {
  try {
    const response = await fetch(`/api/invoices/${invoice.id}`)
    const data = await response.json()

    reviewingInvoice.value = data
    editForm.value = {
      invoice_number: data.invoice_number || '',
      supplier_name: data.supplier_name || '',
      invoice_date: data.invoice_date || '',
      total_amount: data.total_amount_cents ? data.total_amount_cents / 100 : 0,
      extracted_items: data.extracted_items || [],
      notes: data.notes || '',
    }
  } catch (error) {
    console.error('Failed to load invoice:', error)
    alert('Failed to load invoice details')
  }
}

async function saveInvoice() {
  if (!reviewingInvoice.value) return

  saving.value = true
  try {
    const response = await fetch(`/api/invoices/${reviewingInvoice.value.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        invoice_number: editForm.value.invoice_number,
        supplier_name: editForm.value.supplier_name,
        invoice_date: editForm.value.invoice_date,
        total_amount_cents: Math.round(editForm.value.total_amount * 100),
        extracted_items: editForm.value.extracted_items,
        notes: editForm.value.notes,
      }),
    })

    if (!response.ok) throw new Error('Failed to save')

    alert('Invoice updated successfully!')
    refreshInvoices()
    reviewingInvoice.value = null
  } catch (error) {
    console.error('Save error:', error)
    alert('Failed to save invoice')
  } finally {
    saving.value = false
  }
}

async function createGRN() {
  if (!reviewingInvoice.value) return

  // Match products first
  creatingGRN.value = true
  try {
    const matchResponse = await fetch(`/api/invoices/${reviewingInvoice.value.id}/match-products`)
    const matchData = await matchResponse.json()

    // Show product matching interface (simplified - auto-match first result)
    const items = editForm.value.extracted_items.map((item: any, index: number) => {
      const suggestion = matchData.suggestions[index]
      const matchedProduct = suggestion?.matches?.[0]

      if (!matchedProduct) {
        alert(`Cannot find product match for: ${item.product_name}`)
        throw new Error('Product matching incomplete')
      }

      return {
        product_id: matchedProduct.id,
        quantity: item.quantity,
        unit_cost_cents: Math.round((item.unit_price || 0) * 100),
      }
    })

    // Create GRN
    const response = await fetch(`/api/invoices/${reviewingInvoice.value.id}/create-grn`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        location_id: 1, // Default location
        items,
      }),
    })

    if (!response.ok) throw new Error('Failed to create GRN')

    const result = await response.json()
    alert('Goods Receipt created successfully! Invoice approved.')
    refreshInvoices()
    reviewingInvoice.value = null
  } catch (error) {
    console.error('GRN creation error:', error)
    alert('Failed to create Goods Receipt. Please check product matches.')
  } finally {
    creatingGRN.value = false
  }
}

function addLineItem() {
  editForm.value.extracted_items.push({
    product_name: '',
    quantity: 0,
    unit: '',
    unit_price: 0,
    line_total: 0,
  })
}

function removeLineItem(index: number) {
  editForm.value.extracted_items.splice(index, 1)
}

async function deleteInvoice(invoice: any) {
  if (!confirm('Are you sure you want to delete this invoice?')) return

  try {
    const response = await fetch(`/api/invoices/${invoice.id}`, {
      method: 'DELETE',
    })

    if (!response.ok) throw new Error('Failed to delete')

    alert('Invoice deleted successfully')
    refreshInvoices()
  } catch (error) {
    console.error('Delete error:', error)
    alert('Failed to delete invoice')
  }
}

onMounted(() => {
  refreshInvoices()
})
</script>

<style scoped>
.gradient-bg {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  background-attachment: fixed;
}

.glass-effect {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}
</style>
