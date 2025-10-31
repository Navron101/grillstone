<template>
  <div class="min-h-screen gradient-bg">
    <!-- Header -->
    <header class="glass-effect shadow-lg">
      <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-10 h-10 bg-gradient-to-r from-orange-600 to-red-700 rounded-lg flex items-center justify-center">
            <i class="fas fa-users text-white text-lg"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-800">User Administration</h1>
            <p class="text-sm text-gray-600">Manage users and role permissions</p>
          </div>
        </div>

        <div class="flex items-center space-x-3">
          <button @click="loadUsers" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg" title="Refresh">
            <i class="fas fa-refresh text-lg"></i>
          </button>
        </div>
      </div>
    </header>

    <div class="flex h-[calc(100vh-80px)]">
      <!-- Left nav -->
      <nav class="glass-effect m-4 rounded-2xl shadow-2xl w-20 flex flex-col">
        <div class="flex items-center justify-center px-3 py-3">
          <div class="w-9 h-9 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-fire text-white text-base"></i>
          </div>
        </div>

        <div class="px-2">
          <ul class="mt-1 space-y-1">
            <li>
              <a href="/pos" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-cash-register text-lg text-gray-600"></i>
              </a>
            </li>
            <li>
              <a href="/inventory" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-boxes-stacked text-lg text-gray-600"></i>
              </a>
            </li>
            <li>
              <a href="/reports" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
                <i class="fas fa-chart-line text-lg text-gray-600"></i>
              </a>
            </li>
            <li>
              <a href="/settings" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors bg-orange-600 text-white">
                <i class="fas fa-cog text-lg text-white"></i>
              </a>
            </li>
          </ul>
        </div>
      </nav>

      <!-- Main Content -->
      <section class="flex-1 p-4 flex flex-col overflow-y-auto">
        <!-- Tab Navigation -->
        <div class="glass-effect rounded-2xl p-2 mb-4 shadow-lg flex gap-2">
          <button
            @click="activeTab = 'users'"
            :class="activeTab === 'users' ? 'bg-gradient-to-r from-orange-600 to-red-700 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
            class="flex-1 px-6 py-3 rounded-xl font-semibold transition-colors flex items-center justify-center gap-2"
          >
            <i class="fas fa-users"></i>
            <span>Users</span>
          </button>
          <button
            @click="activeTab = 'permissions'"
            :class="activeTab === 'permissions' ? 'bg-gradient-to-r from-orange-600 to-red-700 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
            class="flex-1 px-6 py-3 rounded-xl font-semibold transition-colors flex items-center justify-center gap-2"
          >
            <i class="fas fa-shield-halved"></i>
            <span>Role Permissions</span>
          </button>
        </div>

        <!-- Users Tab -->
        <div v-show="activeTab === 'users'" class="flex-1 flex flex-col">
          <!-- Users Header -->
          <div class="glass-effect rounded-2xl p-4 mb-4 shadow-lg flex items-center justify-between">
            <div class="flex items-center gap-2">
              <i class="fas fa-users text-orange-600"></i>
              <span class="text-sm font-semibold text-gray-700">Total Users: {{ users.length }}</span>
            </div>
            <button @click="openCreateModal" class="px-6 py-2 bg-gradient-to-r from-orange-600 to-red-700 hover:from-orange-700 hover:to-red-800 text-white rounded-lg font-medium transition-colors">
              <i class="fas fa-plus mr-2"></i>New User
            </button>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="glass-effect rounded-2xl p-12 shadow-lg text-center">
            <i class="fas fa-spinner fa-spin text-4xl text-orange-600"></i>
            <p class="mt-4 text-gray-600">Loading users...</p>
          </div>

          <!-- Empty State -->
          <div v-else-if="users.length === 0" class="glass-effect rounded-2xl p-12 shadow-lg text-center">
            <i class="fas fa-users text-6xl text-gray-300"></i>
            <p class="mt-4 text-xl font-medium text-gray-600">No users yet</p>
            <p class="mt-2 text-gray-500">Click "New User" to add one</p>
          </div>

          <!-- Users Table -->
          <div v-else class="glass-effect rounded-2xl shadow-lg overflow-hidden">
            <table class="min-w-full">
              <thead class="bg-gradient-to-r from-orange-100 to-red-100">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Name</th>
                  <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Username</th>
                  <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Role</th>
                  <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="user in users" :key="user.id" class="hover:bg-white hover:bg-opacity-50 transition-colors">
                  <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ user.name }}</td>
                  <td class="px-6 py-4 text-sm text-gray-700">{{ user.username || '-' }}</td>
                  <td class="px-6 py-4 text-sm text-gray-700">{{ user.email }}</td>
                  <td class="px-6 py-4 text-sm">
                    <span class="px-3 py-1 bg-gradient-to-r from-blue-100 to-indigo-100 text-blue-800 rounded-full text-xs font-semibold">
                      {{ user.role?.display_name || user.role?.name || 'No Role' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <button @click="editUser(user)" class="px-3 py-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg text-xs font-medium mr-2 transition-colors">
                      <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    <button @click="deleteUser(user.id)" class="px-3 py-1 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-lg text-xs font-medium transition-colors">
                      <i class="fas fa-trash mr-1"></i>Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Role Permissions Tab -->
        <div v-show="activeTab === 'permissions'" class="flex-1 flex flex-col">
          <!-- Permissions Header -->
          <div class="glass-effect rounded-2xl p-4 mb-4 shadow-lg flex items-center justify-between">
            <div class="flex items-center gap-2">
              <i class="fas fa-shield-halved text-orange-600"></i>
              <span class="text-sm font-semibold text-gray-700">Configure role-based permissions</span>
            </div>
            <button @click="savePermissions" :disabled="savingPermissions" class="px-6 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg font-medium transition-colors disabled:opacity-50">
              <i class="fas fa-save mr-2"></i>{{ savingPermissions ? 'Saving...' : 'Save Permissions' }}
            </button>
          </div>

          <!-- Loading State -->
          <div v-if="loadingPermissions" class="glass-effect rounded-2xl p-12 shadow-lg text-center">
            <i class="fas fa-spinner fa-spin text-4xl text-orange-600"></i>
            <p class="mt-4 text-gray-600">Loading permissions...</p>
          </div>

          <!-- Permissions Matrix -->
          <div v-else class="glass-effect rounded-2xl shadow-lg overflow-auto">
            <table class="min-w-full">
              <thead class="bg-gradient-to-r from-orange-100 to-red-100 sticky top-0">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider sticky left-0 bg-gradient-to-r from-orange-100 to-red-100 z-10">Module / Permission</th>
                  <th v-for="role in roles" :key="role.id" class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                    <div class="flex flex-col items-center gap-1">
                      <i :class="getRoleIcon(role.name)" class="text-lg"></i>
                      <span>{{ role.name }}</span>
                    </div>
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <template v-for="(perms, module) in permissionsByModule" :key="module">
                  <!-- Module Header -->
                  <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <td colspan="100" class="px-6 py-3 font-bold text-gray-800 uppercase text-sm">
                      <i :class="getModuleIcon(module)" class="mr-2 text-orange-600"></i>
                      {{ module }}
                    </td>
                  </tr>
                  <!-- Permission Rows -->
                  <tr v-for="permission in perms" :key="permission" class="hover:bg-white hover:bg-opacity-50 transition-colors">
                    <td class="px-6 py-4 text-sm text-gray-900 font-medium sticky left-0 bg-white bg-opacity-95">
                      {{ formatPermissionName(permission) }}
                    </td>
                    <td v-for="role in roles" :key="`${role.id}-${permission}`" class="px-6 py-4 text-center">
                      <input
                        type="checkbox"
                        :checked="roleHasPermission(role.id, permission)"
                        @change="togglePermission(role.id, permission)"
                        class="w-5 h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 cursor-pointer"
                      />
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>

    <!-- Create/Edit User Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-[60] p-4" @click.self="closeModal">
      <div class="glass-effect rounded-2xl shadow-2xl w-full max-w-2xl">
        <div class="bg-gradient-to-r from-orange-600 to-red-700 text-white px-6 py-4 rounded-t-2xl">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <i class="fas fa-user text-2xl"></i>
              <h2 class="text-xl font-bold">{{ editingUser ? 'Edit' : 'Create' }} User</h2>
            </div>
            <button @click="closeModal" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2">
              <i class="fas fa-times text-lg"></i>
            </button>
          </div>
        </div>

        <form @submit.prevent="saveUser" class="p-6">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
              <input
                v-model="userForm.name"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                placeholder="Enter full name"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Username *</label>
              <input
                v-model="userForm.username"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                placeholder="Enter username"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
              <input
                v-model="userForm.email"
                type="email"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                placeholder="email@example.com"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Password {{ editingUser ? '' : '*' }}
              </label>
              <input
                v-model="userForm.password"
                type="password"
                :required="!editingUser"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                :placeholder="editingUser ? 'Leave blank to keep current password' : 'Enter password'"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Role *</label>
              <select
                v-model="userForm.role_id"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
              >
                <option :value="null">Select a role</option>
                <option v-for="role in roles" :key="role.id" :value="role.id">
                  {{ role.display_name || role.name }}
                </option>
              </select>
            </div>
          </div>

          <div class="flex justify-end gap-3 mt-6">
            <button type="button" @click="closeModal" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition-colors">
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-6 py-2 bg-gradient-to-r from-orange-600 to-red-700 hover:from-orange-700 hover:to-red-800 text-white rounded-lg font-medium disabled:opacity-50 transition-colors"
            >
              {{ saving ? 'Saving...' : 'Save User' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Toast Notification -->
    <div class="notification glass-effect rounded-lg shadow-2xl p-4 w-80" :class="{ show: toastShow }">
      <div class="flex items-center">
        <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3" :class="toastBg">
          <i :class="toastIcon" class="text-white"></i>
        </div>
        <div class="flex-1">
          <p class="font-semibold text-gray-800">{{ toastTitle }}</p>
          <p class="text-sm text-gray-600">{{ toastMsg }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'

// Tab state
const activeTab = ref<'users' | 'permissions'>('users')

// Users state
const users = ref<any[]>([])
const loading = ref(false)
const showModal = ref(false)
const editingUser = ref<any>(null)
const saving = ref(false)

const userForm = ref({
  name: '',
  username: '',
  email: '',
  password: '',
  role_id: null as number | null
})

// Permissions state
const roles = ref<any[]>([])
const permissions = ref<string[]>([])
const rolePermissions = ref<Record<number, string[]>>({})
const permissionIdMap = ref<Record<string, number>>({}) // Map permission name to ID
const loadingPermissions = ref(false)
const savingPermissions = ref(false)

// Toast state
const toastShow = ref(false)
const toastTitle = ref('')
const toastMsg = ref('')
const toastIcon = ref('fas fa-check')
const toastBg = ref('bg-green-500')

// Computed
const permissionsByModule = computed(() => {
  const modules: Record<string, string[]> = {
    'POS': [],
    'Inventory': [],
    'HR': [],
    'Finance': [],
    'Reports': [],
    'Settings': [],
    'Loyalty': []
  }

  permissions.value.forEach(permission => {
    // Determine module from permission name
    if (permission.includes('pos') || permission.includes('order')) {
      modules['POS'].push(permission)
    } else if (permission.includes('inventory') || permission.includes('stock') || permission.includes('product')) {
      modules['Inventory'].push(permission)
    } else if (permission.includes('hr') || permission.includes('employee') || permission.includes('contract')) {
      modules['HR'].push(permission)
    } else if (permission.includes('finance') || permission.includes('expense') || permission.includes('invoice')) {
      modules['Finance'].push(permission)
    } else if (permission.includes('report')) {
      modules['Reports'].push(permission)
    } else if (permission.includes('loyalty')) {
      modules['Loyalty'].push(permission)
    } else if (permission.includes('settings') || permission.includes('user') || permission.includes('role')) {
      modules['Settings'].push(permission)
    } else {
      // Default to Settings for unknown permissions
      modules['Settings'].push(permission)
    }
  })

  // Remove empty modules
  Object.keys(modules).forEach(key => {
    if (modules[key].length === 0) {
      delete modules[key]
    }
  })

  return modules
})

// User Management Functions
async function loadUsers() {
  loading.value = true
  try {
    const resp = await fetch('/api/users')
    if (!resp.ok) throw new Error('Failed to load users')
    const data = await resp.json()
    users.value = data.users || data || []
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load users', 'error')
  } finally {
    loading.value = false
  }
}

function openCreateModal() {
  editingUser.value = null
  userForm.value = {
    name: '',
    username: '',
    email: '',
    password: '',
    role_id: null
  }
  showModal.value = true
}

function editUser(user: any) {
  editingUser.value = user
  userForm.value = {
    name: user.name,
    username: user.username || '',
    email: user.email,
    password: '',
    role_id: user.role_id || null
  }
  showModal.value = true
}

async function saveUser() {
  if (!userForm.value.name.trim() || !userForm.value.email.trim()) {
    toast('Error', 'Name and email are required', 'error')
    return
  }

  if (!userForm.value.role_id) {
    toast('Error', 'Please select a role', 'error')
    return
  }

  saving.value = true
  try {
    const url = editingUser.value
      ? `/api/users/${editingUser.value.id}`
      : '/api/users'

    const method = editingUser.value ? 'PUT' : 'POST'

    const payload: any = {
      name: userForm.value.name,
      username: userForm.value.username,
      email: userForm.value.email,
      role_id: userForm.value.role_id
    }

    // Only include password if provided
    if (userForm.value.password) {
      payload.password = userForm.value.password
    }

    const resp = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    })

    if (!resp.ok) {
      const data = await resp.json()
      throw new Error(data.message || 'Failed to save')
    }

    toast('Success', 'User saved successfully', 'success')
    closeModal()
    loadUsers()
  } catch (e: any) {
    console.error(e)
    toast('Error', e.message || 'Failed to save user', 'error')
  } finally {
    saving.value = false
  }
}

async function deleteUser(id: number) {
  if (!confirm('Are you sure you want to delete this user?')) return

  try {
    const resp = await fetch(`/api/users/${id}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json'
      }
    })

    if (!resp.ok) {
      const data = await resp.json()
      throw new Error(data.message || 'Failed to delete')
    }

    toast('Success', 'User deleted successfully', 'success')
    loadUsers()
  } catch (e: any) {
    console.error(e)
    toast('Error', e.message || 'Failed to delete user', 'error')
  }
}

function closeModal() {
  showModal.value = false
  editingUser.value = null
}

// Permissions Management Functions
async function loadRolesAndPermissions() {
  loadingPermissions.value = true
  try {
    const resp = await fetch('/api/roles')
    if (!resp.ok) throw new Error('Failed to load roles')
    const data = await resp.json()

    roles.value = data.roles || []

    // Extract all unique permissions and build ID map
    const allPermissions = new Set<string>()
    const idMap: Record<string, number> = {}

    roles.value.forEach(role => {
      if (role.permissions && Array.isArray(role.permissions)) {
        role.permissions.forEach((perm: any) => {
          // Extract permission name from permission object
          const permName = typeof perm === 'string' ? perm : perm.name
          const permId = typeof perm === 'string' ? null : perm.id

          allPermissions.add(permName)
          if (permId) {
            idMap[permName] = permId
          }
        })
      }
    })
    permissions.value = Array.from(allPermissions).sort()
    permissionIdMap.value = idMap

    // Build role-permission mapping (store permission names, not objects)
    const mapping: Record<number, string[]> = {}
    roles.value.forEach(role => {
      mapping[role.id] = role.permissions && Array.isArray(role.permissions)
        ? role.permissions.map((perm: any) => typeof perm === 'string' ? perm : perm.name)
        : []
    })
    rolePermissions.value = mapping
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load roles and permissions', 'error')
  } finally {
    loadingPermissions.value = false
  }
}

function roleHasPermission(roleId: number, permission: string): boolean {
  return rolePermissions.value[roleId]?.includes(permission) || false
}

function togglePermission(roleId: number, permission: string) {
  if (!rolePermissions.value[roleId]) {
    rolePermissions.value[roleId] = []
  }

  const index = rolePermissions.value[roleId].indexOf(permission)
  if (index > -1) {
    rolePermissions.value[roleId].splice(index, 1)
  } else {
    rolePermissions.value[roleId].push(permission)
  }
}

async function savePermissions() {
  savingPermissions.value = true
  try {
    // Save permissions for each role
    const promises = roles.value.map(role => {
      // Convert permission names to IDs
      const permissionNames = rolePermissions.value[role.id] || []
      const permissionIds = permissionNames
        .map(name => permissionIdMap.value[name])
        .filter(id => id !== undefined && id !== null)

      return fetch(`/api/roles/${role.id}/permissions`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          permission_ids: permissionIds
        })
      })
    })

    const results = await Promise.all(promises)

    if (results.some(r => !r.ok)) {
      throw new Error('Failed to save some permissions')
    }

    toast('Success', 'Permissions saved successfully', 'success')
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to save permissions', 'error')
  } finally {
    savingPermissions.value = false
  }
}

// Helper Functions
function formatPermissionName(permission: string): string {
  // Convert snake_case or kebab-case to Title Case
  return permission
    .replace(/[_-]/g, ' ')
    .split(' ')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
}

function getRoleIcon(roleName: string): string {
  const name = roleName.toLowerCase()
  if (name.includes('admin')) return 'fas fa-user-shield'
  if (name.includes('manager')) return 'fas fa-user-tie'
  if (name.includes('cashier')) return 'fas fa-cash-register'
  if (name.includes('chef')) return 'fas fa-hat-chef'
  return 'fas fa-user'
}

function getModuleIcon(module: string): string {
  const icons: Record<string, string> = {
    'POS': 'fas fa-cash-register',
    'Inventory': 'fas fa-boxes-stacked',
    'HR': 'fas fa-users',
    'Finance': 'fas fa-dollar-sign',
    'Reports': 'fas fa-chart-line',
    'Settings': 'fas fa-cog',
    'Loyalty': 'fas fa-heart'
  }
  return icons[module] || 'fas fa-folder'
}

function toast(title: string, msg: string, type: 'success' | 'error' | 'warning' = 'success') {
  toastTitle.value = title
  toastMsg.value = msg
  toastIcon.value = type === 'success' ? 'fas fa-check' : type === 'error' ? 'fas fa-times' : 'fas fa-exclamation'
  toastBg.value = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-yellow-500'
  toastShow.value = true
  setTimeout(() => toastShow.value = false, 3000)
}

// Lifecycle
onMounted(() => {
  loadUsers()
  loadRolesAndPermissions()
})
</script>

<style scoped>
.gradient-bg {
  background: linear-gradient(135deg, #ea580c 0%, #dc2626 100%);
  background-attachment: fixed;
}

.glass-effect {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
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
