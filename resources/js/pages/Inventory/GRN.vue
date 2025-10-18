<template>
  <div class="min-h-screen gradient-bg">
    <!-- Header -->
    <header class="glass-effect shadow-lg">
      <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-clipboard-check text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-800">Receive Stock (GRN)</h1>
            <p class="text-sm text-gray-600">Record goods received into inventory</p>
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <!-- Sidebar toggle -->
          <button
            @click="toggleSidebar"
            class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors"
            title="Toggle menu"
          >
            <i class="fas" :class="sidebarOpen ? 'fa-angles-left' : 'fa-angles-right'"></i>
          </button>

          <div class="text-right mr-2">
            <p class="text-sm text-gray-600">{{ currentTime }}</p>
          </div>

          <!-- Back to POS -->
          <a :href="posHref"
             class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors"
             title="POS">
            <i class="fas fa-cash-register text-lg"></i>
          </a>
        </div>
      </div>
    </header>

    <div class="flex h-[calc(100vh-80px)]">
      <!-- LEFT NAV SIDEBAR (same look as POS) -->
      <nav
        class="glass-effect m-4 rounded-2xl shadow-2xl flex flex-col transition-all duration-300 overflow-hidden"
        :class="sidebarOpen ? 'w-64' : 'w-20'"
        aria-label="Main navigation"
      >
        <div class="flex items-center justify-between px-3 py-3">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
              <i class="fas fa-fire text-white text-base"></i>
            </div>
            <span v-if="sidebarOpen" class="font-semibold text-gray-800">Menu</span>
          </div>
          <button
            @click="toggleSidebar"
            class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors"
            :title="sidebarOpen ? 'Collapse' : 'Expand'"
          >
            <i class="fas" :class="sidebarOpen ? 'fa-angles-left' : 'fa-angles-right'"></i>
          </button>
        </div>

        <div class="px-2">
          <ul class="mt-1 space-y-1">
            <li>
              <a :href="posHref"
                 class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors"
                 :class="isActive('/pos') ? 'bg-orange-600 text-white' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-700'">
                <i :class="['fas fa-cash-register text-lg', isActive('/pos') ? 'text-white' : 'text-gray-600']"></i>
                <span v-if="sidebarOpen" class="font-medium">POS</span>
              </a>
            </li>

            <li>
              <a :href="inventoryHref"
                 class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors"
                 :class="isActive('/inventory') ? 'bg-orange-600 text-white' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-700'">
                <i :class="['fas fa-boxes-stacked text-lg', isActive('/inventory') ? 'text-white' : 'text-gray-600']"></i>
                <span v-if="sidebarOpen" class="font-medium">Inventory</span>
              </a>
            </li>

            <li>
              <a :href="grnHref"
                 class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors"
                 :class="isActive('/inventory/grn') ? 'bg-orange-600 text-white' : 'text-gray-700 hover:bg-orange-50 hover:text-orange-700'">
                <i :class="['fas fa-clipboard-check text-lg', isActive('/inventory/grn') ? 'text-white' : 'text-gray-600']"></i>
                <span v-if="sidebarOpen" class="font-medium">Receive Stock</span>
              </a>
            </li>
          </ul>
        </div>

        <div class="mt-auto px-3 py-3 text-xs text-gray-500">
          <div v-if="sidebarOpen">v0.1 • Grillstone</div>
          <div v-else class="text-center">v0.1</div>
        </div>
      </nav>

      <!-- MAIN -->
      <section class="flex-1 p-4 flex flex-col">
        <!-- Errors -->
        <div v-if="err" class="glass-effect rounded-2xl p-4 mb-4 shadow-lg border border-red-200">
          <p class="text-red-700 font-medium">{{ err }}</p>
        </div>

        <!-- Header card -->
        <div class="glass-effect rounded-2xl p-4 mb-4 shadow-lg">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Supplier</label>
              <input v-model="form.supplier_name" type="text" placeholder="e.g. Fresh Farms Ltd."
                     class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">PO # (optional)</label>
              <input v-model="form.po_number" type="text" placeholder="PO-00123"
                     class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Received At</label>
              <input v-model="form.received_at" type="datetime-local"
                     class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Location</label>
              <select v-model.number="form.location_id"
                      class="mt-1 w-full rounded-md border border-gray-300 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                <option :value="1">Main</option>
                <!-- add more if you support multi-location -->
              </select>
            </div>
          </div>
        </div>

        <!-- Lines card -->
        <div class="glass-effect rounded-2xl p-4 mb-4 shadow-lg">
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold text-gray-800">Lines</h2>
            <button @click="addLine"
                    class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
              <i class="fas fa-plus mr-1"></i> Add line
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left text-gray-600">
                  <th class="py-2 pr-3">Ingredient</th>
                  <th class="py-2 pr-3">Qty</th>
                  <th class="py-2 pr-3">Unit</th>
                  <th class="py-2 pr-3">Unit Cost</th>
                  <th class="py-2 pr-3">Line Total</th>
                  <th class="py-2">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(ln, i) in form.lines" :key="ln.uid" class="border-t">
                  <td class="py-2 pr-3">
                    <select v-model.number="ln.ingredient_product_id"
                            class="w-64 rounded-md border border-gray-300 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                      <option :value="0" disabled>Select ingredient…</option>
                      <option v-for="ing in ingredients" :key="ing.id" :value="ing.id">{{ ing.name }}</option>
                    </select>
                  </td>
                  <td class="py-2 pr-3">
                    <input v-model.number="ln.qty" type="number" min="0" step="0.0001"
                           class="w-28 rounded-md border border-gray-300 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                  </td>
                  <td class="py-2 pr-3">
                    <select v-model="ln.unit_name"
                            class="w-36 rounded-md border border-gray-300 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                      <option disabled value="">Select unit…</option>
                      <optgroup label="Weight">
                        <option>g</option><option>kg</option><option>lb</option><option>oz</option>
                      </optgroup>
                      <optgroup label="Volume">
                        <option>ml</option><option>L</option><option>fl oz</option><option>gal</option>
                      </optgroup>
                      <optgroup label="Each / packs">
                        <option>ea</option><option>pack</option><option>box</option>
                      </optgroup>
                    </select>
                  </td>
                  <td class="py-2 pr-3">
                    <div class="flex items-center gap-1">
                      <span class="text-gray-500">JMD</span>
                      <input v-model.number="ln.unit_cost"
                             type="number" min="0" step="0.01"
                             class="w-32 rounded-md border border-gray-300 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                  </td>
                  <td class="py-2 pr-3 font-medium text-gray-800">JMD {{ nf(lineTotal(ln)) }}</td>
                  <td class="py-2">
                    <button @click="removeLine(i)" class="text-red-600 hover:text-red-800">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>

                <tr v-if="!form.lines.length">
                  <td colspan="6" class="py-6 text-center text-gray-500">No lines yet. Click <b>Add line</b> to begin.</td>
                </tr>
              </tbody>
              <tfoot v-if="form.lines.length" class="border-t">
                <tr>
                  <td colspan="4" class="py-3 text-right font-medium text-gray-700">Total:</td>
                  <td class="py-3 font-bold text-gray-900">JMD {{ nf(grandTotal) }}</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div class="mt-4 flex justify-end gap-3">
            <button @click="resetForm" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Clear</button>
            <button @click="submitGRN" :disabled="submitting || !canSubmit"
                    class="px-5 py-2 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
              <span v-if="!submitting"><i class="fas fa-save mr-1"></i> Save Receipt</span>
              <span v-else>Saving…</span>
            </button>
          </div>
        </div>

        <!-- Toast -->
        <div class="notification bg-white rounded-lg shadow-lg p-4 w-80" :class="{ show: toastShow }">
          <div class="flex items-center">
            <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3" :class="toastBg">
              <i :class="toastIcon" class="text-white"></i>
            </div>
            <div class="flex-1">
              <p class="font-medium text-gray-800">{{ toastTitle }}</p>
              <p class="text-sm text-gray-600">{{ toastMsg }}</p>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'

