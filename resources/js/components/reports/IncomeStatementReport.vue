<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
    <!-- Header -->
    <div class="text-center mb-8">
      <h2 class="text-2xl font-bold text-gray-900">Income Statement</h2>
      <p class="text-sm text-gray-600 mt-1">
        For the period {{ formatDate(data.start_date) }} to {{ formatDate(data.end_date) }}
      </p>
    </div>

    <div class="max-w-3xl mx-auto space-y-6">
      <!-- REVENUE -->
      <div>
        <h3 class="text-lg font-bold text-gray-900 mb-3 pb-2 border-b-2 border-green-600">REVENUE</h3>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between pl-4">
            <span class="text-gray-700">Sales Revenue</span>
            <span class="font-medium">{{ formatMoney(data.revenue.sales_revenue) }}</span>
          </div>
          <div class="flex justify-between pl-4">
            <span class="text-gray-700">Other Revenue</span>
            <span class="font-medium">{{ formatMoney(data.revenue.other_revenue) }}</span>
          </div>
          <div class="flex justify-between pl-4 pt-2 border-t border-gray-200 font-semibold">
            <span>Total Revenue</span>
            <span>{{ formatMoney(data.revenue.total) }}</span>
          </div>
        </div>
      </div>

      <!-- COST OF GOODS SOLD -->
      <div>
        <h3 class="text-lg font-bold text-gray-900 mb-3 pb-2 border-b-2 border-orange-600">COST OF GOODS SOLD</h3>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between pl-4">
            <span class="text-gray-700">Cost of Goods Sold</span>
            <span class="font-medium text-red-600">{{ formatMoney(data.cogs.total) }}</span>
          </div>
        </div>
      </div>

      <!-- GROSS PROFIT -->
      <div class="bg-green-50 rounded-lg p-4 border-2 border-green-600">
        <div class="flex justify-between text-lg font-bold text-green-900">
          <span>GROSS PROFIT</span>
          <span>{{ formatMoney(data.gross_profit_cents) }}</span>
        </div>
        <div class="flex justify-between text-sm text-green-700 mt-1">
          <span>Gross Profit Margin</span>
          <span>{{ data.gross_profit_margin.toFixed(2) }}%</span>
        </div>
      </div>

      <!-- OPERATING EXPENSES -->
      <div>
        <h3 class="text-lg font-bold text-gray-900 mb-3 pb-2 border-b-2 border-blue-600">OPERATING EXPENSES</h3>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between pl-4">
            <span class="text-gray-700">Payroll Expenses</span>
            <span class="font-medium text-red-600">{{ formatMoney(data.expenses.payroll) }}</span>
          </div>
          <div class="flex justify-between pl-4">
            <span class="text-gray-700">Other Operating Expenses</span>
            <span class="font-medium text-red-600">{{ formatMoney(data.expenses.operating_expenses) }}</span>
          </div>
          <div class="flex justify-between pl-4 pt-2 border-t border-gray-200 font-semibold">
            <span>Total Operating Expenses</span>
            <span class="text-red-600">{{ formatMoney(data.expenses.total) }}</span>
          </div>
        </div>
      </div>

      <!-- NET INCOME -->
      <div :class="data.net_income_cents >= 0 ? 'bg-green-50 border-green-600' : 'bg-red-50 border-red-600'"
           class="rounded-lg p-4 border-2">
        <div class="flex justify-between text-xl font-bold" :class="data.net_income_cents >= 0 ? 'text-green-900' : 'text-red-900'">
          <span>NET INCOME {{ data.net_income_cents < 0 ? '(LOSS)' : '' }}</span>
          <span>{{ formatMoney(Math.abs(data.net_income_cents)) }}</span>
        </div>
        <div class="flex justify-between text-sm mt-1" :class="data.net_income_cents >= 0 ? 'text-green-700' : 'text-red-700'">
          <span>Net Profit Margin</span>
          <span>{{ data.net_profit_margin.toFixed(2) }}%</span>
        </div>
      </div>

      <!-- Key Metrics -->
      <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
        <h4 class="font-semibold text-gray-800 mb-3">Key Metrics Summary</h4>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-gray-600">Total Revenue</p>
            <p class="text-lg font-bold text-gray-900">{{ formatMoney(data.revenue.total) }}</p>
          </div>
          <div>
            <p class="text-gray-600">Total Expenses</p>
            <p class="text-lg font-bold text-red-600">{{ formatMoney(data.cogs.total + data.expenses.total) }}</p>
          </div>
          <div>
            <p class="text-gray-600">Gross Profit Margin</p>
            <p class="text-lg font-bold text-green-600">{{ data.gross_profit_margin.toFixed(2) }}%</p>
          </div>
          <div>
            <p class="text-gray-600">Net Profit Margin</p>
            <p class="text-lg font-bold" :class="data.net_profit_margin >= 0 ? 'text-green-600' : 'text-red-600'">
              {{ data.net_profit_margin.toFixed(2) }}%
            </p>
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
