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
              <a :href="reportsHref"
                 class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors"
                 :class="isActive(reportsHref)
                  ? 'bg-orange-600 text-white'
                  : 'text-gray-700 hover:bg-orange-50 hover:text-orange-700'">
                <i :class="['fas fa-chart-line text-lg', isActive(reportsHref) ? 'text-white' : 'text-gray-600']"></i>
                <span v-if="sidebarOpen" class="font-medium">Reports</span>
              </a>
            </li>

            <!-- HR / Payroll Dropdown -->
            <li>
              <button @click="hrMenuOpen = !hrMenuOpen"
                      class="w-full flex items-center gap-3 rounded-xl px-3 py-2 transition-colors"
                      :class="isActive('/hr') || isActive('/payroll')
                        ? 'bg-orange-600 text-white'
                        : 'text-gray-700 hover:bg-orange-50 hover:text-orange-700'">
                <i :class="['fas fa-users text-lg', (isActive('/hr') || isActive('/payroll')) ? 'text-white' : 'text-gray-600']"></i>
                <span v-if="sidebarOpen" class="font-medium">HR / Payroll</span>
                <i v-if="sidebarOpen" :class="['fas fa-chevron-down text-xs ml-auto transition-transform', hrMenuOpen ? 'rotate-180' : '']"></i>
              </button>

              <!-- HR Submenu -->
              <ul v-if="hrMenuOpen && sidebarOpen" class="mt-1 ml-6 space-y-1">
                <li>
                  <a href="/hr/employees" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors text-gray-600 hover:bg-orange-50 hover:text-orange-700">
                    <i class="fas fa-user-tie text-xs"></i>
                    <span>Employees</span>
                  </a>
                </li>
                <li>
                  <a href="/hr/departments" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors text-gray-600 hover:bg-orange-50 hover:text-orange-700">
                    <i class="fas fa-building text-xs"></i>
                    <span>Departments</span>
                  </a>
                </li>
                <li>
                  <a href="/hr/time-logs" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors text-gray-600 hover:bg-orange-50 hover:text-orange-700">
                    <i class="fas fa-clock text-xs"></i>
                    <span>Time Logs</span>
                  </a>
                </li>
                <li>
                  <a href="/hr/clock" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors text-gray-600 hover:bg-orange-50 hover:text-orange-700">
                    <i class="fas fa-fingerprint text-xs"></i>
                    <span>Clock In/Out</span>
                  </a>
                </li>
                <li>
                  <a href="/payroll" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors text-gray-600 hover:bg-orange-50 hover:text-orange-700">
                    <i class="fas fa-money-check-alt text-xs"></i>
                    <span>Payroll</span>
                  </a>
                </li>
              </ul>
            </li>

            <li>
              <button @click="comingSoon('Menu Updates')"
                      class="w-full flex items-center gap-3 rounded-xl px-3 py-2 text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-utensils text-lg text-gray-600"></i>
                <span v-if="sidebarOpen" class="font-medium">Menu Updates</span>
              </button>
            </li>

            <!-- Till Management Section -->
            <li class="pt-2 mt-2 border-t border-gray-200">
              <a href="/settlements"
                 class="flex items-center gap-3 rounded-xl px-3 py-2 text-gray-700 hover:bg-orange-50 hover:text-orange-700 transition-colors">
                <i class="fas fa-history text-lg text-gray-600"></i>
                <span v-if="sidebarOpen" class="font-medium">Settlements</span>
              </a>
            </li>

            <li>
              <button @click="showPayoutModal = true"
                      class="w-full flex items-center gap-3 rounded-xl px-3 py-2 text-gray-700 hover:bg-orange-50 hover:text-orange-700 transition-colors">
                <i class="fas fa-money-bill-transfer text-lg text-gray-600"></i>
                <span v-if="sidebarOpen" class="font-medium">Payout</span>
              </button>
            </li>

            <li>
              <button @click="showCloseTillModal = true"
                      class="w-full flex items-center gap-3 rounded-xl px-3 py-2 text-white bg-green-600 hover:bg-green-700 transition-colors">
                <i class="fas fa-cash-register text-lg text-white"></i>
                <span v-if="sidebarOpen" class="font-medium font-bold">Close Till</span>
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

          <div class="flex-1 glass-effect rounded-2xl p-4 shadow-lg overflow-hidden flex flex-col">
            <!-- Products Grid -->
            <div class="flex-1 overflow-y-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 pb-4 auto-rows-max">
              <div v-for="p in paginatedProducts" :key="p.id"
                   class="product-card bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col"
                   :class="p.is_out_of_stock ? 'opacity-60 grayscale cursor-not-allowed' : 'cursor-pointer'"
                   @click="onPickDish(p)">
                <div class="relative">
                  <img :src="p.img || placeholder(p.name)" :alt="p.name" class="w-full h-40 object-cover" @error="e=>onImgFallback(e,p)">

                  <!-- Pin Button -->
                  <button @click.stop="togglePin(p.id)"
                          class="absolute top-2 right-2 w-8 h-8 rounded-full flex items-center justify-center transition-all"
                          :class="isPinned(p.id) ? 'bg-yellow-500 text-white' : 'bg-white/80 text-gray-600 hover:bg-white'">
                    <i class="fas fa-thumbtack text-sm"></i>
                  </button>

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
                <div class="p-4 flex flex-col flex-1">
                  <h3 class="font-bold text-gray-900 mb-3 text-base">{{ p.name }}</h3>
                  <div class="flex items-center justify-between mt-auto">
                    <span class="font-bold text-orange-600 text-lg">JMD {{ nf(p.price) }}</span>
                    <button class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-1.5 rounded-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed"
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

            <!-- Pagination Controls -->
            <div v-if="totalPages > 1" class="border-t border-gray-200 pt-4 mt-4">
              <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                  Showing {{ ((currentPage - 1) * itemsPerPage) + 1 }} - {{ Math.min(currentPage * itemsPerPage, filteredProducts.length) }} of {{ filteredProducts.length }} items
                </div>

                <div class="flex items-center gap-2">
                  <button @click="prevPage" :disabled="currentPage === 1"
                          class="px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                  </button>

                  <div class="flex gap-1">
                    <button v-for="page in visiblePages" :key="page" @click="goToPage(page)"
                            :class="page === currentPage ? 'bg-orange-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'"
                            class="px-3 py-2 rounded-lg min-w-[2.5rem]">
                      {{ page }}
                    </button>
                  </div>

                  <button @click="nextPage" :disabled="currentPage === totalPages"
                          class="px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- Variant picker -->
    <div v-if="variantModal.show" class="fixed inset-0 modal bg-black/50 flex items-center justify-center z-50" @click.self="closeVariantModal">
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

    <!-- Receipt Modal -->
    <div v-if="showReceipt" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <!-- Receipt Content -->
        <div id="receipt-content" class="p-8">
          <!-- Header -->
          <div class="text-center mb-6 border-b-2 border-dashed border-gray-300 pb-4">
            <div class="flex items-center justify-center gap-2 mb-2">
              <i class="fas fa-fire text-3xl text-orange-600"></i>
              <h1 class="text-3xl font-bold text-gray-900">Grillstone</h1>
            </div>
            <p class="text-sm text-gray-600">{{ receiptData.location || 'Main Location' }}</p>
            <p class="text-xs text-gray-500">Thank you for your order!</p>
          </div>

          <!-- Order Info -->
          <div class="mb-4 text-sm space-y-1">
            <div class="flex justify-between">
              <span class="text-gray-600">Order #:</span>
              <span class="font-semibold">{{ receiptData.order_no }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Date:</span>
              <span>{{ receiptData.date }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Time:</span>
              <span>{{ receiptData.time }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Cashier:</span>
              <span>{{ receiptData.cashier }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Table:</span>
              <span>#{{ receiptData.table }}</span>
            </div>
          </div>

          <!-- Items -->
          <div class="border-t-2 border-dashed border-gray-300 pt-4 mb-4">
            <h3 class="font-semibold mb-3">Order Items</h3>
            <div class="space-y-2 text-sm">
              <div v-for="(item, idx) in receiptData.items" :key="idx">
                <div class="flex justify-between items-start">
                  <div class="flex-1">
                    <div class="font-medium">{{ item.name }}</div>
                    <div v-if="item.variant" class="text-xs text-gray-500 ml-2">{{ item.variant }}</div>
                    <div class="text-xs text-gray-500">{{ item.qty }} × JMD {{ formatMoney(item.unit_price) }}</div>
                  </div>
                  <div class="font-semibold whitespace-nowrap ml-4">JMD {{ formatMoney(item.total) }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Totals -->
          <div class="border-t-2 border-dashed border-gray-300 pt-4 space-y-2 text-sm">
            <div class="flex justify-between">
              <span>Subtotal:</span>
              <span>JMD {{ formatMoney(receiptData.subtotal) }}</span>
            </div>
            <div v-if="receiptData.discount > 0" class="flex justify-between text-green-600">
              <span>Discount ({{ receiptData.discount_percent }}%):</span>
              <span>- JMD {{ formatMoney(receiptData.discount) }}</span>
            </div>
            <div class="flex justify-between">
              <span>Tax ({{ (receiptData.tax_rate * 100).toFixed(0) }}%):</span>
              <span>JMD {{ formatMoney(receiptData.tax) }}</span>
            </div>
            <div class="flex justify-between text-lg font-bold border-t-2 border-gray-800 pt-2 mt-2">
              <span>Total:</span>
              <span>JMD {{ formatMoney(receiptData.total) }}</span>
            </div>
          </div>

          <!-- Payment -->
          <div class="border-t-2 border-dashed border-gray-300 mt-4 pt-4 space-y-1 text-sm">
            <div class="flex justify-between">
              <span>Payment Method:</span>
              <span class="font-semibold uppercase">{{ receiptData.payment_method }}</span>
            </div>
            <div class="flex justify-between">
              <span>Tendered:</span>
              <span>JMD {{ formatMoney(receiptData.tendered) }}</span>
            </div>
            <div v-if="receiptData.change > 0" class="flex justify-between font-semibold text-green-600">
              <span>Change:</span>
              <span>JMD {{ formatMoney(receiptData.change) }}</span>
            </div>
          </div>

          <!-- Footer -->
          <div class="text-center mt-6 pt-4 border-t-2 border-dashed border-gray-300 text-xs text-gray-500">
            <p>Powered by Grillstone POS</p>
            <p class="mt-1">{{ new Date().toLocaleDateString() }}</p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-2 p-4 bg-gray-50 border-t">
          <button @click="printReceipt" class="flex-1 bg-orange-600 hover:bg-orange-700 text-white py-3 rounded-lg font-semibold">
            <i class="fas fa-print mr-2"></i>Print Receipt
          </button>
          <button @click="closeReceipt" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white py-3 rounded-lg font-semibold">
            Close
          </button>
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

    <!-- Payout Modal -->
    <div v-if="showPayoutModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @click.self="showPayoutModal = false">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-600 to-red-600 text-white px-6 py-4 rounded-t-2xl">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <i class="fas fa-money-bill-transfer text-2xl"></i>
              <h2 class="text-xl font-bold">Record Payout</h2>
            </div>
            <button @click="showPayoutModal = false" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2">
              <i class="fas fa-times text-lg"></i>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Amount (JMD) *</label>
            <input v-model="payoutForm.amount" type="number" step="0.01" placeholder="0.00"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Reason *</label>
            <input v-model="payoutForm.reason" type="text" placeholder="e.g., Supplier payment"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Recipient</label>
            <input v-model="payoutForm.recipient" type="text" placeholder="e.g., ABC Suppliers"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
            <textarea v-model="payoutForm.notes" rows="3" placeholder="Optional notes..."
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"></textarea>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 px-6 pb-6">
          <button @click="showPayoutModal = false" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 font-semibold">
            Cancel
          </button>
          <button @click="submitPayout" :disabled="processingPayout"
                  class="flex-1 px-4 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold disabled:opacity-50">
            <i v-if="processingPayout" class="fas fa-spinner fa-spin mr-2"></i>
            {{ processingPayout ? 'Processing...' : 'Record Payout' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Close Till Modal -->
    <div v-if="showCloseTillModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @click.self="showCloseTillModal = false">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 text-white px-6 py-4 rounded-t-2xl">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <i class="fas fa-cash-register text-2xl"></i>
              <h2 class="text-xl font-bold">Close Till</h2>
            </div>
            <button @click="showCloseTillModal = false" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2">
              <i class="fas fa-times text-lg"></i>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6">
          <!-- Expected Amounts -->
          <div v-if="calculatedData" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-bold text-blue-900 mb-3 flex items-center gap-2">
              <i class="fas fa-calculator"></i>
              System Expected Amounts
            </h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-600">Cash Sales:</span>
                <span class="font-semibold">{{ formatMoney(calculatedData.expected_cash_cents) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Card Sales:</span>
                <span class="font-semibold">{{ formatMoney(calculatedData.expected_card_cents) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Paid Out:</span>
                <span class="font-semibold text-red-600">-{{ formatMoney(calculatedData.paid_out_cents) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Total Sales:</span>
                <span class="font-semibold">{{ formatMoney(calculatedData.total_sales_cents) }}</span>
              </div>
              <div class="flex justify-between col-span-2 pt-2 border-t border-blue-300">
                <span class="font-bold text-blue-900">Net Cash Expected:</span>
                <span class="font-bold text-blue-900">{{ formatMoney(calculatedData.net_cash_cents) }}</span>
              </div>
            </div>
          </div>

          <!-- Cash Count Form -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Actual Cash Counted (JMD) *</label>
            <input v-model="closeTillForm.actualCash" type="number" step="0.01" placeholder="0.00"
                   class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1">Count all bills and coins in the till and enter the total amount</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
            <textarea v-model="closeTillForm.notes" rows="2" placeholder="Optional notes (e.g., cashier name, shift details)..."
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"></textarea>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 px-6 pb-6">
          <button @click="showCloseTillModal = false" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 font-semibold">
            Cancel
          </button>
          <button @click="submitCloseTill" :disabled="closingTill"
                  class="flex-1 px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold disabled:opacity-50">
            <i v-if="closingTill" class="fas fa-spinner fa-spin mr-2"></i>
            {{ closingTill ? 'Closing...' : 'Close Till' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Settlement Report Modal -->
    <div v-if="showSettlementReportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @click.self="showSettlementReportModal = false">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4 rounded-t-2xl sticky top-0 z-10">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <i class="fas fa-file-invoice-dollar text-2xl"></i>
              <h2 class="text-xl font-bold">Till Settlement Report</h2>
            </div>
            <button @click="showSettlementReportModal = false" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2">
              <i class="fas fa-times text-lg"></i>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div v-if="settlementData" class="p-6 space-y-6">
          <!-- Variance Alert -->
          <div v-if="settlementData.cash_variance_cents !== 0"
               class="rounded-lg p-4 border-2"
               :class="settlementData.cash_variance_cents > 0 ? 'bg-green-50 border-green-500' : 'bg-red-50 border-red-500'">
            <div class="flex items-center gap-3">
              <i class="text-3xl" :class="settlementData.cash_variance_cents > 0 ? 'fas fa-circle-check text-green-600' : 'fas fa-circle-exclamation text-red-600'"></i>
              <div>
                <h3 class="font-bold text-lg" :class="settlementData.cash_variance_cents > 0 ? 'text-green-900' : 'text-red-900'">
                  {{ settlementData.cash_variance_cents > 0 ? 'CASH OVER' : 'CASH SHORT' }}
                </h3>
                <p class="text-sm" :class="settlementData.cash_variance_cents > 0 ? 'text-green-700' : 'text-red-700'">
                  Variance: {{ settlementData.cash_variance_cents > 0 ? '+' : '' }}{{ formatMoney(settlementData.cash_variance_cents) }}
                </p>
              </div>
            </div>
          </div>

          <div v-else class="bg-green-50 border-2 border-green-500 rounded-lg p-4">
            <div class="flex items-center gap-3">
              <i class="fas fa-circle-check text-3xl text-green-600"></i>
              <div>
                <h3 class="font-bold text-lg text-green-900">PERFECT MATCH!</h3>
                <p class="text-sm text-green-700">Cash count matches expected amount exactly</p>
              </div>
            </div>
          </div>

          <!-- Cash Breakdown -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
              <i class="fas fa-money-bill-wave text-green-600"></i>
              Cash Breakdown
            </h3>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-600">Expected Cash:</span>
                <span class="font-semibold">{{ formatMoney(settlementData.expected_cash_cents) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Paid Out:</span>
                <span class="font-semibold text-red-600">-{{ formatMoney(settlementData.paid_out_cents) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Paid In:</span>
                <span class="font-semibold text-green-600">+{{ formatMoney(settlementData.paid_in_cents) }}</span>
              </div>
              <div class="flex justify-between pt-2 border-t border-gray-300">
                <span class="font-semibold text-gray-700">Net Cash Expected:</span>
                <span class="font-bold">{{ formatMoney(settlementData.net_cash_cents) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="font-semibold text-gray-700">Actual Cash Counted:</span>
                <span class="font-bold">{{ formatMoney(settlementData.actual_cash_cents) }}</span>
              </div>
              <div class="flex justify-between pt-2 border-t-2 border-gray-400"
                   :class="settlementData.cash_variance_cents > 0 ? 'text-green-600' : settlementData.cash_variance_cents < 0 ? 'text-red-600' : 'text-gray-900'">
                <span class="font-bold">Variance:</span>
                <span class="font-bold">{{ settlementData.cash_variance_cents > 0 ? '+' : '' }}{{ formatMoney(settlementData.cash_variance_cents) }}</span>
              </div>
            </div>
          </div>

          <!-- Payment Methods Summary -->
          <div class="grid grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-lg p-4">
              <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-money-bill text-green-600"></i>
                <span class="text-sm font-semibold text-gray-700">Cash</span>
              </div>
              <p class="text-xl font-bold text-gray-900">{{ formatMoney(settlementData.net_cash_cents) }}</p>
            </div>

            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
              <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-credit-card text-blue-600"></i>
                <span class="text-sm font-semibold text-gray-700">Credit/Debit</span>
              </div>
              <p class="text-xl font-bold text-gray-900">{{ formatMoney(settlementData.expected_card_cents) }}</p>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-4">
              <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-gift text-purple-600"></i>
                <span class="text-sm font-semibold text-gray-700">Gift Cards</span>
              </div>
              <p class="text-xl font-bold text-gray-900">{{ formatMoney(settlementData.expected_gift_card_cents || 0) }}</p>
            </div>
          </div>

          <!-- Sales Summary -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
              <i class="fas fa-chart-line text-orange-600"></i>
              Sales Summary
            </h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-600">Total Sales:</span>
                <span class="font-semibold">{{ formatMoney(settlementData.total_sales_cents) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Number of Transactions:</span>
                <span class="font-semibold">{{ settlementData.num_transactions }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Cost of Goods Sold:</span>
                <span class="font-semibold">{{ formatMoney(settlementData.cogs_cents || 0) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Gross Profit:</span>
                <span class="font-semibold text-green-600">{{ formatMoney(settlementData.profit_cents) }}</span>
              </div>
            </div>
          </div>

          <!-- Period Info -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-bold text-blue-900 mb-2">Settlement Period</h3>
            <div class="text-sm text-blue-800">
              <p><strong>Start:</strong> {{ formatTime(settlementData.period_start) }}</p>
              <p><strong>End:</strong> {{ formatTime(settlementData.period_end) }}</p>
              <p><strong>Settlement Date:</strong> {{ formatTime(settlementData.settlement_date) }}</p>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="settlementData.notes" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h3 class="font-bold text-yellow-900 mb-2">Notes</h3>
            <p class="text-sm text-yellow-800">{{ settlementData.notes }}</p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 px-6 pb-6">
          <button @click="downloadSettlementPdf" class="flex-1 px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold">
            <i class="fas fa-download mr-2"></i>Download PDF
          </button>
          <button @click="viewAllSettlements" class="flex-1 px-4 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-semibold">
            <i class="fas fa-history mr-2"></i>View All Settlements
          </button>
          <button @click="showSettlementReportModal = false" class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">
            <i class="fas fa-check mr-2"></i>Close Report
          </button>
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

// ---- Utilities
const nf = (n:number) => n.toLocaleString()

// ---- Sidebar
const sidebarOpen = ref(true)
const hrMenuOpen = ref(false)
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
const reportsHref = '/reports'
const hrHref = '/hr/employees'
function isActive(href: string){ try{ const cur=window.location.pathname; const path=new URL(href,window.location.origin).pathname; return cur.startsWith(path) }catch{ return false } }

// ---- Logout
const logout = () => { try { // @ts-ignore
  const url = typeof route === 'function' ? route('logout') : '/logout'; router.post(url)
} catch { router.post('/logout') } }

// ---- Clock
const currentTime = ref(''); function tick(){ currentTime.value = new Date().toLocaleTimeString() }
onMounted(()=>{ tick(); setInterval(tick,1000) })

// ---- Till Management
const showPayoutModal = ref(false)
const showCloseTillModal = ref(false)
const showSettlementReportModal = ref(false)
const payoutForm = ref({ amount: '', reason: '', recipient: '', notes: '' })
const closeTillForm = ref({ actualCash: '', notes: '' })
const settlementData = ref<any>(null)
const calculatedData = ref<any>(null)
const processingPayout = ref(false)
const closingTill = ref(false)

async function submitPayout() {
  if (!payoutForm.value.amount || !payoutForm.value.reason) {
    toast('Error', 'Please fill in amount and reason', 'error')
    return
  }
  processingPayout.value = true
  try {
    const resp = await fetch('/api/payouts', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        amount: parseFloat(payoutForm.value.amount),
        reason: payoutForm.value.reason,
        recipient: payoutForm.value.recipient,
        notes: payoutForm.value.notes
      })
    })
    if (!resp.ok) throw new Error('Failed to record payout')
    toast('Success', `Payout of JMD ${payoutForm.value.amount} recorded`, 'success')
    showPayoutModal.value = false
    payoutForm.value = { amount: '', reason: '', recipient: '', notes: '' }
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to record payout', 'error')
  } finally {
    processingPayout.value = false
  }
}

async function loadCalculatedData() {
  try {
    const resp = await fetch('/api/settlements/calculate')
    if (!resp.ok) throw new Error('Failed to calculate')
    calculatedData.value = await resp.json()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load settlement data', 'error')
  }
}

async function submitCloseTill() {
  if (!closeTillForm.value.actualCash) {
    toast('Error', 'Please enter the cash amount counted', 'error')
    return
  }
  closingTill.value = true
  try {
    const resp = await fetch('/api/settlements', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        actual_cash: parseFloat(closeTillForm.value.actualCash),
        notes: closeTillForm.value.notes
      })
    })
    if (!resp.ok) throw new Error('Failed to close till')
    settlementData.value = await resp.json()
    showCloseTillModal.value = false
    showSettlementReportModal.value = true
    closeTillForm.value = { actualCash: '', notes: '' }
    toast('Success', 'Till closed successfully', 'success')
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to close till', 'error')
  } finally {
    closingTill.value = false
  }
}

function formatMoney(cents: number): string {
  return 'JMD ' + (cents / 100).toLocaleString('en-JM', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatTime(dateStr: string): string {
  try {
    return new Date(dateStr).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
  } catch {
    return dateStr
  }
}

function downloadSettlementPdf() {
  if (settlementData.value && settlementData.value.id) {
    window.location.href = `/api/settlements/${settlementData.value.id}/pdf`
  }
}

function viewAllSettlements() {
  window.location.href = '/settlements'
}

watch(showCloseTillModal, (newVal) => {
  if (newVal) {
    loadCalculatedData()
  }
})

// ---- Products from API
type Product = {
  id:number; name:string; price:number; category:string; img?:string|null; description?:string|null; popular?:boolean;
  on_hand?: number; low_stock_threshold?: number; is_low_stock?: boolean; is_out_of_stock?: boolean; type?: string;
}
const products = ref<Product[]>([])
const loading = ref(false)

async function loadProducts(){
  loading.value = true
  try{
    console.log('Loading products from API...')
    const resp = await fetch(`/api/products?location_id=${locationId}`, { headers:{ 'Accept':'application/json' } })
    console.log('API Response status:', resp.status)
    if(!resp.ok){
      const errorText = await resp.text()
      console.error('API Error:', errorText)
      throw new Error(errorText)
    }
    const data = await resp.json()
    console.log('Products loaded:', data.length, 'products')
    console.log('Sample product:', data[0])
    products.value = Array.isArray(data) ? data : []
  }catch(e){
    console.error('loadProducts failed', e);
    toast('Error','Failed to load products','error')
  }
  finally{
    loading.value = false
    console.log('Products in state:', products.value.length)
  }
}

onMounted(() => {
  loadProducts()
  // Load pinned items from localStorage
  try {
    const saved = localStorage.getItem('pinnedItems')
    if (saved) {
      pinnedItems.value = new Set(JSON.parse(saved))
    }
  } catch (e) {
    console.error('Failed to load pinned items', e)
  }
})

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

// ---- Pin/Favorite Functionality
const pinnedItems = ref<Set<number>>(new Set())

function isPinned(id: number): boolean {
  return pinnedItems.value.has(id)
}

function togglePin(id: number) {
  if (pinnedItems.value.has(id)) {
    pinnedItems.value.delete(id)
  } else {
    pinnedItems.value.add(id)
  }
  // Trigger reactivity
  pinnedItems.value = new Set(pinnedItems.value)
  // Save to localStorage
  localStorage.setItem('pinnedItems', JSON.stringify(Array.from(pinnedItems.value)))
}

// ---- Filters & Pagination
const query = ref('')
const currentCategory = ref('All')
const currentPage = ref(1)
const itemsPerPage = 12

const categories = computed(() => {
  const cats = new Set<string>(['All'])
  for (const p of products.value) cats.add(p.category || 'Other')
  return Array.from(cats)
})

const filteredProducts = computed(() => {
  const q = query.value.toLowerCase()
  const filtered = products.value.filter(p =>
    (currentCategory.value === 'All' || p.category === currentCategory.value) &&
    (!q || p.name.toLowerCase().includes(q))
  )

  // Sort: pinned items first, then alphabetically
  return filtered.sort((a, b) => {
    const aPinned = isPinned(a.id)
    const bPinned = isPinned(b.id)

    if (aPinned && !bPinned) return -1
    if (!aPinned && bPinned) return 1
    return a.name.localeCompare(b.name)
  })
})

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filteredProducts.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(filteredProducts.value.length / itemsPerPage)
})

const visiblePages = computed(() => {
  const total = totalPages.value
  const current = currentPage.value
  const pages: number[] = []

  if (total <= 7) {
    // Show all pages if 7 or less
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    // Always show first page
    pages.push(1)

    if (current > 3) {
      pages.push(-1) // Ellipsis
    }

    // Show pages around current
    const start = Math.max(2, current - 1)
    const end = Math.min(total - 1, current + 1)

    for (let i = start; i <= end; i++) {
      pages.push(i)
    }

    if (current < total - 2) {
      pages.push(-1) // Ellipsis
    }

    // Always show last page
    pages.push(total)
  }

  return pages.filter((v, i, arr) => arr.indexOf(v) === i) // Remove duplicates
})

function nextPage() {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

function prevPage() {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

function goToPage(page: number) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}

// Reset to page 1 when category or search changes
watch([currentCategory, query], () => {
  currentPage.value = 1
})

// ---- Variant modal
type Variant = { id:number; name:string; is_default?:boolean; price:number }
const variantModal = ref<{ show:boolean; product:Product|null; variants:Variant[]; loading:boolean }>({ show:false, product:null, variants:[], loading:false })

async function onPickDish(p: Product){
  console.log('Product clicked:', p.name, p.id, 'type:', p.type)

  // Check if product is out of stock
  if (p.is_out_of_stock) {
    console.log('Product is out of stock')
    toast('Out of Stock', `${p.name} is currently out of stock`, 'warning')
    return
  }

  // Only dishes have variants, products (drinks, snacks) don't
  if (p.type !== 'dish') {
    console.log('Product type is not dish, adding directly to cart')
    addToCart({ id:p.id, name:p.name, price:p.price, variant_id:null, variant_name:null })
    return
  }

  // Try to fetch variants for dishes; if none, add base product immediately
  console.log('Opening variant modal for dish:', p.name)
  variantModal.value = { show:true, product:p, variants:[], loading:true }
  try{
    const resp = await fetch(`/api/inventory/dishes/${p.id}/variants`, { headers:{ 'Accept':'application/json' } })
    console.log('Variants API response:', resp.status)
    if (resp.ok) {
      const rows = (await resp.json()) as any[]
      console.log('Variants found:', rows.length)
      const mapped: Variant[] = rows.map(r => ({ id:r.id, name:r.name, is_default: !!r.is_default, price: (r.price_cents ?? 0)/100 }))
      if (mapped.length) {
        variantModal.value.variants = mapped
        variantModal.value.loading = false
        console.log('Showing variant picker')
        return
      }
    }
  }catch(err){
    console.log('No variants available for dish:', p.id, err)
  }
  // no variants -> add base product (use its own price)
  console.log('No variants, adding base dish to cart')
  variantModal.value.loading = false
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

// ---- Receipt
const showReceipt = ref(false)
const receiptData = ref<any>({
  order_no: '',
  date: '',
  time: '',
  cashier: '',
  table: '',
  location: 'Main Location',
  items: [],
  subtotal: 0,
  discount: 0,
  discount_percent: 0,
  tax: 0,
  tax_rate: 0,
  total: 0,
  payment_method: '',
  tendered: 0,
  change: 0
})

function closeReceipt() {
  showReceipt.value = false
}

function printReceipt() {
  const printContent = document.getElementById('receipt-content')
  if (!printContent) return

  const printWindow = window.open('', '_blank')
  if (!printWindow) return

  printWindow.document.write(`
    <html>
      <head>
        <title>Receipt - ${receiptData.value.order_no}</title>
        <style>
          body { font-family: 'Courier New', monospace; max-width: 300px; margin: 0 auto; padding: 20px; }
          .text-center { text-align: center; }
          .flex { display: flex; }
          .justify-between { justify-content: space-between; }
          .items-start { align-items: flex-start; }
          .font-medium { font-weight: 500; }
          .font-semibold { font-weight: 600; }
          .font-bold { font-weight: bold; }
          .text-lg { font-size: 1.125rem; }
          .text-sm { font-size: 0.875rem; }
          .text-xs { font-size: 0.75rem; }
          .space-y-1 > * + * { margin-top: 0.25rem; }
          .space-y-2 > * + * { margin-top: 0.5rem; }
          .mb-2 { margin-bottom: 0.5rem; }
          .mb-3 { margin-bottom: 0.75rem; }
          .mb-4 { margin-bottom: 1rem; }
          .mb-6 { margin-bottom: 1.5rem; }
          .mt-1 { margin-top: 0.25rem; }
          .mt-2 { margin-top: 0.5rem; }
          .mt-4 { margin-top: 1rem; }
          .mt-6 { margin-top: 1.5rem; }
          .ml-2 { margin-left: 0.5rem; }
          .ml-4 { margin-left: 1rem; }
          .pt-2 { padding-top: 0.5rem; }
          .pt-4 { padding-top: 1rem; }
          .pb-4 { padding-bottom: 1rem; }
          .border-t-2 { border-top: 2px dashed #ccc; }
          .border-b-2 { border-bottom: 2px dashed #ccc; }
          .whitespace-nowrap { white-space: nowrap; }
          .flex-1 { flex: 1; }
          .text-gray-500 { color: #6b7280; }
          .text-gray-600 { color: #4b5563; }
          .text-green-600 { color: #16a34a; }
          @media print {
            body { padding: 0; }
          }
        </style>
      </head>
      <body>
        ${printContent.innerHTML}
      </body>
    </html>
  `)

  printWindow.document.close()
  printWindow.focus()

  setTimeout(() => {
    printWindow.print()
    printWindow.close()
  }, 250)
}

async function processPayment(){
  if((tendered.value ?? 0) < total.value) return toast('Insufficient Payment','Please enter a valid amount','error')

  // Map frontend payment methods to database ENUM values
  const methodMap: Record<string, string> = {
    'Cash': 'cash',
    'Card': 'card',
    'Digital': 'wallet'
  }

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
        method: methodMap[paymentMethod.value] || 'cash',
        tendered_cents: Math.round((tendered.value ?? total.value) * 100),
      },
    }

    const resp = await fetch('/api/orders', {
      method: 'POST',
      headers: { 'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' },
      body: JSON.stringify(payload),
    })

    if (!resp.ok) {
      let errorMsg = 'Please try again'
      try {
        const errorData = await resp.json()
        if (errorData.message) errorMsg = errorData.message
        else if (errorData.errors) errorMsg = Object.values(errorData.errors).flat().join(', ')
      } catch {
        const textError = await resp.text().catch(()=> '')
        if (textError) errorMsg = textError.substring(0, 100)
      }
      console.error('Order failed', errorMsg)
      return toast('Order Failed', errorMsg, 'error')
    }

    const data = await resp.json().catch(()=> ({}))

    // Prepare receipt data
    const now = new Date()
    receiptData.value = {
      order_no: data.order_no || '#' + Date.now(),
      date: now.toLocaleDateString(),
      time: now.toLocaleTimeString(),
      cashier: cashier,
      table: tableNumber,
      location: 'Main Location',
      items: cart.value.map(item => ({
        name: item.name,
        variant: item.variant_name,
        qty: item.qty,
        unit_price: item.price * 100, // Convert to cents for formatMoney
        total: item.qty * item.price * 100 // Convert to cents for formatMoney
      })),
      subtotal: subtotal.value * 100, // Convert to cents
      discount: discountAmount.value * 100,
      discount_percent: currentDiscount.value,
      tax: tax.value * 100,
      tax_rate: taxRate,
      total: total.value * 100,
      payment_method: paymentMethod.value,
      tendered: (tendered.value ?? total.value) * 100,
      change: change.value * 100
    }

    // Clear cart and close payment modal
    cart.value = []
    currentDiscount.value = 0
    closePayment()

    // Show receipt
    showReceipt.value = true

    // Reload products to get updated stock levels
    await loadProducts()
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
</script>

<style>
/* keep your existing global styles from app.blade.php */
</style>