/* ---------- helpers used in template (so page never goes blank) ---------- */
function nf(n: number | string) {
  const x = Number(n || 0)
  return x.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
function cents(v: number) { return Math.round((v || 0) * 100) }

/* ---------- sidebar / nav (same feel as POS) ---------- */
const sidebarOpen = ref(true)
function toggleSidebar(){ sidebarOpen.value = !sidebarOpen.value }
onMounted(() => {
  const saved = localStorage.getItem('sidebarOpen')
  if (saved !== null) sidebarOpen.value = saved === '1'
})
watch(sidebarOpen, v => localStorage.setItem('sidebarOpen', v ? '1' : '0'))

function isActive(path: string) { return window.location.pathname.startsWith(path) }
function routeUrl(name: string, fallback: string) {
  try { /* @ts-ignore */ if (typeof route === 'function') return route(name) } catch {}
  return fallback
}
const posHref       = routeUrl('pos.index', '/pos')
const inventoryHref = '/inventory'
const grnHref       = '/inventory/grn'

/* ---------- header clock ---------- */
const currentTime = ref(''); function tick(){ currentTime.value = new Date().toLocaleTimeString() }
onMounted(()=>{ tick(); setInterval(tick,1000) })

/* ---------- data ---------- */
type Ingredient = { id:number; name:string }
const ingredients = ref<Ingredient[]>([])
const loading = ref(false)
const err = ref<string | null>(null)

type Line = {
  uid: string
  ingredient_product_id: number
  qty: number
  unit_name: string
  unit_cost: number
}
const form = ref({
  supplier_name: '',
  po_number: '',
  received_at: new Date().toISOString().slice(0,16), // yyyy-MM-ddTHH:mm
  location_id: 1,
  lines: [] as Line[],
})
const submitting = ref(false)

/* ---------- computed ---------- */
function lineTotal(ln: Line){ return (ln.qty || 0) * (ln.unit_cost || 0) }
const grandTotal = computed(()=> form.value.lines.reduce((s,ln)=> s + lineTotal(ln), 0))
const canSubmit = computed(()=> {
  if (!form.value.supplier_name) return false
  if (!form.value.lines.length) return false
  return form.value.lines.every(ln => ln.ingredient_product_id > 0 && ln.qty > 0 && !!ln.unit_name && ln.unit_cost >= 0)
})

/* ---------- line ops ---------- */
function uid(){ return Math.random().toString(36).slice(2) }
function addLine(){
  form.value.lines.push({
    uid: uid(),
    ingredient_product_id: 0,
    qty: 0,
    unit_name: '',
    unit_cost: 0,
  })
}
function removeLine(i: number){ form.value.lines.splice(i,1) }
function resetForm(){
  form.value.supplier_name = ''
  form.value.po_number = ''
  form.value.received_at = new Date().toISOString().slice(0,16)
  form.value.location_id = 1
  form.value.lines = []
}

/* ---------- api ---------- */
async function loadIngredients(){
  loading.value = true; err.value = null
  try {
    const resp = await fetch('/api/inventory/ingredients', { headers: { 'Accept':'application/json' }})
    if (!resp.ok) throw new Error(`Failed to load ingredients (${resp.status})`)
    const data = await resp.json()
    ingredients.value = data as Ingredient[]
  } catch (e:any) {
    console.error(e); err.value = e?.message || 'Error loading ingredients.'
  } finally { loading.value = false }
}

async function submitGRN(){
  if (!canSubmit.value) return
  submitting.value = true; err.value = null
  try {
    const payload = {
  supplier_name: form.value.supplier_name,
  po_number: form.value.po_number || null,
  received_at: form.value.received_at ? new Date(form.value.received_at).toISOString() : null,
  location_id: form.value.location_id,
  lines: form.value.lines.map(ln => ({
    product_id: ln.ingredient_product_id,   // 👈 rename for the API
    qty: ln.qty,
    unit_name: ln.unit_name,
    unit_cost_cents: cents(ln.unit_cost),
  })),
}


    const resp = await fetch('/api/grn', {
      method: 'POST',
      headers: {
        'Content-Type':'application/json',
        'X-Requested-With':'XMLHttpRequest',
        'Accept':'application/json',
      },
      body: JSON.stringify(payload),
    })

    if (!resp.ok) {
      const msg = await resp.text().catch(()=> '')
      throw new Error(msg || `Failed to save (HTTP ${resp.status})`)
    }

    const data = await resp.json().catch(()=> ({}))
    toast('Saved', `GRN ${data.grn_no ?? ''} recorded successfully`,'success')
    resetForm()
  } catch (e:any) {
    console.error(e)
    err.value = e?.message || 'Error saving receipt.'
    toast('Error', 'Could not save receipt','error')
  } finally { submitting.value = false }
}

/* ---------- toast ---------- */
const toastShow = ref(false); const toastTitle=ref(''); const toastMsg=ref(''); const toastIcon=ref('fas fa-check'); const toastBg=ref('bg-green-500'); let timer:number|undefined
function toast(title:string,msg:string,type:'success'|'error'|'warning'='success'){
  toastTitle.value=title; toastMsg.value=msg;
  toastIcon.value= type==='success'?'fas fa-check': type==='error'?'fas fa-times':'fas fa-exclamation';
  toastBg.value= type==='success'?'bg-green-500': type==='error'?'bg-red-500':'bg-yellow-500';
  toastShow.value=true; clearTimeout(timer as any); timer = window.setTimeout(()=>toastShow.value=false,3000)
}

/* ---------- init ---------- */
onMounted(() => {
  // make sure the page always renders even if load fails
  addLine()
  loadIngredients()
})
</script>

<style scoped>
.gradient-bg {
  background: linear-gradient(135deg, #f97316 0%, #ea580c 25%, #dc2626 50%, #92400e 75%, #451a03 100%);
}
.glass-effect {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}
.notification { position: fixed; top: 20px; right: 20px; z-index: 1000; transform: translateX(100%); transition: transform .3s ease; }
.notification.show { transform: translateX(0); }
</style>
