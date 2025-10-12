<template>
  <div class="flex flex-wrap gap-2 mt-4">
    <button v-for="c in categories" :key="c"
      class="px-4 py-2 rounded-lg font-medium transition-all"
      :class="modelValue===c ? 'bg-orange-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-orange-50 hover:text-orange-600 shadow-sm'"
      @click="$emit('update:modelValue', c)">
      {{ c }}
    </button>
  </div>
</template>
<script setup lang="ts">
defineProps<{ categories:string[], modelValue:string }>()
defineEmits(['update:modelValue'])
</script>
