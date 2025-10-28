<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <a href="/inventory/invoices" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
            <i class="fas fa-arrow-left text-lg"></i>
          </a>
          <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-camera text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-900">Scan Invoice</h1>
            <p class="text-sm text-gray-600">Upload and automatically extract invoice data</p>
          </div>
        </div>
      </div>
    </header>

    <div class="max-w-7xl mx-auto p-6">
      <!-- Upload Section -->
      <div v-if="!scannedData" class="bg-white rounded-lg shadow p-8">
        <div class="text-center">
          <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-cloud-upload-alt text-blue-600 text-3xl"></i>
          </div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Upload Invoice Image</h2>
          <p class="text-gray-600 mb-6">Take a photo or upload a scanned invoice. We'll automatically extract the data for you.</p>

          <!-- File Upload -->
          <div class="max-w-md mx-auto">
            <label class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
              <div class="flex flex-col items-center justify-center pt-5 pb-6">
                <i class="fas fa-file-image text-gray-400 text-5xl mb-4"></i>
                <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                <p class="text-xs text-gray-500">JPG, PNG or PDF (MAX. 10MB)</p>
              </div>
              <input
                ref="fileInput"
                type="file"
                class="hidden"
                accept="image/*,.pdf"
                @change="handleFileUpload"
              />
            </label>

            <button
              v-if="selectedFile"
              @click="uploadAndScan"
              :disabled="uploading"
              class="w-full mt-4 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="!uploading" class="flex items-center justify-center gap-2">
                <i class="fas fa-magic"></i>
                Scan Invoice
              </span>
              <span v-else class="flex items-center justify-center gap-2">
                <i class="fas fa-spinner fa-spin"></i>
                Processing... {{uploadProgress}}%
              </span>
            </button>

            <div v-if="selectedFile" class="mt-4 text-sm text-gray-600 text-center">
              Selected: {{ selectedFile.name }}
            </div>
          </div>
        </div>
      </div>

      <!-- Review Section -->
      <div v-if="scannedData" class="space-y-6">
        <!-- Confidence Alert -->
        <div v-if="scannedData.needs_review" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
          <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-yellow-600 text-xl mr-3"></i>
            <div>
              <h3 class="text-sm font-medium text-yellow-800">Review Required</h3>
              <p class="text-sm text-yellow-700 mt-1">
                Some fields have low confidence ({{ scannedData.low_confidence_fields.length }} fields). Please verify the highlighted data below.
              </p>
            </div>
          </div>
        </div>

        <!-- Success Message -->
        <div v-else class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
          <div class="flex items-center">
            <i class="fas fa-check-circle text-green-600 text-xl mr-3"></i>
            <div>
              <h3 class="text-sm font-medium text-green-800">Scan Successful</h3>
              <p class="text-sm text-green-700 mt-1">
                Invoice data extracted successfully ({{ (scannedData.ocr_confidence * 100).toFixed(0) }}% confidence). Review and save below.
              </p>
            </div>
          </div>
        </div>

        <!-- Invoice Data Form -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-6">Review Invoice Data</h2>

          <form @submit.prevent="saveInvoice" class="space-y-6">
            <div class="grid grid-cols-3 gap-4">
              <!-- Invoice Number -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                  Invoice Number *
                  <i v-if="isLowConfidence('invoice_number')" class="fas fa-exclamation-triangle text-yellow-500 text-xs" title="Low confidence - please verify"></i>
                </label>
                <input
                  v-model="reviewForm.invoice_number"
                  required
                  :class="isLowConfidence('invoice_number') ? 'border-yellow-400 bg-yellow-50' : ''"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <!-- Supplier Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                  Supplier *
                  <i v-if="isLowConfidence('supplier_name')" class="fas fa-exclamation-triangle text-yellow-500 text-xs" title="Low confidence - please verify"></i>
                </label>
                <input
                  v-model="reviewForm.supplier_name"
                  required
                  :class="isLowConfidence('supplier_name') ? 'border-yellow-400 bg-yellow-50' : ''"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <!-- Invoice Date -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                  Date *
                  <i v-if="isLowConfidence('invoice_date')" class="fas fa-exclamation-triangle text-yellow-500 text-xs" title="Low confidence - please verify"></i>
                </label>
                <input
                  v-model="reviewForm.invoice_date"
                  type="date"
                  required
                  :class="isLowConfidence('invoice_date') ? 'border-yellow-400 bg-yellow-50' : ''"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <!-- Line Items -->
            <div>
              <div class="flex items-center justify-between mb-4">
                <label class="text-sm font-medium text-gray-700">Line Items</label>
                <button
                  type="button"
                  @click="addLineItem"
                  class="text-blue-600 hover:text-blue-700 text-sm font-medium"
                >
                  <i class="fas fa-plus mr-1"></i>Add Item
                </button>
              </div>

              <div class="space-y-3">
                <div
                  v-for="(item, index) in reviewForm.items"
                  :key="index"
                  class="grid grid-cols-12 gap-3 items-start p-3 bg-gray-50 rounded-lg"
                  :class="isItemLowConfidence(index) ? 'bg-yellow-50 border border-yellow-400' : ''"
                >
                  <div class="col-span-5">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Product Name</label>
                    <input
                      v-model="item.product_name"
                      required
                      class="w-full px-3 py-2 border rounded text-sm"
                      placeholder="Product name"
                    />
                  </div>
                  <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Quantity</label>
                    <input
                      v-model.number="item.qty"
                      type="number"
                      step="0.01"
                      required
                      class="w-full px-3 py-2 border rounded text-sm"
                      placeholder="0.00"
                    />
                  </div>
                  <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Unit Price (JMD)</label>
                    <input
                      v-model.number="item.unit_price"
                      type="number"
                      step="0.01"
                      required
                      class="w-full px-3 py-2 border rounded text-sm"
                      placeholder="0.00"
                    />
                  </div>
                  <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Total</label>
                    <input
                      :value="(item.qty * item.unit_price).toFixed(2)"
                      disabled
                      class="w-full px-3 py-2 border rounded bg-gray-100 text-sm"
                    />
                  </div>
                  <div class="col-span-1 flex items-end">
                    <button
                      type="button"
                      @click="removeLineItem(index)"
                      class="p-2 text-red-600 hover:bg-red-50 rounded"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Total Amount -->
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Total Amount (JMD)</label>
                  <input
                    v-model.number="reviewForm.total_amount"
                    type="number"
                    step="0.01"
                    required
                    class="w-full px-4 py-2 border rounded-lg"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Calculated Total</label>
                  <input
                    :value="calculatedTotal.toFixed(2)"
                    disabled
                    class="w-full px-4 py-2 border rounded-lg bg-gray-100"
                  />
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
              <textarea
                v-model="reviewForm.notes"
                rows="3"
                class="w-full px-4 py-2 border rounded-lg"
                placeholder="Additional notes..."
              ></textarea>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t">
              <button
                type="button"
                @click="startOver"
                class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="saving"
                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50"
              >
                <span v-if="!saving">
                  <i class="fas fa-save mr-2"></i>Save Invoice
                </span>
                <span v-else>
                  <i class="fas fa-spinner fa-spin mr-2"></i>Saving...
                </span>
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
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const selectedFile = ref(null)
const uploading = ref(false)
const uploadProgress = ref(0)
const scannedData = ref(null)
const saving = ref(false)

