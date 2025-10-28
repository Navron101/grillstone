<template>
  <div class="min-h-screen gradient-bg">
    <!-- Header -->
    <header class="glass-effect shadow-lg">
      <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-chart-line text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-800">Waste Reports</h1>
            <p class="text-sm text-gray-600">Analyze and track waste trends</p>
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <button @click="toggleSidebar" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg" title="Toggle menu">
            <i class="fas" :class="sidebarOpen ? 'fa-angles-left' : 'fa-angles-right'"></i>
          </button>

          <div class="text-right mr-2">
            <p class="text-sm text-gray-600">{{ currentTime }}</p>
          </div>

          <a href="/pos" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg" title="POS">
            <i class="fas fa-cash-register text-lg"></i>
          </a>
        </div>
      </div>
    </header>

    <div class="flex h-[calc(100vh-80px)]">
      <!-- Sidebar -->
      <nav class="glass-effect m-4 rounded-2xl shadow-2xl flex flex-col transition-all duration-300 overflow-hidden"
           :class="sidebarOpen ? 'w-64' : 'w-20'">
        <div class="flex items-center justify-between px-3 py-3">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
              <i class="fas fa-fire text-white text-base"></i>
            </div>
            <span v-if="sidebarOpen" class="font-semibold text-gray-800">Menu</span>
          </div>
          <button @click="toggleSidebar" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
            <i class="fas" :class="sidebarOpen ? 'fa-angles-left' : 'fa-angles-right'"></i>
          </button>
        </div>

        <div class="px-2">
          <ul class="mt-1 space-y-1">
            <li>
              <a href="/pos" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-cash-register text-lg text-gray-600"></i>
                <span v-if="sidebarOpen" class="font-medium">POS</span>
              </a>
            </li>
            <li>
              <a href="/inventory" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-boxes-stacked text-lg text-gray-600"></i>
                <span v-if="sidebarOpen" class="font-medium">Inventory</span>
              </a>
            </li>
            <li>
              <a href="/inventory/waste" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-trash-alt text-lg text-gray-600"></i>
                <span v-if="sidebarOpen" class="font-medium">Waste</span>
              </a>
            </li>
            <li>
              <a href="/reports/waste" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors bg-orange-600 text-white">
                <i class="fas fa-chart-line text-lg text-white"></i>
                <span v-if="sidebarOpen" class="font-medium">Waste Reports</span>
              </a>
            </li>
          </ul>
        </div>

        <div class="mt-auto px-3 py-3 text-xs text-gray-500">
          <div v-if="sidebarOpen">v0.1 • Grillstone</div>
          <div v-else class="text-center">v0.1</div>
        </div>
      </nav>

      <!-- Main Content -->
      <section class="flex-1 p-4 flex flex-col overflow-y-auto">
        <!-- Date Range Filter -->
        <div class="glass-effect rounded-2xl p-4 mb-4 shadow-lg">
          <div class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
              <input v-model="filters.startDate" type="date"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
              <input v-model="filters.endDate" type="date"
                     class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            <button @click="loadAllReports"
                    class="px-6 py-2 bg-gradient-to-r from-purple-500 to-pink-600 text-white rounded-lg hover:from-purple-600 hover:to-pink-700">
              <i class="fas fa-sync-alt mr-2"></i>
              Refresh
            </button>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
          <div class="glass-effect rounded-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 mb-1">Total Waste Cost</p>
                <p class="text-2xl font-bold text-red-600">${{ summary.total_cost?.toFixed(2) || '0.00' }}</p>
              </div>
              <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-dollar-sign text-red-600 text-xl"></i>
              </div>
            </div>
          </div>

          <div class="glass-effect rounded-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 mb-1">Total Records</p>
                <p class="text-2xl font-bold text-gray-800">{{ summary.total_records || 0 }}</p>
              </div>
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-clipboard-list text-blue-600 text-xl"></i>
              </div>
            </div>
          </div>

          <div class="glass-effect rounded-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 mb-1">Unique Products</p>
                <p class="text-2xl font-bold text-gray-800">{{ summary.unique_products || 0 }}</p>
              </div>
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-box text-green-600 text-xl"></i>
              </div>
            </div>
          </div>

          <div class="glass-effect rounded-2xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 mb-1">Daily Average</p>
                <p class="text-2xl font-bold text-orange-600">${{ summary.daily_average_cost?.toFixed(2) || '0.00' }}</p>
              </div>
              <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-day text-orange-600 text-xl"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Waste Trends Chart -->
        <div class="glass-effect rounded-2xl p-6 mb-4 shadow-lg">
          <h2 class="text-lg font-bold text-gray-800 mb-4">Waste Trends Over Time</h2>
          <div v-if="trends.length === 0" class="text-center py-8 text-gray-500">
            <i class="fas fa-chart-line text-4xl mb-2"></i>
            <p>No trend data available</p>
          </div>
          <div v-else class="overflow-x-auto">
            <div class="min-w-[600px]">
              <div v-for="(trend, index) in trends" :key="index" class="mb-3">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-sm font-medium text-gray-700">{{ trend.period }}</span>
                  <span class="text-sm font-bold text-red-600">${{ Number(trend.total_cost).toFixed(2) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div class="bg-gradient-to-r from-red-500 to-orange-600 h-3 rounded-full transition-all duration-500"
                       :style="{ width: `${getPercentage(trend.total_cost)}%` }">
                  </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ trend.waste_count }} records</p>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <!-- Top Wasted Products -->
          <div class="glass-effect rounded-2xl p-6 shadow-lg">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Most Wasted Products</h2>
            <div v-if="topWasted.length === 0" class="text-center py-8 text-gray-500">
              <i class="fas fa-inbox text-4xl mb-2"></i>
              <p>No data available</p>
            </div>
            <div v-else class="space-y-3">
              <div v-for="(item, index) in topWasted" :key="index"
                   class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                    <span class="text-sm font-bold text-red-600">{{ index + 1 }}</span>
                  </div>
                  <div>
                    <p class="font-medium text-gray-800">{{ item.product_name }}</p>
                    <p class="text-xs text-gray-500">{{ item.waste_count }} times • {{ item.total_quantity }} units</p>
                  </div>
                </div>
                <p class="font-bold text-red-600">${{ Number(item.total_cost).toFixed(2) }}</p>
              </div>
            </div>
          </div>

          <!-- Waste by Reason -->
          <div class="glass-effect rounded-2xl p-6 shadow-lg">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Waste by Reason</h2>
            <div v-if="byReason.length === 0" class="text-center py-8 text-gray-500">
              <i class="fas fa-inbox text-4xl mb-2"></i>
              <p>No data available</p>
            </div>
            <div v-else class="space-y-3">
              <div v-for="(item, index) in byReason" :key="index"
                   class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                  <p class="font-medium text-gray-800">{{ item.reason || 'Unknown' }}</p>
                  <p class="text-xs text-gray-500">{{ item.count }} records</p>
                </div>
                <p class="font-bold text-red-600">${{ Number(item.total_cost).toFixed(2) }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

// State
const sidebarOpen = ref(true)
const currentTime = ref('')
const loading = ref(false)

const filters = ref({
  startDate: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
  endDate: new Date().toISOString().split('T')[0],
})

const summary = ref({
  total_cost: 0,
  total_records: 0,
  unique_products: 0,
  daily_average_cost: 0,
})

const trends = ref([])
const topWasted = ref([])
const byReason = ref([])

// Computed
const maxTrendCost = computed(() => {
  if (trends.value.length === 0) return 0
  return Math.max(...trends.value.map(t => Number(t.total_cost)))
})

// Methods
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const updateTime = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  })
}

