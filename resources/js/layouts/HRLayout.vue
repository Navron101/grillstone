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
    href: '/hr',
    icon: 'fa-home',
  },
  {
    title: 'Employees',
    href: '/hr/employees',
    icon: 'fa-users',
  },
  {
    title: 'Departments',
    href: '/hr/departments',
    icon: 'fa-sitemap',
  },
  {
    title: 'Time & Attendance',
    href: '/hr/time-logs',
    icon: 'fa-clock',
  },
  {
    title: 'Clock In/Out',
    href: '/hr/clock',
    icon: 'fa-user-clock',
  },
  {
    title: 'Employment Contracts',
    href: '/hr/contracts',
    icon: 'fa-file-contract',
  },
  {
    title: 'Job Letters',
    href: '/hr/job-letters',
    icon: 'fa-file-alt',
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
        <Link href="/hr" class="flex items-center gap-3">
          <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-users text-white text-lg"></i>
          </div>
          <div>
            <h2 class="font-bold text-gray-900">Human Resources</h2>
            <p class="text-xs text-gray-500">Employee Management</p>
          </div>
        </Link>
      </div>

      <!-- Navigation Menu -->
      <nav class="flex-1 px-3 py-4 overflow-y-auto">
        <ul class="space-y-1">
          <li v-for="item in menuItems" :key="item.href">
            <Link
              :href="item.href"
              :class="[
                'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                isActive(item.href)
                  ? 'bg-blue-50 text-blue-700'
                  : 'text-gray-700 hover:bg-gray-100'
              ]"
            >
              <i :class="['fas', item.icon, 'w-5 text-center']"></i>
              <span>{{ item.title }}</span>
            </Link>
          </li>
        </ul>
      </nav>

      <!-- Sidebar Footer -->
      <div class="px-3 py-4 border-t border-gray-200">
        <Link
          href="/dashboard"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors"
        >
          <i class="fas fa-arrow-left w-5 text-center"></i>
          <span>Back to Main</span>
        </Link>
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 overflow-auto">
      <slot />
    </div>
  </div>
</template>