const reviewForm = ref({
  invoice_number: '',
  supplier_name: '',
  invoice_date: '',
  items: [],
  total_amount: 0,
  notes: '',
})

const fileInput = ref(null)

function handleFileUpload(event) {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
  }
}

async function uploadAndScan() {
  if (!selectedFile.value) return

  uploading.value = true
  uploadProgress.value = 0

  try {
    const formData = new FormData()
    formData.append('file', selectedFile.value)

    // Simulate progress
    const progressInterval = setInterval(() => {
      if (uploadProgress.value < 90) {
        uploadProgress.value += 10
      }
    }, 200)

    const resp = await fetch('/api/invoices/upload-scan', {
      method: 'POST',
      body: formData,
    })

    clearInterval(progressInterval)
    uploadProgress.value = 100

    if (!resp.ok) throw new Error('Upload failed')

    const data = await resp.json()
    scannedData.value = data

    // Populate review form
    const parsed = data.parsed_data
    reviewForm.value.invoice_number = parsed.invoice_number?.value || ''
    reviewForm.value.supplier_name = parsed.supplier_name?.value || ''
    reviewForm.value.invoice_date = parsed.invoice_date?.value || ''
    reviewForm.value.notes = parsed.notes?.value || ''
    reviewForm.value.total_amount = (parsed.total_amount_cents?.value || 0) / 100

    // Populate items
    reviewForm.value.items = (parsed.items || []).map(item => ({
      product_name: item.product_name?.value || '',
      qty: item.qty?.value || 0,
      unit_price: (item.unit_price_cents?.value || 0) / 100,
    }))

    toast('Success', 'Invoice scanned successfully!', 'success')
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to scan invoice', 'error')
  } finally {
    uploading.value = false
  }
}

function isLowConfidence(field) {
  if (!scannedData.value) return false
  return scannedData.value.low_confidence_fields?.some(f => f.field === field)
}

function isItemLowConfidence(index) {
  if (!scannedData.value) return false
  return scannedData.value.low_confidence_fields?.some(f =>
    f.field.startsWith(`items.${index}.`)
  )
}

function addLineItem() {
  reviewForm.value.items.push({
    product_name: '',
    qty: 0,
    unit_price: 0,
  })
}

function removeLineItem(index) {
  reviewForm.value.items.splice(index, 1)
}

const calculatedTotal = computed(() => {
  return reviewForm.value.items.reduce((sum, item) => {
    return sum + (item.qty * item.unit_price)
  }, 0)
})

async function saveInvoice() {
  saving.value = true
  try {
    const data = {
      invoice_number: reviewForm.value.invoice_number,
      supplier_name: reviewForm.value.supplier_name,
      invoice_date: reviewForm.value.invoice_date,
      total_amount_cents: Math.round(reviewForm.value.total_amount * 100),
      notes: reviewForm.value.notes,
      items: reviewForm.value.items.map(item => ({
        product_name: item.product_name,
        qty: item.qty,
        unit_price_cents: Math.round(item.unit_price * 100),
      })),
      status: 'approved',
    }

    const resp = await fetch(`/api/invoices/${scannedData.value.invoice.id}/review-update`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    })

    if (!resp.ok) throw new Error('Failed to save')

    toast('Success', 'Invoice saved successfully!', 'success')

    setTimeout(() => {
      window.location.href = '/inventory/invoices'
    }, 1500)
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to save invoice', 'error')
  } finally {
    saving.value = false
  }
}

function startOver() {
  scannedData.value = null
  selectedFile.value = null
  reviewForm.value = {
    invoice_number: '',
    supplier_name: '',
    invoice_date: '',
    items: [],
    total_amount: 0,
    notes: '',
  }
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
