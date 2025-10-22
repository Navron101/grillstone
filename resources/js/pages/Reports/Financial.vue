<template>
  <AppLayout title="Financial Reports">
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Financial Reports</h1>
              <p class="mt-1 text-sm text-gray-600">View comprehensive financial statements and analysis</p>
            </div>
          </div>

          <!-- Report Type Selector -->
          <div class="mt-6 flex gap-3">
            <button @click="selectReport('balance-sheet')"
                    :class="selectedReport === 'balance-sheet' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                    class="px-4 py-2 rounded-lg font-medium border border-gray-300 transition-colors">
              <i class="fas fa-balance-scale mr-2"></i>Balance Sheet
            </button>
            <button @click="selectReport('income-statement')"
                    :class="selectedReport === 'income-statement' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                    class="px-4 py-2 rounded-lg font-medium border border-gray-300 transition-colors">
              <i class="fas fa-chart-line mr-2"></i>Income Statement
            </button>
            <button @click="selectReport('profit-and-loss')"
                    :class="selectedReport === 'profit-and-loss' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                    class="px-4 py-2 rounded-lg font-medium border border-gray-300 transition-colors">
              <i class="fas fa-dollar-sign mr-2"></i>Profit & Loss
            </button>
            <button @click="selectReport('cash-flow')"
                    :class="selectedReport === 'cash-flow' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                    class="px-4 py-2 rounded-lg font-medium border border-gray-300 transition-colors">
              <i class="fas fa-money-bill-wave mr-2"></i>Cash Flow
            </button>
          </div>

          <!-- Date Range Selector -->
          <div class="mt-4 bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="flex items-center gap-4 flex-wrap">
              <div v-if="selectedReport === 'balance-sheet'" class="flex items-center gap-3">
                <label class="text-sm font-medium text-gray-700">As of Date:</label>
                <input type="date" v-model="asOfDate" @change="loadReport"
                       class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              </div>
              <div v-else class="flex items-center gap-3">
                <label class="text-sm font-medium text-gray-700">Start Date:</label>
                <input type="date" v-model="startDate" @change="loadReport"
                       class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <label class="text-sm font-medium text-gray-700">End Date:</label>
                <input type="date" v-model="endDate" @change="loadReport"
                       class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              </div>

              <!-- Quick Date Filters -->
              <div class="flex items-center gap-2 ml-auto">
                <button @click="setQuickDate('today')" class="px-3 py-1.5 bg-white hover:bg-gray-100 text-gray-700 rounded-md text-sm border border-gray-300">Today</button>
                <button @click="setQuickDate('week')" class="px-3 py-1.5 bg-white hover:bg-gray-100 text-gray-700 rounded-md text-sm border border-gray-300">This Week</button>
                <button @click="setQuickDate('month')" class="px-3 py-1.5 bg-white hover:bg-gray-100 text-gray-700 rounded-md text-sm border border-gray-300">This Month</button>
                <button @click="setQuickDate('quarter')" class="px-3 py-1.5 bg-white hover:bg-gray-100 text-gray-700 rounded-md text-sm border border-gray-300">This Quarter</button>
                <button @click="setQuickDate('year')" class="px-3 py-1.5 bg-white hover:bg-gray-100 text-gray-700 rounded-md text-sm border border-gray-300">This Year</button>
                <button @click="loadReport" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium">
                  <i class="fas fa-sync-alt mr-2"></i>Refresh
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Report Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
          <i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i>
          <p class="mt-4 text-gray-600">Loading report...</p>
        </div>

        <!-- Balance Sheet -->
        <div v-else-if="selectedReport === 'balance-sheet' && reportData" class="space-y-6">
          <BalanceSheetReport :data="reportData" @export="exportPDF" />
        </div>

        <!-- Income Statement -->
        <div v-else-if="selectedReport === 'income-statement' && reportData" class="space-y-6">
          <IncomeStatementReport :data="reportData" @export="exportPDF" />
        </div>

        <!-- Profit & Loss -->
        <div v-else-if="selectedReport === 'profit-and-loss' && reportData" class="space-y-6">
          <ProfitLossReport :data="reportData" @export="exportPDF" />
        </div>

        <!-- Cash Flow -->
        <div v-else-if="selectedReport === 'cash-flow' && reportData" class="space-y-6">
          <CashFlowReport :data="reportData" @export="exportPDF" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import BalanceSheetReport from '@/components/reports/BalanceSheetReport.vue'
