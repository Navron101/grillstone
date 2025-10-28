<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <a href="/reports" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
            <i class="fas fa-arrow-left text-lg"></i>
          </a>
          <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-chart-pie text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-900">Expenses Report</h1>
            <p class="text-sm text-gray-600">Analyze expenses by category</p>
          </div>
        </div>
      </div>
    </header>

    <div class="max-w-7xl mx-auto p-6">
      <!-- Date Range Filter -->
      <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-3 gap-4 items-end">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">From Date *</label>
            <input v-model="filters.from_date" type="date" required class="w-full px-4 py-2 border rounded-lg" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">To Date *</label>
            <input v-model="filters.to_date" type="date" required class="w-full px-4 py-2 border rounded-lg" />
          </div>
          <div>
            <button
              @click="loadReport"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium"
            >
              <i class="fas fa-search mr-2"></i>Generate Report
            </button>
          </div>
        </div>
      </div>

      <!-- Summary Cards -->
      <div v-if="reportData" class="grid grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
              <i class="fas fa-receipt text-blue-600 text-xl"></i>
            </div>
            <div>
              <p class="text-sm text-gray-600">Total Expenses</p>
              <p class="text-2xl font-bold text-gray-900">{{ reportData.summary.total_expenses }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
              <i class="fas fa-tags text-purple-600 text-xl"></i>
            </div>
            <div>
              <p class="text-sm text-gray-600">Categories</p>
              <p class="text-2xl font-bold text-gray-900">{{ reportData.summary.total_categories }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
              <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
            </div>
            <div>
              <p class="text-sm text-gray-600">Total Amount</p>
              <p class="text-2xl font-bold text-gray-900">
                JMD {{ formatMoney(reportData.summary.total_amount) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Monthly Breakdown -->
      <div v-if="reportData && reportData.monthly_breakdown && reportData.monthly_breakdown.length > 0" class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">
          <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>Monthly Expenses
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div v-for="month in reportData.monthly_breakdown" :key="month.month" class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-4 border border-blue-200">
            <p class="text-sm font-medium text-gray-600">{{ month.month_label }}</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">JMD {{ formatMoney(month.total) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ month.count }} expense(s)</p>
          </div>
        </div>
      </div>

      <!-- Expenses by Category -->
      <div v-if="reportData" class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b">
          <h2 class="text-lg font-bold text-gray-900">Expenses by Category</h2>
        </div>

        <div v-for="category in reportData.report" :key="category.category_id" class="border-b last:border-b-0">
          <div
            class="px-6 py-4 flex items-center justify-between cursor-pointer hover:bg-gray-50"
            @click="toggleCategory(category.category_id)"
          >
            <div class="flex items-center flex-1">
              <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-full flex items-center justify-center mr-4">
                <i class="fas fa-tag text-white text-xs"></i>
              </div>
              <div class="flex-1">
                <p class="font-medium text-gray-900">{{ category.category_name }}</p>
                <p class="text-sm text-gray-500">{{ category.expense_count }} expense(s)</p>
              </div>
            </div>
            <div class="flex items-center gap-6">
              <div class="text-right">
                <p class="font-bold text-gray-900">JMD {{ formatMoney(category.total) }}</p>
                <p class="text-sm text-gray-500">{{ calculatePercentage(category.total) }}%</p>
              </div>
              <i
                :class="expandedCategories.includes(category.category_id) ? 'fa-chevron-up' : 'fa-chevron-down'"
                class="fas text-gray-400"
              ></i>
            </div>
          </div>

          <!-- Expense Details -->
          <div v-if="expandedCategories.includes(category.category_id)" class="bg-gray-50 px-6 py-4">
            <table class="min-w-full">
              <thead>
                <tr class="text-xs text-gray-500 uppercase">
                  <th class="text-left pb-2">Date</th>
                  <th class="text-left pb-2">Description</th>
                  <th class="text-left pb-2">Reference</th>
                  <th class="text-left pb-2">Payment</th>
                  <th class="text-right pb-2">Amount</th>
                </tr>
              </thead>
              <tbody class="text-sm">
                <tr v-for="expense in category.expenses" :key="expense.id" class="border-t border-gray-200">
                  <td class="py-2">{{ formatDate(expense.expense_date) }}</td>
                  <td class="py-2">{{ expense.description || '-' }}</td>
                  <td class="py-2 text-gray-500">{{ expense.reference_number || '-' }}</td>
                  <td class="py-2 text-gray-500">{{ expense.payment_method || '-' }}</td>
                  <td class="py-2 text-right font-medium">JMD {{ formatMoney(expense.amount) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- No Data -->
      <div v-if="!reportData && !loading" class="bg-white rounded-lg shadow p-12 text-center">
        <i class="fas fa-chart-bar text-gray-300 text-6xl mb-4"></i>
        <p class="text-gray-500 text-lg">Select a date range and click "Generate Report" to view expenses</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="bg-white rounded-lg shadow p-12 text-center">
        <i class="fas fa-spinner fa-spin text-blue-600 text-4xl mb-4"></i>
        <p class="text-gray-500">Loading report...</p>
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
import { ref, onMounted } from 'vue'

const reportData = ref(null)
const loading = ref(false)
const expandedCategories = ref([])

// Set default to current month
const now = new Date()
const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0)

const filters = ref({
  from_date: firstDay.toISOString().split('T')[0],
  to_date: lastDay.toISOString().split('T')[0]
})

async function loadReport() {
  if (!filters.value.from_date || !filters.value.to_date) {
    toast('Error', 'Please select date range', 'error')
    return
  }

  loading.value = true
  try {
    const params = new URLSearchParams({
      from_date: filters.value.from_date,
      to_date: filters.value.to_date
    })

    const resp = await fetch(`/api/expenses-report?${params}`)
    if (!resp.ok) throw new Error('Failed to load report')

    const data = await resp.json()
    reportData.value = data
    expandedCategories.value = []
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load report', 'error')
  } finally {
    loading.value = false
  }
}

function toggleCategory(categoryId) {
  const index = expandedCategories.value.indexOf(categoryId)
  if (index > -1) {
    expandedCategories.value.splice(index, 1)
  } else {
    expandedCategories.value.push(categoryId)
  }
}

function calculatePercentage(amount) {
  if (!reportData.value) return 0
  const total = reportData.value.summary.total_amount
  if (total === 0) return 0
  return ((amount / total) * 100).toFixed(1)
}

function formatMoney(amount) {
  return Number(amount).toFixed(2)
}

function formatDate(date) {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
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
  loadReport()
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
