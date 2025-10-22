<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
    <!-- Header -->
    <div class="text-center mb-8">
      <h2 class="text-2xl font-bold text-gray-900">Cash Flow Statement</h2>
      <p class="text-sm text-gray-600 mt-1">
        For the period {{ formatDate(data.start_date) }} to {{ formatDate(data.end_date) }}
      </p>
    </div>

    <div class="max-w-3xl mx-auto space-y-6">
      <!-- Opening Balance -->
      <div class="bg-blue-50 rounded-lg p-4 border-2 border-blue-600">
        <div class="flex justify-between text-lg font-bold text-blue-900">
          <span>OPENING CASH BALANCE</span>
          <span>{{ formatMoney(data.opening_balance_cents) }}</span>
        </div>
      </div>

      <!-- OPERATING ACTIVITIES -->
      <div>
        <h3 class="text-lg font-bold text-gray-900 mb-3 pb-2 border-b-2 border-green-600">CASH FLOW FROM OPERATING ACTIVITIES</h3>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between pl-4">
            <span class="text-gray-700">Cash Receipts from Customers</span>
            <span class="font-medium text-green-600">{{ formatMoney(data.operating_activities.cash_receipts) }}</span>
          </div>
          <div class="flex justify-between pl-4">
            <span class="text-gray-700">Cash Paid to Suppliers</span>
            <span class="font-medium text-red-600">{{ formatMoney(Math.abs(data.operating_activities.cash_paid_suppliers)) }}</span>
          </div>
          <div class="flex justify-between pl-4">
            <span class="text-gray-700">Cash Paid for Operating Expenses</span>
            <span class="font-medium text-red-600">{{ formatMoney(Math.abs(data.operating_activities.cash_paid_expenses)) }}</span>
          </div>
          <div class="flex justify-between pl-4 pt-2 border-t border-gray-200 font-semibold"
               :class="data.operating_activities.total >= 0 ? 'text-green-900' : 'text-red-900'">
            <span>Net Cash from Operating Activities</span>
            <span>{{ formatMoney(Math.abs(data.operating_activities.total)) }}</span>
          </div>
        </div>
      </div>

      <!-- INVESTING ACTIVITIES -->
      <div>
        <h3 class="text-lg font-bold text-gray-900 mb-3 pb-2 border-b-2 border-purple-600">CASH FLOW FROM INVESTING ACTIVITIES</h3>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between pl-4">
            <span class="text-gray-700">Purchase of Equipment</span>
            <span class="font-medium text-red-600">{{ formatMoney(Math.abs(data.investing_activities.equipment_purchases)) }}</span>
          </div>
          <div class="flex justify-between pl-4 pt-2 border-t border-gray-200 font-semibold"
               :class="data.investing_activities.total >= 0 ? 'text-green-900' : 'text-red-900'">
            <span>Net Cash from Investing Activities</span>
            <span>{{ formatMoney(Math.abs(data.investing_activities.total)) }}</span>
          </div>
        </div>
      </div>

      <!-- FINANCING ACTIVITIES -->
      <div>
        <h3 class="text-lg font-bold text-gray-900 mb-3 pb-2 border-b-2 border-orange-600">CASH FLOW FROM FINANCING ACTIVITIES</h3>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between pl-4">
            <span class="text-gray-700">Owner Investments</span>
            <span class="font-medium text-green-600">{{ formatMoney(data.financing_activities.owner_investments) }}</span>
          </div>
          <div class="flex justify-between pl-4">
            <span class="text-gray-700">Owner Withdrawals</span>
            <span class="font-medium text-red-600">{{ formatMoney(Math.abs(data.financing_activities.owner_withdrawals)) }}</span>
          </div>
          <div class="flex justify-between pl-4 pt-2 border-t border-gray-200 font-semibold"
               :class="data.financing_activities.total >= 0 ? 'text-green-900' : 'text-red-900'">
            <span>Net Cash from Financing Activities</span>
            <span>{{ formatMoney(Math.abs(data.financing_activities.total)) }}</span>
          </div>
        </div>
      </div>

      <!-- NET CHANGE IN CASH -->
      <div :class="data.net_cash_flow_cents >= 0 ? 'bg-green-50 border-green-600' : 'bg-red-50 border-red-600'"
           class="rounded-lg p-4 border-2">
        <div class="flex justify-between text-lg font-bold"
             :class="data.net_cash_flow_cents >= 0 ? 'text-green-900' : 'text-red-900'">
          <span>NET {{ data.net_cash_flow_cents >= 0 ? 'INCREASE' : 'DECREASE' }} IN CASH</span>
          <span>{{ formatMoney(Math.abs(data.net_cash_flow_cents)) }}</span>
        </div>
      </div>

      <!-- CLOSING BALANCE -->
      <div class="bg-blue-50 rounded-lg p-4 border-2 border-blue-600">
        <div class="flex justify-between text-xl font-bold text-blue-900">
          <span>CLOSING CASH BALANCE</span>
          <span>{{ formatMoney(data.closing_balance_cents) }}</span>
        </div>
      </div>

      <!-- Cash Flow Summary -->
      <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
        <h4 class="font-semibold text-gray-800 mb-3">Cash Flow Summary</h4>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-gray-600">Operating Cash Flow</p>
            <p class="text-lg font-bold" :class="data.operating_activities.total >= 0 ? 'text-green-600' : 'text-red-600'">
              {{ formatMoney(Math.abs(data.operating_activities.total)) }}
            </p>
          </div>
          <div>
            <p class="text-gray-600">Investing Cash Flow</p>
            <p class="text-lg font-bold" :class="data.investing_activities.total >= 0 ? 'text-green-600' : 'text-red-600'">
              {{ formatMoney(Math.abs(data.investing_activities.total)) }}
            </p>
          </div>
          <div>
            <p class="text-gray-600">Financing Cash Flow</p>
            <p class="text-lg font-bold" :class="data.financing_activities.total >= 0 ? 'text-green-600' : 'text-red-600'">
              {{ formatMoney(Math.abs(data.financing_activities.total)) }}
            </p>
          </div>
          <div>
            <p class="text-gray-600">Net Change in Cash</p>
            <p class="text-lg font-bold" :class="data.net_cash_flow_cents >= 0 ? 'text-green-600' : 'text-red-600'">
              {{ formatMoney(Math.abs(data.net_cash_flow_cents)) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Reconciliation -->
      <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
        <h4 class="font-semibold text-blue-900 mb-2">Cash Balance Reconciliation</h4>
        <div class="space-y-1 text-sm text-blue-800">
          <div class="flex justify-between">
            <span>Opening Balance:</span>
            <span class="font-medium">{{ formatMoney(data.opening_balance_cents) }}</span>
          </div>
          <div class="flex justify-between">
            <span>Add: Net Cash Flow:</span>
            <span class="font-medium" :class="data.net_cash_flow_cents >= 0 ? 'text-green-700' : 'text-red-700'">
              {{ formatMoney(data.net_cash_flow_cents) }}
            </span>
          </div>
          <div class="flex justify-between pt-2 border-t border-blue-300 font-bold">
            <span>Closing Balance:</span>
            <span>{{ formatMoney(data.closing_balance_cents) }}</span>
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
