<template>
  <div class="min-h-screen gradient-bg">
    <!-- Header -->
    <header class="glass-effect shadow-lg">
      <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-money-check-dollar text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-800">Payroll Management</h1>
            <p class="text-sm text-gray-600">Manage pay periods and payslips</p>
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <button @click="toggleSidebar" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg" title="Toggle menu">
            <i class="fas" :class="sidebarOpen ? 'fa-angles-left' : 'fa-angles-right'"></i>
          </button>

          <div class="text-right mr-2">
            <p class="text-sm text-gray-600">{{ currentTime }}</p>
          </div>

          <a :href="posHref" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg" title="POS">
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
            <div class="w-9 h-9 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg flex items-center justify-center">
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
              <a :href="posHref" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors"
                 :class="isActive('/pos') ? 'bg-green-600 text-white' : 'text-gray-700 hover:bg-green-50 hover:text-green-700'">
                <i :class="['fas fa-cash-register text-lg', isActive('/pos') ? 'text-white' : 'text-gray-600']"></i>
                <span v-if="sidebarOpen" class="font-medium">POS</span>
              </a>
            </li>
            <li>
              <a href="/inventory" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-green-50 hover:text-green-700">
                <i class="fas fa-boxes-stacked text-lg text-gray-600"></i>
                <span v-if="sidebarOpen" class="font-medium">Inventory</span>
              </a>
            </li>
            <li>
              <a href="/payroll" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors bg-green-600 text-white">
                <i class="fas fa-money-check-dollar text-lg text-white"></i>
                <span v-if="sidebarOpen" class="font-medium">Payroll</span>
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
        <!-- Filter Bar -->
        <div class="glass-effect rounded-2xl p-4 mb-4 shadow-lg">
          <div class="flex items-center justify-between">
            <div class="flex gap-2">
              <button @click="statusFilter = 'all'"
                      class="px-4 py-2 rounded-lg font-medium transition-colors"
                      :class="statusFilter === 'all' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-green-50'">
                <i class="fas fa-list mr-2"></i>All
              </button>
              <button @click="statusFilter = 'draft'"
                      class="px-4 py-2 rounded-lg font-medium transition-colors"
                      :class="statusFilter === 'draft' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-green-50'">
                <i class="fas fa-file-lines mr-2"></i>Draft
              </button>
              <button @click="statusFilter = 'processing'"
                      class="px-4 py-2 rounded-lg font-medium transition-colors"
                      :class="statusFilter === 'processing' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-green-50'">
                <i class="fas fa-spinner mr-2"></i>Processing
              </button>
              <button @click="statusFilter = 'approved'"
                      class="px-4 py-2 rounded-lg font-medium transition-colors"
                      :class="statusFilter === 'approved' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-green-50'">
                <i class="fas fa-check-circle mr-2"></i>Approved
              </button>
              <button @click="statusFilter = 'paid'"
                      class="px-4 py-2 rounded-lg font-medium transition-colors"
                      :class="statusFilter === 'paid' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-green-50'">
                <i class="fas fa-dollar-sign mr-2"></i>Paid
              </button>
            </div>

            <div class="flex gap-2">
              <button @click="autoGenerateFortnight"
                      :disabled="autoGenerating"
                      class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                <i v-if="autoGenerating" class="fas fa-spinner fa-spin mr-2"></i>
                <i v-else class="fas fa-magic mr-2"></i>
                {{ autoGenerating ? 'Generating...' : 'Auto-Generate Fortnight' }}
              </button>
              <button @click="showCreateModal = true"
                      class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium shadow-md">
                <i class="fas fa-plus mr-2"></i>Create New Period
              </button>
            </div>
          </div>
        </div>

        <!-- Period Cards -->
        <div class="glass-effect rounded-2xl p-6 shadow-lg">
          <div v-if="loading" class="text-center py-8 text-gray-500">
            <i class="fas fa-spinner fa-spin text-4xl mb-3"></i>
            <p>Loading payroll periods...</p>
          </div>

          <div v-else-if="!filteredPeriods.length" class="text-center py-8 text-gray-400">
            <i class="fas fa-money-check-dollar text-4xl mb-3"></i>
            <p>No payroll periods found</p>
            <button @click="showCreateModal = true" class="mt-4 text-green-600 hover:underline">
              Create your first payroll period
            </button>
          </div>

          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="period in filteredPeriods" :key="period.id"
                 class="period-card glass-effect rounded-xl p-5 hover:shadow-xl transition-all duration-300 border border-gray-200">
              <!-- Status Badge -->
              <div class="flex items-center justify-between mb-3">
                <span class="px-3 py-1 text-xs font-semibold rounded-full"
                      :class="statusBadgeClass(period.status)">
                  <i :class="statusIcon(period.status)" class="mr-1"></i>
                  {{ period.status.toUpperCase() }}
                </span>
                <span class="text-xs text-gray-500">#{{ period.id }}</span>
              </div>

              <!-- Period Info -->
              <div class="mb-4">
                <div class="flex items-center text-gray-700 mb-2">
                  <i class="fas fa-calendar text-green-600 mr-2"></i>
                  <span class="font-semibold">Period:</span>
                </div>
                <p class="text-sm text-gray-600 ml-6">
                  {{ formatDate(period.start_date) }} to {{ formatDate(period.end_date) }}
                </p>
              </div>

              <div class="mb-4">
                <div class="flex items-center text-gray-700 mb-2">
                  <i class="fas fa-money-bill-wave text-green-600 mr-2"></i>
                  <span class="font-semibold">Pay Date:</span>
                </div>
                <p class="text-sm text-gray-600 ml-6">
                  {{ formatDate(period.pay_date) }}
                </p>
              </div>

              <div class="mb-4">
                <div class="flex items-center text-gray-700 mb-2">
                  <i class="fas fa-receipt text-green-600 mr-2"></i>
                  <span class="font-semibold">Payslips:</span>
                </div>
                <p class="text-sm text-gray-600 ml-6">
                  {{ period.payslips_count || 0 }} {{ period.payslips_count === 1 ? 'employee' : 'employees' }}
                </p>
              </div>

              <!-- Notes (if any) -->
              <div v-if="period.notes" class="mb-4 p-3 bg-amber-50 rounded-lg border border-amber-200">
                <p class="text-xs text-amber-800">
                  <i class="fas fa-sticky-note mr-1"></i>
                  {{ period.notes }}
                </p>
              </div>

              <!-- Action Button -->
              <div class="pt-3 border-t border-gray-200">
                <a :href="`/payroll/periods/${period.id}`"
                   class="block w-full text-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                  <i class="fas fa-eye mr-2"></i>View Details
                </a>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Create Period Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeCreateModal">
      <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-bold mb-4 text-gray-800">Create New Payroll Period</h3>

        <form @submit.prevent="createPeriod" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
            <input v-model="periodForm.start_date" type="date" required
                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
            <input v-model="periodForm.end_date" type="date" required
                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pay Date *</label>
            <input v-model="periodForm.pay_date" type="date" required
                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
            <textarea v-model="periodForm.notes" rows="3"
                      class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
                      placeholder="Any additional notes about this pay period..."></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <button type="button" @click="closeCreateModal"
                    class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit" :disabled="creating"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
              <i v-if="creating" class="fas fa-spinner fa-spin mr-1"></i>
              {{ creating ? 'Creating...' : 'Create Period' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Toast Notification -->
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
import { ref, onMounted, watch, computed } from 'vue'

// Sidebar
const sidebarOpen = ref(true)
function toggleSidebar() { sidebarOpen.value = !sidebarOpen.value }
onMounted(() => {
  const saved = localStorage.getItem('sidebarOpen')
  if (saved !== null) sidebarOpen.value = saved === '1'
})
watch(sidebarOpen, v => localStorage.setItem('sidebarOpen', v ? '1' : '0'))

// Routes
function isActive(path: string) { return window.location.pathname.startsWith(path) }
function routeUrl(name: string, fallback: string) {
  try { if (typeof (window as any).route === 'function') return (window as any).route(name) } catch {}
  return fallback
}
const posHref = routeUrl('pos.index', '/pos')

// Clock
const currentTime = ref('')
function tick() { currentTime.value = new Date().toLocaleTimeString() }
onMounted(() => { tick(); setInterval(tick, 1000) })

// Types
type PayrollPeriod = {
  id: number
  start_date: string
  end_date: string
  pay_date: string
  status: 'draft' | 'processing' | 'approved' | 'paid'
  notes: string | null
  payslips_count: number
  created_at?: string
  updated_at?: string
}

// Data
const periods = ref<PayrollPeriod[]>([])
const loading = ref(false)
const statusFilter = ref<'all' | 'draft' | 'processing' | 'approved' | 'paid'>('all')

// Filtered periods
const filteredPeriods = computed(() => {
  if (statusFilter.value === 'all') {
    return periods.value
  }
  return periods.value.filter(p => p.status === statusFilter.value)
})

// Load periods
async function loadPeriods() {
  loading.value = true
  try {
    const url = statusFilter.value === 'all'
      ? '/api/payroll/periods'
      : `/api/payroll/periods?status=${statusFilter.value}`

    const resp = await fetch(url)
    if (!resp.ok) throw new Error('Failed to load payroll periods')
    periods.value = await resp.json()

    // Sort by pay_date descending (newest first)
    periods.value.sort((a, b) => {
      return new Date(b.pay_date).getTime() - new Date(a.pay_date).getTime()
    })
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load payroll periods', 'error')
  } finally {
    loading.value = false
  }
}

// Watch status filter
watch(statusFilter, () => {
  loadPeriods()
})

// Create modal
const showCreateModal = ref(false)
const creating = ref(false)
const autoGenerating = ref(false)
const periodForm = ref({
  start_date: '',
  end_date: '',
  pay_date: '',
  notes: ''
})

function closeCreateModal() {
  showCreateModal.value = false
  periodForm.value = {
    start_date: '',
    end_date: '',
    pay_date: '',
    notes: ''
  }
}

async function createPeriod() {
  creating.value = true
  try {
    const payload = {
      start_date: periodForm.value.start_date,
      end_date: periodForm.value.end_date,
      pay_date: periodForm.value.pay_date,
      notes: periodForm.value.notes || null
    }

    const resp = await fetch('/api/payroll/periods', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })

    if (!resp.ok) {
      const errorData = await resp.json().catch(() => ({}))
      throw new Error(errorData.message || 'Failed to create payroll period')
    }

    toast('Success', 'Payroll period created successfully', 'success')
    closeCreateModal()
    await loadPeriods()
  } catch (e) {
    console.error(e)
    toast('Error', e instanceof Error ? e.message : 'Failed to create payroll period', 'error')
  } finally {
    creating.value = false
  }
}

// Auto-generate fortnight period (Monday 2 weeks ago to today)
async function autoGenerateFortnight() {
  autoGenerating.value = true
  try {
    const today = new Date()

    // Calculate Monday 2 weeks ago (14 days ago)
    const twoWeeksAgo = new Date(today)
    twoWeeksAgo.setDate(today.getDate() - 14)

    // Adjust to the previous Monday
    const dayOfWeek = twoWeeksAgo.getDay()
    const daysToMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1 // If Sunday (0), go back 6 days; otherwise go back (dayOfWeek - 1) days
    twoWeeksAgo.setDate(twoWeeksAgo.getDate() - daysToMonday)

    // Format dates as YYYY-MM-DD
    const startDate = twoWeeksAgo.toISOString().split('T')[0]
    const endDate = today.toISOString().split('T')[0]
    const payDate = today.toISOString().split('T')[0]

    const payload = {
      start_date: startDate,
      end_date: endDate,
      pay_date: payDate,
      notes: 'Auto-generated fortnight period'
    }

    const resp = await fetch('/api/payroll/periods', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })

    if (!resp.ok) {
      const errorData = await resp.json().catch(() => ({}))
      throw new Error(errorData.message || 'Failed to create payroll period')
    }

    const newPeriod = await resp.json()
    toast('Success', `Fortnight period created (${formatDate(startDate)} - ${formatDate(endDate)})`, 'success')

    // Redirect to the new period detail page
    window.location.href = `/payroll/periods/${newPeriod.id}`
  } catch (e) {
    console.error(e)
    toast('Error', e instanceof Error ? e.message : 'Failed to auto-generate period', 'error')
  } finally {
    autoGenerating.value = false
  }
}

