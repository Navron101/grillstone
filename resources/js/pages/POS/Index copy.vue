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
            <p class="text-sm text-gray-600">Table #<span>{{ tableNumber }}</span></p>
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <div class="text-right mr-2">
            <p class="text-sm text-gray-600">{{ currentTime }}</p>
            <p class="text-sm font-medium text-gray-800">Cashier: {{ cashier }}</p>
          </div>

          <!-- Settings -->
          <button
            @click="openSettings"
            class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors"
            title="Settings"
          >
            <i class="fas fa-cog text-lg"></i>
          </button>

          <!-- Logout -->
          <button
            @click="logout"
            class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors"
            title="Log out"
          >
            <i class="fas fa-right-from-bracket text-lg"></i>
          </button>
        </div>
      </div>
    </header>

    <div class="flex h-[calc(100vh-80px)]">
      <!-- Cart Sidebar -->
      <aside class="w-96 glass-effect m-4 rounded-2xl shadow-2xl flex flex-col">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Current Order</h2>
            <div class="flex items-center space-x-2">
              <button @click="clearCart" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Clear Cart">
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
          <div v-for="(item,i) in cart" :key="item.id" class="cart-item bg-gray-50 rounded-lg p-3 flex items-center justify-between">
            <div class="flex-1">
              <h4 class="font-medium text-gray-800">{{ item.name }}</h4>
              <p class="text-sm text-gray-500">JMD {{ nf(item.price) }} each</p>
              <div class="flex items-center mt-2">
                <button @click="updateQuantity(i,-1)" class="w-6 h-6 bg-gray-200 rounded-full text-gray-600 hover:bg-gray-300 transition-colors">
                  <i class="fas fa-minus text-xs"></i>
                </button>
                <span class="mx-3 font-medium">{{ item.qty }}</span>
                <button @click="updateQuantity(i,1)" class="w-6 h-6 bg-orange-100 rounded-full text-orange-600 hover:bg-orange-200 transition-colors">
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
            <button @click="applyDiscount" class="bg-orange-500 hover:bg-orange-600 text-white py-2 rounded-lg font-medium transition-colors">
              <i class="fas fa-percentage mr-1"></i> Discount
            </button>
            <button @click="addNote" class="bg-amber-600 hover:bg-amber-700 text-white py-2 rounded-lg font-medium transition-colors">
              <i class="fas fa-sticky-note mr-1"></i> Note
            </button>
          </div>

          <div class="grid grid-cols-2 gap-2">
            <button @click="holdOrder" class="bg-yellow-600 hover:bg-yellow-700 text-white py-2 rounded-lg font-medium transition-colors">
              <i class="fas fa-pause mr-1"></i> Hold
            </button>
            <button @click="sendToKitchen" class="bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-medium transition-colors">
              <i class="fas fa-utensils mr-1"></i> Kitchen
            </button>
          </div>

          <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Payment Method:</h3>
            <div class="grid grid-cols-3 gap-2">
              <button @click="openPayment('Cash')" class="bg-gray-800 hover:bg-gray-900 text-white py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-money-bill-wave mr-1"></i><br>Cash
              </button>
              <button @click="openPayment('Card')" class="bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-credit-card mr-1"></i><br>Card
              </button>
              <button @click="openPayment('Digital')" class="bg-purple-600 hover:bg-purple-700 text-white py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-mobile-alt mr-1"></i><br>Digital
              </button>
            </div>
          </div>
        </div>
      </aside>

      <!-- Main -->
      <section class="flex-1 p-4 flex flex-col">
        <div class="glass-effect rounded-2xl p-4 mb-4 shadow-lg">
          <div class="flex items-center space-x-4">
            <div class="flex-1 relative">
              <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
              <input v-model="query" type="text" placeholder="Search products..."
                     class="w-full pl-10 pr-4 py-2 border-0 bg-white rounded-lg shadow-sm focus:ring-2 focus:ring-orange-500 focus:outline-none">
            </div>
            <button @click="toggleView" class="p-2 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow" title="Toggle View">
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
                 class="product-card bg-white rounded-xl shadow-sm hover:shadow-lg cursor-pointer transition-all duration-300 overflow-hidden"
                 @click="addToCart(p)">
              <div class="relative">
                <img :src="p.img" :alt="p.name" class="w-full h-32 object-cover" @error="onImgFallback($event,p)">
                <div v-if="p.popular" class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">Popular</div>
              </div>
              <div class="p-3">
                <h3 class="font-semibold text-gray-800 mb-1 truncate">{{ p.name }}</h3>
                <p class="text-xs text-gray-500 mb-2 line-clamp-2">{{ p.description }}</p>
                <div class="flex items-center justify-between">
                  <span class="font-bold text-orange-600">JMD {{ nf(p.price) }}</span>
                  <button class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-1 rounded-lg text-sm transition-colors">
                    <i class="fas fa-plus"></i>
                  </button>
                </div>
              </div>
            </div>

            <div v-if="!filteredProducts.length" class="col-span-full text-center py-12">
              <i class="fas fa-search text-4xl text-gray-400 mb-4"></i>
              <p class="text-gray-500">No products found</p>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Payment Modal -->
    <div v-if="showPayment" class="fixed inset-0 modal bg-black bg-opacity-50 flex items-center justify-center z-50">
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

          <div v-if="tendered !== null" class="rounded-lg p-4" :class="change>=0?'bg-green-50 border border-green-200':'bg-red-50 border red-200'">
            <div class="text-sm" :class="change>=0?'text-green-700':'text-red-700'">Change Due:</div>
            <div class="text-2xl font-bold" :class="change>=0?'text-green-800':'text-red-800'">
              {{ change>=0 ? 'JMD ' + nf(change) : 'Short ' + nf(Math.abs(change)) }}
            </div>
          </div>

          <div class="flex space-x-3">
            <button @click="closePayment" class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
              Cancel
            </button>
            <button @click="processPayment" class="flex-1 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
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
import { computed, onMounted, ref } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

