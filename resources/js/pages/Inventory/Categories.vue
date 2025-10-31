<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <nav class="w-20 hover:w-64 transition-all duration-300 ease-in-out bg-white border-r border-gray-200 flex flex-col group"
         @mouseenter="sidebarOpen = true"
         @mouseleave="sidebarOpen = false">
      <div class="px-3 py-4 flex items-center gap-3">
        <i class="fas fa-fire text-2xl text-orange-600"></i>
        <span v-if="sidebarOpen" class="font-bold text-xl text-gray-800 whitespace-nowrap">Grillstone</span>
      </div>

      <div class="flex-1 px-3 py-4 overflow-y-auto">
        <ul class="space-y-2">
          <li>
            <a href="/pos" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
              <i class="fas fa-cash-register text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">POS</span>
            </a>
          </li>
          <li>
            <a href="/inventory" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors bg-orange-50 text-orange-700">
              <i class="fas fa-boxes text-lg text-orange-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Inventory</span>
            </a>
          </li>
          <li>
            <a href="/inventory/dishes" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
              <i class="fas fa-utensils text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Dishes</span>
            </a>
          </li>
          <li>
            <a href="/inventory/combos" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
              <i class="fas fa-layer-group text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Combos</span>
            </a>
          </li>
          <li>
            <a href="/inventory/categories" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors bg-orange-100 text-orange-800">
              <i class="fas fa-tags text-lg text-orange-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Categories</span>
            </a>
          </li>
          <li>
            <a href="/inventory/grn" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
              <i class="fas fa-clipboard-check text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Receive Stock</span>
            </a>
          </li>
          <li>
            <a href="/inventory/stocktake" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
              <i class="fas fa-clipboard-list text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Stocktake</span>
            </a>
          </li>
          <li>
            <a href="/reports" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
              <i class="fas fa-chart-line text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Reports</span>
            </a>
          </li>
        </ul>
      </div>

      <div class="mt-auto px-3 py-3 text-xs text-gray-500">
        <div v-if="sidebarOpen">v0.1 • Grillstone</div>
        <div v-else class="text-center">v0.1</div>
      </div>
    </nav>

    <!-- Main Content -->
    <section class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-4xl mx-auto">
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Categories</h1>
          <p class="text-gray-600 mt-1">Manage product and dish categories</p>
        </div>

        <div class="glass-effect rounded-2xl p-6 shadow-lg">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-800">All Categories</h2>
            <button @click="showAddModal = true"
                    class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium">
              <i class="fas fa-plus mr-2"></i>Add Category
            </button>
          </div>

          <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>

          <div v-else-if="!categories.length" class="text-center py-8 text-gray-400">
            <i class="fas fa-tags text-4xl mb-3"></i>
            <p>No categories yet. Add one to get started.</p>
          </div>

          <div v-else class="space-y-2">
            <div v-for="category in categories" :key="category.id"
                 class="flex items-center justify-between p-4 bg-white rounded-lg border border-gray-200 hover:border-orange-300 transition-colors">
              <div class="flex items-center gap-3">
                <i class="fas fa-tag text-orange-600"></i>
                <span class="font-medium text-gray-800">{{ category.name }}</span>
              </div>
              <div class="flex gap-2">
                <button @click="editCategory(category)" class="text-blue-600 hover:text-blue-800" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button @click="confirmDelete(category.id)" class="text-red-600 hover:text-red-800" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal || editingCategory" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeModal">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">{{ editingCategory ? 'Edit' : 'Add' }} Category</h3>

        <form @submit.prevent="saveCategory" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category Name *</label>
            <input v-model="categoryForm.name" type="text" required
                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                   placeholder="e.g., Beverages, Snacks, Grilled">
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <button type="button" @click="closeModal"
                    class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit"
                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
              {{ editingCategory ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showDeleteConfirm = false">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Confirm Delete</h3>
        <p class="text-gray-700 mb-6">Are you sure you want to delete this category? This action cannot be undone.</p>
        <div class="flex justify-end gap-2">
          <button @click="showDeleteConfirm = false" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
          <button @click="deleteCategory" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
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
import { ref, onMounted } from 'vue'

const sidebarOpen = ref(false)
const loading = ref(false)
const categories = ref<any[]>([])
const showAddModal = ref(false)
const editingCategory = ref<any | null>(null)
const categoryForm = ref({ name: '' })
const showDeleteConfirm = ref(false)
const deleteTarget = ref<number | null>(null)

async function loadCategories() {
  loading.value = true
  try {
    const resp = await fetch('/api/categories', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    if (!resp.ok) throw new Error('Failed to load categories')
    categories.value = await resp.json()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load categories', 'error')
  } finally {
    loading.value = false
  }
}

function editCategory(category: any) {
  editingCategory.value = category
  categoryForm.value = { name: category.name }
  showAddModal.value = true
}

async function saveCategory() {
  try {
    const payload = { name: categoryForm.value.name }

    if (editingCategory.value) {
      const resp = await fetch(`/api/categories/${editingCategory.value.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
      })
      if (!resp.ok) {
        const error = await resp.json().catch(() => ({}))
        throw new Error(error.message || 'Failed to update category')
      }
      toast('Success', 'Category updated successfully', 'success')
    } else {
      const resp = await fetch('/api/categories', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
      })
      if (!resp.ok) {
        const error = await resp.json().catch(() => ({}))
        throw new Error(error.message || 'Failed to create category')
      }
      toast('Success', 'Category created successfully', 'success')
    }

    closeModal()
    await loadCategories()
  } catch (e: any) {
    console.error(e)
    toast('Error', e.message || 'Failed to save category', 'error')
  }
}

function confirmDelete(id: number) {
  deleteTarget.value = id
  showDeleteConfirm.value = true
}

async function deleteCategory() {
  if (!deleteTarget.value) return

  try {
    const resp = await fetch(`/api/categories/${deleteTarget.value}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    if (!resp.ok) {
      const error = await resp.json().catch(() => ({}))
      throw new Error(error.error || 'Failed to delete category')
    }
    toast('Success', 'Category deleted', 'success')
    await loadCategories()
  } catch (e: any) {
    console.error(e)
    toast('Error', e.message || 'Failed to delete category', 'error')
  } finally {
    showDeleteConfirm.value = false
    deleteTarget.value = null
  }
}

function closeModal() {
  showAddModal.value = false
  editingCategory.value = null
  categoryForm.value = { name: '' }
}

// Toast
const toastShow = ref(false)
const toastTitle = ref('')
const toastMsg = ref('')
const toastIcon = ref('fas fa-check')
const toastBg = ref('bg-green-500')

function toast(title: string, msg: string, type: 'success' | 'error' | 'warning' = 'success') {
  toastTitle.value = title
  toastMsg.value = msg
  toastIcon.value = type === 'success' ? 'fas fa-check' : type === 'error' ? 'fas fa-times' : 'fas fa-exclamation'
  toastBg.value = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-yellow-500'
  toastShow.value = true
  setTimeout(() => toastShow.value = false, 3000)
}

onMounted(loadCategories)
</script>

<style scoped>
.glass-effect {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
}

.notification {
  position: fixed;
  top: 20px;
  right: 20px;
  opacity: 0;
  transform: translateX(400px);
  transition: all 0.3s ease;
  z-index: 9999;
}

.notification.show {
  opacity: 1;
  transform: translateX(0);
}
</style>
