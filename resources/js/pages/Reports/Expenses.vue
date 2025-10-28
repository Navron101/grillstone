<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <a href="/reports" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
            <i class="fas fa-arrow-left text-lg"></i>
          </a>
          <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-money-bill-wave text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-900">Expenses</h1>
            <p class="text-sm text-gray-600">Track and manage business expenses</p>
          </div>
        </div>
        <button
          @click="openCreateModal"
          class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-medium"
        >
          <i class="fas fa-plus mr-2"></i>Record Expense
        </button>
      </div>
    </header>

    <div class="max-w-7xl mx-auto p-6">
      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <select v-model="filters.category_id" @change="loadExpenses" class="w-full px-4 py-2 border rounded-lg">
              <option value="">All Categories</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
            <input v-model="filters.from_date" @change="loadExpenses" type="date" class="w-full px-4 py-2 border rounded-lg" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
            <input v-model="filters.to_date" @change="loadExpenses" type="date" class="w-full px-4 py-2 border rounded-lg" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input v-model="filters.search" @input="loadExpenses" placeholder="Search..." class="w-full px-4 py-2 border rounded-lg" />
          </div>
        </div>
      </div>

      <!-- Expenses Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Method</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="expenses.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                No expenses found. Click "Record Expense" to add one.
              </td>
            </tr>
            <tr v-for="expense in expenses" :key="expense.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatDate(expense.expense_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">{{ expense.category?.name }}</td>
              <td class="px-6 py-4 text-sm">{{ expense.description || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ expense.reference_number || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                JMD {{ (expense.amount_cents / 100).toFixed(2) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ expense.payment_method || '-' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <button @click="editExpense(expense)" class="text-purple-600 hover:text-purple-900 mr-3">
                  Edit
                </button>
                <button @click="deleteExpense(expense.id)" class="text-red-600 hover:text-red-900">
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold">{{ editingExpense ? 'Edit' : 'Record' }} Expense</h2>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>

          <form @submit.prevent="saveExpense">
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                <select v-model.number="form.expense_category_id" required class="w-full px-4 py-2 border rounded-lg">
                  <option value="">Select category...</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                <input v-model="form.expense_date" type="date" required class="w-full px-4 py-2 border rounded-lg" />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Amount *</label>
                <input v-model.number="form.amount" type="number" step="0.01" required class="w-full px-4 py-2 border rounded-lg" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                <select v-model="form.payment_method" class="w-full px-4 py-2 border rounded-lg">
                  <option value="">Select...</option>
                  <option value="cash">Cash</option>
                  <option value="card">Card</option>
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="cheque">Cheque</option>
                </select>
              </div>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
              <input v-model="form.reference_number" class="w-full px-4 py-2 border rounded-lg" />
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
              <textarea v-model="form.description" rows="2" class="w-full px-4 py-2 border rounded-lg"></textarea>
            </div>

            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
              <textarea v-model="form.notes" rows="2" class="w-full px-4 py-2 border rounded-lg"></textarea>
            </div>

            <div class="flex justify-end gap-3">
              <button type="button" @click="closeModal" class="px-6 py-2 border rounded-lg">Cancel</button>
              <button
                type="submit"
                :disabled="saving"
                class="bg-purple-600 text-white px-6 py-2 rounded-lg disabled:opacity-50"
              >
                {{ saving ? 'Saving...' : 'Save Expense' }}
              </button>
            </div>
          </form>
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

const expenses = ref([])
const categories = ref([])
const showModal = ref(false)
const editingExpense = ref(null)
const saving = ref(false)

const filters = ref({
  category_id: '',
  from_date: '',
  to_date: '',
  search: ''
})

const form = ref({
  expense_category_id: '',
  expense_date: new Date().toISOString().split('T')[0],
  reference_number: '',
  amount: 0,
  description: '',
  notes: '',
  payment_method: ''
})

async function loadExpenses() {
  try {
    const params = new URLSearchParams()
    if (filters.value.category_id) params.append('category_id', filters.value.category_id)
    if (filters.value.from_date) params.append('from_date', filters.value.from_date)
    if (filters.value.to_date) params.append('to_date', filters.value.to_date)
    if (filters.value.search) params.append('search', filters.value.search)

    const resp = await fetch(`/api/expenses?${params}`)
    const data = await resp.json()
    expenses.value = data.expenses || []
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load expenses', 'error')
  }
}

async function loadCategories() {
  try {
    const resp = await fetch('/api/expense-categories')
    const data = await resp.json()
    categories.value = data.categories || []
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load categories', 'error')
  }
}

function openCreateModal() {
  editingExpense.value = null
  form.value = {
    expense_category_id: '',
    expense_date: new Date().toISOString().split('T')[0],
    reference_number: '',
    amount: 0,
    description: '',
    notes: '',
    payment_method: ''
  }
  showModal.value = true
}

function editExpense(expense) {
  editingExpense.value = expense
  form.value = {
    expense_category_id: expense.expense_category_id,
    expense_date: expense.expense_date?.split('T')[0] || '',
    reference_number: expense.reference_number || '',
    amount: expense.amount_cents / 100,
    description: expense.description || '',
    notes: expense.notes || '',
    payment_method: expense.payment_method || ''
  }
  showModal.value = true
}

async function saveExpense() {
  if (!form.value.expense_category_id || !form.value.amount) {
    toast('Error', 'Please fill in required fields', 'error')
    return
  }

  saving.value = true
  try {
    const url = editingExpense.value
      ? `/api/expenses/${editingExpense.value.id}`
      : '/api/expenses'

    const method = editingExpense.value ? 'PUT' : 'POST'

    const resp = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form.value)
    })

    if (!resp.ok) throw new Error('Failed to save')

    toast('Success', 'Expense saved successfully', 'success')
    closeModal()
    loadExpenses()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to save expense', 'error')
  } finally {
    saving.value = false
  }
}

async function deleteExpense(id) {
  if (!confirm('Delete this expense?')) return

  try {
    const resp = await fetch(`/api/expenses/${id}`, { method: 'DELETE' })
    if (!resp.ok) throw new Error('Failed')

    toast('Success', 'Expense deleted', 'success')
    loadExpenses()
  } catch (e) {
    toast('Error', 'Failed to delete', 'error')
  }
}

function closeModal() {
  showModal.value = false
  editingExpense.value = null
}

function formatDate(date) {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
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

onMounted(() => {
  loadExpenses()
  loadCategories()
})
</script>

<style scoped>
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
