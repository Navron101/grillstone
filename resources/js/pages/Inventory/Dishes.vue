<template>
  <div class="min-h-screen gradient-bg">
    <header class="glass-effect shadow-lg">
      <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-utensils text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-800">Dishes & Recipes</h1>
            <p class="text-sm text-gray-600">Define dishes, variants, and ingredients</p>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <button @click="sidebarOpen = !sidebarOpen" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
            <i class="fas" :class="sidebarOpen ? 'fa-angles-left' : 'fa-angles-right'"></i>
          </button>
          <div class="text-right mr-2"><p class="text-sm text-gray-600">{{ currentTime }}</p></div>
          <a href="/pos" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg"><i class="fas fa-cash-register text-lg"></i></a>
        </div>
      </div>
    </header>

    <div class="flex h-[calc(100vh-80px)]">
      <nav class="glass-effect m-4 rounded-2xl shadow-2xl flex flex-col transition-all duration-300 overflow-hidden" :class="sidebarOpen ? 'w-64' : 'w-20'">
        <div class="flex items-center justify-between px-3 py-3">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center"><i class="fas fa-fire text-white text-base"></i></div>
            <span v-if="sidebarOpen" class="font-semibold text-gray-800">Menu</span>
          </div>
        </div>
        <div class="px-2">
          <ul class="mt-1 space-y-1">
            <li><a href="/pos" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50"><i class="fas fa-cash-register text-lg text-gray-600"></i><span v-if="sidebarOpen" class="font-medium">POS</span></a></li>
            <li><a href="/inventory" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50"><i class="fas fa-boxes-stacked text-lg text-gray-600"></i><span v-if="sidebarOpen" class="font-medium">Inventory</span></a></li>
            <li><a href="/inventory/dishes" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors bg-orange-600 text-white"><i class="fas fa-utensils text-lg text-white"></i><span v-if="sidebarOpen" class="font-medium">Dishes</span></a></li>
            <li><a href="/inventory/grn" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50"><i class="fas fa-clipboard-check text-lg text-gray-600"></i><span v-if="sidebarOpen" class="font-medium">Receive Stock</span></a></li>
            <li><a href="/inventory/stocktake" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50"><i class="fas fa-clipboard-list text-lg text-gray-600"></i><span v-if="sidebarOpen" class="font-medium">Stocktake</span></a></li>
            <li><a href="/inventory/waste" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50"><i class="fas fa-trash-alt text-lg text-gray-600"></i><span v-if="sidebarOpen" class="font-medium">Waste</span></a></li>
            <li><a href="/reports/waste" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50"><i class="fas fa-chart-line text-lg text-gray-600"></i><span v-if="sidebarOpen" class="font-medium">Waste Reports</span></a></li>
          </ul>
        </div>
      </nav>

      <section class="flex-1 p-4 flex flex-col overflow-y-auto">
        <div class="glass-effect rounded-2xl p-4 mb-4 shadow-lg flex items-center gap-3">
          <button @click="showDishModal = true" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg"><i class="fas fa-plus mr-2"></i> New Dish</button>
          <span class="text-sm text-gray-600">Click a dish to manage variants & ingredients.</span>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
          <div class="glass-effect rounded-2xl p-4 shadow-lg">
            <h3 class="font-semibold text-gray-800 mb-3">Dishes</h3>
            <div v-if="loading" class="text-gray-500">Loading…</div>
            <div v-else>
              <div v-for="d in dishes" :key="d.id" class="bg-white rounded-xl p-3 mb-2 shadow-sm hover:shadow-md cursor-pointer" @click="selectDish(d)">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="font-medium text-gray-800">{{ d.name }}</div>
                    <div class="text-xs text-gray-500">JMD {{ (d.price_cents/100).toFixed(2) }}</div>
                  </div>
                  <div class="flex gap-2">
                    <button @click.stop="editDish(d)" class="text-gray-600 hover:text-gray-900 px-2"><i class="fas fa-pen"></i></button>
                    <button @click.stop="confirmDelete('dish', d)" class="text-red-600 hover:text-red-800 px-2"><i class="fas fa-trash"></i></button>
                  </div>
                </div>
              </div>
              <div v-if="!dishes.length" class="text-gray-500">No dishes yet</div>
            </div>
          </div>

          <div class="glass-effect rounded-2xl p-4 shadow-lg">
            <div class="flex items-center justify-between mb-3">
              <h3 class="font-semibold text-gray-800">Variants</h3>
              <button v-if="currentDish" @click="showVariantModal = true" class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-1 rounded-lg text-sm"><i class="fas fa-plus mr-1"></i> New Variant</button>
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
                    <button @click="toggleComponents(v)" class="px-2 text-gray-700 hover:text-gray-900 text-sm"><i class="fas fa-list-check mr-1"></i> {{ components[v.id] ? 'Hide' : 'Show' }} Ingredients</button>
                    <button v-if="!v.is_default" @click="setDefaultVariant(v)" class="px-2 text-amber-700 hover:text-amber-900 text-sm"><i class="fas fa-star mr-1"></i></button>
                    <button @click="confirmDelete('variant', v)" class="px-2 text-red-600 hover:text-red-800 text-sm"><i class="fas fa-trash mr-1"></i></button>
                  </div>
                </div>

                <div v-if="components[v.id]" class="mt-3">
                  <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-600"><th class="py-1">Ingredient</th><th class="py-1">Qty</th><th class="py-1">Unit</th><th class="py-1 w-10"></th></tr></thead>
                    <tbody>
                      <tr v-for="row in components[v.id]" :key="row.id" class="border-t">
                        <td class="py-1">{{ row.ingredient_name }}</td>
                        <td class="py-1">{{ row.qty_per_unit }}</td>
                        <td class="py-1">{{ row.unit_name || row.default_unit || 'unit' }}</td>
                        <td class="py-1 text-right"><button @click="deleteComponent(v, row)" class="text-red-600 hover:text-red-800"><i class="fas fa-xmark"></i></button></td>
                      </tr>
                    </tbody>
                  </table>
                  <div class="mt-3 flex gap-2 items-end">
                    <select v-model="newComp.ingredient_product_id" class="border rounded px-2 py-1 flex-1">
                      <option value="">Select ingredient…</option>
                      <option v-for="i in ingredients" :key="i.id" :value="i.id">{{ i.name }}</option>
                    </select>
                    <input v-model.number="newComp.qty_per_unit" type="number" min="0" step="0.0001" class="border rounded px-2 py-1 w-28" placeholder="Qty">
                    <select v-model="newComp.unit_name" class="border rounded px-2 py-1 w-28">
                      <option v-for="u in ['unit','g','kg','lb','oz','ml','l']" :key="u" :value="u">{{ u }}</option>
                    </select>
                    <button @click="addComponent(v)" class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-1 rounded">Add</button>
                  </div>
                </div>
              </div>
              <div v-if="currentDish && !variants.length" class="text-gray-500">No variants yet</div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Dish Modal -->
    <div v-if="showDishModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeDishModal">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">{{ editingDish ? 'Edit' : 'Create' }} Dish</h3>
        <form @submit.prevent="saveDish" class="space-y-4">
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Dish Name *</label><input v-model="dishForm.name" type="text" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="e.g., Jerk Chicken"></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Price (JMD) *</label><input v-model.number="dishForm.price" type="number" step="0.01" min="0" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="0.00"></div>
          <div class="flex justify-end gap-2 pt-4">
            <button type="button" @click="closeDishModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">{{ editingDish ? 'Update' : 'Create' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Variant Modal -->
    <div v-if="showVariantModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showVariantModal = false">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Create Variant</h3>
        <form @submit.prevent="saveVariant" class="space-y-4">
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Variant Name *</label><input v-model="variantForm.name" type="text" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="e.g., Large, Spicy"></div>
          <div class="flex justify-end gap-2 pt-4">
            <button type="button" @click="showVariantModal = false" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">Create</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <div v-if="showConfirm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showConfirm = false">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Confirm Delete</h3>
        <p class="text-gray-700 mb-6">{{ confirmMessage }}</p>
        <div class="flex justify-end gap-2">
          <button @click="showConfirm = false" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
          <button @click="executeDelete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <div class="notification bg-white rounded-lg shadow-lg p-4 w-80" :class="{ show: toastShow }">
      <div class="flex items-center">
        <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3" :class="toastBg"><i :class="toastIcon" class="text-white"></i></div>
        <div class="flex-1"><p class="font-medium text-gray-800">{{ toastTitle }}</p><p class="text-sm text-gray-600">{{ toastMsg }}</p></div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'

const sidebarOpen = ref(true)
const currentTime = ref('')
function tick(){ currentTime.value = new Date().toLocaleTimeString() }
onMounted(()=>{ tick(); setInterval(tick,1000) })

const loading = ref(false)
const dishes = ref<any[]>([])
const currentDish = ref<any|null>(null)
const variants = ref<any[]>([])
const ingredients = ref<any[]>([])
const components = reactive<Record<number, any[]>>({})

const showDishModal = ref(false)
const showVariantModal = ref(false)
const showConfirm = ref(false)
const editingDish = ref<any|null>(null)
const dishForm = ref({ name: '', price: 0 })
const variantForm = ref({ name: '' })
const newComp = reactive({ ingredient_product_id: '', qty_per_unit: 0, unit_name: 'unit' })

const confirmMessage = ref('')
const confirmAction = ref<any>(null)

async function loadDishes(){
  loading.value = true
  const r = await fetch('/api/inventory/dishes'); dishes.value = await r.json().catch(()=>[])
  loading.value = false
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

function selectDish(d:any){ currentDish.value = d; loadVariants(d) }

function editDish(d:any){
  editingDish.value = d
  dishForm.value = { name: d.name, price: d.price_cents/100 }
  showDishModal.value = true
}

async function saveDish(){
  const payload = { name: dishForm.value.name, price_cents: Math.round(dishForm.value.price*100) }
  if(editingDish.value){
    await fetch(`/api/inventory/dishes/${editingDish.value.id}`, {method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)})
    toast('Success', 'Dish updated', 'success')
  } else {
    await fetch('/api/inventory/dishes', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)})
    toast('Success', 'Dish created', 'success')
  }
  closeDishModal()
  await loadDishes()
}

