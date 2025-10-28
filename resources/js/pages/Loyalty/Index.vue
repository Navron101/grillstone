<template>
  <LoyaltyLayout>
    <div class="min-h-screen bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
          <p class="mt-2 text-gray-600">Manage company partnerships and employee benefits</p>
        </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
              <i class="fas fa-building text-white text-2xl"></i>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Active Companies</dt>
                <dd class="text-3xl font-semibold text-gray-900">{{ stats.companies }}</dd>
              </dl>
            </div>
          </div>
          <div class="mt-4">
            <Link href="/loyalty/companies" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
              View all companies →
            </Link>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
              <i class="fas fa-users text-white text-2xl"></i>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Active Employees</dt>
                <dd class="text-3xl font-semibold text-gray-900">{{ stats.employees }}</dd>
              </dl>
            </div>
          </div>
          <div class="mt-4">
            <Link href="/loyalty/employees" class="text-green-600 hover:text-green-700 text-sm font-medium">
              View all employees →
            </Link>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0 bg-orange-500 rounded-md p-3">
              <i class="fas fa-file-invoice-dollar text-white text-2xl"></i>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Pending Settlements</dt>
                <dd class="text-3xl font-semibold text-gray-900">{{ stats.pendingSettlements }}</dd>
              </dl>
            </div>
          </div>
          <div class="mt-4">
            <Link href="/loyalty/settlements" class="text-orange-600 hover:text-orange-700 text-sm font-medium">
              View settlements →
            </Link>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Link href="/loyalty/companies" class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6">
          <div class="flex items-center mb-4">
            <i class="fas fa-building text-blue-500 text-3xl"></i>
            <h3 class="ml-4 text-lg font-semibold text-gray-900">Companies</h3>
          </div>
          <p class="text-gray-600 mb-4">Manage participating companies, discount rates, and caps</p>
          <div class="text-blue-600 font-medium">Manage companies →</div>
        </Link>

        <Link href="/loyalty/employees" class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6">
          <div class="flex items-center mb-4">
            <i class="fas fa-id-card text-green-500 text-3xl"></i>
            <h3 class="ml-4 text-lg font-semibold text-gray-900">Employees</h3>
          </div>
          <p class="text-gray-600 mb-4">Add and manage employee accounts for loyalty discounts</p>
          <div class="text-green-600 font-medium">Manage employees →</div>
        </Link>

        <Link href="/loyalty/settlements" class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6">
          <div class="flex items-center mb-4">
            <i class="fas fa-file-invoice text-orange-500 text-3xl"></i>
            <h3 class="ml-4 text-lg font-semibold text-gray-900">Settlements</h3>
          </div>
          <p class="text-gray-600 mb-4">Generate monthly settlement reports for companies</p>
          <div class="text-orange-600 font-medium">View settlements →</div>
        </Link>
      </div>

      <!-- How it Works -->
      <div class="mt-12 bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">How the Loyalty Program Works</h2>
        <div class="grid md:grid-cols-4 gap-6">
          <div>
            <div class="bg-blue-100 rounded-full w-12 h-12 flex items-center justify-center mb-3">
              <span class="text-blue-600 font-bold">1</span>
            </div>
            <h4 class="font-semibold text-gray-900 mb-2">Company Signs Up</h4>
            <p class="text-sm text-gray-600">Companies register with custom discount percentage and optional caps</p>
          </div>
          <div>
            <div class="bg-green-100 rounded-full w-12 h-12 flex items-center justify-center mb-3">
              <span class="text-green-600 font-bold">2</span>
            </div>
            <h4 class="font-semibold text-gray-900 mb-2">Employees Get Discount</h4>
            <p class="text-sm text-gray-600">Staff use phone/email at checkout to receive instant discounts</p>
          </div>
          <div>
            <div class="bg-orange-100 rounded-full w-12 h-12 flex items-center justify-center mb-3">
              <span class="text-orange-600 font-bold">3</span>
            </div>
            <h4 class="font-semibold text-gray-900 mb-2">Monthly Settlement</h4>
            <p class="text-sm text-gray-600">Generate settlement reports showing total discounts per employee</p>
          </div>
          <div>
            <div class="bg-purple-100 rounded-full w-12 h-12 flex items-center justify-center mb-3">
              <span class="text-purple-600 font-bold">4</span>
            </div>
            <h4 class="font-semibold text-gray-900 mb-2">Company Pays Back</h4>
            <p class="text-sm text-gray-600">Company deducts from payroll and remits total to Grillstone</p>
          </div>
        </div>
      </div>
    </div>
    </div>
  </LoyaltyLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import LoyaltyLayout from '@/layouts/LoyaltyLayout.vue'

const stats = ref({
  companies: 0,
  employees: 0,
  pendingSettlements: 0,
})

onMounted(async () => {
  // Fetch stats from API
  try {
    const [companiesRes, employeesRes] = await Promise.all([
      fetch('/api/loyalty/companies'),
      fetch('/api/loyalty/employees'),
    ])

    const companies = await companiesRes.json()
    const employees = await employeesRes.json()

    stats.value.companies = companies.filter((c: any) => c.status === 'active').length
    stats.value.employees = employees.filter((e: any) => e.status === 'active').length
    stats.value.pendingSettlements = 0 // TODO: Add settlements API
  } catch (error) {
    console.error('Error fetching loyalty stats:', error)
  }
})
</script>
