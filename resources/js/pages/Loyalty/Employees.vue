<template>
  <LoyaltyLayout>
    <div class="min-h-screen bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Employees</h1>
            <p class="mt-1 text-gray-600">Manage employees enrolled in the loyalty program</p>
          </div>
        <button
          @click="showModal = true; editingEmployee = null"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
        >
          <i class="fas fa-plus mr-2"></i>Add Employee
        </button>
      </div>

      <!-- Filter -->
      <div class="mb-4 bg-white p-4 rounded-lg shadow flex gap-4">
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Company</label>
          <select v-model="filterCompany" @change="fetchEmployees" class="w-full px-3 py-2 border rounded-lg">
            <option value="">All Companies</option>
            <option v-for="company in companies" :key="company.id" :value="company.id">
              {{ company.name }}
            </option>
          </select>
        </div>
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
          <select v-model="filterStatus" @change="fetchEmployees" class="w-full px-3 py-2 border rounded-lg">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="suspended">Suspended</option>
          </select>
        </div>
      </div>

      <!-- Employees List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="employee in employees" :key="employee.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="font-medium text-gray-900">{{ employee.first_name }} {{ employee.last_name }}</div>
                <div class="text-xs text-gray-500" v-if="employee.employee_id">ID: {{ employee.employee_id }}</div>
              </td>
              <td class="px-6 py-4">
                <span class="text-sm text-gray-900">{{ employee.company?.name || '-' }}</span>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ employee.email }}</div>
                <div class="text-sm text-gray-500">{{ employee.phone }}</div>
              </td>
              <td class="px-6 py-4">
                <span
                  :class="{
                    'bg-green-100 text-green-800': employee.status === 'active',
                    'bg-gray-100 text-gray-800': employee.status === 'inactive',
                    'bg-red-100 text-red-800': employee.status === 'suspended',
                  }"
                  class="px-2 py-1 text-xs font-semibold rounded-full"
                >
                  {{ employee.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm">
                <button @click="editEmployee(employee)" class="text-blue-600 hover:text-blue-900 mr-3">
                  <i class="fas fa-edit"></i>
                </button>
                <button @click="deleteEmployee(employee)" class="text-red-600 hover:text-red-900">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
            <tr v-if="employees.length === 0">
              <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                No employees found. Click "Add Employee" to get started.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <h3 class="text-xl font-bold mb-4">{{ editingEmployee ? 'Edit Employee' : 'Add Employee' }}</h3>

        <form @submit.prevent="saveEmployee">
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Company *</label>
              <select v-model="form.loyalty_company_id" required class="w-full px-3 py-2 border rounded-lg">
                <option value="">Select a company</option>
                <option v-for="company in companies" :key="company.id" :value="company.id">
                  {{ company.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
              <input v-model="form.first_name" type="text" required class="w-full px-3 py-2 border rounded-lg" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
              <input v-model="form.last_name" type="text" required class="w-full px-3 py-2 border rounded-lg" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
              <input v-model="form.email" type="email" required class="w-full px-3 py-2 border rounded-lg" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
              <input v-model="form.phone" type="tel" required class="w-full px-3 py-2 border rounded-lg" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID</label>
              <input v-model="form.employee_id" type="text" class="w-full px-3 py-2 border rounded-lg" placeholder="Optional" />
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
            <button type="submit" :disabled="saving" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50">
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
const employees = ref<any[]>([])
const showModal = ref(false)
const editingEmployee = ref<any>(null)
const saving = ref(false)
const filterCompany = ref('')
const filterStatus = ref('')

const form = ref({
  loyalty_company_id: '',
  employee_id: '',
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
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

const fetchEmployees = async () => {
  try {
    let url = '/api/loyalty/employees?'
    if (filterCompany.value) url += `company_id=${filterCompany.value}&`
    if (filterStatus.value) url += `status=${filterStatus.value}`

    const res = await fetch(url)
    employees.value = await res.json()
  } catch (error) {
    console.error('Error fetching employees:', error)
  }
}

const editEmployee = (employee: any) => {
  editingEmployee.value = employee
  form.value = { ...employee }
  showModal.value = true
}

const saveEmployee = async () => {
  saving.value = true
  try {
    const url = editingEmployee.value
      ? `/api/loyalty/employees/${editingEmployee.value.id}`
      : '/api/loyalty/employees'
    const method = editingEmployee.value ? 'PUT' : 'POST'

    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form.value),
    })

    if (res.ok) {
      showModal.value = false
      await fetchEmployees()
      resetForm()
    } else {
      const error = await res.json()
      alert(error.message || 'Error saving employee')
    }
  } catch (error) {
    console.error('Error saving employee:', error)
    alert('Error saving employee')
  } finally {
    saving.value = false
  }
}

const deleteEmployee = async (employee: any) => {
  if (!confirm(`Delete ${employee.first_name} ${employee.last_name}?`)) return

  try {
    const res = await fetch(`/api/loyalty/employees/${employee.id}`, { method: 'DELETE' })
    if (res.ok) {
      await fetchEmployees()
    }
  } catch (error) {
    console.error('Error deleting employee:', error)
  }
}

const resetForm = () => {
  form.value = {
    loyalty_company_id: '',
    employee_id: '',
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    status: 'active',
  }
  editingEmployee.value = null
}

onMounted(() => {
  fetchCompanies()
  fetchEmployees()
})
</script>
