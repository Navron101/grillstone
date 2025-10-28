<template>
  <LoyaltyLayout>
    <div class="min-h-screen bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Settlements</h1>
            <p class="mt-1 text-gray-600">Generate and manage monthly settlement reports</p>
          </div>
        <button
          @click="showGenerateModal = true"
          class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition"
        >
          <i class="fas fa-plus mr-2"></i>Generate Settlement
        </button>
      </div>

      <!-- Pending Transactions Summary -->
      <div v-if="pendingGroups.length > 0" class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <h3 class="font-semibold text-yellow-900 mb-3 flex items-center gap-2">
          <i class="fas fa-exclamation-triangle"></i>
          Pending Transactions (Not Yet Settled)
        </h3>
        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="group in pendingGroups"
            :key="`${group.company_id}-${group.period}`"
            class="bg-white rounded-lg p-3 border border-yellow-300"
          >
            <div class="font-medium text-gray-900">{{ group.company_name }}</div>
            <div class="text-sm text-gray-600">{{ group.period_label }}</div>
            <div class="mt-2 flex justify-between items-end">
              <div>
                <div class="text-xs text-gray-500">{{ group.transaction_count }} transactions</div>
                <div class="text-lg font-bold text-orange-600">${{ group.total_amount }}</div>
              </div>
              <button
                @click="quickGenerate(group.company_id, group.period)"
                class="px-3 py-1 bg-orange-600 text-white text-sm rounded hover:bg-orange-700"
              >
                Generate
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="mb-4 bg-white p-4 rounded-lg shadow flex gap-4">
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Company</label>
          <select v-model="filterCompany" @change="fetchSettlements" class="w-full px-3 py-2 border rounded-lg">
            <option value="">All Companies</option>
            <option v-for="company in companies" :key="company.id" :value="company.id">
              {{ company.name }}
            </option>
          </select>
        </div>
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
          <select v-model="filterStatus" @change="fetchSettlements" class="w-full px-3 py-2 border rounded-lg">
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="finalized">Finalized</option>
            <option value="sent">Sent</option>
            <option value="paid">Paid</option>
            <option value="partially_paid">Partially Paid</option>
          </select>
        </div>
      </div>

      <!-- Settlements List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transactions</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="settlement in settlements" :key="settlement.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="font-medium text-gray-900">{{ formatPeriod(settlement.period) }}</div>
                <div class="text-xs text-gray-500">{{ settlement.period_start }} to {{ settlement.period_end }}</div>
              </td>
              <td class="px-6 py-4">
                <span class="text-sm text-gray-900">{{ settlement.company?.name }}</span>
              </td>
              <td class="px-6 py-4">
                <span class="text-sm text-gray-900">{{ settlement.transaction_count }}</span>
              </td>
              <td class="px-6 py-4">
                <div class="font-semibold text-gray-900">${{ settlement.total_amount }}</div>
                <div v-if="settlement.amount_paid > 0" class="text-xs text-green-600">
                  Paid: ${{ settlement.amount_paid }}
                </div>
              </td>
              <td class="px-6 py-4">
                <span
                  :class="getStatusClass(settlement.status)"
                  class="px-2 py-1 text-xs font-semibold rounded-full"
                >
                  {{ settlement.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm space-x-2">
                <button @click="viewSettlement(settlement.id)" class="text-blue-600 hover:text-blue-900">
                  <i class="fas fa-eye"></i>
                </button>
                <button
                  v-if="settlement.status === 'draft'"
                  @click="finalizeSettlement(settlement.id)"
                  class="text-green-600 hover:text-green-900"
                  title="Finalize"
                >
                  <i class="fas fa-check-circle"></i>
                </button>
                <button
                  v-if="settlement.status === 'finalized'"
                  @click="markAsSent(settlement.id)"
                  class="text-purple-600 hover:text-purple-900"
                  title="Mark as Sent"
                >
                  <i class="fas fa-paper-plane"></i>
                </button>
                <button
                  v-if="['sent', 'partially_paid'].includes(settlement.status)"
                  @click="recordPayment(settlement)"
                  class="text-green-600 hover:text-green-900"
                  title="Record Payment"
                >
                  <i class="fas fa-dollar-sign"></i>
                </button>
                <button
                  v-if="settlement.status === 'draft'"
                  @click="deleteSettlement(settlement.id)"
                  class="text-red-600 hover:text-red-900"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
            <tr v-if="settlements.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                No settlements found. Generate your first settlement above.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Generate Settlement Modal -->
    <div v-if="showGenerateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-xl font-bold mb-4">Generate Settlement</h3>

        <form @submit.prevent="generateSettlement">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Company *</label>
            <select v-model="generateForm.company_id" required class="w-full px-3 py-2 border rounded-lg">
              <option value="">Select a company</option>
              <option v-for="company in companies" :key="company.id" :value="company.id">
                {{ company.name }}
              </option>
            </select>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Period (YYYY-MM) *</label>
            <input
              v-model="generateForm.period"
              type="month"
              required
              class="w-full px-3 py-2 border rounded-lg"
            />
            <p class="text-xs text-gray-500 mt-1">Select the month to generate settlement for</p>
          </div>

          <div v-if="generateError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
            {{ generateError }}
          </div>

          <div class="flex gap-3">
            <button
              type="button"
              @click="showGenerateModal = false; generateError = ''"
              class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="generating"
              class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 disabled:opacity-50"
            >
              {{ generating ? 'Generating...' : 'Generate' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Record Payment Modal -->
    <div v-if="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-xl font-bold mb-4">Record Payment</h3>

        <div class="mb-4 p-3 bg-gray-50 rounded-lg">
          <div class="text-sm text-gray-600">Settlement Total:</div>
          <div class="text-xl font-bold text-gray-900">${{ paymentForm.settlement?.total_amount }}</div>
          <div class="text-sm text-gray-600 mt-1">Already Paid: ${{ paymentForm.settlement?.amount_paid || 0 }}</div>
          <div class="text-sm font-semibold text-orange-600">Remaining: ${{ (paymentForm.settlement?.total_amount - (paymentForm.settlement?.amount_paid || 0)).toFixed(2) }}</div>
        </div>

        <form @submit.prevent="submitPayment">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Amount *</label>
            <input
              v-model.number="paymentForm.amount"
              type="number"
              step="0.01"
              min="0"
              required
              class="w-full px-3 py-2 border rounded-lg"
              placeholder="0.00"
            />
          </div>

          <div class="flex gap-3">
            <button
              type="button"
              @click="showPaymentModal = false"
              class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="recordingPayment"
              class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
            >
              {{ recordingPayment ? 'Recording...' : 'Record Payment' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Settlement Detail Modal -->
    <div v-if="selectedSettlement" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-start mb-6">
          <div>
            <h3 class="text-2xl font-bold text-gray-900">Settlement Report</h3>
            <p class="text-gray-600">{{ formatPeriod(selectedSettlement.period) }}</p>
          </div>
          <button @click="selectedSettlement = null" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>

        <!-- Company Info -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <div class="text-sm text-gray-600">Company</div>
              <div class="font-semibold text-gray-900">{{ selectedSettlement.company?.name }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-600">Period</div>
              <div class="font-semibold text-gray-900">{{ selectedSettlement.period_start }} to {{ selectedSettlement.period_end }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-600">Total Transactions</div>
              <div class="font-semibold text-gray-900">{{ selectedSettlement.transaction_count }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-600">Total Amount Due</div>
              <div class="text-2xl font-bold text-orange-600">${{ selectedSettlement.total_amount }}</div>
            </div>
          </div>
        </div>

        <!-- Transactions by Employee -->
        <div class="mb-6">
          <h4 class="font-bold text-gray-900 mb-3">Transactions by Employee</h4>
          <div class="space-y-3">
            <div
              v-for="emp in selectedSettlement.transactions_by_employee"
              :key="emp.employee_id"
              class="border rounded-lg p-4"
            >
              <div class="flex justify-between items-start mb-2">
                <div>
                  <div class="font-semibold text-gray-900">{{ emp.employee_name }}</div>
                  <div class="text-xs text-gray-500" v-if="emp.employee_number">ID: {{ emp.employee_number }}</div>
                </div>
                <div class="text-right">
                  <div class="text-lg font-bold text-orange-600">${{ emp.total_discount }}</div>
                  <div class="text-xs text-gray-500">{{ emp.transaction_count }} orders</div>
                </div>
              </div>
              <div class="mt-2 text-xs text-gray-600">
                <div class="font-semibold mb-1">Transactions:</div>
                <div class="space-y-1 max-h-32 overflow-y-auto">
                  <div v-for="t in emp.transactions" :key="t.id" class="flex justify-between py-1 border-b">
                    <span>{{ t.date }} - Order #{{ t.order_id }}</span>
                    <span class="font-semibold">${{ t.discount_amount }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="flex gap-3">
          <button @click="selectedSettlement = null" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
            Close
          </button>
          <a
            :href="`/api/loyalty/settlements/${selectedSettlement.id}/excel`"
            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-center"
            download
          >
            <i class="fas fa-file-excel mr-2"></i>Excel
          </a>
          <a
            :href="`/api/loyalty/settlements/${selectedSettlement.id}/pdf`"
            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-center"
            download
          >
            <i class="fas fa-file-pdf mr-2"></i>PDF
          </a>
        </div>
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
const settlements = ref<any[]>([])
const pendingGroups = ref<any[]>([])
const filterCompany = ref('')
const filterStatus = ref('')
const showGenerateModal = ref(false)
const generating = ref(false)
const generateError = ref('')
const generateForm = ref({
  company_id: '',
  period: new Date().toISOString().slice(0, 7), // Current month YYYY-MM
})

const showPaymentModal = ref(false)
const recordingPayment = ref(false)
const paymentForm = ref({
  settlement: null as any,
  amount: '',
})

const selectedSettlement = ref<any>(null)

const fetchCompanies = async () => {
  try {
    const res = await fetch('/api/loyalty/companies')
    companies.value = await res.json()
  } catch (error) {
    console.error('Error fetching companies:', error)
  }
}

const fetchSettlements = async () => {
  try {
    let url = '/api/loyalty/settlements?'
    if (filterCompany.value) url += `company_id=${filterCompany.value}&`
    if (filterStatus.value) url += `status=${filterStatus.value}`

    const res = await fetch(url)
    settlements.value = await res.json()
  } catch (error) {
    console.error('Error fetching settlements:', error)
  }
}

const fetchPendingTransactions = async () => {
  try {
    const res = await fetch('/api/loyalty/settlements/pending-transactions')
    pendingGroups.value = await res.json()
  } catch (error) {
    console.error('Error fetching pending transactions:', error)
  }
}

const generateSettlement = async () => {
  generating.value = true
  generateError.value = ''

  try {
    const res = await fetch('/api/loyalty/settlements/generate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(generateForm.value),
    })

    if (res.ok) {
      showGenerateModal.value = false
      await fetchSettlements()
      await fetchPendingTransactions()
      generateForm.value = {
        company_id: '',
        period: new Date().toISOString().slice(0, 7),
      }
      alert('Settlement generated successfully!')
    } else {
      const error = await res.json()
      generateError.value = error.message || 'Error generating settlement'
    }
  } catch (error) {
    console.error('Error generating settlement:', error)
    generateError.value = 'Error generating settlement'
  } finally {
    generating.value = false
  }
}

const quickGenerate = (companyId: number, period: string) => {
  generateForm.value = {
    company_id: companyId.toString(),
    period: period,
  }
  showGenerateModal.value = true
}

const viewSettlement = async (id: number) => {
  try {
    const res = await fetch(`/api/loyalty/settlements/${id}`)
    selectedSettlement.value = await res.json()
  } catch (error) {
    console.error('Error fetching settlement details:', error)
  }
}

const finalizeSettlement = async (id: number) => {
  if (!confirm('Finalize this settlement? This action cannot be undone.')) return

  try {
    const res = await fetch(`/api/loyalty/settlements/${id}/finalize`, { method: 'POST' })
    if (res.ok) {
      await fetchSettlements()
      alert('Settlement finalized successfully!')
    }
  } catch (error) {
    console.error('Error finalizing settlement:', error)
  }
}

const markAsSent = async (id: number) => {
  try {
    const res = await fetch(`/api/loyalty/settlements/${id}/mark-sent`, { method: 'POST' })
    if (res.ok) {
      await fetchSettlements()
      alert('Settlement marked as sent!')
    }
  } catch (error) {
    console.error('Error marking as sent:', error)
  }
}

const recordPayment = (settlement: any) => {
  paymentForm.value = {
    settlement: settlement,
    amount: '',
  }
  showPaymentModal.value = true
}

const submitPayment = async () => {
  recordingPayment.value = true

  try {
    const res = await fetch(`/api/loyalty/settlements/${paymentForm.value.settlement.id}/record-payment`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ amount: paymentForm.value.amount }),
    })

    if (res.ok) {
      showPaymentModal.value = false
      await fetchSettlements()
      alert('Payment recorded successfully!')
    }
  } catch (error) {
    console.error('Error recording payment:', error)
  } finally {
    recordingPayment.value = false
  }
}

const deleteSettlement = async (id: number) => {
  if (!confirm('Delete this draft settlement?')) return

  try {
    const res = await fetch(`/api/loyalty/settlements/${id}`, { method: 'DELETE' })
    if (res.ok) {
      await fetchSettlements()
      await fetchPendingTransactions()
      alert('Settlement deleted!')
    }
  } catch (error) {
    console.error('Error deleting settlement:', error)
  }
}

const formatPeriod = (period: string) => {
  const [year, month] = period.split('-')
  const date = new Date(parseInt(year), parseInt(month) - 1)
  return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
}

const getStatusClass = (status: string) => {
  const classes: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    finalized: 'bg-blue-100 text-blue-800',
    sent: 'bg-purple-100 text-purple-800',
    paid: 'bg-green-100 text-green-800',
    partially_paid: 'bg-yellow-100 text-yellow-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

onMounted(() => {
  fetchCompanies()
  fetchSettlements()
  fetchPendingTransactions()
})
</script>
