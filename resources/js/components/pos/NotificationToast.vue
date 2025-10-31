<template>
  <div class="fixed top-5 right-5 z-[1000]">
    <div class="notification bg-white rounded-lg shadow-lg p-4 w-80 flex items-center">
      <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3"
           :class="type==='success' ? 'bg-green-500' : type==='error' ? 'bg-red-500' : 'bg-yellow-500'">
        <i :class="icon" class="text-white"></i>
      </div>
      <div class="flex-1">
        <p class="font-medium text-gray-800">{{ title }}</p>
        <p class="text-sm text-gray-600">{{ message }}</p>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{ type:'success'|'error'|'warning'; title:string; message:string }>()
const icon = computed(()=> props.type==='success' ? 'fas fa-check' : props.type==='error' ? 'fas fa-times' : 'fas fa-exclamation' )
</script>
<style scoped>
.notification{ transform: translateX(0); }
</style>
