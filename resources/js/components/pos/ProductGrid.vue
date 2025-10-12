<template>
  <div id="productList" class="h-full overflow-y-auto grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
    <div v-for="p in products" :key="p.id"
         class="product-card bg-white rounded-xl shadow-sm hover:shadow-lg cursor-pointer transition-all duration-300 overflow-hidden"
         @click="$emit('add', p)">
      <div class="relative">
        <img :src="p.img" :alt="p.name" class="w-full h-32 object-cover"
             @error="(e:any)=> e.target.src=`https://via.placeholder.com/200x200/f97316/ffffff?text=${encodeURIComponent(p.name)}`">
        <div v-if="p.popular" class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">Popular</div>
      </div>
      <div class="p-3">
        <h3 class="font-semibold text-gray-800 mb-1 truncate">{{ p.name }}</h3>
        <p class="text-xs text-gray-500 mb-2 line-clamp-2">{{ p.description }}</p>
        <div class="flex items-center justify-between">
          <span class="font-bold text-orange-600">JMD {{ (p.price/100).toLocaleString(undefined,{minimumFractionDigits:2}) }}</span>
          <button class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-1 rounded-lg text-sm"><i class="fas fa-plus"/></button>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
defineProps<{ products: Array<{id:number;name:string;price:number;img?:string;description?:string;popular?:boolean}> }>()
defineEmits(['add'])
</script>
<style scoped>
.product-card{ transition: all .3s cubic-bezier(.4,0,.2,1); }
</style>
