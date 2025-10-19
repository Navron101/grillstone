<template>
  <div class="min-h-screen gradient-bg">
    <!-- Header -->
    <header class="glass-effect shadow-lg">
      <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 bg-gradient-to-r from-orange-600 to-red-700 rounded-lg flex items-center justify-center">
            <i class="fas fa-chart-line text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-800">Reports Dashboard</h1>
            <p class="text-sm text-gray-600">Business Performance & Analytics</p>
          </div>
        </div>

        <div class="flex items-center space-x-3">
          <!-- Period Selector -->
          <select v-model="periodMode" @change="onPeriodModeChange" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
            <option value="preset">Quick Select</option>
            <option value="custom">Custom Range</option>
          </select>

          <!-- Preset Period -->
          <select v-if="periodMode === 'preset'" v-model="selectedPeriod" @change="loadData" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
            <option value="7">Last 7 Days</option>
            <option value="30">Last 30 Days</option>
            <option value="90">Last 90 Days</option>
            <option value="365">Last Year</option>
          </select>

          <!-- Custom Date Range -->
          <div v-if="periodMode === 'custom'" class="flex items-center space-x-2">
            <input
              v-model="customStartDate"
              type="date"
              class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent text-sm"
              :max="customEndDate"
            />
            <span class="text-gray-500">to</span>
            <input
              v-model="customEndDate"
              type="date"
              class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent text-sm"
              :min="customStartDate"
              :max="today"
            />
            <button @click="loadData" class="px-4 py-2 bg-gradient-to-r from-orange-600 to-red-700 text-white rounded-lg hover:from-orange-700 hover:to-red-800 text-sm font-medium">
              Apply
            </button>
          </div>

          <button @click="loadData" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg" title="Refresh">
            <i class="fas fa-refresh text-lg"></i>
          </button>

          <button @click="logout" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg" title="Log out">
            <i class="fas fa-right-from-bracket text-lg"></i>
          </button>
        </div>
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
              <a :href="posHref" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-cash-register text-lg text-gray-600"></i>
              </a>
            </li>

            <li>
              <a :href="inventoryHref" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-boxes-stacked text-lg text-gray-600"></i>
              </a>
            </li>

            <li>
              <a :href="reportsHref" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors bg-orange-600 text-white">
                <i class="fas fa-chart-line text-lg text-white"></i>
              </a>
            </li>
          </ul>
        </div>
      </nav>

      <!-- Main Content -->
      <div class="flex-1 overflow-y-auto p-4">
        <div v-if="loading" class="flex items-center justify-center h-full">
          <div class="text-center">
            <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
            <p class="text-gray-600">Loading reports...</p>
          </div>
        </div>

        <div v-else class="space-y-4">
          <!-- KPI Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Revenue -->
            <div class="glass-effect rounded-2xl p-6 hover:shadow-xl transition-shadow">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-600 font-medium">Total Revenue</p>
                  <p class="text-3xl font-bold text-gray-800 mt-2">JMD {{ nf(overview.total_revenue) }}</p>
                  <p class="text-xs text-green-600 mt-1">
                    <i class="fas fa-arrow-up"></i> {{ overview.total_orders }} orders
                  </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                  <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
              </div>
            </div>

            <!-- Gross Profit -->
            <div class="glass-effect rounded-2xl p-6 hover:shadow-xl transition-shadow">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-600 font-medium">Gross Profit</p>
                  <p class="text-3xl font-bold text-gray-800 mt-2">JMD {{ nf(overview.gross_profit) }}</p>
                  <p class="text-xs text-gray-600 mt-1">
                    Margin: {{ profitMargin }}%
                  </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                  <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                </div>
              </div>
            </div>

            <!-- Avg Order Value -->
            <div class="glass-effect rounded-2xl p-6 hover:shadow-xl transition-shadow">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-600 font-medium">Avg Order Value</p>
                  <p class="text-3xl font-bold text-gray-800 mt-2">JMD {{ nf(overview.avg_order_value) }}</p>
                  <p class="text-xs text-gray-600 mt-1">Per transaction</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                  <i class="fas fa-receipt text-amber-600 text-xl"></i>
                </div>
              </div>
            </div>

            <!-- Total COGS -->
            <div class="glass-effect rounded-2xl p-6 hover:shadow-xl transition-shadow">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-600 font-medium">Total COGS</p>
                  <p class="text-3xl font-bold text-gray-800 mt-2">JMD {{ nf(overview.total_cogs) }}</p>
                  <p class="text-xs text-orange-600 mt-1">Cost of goods sold</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                  <i class="fas fa-box text-orange-600 text-xl"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Charts Row 1 -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Sales Trend Line Chart -->
            <div class="glass-effect rounded-2xl p-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center justify-between">
                <span><i class="fas fa-chart-area text-orange-600 mr-2"></i>Sales Over Time</span>
                <select v-model="chartPeriod" @change="loadData" class="text-sm px-3 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                  <option value="daily">Daily</option>
                  <option value="weekly">Weekly</option>
                  <option value="monthly">Monthly</option>
                </select>
              </h3>
              <div class="h-80">
                <Line :data="salesChartData" :options="salesChartOptions" />
              </div>
            </div>

            <!-- Top Sellers Bar Chart -->
            <div class="glass-effect rounded-2xl p-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-trophy text-amber-600 mr-2"></i>Top Selling Items
              </h3>
              <div class="h-80">
                <Bar :data="topSellersChartData" :options="topSellersChartOptions" />
              </div>
            </div>
          </div>

          <!-- Profit & Loss Chart -->
          <div class="glass-effect rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
              <i class="fas fa-hand-holding-dollar text-green-600 mr-2"></i>Profit & Loss Over Time
            </h3>
            <div class="h-80">
              <Line :data="profitLossChartData" :options="profitLossChartOptions" />
            </div>
          </div>

          <!-- Charts Row 2 -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Category Breakdown Pie Chart -->
            <div class="glass-effect rounded-2xl p-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-chart-pie text-orange-600 mr-2"></i>Sales by Category
              </h3>
              <div class="h-64">
                <Pie :data="categoryChartData" :options="categoryChartOptions" />
              </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="glass-effect rounded-2xl p-6 lg:col-span-2">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>Low Stock Alerts
              </h3>
              <div class="overflow-y-auto max-h-64">
                <table class="w-full text-sm">
                  <thead class="bg-gray-50 sticky top-0">
                    <tr>
                      <th class="px-4 py-2 text-left font-medium text-gray-700">Item</th>
                      <th class="px-4 py-2 text-right font-medium text-gray-700">On Hand</th>
                      <th class="px-4 py-2 text-right font-medium text-gray-700">Threshold</th>
                      <th class="px-4 py-2 text-center font-medium text-gray-700">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="lowStock.length === 0">
                      <td colspan="4" class="px-4 py-3 text-center text-gray-500">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>All items are well stocked
                      </td>
                    </tr>
                    <tr v-for="item in lowStock" :key="item.id" class="border-t border-gray-100 hover:bg-gray-50">
                      <td class="px-4 py-3 font-medium text-gray-800">{{ item.name }}</td>
                      <td class="px-4 py-3 text-right">
                        <span :class="item.on_hand <= 0 ? 'text-red-600 font-bold' : 'text-gray-700'">
                          {{ item.on_hand.toFixed(2) }} {{ item.unit }}
                        </span>
                      </td>
                      <td class="px-4 py-3 text-right text-gray-600">{{ item.threshold.toFixed(2) }}</td>
                      <td class="px-4 py-3 text-center">
                        <span v-if="item.on_hand <= 0" class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                          OUT OF STOCK
                        </span>
                        <span v-else class="px-2 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-medium">
                          LOW STOCK
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Variance Summary -->
          <div v-if="recentVariances.length > 0" class="glass-effect rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
              <i class="fas fa-balance-scale text-orange-600 mr-2"></i>Recent Stocktake Variances
            </h3>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-700">Stocktake</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-700">Date</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-700">Product</th>
                    <th class="px-4 py-2 text-right font-medium text-gray-700">System Qty</th>
                    <th class="px-4 py-2 text-right font-medium text-gray-700">Actual Qty</th>
                    <th class="px-4 py-2 text-right font-medium text-gray-700">Variance</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(variance, idx) in recentVariances" :key="idx" class="border-t border-gray-100 hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-700">{{ variance.stocktake }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ formatDate(variance.counted_at) }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ variance.product }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ variance.system_qty.toFixed(2) }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ variance.actual_qty.toFixed(2) }}</td>
                    <td class="px-4 py-3 text-right">
                      <span :class="variance.variance < 0 ? 'text-red-600' : 'text-green-600'" class="font-medium">
                        {{ variance.variance > 0 ? '+' : '' }}{{ variance.variance.toFixed(2) }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Line, Bar, Pie } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

