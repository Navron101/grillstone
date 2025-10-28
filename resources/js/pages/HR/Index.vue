<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-indigo-50 flex">
    <!-- Sidebar -->
    <nav class="w-20 hover:w-64 transition-all duration-300 ease-in-out bg-white border-r border-gray-200 flex flex-col group"
         @mouseenter="sidebarOpen = true"
         @mouseleave="sidebarOpen = false">
      <div class="px-3 py-4 flex items-center gap-3">
        <i class="fas fa-fire text-2xl text-orange-600"></i>
        <span v-if="sidebarOpen" class="font-bold text-xl text-gray-800 whitespace-nowrap">Grillstone</span>
      </div>

      <div class="flex-1 px-3 py-4 overflow-y-auto">
        <ul class="space-y-2">
          <li>
            <a href="/pos" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-blue-50 hover:text-blue-700">
              <i class="fas fa-cash-register text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">POS</span>
            </a>
          </li>
          <li>
            <a href="/inventory" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-blue-50 hover:text-blue-700">
              <i class="fas fa-boxes text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Inventory</span>
            </a>
          </li>
          <li>
            <a href="/inventory/dishes" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-blue-50 hover:text-blue-700">
              <i class="fas fa-utensils text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Dishes</span>
            </a>
          </li>
          <li>
            <a href="/inventory/categories" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-blue-50 hover:text-blue-700">
              <i class="fas fa-tags text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Categories</span>
            </a>
          </li>
          <li>
            <a href="/hr" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors bg-blue-600 text-white">
              <i class="fas fa-users text-lg text-white"></i>
              <span v-if="sidebarOpen" class="font-medium">HR Dashboard</span>
            </a>
          </li>
          <li>
            <a href="/hr/employees" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-blue-50 hover:text-blue-700">
              <i class="fas fa-id-badge text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Employees</span>
            </a>
          </li>
          <li>
            <a href="/hr/departments" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-blue-50 hover:text-blue-700">
              <i class="fas fa-building text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Departments</span>
            </a>
          </li>
          <li>
            <a href="/hr/time-logs" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-blue-50 hover:text-blue-700">
              <i class="fas fa-clock text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Time Logs</span>
            </a>
          </li>
          <li>
            <a href="/hr/contracts" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-blue-50 hover:text-blue-700">
              <i class="fas fa-file-contract text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Contracts</span>
            </a>
          </li>
          <li>
            <a href="/hr/job-letters" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-blue-50 hover:text-blue-700">
              <i class="fas fa-file-alt text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Job Letters</span>
            </a>
          </li>
          <li>
            <a href="/reports" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-blue-50 hover:text-blue-700">
              <i class="fas fa-chart-line text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Reports</span>
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
    <section class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center">
              <i class="fas fa-users text-white text-xl"></i>
            </div>
            <div>
              <h1 class="text-3xl font-bold text-gray-900">HR Dashboard</h1>
              <p class="text-gray-600">Human Resources & Employee Management Overview</p>
            </div>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
          <!-- Total Employees -->
          <div class="glass-effect rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-blue-100">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-users text-white text-xl"></i>
              </div>
              <div v-if="!loading" class="text-right">
                <p class="text-3xl font-bold text-gray-900">{{ totalEmployees }}</p>
                <p class="text-sm text-gray-600">Total</p>
              </div>
              <div v-else class="text-right">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
              </div>
            </div>
            <h3 class="text-lg font-semibold text-gray-800">Total Employees</h3>
            <p class="text-sm text-gray-600 mt-1">All registered employees</p>
          </div>

          <!-- Active Employees -->
          <div class="glass-effect rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-green-100">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-user-check text-white text-xl"></i>
              </div>
              <div v-if="!loading" class="text-right">
                <p class="text-3xl font-bold text-gray-900">{{ activeEmployees }}</p>
                <p class="text-sm text-gray-600">Active</p>
              </div>
              <div v-else class="text-right">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
              </div>
            </div>
            <h3 class="text-lg font-semibold text-gray-800">Active Employees</h3>
            <div class="flex items-center gap-2 mt-1">
              <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-medium">
                Currently Working
              </span>
            </div>
          </div>

          <!-- Departments -->
          <div class="glass-effect rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-purple-100">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-building text-white text-xl"></i>
              </div>
              <div v-if="!loading" class="text-right">
                <p class="text-3xl font-bold text-gray-900">{{ totalDepartments }}</p>
                <p class="text-sm text-gray-600">Depts</p>
              </div>
              <div v-else class="text-right">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
              </div>
            </div>
            <h3 class="text-lg font-semibold text-gray-800">Departments</h3>
            <p class="text-sm text-gray-600 mt-1">Organizational units</p>
          </div>

          <!-- Pending Time Logs -->
          <div class="glass-effect rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-yellow-100">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-amber-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-clock text-white text-xl"></i>
              </div>
              <div v-if="!loading" class="text-right">
                <p class="text-3xl font-bold text-gray-900">{{ pendingTimeLogs }}</p>
                <p class="text-sm text-gray-600">Pending</p>
              </div>
              <div v-else class="text-right">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
              </div>
            </div>
            <h3 class="text-lg font-semibold text-gray-800">Time Log Approval</h3>
            <div class="flex items-center gap-2 mt-1">
              <span v-if="pendingTimeLogs > 0" class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 font-medium animate-pulse">
                {{ pendingTimeLogs }} awaiting review
              </span>
              <span v-else class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600 font-medium">
                All caught up
              </span>
            </div>
          </div>
        </div>

        <!-- Quick Links Section -->
        <div class="mb-6">
          <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fas fa-bolt text-blue-600"></i>
            Quick Actions
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Manage Employees -->
            <a href="/hr/employees" class="glass-effect rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-blue-100 hover:border-blue-300 group">
              <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                  <i class="fas fa-id-badge text-white text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Manage Employees</h3>
                <p class="text-sm text-gray-600">View, add, and edit employee records</p>
              </div>
            </a>

            <!-- Manage Departments -->
            <a href="/hr/departments" class="glass-effect rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-purple-100 hover:border-purple-300 group">
              <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                  <i class="fas fa-building text-white text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Manage Departments</h3>
                <p class="text-sm text-gray-600">Organize teams and departments</p>
              </div>
            </a>

            <!-- Time Tracking -->
            <a href="/hr/time-logs" class="glass-effect rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-indigo-100 hover:border-indigo-300 group">
              <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                  <i class="fas fa-clock text-white text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Time Tracking</h3>
                <p class="text-sm text-gray-600">Review and approve time logs</p>
                <span v-if="pendingTimeLogs > 0" class="mt-2 px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 font-medium">
                  {{ pendingTimeLogs }} pending
                </span>
              </div>
            </a>

            <!-- Employment Contracts -->
            <a href="/hr/contracts" class="glass-effect rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-orange-100 hover:border-orange-300 group">
              <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                  <i class="fas fa-file-contract text-white text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Contracts</h3>
                <p class="text-sm text-gray-600">Generate employment contracts</p>
              </div>
            </a>

            <!-- Job Letters -->
            <a href="/hr/job-letters" class="glass-effect rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-teal-100 hover:border-teal-300 group">
              <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-teal-500 to-teal-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                  <i class="fas fa-file-alt text-white text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Job Letters</h3>
                <p class="text-sm text-gray-600">Generate appointment letters</p>
              </div>
            </a>
          </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Pending Time Logs -->
          <div class="glass-effect rounded-2xl p-6 shadow-lg border border-gray-100">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-clock text-yellow-600"></i>
                Recent Time Logs (Pending)
              </h2>
              <a href="/hr/time-logs" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                View All <i class="fas fa-arrow-right ml-1"></i>
              </a>
            </div>

            <div v-if="loadingTimeLogs" class="text-center py-8 text-gray-500">
              <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
              <p>Loading time logs...</p>
            </div>

            <div v-else-if="!recentTimeLogs.length" class="text-center py-8 text-gray-400">
              <i class="fas fa-check-circle text-4xl mb-3 text-green-400"></i>
              <p>No pending time logs</p>
              <p class="text-sm">All time entries have been approved</p>
            </div>

            <div v-else class="space-y-3">
              <div v-for="log in recentTimeLogs" :key="log.id"
                   class="p-4 bg-gradient-to-r from-yellow-50 to-amber-50 rounded-lg border border-yellow-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                  <div class="font-semibold text-gray-900">{{ log.employee_name }}</div>
                  <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 font-medium">
                    Pending
                  </span>
                </div>
                <div class="text-sm text-gray-600 space-y-1">
                  <div class="flex items-center gap-2">
                    <i class="fas fa-calendar text-gray-400"></i>
                    <span>{{ formatDate(log.date) }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <i class="fas fa-clock text-gray-400"></i>
                    <span>{{ log.clock_in }} - {{ log.clock_out || 'Still working' }}</span>
                  </div>
                  <div v-if="log.hours" class="flex items-center gap-2">
                    <i class="fas fa-hourglass-half text-gray-400"></i>
                    <span class="font-medium">{{ log.hours }} hours</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recently Added Employees -->
          <div class="glass-effect rounded-2xl p-6 shadow-lg border border-gray-100">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-user-plus text-blue-600"></i>
                Recently Added Employees
              </h2>
              <a href="/hr/employees" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                View All <i class="fas fa-arrow-right ml-1"></i>
              </a>
            </div>

            <div v-if="loadingEmployees" class="text-center py-8 text-gray-500">
              <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
              <p>Loading employees...</p>
            </div>

            <div v-else-if="!recentEmployees.length" class="text-center py-8 text-gray-400">
              <i class="fas fa-users text-4xl mb-3"></i>
              <p>No employees added yet</p>
              <p class="text-sm">Add your first employee to get started</p>
            </div>

            <div v-else class="space-y-3">
              <div v-for="employee in recentEmployees" :key="employee.id"
                   class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                      <i class="fas fa-user text-white"></i>
                    </div>
                    <div>
                      <div class="font-semibold text-gray-900">{{ employee.first_name }} {{ employee.last_name }}</div>
                      <div class="text-sm text-gray-600">{{ employee.position }}</div>
                      <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs text-gray-500">
                          <i class="fas fa-building text-gray-400"></i>
                          {{ getDepartmentName(employee.department_id) }}
                        </span>
                      </div>
                    </div>
                  </div>
                  <div class="text-right">
                    <span class="px-2 py-1 text-xs rounded-full" :class="getStatusClass(employee.employment_status)">
                      {{ employee.employment_status }}
                    </span>
                    <div class="text-xs text-gray-500 mt-1">
                      {{ formatDate(employee.hire_date) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

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
import { ref, onMounted, computed } from 'vue'

// Types
interface Employee {
  id: number
  employee_number: string
  first_name: string
  last_name: string
  position: string
  department_id: number | null
  hire_date: string
  employment_status: 'active' | 'on-leave' | 'terminated' | 'suspended'
  created_at: string
}

interface Department {
  id: number
  name: string
  is_active: boolean
}

interface TimeLog {
  id: number
  employee_id: number
  employee_name: string
  date: string
  clock_in: string
  clock_out: string | null
  hours: number | null
  status: string
}

// State
const sidebarOpen = ref(false)
const loading = ref(true)
const loadingEmployees = ref(true)
const loadingTimeLogs = ref(true)
const employees = ref<Employee[]>([])
const departments = ref<Department[]>([])
const timeLogs = ref<TimeLog[]>([])

// Computed
const totalEmployees = computed(() => employees.value.length)
const activeEmployees = computed(() => employees.value.filter(e => e.employment_status === 'active').length)
const totalDepartments = computed(() => departments.value.filter(d => d.is_active).length)
const pendingTimeLogs = computed(() => timeLogs.value.filter(t => t.status === 'pending').length)

const recentEmployees = computed(() => {
  return [...employees.value]
    .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
    .slice(0, 5)
})

const recentTimeLogs = computed(() => {
  return [...timeLogs.value]
    .filter(t => t.status === 'pending')
    .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())
    .slice(0, 5)
})

// Functions
async function loadEmployees() {
  loadingEmployees.value = true
  try {
    const resp = await fetch('/api/employees', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    if (!resp.ok) throw new Error('Failed to load employees')
    employees.value = await resp.json()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load employees', 'error')
  } finally {
    loadingEmployees.value = false
  }
}

async function loadDepartments() {
  try {
    const resp = await fetch('/api/departments', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    if (!resp.ok) throw new Error('Failed to load departments')
    departments.value = await resp.json()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load departments', 'error')
  }
}

async function loadTimeLogs() {
  loadingTimeLogs.value = true
  try {
    const resp = await fetch('/api/time-logs?status=pending', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    if (!resp.ok) throw new Error('Failed to load time logs')
    timeLogs.value = await resp.json()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load time logs', 'error')
  } finally {
    loadingTimeLogs.value = false
  }
}

function getDepartmentName(departmentId: number | null): string {
  if (!departmentId) return 'N/A'
  const dept = departments.value.find(d => d.id === departmentId)
  return dept?.name || 'N/A'
}

function getStatusClass(status: string): string {
  const classes: Record<string, string> = {
    'active': 'bg-green-100 text-green-800',
    'on-leave': 'bg-yellow-100 text-yellow-800',
    'terminated': 'bg-red-100 text-red-800',
    'suspended': 'bg-orange-100 text-orange-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

function formatDate(date: string | null): string {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
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
onMounted(async () => {
  loading.value = true
  await Promise.all([
    loadEmployees(),
    loadDepartments(),
    loadTimeLogs()
  ])
  loading.value = false
})
</script>

<style scoped>
.glass-effect {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
}

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
