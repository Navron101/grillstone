<template>
  <aside class="p-4 border-l min-w-[300px]">
    <h2 class="text-lg font-semibold mb-4">Cart</h2>

    <ul class="space-y-1 mb-4">
      <li v-for="it in items" :key="it.id" class="flex justify-between">
        <span>{{ it.name }} × {{ it.qty }}</span>
        <span>{{ format(it.price * it.qty) }}</span>
      </li>
      <li v-if="!items.length" class="text-sm text-gray-500">No items yet.</li>
    </ul>

    <div class="space-y-1 text-sm">
      <div class="flex justify-between"><span>Subtotal</span><span>{{ format(subtotal) }}</span></div>
      <div class="flex justify-between"><span>Discount ({{ discountPercent }}%)</span><span>-{{ format(discount) }}</span></div>
      <div class="flex justify-between"><span>Tax ({{ (taxRate * 100).toFixed(0) }}%)</span><span>{{ format(tax) }}</span></div>
      <hr class="my-2" />
      <div class="flex justify-between font-semibold text-base">
        <span>Total</span><span>{{ format(total) }}</span>
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { computed } from 'vue'

type Item = { id: number | string; name: string; price: number; qty: number }

const props = withDefaults(defineProps<{
  items?: Item[]
  taxRate?: number            // e.g. 0.15
  discountPercent?: number    // 0–100
}>(), {
  items: () => [],
  taxRate: 0,
  discountPercent: 0,
})

const safeNum = (v: unknown) => Number.isFinite(Number(v)) ? Number(v) : 0

const subtotal = computed(() =>
  (props.items ?? []).reduce((sum, it) =>
    sum + safeNum(it.price) * safeNum(it.qty), 0)  // ✅ initial value = 0
)

const discount = computed(() => {
  const pct = Math.min(100, Math.max(0, safeNum(props.discountPercent))) / 100
  return subtotal.value * pct
})

const taxBase = computed(() => subtotal.value - discount.value)
const tax = computed(() => taxBase.value * safeNum(props.taxRate))
const total = computed(() => taxBase.value + tax.value)

const format = (n: number) => n.toFixed(2)
</script>
