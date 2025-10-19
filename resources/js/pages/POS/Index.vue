<template>
  <div class="min-h-screen gradient-bg">
    <!-- Header -->
    <header class="glass-effect shadow-lg">
      <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-utensils text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-800">Grillstone POS</h1>
            <p class="text-sm text-gray-600">
              Table #<span>{{ tableNumber }}</span>
            </p>
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <button @click="toggleSidebar" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg" title="Toggle menu">
            <i class="fas" :class="sidebarOpen ? 'fa-angles-left' : 'fa-angles-right'"></i>
          </button>

          <div class="text-right mr-2">
            <p class="text-sm text-gray-600">{{ currentTime }}</p>
            <p class="text-sm font-medium text-gray-800">Cashier: {{ cashier }}</p>
          </div>

          <button @click="openSettings" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg" title="Settings">
            <i class="fas fa-cog text-lg"></i>
          </button>

          <button @click="logout" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg" title="Log out">
            <i class="fas fa-right-from-bracket text-lg"></i>
          </button>
        </div>
      </div>
    </header>

    <div class="flex h-[calc(100vh-80px)]">
      <!-- Left nav -->
      <nav
        class="glass-effect m-4 rounded-2xl shadow-2xl flex flex-col transition-all duration-300 overflow-hidden"
        :class="sidebarOpen ? 'w-64' : 'w-20'"
      >
        <div class="flex items-center justify-between px-3 py-3">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
              <i class="fas fa-fire text-white text-base"></i>
            </div>
            <span v-if="sidebarOpen" class="font-semibold text-gray-800">Menu</span>
          </div>
          <button @click="toggleSidebar" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
            <i class="fas" :class="sidebarOpen ? 'fa-angles-left' : 'fa-angles-right'"></i>
          </button>
        </div>

        <div class="px-2">
          <ul class="mt-1 space-y-1">
            <li>
              <a :href="posHref"
                 class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors"
                 :class="isActive(posHref)
                  ? 'bg-orange-600 text-white'
                  : 'text-gray-700 hover:bg-orange-50 hover:text-orange-700'">
                <i :class="['fas fa-cash-register text-lg', isActive(posHref) ? 'text-white' : 'text-gray-600']"></i>
                <span v-if="sidebarOpen" class="font-medium">POS</span>
              </a>
            </li>

            <li>
              <a :href="inventoryHref"
                 class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors"
                 :class="isActive(inventoryHref)
                  ? 'bg-orange-600 text-white'
                  : 'text-gray-700 hover:bg-orange-50 hover:text-orange-700'">
                <i :class="['fas fa-boxes-stacked text-lg', isActive(inventoryHref) ? 'text-white' : 'text-gray-600']"></i>
                <span v-if="sidebarOpen" class="font-medium">Inventory</span>
              </a>
            </li>

            <li>
              <button @click="comingSoon('HR / Payroll')"
                      class="w-full flex items-center gap-3 rounded-xl px-3 py-2 text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-users text-lg text-gray-600"></i>
                <span v-if="sidebarOpen" class="font-medium">HR / Payroll</span>
              </button>
            </li>

            <li>
              <button @click="comingSoon('Menu Updates')"
                      class="w-full flex items-center gap-3 rounded-xl px-3 py-2 text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-utensils text-lg text-gray-600"></i>
                <span v-if="sidebarOpen" class="font-medium">Menu Updates</span>
              </button>
            </li>
          </ul>
        </div>

        <div class="mt-auto px-3 py-3 text-xs text-gray-500">
          <div v-if="sidebarOpen">v0.1 • Grillstone</div>
          <div v-else class="text-center">v0.1</div>
        </div>
      </nav>

      <!-- Main area -->
      <div class="flex-1 flex">
        <!-- Cart -->
        <aside class="w-96 glass-effect m-4 rounded-2xl shadow-2xl flex flex-col">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold text-gray-800">Current Order</h2>
              <div class="flex items-center space-x-2">
                <button @click="clearCart" class="p-2 text-red-500 hover:bg-red-50 rounded-lg" title="Clear Cart">
                  <i class="fas fa-trash text-sm"></i>
                </button>
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                  {{ totalItems }} {{ totalItems===1?'item':'items' }}
                </span>
              </div>
            </div>
          </div>

          <div class="flex-grow overflow-y-auto p-4 space-y-3">
            <div v-if="!cart.length" class="text-center py-8 text-gray-400">
              <i class="fas fa-shopping-cart text-4xl mb-4"></i>
              <p>Cart is empty</p><p class="text-sm">Add items to get started</p>
            </div>
            <div v-for="(item,i) in cart" :key="i" class="cart-item bg-gray-50 rounded-lg p-3 flex items-center justify-between">
              <div class="flex-1">
                <h4 class="font-medium text-gray-800">{{ item.name }}</h4>
                <p class="text-xs text-gray-500">
                  <span v-if="item.variant_name" class="mr-1">({{ item.variant_name }})</span>
                  JMD {{ nf(item.price) }} each
                </p>
                <div class="flex items-center mt-2">
                  <button @click="updateQuantity(i,-1)" class="w-6 h-6 bg-gray-200 rounded-full text-gray-600 hover:bg-gray-300">
                    <i class="fas fa-minus text-xs"></i>
                  </button>
                  <span class="mx-3 font-medium">{{ item.qty }}</span>
                  <button @click="updateQuantity(i,1)" class="w-6 h-6 bg-orange-100 rounded-full text-orange-600 hover:bg-orange-200">
                    <i class="fas fa-plus text-xs"></i>
                  </button>
                </div>
              </div>
              <div class="text-right">
                <p class="font-bold text-gray-800">JMD {{ nf(item.qty*item.price) }}</p>
                <button @click="removeFromCart(i)" class="text-red-500 hover:text-red-700 text-sm mt-1">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          </div>

          <div class="p-6 border-t border-gray-200 space-y-4">
            <div class="space-y-2">
              <div class="flex justify-between text-sm text-gray-600">
                <span>Subtotal</span><span>JMD {{ nf(subtotal) }}.00</span>
              </div>
              <div class="flex justify-between text-sm text-gray-600">
                <span>Tax ({{ (taxRate*100).toFixed(0) }}%)</span><span>JMD {{ nf(tax) }}.00</span>
              </div>
              <div class="flex justify-between text-sm text-gray-600">
                <span>Discount</span><span class="text-green-600">-JMD {{ nf(discountAmount) }}.00</span>
              </div>
              <div class="flex justify-between text-lg font-bold text-gray-800 pt-2 border-t">
                <span>Total</span><span>JMD {{ nf(total) }}.00</span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
              <button @click="applyDiscount" class="bg-orange-500 hover:bg-orange-600 text-white py-2 rounded-lg font-medium">
                <i class="fas fa-percentage mr-1"></i> Discount
              </button>
              <button @click="addNote" class="bg-amber-600 hover:bg-amber-700 text-white py-2 rounded-lg font-medium">
                <i class="fas fa-sticky-note mr-1"></i> Note
              </button>
            </div>

            <div class="grid grid-cols-2 gap-2">
              <button @click="holdOrder" class="bg-yellow-600 hover:bg-yellow-700 text-white py-2 rounded-lg font-medium">
                <i class="fas fa-pause mr-1"></i> Hold
              </button>
              <button @click="sendToKitchen" class="bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-medium">
                <i class="fas fa-utensils mr-1"></i> Kitchen
              </button>
            </div>

            <div>
              <h3 class="text-sm font-semibold text-gray-700 mb-2">Payment Method:</h3>
              <div class="grid grid-cols-3 gap-2">
                <button @click="openPayment('Cash')" class="bg-gray-800 hover:bg-gray-900 text-white py-2 rounded-lg font-medium">
                  <i class="fas fa-money-bills mr-1"></i><br>Cash
                </button>
                <button @click="openPayment('Card')" class="bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-medium">
                  <i class="fas fa-credit-card mr-1"></i><br>Card
                </button>
                <button @click="openPayment('Digital')" class="bg-purple-600 hover:bg-purple-700 text-white py-2 rounded-lg font-medium">
                  <i class="fas fa-mobile-alt mr-1"></i><br>Digital
                </button>
              </div>
            </div>
          </div>
        </aside>

        <!-- Products -->
        <section class="flex-1 p-4 flex flex-col">
          <div class="glass-effect rounded-2xl p-4 mb-4 shadow-lg">
            <div class="flex items-center space-x-4">
              <div class="flex-1 relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input v-model="query" type="text" placeholder="Search products..."
                       class="w-full pl-10 pr-4 py-2 border-0 bg-white rounded-lg shadow-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
              </div>
              <button @click="toggleView" class="p-2 bg-white rounded-lg shadow-sm hover:shadow-md" title="Toggle View">
                <i class="fas fa-th-large text-gray-600"></i>
              </button>
            </div>
            <div class="flex flex-wrap gap-2 mt-4">
              <button v-for="cat in categories" :key="cat" @click="currentCategory=cat"
                      class="px-4 py-2 rounded-lg font-medium transition-all"
                      :class="currentCategory===cat ? 'bg-orange-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-orange-50 hover:text-orange-600 shadow-sm'">
                {{ cat }}
              </button>
            </div>
          </div>

          <div class="flex-1 glass-effect rounded-2xl p-4 shadow-lg overflow-hidden">
            <div class="h-full overflow-y-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 gap-4">
              <div v-for="p in filteredProducts" :key="p.id"
                   class="product-card bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden"
                   :class="p.is_out_of_stock ? 'opacity-60 grayscale cursor-not-allowed' : 'cursor-pointer'"
                   @click="onPickDish(p)">
                <div class="relative">
                  <img :src="p.img || placeholder(p.name)" :alt="p.name" class="w-full h-32 object-cover" @error="e=>onImgFallback(e,p)">
                  <div v-if="p.popular" class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">Popular</div>
                  <div v-if="p.is_out_of_stock" class="absolute top-2 left-2 text-xs px-2 py-1 rounded-full bg-red-600 text-white font-semibold">
                    OUT OF STOCK
                  </div>
                  <div v-else-if="p.is_low_stock" class="absolute top-2 left-2 text-xs px-2 py-1 rounded-full bg-amber-600 text-white font-semibold animate-pulse">
                    LOW: {{ p.on_hand }}
                  </div>
                  <div v-else class="absolute top-2 left-2 text-xs px-2 py-1 rounded-full"
                       :class="stockBadgeClass(p)">
                    Stock: {{ p.on_hand ?? 0 }}
                  </div>
                </div>
                <div class="p-3">
                  <h3 class="font-semibold text-gray-800 mb-1 truncate">{{ p.name }}</h3>
                  <p class="text-xs text-gray-500 mb-2 line-clamp-2">{{ p.description }}</p>
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-orange-600">JMD {{ nf(p.price) }}</span>
                    <button class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-1 rounded-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="p.is_out_of_stock">
                      <i class="fas fa-plus"></i>
                    </button>
                  </div>
                </div>
              </div>

              <div v-if="!loading && !filteredProducts.length" class="col-span-full text-center py-12">
                <i class="fas fa-search text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500">No products found</p>
              </div>

              <div v-if="loading" class="col-span-full text-center py-12 text-gray-500">
                Loading…
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- Variant picker -->
    <div v-if="variantModal.show" class="fixed inset-0 modal bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-6 w-[440px] max-w-[95vw]">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">
          Choose an option for {{ variantModal.product?.name }}
        </h3>

        <div v-if="variantModal.loading" class="text-gray-500 py-6 text-center">Loading variants…</div>

        <div v-else class="space-y-2 max-h-72 overflow-y-auto">
          <button v-for="v in variantModal.variants" :key="v.id"
                  class="w-full text-left p-3 rounded-lg border hover:border-orange-400 hover:bg-orange-50"
                  @click="addVariantToCart(variantModal.product!, v)">
            <div class="flex items-center justify-between">
              <div>
                <div class="font-medium text-gray-800">{{ v.name }}</div>
                <div class="text-xs text-gray-500" v-if="v.is_default">Default</div>
              </div>
              <div class="font-semibold text-gray-900">JMD {{ nf(v.price) }}</div>
            </div>
          </button>

          <div v-if="!variantModal.variants.length" class="text-sm text-gray-500 text-center py-4">
            No variants found — using base price.
          </div>
        </div>

        <div class="mt-4 flex justify-end gap-2">
          <button class="px-3 py-2 rounded border" @click="closeVariantModal">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Payment Modal -->
    <div v-if="showPayment" class="fixed inset-0 modal bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-8 w-96 max-w-md mx-4">
        <div class="text-center mb-6">
          <div class="w-16 h-16 mx-auto mb-4 bg-orange-100 rounded-full flex items-center justify-center">
            <i class="fas fa-dollar-sign text-orange-600 text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-800">Complete {{ paymentMethod }} Payment</h3>
          <p class="text-gray-600 mt-2">Total Amount: <span class="font-semibold">JMD {{ nf(total) }}.00</span></p>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Amount Tendered</label>
            <input v-model.number="tendered" type="number" step="0.01"
                   class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
                   placeholder="Enter amount">
          </div>

          <div v-if="tendered !== null" class="rounded-lg p-4" :class="change>=0?'bg-green-50 border border-green-200':'bg-red-50 border border-red-200'">
            <div class="text-sm" :class="change>=0?'text-green-700':'text-red-700'">Change Due:</div>
            <div class="text-2xl font-bold" :class="change>=0?'text-green-800':'text-red-800'">
              {{ change>=0 ? 'JMD ' + nf(change) : 'Short ' + nf(Math.abs(change)) }}
            </div>
          </div>

          <div class="flex space-x-3">
            <button @click="closePayment" class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
              Cancel
            </button>
            <button @click="processPayment" class="flex-1 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
              Process Payment
            </button>
          </div>
        </div>
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
  </div>
