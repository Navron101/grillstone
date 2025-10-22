<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 flex items-center justify-center p-4">
    <!-- Main Kiosk Interface -->
    <div class="max-w-2xl w-full">
      <!-- Header with Clock -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center gap-4 bg-white/10 backdrop-blur-lg rounded-3xl px-8 py-4 mb-6">
          <i class="fas fa-building text-4xl text-blue-400"></i>
          <div class="text-left">
            <h1 class="text-3xl font-bold text-white">GRILLSTONE</h1>
            <p class="text-blue-300 text-sm">Employee Time Clock</p>
          </div>
        </div>
        <div class="text-6xl font-bold text-white mb-2">{{ currentTime }}</div>
        <div class="text-xl text-blue-300">{{ currentDate }}</div>
      </div>

      <!-- Main Card -->
      <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl p-8 mb-6">
        <!-- Employee Info (shown after number entered) -->
        <div v-if="employee" class="text-center mb-6 pb-6 border-b-2 border-gray-200">
          <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
            <i class="fas fa-user text-4xl text-white"></i>
          </div>
          <h2 class="text-3xl font-bold text-gray-800 mb-1">{{ employee.first_name }} {{ employee.last_name }}</h2>
          <p class="text-lg text-gray-600">{{ employee.position }}</p>
          <p class="text-sm text-gray-500">{{ employee.employee_number }}</p>
        </div>

        <!-- Employee Number Input -->
        <div class="mb-6">
          <label class="block text-xl font-semibold text-gray-700 mb-3 text-center">Enter Employee Number</label>
          <input
            v-model="employeeNumber"
            type="text"
            ref="employeeInput"
            @keyup.enter="quickClock"
            class="w-full text-center text-4xl font-bold border-4 border-blue-500 rounded-2xl px-6 py-6 focus:outline-none focus:ring-4 focus:ring-blue-300 bg-gray-50"
            placeholder="EMP-000"
            maxlength="10"
            autofocus
          >
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-1 gap-4 mb-6">
          <button
            @click="quickClock"
            :disabled="!employeeNumber || processing"
            class="w-full py-8 text-3xl font-bold rounded-2xl transition-all transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
            :class="processing ? 'bg-gray-400 text-white' : 'bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white shadow-lg'"
          >
            <i class="fas fa-clock mr-3"></i>
            {{ processing ? 'PROCESSING...' : 'CLOCK IN / OUT' }}
          </button>
        </div>

        <!-- Clear Button -->
        <button
          @click="clearForm"
          class="w-full py-4 text-xl font-semibold text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-xl transition-colors"
        >
          <i class="fas fa-times mr-2"></i> Clear
        </button>
      </div>

      <!-- Recent Activity Feed -->
      <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
          <i class="fas fa-history mr-2 text-blue-500"></i>Recent Activity
        </h3>
        <div v-if="recentActivity.length" class="space-y-2 max-h-48 overflow-y-auto">
          <div
            v-for="(activity, i) in recentActivity"
            :key="i"
            class="flex items-center justify-between p-3 rounded-lg"
            :class="activity.action === 'clock_in' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'"
          >
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-full flex items-center justify-center"
                :class="activity.action === 'clock_in' ? 'bg-green-500' : 'bg-red-500'"
              >
                <i :class="activity.action === 'clock_in' ? 'fas fa-arrow-right' : 'fas fa-arrow-left'" class="text-white"></i>
              </div>
              <div>
                <p class="font-semibold text-gray-800">{{ activity.employee }}</p>
                <p class="text-sm text-gray-600">{{ activity.action === 'clock_in' ? 'Clocked In' : 'Clocked Out' }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-gray-700">{{ activity.time }}</p>
            </div>
          </div>
        </div>
        <div v-else class="text-center text-gray-400 py-4">
          No recent activity
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <div v-if="showSuccess" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 backdrop-blur-sm">
      <div class="bg-white rounded-3xl p-12 text-center max-w-md mx-4 shadow-2xl transform scale-100 animate-bounce-in">
        <div
          class="w-32 h-32 mx-auto mb-6 rounded-full flex items-center justify-center animate-scale-in"
          :class="successData.action === 'clock_in' ? 'bg-green-500' : 'bg-red-500'"
        >
          <i
            :class="successData.action === 'clock_in' ? 'fas fa-arrow-right' : 'fas fa-arrow-left'"
            class="text-6xl text-white"
          ></i>
        </div>
        <h2 class="text-4xl font-bold mb-2" :class="successData.action === 'clock_in' ? 'text-green-600' : 'text-red-600'">
          {{ successData.action === 'clock_in' ? 'CLOCKED IN' : 'CLOCKED OUT' }}
        </h2>
        <p class="text-2xl text-gray-700 mb-2">{{ successData.employee }}</p>
        <p class="text-xl text-gray-500">{{ successData.time }}</p>
        <div v-if="successData.hours" class="mt-4 p-4 bg-blue-50 rounded-xl">
          <p class="text-sm text-gray-600 mb-1">Hours Worked</p>
          <p class="text-3xl font-bold text-blue-600">{{ successData.hours }}</p>
        </div>
      </div>
    </div>

    <!-- Error Toast -->
    <div v-if="errorMsg" class="fixed top-6 right-6 bg-red-500 text-white px-6 py-4 rounded-xl shadow-lg animate-slide-in z-50">
      <div class="flex items-center gap-3">
        <i class="fas fa-exclamation-circle text-2xl"></i>
        <div>
          <p class="font-bold">Error</p>
          <p>{{ errorMsg }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'

// State
const employeeNumber = ref('')
const employee = ref<any>(null)
const processing = ref(false)
const showSuccess = ref(false)
const successData = ref<any>({})
const errorMsg = ref('')
const recentActivity = ref<any[]>([])
const currentTime = ref('')
const currentDate = ref('')
const employeeInput = ref<HTMLInputElement | null>(null)

// Update clock
function updateClock() {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
}

// Clock interval
let clockInterval: number
onMounted(() => {
  updateClock()
  clockInterval = window.setInterval(updateClock, 1000)
  loadRecentActivity()
})

onUnmounted(() => {
  clearInterval(clockInterval)
})

// Load recent activity
async function loadRecentActivity() {
  try {
    const resp = await fetch('/api/time-logs?limit=5', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    if (resp.ok) {
      const logs = await resp.json()
      recentActivity.value = logs.slice(0, 5).map((log: any) => ({
        employee: log.employee_name,
        action: log.clock_out ? 'clock_out' : 'clock_in',
        time: new Date(log.clock_out || log.clock_in).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
      }))
    }
  } catch (e) {
    console.error('Failed to load recent activity', e)
  }
}

// Quick clock (auto-detect in/out)
async function quickClock() {
  if (!employeeNumber.value || processing.value) return

  processing.value = true
  errorMsg.value = ''

  try {
    // First, lookup employee
    const empResp = await fetch(`/api/employees?filter=${employeeNumber.value}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    if (!empResp.ok) {
      throw new Error('Employee not found')
    }

    const employees = await empResp.json()
    const emp = employees.find((e: any) => e.employee_number === employeeNumber.value)

    if (!emp) {
      throw new Error('Employee not found')
    }

    employee.value = emp

    // Quick clock
    const resp = await fetch('/api/clock/quick', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        employee_number: employeeNumber.value
      })
    })

    if (!resp.ok) {
      const error = await resp.json()
      throw new Error(error.error || 'Clock operation failed')
    }

    const result = await resp.json()

    // Show success
    successData.value = {
      action: result.action,
      employee: `${emp.first_name} ${emp.last_name}`,
      time: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }),
      hours: result.action === 'clock_out' && result.time_log ?
        `${(result.time_log.regular_hours || 0).toFixed(2)} hrs` : null
    }

    showSuccess.value = true

    // Add to recent activity
    recentActivity.value.unshift({
      employee: `${emp.first_name} ${emp.last_name}`,
      action: result.action,
      time: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
    })
    if (recentActivity.value.length > 5) recentActivity.value.pop()

    // Auto-clear after 3 seconds
    setTimeout(() => {
      showSuccess.value = false
      clearForm()
      employeeInput.value?.focus()
    }, 3000)

  } catch (e: any) {
    errorMsg.value = e.message || 'An error occurred'
    setTimeout(() => {
      errorMsg.value = ''
    }, 5000)
  } finally {
    processing.value = false
  }
}

// Clear form
function clearForm() {
  employeeNumber.value = ''
  employee.value = null
  employeeInput.value?.focus()
}
</script>

<style scoped>
@keyframes bounce-in {
  0% {
    transform: scale(0);
    opacity: 0;
  }
  50% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

@keyframes scale-in {
  0% {
    transform: scale(0) rotate(0deg);
  }
  50% {
    transform: scale(1.2) rotate(180deg);
  }
  100% {
    transform: scale(1) rotate(360deg);
  }
}

@keyframes slide-in {
  0% {
    transform: translateX(400px);
    opacity: 0;
  }
  100% {
    transform: translateX(0);
    opacity: 1;
  }
}

.animate-bounce-in {
  animation: bounce-in 0.5s ease-out;
}

.animate-scale-in {
  animation: scale-in 0.6s ease-out;
}

.animate-slide-in {
  animation: slide-in 0.3s ease-out;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
