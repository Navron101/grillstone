<script setup lang="ts">
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

// Props
interface Props {
  modelValue?: boolean
  onPayoutClick?: () => void
  onCloseTillClick?: () => void
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: true
})

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
}>()

// Sidebar state
const sidebarOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
}

// Menu states
const posMenuOpen = ref(false)
const hrMenuOpen = ref(false)
const financeMenuOpen = ref(false)
const settingsMenuOpen = ref(false)

// Get user from Inertia
const page = usePage()
const user = computed(() => page.props.auth?.user)

// Helper function to check if user has access to a module
const hasModuleAccess = (module: string): boolean => {
  if (!user.value?.role?.permissions) return false;
  return user.value.role.permissions.some(permission => permission.module === module);
};

// Check module access
const canSeePOS = computed(() => hasModuleAccess('POS'))
const canSeeInventory = computed(() => hasModuleAccess('Inventory'))
const canSeeReports = computed(() => hasModuleAccess('Reports'))
const canSeeHR = computed(() => hasModuleAccess('HR'))
const canSeeFinance = computed(() => hasModuleAccess('Finance'))
const canSeeSettings = computed(() => hasModuleAccess('Settings'))

// Active route helper
function isActive(path: string): boolean {
  if (typeof window !== 'undefined') {
    return window.location.pathname.startsWith(path)
  }
  return false
}

// Hrefs
const inventoryHref = '/inventory'
const reportsHref = '/reports'
</script>

