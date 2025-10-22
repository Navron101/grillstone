<template>
  <div class="min-h-screen gradient-bg">
    <!-- Header -->
    <header class="glass-effect shadow-lg">
      <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-clock text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-800">Time Logs</h1>
            <p class="text-sm text-gray-600">Manage employee time tracking</p>
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
              <a :href="posHref" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
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
              <a href="/hr/time-logs" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors bg-orange-600 text-white">
                <i class="fas fa-clock text-lg text-white"></i>
                <span v-if="sidebarOpen" class="font-medium">Time Logs</span>
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
        <!-- Filters -->
        <div class="glass-effect rounded-2xl p-6 mb-4 shadow-lg">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
              <select v-model="filters.employee_id"
                      class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
                <option value="">All Employees</option>
                <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
              <input v-model="filters.start_date" type="date"
                     class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
              <input v-model="filters.end_date" type="date"
                     class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select v-model="filters.status"
                      class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>
          </div>

          <div class="flex gap-2 mt-4">
            <button @click="applyFilters"
                    class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium">
              <i class="fas fa-filter mr-2"></i>Apply Filters
            </button>
            <button @click="resetFilters"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium">
              <i class="fas fa-redo mr-2"></i>Reset
            </button>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="glass-effect rounded-2xl p-4 mb-4 shadow-lg">
          <div class="flex items-center justify-between">
            <div class="flex gap-2">
              <button @click="showAddModal = true"
                      class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium">
                <i class="fas fa-plus mr-2"></i>Add Time Log
              </button>
              <button @click="bulkApprove" :disabled="!selectedLogs.length"
                      class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-check-double mr-2"></i>Approve Selected ({{ selectedLogs.length }})
              </button>
            </div>
            <div class="text-sm text-gray-600">
              Total: {{ timeLogs.length }} logs
            </div>
          </div>
        </div>

        <!-- Time Logs Table -->
        <div class="glass-effect rounded-2xl p-6 shadow-lg flex-1 overflow-hidden flex flex-col">
          <div v-if="loading" class="text-center py-8 text-gray-500">Loading time logs...</div>

          <div v-else-if="!timeLogs.length" class="text-center py-8 text-gray-400">
            <i class="fas fa-clock text-4xl mb-3"></i>
            <p>No time logs found.</p>
          </div>

          <div v-else class="overflow-x-auto flex-1">
            <table class="min-w-full">
              <thead class="sticky top-0 bg-white">
                <tr class="text-left text-gray-600 border-b">
                  <th class="py-3 px-4">
                    <input type="checkbox" @change="toggleSelectAll" :checked="isAllSelected"
                           class="rounded focus:ring-2 focus:ring-orange-500">
                  </th>
                  <th class="py-3 px-4">Employee</th>
                  <th class="py-3 px-4">Date</th>
                  <th class="py-3 px-4">Clock In</th>
                  <th class="py-3 px-4">Clock Out</th>
                  <th class="py-3 px-4 text-right">Regular Hours</th>
                  <th class="py-3 px-4 text-right">Overtime Hours</th>
                  <th class="py-3 px-4">Status</th>
                  <th class="py-3 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="log in timeLogs" :key="log.id" class="border-b hover:bg-gray-50">
                  <td class="py-3 px-4">
                    <input type="checkbox" v-model="selectedLogs" :value="log.id"
                           :disabled="log.status !== 'pending'"
                           class="rounded focus:ring-2 focus:ring-orange-500 disabled:opacity-50">
                  </td>
                  <td class="py-3 px-4 font-medium text-gray-900">{{ log.employee_name }}</td>
                  <td class="py-3 px-4 text-gray-600">{{ formatDate(log.work_date) }}</td>
                  <td class="py-3 px-4 text-gray-600">{{ log.clock_in || 'N/A' }}</td>
                  <td class="py-3 px-4 text-gray-600">{{ log.clock_out || 'N/A' }}</td>
                  <td class="py-3 px-4 text-right font-medium text-gray-900">{{ formatHours(log.regular_hours) }}</td>
                  <td class="py-3 px-4 text-right font-medium text-gray-900">{{ formatHours(log.overtime_hours) }}</td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-1 text-xs rounded-full font-medium" :class="statusClass(log.status)">
                      {{ log.status.toUpperCase() }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right space-x-2">
                    <template v-if="log.status === 'pending'">
                      <button @click="approveLog(log.id)"
                              class="text-green-600 hover:text-green-800"
                              title="Approve">
                        <i class="fas fa-check"></i>
                      </button>
                      <button @click="rejectLog(log.id)"
                              class="text-red-600 hover:text-red-800"
                              title="Reject">
                        <i class="fas fa-times"></i>
                      </button>
                      <button @click="editLog(log)"
                              class="text-blue-600 hover:text-blue-800"
                              title="Edit">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button @click="confirmDeleteLog(log.id)"
                              class="text-red-600 hover:text-red-800"
                              title="Delete">
                        <i class="fas fa-trash"></i>
                      </button>
                    </template>
                    <template v-else>
                      <span class="text-xs text-gray-400">No actions</span>
                    </template>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>

    <!-- Add/Edit Time Log Modal -->
    <div v-if="showAddModal || editingLog" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeModal">
      <div class="bg-white rounded-lg p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-bold mb-4">{{ editingLog ? 'Edit' : 'Add' }} Time Log</h3>

        <form @submit.prevent="saveLog" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Employee *</label>
            <select v-model="logForm.employee_id" required
                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
              <option value="">Select employee...</option>
              <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Work Date *</label>
            <input v-model="logForm.work_date" type="date" required
                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
          </div>

          <div class="border-t pt-4">
            <p class="text-sm font-medium text-gray-700 mb-2">Option 1: Clock In/Out Times</p>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Clock In</label>
                <input v-model="logForm.clock_in" type="time"
                       @input="calculateHours"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Clock Out</label>
                <input v-model="logForm.clock_out" type="time"
                       @input="calculateHours"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
              </div>
            </div>
          </div>

          <div class="border-t pt-4">
            <p class="text-sm font-medium text-gray-700 mb-2">Option 2: Manual Hours Entry</p>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Regular Hours</label>
                <input v-model.number="logForm.regular_hours" type="number" step="0.1" min="0"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                       placeholder="0.0">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Overtime Hours</label>
                <input v-model.number="logForm.overtime_hours" type="number" step="0.1" min="0"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                       placeholder="0.0">
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea v-model="logForm.notes" rows="3"
                      class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                      placeholder="Any additional notes..."></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <button type="button" @click="closeModal"
                    class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit"
                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
              {{ editingLog ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showDeleteConfirm = false">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Confirm Delete</h3>
        <p class="text-gray-700 mb-6">Are you sure you want to delete this time log? This action cannot be undone.</p>
        <div class="flex justify-end gap-2">
          <button @click="showDeleteConfirm = false" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
          <button @click="deleteLog" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
        </div>
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
type Employee = {
  id: number
  name: string
}

type TimeLog = {
  id: number
  employee_id: number
  employee_name: string
  work_date: string
  clock_in: string | null
  clock_out: string | null
  regular_hours: number
  overtime_hours: number
  status: 'pending' | 'approved' | 'rejected'
  notes: string | null
}

// Data
const employees = ref<Employee[]>([])
const timeLogs = ref<TimeLog[]>([])
const loading = ref(false)
const selectedLogs = ref<number[]>([])

// Filters
const filters = ref({
  employee_id: '',
  start_date: '',
  end_date: '',
  status: ''
})

// Modal
const showAddModal = ref(false)
const editingLog = ref<TimeLog | null>(null)
const logForm = ref({
  employee_id: '',
  work_date: '',
  clock_in: '',
  clock_out: '',
  regular_hours: 0,
  overtime_hours: 0,
  notes: ''
})

// Delete
const showDeleteConfirm = ref(false)
const deleteTarget = ref<number | null>(null)

// Computed
const isAllSelected = computed(() => {
  const pendingLogs = timeLogs.value.filter(l => l.status === 'pending')
  return pendingLogs.length > 0 && pendingLogs.every(l => selectedLogs.value.includes(l.id))
})

// Functions
async function loadEmployees() {
  try {
    const resp = await fetch('/api/employees')
    if (!resp.ok) throw new Error('Failed to load employees')
    employees.value = await resp.json()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load employees', 'error')
  }
}

async function loadTimeLogs() {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (filters.value.employee_id) params.append('employee_id', filters.value.employee_id)
    if (filters.value.start_date) params.append('start_date', filters.value.start_date)
    if (filters.value.end_date) params.append('end_date', filters.value.end_date)
    if (filters.value.status) params.append('status', filters.value.status)

    const resp = await fetch(`/api/time-logs?${params.toString()}`)
    if (!resp.ok) throw new Error('Failed to load time logs')
    timeLogs.value = await resp.json()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load time logs', 'error')
  } finally {
    loading.value = false
  }
}

function applyFilters() {
  loadTimeLogs()
}

function resetFilters() {
  filters.value = {
    employee_id: '',
    start_date: '',
    end_date: '',
    status: ''
  }
  loadTimeLogs()
}

function calculateHours() {
  if (logForm.value.clock_in && logForm.value.clock_out) {
    const [inH, inM] = logForm.value.clock_in.split(':').map(Number)
    const [outH, outM] = logForm.value.clock_out.split(':').map(Number)

    const inMinutes = inH * 60 + inM
    const outMinutes = outH * 60 + outM

    let totalMinutes = outMinutes - inMinutes
    if (totalMinutes < 0) totalMinutes += 24 * 60 // Handle overnight shifts

    const totalHours = totalMinutes / 60

    // Assume 8 hours is regular, rest is overtime
    if (totalHours <= 8) {
      logForm.value.regular_hours = parseFloat(totalHours.toFixed(2))
      logForm.value.overtime_hours = 0
    } else {
      logForm.value.regular_hours = 8
      logForm.value.overtime_hours = parseFloat((totalHours - 8).toFixed(2))
    }
  }
}

async function saveLog() {
  try {
    const payload = {
      employee_id: logForm.value.employee_id,
      work_date: logForm.value.work_date,
      clock_in: logForm.value.clock_in || null,
      clock_out: logForm.value.clock_out || null,
      regular_hours: logForm.value.regular_hours || 0,
      overtime_hours: logForm.value.overtime_hours || 0,
      notes: logForm.value.notes || null
    }

    if (editingLog.value) {
      const resp = await fetch(`/api/time-logs/${editingLog.value.id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      if (!resp.ok) throw new Error('Failed to update time log')
      toast('Success', 'Time log updated successfully', 'success')
    } else {
      const resp = await fetch('/api/time-logs', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      if (!resp.ok) throw new Error('Failed to create time log')
      toast('Success', 'Time log created successfully', 'success')
    }

    closeModal()
    await loadTimeLogs()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to save time log', 'error')
  }
}

function editLog(log: TimeLog) {
  editingLog.value = log
  logForm.value = {
    employee_id: String(log.employee_id),
    work_date: log.work_date,
    clock_in: log.clock_in || '',
    clock_out: log.clock_out || '',
    regular_hours: log.regular_hours || 0,
    overtime_hours: log.overtime_hours || 0,
    notes: log.notes || ''
  }
}

async function approveLog(id: number) {
  try {
    const resp = await fetch(`/api/time-logs/${id}/approve`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    })
    if (!resp.ok) throw new Error('Failed to approve time log')
    toast('Success', 'Time log approved', 'success')
    await loadTimeLogs()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to approve time log', 'error')
  }
}

async function rejectLog(id: number) {
  try {
    const resp = await fetch(`/api/time-logs/${id}/reject`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    })
    if (!resp.ok) throw new Error('Failed to reject time log')
    toast('Success', 'Time log rejected', 'success')
    await loadTimeLogs()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to reject time log', 'error')
  }
}

async function bulkApprove() {
  if (!selectedLogs.value.length) return

  try {
    const resp = await fetch('/api/time-logs/bulk-approve', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ids: selectedLogs.value })
    })
    if (!resp.ok) throw new Error('Failed to bulk approve')
    toast('Success', `${selectedLogs.value.length} time logs approved`, 'success')
    selectedLogs.value = []
    await loadTimeLogs()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to bulk approve', 'error')
  }
}

function confirmDeleteLog(id: number) {
  deleteTarget.value = id
  showDeleteConfirm.value = true
}

async function deleteLog() {
  if (!deleteTarget.value) return

  try {
    const resp = await fetch(`/api/time-logs/${deleteTarget.value}`, { method: 'DELETE' })
    if (!resp.ok) throw new Error('Failed to delete time log')
    toast('Success', 'Time log deleted', 'success')
    await loadTimeLogs()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to delete time log', 'error')
  } finally {
    showDeleteConfirm.value = false
    deleteTarget.value = null
  }
}

function toggleSelectAll() {
  const pendingLogs = timeLogs.value.filter(l => l.status === 'pending')
  if (isAllSelected.value) {
    selectedLogs.value = []
  } else {
    selectedLogs.value = pendingLogs.map(l => l.id)
  }
}

function closeModal() {
  showAddModal.value = false
  editingLog.value = null
  logForm.value = {
    employee_id: '',
    work_date: '',
    clock_in: '',
    clock_out: '',
    regular_hours: 0,
    overtime_hours: 0,
    notes: ''
  }
}

function formatDate(dateStr: string) {
  return new Date(dateStr).toLocaleDateString()
}

function formatHours(hours: number) {
  return hours ? hours.toFixed(2) : '0.00'
}

function statusClass(status: string) {
  switch (status) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'approved':
      return 'bg-green-100 text-green-800'
    case 'rejected':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
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
  loadEmployees()
  loadTimeLogs()
})
</script>

<style scoped>
.gradient-bg {
  background: linear-gradient(135deg, #f97316 0%, #ea580c 25%, #dc2626 50%, #92400e 75%, #451a03 100%);
}

.glass-effect {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
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