type PageProps = { cashier: string; tableNumber: number; taxRate: number }
const page = usePage<PageProps>()
const cashier = page.props.cashier ?? 'Cashier'
const tableNumber = page.props.tableNumber ?? 1
const taxRate = page.props.taxRate ?? 0.15

// --- Logout (works with or without Ziggy) ---
const logout = () => {
  try {
    // @ts-ignore `route` is injected by Ziggy when @routes is in app.blade.php
    const url = typeof route === 'function' ? route('logout') : '/logout'
    router.post(url)
  } catch {
    router.post('/logout')
  }
}

// --- Clock ---
const currentTime = ref('')
function tick(){ currentTime.value = new Date().toLocaleTimeString() }

// --- Products (replace with DB props when ready) ---
const products = ref([
  { id:1, name:'Jerk Chicken Wrap', price:1200, category:'Entrees', img:'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=200&h=200&fit=crop', description:'Spicy jerk chicken wrapped in soft tortilla', popular:true },
  { id:2, name:'BBQ Ribs', price:1800, category:'Entrees', img:'https://images.unsplash.com/photo-1544025162-d76694265947?w=200&h=200&fit=crop', description:'Tender ribs with house BBQ sauce' },
  { id:3, name:'Fried Plantain', price:450, category:'Sides', img:'https://images.unsplash.com/photo-1606312619070-d48b4c652a52?w=200&h=200&fit=crop', description:'Sweet fried plantains' },
  { id:4, name:'Curry Goat', price:2000, category:'Entrees', img:'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=200&h=200&fit=crop', description:'Traditional Caribbean curry goat', popular:true },
  { id:5, name:'Festival', price:300, category:'Sides', img:'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=200&h=200&fit=crop', description:'Fried cornmeal dumplings' },
  { id:6, name:'Ackee & Saltfish', price:1600, category:'Entrees', img:'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=200&h=200&fit=crop', description:'National dish of Jamaica' },
  { id:7, name:'Rice & Peas', price:500, category:'Sides', img:'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?w=200&h=200&fit=crop', description:'Coconut rice with kidney beans' },
  { id:8, name:'Red Stripe Beer', price:350, category:'Beverages', img:'https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?w=200&h=200&fit=crop', description:'Local Jamaican beer' },
])

// --- Filters/search ---
const query = ref('')
const currentCategory = ref('All')
const categories = computed(() => ['All', ...new Set(products.value.map(p => p.category))])
const filteredProducts = computed(() => {
  const q = query.value.toLowerCase()
  return products.value.filter(p =>
    (currentCategory.value === 'All' || p.category === currentCategory.value) &&
    (!q || p.name.toLowerCase().includes(q))
  )
})

