<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import HRLayout from '@/layouts/HRLayout.vue'
import axios from 'axios'

interface Contract {
  id: number
  employee_name: string
  employee_address: string
  position: string
  contract_type: string
  start_date: string
  end_date: string | null
  salary_amount_cents: number | null
  status: string
  sent_at: string | null
  sent_to_email: string | null
  contract_html: string
  created_at: string
}

interface Employee {
  id: number
  name: string
  email: string
  position: string
  address: string
}

const contracts = ref<Contract[]>([])
const employees = ref<Employee[]>([])
const loading = ref(false)
const showGenerateModal = ref(false)
const showPreviewModal = ref(false)
const showSendEmailModal = ref(false)
const selectedContract = ref<Contract | null>(null)
const searchQuery = ref('')
const statusFilter = ref('all')
const employeeSearchQuery = ref('')
const showEmployeeDropdown = ref(false)

const generateForm = ref({
  employee_id: null as number | null,
  employee_name: '',
  employee_address: '',
  position: '',
  salary_amount_cents: null as number | null,
  start_date: '',
  end_date: '',
  contract_type: 'permanent'
})

const emailForm = ref({
  email: ''
})

// Fetch contracts
const fetchContracts = async () => {
  loading.value = true
  try {
    const params: any = {}
    if (statusFilter.value !== 'all') {
      params.status = statusFilter.value
    }
    if (searchQuery.value) {
      params.search = searchQuery.value
    }

    const { data } = await axios.get('/api/employment-contracts', { params })
    contracts.value = data.contracts
  } catch (error) {
    console.error('Failed to fetch contracts:', error)
  } finally {
    loading.value = false
  }
}

// Search employees (autocomplete)
const searchEmployees = async () => {
  if (employeeSearchQuery.value.length < 2) {
    employees.value = []
    return
  }

  try {
    const { data } = await axios.get('/api/job-letters/search-employees', {
      params: { query: employeeSearchQuery.value }
    })
    employees.value = data.employees
    showEmployeeDropdown.value = true
  } catch (error) {
    console.error('Failed to search employees:', error)
  }
}

// Select employee from dropdown
const selectEmployee = (employee: Employee) => {
  generateForm.value.employee_id = employee.id
  generateForm.value.employee_name = employee.name
  generateForm.value.employee_address = employee.address || ''
  generateForm.value.position = employee.position
  employeeSearchQuery.value = employee.name
  emailForm.value.email = employee.email
  showEmployeeDropdown.value = false
}

// Generate new contract
const generateContract = async () => {
  loading.value = true
  try {
    const payload = {
      ...generateForm.value,
      end_date: generateForm.value.end_date || null
    }

    const { data } = await axios.post('/api/employment-contracts', payload)
    alert(data.message)
    showGenerateModal.value = false
    resetGenerateForm()
    fetchContracts()
  } catch (error: any) {
    alert(error.response?.data?.message || 'Failed to generate contract')
  } finally {
    loading.value = false
  }
}

// Send contract via email
const sendEmail = async () => {
  if (!selectedContract.value) return

  loading.value = true
  try {
    const { data } = await axios.post(
      `/api/employment-contracts/${selectedContract.value.id}/send-email`,
      emailForm.value
    )
    alert(data.message)
    showSendEmailModal.value = false
    emailForm.value.email = ''
    fetchContracts()
  } catch (error: any) {
    alert(error.response?.data?.message || 'Failed to send email')
  } finally {
    loading.value = false
  }
}

// Update contract status
const updateStatus = async (contractId: number, status: string) => {
  if (!confirm(`Mark this contract as ${status}?`)) return

  loading.value = true
  try {
    await axios.post(`/api/employment-contracts/${contractId}/update-status`, { status })
    alert('Status updated successfully')
    fetchContracts()
  } catch (error: any) {
    alert(error.response?.data?.message || 'Failed to update status')
  } finally {
    loading.value = false
  }
}