</template>

<script setup lang="ts">
// Vue / Inertia
import { computed, onMounted, ref, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

// ---- Page props from server
type PageProps = { cashier: string; tableNumber: number; taxRate: number }
const page = usePage<PageProps>()
const cashier = page.props.cashier ?? 'Cashier'
const tableNumber = page.props.tableNumber ?? 1
const taxRate = page.props.taxRate ?? 0.15
const locationId = 1

// ---- Sidebar
const sidebarOpen = ref(true)
function toggleSidebar(){ sidebarOpen.value = !sidebarOpen.value }
onMounted(() => { const saved = localStorage.getItem('sidebarOpen'); if (saved!==null) sidebarOpen.value = saved === '1' })
watch(sidebarOpen, v => localStorage.setItem('sidebarOpen', v ? '1' : '0'))

// ---- Routes helpers (works with or without Ziggy)
function routeUrl(name: string, fallback: string) {
  try { // @ts-ignore
    if (typeof route === 'function') return route(name)
  } catch {}
  return fallback
}
const posHref = routeUrl('pos.index', '/pos')
const inventoryHref = '/inventory'
function isActive(href: string){ try{ const cur=window.location.pathname; const path=new URL(href,window.location.origin).pathname; return cur.startsWith(path) }catch{ return false } }

// ---- Logout
const logout = () => { try { // @ts-ignore
  const url = typeof route === 'function' ? route('logout') : '/logout'; router.post(url)
} catch { router.post('/logout') } }

// ---- Clock
const currentTime = ref(''); function tick(){ currentTime.value = new Date().toLocaleTimeString() }
onMounted(()=>{ tick(); setInterval(tick,1000) })

// ---- Products from API
type Product = {
  id:number; name:string; price:number; category:string; img?:string|null; description?:string|null; popular?:boolean;
  on_hand?: number; low_stock_threshold?: number; is_low_stock?: boolean; is_out_of_stock?: boolean;
}
const products = ref<Product[]>([])
const loading = ref(false)

async function loadProducts(){
  loading.value = true
  try{
    const resp = await fetch(`/api/products?location_id=${locationId}`, { headers:{ 'Accept':'application/json' } })
    if(!resp.ok){ throw new Error(await resp.text()) }
    const data = await resp.json()
    products.value = Array.isArray(data) ? data : []
  }catch(e){ console.error('loadProducts failed', e); toast('Error','Failed to load products','error') }
  finally{ loading.value = false }
}
onMounted(loadProducts)

// ---- Stock helpers
function stockOf(id:number){
  const p = products.value.find(x => x.id === id)
  return p?.on_hand ?? 0
}
function stockBadgeClass(product: Product){
  if (product.is_out_of_stock) return 'bg-red-600 text-white'
  if (product.is_low_stock) return 'bg-amber-600 text-white'
  return 'bg-green-600 text-white'
}

// ---- Filters
const query = ref('')
const currentCategory = ref('All')
const categories = computed(() => {
  const cats = new Set<string>(['All'])
  for (const p of products.value) cats.add(p.category || 'Other')
  return Array.from(cats)
})
const filteredProducts = computed(() => {
  const q = query.value.toLowerCase()
  return products.value.filter(p =>
    (currentCategory.value === 'All' || p.category === currentCategory.value) &&
    (!q || p.name.toLowerCase().includes(q))
  )
})

// ---- Variant modal
type Variant = { id:number; name:string; is_default?:boolean; price:number }
const variantModal = ref<{ show:boolean; product:Product|null; variants:Variant[]; loading:boolean }>({ show:false, product:null, variants:[], loading:false })

async function onPickDish(p: Product){
  // If stock is zero, block (strict mode)
  if (p.is_out_of_stock && (p.on_hand ?? 0) <= 0) {
    return toast('Out of stock', `${p.name} is not available`, 'warning')
  }

  // Try to fetch variants; if none or API missing, add base product immediately
  variantModal.value = { show:true, product:p, variants:[], loading:true }
  try{
    const resp = await fetch(`/api/inventory/dishes/${p.id}/variants`, { headers:{ 'Accept':'application/json' } })
    if (resp.ok) {
      const rows = (await resp.json()) as any[]
      const mapped: Variant[] = rows.map(r => ({ id:r.id, name:r.name, is_default: !!r.is_default, price: (r.price_cents ?? 0)/100 }))
      if (mapped.length) {
        variantModal.value.variants = mapped
        variantModal.value.loading = false
        return
      }
    }
  }catch{/* ignore */}
  // no variants -> add base product (use its own price)
  variantModal.value.show = false
  addToCart({ id:p.id, name:p.name, price:p.price, variant_id:null, variant_name:null })
}

function closeVariantModal(){ variantModal.value = { show:false, product:null, variants:[], loading:false } }

function addVariantToCart(prod: Product, v: Variant){
  closeVariantModal()
  addToCart({ id: prod.id, name: prod.name, price: v.price || prod.price, variant_id: v.id, variant_name: v.name })
}

// ---- Cart
type CartItem = { id:number; name:string; price:number; qty:number; variant_id:number|null; variant_name:string|null }
const cart = ref<CartItem[]>([])
const totalItems = computed(() => cart.value.reduce((s,i)=>s+i.qty,0))

function addToCart(base:{ id:number; name:string; price:number; variant_id:number|null; variant_name:string|null }){
  // stock control - allow negative but warn
  const inCart = cart.value.filter(i=>i.id===base.id).reduce((s,i)=>s+i.qty,0)
  const available = stockOf(base.id)

  if (inCart + 1 > available) {
    if (available <= 0) {
      toast('Negative Inventory', `Warning: ${base.name} has no stock available`, 'warning')
    } else {
      toast('Low Stock Warning', `Only ${available} units available, adding anyway`, 'warning')
    }
  }

  // merge lines by product+variant
  const ex = cart.value.find(i=>i.id===base.id && i.variant_id===base.variant_id)
  if (ex) ex.qty++
  else cart.value.push({ ...base, qty:1 })

  if (inCart + 1 <= available) {
    toast('Added to Cart', `${base.name}${base.variant_name ? ' - '+base.variant_name : ''} added`)
  }
}

function updateQuantity(i:number, d:number){
  const it=cart.value[i]
  const available = stockOf(it.id)

  if (d > 0 && it.qty + d > available) {
    if (available <= 0) {
      toast('Negative Inventory', `Warning: ${it.name} has no stock available`, 'warning')
    } else {
      toast('Low Stock Warning', `Only ${available} units available, adding anyway`, 'warning')
    }
  }

  it.qty+=d; if(it.qty<=0)cart.value.splice(i,1)
}
function removeFromCart(i:number){ const it=cart.value[i]; cart.value.splice(i,1); toast('Removed', `${it.name} removed`, 'warning') }
function clearCart(){ if(!cart.value.length) return; if(confirm('Clear entire cart?')){ cart.value=[]; currentDiscount.value=0 } }

// ---- Totals
const currentDiscount = ref(0)
const subtotal = computed(()=> cart.value.reduce((s,i)=>s+i.price*i.qty,0))
const discountAmount = computed(()=> Math.round(subtotal.value * (currentDiscount.value/100)))
const tax = computed(()=> Math.round((subtotal.value - discountAmount.value) * taxRate))
const total = computed(()=> subtotal.value + tax.value - discountAmount.value)

// ---- Actions
function applyDiscount(){ const v = prompt('Enter discount percentage (0-50):'); const n=Number(v); if(!Number.isFinite(n)||n<0||n>50){ return toast('Invalid Discount','Use a % between 0 and 50','error') } currentDiscount.value=n }
function addNote(){ toast('Note Added','Order note has been saved') }
function holdOrder(){ if(!cart.value.length) return toast('Empty Cart','Cannot hold an empty order','warning'); toast('Order Held','Order has been saved for later') }
function sendToKitchen(){ if(!cart.value.length) return toast('Empty Cart','Cannot send empty order','warning'); toast('Sent to Kitchen','Order has been sent to the kitchen') }

// ---- Payment
const showPayment = ref(false)
const paymentMethod = ref<'Cash'|'Card'|'Digital'>('Cash')
const tendered = ref<number|null>(null)
const change = computed(()=> (tendered.value ?? 0) - total.value)
function openPayment(m:'Cash'|'Card'|'Digital'){
  if(!cart.value.length) return toast('Empty Cart','Cannot process payment for empty cart','warning')
  paymentMethod.value=m; tendered.value=total.value; showPayment.value=true
}
function closePayment(){ showPayment.value=false; tendered.value=null }

async function processPayment(){
  if((tendered.value ?? 0) < total.value) return toast('Insufficient Payment','Please enter a valid amount','error')

  try {
    const payload = {
      location_id: locationId,
      items: cart.value.map(i => ({
        product_id: i.id,
        variant_id: i.variant_id, // may be null
        qty: i.qty,
      })),
      discount_percent: currentDiscount.value,
      payment: {
        method: paymentMethod.value,
        tendered_cents: Math.round((tendered.value ?? total.value) * 100),
      },
    }

    const resp = await fetch('/api/orders', {
      method: 'POST',
      headers: { 'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' },
      body: JSON.stringify(payload),
    })

    if (!resp.ok) {
      const err = await resp.text().catch(()=> '')
      console.error('Order failed', err)
      return toast('Order Failed', 'Please try again', 'error')
    }

    cart.value = []
    currentDiscount.value = 0
    closePayment()
    const data = await resp.json().catch(()=> ({}))
    toast('Payment Successful', `Order ${data.order_no ?? ''} completed`, 'success')

    await loadProducts() // Reload products to get updated stock levels
  } catch (e) {
    console.error(e)
    toast('Error', 'Unexpected error processing order', 'error')
  }
}

// ---- Misc UI helpers
function toggleView(){ /* placeholder */ }
function openSettings(){ toast('Settings','Settings panel would open here') }
function placeholder(name:string){ return `https://via.placeholder.com/400x240/f97316/ffffff?text=${encodeURIComponent(name)}` }
function onImgFallback(e:Event,p:Product){ const img=e.target as HTMLImageElement; img.src=placeholder(p.name) }
function comingSoon(label:string){ toast('Coming soon', `${label} is under construction`, 'warning') }

// ---- Toast
const toastShow = ref(false); const toastTitle=ref(''); const toastMsg=ref(''); const toastIcon=ref('fas fa-check'); const toastBg=ref('bg-green-500'); let timer:number|undefined
function toast(title:string,msg:string,type:'success'|'error'|'warning'='success'){
  toastTitle.value=title; toastMsg.value=msg
  toastIcon.value= type==='success'?'fas fa-check': type==='error'?'fas fa-times':'fas fa-exclamation'
  toastBg.value= type==='success'?'bg-green-500': type==='error'?'bg-red-500':'bg-yellow-500'
  toastShow.value=true; clearTimeout(timer as any); timer = window.setTimeout(()=>toastShow.value=false,3000)
}

// number format
function nf(n:number){ return n.toLocaleString() }
</script>

<style>
/* keep your existing global styles from app.blade.php */
</style>
