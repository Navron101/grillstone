<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const currentPath = computed(() => window.location.pathname)

const menuItems = [
  {
    title: 'Profile',
    href: '/settings/profile',
    icon: 'fa-user',
    description: 'Manage your account details'
  },
  {
    title: 'Password',
    href: '/settings/password',
    icon: 'fa-lock',
    description: 'Update your password'
  },
  {
    title: 'Appearance',
    href: '/settings/appearance',
    icon: 'fa-palette',
    description: 'Customize appearance'
  },
  {
    title: 'Tax Settings',
    href: '/settings/tax',
    icon: 'fa-percentage',
    description: 'Configure tax rates'
  },
  {
    title: 'HR Settings',
    href: '/settings/hr',
    icon: 'fa-user-tie',
    description: 'HR configurations'
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
        <Link href="/settings/profile" class="flex items-center gap-3">
          <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-cog text-white text-lg"></i>
          </div>
          <div>
            <h2 class="font-bold text-gray-900">Settings</h2>
            <p class="text-xs text-gray-500">System Configuration</p>
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
                'flex items-start gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors',
                isActive(item.href)
                  ? 'bg-purple-50 text-purple-700'
                  : 'text-gray-700 hover:bg-gray-100'
              ]"
            >
              <i :class="['fas', item.icon, 'w-5 text-center mt-0.5']"></i>
              <div class="flex-1">
                <div class="font-medium">{{ item.title }}</div>
                <div class="text-xs text-gray-500 mt-0.5">{{ item.description }}</div>
              </div>
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
      <div class="p-8 max-w-4xl">
        <slot />
      </div>
    </div>
  </div>
</template>