// Delete contract
const deleteContract = async (contractId: number) => {
  if (!confirm('Are you sure you want to delete this contract?')) return

  loading.value = true
  try {
    await axios.delete(`/api/employment-contracts/${contractId}`)
    alert('Contract deleted successfully')
    fetchContracts()
  } catch (error: any) {
    alert(error.response?.data?.message || 'Failed to delete contract')
  } finally {
    loading.value = false
  }
}

// Open preview modal
const previewContract = (contract: Contract) => {
  selectedContract.value = contract
  showPreviewModal.value = true
}

// Open send email modal
const openSendEmailModal = (contract: Contract) => {
  selectedContract.value = contract
  emailForm.value.email = contract.sent_to_email || ''
  showSendEmailModal.value = true
}

// Reset generate form
const resetGenerateForm = () => {
  generateForm.value = {
    employee_id: null,
    employee_name: '',
    employee_address: '',
    position: '',
    salary_amount_cents: null,
    start_date: '',
    end_date: '',
    contract_type: 'permanent'
  }
  employeeSearchQuery.value = ''
  employees.value = []
  showEmployeeDropdown.value = false
}

// Computed
const filteredContracts = computed(() => {
  return contracts.value
})

const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-700',
    sent: 'bg-blue-100 text-blue-700',
    signed: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700'
  }
  return colors[status] || 'bg-gray-100 text-gray-700'
}

const formatDate = (date: string | null) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatMoney = (cents: number | null) => {
  if (!cents) return 'N/A'
  return `JMD $${(cents / 100).toLocaleString('en-US', { minimumFractionDigits: 2 })}`
}

onMounted(() => {
  fetchContracts()
})
</script>