// --- Cart ---
type CartItem = { id:number; name:string; price:number; qty:number }
const cart = ref<CartItem[]>([])
const totalItems = computed(() => cart.value.reduce((s,i)=>s+i.qty,0))
function addToCart(p:any){ const ex=cart.value.find(i=>i.id===p.id); ex?ex.qty++:cart.value.push({id:p.id,name:p.name,price:p.price,qty:1}); toast('Added to Cart', `${p.name} added successfully`) }
function updateQuantity(i:number, d:number){ const it=cart.value[i]; it.qty+=d; if(it.qty<=0)cart.value.splice(i,1) }
function removeFromCart(i:number){ const it=cart.value[i]; cart.value.splice(i,1); toast('Removed from Cart', `${it.name} removed`, 'warning') }
function clearCart(){ if(!cart.value.length) return; if(confirm('Clear entire cart?')){ cart.value=[]; currentDiscount.value=0 } }

// --- Totals ---
const currentDiscount = ref(0)
const subtotal = computed(()=> cart.value.reduce((s,i)=>s+i.price*i.qty,0))
const discountAmount = computed(()=> Math.round(subtotal.value * (currentDiscount.value/100)))
const tax = computed(()=> Math.round((subtotal.value - discountAmount.value) * taxRate))
const total = computed(()=> subtotal.value + tax.value - discountAmount.value)

// --- Actions ---
function applyDiscount(){ const v = prompt('Enter discount percentage (0-50):'); const n=Number(v); if(!Number.isFinite(n)||n<0||n>50){ return toast('Invalid Discount','Enter a valid % between 0 and 50','error') } currentDiscount.value=n }
function addNote(){ toast('Note Added','Order note has been saved') }
function holdOrder(){ if(!cart.value.length) return toast('Empty Cart','Cannot hold an empty order','warning'); toast('Order Held','Order has been saved for later') }
function sendToKitchen(){ if(!cart.value.length) return toast('Empty Cart','Cannot send empty order','warning'); toast('Sent to Kitchen','Order has been sent to the kitchen') }

// --- Payment ---
const showPayment = ref(false)
const paymentMethod = ref<'Cash'|'Card'|'Digital'>('Cash')
const tendered = ref<number|null>(null)
const change = computed(()=> (tendered.value ?? 0) - total.value)
function openPayment(m:'Cash'|'Card'|'Digital'){ if(!cart.value.length) return toast('Empty Cart','Cannot process payment for empty cart','warning'); paymentMethod.value=m; tendered.value=total.value; showPayment.value=true }
function closePayment(){ showPayment.value=false; tendered.value=null }
function processPayment(){ if((tendered.value ?? 0) < total.value) return toast('Insufficient Payment','Please enter a valid amount','error'); const ch=(tendered.value??0)-total.value; cart.value=[]; currentDiscount.value=0; closePayment(); toast('Payment Successful',`Change: JMD ${nf(ch)}.00`,'success') }
function toggleView(){ toast('View Toggle','View toggle feature') }
function openSettings(){ toast('Settings','Settings panel would open here') }
function onImgFallback(e:Event,p:any){ const img=e.target as HTMLImageElement; img.src=`https://via.placeholder.com/200x200/f97316/ffffff?text=${encodeURIComponent(p.name)}` }

// --- Toast ---
const toastShow = ref(false); const toastTitle=ref(''); const toastMsg=ref(''); const toastIcon=ref('fas fa-check'); const toastBg=ref('bg-green-500'); let timer:number|undefined
function toast(title:string,msg:string,type:'success'|'error'|'warning'='success'){ toastTitle.value=title; toastMsg.value=msg; toastIcon.value= type==='success'?'fas fa-check': type==='error'?'fas fa-times':'fas fa-exclamation'; toastBg.value= type==='success'?'bg-green-500': type==='error'?'bg-red-500':'bg-yellow-500'; toastShow.value=true; clearTimeout(timer as any); timer = window.setTimeout(()=>toastShow.value=false,3000) }

// --- Init ---
onMounted(()=>{ tick(); setInterval(tick,1000) })
function nf(n:number){ return n.toLocaleString() }
</script>