function closeDishModal(){
  showDishModal.value = false
  editingDish.value = null
  dishForm.value = { name: '', price: 0 }
}

async function saveVariant(){
  if(!currentDish.value) return
  await fetch(`/api/inventory/dishes/${currentDish.value.id}/variants`, {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({name: variantForm.value.name, is_default:false})})
  toast('Success', 'Variant created', 'success')
  showVariantModal.value = false
  variantForm.value = { name: '' }
  await loadVariants(currentDish.value)
}

async function setDefaultVariant(v:any){
  if(!currentDish.value) return
  await fetch(`/api/inventory/dishes/${currentDish.value.id}/variants/${v.id}`, {method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify({name:v.name, is_default:true})})
  toast('Success', 'Default variant updated', 'success')
  await loadVariants(currentDish.value)
}

function toggleComponents(v:any){
  if (!components[v.id]) loadComponents(v.id)
  else delete components[v.id]
}

async function addComponent(v:any){
  if (!newComp.ingredient_product_id || !newComp.qty_per_unit) return
  await fetch(`/api/inventory/variants/${v.id}/components`, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(newComp) })
  await loadComponents(v.id)
  newComp.ingredient_product_id = ''; newComp.qty_per_unit = 0; newComp.unit_name = 'unit'
  toast('Success', 'Ingredient added', 'success')
}

