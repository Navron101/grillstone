<template>
  <div class="min-h-screen bg-white p-8">
    <div v-if="loading" class="text-center py-12">
      <p class="text-gray-600">Loading settlement...</p>
    </div>

    <div v-else-if="settlement" class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8 pb-6 border-b-2 border-gray-800">
        <h1 class="text-3xl font-bold text-gray-900">Grillstone POS</h1>
        <h2 class="text-xl font-semibold text-gray-700 mt-2">Till Settlement Report</h2>
        <p class="text-sm text-gray-600 mt-2">Settlement #{{ settlement.settlement.id }}</p>
        <p class="text-sm text-gray-600">{{ formatDateTime(settlement.settlement.settlement_date) }}</p>
      </div>

      <!-- Variance Alert -->
      <div v-if="settlement.settlement.cash_variance_cents !== 0"
           class="mb-6 p-4 border-2 rounded"
           :class="settlement.settlement.cash_variance_cents > 0 ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50'">
        <h3 class="font-bold text-lg" :class="settlement.settlement.cash_variance_cents > 0 ? 'text-green-900' : 'text-red-900'">
          {{ settlement.settlement.cash_variance_cents > 0 ? 'CASH OVER' : 'CASH SHORT' }}
        </h3>
        <p class="text-sm" :class="settlement.settlement.cash_variance_cents > 0 ? 'text-green-700' : 'text-red-700'">
          Variance: {{ settlement.settlement.cash_variance_cents > 0 ? '+' : '' }}{{ formatMoney(settlement.settlement.cash_variance_cents) }}
        </p>
      </div>

      <div v-else class="mb-6 p-4 border-2 border-green-500 bg-green-50 rounded">
        <h3 class="font-bold text-lg text-green-900">PERFECT MATCH!</h3>
        <p class="text-sm text-green-700">Cash count matches expected amount exactly</p>
      </div>

      <!-- Period Info -->
      <div class="mb-6">
        <h3 class="font-bold text-gray-900 mb-2 text-lg border-b border-gray-300 pb-2">Settlement Period</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-gray-600">Period Start:</p>
            <p class="font-medium">{{ formatDateTime(settlement.settlement.period_start) }}</p>
          </div>
          <div>
            <p class="text-gray-600">Period End:</p>
            <p class="font-medium">{{ formatDateTime(settlement.settlement.period_end) }}</p>
          </div>
          <div>
            <p class="text-gray-600">Cashier:</p>
            <p class="font-medium">{{ settlement.settlement.user_name || 'Unknown' }}</p>
          </div>
          <div>
            <p class="text-gray-600">Status:</p>
            <p class="font-medium uppercase">{{ settlement.settlement.status }}</p>
          </div>
        </div>
      </div>

      <!-- Cash Breakdown -->
      <div class="mb-6">
        <h3 class="font-bold text-gray-900 mb-2 text-lg border-b border-gray-300 pb-2">Cash Breakdown</h3>
        <table class="w-full text-sm">
          <tbody>
            <tr class="border-b">
              <td class="py-2 text-gray-600">Expected Cash Sales:</td>
              <td class="py-2 text-right font-medium">{{ formatMoney(settlement.settlement.expected_cash_cents) }}</td>
            </tr>
            <tr class="border-b">
              <td class="py-2 text-gray-600">Paid Out (Payouts):</td>
              <td class="py-2 text-right font-medium text-red-600">-{{ formatMoney(settlement.settlement.paid_out_cents) }}</td>
            </tr>
            <tr class="border-b">
              <td class="py-2 text-gray-600">Paid In:</td>
              <td class="py-2 text-right font-medium text-green-600">+{{ formatMoney(settlement.settlement.paid_in_cents) }}</td>
            </tr>
            <tr class="border-b-2 border-gray-400">
              <td class="py-2 font-semibold">Net Cash Expected:</td>
              <td class="py-2 text-right font-bold">{{ formatMoney(settlement.settlement.net_cash_cents) }}</td>
            </tr>
            <tr class="border-b-2 border-gray-400">
              <td class="py-2 font-semibold">Actual Cash Counted:</td>
              <td class="py-2 text-right font-bold">{{ formatMoney(settlement.settlement.actual_cash_cents) }}</td>
            </tr>
            <tr class="border-b-2 border-gray-800">
              <td class="py-2 font-bold text-lg">VARIANCE:</td>
              <td class="py-2 text-right font-bold text-lg"
                  :class="settlement.settlement.cash_variance_cents > 0 ? 'text-green-600' : settlement.settlement.cash_variance_cents < 0 ? 'text-red-600' : 'text-gray-900'">
                {{ settlement.settlement.cash_variance_cents > 0 ? '+' : '' }}{{ formatMoney(settlement.settlement.cash_variance_cents) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Payment Methods Summary -->
      <div class="mb-6">
        <h3 class="font-bold text-gray-900 mb-2 text-lg border-b border-gray-300 pb-2">Payment Methods</h3>
        <table class="w-full text-sm">
          <tbody>
            <tr class="border-b">
              <td class="py-2 text-gray-600">Cash:</td>
              <td class="py-2 text-right font-medium">{{ formatMoney(settlement.settlement.net_cash_cents) }}</td>
            </tr>
            <tr class="border-b">
              <td class="py-2 text-gray-600">Credit/Debit Cards:</td>
              <td class="py-2 text-right font-medium">{{ formatMoney(settlement.settlement.expected_card_cents) }}</td>
            </tr>
            <tr class="border-b">
              <td class="py-2 text-gray-600">Gift Cards:</td>
              <td class="py-2 text-right font-medium">{{ formatMoney(settlement.settlement.expected_gift_card_cents || 0) }}</td>
            </tr>
            <tr class="border-b-2 border-gray-800">
              <td class="py-2 font-bold">TOTAL:</td>
              <td class="py-2 text-right font-bold">{{ formatMoney(settlement.settlement.total_sales_cents) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Sales Summary -->
      <div class="mb-6">
        <h3 class="font-bold text-gray-900 mb-2 text-lg border-b border-gray-300 pb-2">Sales Summary</h3>
        <table class="w-full text-sm">
          <tbody>
            <tr class="border-b">
              <td class="py-2 text-gray-600">Total Sales:</td>
              <td class="py-2 text-right font-medium">{{ formatMoney(settlement.settlement.total_sales_cents) }}</td>
            </tr>
            <tr class="border-b">
              <td class="py-2 text-gray-600">Number of Transactions:</td>
              <td class="py-2 text-right font-medium">{{ settlement.settlement.num_transactions }}</td>
            </tr>
            <tr class="border-b">
              <td class="py-2 text-gray-600">Average Transaction:</td>
              <td class="py-2 text-right font-medium">{{ formatMoney(settlement.settlement.num_transactions > 0 ? settlement.settlement.total_sales_cents / settlement.settlement.num_transactions : 0) }}</td>
            </tr>
            <tr class="border-b">
              <td class="py-2 text-gray-600">Cost of Goods Sold:</td>
              <td class="py-2 text-right font-medium">{{ formatMoney(settlement.settlement.cogs_cents || 0) }}</td>
            </tr>
            <tr class="border-b-2 border-gray-800">
              <td class="py-2 font-bold">Gross Profit:</td>
              <td class="py-2 text-right font-bold text-green-600">{{ formatMoney(settlement.settlement.profit_cents) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Credit Card Transactions -->
      <div v-if="settlement.card_transactions && settlement.card_transactions.length > 0" class="mb-6 page-break">
        <h3 class="font-bold text-gray-900 mb-2 text-lg border-b border-gray-300 pb-2">
          Credit Card Transactions ({{ settlement.card_transactions.length }})
        </h3>
        <table class="w-full text-sm border border-gray-300">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-3 py-2 text-left border-b">Time</th>
              <th class="px-3 py-2 text-left border-b">Order #</th>
              <th class="px-3 py-2 text-left border-b">Reference</th>
              <th class="px-3 py-2 text-right border-b">Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="txn in settlement.card_transactions" :key="txn.id" class="border-b">
              <td class="px-3 py-2">{{ formatTime(txn.created_at) }}</td>
              <td class="px-3 py-2">#{{ txn.order_id }}</td>
              <td class="px-3 py-2">{{ txn.reference || '-' }}</td>
              <td class="px-3 py-2 text-right font-medium">{{ formatMoney(txn.amount_cents) }}</td>
            </tr>
          </tbody>
          <tfoot class="bg-gray-100 font-bold border-t-2 border-gray-800">
            <tr>
              <td colspan="3" class="px-3 py-2">TOTAL</td>
              <td class="px-3 py-2 text-right">{{ formatMoney(settlement.settlement.expected_card_cents) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Notes -->
      <div v-if="settlement.settlement.notes" class="mb-6">
        <h3 class="font-bold text-gray-900 mb-2 text-lg border-b border-gray-300 pb-2">Notes</h3>
        <p class="text-sm text-gray-700">{{ settlement.settlement.notes }}</p>
      </div>

      <!-- Footer -->
      <div class="mt-8 pt-4 border-t-2 border-gray-800 text-center text-sm text-gray-600">
        <p>Powered by Grillstone POS</p>
        <p class="mt-1">Printed: {{ new Date().toLocaleString() }}</p>
      </div>
    </div>

    <div v-else class="text-center py-12">
      <p class="text-gray-600">Settlement not found</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const settlementId = page.props.settlementId as number

const settlement = ref<any>(null)
const loading = ref(true)

async function loadSettlement() {
  try {
    const resp = await fetch(`/api/settlements/${settlementId}`)
    if (!resp.ok) throw new Error('Failed to load settlement')
    settlement.value = await resp.json()
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function formatMoney(cents: number): string {
  return 'JMD ' + (cents / 100).toLocaleString('en-JM', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDateTime(dateStr: string): string {
  try {
    return new Date(dateStr).toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return dateStr
  }
}

function formatTime(dateStr: string): string {
  try {
    return new Date(dateStr).toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return dateStr
  }
}

onMounted(loadSettlement)
</script>

<style>
@media print {
  .page-break {
    page-break-before: always;
  }

  body {
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
  }
}
</style>
