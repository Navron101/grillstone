<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
    <!-- Header -->
    <div class="text-center mb-8">
      <h2 class="text-2xl font-bold text-gray-900">Balance Sheet</h2>
      <p class="text-sm text-gray-600 mt-1">As of {{ formatDate(data.as_of_date) }}</p>
    </div>

    <!-- Balance Status -->
    <div v-if="data.balanced" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
      <div class="flex items-center gap-3">
        <i class="fas fa-check-circle text-2xl text-green-600"></i>
        <div>
          <h3 class="font-bold text-green-900">Balance Sheet is Balanced</h3>
          <p class="text-sm text-green-700">Assets = Liabilities + Equity</p>
        </div>
      </div>
    </div>
    <div v-else class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
      <div class="flex items-center gap-3">
        <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
        <div>
          <h3 class="font-bold text-red-900">Balance Sheet is NOT Balanced</h3>
          <p class="text-sm text-red-700">Please review your entries</p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- ASSETS -->
      <div>
        <h3 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b-2 border-blue-600">ASSETS</h3>

        <!-- Current Assets -->
        <div class="mb-6">
          <h4 class="font-semibold text-gray-800 mb-2">Current Assets</h4>
          <div class="space-y-1 text-sm">
            <div class="flex justify-between pl-4">
              <span class="text-gray-700">Cash</span>
              <span class="font-medium">{{ formatMoney(data.assets.current_assets.cash) }}</span>
            </div>
            <div class="flex justify-between pl-4">
              <span class="text-gray-700">Inventory</span>
              <span class="font-medium">{{ formatMoney(data.assets.current_assets.inventory) }}</span>
            </div>
            <div class="flex justify-between pl-4">
              <span class="text-gray-700">Accounts Receivable</span>
              <span class="font-medium">{{ formatMoney(data.assets.current_assets.accounts_receivable) }}</span>
            </div>
            <div class="flex justify-between pl-4 pt-2 border-t border-gray-200 font-semibold">
              <span>Total Current Assets</span>
              <span>{{ formatMoney(data.assets.current_assets.total) }}</span>
            </div>
          </div>
        </div>

        <!-- Fixed Assets -->
        <div class="mb-6">
          <h4 class="font-semibold text-gray-800 mb-2">Fixed Assets</h4>
          <div class="space-y-1 text-sm">
            <div class="flex justify-between pl-4">
              <span class="text-gray-700">Equipment</span>
              <span class="font-medium">{{ formatMoney(data.assets.fixed_assets.equipment) }}</span>
            </div>
            <div class="flex justify-between pl-4 pt-2 border-t border-gray-200 font-semibold">
              <span>Total Fixed Assets</span>
              <span>{{ formatMoney(data.assets.fixed_assets.total) }}</span>
            </div>
          </div>
        </div>

        <!-- Total Assets -->
        <div class="bg-blue-50 rounded-lg p-3 border-2 border-blue-600">
          <div class="flex justify-between text-lg font-bold text-blue-900">
            <span>TOTAL ASSETS</span>
            <span>{{ formatMoney(data.total_assets) }}</span>
          </div>
        </div>
      </div>

      <!-- LIABILITIES & EQUITY -->
      <div>
        <h3 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b-2 border-red-600">LIABILITIES & EQUITY</h3>

        <!-- Current Liabilities -->
        <div class="mb-6">
          <h4 class="font-semibold text-gray-800 mb-2">Current Liabilities</h4>
          <div class="space-y-1 text-sm">
            <div class="flex justify-between pl-4">
              <span class="text-gray-700">Accounts Payable</span>
              <span class="font-medium">{{ formatMoney(data.liabilities.current_liabilities.accounts_payable) }}</span>
            </div>
            <div class="flex justify-between pl-4">
              <span class="text-gray-700">Payroll Payable</span>
              <span class="font-medium">{{ formatMoney(data.liabilities.current_liabilities.payroll_payable) }}</span>
            </div>
            <div class="flex justify-between pl-4">
              <span class="text-gray-700">Employee Tabs</span>
              <span class="font-medium">{{ formatMoney(data.liabilities.current_liabilities.employee_tabs) }}</span>
            </div>
            <div class="flex justify-between pl-4 pt-2 border-t border-gray-200 font-semibold">
              <span>Total Current Liabilities</span>
              <span>{{ formatMoney(data.liabilities.current_liabilities.total) }}</span>
            </div>
          </div>
        </div>

        <!-- Long-term Liabilities -->
        <div class="mb-6">
          <h4 class="font-semibold text-gray-800 mb-2">Long-term Liabilities</h4>
          <div class="space-y-1 text-sm">
            <div class="flex justify-between pl-4">
              <span class="text-gray-700">Loans</span>
              <span class="font-medium">{{ formatMoney(data.liabilities.long_term_liabilities.loans) }}</span>
            </div>
            <div class="flex justify-between pl-4 pt-2 border-t border-gray-200 font-semibold">
              <span>Total Long-term Liabilities</span>
              <span>{{ formatMoney(data.liabilities.long_term_liabilities.total) }}</span>
            </div>
          </div>
        </div>

        <!-- Total Liabilities -->
        <div class="bg-gray-100 rounded-lg p-3 mb-4 border border-gray-300">
          <div class="flex justify-between font-bold text-gray-900">
            <span>Total Liabilities</span>
            <span>{{ formatMoney(data.total_liabilities) }}</span>
          </div>
        </div>

        <!-- Equity -->
        <div class="mb-6">
          <h4 class="font-semibold text-gray-800 mb-2">Equity</h4>
          <div class="space-y-1 text-sm">
            <div class="flex justify-between pl-4">
              <span class="text-gray-700">Owner's Equity</span>
              <span class="font-medium">{{ formatMoney(data.equity.owners_equity) }}</span>
            </div>
            <div class="flex justify-between pl-4">
              <span class="text-gray-700">Retained Earnings</span>
              <span class="font-medium">{{ formatMoney(data.equity.retained_earnings) }}</span>
            </div>
            <div class="flex justify-between pl-4 pt-2 border-t border-gray-200 font-semibold">
              <span>Total Equity</span>
              <span>{{ formatMoney(data.total_equity) }}</span>
            </div>
          </div>
        </div>

        <!-- Total Liabilities & Equity -->
        <div class="bg-red-50 rounded-lg p-3 border-2 border-red-600">
          <div class="flex justify-between text-lg font-bold text-red-900">
            <span>TOTAL LIABILITIES & EQUITY</span>
            <span>{{ formatMoney(data.total_liabilities_and_equity) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Export Button -->
    <div class="mt-8 flex justify-end gap-3">
      <button @click="$emit('export')" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold">
        <i class="fas fa-file-pdf mr-2"></i>Export to PDF
      </button>
      <button @click="print" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold">
        <i class="fas fa-print mr-2"></i>Print
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  data: any
}>()

defineEmits<{
  export: []
}>()

function formatMoney(cents: number): string {
  return 'JMD ' + (cents / 100).toLocaleString('en-JM', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

function print() {
  window.print()
}
</script>
