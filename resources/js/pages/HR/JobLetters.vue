<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Head } from '@inertiajs/vue3'
import HRLayout from '@/layouts/HRLayout.vue'
import axios from 'axios'
import { onClickOutside } from '@vueuse/core'

interface JobLetter {
  id: number
  employee_id: number
  employee_name: string
  recipient_name: string | null
  recipient_organization: string | null
  recipient_address: string | null
  letter_purpose: string
  letter_date: string
  status: string
  sent_at: string | null
  sent_to_email: string | null
  letter_html: string
  created_at: string
}

interface Employee {
  id: number
  name: string
  email: string
  position: string
  address: string
}

const letters = ref<JobLetter[]>([])
const employees = ref<Employee[]>([])
const loading = ref(false)
const showGenerateModal = ref(false)
const showPreviewModal = ref(false)
const showSendEmailModal = ref(false)
const selectedLetter = ref<JobLetter | null>(null)
const searchQuery = ref('')
const statusFilter = ref('all')
const employeeSearchQuery = ref('')
const showEmployeeDropdown = ref(false)
const employeeDropdownRef = ref(null)

const generateForm = ref({
  employee_id: null as number | null,
  employee_name: '',
  recipient_name: '',
  recipient_organization: '',
  recipient_address: '',
  letter_purpose: 'employment_verification',
  notes: ''
})

const emailForm = ref({
  email: ''
})