// Utility functions
function formatDate(dateStr: string) {
  try {
    const date = new Date(dateStr)
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
  } catch {
    return dateStr
  }
}

function statusBadgeClass(status: string) {
  const classes: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-700',
    processing: 'bg-orange-100 text-orange-700',
    approved: 'bg-green-100 text-green-700',
    paid: 'bg-blue-100 text-blue-700'
  }
  return classes[status] || 'bg-gray-100 text-gray-700'
}

function statusIcon(status: string) {
  const icons: Record<string, string> = {
    draft: 'fas fa-file-lines',
    processing: 'fas fa-spinner fa-spin',
    approved: 'fas fa-check-circle',
    paid: 'fas fa-dollar-sign'
  }
  return icons[status] || 'fas fa-circle'
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

// Init
onMounted(() => {
  loadPeriods()
})
</script>

<style scoped>
.gradient-bg {
  background: linear-gradient(135deg, #10b981 0%, #059669 25%, #047857 50%, #065f46 75%, #064e3b 100%);
}

.glass-effect {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.period-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.period-card:hover {
  transform: translateY(-2px);
}

.notification {
  position: fixed;
  bottom: 20px;
  right: 20px;
  opacity: 0;
  transform: translateY(20px);
  transition: all 0.3s ease;
  pointer-events: none;
  z-index: 9999;
}

.notification.show {
  opacity: 1;
  transform: translateY(0);
  pointer-events: all;
}
</style>