const getPercentage = (cost) => {
  if (maxTrendCost.value === 0) return 0
  return (Number(cost) / maxTrendCost.value) * 100
}

const loadSummary = async () => {
  try {
    const response = await axios.get('/api/waste-reports/summary', {
      params: {
        start_date: filters.value.startDate,
        end_date: filters.value.endDate,
      }
    })
    summary.value = response.data
  } catch (error) {
    console.error('Error loading summary:', error)
  }
}

const loadTrends = async () => {
  try {
    const response = await axios.get('/api/waste-reports/trends', {
      params: {
        start_date: filters.value.startDate,
        end_date: filters.value.endDate,
        group_by: 'day',
      }
    })
    trends.value = response.data.trends || []
  } catch (error) {
    console.error('Error loading trends:', error)
  }
}

const loadTopWasted = async () => {
  try {
    const response = await axios.get('/api/waste-reports/top-wasted', {
      params: {
        start_date: filters.value.startDate,
        end_date: filters.value.endDate,
        limit: 10,
      }
    })
    topWasted.value = response.data.top_wasted || []
  } catch (error) {
    console.error('Error loading top wasted:', error)
  }
}

const loadByReason = async () => {
  try {
    const response = await axios.get('/api/waste-reports/by-reason', {
      params: {
        start_date: filters.value.startDate,
        end_date: filters.value.endDate,
      }
    })
    byReason.value = response.data.by_reason || []
  } catch (error) {
    console.error('Error loading by reason:', error)
  }
}

const loadAllReports = async () => {
  loading.value = true
  await Promise.all([
    loadSummary(),
    loadTrends(),
    loadTopWasted(),
    loadByReason(),
  ])
  loading.value = false
}

// Lifecycle
onMounted(() => {
  updateTime()
  setInterval(updateTime, 1000)
  loadAllReports()
})
</script>

<style scoped>
.gradient-bg {
  background: linear-gradient(135deg, #f3e7ff 0%, #e7d8ff 100%);
}

.glass-effect {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
}
</style>
