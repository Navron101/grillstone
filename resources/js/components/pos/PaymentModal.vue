<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 w-96 max-w-md mx-4">
      <div class="text-center mb-6">
        <div class="w-16 h-16 mx-auto mb-4 bg-orange-100 rounded-full flex items-center justify-center">
          <i class="fas fa-dollar-sign text-orange-600 text-2xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800">Complete Payment</h3>
        <p class="text-gray-600 mt-2">Total Amount: <span class="font-semibold">JMD {{ money(totalCents) }}</span></p>
      </div>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Amount Tendered</label>
          <input type="number" step="0.01" v-model.number="amount"
                 class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter amount">
        </div>
        <div v-if="showChange" class="bg-green-50 border border-green-200 rounded-lg p-4">
          <div class="text-sm text-green-700">Change Due:</div>
          <div class="text-2xl font-bold text-green-800">JMD {{ money(changeCents) }}</div>
        </div>
        <div class="flex space-x-3">
          <button @click="$emit('close')" class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
          <button @click="process" class="flex-1 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700">Process Payment</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
const props = defineProps<{ totalCents:number; method?:string }>()
const emit = defineEmits<{ (e:'process', tenderedCents:number|null):void; (e:'close'):void }>()
const amount = ref<number | null>(null)
watch(()=>props.totalCents,()=> amount.value = props.totalCents/100, { immediate:true })
const changeCents = computed(()=> {
  const t = Math.round((amount.value ?? 0) * 100)
  return Math.max(0, t - props.totalCents)
})
const showChange = computed(()=> (amount.value ?? 0) * 100 >= props.totalCents)
function money(c:number){ return (c/100).toLocaleString(undefined,{minimumFractionDigits:2}) }
function process(){ emit('process', amount.value ? Math.round(amount.value*100) : null) }
</script>