<template>
  <Head title="Employment Contracts" />
  <HRLayout>
    <div class="p-6 min-h-screen bg-white">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Employment Contracts</h1>
          <p class="text-sm text-gray-600 mt-1">Generate and manage employee contracts</p>
        </div>
        <button
          @click="showGenerateModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
        >
          <i class="fas fa-file-contract"></i>
          Generate Contract
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="searchQuery"
              @input="fetchContracts"
              type="text"
              placeholder="Search by employee name..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="statusFilter"
              @change="fetchContracts"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg"
            >
              <option value="all">All Statuses</option>
              <option value="draft">Draft</option>
              <option value="sent">Sent</option>
              <option value="signed">Signed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Contracts List -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div v-if="loading" class="p-8 text-center text-gray-500">
          <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
          <p>Loading contracts...</p>
        </div>

        <div v-else-if="filteredContracts.length === 0" class="p-8 text-center text-gray-500">
          <i class="fas fa-file-contract text-4xl mb-3 text-gray-300"></i>
          <p class="text-lg font-medium">No contracts found</p>
          <p class="text-sm">Generate your first contract to get started</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Position</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="contract in filteredContracts" :key="contract.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="font-medium text-gray-900">{{ contract.employee_name }}</div>
                  <div class="text-sm text-gray-500">{{ formatMoney(contract.salary_amount_cents) }}</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ contract.position }}</td>
                <td class="px-6 py-4">
                  <span class="text-sm capitalize">{{ contract.contract_type }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(contract.start_date) }}</td>
                <td class="px-6 py-4">
                  <span
                    :class="getStatusColor(contract.status)"
                    class="px-2 py-1 text-xs font-medium rounded-full capitalize"
                  >
                    {{ contract.status }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <button
                      @click="previewContract(contract)"
                      class="text-blue-600 hover:text-blue-800"
                      title="Preview"
                    >
                      <i class="fas fa-eye"></i>
                    </button>
                    <a
                      :href="`/api/employment-contracts/${contract.id}/download-pdf`"
                      class="text-orange-600 hover:text-orange-800"
                      title="Download PDF"
                      target="_blank"
                    >
                      <i class="fas fa-file-pdf"></i>
                    </a>
                    <a
                      :href="`/api/employment-contracts/${contract.id}/download-word`"
                      class="text-indigo-600 hover:text-indigo-800"
                      title="Download Word"
                      target="_blank"
                    >
                      <i class="fas fa-file-word"></i>
                    </a>
                    <button
                      @click="openSendEmailModal(contract)"
                      class="text-green-600 hover:text-green-800"
                      title="Send Email"
                    >
                      <i class="fas fa-envelope"></i>
                    </button>
                    <button
                      v-if="contract.status === 'sent'"
                      @click="updateStatus(contract.id, 'signed')"
                      class="text-purple-600 hover:text-purple-800"
                      title="Mark as Signed"
                    >
                      <i class="fas fa-signature"></i>
                    </button>
                    <button
                      @click="deleteContract(contract.id)"
                      class="text-red-600 hover:text-red-800"
                      title="Delete"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Generate Contract Modal -->
      <div
        v-if="showGenerateModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        @click.self="showGenerateModal = false"
      >
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">Generate Employment Contract</h2>
            <button @click="showGenerateModal = false" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form @submit.prevent="generateContract" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search Employee (Optional)</label>
                <div class="relative">
                  <input
                    v-model="employeeSearchQuery"
                    @input="searchEmployees"
                    @focus="showEmployeeDropdown = employees.length > 0"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                    placeholder="Start typing to search existing employees..."
                    autocomplete="off"
                  />
                  <div
                    v-if="showEmployeeDropdown && employees.length > 0"
                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                  >
                    <button
                      v-for="employee in employees"
                      :key="employee.id"
                      type="button"
                      @click="selectEmployee(employee)"
                      class="w-full px-4 py-3 text-left hover:bg-gray-50 border-b last:border-b-0"
                    >
                      <div class="font-medium text-gray-900">{{ employee.name }}</div>
                      <div class="text-sm text-gray-600">{{ employee.position }} | {{ employee.email }}</div>
                    </button>
                  </div>
                </div>
                <p v-if="generateForm.employee_id" class="text-xs text-green-600 mt-1">
                  <i class="fas fa-check-circle"></i>
                  Employee selected: {{ generateForm.employee_name }} - Details auto-filled
                </p>
                <p v-else class="text-xs text-gray-500 mt-1">
                  <i class="fas fa-info-circle"></i>
                  Or enter details manually below
                </p>
              </div>

              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Employee Name *</label>
                <input
                  v-model="generateForm.employee_name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                  placeholder="John Doe"
                />
              </div>

              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Employee Address *</label>
                <textarea
                  v-model="generateForm.employee_address"
                  required
                  rows="2"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                  placeholder="123 Main St, Kingston, Jamaica"
                ></textarea>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Position *</label>
                <input
                  v-model="generateForm.position"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                  placeholder="Server"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contract Type *</label>
                <select
                  v-model="generateForm.contract_type"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                >
                  <option value="employment">Employment</option>
                  <option value="probation">Probation</option>
                  <option value="permanent">Permanent</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Salary (JMD)</label>
                <input
                  :value="generateForm.salary_amount_cents ? generateForm.salary_amount_cents / 100 : ''"
                  type="number"
                  step="0.01"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                  placeholder="50000"
                  @input="(e: any) => generateForm.salary_amount_cents = e.target.value ? Math.round(parseFloat(e.target.value) * 100) : null"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                <input
                  v-model="generateForm.start_date"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date (optional)</label>
                <input
                  v-model="generateForm.end_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                />
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t">
              <button
                type="button"
                @click="showGenerateModal = false"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
              >
                <i class="fas fa-file-contract mr-2"></i>
                Generate Contract
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Preview Modal -->
      <div
        v-if="showPreviewModal && selectedContract"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        @click.self="showPreviewModal = false"
      >
        <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">Contract Preview</h2>
            <button @click="showPreviewModal = false" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <div v-html="selectedContract.contract_html" class="border border-gray-200 rounded-lg p-6"></div>
        </div>
      </div>

      <!-- Send Email Modal -->
      <div
        v-if="showSendEmailModal && selectedContract"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        @click.self="showSendEmailModal = false"
      >
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">Send Contract via Email</h2>
            <button @click="showSendEmailModal = false" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form @submit.prevent="sendEmail" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
              <input
                v-model="emailForm.email"
                type="email"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                placeholder="employee@example.com"
              />
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t">
              <button
                type="button"
                @click="showSendEmailModal = false"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
              >
                <i class="fas fa-paper-plane mr-2"></i>
                Send Email
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </HRLayout>
</template>
