<template>
  <MainShell
    title="Inventory — Dishes"
    :subtitle="'Define dishes, their variants and ingredients'"
    :cashier="'Cashier'"
    :sidebarOpen="sidebarOpen"
    :routes="{ pos: '/pos', inventory: '/inventory', grn: '/inventory/grn' }"
    @toggle-sidebar="toggleSidebar"
    @open-settings="openSettings"
    @logout="logout"
  >
    <section class="flex-1 p-4 flex flex-col">
      <div class="glass-effect rounded-2xl p-4 mb-4 shadow-lg flex items-center gap-3">
        <button @click="openCreateDish" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg">
          <i class="fas fa-plus mr-2"></i> New Dish
        </button>
        <span class="text-sm text-gray-600">Click a dish to manage variants & ingredients.</span>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <!-- Dishes list -->
        <div class="glass-effect rounded-2xl p-4 shadow-lg">
          <h3 class="font-semibold text-gray-800 mb-3">Dishes</h3>
          <div v-if="loadingDishes" class="text-gray-500">Loading…</div>
          <div v-else>
            <div v-for="d in dishes" :key="d.id"
                 class="bg-white rounded-xl p-3 mb-2 shadow-sm hover:shadow-md cursor-pointer"
                 @click="selectDish(d)">
              <div class="flex items-center justify-between">
                <div>
                  <div class="font-medium text-gray-800">{{ d.name }}</div>
                  <div class="text-xs text-gray-500">JMD {{ (d.price_cents/100).toFixed(2) }}</div>
                </div>
                <div class="flex gap-2">
                  <button @click.stop="renameDish(d)" class="text-gray-600 hover:text-gray-900 px-2">
                    <i class="fas fa-pen"></i>
                  </button>
                  <button @click.stop="removeDish(d)" class="text-red-600 hover:text-red-800 px-2">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>
            <div v-if="!dishes.length" class="text-gray-500">No dishes yet</div>
          </div>
        </div>

        <!-- Variants + Ingredients -->
        <div class="glass-effect rounded-2xl p-4 shadow-lg">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-gray-800">Variants</h3>
            <button v-if="currentDish" @click="addVariant" class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-1 rounded-lg text-sm">
              <i class="fas fa-plus mr-1"></i> New Variant
            </button>
          </div>

          <div v-if="!currentDish" class="text-gray-500">Select a dish to manage variants.</div>

          <div v-else>
            <div v-for="v in variants" :key="v.id" class="bg-white rounded-xl p-3 mb-2 shadow-sm">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="font-medium text-gray-800">{{ v.name || 'Variant' }}</span>
                  <span v-if="v.is_default" class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Default</span>
                </div>
                <div class="flex gap-2">
                  <button @click="ensureComponentsLoaded(v)" class="px-2 text-gray-700 hover:text-gray-900">
                    <i class="fas fa-list-check mr-1"></i> Edit Ingredients
                  </button>
                  <button v-if="!v.is_default" @click="setDefaultVariant(v)" class="px-2 text-amber-700 hover:text-amber-900">
                    <i class="fas fa-star mr-1"></i> Set as default
                  </button>
                  <button @click="deleteVariant(v)" class="px-2 text-red-600 hover:text-red-800">
                    <i class="fas fa-trash mr-1"></i> Delete
                  </button>
                </div>
              </div>

              <!-- Ingredients table -->
              <div v-if="components[v.id]" class="mt-3">
                <table class="w-full text-sm">
                  <thead>
                    <tr class="text-left text-gray-600">
                      <th class="py-1">Ingredient</th>
                      <th class="py-1">Qty</th>
                      <th class="py-1">Unit</th>
                      <th class="py-1 w-10"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="row in components[v.id]" :key="row.id" class="border-t">
                      <td class="py-1">{{ row.ingredient_name }}</td>
                      <td class="py-1">{{ row.qty_per_unit }}</td>
                      <td class="py-1">{{ row.unit_name || row.default_unit || 'unit' }}</td>
                      <td class="py-1 text-right">
                        <button @click="removeComponent(v, row)" class="text-red-600 hover:text-red-800">
                          <i class="fas fa-xmark"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>

                <!-- Add ingredient row -->
                <div class="mt-3 flex gap-2 items-end">
                  <select v-model="newComp.ingredient_product_id" class="border rounded px-2 py-1 flex-1">
                    <option value="">Select ingredient…</option>
                    <option v-for="ing in ingredients" :key="ing.id" :value="ing.id">{{ ing.name }}</option>
                  </select>
                  <input v-model.number="newComp.qty_per_unit" type="number" min="0" step="0.0001" class="border rounded px-2 py-1 w-28" placeholder="Qty">
                  <select v-model="newComp.unit_name" class="border rounded px-2 py-1 w-28">
                    <option v-for="u in UNITS" :key="u" :value="u">{{ u }}</option>
                  </select>
                  <button @click="addComponent(v)" class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-1 rounded">
                    Add
                  </button>
                </div>
              </div>
            </div>

            <div v-if="currentDish && !variants.length" class="text-gray-500">No variants yet</div>
          </div>
        </div>
      </div>
    </section>
  </MainShell>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'

