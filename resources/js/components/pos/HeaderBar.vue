<template>
  <header class="glass-effect shadow-lg">
    <div class="px-6 py-4 flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
          <i class="fas fa-utensils text-white text-lg"></i>
        </div>
        <div>
          <h1 class="text-xl font-bold text-gray-800">Grillstone POS</h1>
          <p class="text-sm text-gray-600">Table #{{ table }}</p>
        </div>
      </div>
      <div class="flex items-center space-x-4">
        <div class="text-right">
          <p class="text-sm text-gray-600">{{ time }}</p>
          <p class="text-sm font-medium text-gray-800">Cashier: {{ cashier }}</p>
        </div>
        <button class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
          <i class="fas fa-cog text-lg"></i>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
defineProps<{ cashier:string, table:number }>()

const time = ref('')
onMounted(() => {
  const update = () => time.value = new Date().toLocaleTimeString()
  update()
  setInterval(update, 1000)
})
</script>

<style scoped>
.glass-effect{ background:rgba(255,255,255,.95); backdrop-filter:blur(20px); border:1px solid rgba(255,255,255,.2);}
</style>
