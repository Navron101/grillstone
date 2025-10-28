<template>
  <LoyaltyLayout>
    <div class="min-h-screen bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Companies</h1>
            <p class="mt-1 text-gray-600">Manage companies participating in the loyalty program</p>
          </div>
        <button
          @click="showModal = true; editingCompany = null"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          <i class="fas fa-plus mr-2"></i>Add Company
        </button>
      </div>

      <!-- Companies List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employees</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="company in companies" :key="company.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="font-medium text-gray-900">{{ company.name }}</div>
                <div class="text-sm text-gray-500" v-if="company.contact_email">{{ company.contact_email }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ company.contact_name || '-' }}</div>
                <div class="text-sm text-gray-500">{{ company.contact_phone || '-' }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="font-medium text-gray-900">{{ company.discount_percentage }}%</div>
                <div class="text-xs text-gray-500" v-if="company.per_order_cap">
                  Cap: ${{ company.per_order_cap }}/order
                </div>
              </td>
              <td class="px-6 py-4">
                <span class="text-gray-900">{{ company.employees_count || 0 }}</span>
              </td>
              <td class="px-6 py-4">
                <span
                  :class="{
                    'bg-green-100 text-green-800': company.status === 'active',
                    'bg-gray-100 text-gray-800': company.status === 'inactive',
                    'bg-red-100 text-red-800': company.status === 'suspended',
                  }"
                  class="px-2 py-1 text-xs font-semibold rounded-full"
                >
                  {{ company.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm">
                <button @click="editCompany(company)" class="text-blue-600 hover:text-blue-900 mr-3">
                  <i class="fas fa-edit"></i>
                </button>
                <button @click="deleteCompany(company)" class="text-red-600 hover:text-red-900">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
            <tr v-if="companies.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                No companies found. Click "Add Company" to get started.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <h3 class="text-xl font-bold mb-4">{{ editingCompany ? 'Edit Company' : 'Add Company' }}</h3>

        <form @submit.prevent="saveCompany">
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Company Name *</label>
              <input v-model="form.name" type="text" required class="w-full px-3 py-2 border rounded-lg" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Contact Name</label>
              <input v-model="form.contact_name" type="text" class="w-full px-3 py-2 border rounded-lg" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
              <input v-model="form.contact_phone" type="tel" class="w-full px-3 py-2 border rounded-lg" />
            </div>

            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
              <input v-model="form.contact_email" type="email" class="w-full px-3 py-2 border rounded-lg" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Discount Percentage * (%)</label>
              <input v-model.number="form.discount_percentage" type="number" step="0.01" min="0" max="100" required class="w-full px-3 py-2 border rounded-lg" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Per-Order Cap ($)</label>
              <input v-model.number="form.per_order_cap" type="number" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg" placeholder="Optional" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Per-Employee Monthly Cap ($)</label>
              <input v-model.number="form.per_employee_monthly_cap" type="number" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg" placeholder="Optional" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Company Monthly Cap ($)</label>
              <input v-model.number="form.company_monthly_cap" type="number" step="0.01" min="0" class="w-full px-3 py-2 border rounded-lg" placeholder="Optional" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select v-model="form.status" class="w-full px-3 py-2 border rounded-lg">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
            </div>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="showModal = false" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
              Cancel
            </button>
            <button type="submit" :disabled="saving" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
              {{ saving ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>
    </div>
  </LoyaltyLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import LoyaltyLayout from '@/layouts/LoyaltyLayout.vue'

const companies = ref<any[]>([])
const showModal = ref(false)
const editingCompany = ref<any>(null)
const saving = ref(false)

const form = ref({
  name: '',
  contact_name: '',
  contact_email: '',
  contact_phone: '',
  discount_percentage: 10,
  per_order_cap: null,
  per_employee_monthly_cap: null,
  company_monthly_cap: null,
  status: 'active',
})

const fetchCompanies = async () => {
  try {
    const res = await fetch('/api/loyalty/companies')
    companies.value = await res.json()
  } catch (error) {
    console.error('Error fetching companies:', error)
  }
}

const editCompany = (company: any) => {
  editingCompany.value = company
  form.value = { ...company }
  showModal.value = true
}

const saveCompany = async () => {
  saving.value = true
  try {
    const url = editingCompany.value
      ? `/api/loyalty/companies/${editingCompany.value.id}`
      : '/api/loyalty/companies'
    const method = editingCompany.value ? 'PUT' : 'POST'

    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form.value),
    })

    if (res.ok) {
      showModal.value = false
      await fetchCompanies()
      resetForm()
    }
  } catch (error) {
    console.error('Error saving company:', error)
  } finally {
    saving.value = false
  }
}

const deleteCompany = async (company: any) => {
  if (!confirm(`Delete ${company.name}?`)) return

  try {
    const res = await fetch(`/api/loyalty/companies/${company.id}`, { method: 'DELETE' })
    if (res.ok) {
      await fetchCompanies()
    }
  } catch (error) {
    console.error('Error deleting company:', error)
  }
}

const resetForm = () => {
  form.value = {
    name: '',
    contact_name: '',
    contact_email: '',
    contact_phone: '',
    discount_percentage: 10,
    per_order_cap: null,
    per_employee_monthly_cap: null,
    company_monthly_cap: null,
    status: 'active',
  }
  editingCompany.value = null
}

onMounted(() => {
  fetchCompanies()
})
</script>