// If you have the alias "@", use this:
import MainShell from '@/Components/Shell/MainShell.vue'
import { UNITS } from '@/constants/units'

// If you do NOT have the "@" alias, replace those two imports with:
// import MainShell from '../../Components/Shell/MainShell.vue'
// const UNITS = ['unit','g','kg','lb','oz','ml','l','tsp','tbsp','cup','pcs','dozen']

const sidebarOpen = ref(true)
function toggleSidebar(){ sidebarOpen.value = !sidebarOpen.value }
function openSettings(){}
async function logout(){ try { await fetch('/logout', {method:'POST'}) } catch {} location.href='/login' }

// data
const loadingDishes = ref(false)
const dishes = ref<any[]>([])
const currentDish = ref<any|null>(null)
const variants = ref<any[]>([])
const ingredients = ref<any[]>([])
const components = reactive<Record<number, any[]>>({})
const newComp = reactive({ ingredient_product_id: '', qty_per_unit: 0, unit_name: 'unit' })

async function loadDishes(){
  loadingDishes.value = true
  const r = await fetch('/api/inventory/dishes'); dishes.value = await r.json().catch(()=>[])
  loadingDishes.value = false
}
async function loadIngredients(){
  const r = await fetch('/api/inventory/ingredients'); ingredients.value = await r.json().catch(()=>[])
}
async function loadVariants(dish:any){
  const r = await fetch(`/api/inventory/dishes/${dish.id}/variants`)
  variants.value = await r.json().catch(()=>[])
}
async function loadComponents(variantId:number){
  const rr = await fetch(`/api/inventory/variants/${variantId}/components`)
  components[variantId] = await rr.json().catch(()=>[])
}

// CRUD dish
function openCreateDish(){
  const name = prompt('Dish name?'); if(!name) return
  const price = Number(prompt('Price (JMD dollars)?') || '0')
  const price_cents = Math.round(price*100)
  fetch('/api/inventory/dishes', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({name, price_cents})})
    .then(()=> loadDishes())
}
function selectDish(d:any){ currentDish.value = d; loadVariants(d) }
function renameDish(d:any){
  const name = prompt('New name', d.name) || d.name
  const price = Number(prompt('Price (JMD dollars)?', (d.price_cents/100).toFixed(2)) || (d.price_cents/100))
  fetch(`/api/inventory/dishes/${d.id}`, {method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify({name, price_cents: Math.round(price*100)})})
    .then(()=> loadDishes())
}
function removeDish(d:any){
  if(!confirm(`Delete ${d.name}?`)) return
  fetch(`/api/inventory/dishes/${d.id}`, {method:'DELETE'}).then(()=>{ if(currentDish.value?.id===d.id) currentDish.value=null; loadDishes() })
}

// Variants
function addVariant(){
  if(!currentDish.value) return
  const name = prompt('Variant name (e.g. Large, Spicy, No Rice)?') || 'Variant'
  fetch(`/api/inventory/dishes/${currentDish.value.id}/variants`, {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({name, is_default:false})})
    .then(()=> loadVariants(currentDish.value))
}
function setDefaultVariant(v:any){
  if(!currentDish.value) return
  fetch(`/api/inventory/dishes/${currentDish.value.id}/variants/${v.id}`, {method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify({name:v.name, is_default:true})})
    .then(()=> loadVariants(currentDish.value))
}
function deleteVariant(v:any){
  if(!currentDish.value) return
  if(!confirm(`Delete variant "${v.name}"?`)) return
  fetch(`/api/inventory/dishes/${currentDish.value.id}/variants/${v.id}`, {method:'DELETE'})
    .then(()=> loadVariants(currentDish.value))
}

// Components
function ensureComponentsLoaded(v:any){
  if (!components[v.id]) loadComponents(v.id)
  else delete components[v.id] // click again to hide
}
function addComponent(v:any){
  if (!newComp.ingredient_product_id || !newComp.qty_per_unit) return
  fetch(`/api/inventory/variants/${v.id}/components`, {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify(newComp)
  }).then(async ()=>{
    await loadComponents(v.id)
    newComp.ingredient_product_id = ''; newComp.qty_per_unit = 0; newComp.unit_name = 'unit'
  })
}
function removeComponent(v:any, row:any){
  fetch(`/api/inventory/variants/${v.id}/components/${row.id}`, {method:'DELETE'})
    .then(()=> loadComponents(v.id))
}

onMounted(()=>{ loadDishes(); loadIngredients() })
</script>