<template>
  <nav
    class="glass-effect m-4 rounded-2xl shadow-2xl flex flex-col transition-all duration-300 overflow-hidden"
    :class="sidebarOpen ? 'w-64' : 'w-20'"
  >
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

    <div class="px-2 overflow-y-auto flex-1">
      <ul class="mt-1 space-y-1">
        <!-- POS Dropdown -->
        <li v-if="canSeePOS">
          <button @click="posMenuOpen = !posMenuOpen"
                  class="w-full flex items-center gap-3 rounded-xl px-3 py-2 transition-colors"
                  :class="isActive('/pos') || isActive('/settlements')
                    ? 'bg-orange-600 text-white'
                    : 'text-gray-700 hover:bg-orange-50 hover:text-orange-700'">
            <i :class="['fas fa-cash-register text-lg', (isActive('/pos') || isActive('/settlements')) ? 'text-white' : 'text-gray-600']"></i>
            <span v-if="sidebarOpen" class="font-medium">POS</span>
            <i v-if="sidebarOpen" :class="['fas fa-chevron-down text-xs ml-auto transition-transform', posMenuOpen ? 'rotate-180' : '']"></i>
          </button>

          <!-- POS Submenu -->
          <ul v-if="posMenuOpen && sidebarOpen" class="mt-1 ml-6 space-y-1">
            <li>
              <a href="/pos" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors text-gray-600 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-store text-xs"></i>
                <span>Point of Sale</span>
              </a>
            </li>
            <li v-if="onPayoutClick">
              <button @click="onPayoutClick" class="w-full flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors text-gray-600 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-money-bill-transfer text-xs"></i>
                <span>Payout</span>
              </button>
            </li>
            <li v-if="onCloseTillClick">
              <button @click="onCloseTillClick" class="w-full flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors text-white bg-green-500 hover:bg-green-600">
                <i class="fas fa-cash-register text-xs"></i>
                <span>Close Till</span>
              </button>
            </li>
            <li>
              <a href="/settlements" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors text-gray-600 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-file-invoice-dollar text-xs"></i>
                <span>Settlements</span>
              </a>
            </li>
          </ul>
        </li>

        <li v-if="canSeeInventory">
          <a :href="inventoryHref"
             class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors"
             :class="isActive(inventoryHref)
              ? 'bg-orange-600 text-white'
              : 'text-gray-700 hover:bg-orange-50 hover:text-orange-700'">
            <i :class="['fas fa-boxes-stacked text-lg', isActive(inventoryHref) ? 'text-white' : 'text-gray-600']"></i>
            <span v-if="sidebarOpen" class="font-medium">Inventory</span>
          </a>
        </li>

        <li v-if="canSeeReports">
          <a :href="reportsHref"
             class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors"
             :class="isActive(reportsHref)
              ? 'bg-orange-600 text-white'
              : 'text-gray-700 hover:bg-orange-50 hover:text-orange-700'">
            <i :class="['fas fa-chart-line text-lg', isActive(reportsHref) ? 'text-white' : 'text-gray-600']"></i>
            <span v-if="sidebarOpen" class="font-medium">Reports</span>
          </a>
        </li>

        <!-- HR Dropdown -->
        <li v-if="canSeeHR">
          <button @click="hrMenuOpen = !hrMenuOpen"
                  class="w-full flex items-center gap-3 rounded-xl px-3 py-2 transition-colors"
                  :class="isActive('/hr')
                    ? 'bg-orange-600 text-white'
                    : 'text-gray-700 hover:bg-orange-50 hover:text-orange-700'">
            <i :class="['fas fa-users text-lg', isActive('/hr') ? 'text-white' : 'text-gray-600']"></i>
            <span v-if="sidebarOpen" class="font-medium">HR</span>
            <i v-if="sidebarOpen" :class="['fas fa-chevron-down text-xs ml-auto transition-transform', hrMenuOpen ? 'rotate-180' : '']"></i>
          </button>

          <!-- HR Submenu -->
          <ul v-if="hrMenuOpen && sidebarOpen" class="mt-1 ml-6 space-y-1">
            <li>
              <a href="/hr" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors text-gray-600 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-home text-xs"></i>
                <span>Dashboard</span>
              </a>
            </li>
            <li>
              <a href="/hr/employees" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors text-gray-600 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-user-tie text-xs"></i>
                <span>Employees</span>
              </a>
            </li>
            <li>
              <a href="/hr/departments" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors text-gray-600 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-building text-xs"></i>
                <span>Departments</span>
              </a>
            </li>
            <li>
              <a href="/hr/time-logs" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors text-gray-600 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-clock text-xs"></i>
                <span>Time Logs</span>
              </a>
            </li>
            <li>
              <a href="/hr/clock" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors text-gray-600 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-fingerprint text-xs"></i>
                <span>Clock In/Out</span>
              </a>
            </li>
          </ul>
        </li>

        <!-- Finance Dropdown -->
        <li v-if="canSeeFinance">
          <button @click="financeMenuOpen = !financeMenuOpen"
                  class="w-full flex items-center gap-3 rounded-xl px-3 py-2 transition-colors"
                  :class="isActive('/payroll') || isActive('/loyalty')
                    ? 'bg-orange-600 text-white'
                    : 'text-gray-700 hover:bg-orange-50 hover:text-orange-700'">
            <i :class="['fas fa-dollar-sign text-lg', (isActive('/payroll') || isActive('/loyalty')) ? 'text-white' : 'text-gray-600']"></i>
            <span v-if="sidebarOpen" class="font-medium">Finance</span>
            <i v-if="sidebarOpen" :class="['fas fa-chevron-down text-xs ml-auto transition-transform', financeMenuOpen ? 'rotate-180' : '']"></i>
          </button>

          <!-- Finance Submenu -->
          <ul v-if="financeMenuOpen && sidebarOpen" class="mt-1 ml-6 space-y-1">
            <li>
              <a href="/payroll" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors text-gray-600 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-money-check-alt text-xs"></i>
                <span>Payroll</span>
              </a>
            </li>
            <li>
              <a href="/loyalty" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors text-gray-600 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-gift text-xs"></i>
                <span>Loyalty Program</span>
              </a>
            </li>
          </ul>
        </li>

        <!-- Settings Dropdown (Admin/Director only) -->
        <li v-if="canSeeSettings">
          <button @click="settingsMenuOpen = !settingsMenuOpen"
                  class="w-full flex items-center gap-3 rounded-xl px-3 py-2 transition-colors"
                  :class="isActive('/settings')
                    ? 'bg-orange-600 text-white'
                    : 'text-gray-700 hover:bg-orange-50 hover:text-orange-700'">
            <i :class="['fas fa-cog text-lg', isActive('/settings') ? 'text-white' : 'text-gray-600']"></i>
            <span v-if="sidebarOpen" class="font-medium">Settings</span>
            <i v-if="sidebarOpen" :class="['fas fa-chevron-down text-xs ml-auto transition-transform', settingsMenuOpen ? 'rotate-180' : '']"></i>
          </button>

          <!-- Settings Submenu -->
          <ul v-if="settingsMenuOpen && sidebarOpen" class="mt-1 ml-6 space-y-1">
            <li>
              <a href="/settings/profile" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors text-gray-600 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-user text-xs"></i>
                <span>Profile</span>
              </a>
            </li>
            <li>
              <a href="/settings/appearance" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors text-gray-600 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-palette text-xs"></i>
                <span>Appearance</span>
              </a>
            </li>
            <li>
              <a href="/settings/users" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors text-gray-600 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-users-cog text-xs"></i>
                <span>User Administration</span>
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </div>

    <div class="mt-auto px-3 py-3 text-xs text-gray-500 border-t border-gray-200">
      <div v-if="sidebarOpen">v0.1 • Grillstone</div>
      <div v-else class="text-center">v0.1</div>
    </div>
  </nav>
</template>

<style scoped>
.glass-effect {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}
</style>
