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
            <a href="/inventory" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
              <i class="fas fa-boxes text-lg text-gray-600"></i>
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
            <a href="/inventory/categories" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors text-gray-700 hover:bg-orange-50 hover:text-orange-700">
              <i class="fas fa-tags text-lg text-gray-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Categories</span>
            </a>
          </li>
          <li>
            <a href="/hr/employees" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors bg-orange-100 text-orange-800">
              <i class="fas fa-users text-lg text-orange-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Employees</span>
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
      <div class="max-w-7xl mx-auto">
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Employee Management</h1>
          <p class="text-gray-600 mt-1">Manage employee records, departments, and employment details</p>
        </div>

        <!-- Search and Actions Bar -->
        <div class="glass-effect rounded-2xl p-4 mb-6 shadow-lg">
          <div class="flex items-center justify-between gap-4">
            <div class="flex-1 max-w-md">
              <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search by name, email, employee number..."
                  class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                >
              </div>
            </div>
            <div class="flex gap-2">
              <select v-model="filterStatus" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="on-leave">On Leave</option>
                <option value="terminated">Terminated</option>
                <option value="suspended">Suspended</option>
              </select>
              <select v-model="filterDepartment" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
                <option value="">All Departments</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
              </select>
              <button @click="openAddModal"
                      class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium">
                <i class="fas fa-plus mr-2"></i>Add Employee
              </button>
            </div>
          </div>
        </div>

        <!-- Employees Table -->
        <div class="glass-effect rounded-2xl p-6 shadow-lg">
          <div v-if="loading" class="text-center py-8 text-gray-500">
            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
            <p>Loading employees...</p>
          </div>

          <div v-else-if="!filteredEmployees.length" class="text-center py-8 text-gray-400">
            <i class="fas fa-users text-4xl mb-3"></i>
            <p v-if="searchQuery || filterStatus || filterDepartment">No employees found matching your filters.</p>
            <p v-else>No employees yet. Add one to get started.</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full">
              <thead>
                <tr class="text-left text-gray-600 border-b">
                  <th class="py-3 px-4">Employee #</th>
                  <th class="py-3 px-4">Name</th>
                  <th class="py-3 px-4">Email</th>
                  <th class="py-3 px-4">Position</th>
                  <th class="py-3 px-4">Department</th>
                  <th class="py-3 px-4">Hire Date</th>
                  <th class="py-3 px-4">Status</th>
                  <th class="py-3 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="employee in filteredEmployees" :key="employee.id"
                    class="border-b hover:bg-gray-50 transition-colors">
                  <td class="py-3 px-4 font-mono text-sm text-gray-600">{{ employee.employee_number }}</td>
                  <td class="py-3 px-4 font-medium text-gray-900">{{ employee.first_name }} {{ employee.last_name }}</td>
                  <td class="py-3 px-4 text-gray-600">{{ employee.email }}</td>
                  <td class="py-3 px-4 text-gray-600">{{ employee.position }}</td>
                  <td class="py-3 px-4 text-gray-600">{{ getDepartmentName(employee.department_id) }}</td>
                  <td class="py-3 px-4 text-gray-600">{{ formatDate(employee.hire_date) }}</td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-1 text-xs rounded-full font-medium" :class="getStatusClass(employee.employment_status)">
                      {{ employee.employment_status }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right space-x-2">
                    <button @click="viewEmployee(employee)" class="text-blue-600 hover:text-blue-800" title="View">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button @click="editEmployee(employee)" class="text-orange-600 hover:text-orange-800" title="Edit">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button @click="confirmDelete(employee.id)" class="text-red-600 hover:text-red-800" title="Delete">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <!-- Add/Edit Employee Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeModal">
      <div class="bg-white rounded-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
          <h3 class="text-lg font-bold">{{ editingEmployee ? 'Edit' : 'Add' }} Employee</h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <form @submit.prevent="saveEmployee" class="p-6 space-y-6">
          <!-- Personal Information -->
          <div class="bg-orange-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
              <i class="fas fa-user mr-2 text-orange-600"></i>
              Personal Information
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                <input v-model="employeeForm.first_name" type="text" required
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                <input v-model="employeeForm.last_name" type="text" required
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                <input v-model="employeeForm.email" type="email" required
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input v-model="employeeForm.phone" type="text"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">TRN (Tax Registration Number)</label>
                <input v-model="employeeForm.trn" type="text"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NIS (National Insurance Scheme)</label>
                <input v-model="employeeForm.nis" type="text"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
              </div>
            </div>
          </div>

          <!-- Address Information -->
          <div class="bg-blue-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
              <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>
              Address Information
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea v-model="employeeForm.address" rows="2"
                          class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <input v-model="employeeForm.city" type="text"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Parish</label>
                <select v-model="employeeForm.parish"
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
                  <option value="">Select Parish</option>
                  <option>Kingston</option>
                  <option>St. Andrew</option>
                  <option>St. Thomas</option>
                  <option>Portland</option>
                  <option>St. Mary</option>
                  <option>St. Ann</option>
                  <option>Trelawny</option>
                  <option>St. James</option>
                  <option>Hanover</option>
                  <option>Westmoreland</option>
                  <option>St. Elizabeth</option>
                  <option>Manchester</option>
                  <option>Clarendon</option>
                  <option>St. Catherine</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Employment Details -->
          <div class="bg-green-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
              <i class="fas fa-briefcase mr-2 text-green-600"></i>
              Employment Details
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Employee Number *</label>
                <input v-model="employeeForm.employee_number" type="text" required
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                       placeholder="e.g., EMP001">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Position *</label>
                <input v-model="employeeForm.position" type="text" required
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                       placeholder="e.g., Chef, Cashier, Manager">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                <select v-model="employeeForm.department_id"
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
                  <option :value="null">Select Department</option>
                  <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date *</label>
                <input v-model="employeeForm.hire_date" type="date" required
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Employment Type *</label>
                <select v-model="employeeForm.employment_type" required
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
                  <option value="full-time">Full-time</option>
                  <option value="part-time">Part-time</option>
                  <option value="contract">Contract</option>
                  <option value="casual">Casual</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Employment Status *</label>
                <select v-model="employeeForm.employment_status" required
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
                  <option value="active">Active</option>
                  <option value="on-leave">On Leave</option>
                  <option value="terminated">Terminated</option>
                  <option value="suspended">Suspended</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Compensation -->
          <div class="bg-purple-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
              <i class="fas fa-money-bill-wave mr-2 text-purple-600"></i>
              Compensation
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pay Frequency</label>
                <div class="flex gap-4">
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input v-model="employeeForm.pay_frequency" type="radio" value="hourly" class="w-4 h-4 text-orange-600 focus:ring-orange-500">
                    <span class="text-sm font-medium text-gray-700">Hourly</span>
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input v-model="employeeForm.pay_frequency" type="radio" value="weekly" class="w-4 h-4 text-orange-600 focus:ring-orange-500">
                    <span class="text-sm font-medium text-gray-700">Weekly</span>
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input v-model="employeeForm.pay_frequency" type="radio" value="fortnightly" class="w-4 h-4 text-orange-600 focus:ring-orange-500">
                    <span class="text-sm font-medium text-gray-700">Fortnightly</span>
                  </label>
                </div>
              </div>

              <div v-if="employeeForm.pay_frequency === 'hourly'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Hourly Rate (JMD)</label>
                <input v-model.number="employeeForm.hourly_rate" type="number" step="0.01" min="0"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                       placeholder="0.00">
              </div>

              <div v-if="employeeForm.pay_frequency === 'weekly'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Weekly Salary (JMD)</label>
                <input v-model.number="employeeForm.salary_amount" type="number" step="0.01" min="0"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                       placeholder="0.00">
                <p class="text-xs text-gray-500 mt-1">Salary per week</p>
              </div>

              <div v-if="employeeForm.pay_frequency === 'fortnightly'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Fortnightly Salary (JMD)</label>
                <input v-model.number="employeeForm.salary_amount" type="number" step="0.01" min="0"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                       placeholder="0.00">
                <p class="text-xs text-gray-500 mt-1">Salary per fortnight (2 weeks)</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Standard Hours Per Day</label>
                <input v-model.number="employeeForm.standard_hours_per_day" type="number" step="0.5" min="0" max="24"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                       placeholder="8.0">
                <p class="text-xs text-gray-500 mt-1">Default: 8 hours</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Overtime Rate Multiplier</label>
                <input v-model.number="employeeForm.overtime_rate_multiplier" type="number" step="0.1" min="1" max="5"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                       placeholder="1.5">
                <p class="text-xs text-gray-500 mt-1">Default: 1.5× (time-and-a-half)</p>
              </div>

              <div class="md:col-span-2">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input v-model="employeeForm.clock_system_enabled" type="checkbox" class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500">
                  <span class="text-sm font-medium text-gray-700">Enable Clock In/Out System</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Banking Details -->
          <div class="bg-yellow-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
              <i class="fas fa-university mr-2 text-yellow-600"></i>
              Banking Details
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                <input v-model="employeeForm.bank_name" type="text"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                <input v-model="employeeForm.bank_account" type="text"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <input v-model="employeeForm.bank_branch" type="text"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
              </div>
            </div>
          </div>

          <!-- Emergency Contact -->
          <div class="bg-red-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
              <i class="fas fa-phone-alt mr-2 text-red-600"></i>
              Emergency Contact
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Name</label>
                <input v-model="employeeForm.emergency_contact_name" type="text"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                <input v-model="employeeForm.emergency_contact_phone" type="text"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Relationship</label>
                <input v-model="employeeForm.emergency_contact_relationship" type="text"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                       placeholder="e.g., Spouse, Parent, Sibling">
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
              <i class="fas fa-sticky-note mr-2 text-gray-600"></i>
              Additional Notes
            </h4>
            <textarea v-model="employeeForm.notes" rows="3"
                      class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                      placeholder="Any additional information about the employee..."></textarea>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-end gap-2 pt-4 border-t">
            <button type="button" @click="closeModal"
                    class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit" :disabled="saving"
                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 disabled:opacity-50">
              <i v-if="saving" class="fas fa-spinner fa-spin mr-2"></i>
              {{ saving ? 'Saving...' : (editingEmployee ? 'Update' : 'Create') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Employee Modal -->
    <div v-if="showViewModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeViewModal">
      <div class="bg-white rounded-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
          <h3 class="text-lg font-bold">Employee Details</h3>
          <button @click="closeViewModal" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div v-if="viewingEmployee" class="p-6 space-y-6">
          <!-- Personal Information -->
          <div class="bg-orange-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
              <i class="fas fa-user mr-2 text-orange-600"></i>
              Personal Information
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-600">Full Name</p>
                <p class="font-medium">{{ viewingEmployee.first_name }} {{ viewingEmployee.last_name }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Email</p>
                <p class="font-medium">{{ viewingEmployee.email }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Phone</p>
                <p class="font-medium">{{ viewingEmployee.phone || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">TRN</p>
                <p class="font-medium">{{ viewingEmployee.trn || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">NIS</p>
                <p class="font-medium">{{ viewingEmployee.nis || 'N/A' }}</p>
              </div>
            </div>
          </div>

          <!-- Address Information -->
          <div class="bg-blue-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
              <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>
              Address Information
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="md:col-span-3">
                <p class="text-sm text-gray-600">Address</p>
                <p class="font-medium">{{ viewingEmployee.address || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">City</p>
                <p class="font-medium">{{ viewingEmployee.city || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Parish</p>
                <p class="font-medium">{{ viewingEmployee.parish || 'N/A' }}</p>
              </div>
            </div>
          </div>

          <!-- Employment Details -->
          <div class="bg-green-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
              <i class="fas fa-briefcase mr-2 text-green-600"></i>
              Employment Details
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-600">Employee Number</p>
                <p class="font-medium font-mono">{{ viewingEmployee.employee_number }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Position</p>
                <p class="font-medium">{{ viewingEmployee.position }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Department</p>
                <p class="font-medium">{{ getDepartmentName(viewingEmployee.department_id) }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Hire Date</p>
                <p class="font-medium">{{ formatDate(viewingEmployee.hire_date) }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Employment Type</p>
                <p class="font-medium capitalize">{{ viewingEmployee.employment_type }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Employment Status</p>
                <span class="px-2 py-1 text-xs rounded-full font-medium" :class="getStatusClass(viewingEmployee.employment_status)">
                  {{ viewingEmployee.employment_status }}
                </span>
              </div>
            </div>
          </div>

          <!-- Compensation -->
          <div class="bg-purple-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
              <i class="fas fa-money-bill-wave mr-2 text-purple-600"></i>
              Compensation
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-600">Pay Frequency</p>
                <p class="font-medium capitalize">{{ viewingEmployee.pay_frequency || 'Hourly' }}</p>
              </div>
              <div v-if="viewingEmployee.pay_frequency === 'hourly'">
                <p class="text-sm text-gray-600">Hourly Rate</p>
                <p class="font-medium">JMD {{ formatAmount(viewingEmployee.hourly_rate) }}</p>
              </div>
              <div v-if="viewingEmployee.pay_frequency === 'weekly'">
                <p class="text-sm text-gray-600">Weekly Salary</p>
                <p class="font-medium">JMD {{ formatAmount(viewingEmployee.salary_amount) }}</p>
              </div>
              <div v-if="viewingEmployee.pay_frequency === 'fortnightly'">
                <p class="text-sm text-gray-600">Fortnightly Salary</p>
                <p class="font-medium">JMD {{ formatAmount(viewingEmployee.salary_amount) }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Standard Hours/Day</p>
                <p class="font-medium">{{ viewingEmployee.standard_hours_per_day || 8 }} hours</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Overtime Multiplier</p>
                <p class="font-medium">{{ viewingEmployee.overtime_rate_multiplier || 1.5 }}×</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Clock System</p>
                <p class="font-medium">{{ viewingEmployee.clock_system_enabled ? 'Enabled' : 'Disabled' }}</p>
              </div>
            </div>
          </div>

          <!-- Banking Details -->
          <div class="bg-yellow-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
              <i class="fas fa-university mr-2 text-yellow-600"></i>
              Banking Details
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <p class="text-sm text-gray-600">Bank Name</p>
                <p class="font-medium">{{ viewingEmployee.bank_name || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Account Number</p>
                <p class="font-medium">{{ viewingEmployee.bank_account || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Branch</p>
                <p class="font-medium">{{ viewingEmployee.bank_branch || 'N/A' }}</p>
              </div>
            </div>
          </div>

          <!-- Emergency Contact -->
          <div class="bg-red-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
              <i class="fas fa-phone-alt mr-2 text-red-600"></i>
              Emergency Contact
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <p class="text-sm text-gray-600">Contact Name</p>
                <p class="font-medium">{{ viewingEmployee.emergency_contact_name || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Contact Phone</p>
                <p class="font-medium">{{ viewingEmployee.emergency_contact_phone || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Relationship</p>
                <p class="font-medium">{{ viewingEmployee.emergency_contact_relationship || 'N/A' }}</p>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="viewingEmployee.notes" class="bg-gray-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
              <i class="fas fa-sticky-note mr-2 text-gray-600"></i>
              Additional Notes
            </h4>
            <p class="text-gray-700 whitespace-pre-wrap">{{ viewingEmployee.notes }}</p>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t">
            <button @click="closeViewModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
              Close
            </button>
            <button @click="editEmployee(viewingEmployee)" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
              <i class="fas fa-edit mr-2"></i>Edit Employee
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="showDeleteConfirm = false">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Confirm Delete</h3>
        <p class="text-gray-700 mb-6">Are you sure you want to delete this employee? This action cannot be undone.</p>
        <div class="flex justify-end gap-2">
          <button @click="showDeleteConfirm = false" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
          <button @click="deleteEmployee" :disabled="deleting" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
            <i v-if="deleting" class="fas fa-spinner fa-spin mr-2"></i>
            {{ deleting ? 'Deleting...' : 'Delete' }}
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
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'

// Types
interface Department {
  id: number
  name: string
  description: string | null
  is_active: boolean
}

interface Employee {
  id: number
  employee_number: string
  first_name: string
  last_name: string
  email: string
  phone: string | null
  trn: string | null
  nis: string | null
  address: string | null
  city: string | null
  parish: string | null
  department_id: number | null
  position: string
  hire_date: string
  termination_date: string | null
  employment_type: 'full-time' | 'part-time' | 'contract' | 'casual'
  employment_status: 'active' | 'on-leave' | 'terminated' | 'suspended'
  hourly_rate: number | null
  salary_per_period: number | null
  is_salaried: boolean
  bank_name: string | null
  bank_account: string | null
  bank_branch: string | null
  emergency_contact_name: string | null
  emergency_contact_phone: string | null
  emergency_contact_relationship: string | null
  notes: string | null
  is_active: boolean
  created_at: string
  updated_at: string
}

interface EmployeeForm {
  employee_number: string
  first_name: string
  last_name: string
  email: string
  phone: string
  trn: string
  nis: string
  address: string
  city: string
  parish: string
  department_id: number | null
  position: string
  hire_date: string
  employment_type: string
  employment_status: string
  is_salaried: boolean
  pay_frequency: string
  hourly_rate: number | null
  salary_amount: number | null
  standard_hours_per_day: number
  overtime_rate_multiplier: number
  clock_system_enabled: boolean
  bank_name: string
  bank_account: string
  bank_branch: string
  emergency_contact_name: string
  emergency_contact_phone: string
  emergency_contact_relationship: string
  notes: string
}

// State
const sidebarOpen = ref(false)
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const employees = ref<Employee[]>([])
const departments = ref<Department[]>([])
const showModal = ref(false)
const showViewModal = ref(false)
const showDeleteConfirm = ref(false)
const editingEmployee = ref<Employee | null>(null)
const viewingEmployee = ref<Employee | null>(null)
const deleteTarget = ref<number | null>(null)
const searchQuery = ref('')
const filterStatus = ref('')
const filterDepartment = ref('')

// Form
const employeeForm = ref<EmployeeForm>({
  employee_number: '',
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  trn: '',
  nis: '',
  address: '',
  city: '',
  parish: '',
  department_id: null,
  position: '',
  hire_date: '',
  employment_type: 'full-time',
  employment_status: 'active',
  is_salaried: false,
  pay_frequency: 'hourly',
  hourly_rate: null,
  salary_amount: null,
  standard_hours_per_day: 8.0,
  overtime_rate_multiplier: 1.5,
  clock_system_enabled: true,
  bank_name: '',
  bank_account: '',
  bank_branch: '',
  emergency_contact_name: '',
  emergency_contact_phone: '',
  emergency_contact_relationship: '',
  notes: ''
})

// Computed
const filteredEmployees = computed(() => {
  let result = employees.value

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(emp =>
      emp.first_name.toLowerCase().includes(query) ||
      emp.last_name.toLowerCase().includes(query) ||
      emp.email.toLowerCase().includes(query) ||
      emp.employee_number.toLowerCase().includes(query)
    )
  }

  // Status filter
  if (filterStatus.value) {
    result = result.filter(emp => emp.employment_status === filterStatus.value)
  }

  // Department filter
  if (filterDepartment.value) {
    result = result.filter(emp => emp.department_id === Number(filterDepartment.value))
  }

  return result
})

// Functions
async function loadEmployees() {
  loading.value = true
  try {
    const resp = await fetch('/api/employees', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    if (!resp.ok) throw new Error('Failed to load employees')
    employees.value = await resp.json()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load employees', 'error')
  } finally {
    loading.value = false
  }
}

async function loadDepartments() {
  try {
    const resp = await fetch('/api/departments', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    if (!resp.ok) throw new Error('Failed to load departments')
    departments.value = await resp.json()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load departments', 'error')
  }
}

function openAddModal() {
  editingEmployee.value = null
  resetForm()
  showModal.value = true
}

function editEmployee(employee: Employee) {
  editingEmployee.value = employee
  employeeForm.value = {
    employee_number: employee.employee_number,
    first_name: employee.first_name,
    last_name: employee.last_name,
    email: employee.email,
    phone: employee.phone || '',
    trn: employee.trn || '',
    nis: employee.nis || '',
    address: employee.address || '',
    city: employee.city || '',
    parish: employee.parish || '',
    department_id: employee.department_id,
    position: employee.position,
    hire_date: employee.hire_date,
    employment_type: employee.employment_type,
    employment_status: employee.employment_status,
    is_salaried: employee.is_salaried,
    hourly_rate: employee.hourly_rate,
    salary_per_period: employee.salary_per_period,
    bank_name: employee.bank_name || '',
    bank_account: employee.bank_account || '',
    bank_branch: employee.bank_branch || '',
    emergency_contact_name: employee.emergency_contact_name || '',
    emergency_contact_phone: employee.emergency_contact_phone || '',
    emergency_contact_relationship: employee.emergency_contact_relationship || '',
    notes: employee.notes || ''
  }
  showViewModal.value = false
  showModal.value = true
}

function viewEmployee(employee: Employee) {
  viewingEmployee.value = employee
  showViewModal.value = true
}

async function saveEmployee() {
  saving.value = true
  try {
    const payload = {
      ...employeeForm.value,
      department_id: employeeForm.value.department_id || null,
      phone: employeeForm.value.phone || null,
      trn: employeeForm.value.trn || null,
      nis: employeeForm.value.nis || null,
      address: employeeForm.value.address || null,
      city: employeeForm.value.city || null,
      parish: employeeForm.value.parish || null,
      bank_name: employeeForm.value.bank_name || null,
      bank_account: employeeForm.value.bank_account || null,
      bank_branch: employeeForm.value.bank_branch || null,
      emergency_contact_name: employeeForm.value.emergency_contact_name || null,
      emergency_contact_phone: employeeForm.value.emergency_contact_phone || null,
      emergency_contact_relationship: employeeForm.value.emergency_contact_relationship || null,
      notes: employeeForm.value.notes || null
    }

    if (editingEmployee.value) {
      const resp = await fetch(`/api/employees/${editingEmployee.value.id}`, {
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
        throw new Error(error.message || 'Failed to update employee')
      }
      toast('Success', 'Employee updated successfully', 'success')
    } else {
      const resp = await fetch('/api/employees', {
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
        throw new Error(error.message || 'Failed to create employee')
      }
      toast('Success', 'Employee created successfully', 'success')
    }

    closeModal()
    await loadEmployees()
  } catch (e: any) {
    console.error(e)
    toast('Error', e.message || 'Failed to save employee', 'error')
  } finally {
    saving.value = false
  }
}

function confirmDelete(id: number) {
  deleteTarget.value = id
  showDeleteConfirm.value = true
}

async function deleteEmployee() {
  if (!deleteTarget.value) return

  deleting.value = true
  try {
    const resp = await fetch(`/api/employees/${deleteTarget.value}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    if (!resp.ok) {
      const error = await resp.json().catch(() => ({}))
      throw new Error(error.error || 'Failed to delete employee')
    }
    toast('Success', 'Employee deleted', 'success')
    await loadEmployees()
  } catch (e: any) {
    console.error(e)
    toast('Error', e.message || 'Failed to delete employee', 'error')
  } finally {
    deleting.value = false
    showDeleteConfirm.value = false
    deleteTarget.value = null
  }
}

function closeModal() {
  showModal.value = false
  editingEmployee.value = null
  resetForm()
}

function closeViewModal() {
  showViewModal.value = false
  viewingEmployee.value = null
}

function resetForm() {
  employeeForm.value = {
    employee_number: '',
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    trn: '',
    nis: '',
    address: '',
    city: '',
    parish: '',
    department_id: null,
    position: '',
    hire_date: '',
    employment_type: 'full-time',
    employment_status: 'active',
    is_salaried: false,
    hourly_rate: null,
    salary_per_period: null,
    bank_name: '',
    bank_account: '',
    bank_branch: '',
    emergency_contact_name: '',
    emergency_contact_phone: '',
    emergency_contact_relationship: '',
    notes: ''
  }
}

function getDepartmentName(departmentId: number | null): string {
  if (!departmentId) return 'N/A'
  const dept = departments.value.find(d => d.id === departmentId)
  return dept?.name || 'N/A'
}

function getStatusClass(status: string): string {
  const classes: Record<string, string> = {
    'active': 'bg-green-100 text-green-800',
    'on-leave': 'bg-yellow-100 text-yellow-800',
    'terminated': 'bg-red-100 text-red-800',
    'suspended': 'bg-orange-100 text-orange-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

function formatDate(date: string | null): string {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

function formatAmount(amount: number | null): string {
  if (amount === null || amount === undefined) return '0.00'
  return amount.toFixed(2)
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
  loadEmployees()
  loadDepartments()
})
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
