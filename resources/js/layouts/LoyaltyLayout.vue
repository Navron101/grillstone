<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

interface Props {
  title?: string
}

defineProps<Props>()

const currentPath = computed(() => window.location.pathname)

const menuItems = [
  {
    title: 'Dashboard',
    href: '/loyalty',
    icon: 'fa-home',
  },
  {
    title: 'Companies',
    href: '/loyalty/companies',
    icon: 'fa-building',
  },
  {
    title: 'Employees',
    href: '/loyalty/employees',
    icon: 'fa-id-card',
  },
  {
    title: 'Settlements',
    href: '/loyalty/settlements',
    icon: 'fa-file-invoice-dollar',
  },
]

const isActive = (href: string) => {
  return currentPath.value === href || currentPath.value.startsWith(href + '/')
}
</script>

<template>
  <div class="flex h-screen bg-gray-50">
    <!-- Left Sidebar -->
    <div class="w-64 bg-white border-r border-gray-200 flex flex-col">
      <!-- Sidebar Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <Link href="/loyalty" class="flex items-center gap-3">
          <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-star text-white text-lg"></i>
          </div>
          <div>
            <h2 class="font-bold text-gray-900">Loyalty Program</h2>
            <p class="text-xs text-gray-500">Partner Discounts</p>
          </div>
        </Link>
      </div>

      <!-- Back to Main Menu -->
      <div class="px-3 pt-4 pb-2">
        <Link
          href="/"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors"
        >
          <i class="fas fa-home w-5 text-center"></i>
          <span>Main Menu</span>
        </Link>
      </div>

      <!-- Navigation Menu -->
      <nav class="flex-1 px-3 py-2 overflow-y-auto">
        <ul class="space-y-1">
          <li v-for="item in menuItems" :key="item.href">
            <Link
              :href="item.href"
              :class="[
                'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                isActive(item.href)
                  ? 'bg-purple-50 text-purple-700'
                  : 'text-gray-700 hover:bg-gray-100'
              ]"
            >
              <i :class="['fas', item.icon, 'w-5 text-center']"></i>
              <span>{{ item.title }}</span>
            </Link>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 overflow-auto">
      <slot />
    </div>
  </div>
</template>
