<template>
  <div class="min-h-screen gradient-bg">
    <!-- Header -->
    <header class="glass-effect shadow-lg">
      <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 bg-gradient-to-r from-orange-600 to-red-700 rounded-lg flex items-center justify-center">
            <i class="fas fa-truck text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-800">Vendors</h1>
            <p class="text-sm text-gray-600">Manage suppliers and vendors</p>
          </div>
        </div>

        <button @click="openCreateModal" class="px-6 py-2 bg-gradient-to-r from-orange-600 to-red-700 hover:from-orange-700 hover:to-red-800 text-white rounded-lg font-medium transition-colors">
          <i class="fas fa-plus mr-2"></i>New Vendor
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
              <a href="/inventory/vendors" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors bg-orange-600 text-white">
                <i class="fas fa-truck text-lg text-white"></i>
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
        <!-- Vendors Table -->
        <div class="glass-effect rounded-2xl shadow-lg overflow-hidden">
          <table class="min-w-full">
            <thead class="bg-gradient-to-r from-orange-100 to-red-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Contact</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Phone</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-if="vendors.length === 0">
                <td colspan="5" class="px-6 py-12 text-center">
                  <i class="fas fa-truck text-6xl text-gray-300 mb-4"></i>
                  <p class="text-xl font-medium text-gray-600">No vendors found</p>
                  <p class="text-gray-500 mt-2">Click "New Vendor" to add one</p>
                </td>
              </tr>
              <tr v-for="vendor in vendors" :key="vendor.id" class="hover:bg-white hover:bg-opacity-50 transition-colors">
                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ vendor.name }}</td>
                <td class="px-6 py-4 text-sm text-gray-700">{{ vendor.contact_name || '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-700">{{ vendor.phone || '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-700">{{ vendor.email || '-' }}</td>
                <td class="px-6 py-4 text-sm">
                  <button @click="editVendor(vendor)" class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg text-xs font-medium mr-2 transition-colors">
                    <i class="fas fa-edit mr-1"></i>Edit
                  </button>
                  <button @click="deleteVendor(vendor.id)" class="px-3 py-1 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-lg text-xs font-medium transition-colors">
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
      <div class="glass-effect rounded-2xl shadow-2xl w-full max-w-2xl">
        <div class="bg-gradient-to-r from-orange-600 to-red-700 text-white px-6 py-4 rounded-t-2xl">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold">{{ editingVendor ? 'Edit' : 'Create' }} Vendor</h2>
            <button @click="closeModal" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2">
              <i class="fas fa-times text-lg"></i>
            </button>
          </div>
        </div>

        <form @submit.prevent="saveVendor" class="p-6">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Vendor Name *</label>
              <input
                v-model="form.name"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                placeholder="Enter vendor name"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Person</label>
              <input
                v-model="form.contact_name"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                placeholder="Contact name"
              />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                <input
                  v-model="form.phone"
                  type="tel"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                  placeholder="Phone number"
                />
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <input
                  v-model="form.email"
                  type="email"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                  placeholder="email@example.com"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Notes</label>
              <textarea
                v-model="form.notes"
                rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                placeholder="Additional notes..."
              ></textarea>
            </div>
          </div>

          <div class="flex justify-end gap-3 mt-6">
            <button type="button" @click="closeModal" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition-colors">
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-6 py-2 bg-gradient-to-r from-orange-600 to-red-700 hover:from-orange-700 hover:to-red-800 text-white rounded-lg font-medium disabled:opacity-50 transition-colors"
            >
              {{ saving ? 'Saving...' : 'Save Vendor' }}
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

const vendors = ref([])
const showModal = ref(false)
const editingVendor = ref(null)
const saving = ref(false)

const form = ref({
  name: '',
  contact_name: '',
  phone: '',
  email: '',
  notes: ''
})

async function loadVendors() {
  try {
    const resp = await fetch('/api/vendors')
    const data = await resp.json()
    vendors.value = data.vendors || []
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load vendors', 'error')
  }
}

function openCreateModal() {
  editingVendor.value = null
  form.value = {
    name: '',
    contact_name: '',
    phone: '',
    email: '',
    notes: ''
  }
  showModal.value = true
}

function editVendor(vendor) {
  editingVendor.value = vendor
  form.value = {
    name: vendor.name,
    contact_name: vendor.contact_name || '',
    phone: vendor.phone || '',
    email: vendor.email || '',
    notes: vendor.notes || ''
  }
  showModal.value = true
}

async function saveVendor() {
  if (!form.value.name.trim()) {
    toast('Error', 'Vendor name is required', 'error')
    return
  }

  saving.value = true
  try {
    const url = editingVendor.value
      ? `/api/vendors/${editingVendor.value.id}`
      : '/api/vendors'

    const method = editingVendor.value ? 'PUT' : 'POST'

    const resp = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form.value)
    })

    if (!resp.ok) throw new Error('Failed to save')

    toast('Success', 'Vendor saved successfully', 'success')
    closeModal()
    loadVendors()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to save vendor', 'error')
  } finally {
    saving.value = false
  }
}

async function deleteVendor(id) {
  if (!confirm('Are you sure you want to delete this vendor?')) return

  try {
    const resp = await fetch(`/api/vendors/${id}`, { method: 'DELETE' })

    if (!resp.ok) {
      const data = await resp.json()
      throw new Error(data.error || 'Failed to delete')
    }

    toast('Success', 'Vendor deleted successfully', 'success')
    loadVendors()
  } catch (e) {
    console.error(e)
    toast('Error', e.message || 'Failed to delete vendor', 'error')
  }
}

function closeModal() {
  showModal.value = false
  editingVendor.value = null
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
