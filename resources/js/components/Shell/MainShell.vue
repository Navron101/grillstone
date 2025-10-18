<template>
  <div class="min-h-screen gradient-bg">
    <!-- Header (same as POS) -->
    <header class="glass-effect shadow-lg">
      <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-utensils text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-800">{{ title }}</h1>
            <p v-if="subtitle" class="text-sm text-gray-600">{{ subtitle }}</p>
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <button @click="$emit('toggle-sidebar')" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
            <i class="fas" :class="sidebarOpen ? 'fa-angles-left' : 'fa-angles-right'"></i>
          </button>
          <div class="text-right mr-2">
            <p class="text-sm text-gray-600">{{ currentTime }}</p>
            <p class="text-sm font-medium text-gray-800">Cashier: {{ cashier }}</p>
          </div>
          <button @click="$emit('open-settings')" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
            <i class="fas fa-cog text-lg"></i>
          </button>
          <button @click="$emit('logout')" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
            <i class="fas fa-right-from-bracket text-lg"></i>
          </button>
        </div>
      </div>
    </header>

    <div class="flex h-[calc(100vh-80px)]">
      <!-- Left menu identical to POS -->
      <nav
        class="glass-effect m-4 rounded-2xl shadow-2xl flex flex-col transition-all duration-300 overflow-hidden"
        :class="sidebarOpen ? 'w-64' : 'w-20'"
        aria-label="Main navigation"
      >
        <div class="flex items-center justify-between px-3 py-3">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
              <i class="fas fa-fire text-white text-base"></i>
            </div>
            <span v-if="sidebarOpen" class="font-semibold text-gray-800">Menu</span>
          </div>
          <button @click="$emit('toggle-sidebar')" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
            <i class="fas" :class="sidebarOpen ? 'fa-angles-left' : 'fa-angles-right'"></i>
          </button>
        </div>

        <div class="px-2">
          <ul class="mt-1 space-y-1">
            <li>
              <a :href="routes.pos" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors"
                 :class="isActive(routes.pos) ? 'bg-orange-600 text-white' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-700'">
                <i :class="['fas fa-cash-register text-lg', isActive(routes.pos) ? 'text-white' : 'text-gray-600']"></i>
                <span v-if="sidebarOpen" class="font-medium">POS</span>
              </a>
            </li>

            <li>
              <a :href="routes.inventory" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors"
                 :class="isActive(routes.inventory) ? 'bg-orange-600 text-white' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-700'">
                <i :class="['fas fa-boxes-stacked text-lg', isActive(routes.inventory) ? 'text-white' : 'text-gray-600']"></i>
                <span v-if="sidebarOpen" class="font-medium">Inventory</span>
              </a>
            </li>

            <li>
              <a :href="routes.grn" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors"
                 :class="isActive(routes.grn) ? 'bg-orange-600 text-white' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-700'">
                <i :class="['fas fa-truck-loading text-lg', isActive(routes.grn) ? 'text-white' : 'text-gray-600']"></i>
                <span v-if="sidebarOpen" class="font-medium">Receive Stock</span>
              </a>
            </li>
          </ul>
        </div>

        <div class="mt-auto px-3 py-3 text-xs text-gray-500">
          <div v-if="sidebarOpen">v0.1 • Grillstone</div>
          <div v-else class="text-center">v0.1</div>
        </div>
      </nav>

      <!-- Page body slot -->
      <div class="flex-1 flex">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'

const props = defineProps<{
  title: string
  subtitle?: string
  cashier?: string
  sidebarOpen: boolean
  routes: { pos: string; inventory: string; grn: string }
}>()

const currentTime = ref('')
function tick(){ currentTime.value = new Date().toLocaleTimeString() }
onMounted(()=>{ tick(); setInterval(tick,1000) })

function isActive(href: string) {
  try {
    const cur = window.location.pathname
    const path = new URL(href, window.location.origin).pathname
    return cur.startsWith(path)
  } catch { return false }
}
</script>
