<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
    <!-- Header -->
    <div class="text-center mb-8">
      <h2 class="text-2xl font-bold text-gray-900">Profit & Loss Statement</h2>
      <p class="text-sm text-gray-600 mt-1">
        For the period {{ formatDate(data.start_date) }} to {{ formatDate(data.end_date) }}
      </p>
    </div>

    <div class="max-w-4xl mx-auto">
      <!-- Summary Cards -->
      <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-500 rounded-lg p-4">
          <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-up text-green-600 text-xl"></i>
            <span class="text-sm font-semibold text-gray-700">Total Revenue</span>
          </div>
          <p class="text-2xl font-bold text-green-900">{{ formatMoney(data.revenue.total) }}</p>
        </div>

        <div class="bg-gradient-to-br from-red-50 to-rose-50 border-2 border-red-500 rounded-lg p-4">
          <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-down text-red-600 text-xl"></i>
            <span class="text-sm font-semibold text-gray-700">Total Costs</span>
          </div>
          <p class="text-2xl font-bold text-red-900">{{ formatMoney(data.cogs.total + data.expenses.total) }}</p>
        </div>

        <div :class="data.net_income_cents >= 0 ? 'from-blue-50 to-indigo-50 border-blue-500' : 'from-orange-50 to-red-50 border-orange-500'"
             class="bg-gradient-to-br border-2 rounded-lg p-4">
          <div class="flex items-center gap-2 mb-2">
            <i :class="data.net_income_cents >= 0 ? 'text-blue-600' : 'text-orange-600'" class="fas fa-dollar-sign text-xl"></i>
            <span class="text-sm font-semibold text-gray-700">Net {{ data.net_income_cents >= 0 ? 'Profit' : 'Loss' }}</span>
          </div>
          <p :class="data.net_income_cents >= 0 ? 'text-blue-900' : 'text-orange-900'" class="text-2xl font-bold">
            {{ formatMoney(Math.abs(data.net_income_cents)) }}
          </p>
        </div>
      </div>

      <!-- Detailed P&L -->
      <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
        <table class="w-full">
          <tbody class="divide-y divide-gray-200">
            <!-- Revenue Section -->
            <tr class="bg-green-100">
              <td colspan="2" class="py-3 px-4 font-bold text-gray-900 text-lg">INCOME</td>
            </tr>
            <tr>
              <td class="py-2 px-8 text-gray-700">Sales Revenue</td>
              <td class="py-2 px-4 text-right font-medium">{{ formatMoney(data.revenue.sales_revenue) }}</td>
            </tr>
            <tr>
              <td class="py-2 px-8 text-gray-700">Other Income</td>
              <td class="py-2 px-4 text-right font-medium">{{ formatMoney(data.revenue.other_revenue) }}</td>
            </tr>
            <tr class="bg-green-50">
              <td class="py-2 px-4 font-bold text-gray-900">TOTAL INCOME</td>
              <td class="py-2 px-4 text-right font-bold text-green-900">{{ formatMoney(data.revenue.total) }}</td>
            </tr>

            <!-- COGS Section -->
            <tr class="bg-orange-100">
              <td colspan="2" class="py-3 px-4 font-bold text-gray-900 text-lg">COST OF GOODS SOLD</td>
            </tr>
            <tr>
              <td class="py-2 px-8 text-gray-700">Direct Costs</td>
              <td class="py-2 px-4 text-right font-medium text-red-600">{{ formatMoney(data.cogs.total) }}</td>
            </tr>
            <tr class="bg-orange-50">
              <td class="py-2 px-4 font-bold text-gray-900">TOTAL COGS</td>
              <td class="py-2 px-4 text-right font-bold text-red-900">{{ formatMoney(data.cogs.total) }}</td>
            </tr>

            <!-- Gross Profit -->
            <tr class="bg-green-200">
              <td class="py-3 px-4 font-bold text-gray-900 text-lg">GROSS PROFIT</td>
              <td class="py-3 px-4 text-right font-bold text-green-900 text-lg">{{ formatMoney(data.gross_profit_cents) }}</td>
            </tr>
            <tr class="bg-green-100">
              <td class="py-2 px-8 text-sm text-gray-700">Gross Profit Margin</td>
              <td class="py-2 px-4 text-right text-sm font-semibold text-green-800">{{ data.gross_profit_margin.toFixed(2) }}%</td>
            </tr>

            <!-- Expenses Section -->
            <tr class="bg-blue-100">
              <td colspan="2" class="py-3 px-4 font-bold text-gray-900 text-lg">OPERATING EXPENSES</td>
            </tr>
            <tr>
              <td class="py-2 px-8 text-gray-700">Payroll & Wages</td>
              <td class="py-2 px-4 text-right font-medium text-red-600">{{ formatMoney(data.expenses.payroll) }}</td>
            </tr>
            <tr>
              <td class="py-2 px-8 text-gray-700">Other Operating Expenses</td>
              <td class="py-2 px-4 text-right font-medium text-red-600">{{ formatMoney(data.expenses.operating_expenses) }}</td>
            </tr>
            <tr class="bg-blue-50">
              <td class="py-2 px-4 font-bold text-gray-900">TOTAL EXPENSES</td>
              <td class="py-2 px-4 text-right font-bold text-red-900">{{ formatMoney(data.expenses.total) }}</td>
            </tr>

            <!-- Net Income/Loss -->
            <tr :class="data.net_income_cents >= 0 ? 'bg-green-300' : 'bg-red-300'">
              <td class="py-4 px-4 font-bold text-lg" :class="data.net_income_cents >= 0 ? 'text-green-900' : 'text-red-900'">
                NET {{ data.net_income_cents >= 0 ? 'INCOME' : 'LOSS' }}
              </td>
              <td class="py-4 px-4 text-right font-bold text-xl" :class="data.net_income_cents >= 0 ? 'text-green-900' : 'text-red-900'">
                {{ formatMoney(Math.abs(data.net_income_cents)) }}
              </td>
            </tr>
            <tr :class="data.net_income_cents >= 0 ? 'bg-green-200' : 'bg-red-200'">
              <td class="py-2 px-8 text-sm font-semibold" :class="data.net_income_cents >= 0 ? 'text-green-900' : 'text-red-900'">
                Net Profit Margin
              </td>
              <td class="py-2 px-4 text-right text-sm font-bold" :class="data.net_income_cents >= 0 ? 'text-green-900' : 'text-red-900'">
                {{ data.net_profit_margin.toFixed(2) }}%
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Performance Indicators -->
      <div class="mt-6 grid grid-cols-2 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-4">
          <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
            <i class="fas fa-chart-pie text-purple-600"></i>
            Expense Breakdown
          </h4>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-600">COGS as % of Revenue</span>
              <span class="font-semibold">{{ (data.revenue.total > 0 ? (data.cogs.total / data.revenue.total * 100) : 0).toFixed(2) }}%</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Operating Expenses as % of Revenue</span>
              <span class="font-semibold">{{ (data.revenue.total > 0 ? (data.expenses.total / data.revenue.total * 100) : 0).toFixed(2) }}%</span>
            </div>
          </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-4">
          <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
            <i class="fas fa-trophy text-yellow-600"></i>
            Profitability
          </h4>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-600">Gross Margin</span>
              <span class="font-semibold text-green-600">{{ data.gross_profit_margin.toFixed(2) }}%</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Net Margin</span>
              <span class="font-semibold" :class="data.net_profit_margin >= 0 ? 'text-green-600' : 'text-red-600'">
                {{ data.net_profit_margin.toFixed(2) }}%
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Export Buttons -->
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
    month: 'short',
    day: 'numeric'
  })
}

function print() {
  window.print()
}
</script>