async function deleteComponent(v:any, row:any){
  await fetch(`/api/inventory/variants/${v.id}/components/${row.id}`, {method:'DELETE'})
  await loadComponents(v.id)
  toast('Success', 'Ingredient removed', 'success')
}

function confirmDelete(type: 'dish'|'variant', item: any){
  if(type === 'dish'){
    confirmMessage.value = `Delete "${item.name}"? This cannot be undone.`
    confirmAction.value = async () => {
      await fetch(`/api/inventory/dishes/${item.id}`, {method:'DELETE'})
      if(currentDish.value?.id===item.id) currentDish.value=null
      await loadDishes()
      toast('Success', 'Dish deleted', 'success')
    }
  } else {
    confirmMessage.value = `Delete variant "${item.name}"? This cannot be undone.`
    confirmAction.value = async () => {
      await fetch(`/api/inventory/dishes/${currentDish.value.id}/variants/${item.id}`, {method:'DELETE'})
      await loadVariants(currentDish.value)
      toast('Success', 'Variant deleted', 'success')
    }
  }
  showConfirm.value = true
}

async function executeDelete(){
  if(confirmAction.value) await confirmAction.value()
  showConfirm.value = false
  confirmAction.value = null
}

const toastShow = ref(false), toastTitle=ref(''), toastMsg=ref(''), toastIcon=ref('fas fa-check'), toastBg=ref('bg-green-500')
function toast(title:string,msg:string,type:'success'|'error'='success'){
  toastTitle.value=title; toastMsg.value=msg
  toastIcon.value= type==='success'?'fas fa-check':'fas fa-times'
  toastBg.value= type==='success'?'bg-green-500':'bg-red-500'
  toastShow.value=true; setTimeout(()=>toastShow.value=false,3000)
}

onMounted(()=>{ loadDishes(); loadIngredients() })
</script>

<style scoped>
.gradient-bg { background: linear-gradient(135deg, #f97316 0%, #ea580c 25%, #dc2626 50%, #92400e 75%, #451a03 100%); }
.glass-effect { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.2); }
.notification { position: fixed; bottom: 20px; right: 20px; opacity: 0; transform: translateY(20px); transition: all 0.3s ease; pointer-events: none; z-index: 9999; }
.notification.show { opacity: 1; transform: translateY(0); pointer-events: all; }
</style>