// Routes
const posHref = '/pos'
const inventoryHref = '/inventory'
const reportsHref = '/reports'

// State
const loading = ref(false)
const periodMode = ref<'preset' | 'custom'>('preset')
const selectedPeriod = ref(30)
const chartPeriod = ref('daily')
const locationId = 1

// Custom date range
const today = new Date().toISOString().split('T')[0]
const customStartDate = ref(new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0])
const customEndDate = ref(today)

// Data
const overview = ref({
  total_orders: 0,
  total_revenue: 0,
  total_cogs: 0,
  gross_profit: 0,
  avg_order_value: 0,
  total_tax: 0,
  total_discount: 0,
})
const dailySales = ref<any[]>([])
const topSellers = ref<any[]>([])
const categoryBreakdown = ref<any[]>([])
const lowStock = ref<any[]>([])
const recentVariances = ref<any[]>([])

// Computed
const profitMargin = computed(() => {
  if (overview.value.total_revenue === 0) return '0.00'
  return ((overview.value.gross_profit / overview.value.total_revenue) * 100).toFixed(2)
})

// Sales Chart Data
const salesChartData = computed(() => ({
  labels: dailySales.value.map(d => d.date),
  datasets: [
    {
      label: 'Revenue (JMD)',
      data: dailySales.value.map(d => d.revenue),
      borderColor: 'rgb(234, 88, 12)',
      backgroundColor: 'rgba(234, 88, 12, 0.1)',
      fill: true,
      tension: 0.4,
    }
  ]
}))

const salesChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: true, position: 'top' as const },
    tooltip: {
      callbacks: {
        label: (context: any) => `JMD ${context.parsed.y.toLocaleString()}`
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: (value: any) => `JMD ${value.toLocaleString()}`
      }
    }
  }
}

// Top Sellers Chart
const topSellersChartData = computed(() => ({
  labels: topSellers.value.map(p => p.name),
  datasets: [
    {
      label: 'Quantity Sold',
      data: topSellers.value.map(p => p.qty_sold),
      backgroundColor: [
        'rgba(255, 99, 132, 0.8)',
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(153, 102, 255, 0.8)',
        'rgba(255, 159, 64, 0.8)',
        'rgba(199, 199, 199, 0.8)',
        'rgba(83, 102, 255, 0.8)',
        'rgba(255, 99, 255, 0.8)',
        'rgba(99, 255, 132, 0.8)',
      ],
    }
  ]
}))

const topSellersChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  indexAxis: 'y' as const,
  plugins: {
    legend: { display: false },
  },
  scales: {
    x: { beginAtZero: true }
  }
}

// Category Pie Chart
const categoryChartData = computed(() => ({
  labels: categoryBreakdown.value.map(c => c.category),
  datasets: [
    {
      data: categoryBreakdown.value.map(c => c.revenue),
      backgroundColor: [
        'rgba(255, 99, 132, 0.8)',
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(153, 102, 255, 0.8)',
        'rgba(255, 159, 64, 0.8)',
      ],
    }
  ]
}))

const categoryChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'right' as const },
    tooltip: {
      callbacks: {
        label: (context: any) => `${context.label}: JMD ${context.parsed.toLocaleString()}`
      }
    }
  }
}

// Profit & Loss Chart
const profitLossChartData = computed(() => {
  // Estimate daily COGS based on profit margin
  const avgMargin = overview.value.total_revenue > 0
    ? (overview.value.gross_profit / overview.value.total_revenue)
    : 0.3 // Default 30% margin if no data

  return {
    labels: dailySales.value.map(d => d.date),
    datasets: [
      {
        label: 'Revenue',
        data: dailySales.value.map(d => d.revenue),
        borderColor: 'rgb(34, 197, 94)',
        backgroundColor: 'rgba(34, 197, 94, 0.1)',
        fill: false,
        tension: 0.4,
      },
      {
        label: 'Estimated COGS',
        data: dailySales.value.map(d => d.revenue * (1 - avgMargin)),
        borderColor: 'rgb(239, 68, 68)',
        backgroundColor: 'rgba(239, 68, 68, 0.1)',
        fill: false,
        tension: 0.4,
      },
      {
        label: 'Gross Profit',
        data: dailySales.value.map(d => d.revenue * avgMargin),
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.2)',
        fill: true,
        tension: 0.4,
      }
    ]
  }
})

const profitLossChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: true, position: 'top' as const },
    tooltip: {
      callbacks: {
        label: (context: any) => `${context.dataset.label}: JMD ${context.parsed.y.toLocaleString()}`
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: (value: any) => `JMD ${value.toLocaleString()}`
      }
    }
  }
}

// Methods
function nf(val: number): string {
  return val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDate(dateStr: string): string {
  if (!dateStr) return 'N/A'
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

async function loadData() {
  loading.value = true
  try {
    let url = `/api/reports/dashboard?location_id=${locationId}`

    if (periodMode.value === 'custom') {
      url += `&start_date=${customStartDate.value}&end_date=${customEndDate.value}`
    } else {
      url += `&days=${selectedPeriod.value}`
    }

    const resp = await fetch(url, {
      headers: { 'Accept': 'application/json' }
    })

    if (!resp.ok) throw new Error('Failed to load reports')

    const data = await resp.json()
    overview.value = data.overview
    dailySales.value = data.daily_sales
    topSellers.value = data.top_sellers
    categoryBreakdown.value = data.category_breakdown
    lowStock.value = data.low_stock
    recentVariances.value = data.recent_variances
  } catch (e) {
    console.error('Failed to load reports:', e)
  } finally {
    loading.value = false
  }
}

function onPeriodModeChange() {
  if (periodMode.value === 'custom') {
    // Set default custom range to last 30 days
    customEndDate.value = today
    customStartDate.value = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
  }
  loadData()
}

function logout() {
  try {
    // @ts-ignore
    const url = typeof route === 'function' ? route('logout') : '/logout'
    router.post(url)
  } catch {
    router.post('/logout')
  }
}

onMounted(() => {
  loadData()
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
</style>
