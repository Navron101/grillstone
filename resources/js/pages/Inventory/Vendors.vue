<template>
  <AppLayout title="Vendors">
    <div class="bg-white shadow-sm border-b border-gray-200 -mt-6 -mx-6 px-6 py-4 mb-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-truck text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-900">Vendors</h1>
            <p class="text-sm text-gray-600">Manage suppliers and vendors</p>
          </div>
        </div>
        <button
          @click="openCreateModal"
          class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg font-medium"
        >
          <i class="fas fa-plus mr-2"></i>New Vendor
        </button>
      </div>
    </div>

    <div class="max-w-7xl mx-auto">
      <!-- Vendors List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="vendors.length === 0">
              <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                No vendors found. Click "New Vendor" to add one.
              </td>
            </tr>
            <tr v-for="vendor in vendors" :key="vendor.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ vendor.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">{{ vendor.contact_name || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ vendor.phone || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ vendor.email || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <button @click="editVendor(vendor)" class="text-orange-600 hover:text-orange-900 mr-3">
                  Edit
                </button>
                <button @click="deleteVendor(vendor.id)" class="text-red-600 hover:text-red-900">
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
      <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold">{{ editingVendor ? 'Edit' : 'Create' }} Vendor</h2>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>

          <form @submit.prevent="saveVendor">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Vendor Name *</label>
                <input
                  v-model="form.name"
                  required
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                  placeholder="Enter vendor name"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Person</label>
                <input
                  v-model="form.contact_name"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                  placeholder="Contact name"
                />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                  <input
                    v-model="form.phone"
                    type="tel"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                    placeholder="Phone number"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                  <input
                    v-model="form.email"
                    type="email"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                    placeholder="email@example.com"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea
                  v-model="form.notes"
                  rows="3"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                  placeholder="Additional notes..."
                ></textarea>
              </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
              <button type="button" @click="closeModal" class="px-6 py-2 border rounded-lg hover:bg-gray-50">
                Cancel
              </button>
              <button
                type="submit"
                :disabled="saving"
                class="bg-orange-600 text-white px-6 py-2 rounded-lg hover:bg-orange-700 disabled:opacity-50"
              >
                {{ saving ? 'Saving...' : 'Save Vendor' }}
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