// Fetch job letters
const fetchLetters = async () => {
  loading.value = true
  try {
    const params: any = {}
    if (statusFilter.value !== 'all') {
      params.status = statusFilter.value
    }
    if (searchQuery.value) {
      params.search = searchQuery.value
    }

    const { data } = await axios.get('/api/job-letters', { params })
    letters.value = data.letters
  } catch (error) {
    console.error('Failed to fetch job letters:', error)
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
  employeeSearchQuery.value = employee.name
  emailForm.value.email = employee.email
  showEmployeeDropdown.value = false
}

// Generate job letter
const generateLetter = async () => {
  if (!generateForm.value.employee_id) {
    alert('Please select an employee from the autocomplete')
    return
  }

  loading.value = true
  try {
    const { data } = await axios.post('/api/job-letters', generateForm.value)
    alert(data.message)
    showGenerateModal.value = false
    resetGenerateForm()
    fetchLetters()
  } catch (error: any) {
    alert(error.response?.data?.message || 'Failed to generate job letter')
  } finally {
    loading.value = false
  }
}

// Send letter via email
const sendEmail = async () => {
  if (!selectedLetter.value) return

  loading.value = true
  try {
    const { data} = await axios.post(
      `/api/job-letters/${selectedLetter.value.id}/send-email`,
      emailForm.value
    )
    alert(data.message)
    showSendEmailModal.value = false
    emailForm.value.email = ''
    fetchLetters()
  } catch (error: any) {
    alert(error.response?.data?.message || 'Failed to send email')
  } finally {
    loading.value = false
  }
}

// Update letter status
const updateStatus = async (letterId: number, status: string) => {
  if (!confirm(`Mark this letter as ${status}?`)) return

  loading.value = true
  try {
    await axios.post(`/api/job-letters/${letterId}/update-status`, { status })
    alert('Status updated successfully')
    fetchLetters()
  } catch (error: any) {
    alert(error.response?.data?.message || 'Failed to update status')
  } finally {
    loading.value = false
  }
}

// Delete letter
const deleteLetter = async (letterId: number) => {
  if (!confirm('Are you sure you want to delete this job letter?')) return

  loading.value = true
  try {
    await axios.delete(`/api/job-letters/${letterId}`)
    alert('Job letter deleted successfully')
    fetchLetters()
  } catch (error: any) {
    alert(error.response?.data?.message || 'Failed to delete letter')
  } finally {
    loading.value = false
  }
}

// Open preview modal
const previewLetter = (letter: JobLetter) => {
  selectedLetter.value = letter
  showPreviewModal.value = true
}

// Open send email modal
const openSendEmailModal = (letter: JobLetter) => {
  selectedLetter.value = letter
  emailForm.value.email = letter.sent_to_email || ''
  showSendEmailModal.value = true
}

// Reset generate form
const resetGenerateForm = () => {
  generateForm.value = {
    employee_id: null,
    employee_name: '',
    recipient_name: '',
    recipient_organization: '',
    recipient_address: '',
    letter_purpose: 'employment_verification',
    notes: ''
  }
  employeeSearchQuery.value = ''
  employees.value = []
  showEmployeeDropdown.value = false
}

// Computed
const filteredLetters = computed(() => {
  return letters.value
})

const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-700',
    sent: 'bg-blue-100 text-blue-700',
    acknowledged: 'bg-green-100 text-green-700'
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

// Close employee dropdown when clicking outside
onClickOutside(employeeDropdownRef, () => {
  showEmployeeDropdown.value = false
})

onMounted(() => {
  fetchLetters()
})
</script>

<template>
  <Head title="Employment Verification Letters" />
  <HRLayout>
    <div class="p-6 min-h-screen bg-white">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Employment Verification Letters</h1>
          <p class="text-sm text-gray-600 mt-1">Generate employment verification letters for embassies, banks, and official purposes</p>
        </div>
        <button
          @click="showGenerateModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
        >
          <i class="fas fa-file-alt"></i>
          Generate Verification Letter
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="searchQuery"
              @input="fetchLetters"
              type="text"
              placeholder="Search by employee name..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="statusFilter"
              @change="fetchLetters"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg"
            >
              <option value="all">All Statuses</option>
              <option value="draft">Draft</option>
              <option value="sent">Sent</option>
              <option value="acknowledged">Acknowledged</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Letters List -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div v-if="loading" class="p-8 text-center text-gray-500">
          <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
          <p>Loading job letters...</p>
        </div>

        <div v-else-if="filteredLetters.length === 0" class="p-8 text-center text-gray-500">
          <i class="fas fa-file-alt text-4xl mb-3 text-gray-300"></i>
          <p class="text-lg font-medium">No employment verification letters found</p>
          <p class="text-sm">Generate your first verification letter to get started</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recipient</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purpose</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Generated</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="letter in filteredLetters" :key="letter.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="font-medium text-gray-900">{{ letter.employee_name }}</div>
                  <div class="text-sm text-gray-500">{{ formatDate(letter.created_at) }}</div>
                </td>
                <td class="px-6 py-4">
                  <div v-if="letter.recipient_name || letter.recipient_organization">
                    <div v-if="letter.recipient_name" class="font-medium text-gray-900">{{ letter.recipient_name }}</div>
                    <div v-if="letter.recipient_organization" class="text-sm text-gray-600">{{ letter.recipient_organization }}</div>
                  </div>
                  <span v-else class="text-gray-400 text-sm">General</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600 capitalize">
                  {{ letter.letter_purpose.replace('_', ' ') }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(letter.letter_date) }}</td>
                <td class="px-6 py-4">
                  <span
                    :class="getStatusColor(letter.status)"
                    class="px-2 py-1 text-xs font-medium rounded-full capitalize"
                  >
                    {{ letter.status }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <button
                      @click="previewLetter(letter)"
                      class="text-blue-600 hover:text-blue-800"
                      title="Preview"
                    >
                      <i class="fas fa-eye"></i>
                    </button>
                    <a
                      :href="`/api/job-letters/${letter.id}/download-pdf`"
                      class="text-orange-600 hover:text-orange-800"
                      title="Download PDF"
                      target="_blank"
                    >
                      <i class="fas fa-file-pdf"></i>
                    </a>
                    <a
                      :href="`/api/job-letters/${letter.id}/download-word`"
                      class="text-indigo-600 hover:text-indigo-800"
                      title="Download Word"
                      target="_blank"
                    >
                      <i class="fas fa-file-word"></i>
                    </a>
                    <button
                      @click="openSendEmailModal(letter)"
                      class="text-green-600 hover:text-green-800"
                      title="Send Email"
                    >
                      <i class="fas fa-envelope"></i>
                    </button>
                    <button
                      v-if="letter.status === 'sent'"
                      @click="updateStatus(letter.id, 'acknowledged')"
                      class="text-purple-600 hover:text-purple-800"
                      title="Mark as Acknowledged"
                    >
                      <i class="fas fa-check-circle"></i>
                    </button>
                    <button
                      @click="deleteLetter(letter.id)"
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

      <!-- Generate Letter Modal -->
      <Teleport to="body">
        <div
          v-if="showGenerateModal"
          class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9998] p-4"
          @click.self="showGenerateModal = false; resetGenerateForm()"
        >
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold">Generate Employment Verification Letter</h2>
            <button @click="showGenerateModal = false; resetGenerateForm()" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form @submit.prevent="generateLetter" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Search Employee *
              </label>
              <div ref="employeeDropdownRef" class="relative">
                <input
                  v-model="employeeSearchQuery"
                  @input="searchEmployees"
                  @focus="showEmployeeDropdown = employees.length > 0"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                  placeholder="Start typing employee name..."
                  autocomplete="off"
                />
                <div
                  v-if="showEmployeeDropdown && employees.length > 0"
                  class="absolute z-[9999] w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
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
                Employee selected: {{ generateForm.employee_name }}
              </p>
              <p v-else class="text-xs text-gray-500 mt-1">
                <i class="fas fa-info-circle"></i>
                Type to search for an employee by name or email
              </p>
            </div>

            <div class="border-t pt-4">
              <h3 class="text-sm font-semibold text-gray-700 mb-3">Recipient Details (Optional)</h3>
              <p class="text-xs text-gray-500 mb-3">
                If this letter is for a specific organization (embassy, bank, etc.), enter their details below.
                Otherwise, leave blank for a general verification letter.
              </p>

              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Recipient Name</label>
                  <input
                    v-model="generateForm.recipient_name"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                    placeholder="e.g., Visa Officer, Loan Manager"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Recipient Organization</label>
                  <input
                    v-model="generateForm.recipient_organization"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                    placeholder="e.g., Embassy of Canada, First National Bank"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Recipient Address</label>
                  <textarea
                    v-model="generateForm.recipient_address"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                    placeholder="Full mailing address (optional)"
                  ></textarea>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
              <textarea
                v-model="generateForm.notes"
                rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                placeholder="Internal notes (not shown on letter)..."
              ></textarea>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <p class="text-sm text-blue-800">
                <i class="fas fa-info-circle mr-1"></i>
                This letter will verify the employee's current employment status, position, and hire date.
                Perfect for visa applications, bank loans, and other official purposes.
              </p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t">
              <button
                type="button"
                @click="showGenerateModal = false; resetGenerateForm()"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="loading || !generateForm.employee_id"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
              >
                <i class="fas fa-file-alt mr-2"></i>
                Generate Verification Letter
              </button>
            </div>
          </form>
        </div>
        </div>
      </Teleport>

      <!-- Preview Modal -->
      <Teleport to="body">
        <div
          v-if="showPreviewModal && selectedLetter"
          class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9998] p-4"
          @click.self="showPreviewModal = false"
        >
          <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-bold">Letter Preview</h2>
              <button @click="showPreviewModal = false" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
              </button>
            </div>

            <div v-html="selectedLetter.letter_html" class="border border-gray-200 rounded-lg p-6"></div>
          </div>
        </div>
      </Teleport>

      <!-- Send Email Modal -->
      <Teleport to="body">
        <div
          v-if="showSendEmailModal && selectedLetter"
          class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9998] p-4"
          @click.self="showSendEmailModal = false"
        >
          <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-bold">Send Letter via Email</h2>
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
      </Teleport>
    </div>
  </HRLayout>
</template>
