<template>
  <AppLayout title="Till Settlements">
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Till Settlements</h1>
              <p class="mt-1 text-sm text-gray-600">View and manage all till settlement history</p>
            </div>
            <div class="flex items-center gap-3">
              <button @click="loadSettlements" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors">
                <i class="fas fa-sync-alt mr-2"></i>Refresh
              </button>
            </div>
          </div>

          <!-- Date Filter Section -->
          <div class="mt-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="flex items-center gap-4 flex-wrap">
              <div class="flex items-center gap-2">
                <i class="fas fa-filter text-gray-600"></i>
                <span class="text-sm font-medium text-gray-700">Filter by Date:</span>
              </div>

              <!-- Quick Filters -->
              <div class="flex items-center gap-2">
                <button @click="setDateFilter('today')"
                        :class="filterType === 'today' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                        class="px-3 py-1.5 rounded-md text-sm font-medium border border-gray-300 transition-colors">
                  Today
                </button>
                <button @click="setDateFilter('yesterday')"
                        :class="filterType === 'yesterday' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                        class="px-3 py-1.5 rounded-md text-sm font-medium border border-gray-300 transition-colors">
                  Yesterday
                </button>
                <button @click="setDateFilter('week')"
                        :class="filterType === 'week' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                        class="px-3 py-1.5 rounded-md text-sm font-medium border border-gray-300 transition-colors">
                  This Week
                </button>
                <button @click="setDateFilter('month')"
                        :class="filterType === 'month' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                        class="px-3 py-1.5 rounded-md text-sm font-medium border border-gray-300 transition-colors">
                  This Month
                </button>
                <button @click="setDateFilter('all')"
                        :class="filterType === 'all' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                        class="px-3 py-1.5 rounded-md text-sm font-medium border border-gray-300 transition-colors">
                  All Time
                </button>
              </div>

              <!-- Custom Date Range -->
              <div class="flex items-center gap-2 ml-auto">
                <div class="flex flex-col">
                  <label class="text-xs text-gray-600 mb-1">Start Date</label>
                  <input type="date" v-model="startDate" @change="setDateFilter('custom')"
                         class="px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex flex-col">
                  <label class="text-xs text-gray-600 mb-1">End Date</label>
                  <input type="date" v-model="endDate" @change="setDateFilter('custom')"
                         class="px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <button v-if="filterType === 'custom'" @click="clearDateFilter"
                        class="mt-5 px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-md text-sm font-medium transition-colors">
                  <i class="fas fa-times"></i> Clear
                </button>
              </div>
            </div>

            <!-- Active Filter Display -->
            <div v-if="filterType !== 'all'" class="mt-3 flex items-center gap-2 text-sm text-gray-700">
              <i class="fas fa-info-circle text-blue-600"></i>
              <span class="font-medium">Active Filter:</span>
              <span>{{ getFilterDescription() }}</span>
              <span v-if="filteredCount >= 0" class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                {{ filteredCount }} {{ filteredCount === 1 ? 'settlement' : 'settlements' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
          <i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i>
          <p class="mt-4 text-gray-600">Loading settlements...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="settlements.length === 0" class="text-center py-12">
          <i class="fas fa-cash-register text-6xl text-gray-300"></i>
          <p class="mt-4 text-xl font-medium text-gray-600">No settlements yet</p>
          <p class="mt-2 text-gray-500">Close your first till to see settlements here</p>
        </div>

        <!-- Settlements List -->
        <div v-else class="space-y-4">
          <div v-for="settlement in settlements" :key="settlement.id"
               class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
            <div class="p-6">
              <div class="flex items-start justify-between">
                <!-- Left: Settlement Info -->
                <div class="flex-1">
                  <div class="flex items-center gap-3">
                    <h3 class="text-lg font-semibold text-gray-900">
                      Settlement #{{ settlement.id }}
                    </h3>
                    <span class="px-2 py-1 text-xs font-medium rounded-full"
                          :class="settlement.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                      {{ settlement.status }}
                    </span>
                    <span v-if="settlement.cash_variance_cents !== 0"
                          class="px-2 py-1 text-xs font-medium rounded-full"
                          :class="settlement.cash_variance_cents > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                      {{ settlement.cash_variance_cents > 0 ? 'OVER' : 'SHORT' }}: {{ formatMoney(Math.abs(settlement.cash_variance_cents)) }}
                    </span>
                    <span v-else class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                      PERFECT MATCH
                    </span>
                  </div>

                  <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                      <p class="text-gray-500">Date</p>
                      <p class="font-medium text-gray-900">{{ formatDate(settlement.settlement_date) }}</p>
                    </div>
                    <div>
                      <p class="text-gray-500">Total Sales</p>
                      <p class="font-medium text-gray-900">{{ formatMoney(settlement.total_sales_cents) }}</p>
                    </div>
                    <div>
                      <p class="text-gray-500">Transactions</p>
                      <p class="font-medium text-gray-900">{{ settlement.num_transactions }}</p>
                    </div>
                    <div>
                      <p class="text-gray-500">Cashier</p>
                      <p class="font-medium text-gray-900">{{ settlement.user_name || 'Unknown' }}</p>
                    </div>
                  </div>

                  <div v-if="settlement.notes" class="mt-3 text-sm text-gray-600">
                    <i class="fas fa-sticky-note mr-1"></i>{{ settlement.notes }}
                  </div>
                </div>

                <!-- Right: Actions -->
                <div class="flex flex-col gap-2 ml-4">
                  <button @click="viewSettlement(settlement.id)"
                          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg font-medium transition-colors">
                    <i class="fas fa-eye mr-2"></i>View
                  </button>
                  <button @click="downloadPdf(settlement.id)"
                          class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg font-medium transition-colors">
                    <i class="fas fa-download mr-2"></i>Download
                  </button>
                  <button @click="printSettlement(settlement.id)"
                          class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg font-medium transition-colors">
                    <i class="fas fa-print mr-2"></i>Print
                  </button>
                </div>
              </div>

              <!-- Quick Stats -->
              <div class="mt-4 pt-4 border-t border-gray-200 grid grid-cols-3 gap-4 text-sm">
                <div class="text-center">
                  <p class="text-gray-500">Cash</p>
                  <p class="font-semibold text-gray-900">{{ formatMoney(settlement.net_cash_cents) }}</p>
                </div>
                <div class="text-center">
                  <p class="text-gray-500">Card</p>
                  <p class="font-semibold text-gray-900">{{ formatMoney(settlement.net_card_cents) }}</p>
                </div>
                <div class="text-center">
                  <p class="text-gray-500">Profit</p>
                  <p class="font-semibold text-green-600">{{ formatMoney(settlement.profit_cents) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Settlement Detail Modal -->
    <div v-if="showDetailModal && selectedSettlement"
         class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50 p-4"
         @click.self="showDetailModal = false">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4 rounded-t-2xl sticky top-0 z-10">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <i class="fas fa-file-invoice-dollar text-2xl"></i>
              <h2 class="text-xl font-bold">Settlement #{{ selectedSettlement.settlement.id }}</h2>
            </div>
            <button @click="showDetailModal = false" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2">
              <i class="fas fa-times text-lg"></i>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6">
          <!-- Variance Alert -->
          <div v-if="selectedSettlement.settlement.cash_variance_cents !== 0"
               class="rounded-lg p-4 border-2"
               :class="selectedSettlement.settlement.cash_variance_cents > 0 ? 'bg-green-50 border-green-500' : 'bg-red-50 border-red-500'">
            <div class="flex items-center gap-3">
              <i class="text-3xl" :class="selectedSettlement.settlement.cash_variance_cents > 0 ? 'fas fa-circle-check text-green-600' : 'fas fa-circle-exclamation text-red-600'"></i>
              <div>
                <h3 class="font-bold text-lg" :class="selectedSettlement.settlement.cash_variance_cents > 0 ? 'text-green-900' : 'text-red-900'">
                  {{ selectedSettlement.settlement.cash_variance_cents > 0 ? 'CASH OVER' : 'CASH SHORT' }}
                </h3>
                <p class="text-sm" :class="selectedSettlement.settlement.cash_variance_cents > 0 ? 'text-green-700' : 'text-red-700'">
                  Variance: {{ selectedSettlement.settlement.cash_variance_cents > 0 ? '+' : '' }}{{ formatMoney(selectedSettlement.settlement.cash_variance_cents) }}
                </p>
              </div>
            </div>
          </div>

          <div v-else class="bg-green-50 border-2 border-green-500 rounded-lg p-4">
            <div class="flex items-center gap-3">
              <i class="fas fa-circle-check text-3xl text-green-600"></i>
              <div>
                <h3 class="font-bold text-lg text-green-900">PERFECT MATCH!</h3>
                <p class="text-sm text-green-700">Cash count matches expected amount exactly</p>
              </div>
            </div>
          </div>

          <!-- Cash Breakdown -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
              <i class="fas fa-money-bill-wave text-green-600"></i>
              Cash Breakdown
            </h3>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-600">Expected Cash:</span>
                <span class="font-semibold">{{ formatMoney(selectedSettlement.settlement.expected_cash_cents) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Paid Out:</span>
                <span class="font-semibold text-red-600">-{{ formatMoney(selectedSettlement.settlement.paid_out_cents) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Paid In:</span>
                <span class="font-semibold text-green-600">+{{ formatMoney(selectedSettlement.settlement.paid_in_cents) }}</span>
              </div>
              <div class="flex justify-between pt-2 border-t border-gray-300">
                <span class="font-semibold text-gray-700">Net Cash Expected:</span>
                <span class="font-bold">{{ formatMoney(selectedSettlement.settlement.net_cash_cents) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="font-semibold text-gray-700">Actual Cash Counted:</span>
                <span class="font-bold">{{ formatMoney(selectedSettlement.settlement.actual_cash_cents) }}</span>
              </div>
              <div class="flex justify-between pt-2 border-t-2 border-gray-400"
                   :class="selectedSettlement.settlement.cash_variance_cents > 0 ? 'text-green-600' : selectedSettlement.settlement.cash_variance_cents < 0 ? 'text-red-600' : 'text-gray-900'">
                <span class="font-bold">Variance:</span>
                <span class="font-bold">{{ selectedSettlement.settlement.cash_variance_cents > 0 ? '+' : '' }}{{ formatMoney(selectedSettlement.settlement.cash_variance_cents) }}</span>
              </div>
            </div>
          </div>

          <!-- Payment Methods Summary -->
          <div class="grid grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-lg p-4">
              <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-money-bill text-green-600"></i>
                <span class="text-sm font-semibold text-gray-700">Cash</span>
              </div>
              <p class="text-xl font-bold text-gray-900">{{ formatMoney(selectedSettlement.settlement.net_cash_cents) }}</p>
            </div>

            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
              <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-credit-card text-blue-600"></i>
                <span class="text-sm font-semibold text-gray-700">Credit/Debit</span>
              </div>
              <p class="text-xl font-bold text-gray-900">{{ formatMoney(selectedSettlement.settlement.expected_card_cents) }}</p>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-4">
              <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-gift text-purple-600"></i>
                <span class="text-sm font-semibold text-gray-700">Gift Cards</span>
              </div>
              <p class="text-xl font-bold text-gray-900">{{ formatMoney(selectedSettlement.settlement.expected_gift_card_cents || 0) }}</p>
            </div>
          </div>

          <!-- Sales Summary -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
              <i class="fas fa-chart-line text-orange-600"></i>
              Sales Summary
            </h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-600">Total Sales:</span>
                <span class="font-semibold">{{ formatMoney(selectedSettlement.settlement.total_sales_cents) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Number of Transactions:</span>
                <span class="font-semibold">{{ selectedSettlement.settlement.num_transactions }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Cost of Goods Sold:</span>
                <span class="font-semibold">{{ formatMoney(selectedSettlement.settlement.cogs_cents || 0) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Gross Profit:</span>
                <span class="font-semibold text-green-600">{{ formatMoney(selectedSettlement.settlement.profit_cents) }}</span>
              </div>
            </div>
          </div>

          <!-- Period Info -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-bold text-blue-900 mb-2">Settlement Period</h3>
            <div class="text-sm text-blue-800">
              <p><strong>Start:</strong> {{ formatDateTime(selectedSettlement.settlement.period_start) }}</p>
              <p><strong>End:</strong> {{ formatDateTime(selectedSettlement.settlement.period_end) }}</p>
              <p><strong>Settlement Date:</strong> {{ formatDateTime(selectedSettlement.settlement.settlement_date) }}</p>
              <p v-if="selectedSettlement.settlement.user_name"><strong>Cashier:</strong> {{ selectedSettlement.settlement.user_name }}</p>
            </div>
          </div>

          <!-- Credit Card Transactions -->
          <div v-if="selectedSettlement.card_transactions && selectedSettlement.card_transactions.length > 0" class="bg-gray-50 rounded-lg p-4">
            <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
              <i class="fas fa-credit-card text-blue-600"></i>
              Credit Card Transactions ({{ selectedSettlement.card_transactions.length }})
            </h3>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead class="bg-gray-200">
                  <tr>
                    <th class="px-3 py-2 text-left">Time</th>
                    <th class="px-3 py-2 text-left">Order #</th>
                    <th class="px-3 py-2 text-left">Reference</th>
                    <th class="px-3 py-2 text-right">Amount</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="txn in selectedSettlement.card_transactions" :key="txn.id" class="hover:bg-gray-100">
                    <td class="px-3 py-2">{{ formatTime(txn.created_at) }}</td>
                    <td class="px-3 py-2">#{{ txn.order_id }}</td>
                    <td class="px-3 py-2">{{ txn.reference || '-' }}</td>
                    <td class="px-3 py-2 text-right font-medium">{{ formatMoney(txn.amount_cents) }}</td>
                  </tr>
                </tbody>
                <tfoot class="bg-gray-200 font-bold">
                  <tr>
                    <td colspan="3" class="px-3 py-2">TOTAL</td>
                    <td class="px-3 py-2 text-right">{{ formatMoney(selectedSettlement.settlement.expected_card_cents) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="selectedSettlement.settlement.notes" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h3 class="font-bold text-yellow-900 mb-2">Notes</h3>
            <p class="text-sm text-yellow-800">{{ selectedSettlement.settlement.notes }}</p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 px-6 pb-6">
          <button @click="downloadPdf(selectedSettlement.settlement.id)"
                  class="flex-1 px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold">
            <i class="fas fa-download mr-2"></i>Download PDF
          </button>
          <button @click="printSettlement(selectedSettlement.settlement.id)"
                  class="flex-1 px-4 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold">
            <i class="fas fa-print mr-2"></i>Print Report
          </button>
          <button @click="showDetailModal = false"
                  class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">
            <i class="fas fa-check mr-2"></i>Close
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'

const settlements = ref<any[]>([])
const loading = ref(false)
const showDetailModal = ref(false)
const selectedSettlement = ref<any>(null)

// Date filter state
const filterType = ref<'all' | 'today' | 'yesterday' | 'week' | 'month' | 'custom'>('all')
const startDate = ref('')
const endDate = ref('')

const filteredCount = computed(() => {
  return filterType.value !== 'all' ? settlements.value.length : -1
})

async function loadSettlements() {
  loading.value = true
  try {
    const params = new URLSearchParams()

    // Add date filters based on filter type
    if (filterType.value !== 'all') {
      const { start, end } = getDateRange()
      if (start) params.append('start_date', start)
      if (end) params.append('end_date', end)
    }

    const url = `/api/settlements${params.toString() ? '?' + params.toString() : ''}`
    const resp = await fetch(url)
    if (!resp.ok) throw new Error('Failed to load settlements')
    settlements.value = await resp.json()
  } catch (e) {
    console.error(e)
    alert('Failed to load settlements')
  } finally {
    loading.value = false
  }
}

function getDateRange(): { start: string | null, end: string | null } {
  const today = new Date()
  const formatDate = (date: Date) => date.toISOString().split('T')[0]

  switch (filterType.value) {
    case 'today':
      return { start: formatDate(today), end: formatDate(today) }

    case 'yesterday': {
      const yesterday = new Date(today)
      yesterday.setDate(yesterday.getDate() - 1)
      return { start: formatDate(yesterday), end: formatDate(yesterday) }
    }

    case 'week': {
      const weekStart = new Date(today)
      weekStart.setDate(today.getDate() - today.getDay()) // Start of week (Sunday)
      return { start: formatDate(weekStart), end: formatDate(today) }
    }

    case 'month': {
      const monthStart = new Date(today.getFullYear(), today.getMonth(), 1)
      return { start: formatDate(monthStart), end: formatDate(today) }
    }

    case 'custom':
      return { start: startDate.value || null, end: endDate.value || null }

    default:
      return { start: null, end: null }
  }
}

function setDateFilter(type: typeof filterType.value) {
  filterType.value = type

  // Pre-fill custom dates for quick filters
  if (type !== 'custom' && type !== 'all') {
    const { start, end } = getDateRange()
    startDate.value = start || ''
    endDate.value = end || ''
  }

  loadSettlements()
}

function clearDateFilter() {
  filterType.value = 'all'
  startDate.value = ''
  endDate.value = ''
  loadSettlements()
}

function getFilterDescription(): string {
  switch (filterType.value) {
    case 'today':
      return 'Showing settlements from today'
    case 'yesterday':
      return 'Showing settlements from yesterday'
    case 'week':
      return 'Showing settlements from this week'
    case 'month':
      return 'Showing settlements from this month'
    case 'custom': {
      if (startDate.value && endDate.value) {
        return `Showing settlements from ${formatDate(startDate.value)} to ${formatDate(endDate.value)}`
      } else if (startDate.value) {
        return `Showing settlements from ${formatDate(startDate.value)} onwards`
      } else if (endDate.value) {
        return `Showing settlements up to ${formatDate(endDate.value)}`
      }
      return 'Custom date range'
    }
    default:
      return 'Showing all settlements'
  }
}

async function viewSettlement(id: number) {
  try {
    const resp = await fetch(`/api/settlements/${id}`)
    if (!resp.ok) throw new Error('Failed to load settlement')
    selectedSettlement.value = await resp.json()
    showDetailModal.value = true
  } catch (e) {
    console.error(e)
    alert('Failed to load settlement details')
  }
}

function downloadPdf(id: number) {
  // Download PDF file
  window.location.href = `/api/settlements/${id}/pdf`
}

function printSettlement(id: number) {
  // Open settlement in new window for printing
  const printUrl = `/settlements/${id}/print`
  const printWindow = window.open(printUrl, '_blank', 'width=800,height=600')
  if (printWindow) {
    printWindow.addEventListener('load', () => {
      printWindow.print()
    })
  }
}

function formatMoney(cents: number): string {
  return 'JMD ' + (cents / 100).toLocaleString('en-JM', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDate(dateStr: string): string {
  try {
    return new Date(dateStr).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  } catch {
    return dateStr
  }
}

function formatDateTime(dateStr: string): string {
  try {
    return new Date(dateStr).toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return dateStr
  }
}

function formatTime(dateStr: string): string {
  try {
    return new Date(dateStr).toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return dateStr
  }
}

onMounted(loadSettlements)
</script>