import IncomeStatementReport from '@/components/reports/IncomeStatementReport.vue'
import ProfitLossReport from '@/components/reports/ProfitLossReport.vue'
import CashFlowReport from '@/components/reports/CashFlowReport.vue'

const selectedReport = ref<'balance-sheet' | 'income-statement' | 'profit-and-loss' | 'cash-flow'>('balance-sheet')
const loading = ref(false)
const reportData = ref<any>(null)

// Date filters
const asOfDate = ref(new Date().toISOString().split('T')[0])
const startDate = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0])
const endDate = ref(new Date().toISOString().split('T')[0])

async function loadReport() {
  loading.value = true
  reportData.value = null

  try {
    let url = ''
    const params = new URLSearchParams()

    switch (selectedReport.value) {
      case 'balance-sheet':
        url = '/api/financial-reports/balance-sheet'
        params.append('as_of_date', asOfDate.value)
        break
      case 'income-statement':
        url = '/api/financial-reports/income-statement'
        params.append('start_date', startDate.value)
        params.append('end_date', endDate.value)
        break
      case 'profit-and-loss':
        url = '/api/financial-reports/profit-and-loss'
        params.append('start_date', startDate.value)
        params.append('end_date', endDate.value)
        break
      case 'cash-flow':
        url = '/api/financial-reports/cash-flow'
        params.append('start_date', startDate.value)
        params.append('end_date', endDate.value)
        break
    }

    const resp = await fetch(`${url}?${params.toString()}`)
    if (!resp.ok) throw new Error('Failed to load report')
    reportData.value = await resp.json()
  } catch (e) {
    console.error(e)
    alert('Failed to load report')
  } finally {
    loading.value = false
  }
}

function selectReport(report: typeof selectedReport.value) {
  selectedReport.value = report
  loadReport()
}

function setQuickDate(period: string) {
  const today = new Date()

  switch (period) {
    case 'today':
      if (selectedReport.value === 'balance-sheet') {
        asOfDate.value = today.toISOString().split('T')[0]
      } else {
        startDate.value = today.toISOString().split('T')[0]
        endDate.value = today.toISOString().split('T')[0]
      }
      break
    case 'week': {
      const weekStart = new Date(today)
      weekStart.setDate(today.getDate() - today.getDay())
      startDate.value = weekStart.toISOString().split('T')[0]
      endDate.value = today.toISOString().split('T')[0]
      break
    }
    case 'month': {
      const monthStart = new Date(today.getFullYear(), today.getMonth(), 1)
      startDate.value = monthStart.toISOString().split('T')[0]
      endDate.value = today.toISOString().split('T')[0]
      break
    }
    case 'quarter': {
      const quarter = Math.floor(today.getMonth() / 3)
      const quarterStart = new Date(today.getFullYear(), quarter * 3, 1)
      startDate.value = quarterStart.toISOString().split('T')[0]
      endDate.value = today.toISOString().split('T')[0]
      break
    }
    case 'year': {
      const yearStart = new Date(today.getFullYear(), 0, 1)
      startDate.value = yearStart.toISOString().split('T')[0]
      endDate.value = today.toISOString().split('T')[0]
      break
    }
  }

  loadReport()
}

function exportPDF() {
  alert('PDF export coming soon!')
  // TODO: Implement PDF export
}

onMounted(() => {
  loadReport()
})
</script>
