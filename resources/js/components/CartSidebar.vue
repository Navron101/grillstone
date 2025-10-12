<template>
  <aside class="w-full lg:w-[380px] xl:w-[420px] border-l bg-white/90 dark:bg-zinc-900/70 backdrop-blur p-5 space-y-4 sticky top-16">
    <div class="flex items-center justify-between">
      <h2 class="text-lg font-semibold">Cart</h2>
      <span class="text-xs text-zinc-500" v-if="items.length">{{ items.length }} item{{ items.length>1?'s':'' }}</span>
    </div>

    <!-- Lines -->
    <ul class="rounded-2xl border divide-y bg-white/80 dark:bg-zinc-900/60">
      <li v-for="it in items" :key="it.id" class="px-3 py-3">
        <div class="flex items-center gap-3">
          <div class="min-w-0 flex-1">
            <p class="truncate font-medium leading-tight">{{ it.name }}</p>
            <p class="text-xs text-zinc-500">JMD {{ nf(it.price) }} × {{ it.qty }}</p>
          </div>

          <!-- Stepper -->
          <div class="flex items-center gap-1">
            <button class="size-8 rounded-lg border hover:bg-zinc-50 active:scale-95" @click="$emit('dec', it)">−</button>
            <div class="w-8 text-center font-semibold">{{ it.qty }}</div>
            <button class="size-8 rounded-lg border hover:bg-zinc-50 active:scale-95" @click="$emit('inc', it)">+</button>
          </div>

          <div class="w-20 text-right font-semibold tabular-nums">JMD {{ nf(it.price * it.qty) }}</div>
          <button class="ml-1 size-8 rounded-lg border hover:bg-zinc-50 active:scale-95" title="Remove" @click="$emit('remove', it)">×</button>
        </div>
      </li>

      <li v-if="!items.length" class="px-3 py-10 text-center text-sm text-zinc-500">
        Cart is empty. Tap a product to add.
      </li>
    </ul>

    <!-- Totals -->
    <div class="rounded-2xl border p-4 bg-white/80 dark:bg-zinc-900/60 space-y-2 text-sm">
      <div class="flex justify-between"><span>Subtotal</span><span class="tabular-nums">JMD {{ nf(subtotal) }}</span></div>
      <div class="flex justify-between">
        <span class="flex items-center gap-2">
          Discount
          <input type="number" min="0" max="100"
                 class="w-16 h-8 rounded-lg border px-2 bg-white/90 dark:bg-zinc-900/70"
                 :value="discountPercent"
                 @input="$emit('update:discountPercent', toNum(($event.target as HTMLInputElement).value))"> %
        </span>
        <span class="tabular-nums">− JMD {{ nf(discount) }}</span>
      </div>
      <div class="flex justify-between"><span>Tax ({{ (taxRate * 100).toFixed(0) }}%)</span><span class="tabular-nums">JMD {{ nf(tax) }}</span></div>
      <hr class="my-2">
      <div class="flex justify-between text-base font-semibold">
        <span>Total</span><span class="tabular-nums">JMD {{ nf(total) }}</span>
      </div>
    </div>

    <!-- Pay -->
    <div class="grid grid-cols-3 gap-2">
      <button class="h-12 rounded-xl border font-medium hover:bg-emerald-50 active:scale-95"
              @click="$emit('pay', { method: 'Cash', amount: total })">Cash</button>
      <button class="h-12 rounded-xl border font-medium hover:bg-indigo-50 active:scale-95"
              @click="$emit('pay', { method: 'Card', amount: total })">Card</button>
      <button class="h-12 rounded-xl border font-medium hover:bg-amber-50 active:scale-95"
              @click="$emit('pay', { method: 'Gift', amount: total })">Gift</button>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { computed } from 'vue'
type Item = { id:number|string; name:string; price:number; qty:number }

const props = withDefaults(defineProps<{
  items?: Item[]
  taxRate?: number
  discountPercent?: number
}>(), { items: () => [], taxRate: 0, discountPercent: 0 })

const toNum = (v: unknown) => { const n = Number(v); return Number.isFinite(n) ? n : 0 }

const subtotal = computed(() => (props.items??[]).reduce((s, it) => s + toNum(it.price)*toNum(it.qty), 0))
const discount = computed(() => subtotal.value * Math.min(100, Math.max(0, toNum(props.discountPercent))) / 100)
const taxBase  = computed(() => subtotal.value - discount.value)
const tax      = computed(() => taxBase.value * toNum(props.taxRate))
const total    = computed(() => taxBase.value + tax.value)

const nf = (n:number) => new Intl.NumberFormat('en-JM', { maximumFractionDigits: 0 }).format(n)
</script>
