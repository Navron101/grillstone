<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar (same as other inventory pages) -->
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
            <a href="/payroll" class="flex items-center gap-3 rounded-xl px-3 py-2 transition-colors bg-orange-50 text-orange-700">
              <i class="fas fa-money-check-alt text-lg text-orange-600"></i>
              <span v-if="sidebarOpen" class="font-medium">Payroll</span>
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
    <div class="flex-1 p-6 overflow-y-auto">
      <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
          <div>
            <div class="flex items-center gap-3 mb-2">
              <a href="/payroll" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left"></i>
              </a>
              <h1 class="text-3xl font-bold text-gray-900">{{ period?.reference || 'Loading...' }}</h1>
              <span v-if="period" class="px-3 py-1 text-sm rounded-full font-medium" :class="statusClass(period.status)">
                {{ period.status }}
              </span>
            </div>
            <p class="text-gray-600">Payroll Period Details</p>
          </div>

          <div class="flex gap-2">
            <button v-if="period?.status === 'draft'" @click="showGenerateConfirmation"
                    :disabled="generating"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium disabled:opacity-50">
              <i class="fas fa-cog mr-2"></i>{{ generating ? 'Generating...' : 'Generate Payslips' }}
            </button>
            <button v-if="period?.status === 'processing'" @click="showApproveConfirmation"
                    :disabled="approving"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium disabled:opacity-50">
              <i class="fas fa-check mr-2"></i>{{ approving ? 'Approving...' : 'Approve Period' }}
            </button>
            <button v-if="period?.status === 'approved'" @click="showEmailConfirmation"
                    :disabled="emailing"
                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium disabled:opacity-50">
              <i class="fas fa-envelope mr-2"></i>{{ emailing ? 'Emailing...' : 'Email Payslips' }}
            </button>
            <button v-if="period?.status === 'approved'" @click="showMarkPaidConfirmation"
                    :disabled="markingPaid"
                    class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium disabled:opacity-50">
              <i class="fas fa-dollar-sign mr-2"></i>{{ markingPaid ? 'Processing...' : 'Mark as Paid' }}
            </button>
          </div>
        </div>

        <div v-if="loading" class="text-center py-12 text-gray-500">
          <i class="fas fa-spinner fa-spin text-3xl mb-3"></i>
          <p>Loading period details...</p>
        </div>

        <!-- Period Details -->
        <div v-else-if="period" class="space-y-6">
          <!-- Summary Stats -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Period Summary</h3>
            <div class="grid grid-cols-4 gap-4">
              <div class="text-center">
                <div class="text-2xl font-bold text-gray-900">{{ formatDate(period.start_date) }}</div>
                <div class="text-sm text-gray-600">Start Date</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-gray-900">{{ formatDate(period.end_date) }}</div>
                <div class="text-sm text-gray-600">End Date</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ payslips.length }}</div>
                <div class="text-sm text-gray-600">Payslips</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-green-600">JMD {{ formatCurrency(totalGrossPay) }}</div>
                <div class="text-sm text-gray-600">Total Gross Pay</div>
              </div>
            </div>
          </div>

          <!-- Payslips Table -->
          <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
              <h3 class="text-lg font-semibold text-gray-900">Payslips</h3>
            </div>

            <div v-if="loadingPayslips" class="text-center py-12 text-gray-500">
              <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
              <p>Loading payslips...</p>
            </div>

            <div v-else-if="!payslips.length" class="text-center py-12 text-gray-400">
              <i class="fas fa-file-invoice-dollar text-4xl mb-3"></i>
              <p>No payslips generated yet.</p>
              <button v-if="period.status === 'draft'" @click="showGenerateConfirmation"
                      class="mt-4 text-blue-600 hover:underline">
                Generate payslips to get started
              </button>
            </div>

            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee #</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Regular Hours</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">OT Hours</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gross Pay</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Deductions</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net Pay</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="payslip in payslips" :key="payslip.id" class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                      {{ payslip.employee_name }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ payslip.employee_number || '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ payslip.department_name || '-' }}</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-700">{{ formatHours(payslip.regular_hours) }}</td>
                    <td class="px-4 py-3 text-sm text-right text-gray-700">{{ formatHours(payslip.overtime_hours) }}</td>
                    <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">
                      JMD {{ formatCurrency(centsToAmount(payslip.gross_pay_cents)) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-right text-red-600">
                      JMD {{ formatCurrency(calculateTotalDeductions(payslip)) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-right font-bold text-green-600">
                      JMD {{ formatCurrency(centsToAmount(payslip.net_pay_cents)) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                      <span class="px-2 py-1 text-xs rounded-full font-medium" :class="payslipStatusClass(payslip.status)">
                        {{ payslip.status }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-center space-x-2">
                      <button @click="viewPayslip(payslip.id)"
                              class="text-blue-600 hover:text-blue-800"
                              title="View Details">
                        <i class="fas fa-eye"></i>
                      </button>
                      <button @click="downloadPayslipPdf(payslip.id)"
                              class="text-green-600 hover:text-green-800"
                              title="Download PDF">
                        <i class="fas fa-download"></i>
                      </button>
                      <button v-if="period.status !== 'paid'" @click="editPayslip(payslip)"
                              class="text-orange-600 hover:text-orange-800"
                              title="Edit">
                        <i class="fas fa-edit"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Generate Confirmation Modal -->
    <div v-if="showGenerateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Generate Payslips</h3>
        <p class="text-gray-700 mb-4">
          This will generate payslips for all active employees for the period from
          <strong>{{ formatDate(period?.start_date) }}</strong> to <strong>{{ formatDate(period?.end_date) }}</strong>.
        </p>
        <p class="text-sm text-gray-600 mb-4">
          Payslips will be calculated based on timesheets and employee pay rates.
        </p>

        <!-- Statutory Deductions Checkbox -->
        <div class="mb-6 p-4 border border-orange-200 bg-orange-50 rounded-lg">
          <label class="flex items-start gap-3 cursor-pointer">
            <input v-model="includeStatutoryDeductions" type="checkbox"
                   class="mt-1 w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
            <div>
              <span class="font-medium text-gray-900">Include Statutory Deductions</span>
              <p class="text-xs text-gray-600 mt-1">
                NIS (3%), NHT (2%), Education Tax (2.25%), and PAYE (Income Tax)
              </p>
              <p class="text-xs text-orange-700 mt-1 font-medium">
                Note: Employee tab charges will always be included
              </p>
            </div>
          </label>
        </div>

        <div class="flex justify-end gap-2">
          <button @click="showGenerateModal = false" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
            Cancel
          </button>
          <button @click="generatePayslips" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Generate
          </button>
        </div>
      </div>
    </div>

    <!-- Approve Confirmation Modal -->
    <div v-if="showApproveModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Approve Payroll Period</h3>
        <p class="text-gray-700 mb-4">
          Are you sure you want to approve this payroll period? This action will finalize all payslips.
        </p>
        <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
          <p class="text-sm text-yellow-800">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            Once approved, payslips cannot be edited. You can still email them and mark them as paid.
          </p>
        </div>
        <div class="flex justify-end gap-2">
          <button @click="showApproveModal = false" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
            Cancel
          </button>
          <button @click="approvePeriod" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            Approve
          </button>
        </div>
      </div>
    </div>

    <!-- Email Confirmation Modal -->
    <div v-if="showEmailModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Email Payslips</h3>
        <p class="text-gray-700 mb-4">
          This will send payslip emails to all {{ payslips.length }} employees for this period.
        </p>
        <p class="text-sm text-gray-600 mb-6">
          Employees will receive their payslips as PDF attachments.
        </p>
        <div class="flex justify-end gap-2">
          <button @click="showEmailModal = false" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
            Cancel
          </button>
          <button @click="emailPayslips" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
            Send Emails
          </button>
        </div>
      </div>
    </div>

    <!-- Mark Paid Confirmation Modal -->
    <div v-if="showMarkPaidModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Mark Period as Paid</h3>
        <p class="text-gray-700 mb-4">
          This will mark all payslips in this period as paid. Total amount: <strong>JMD {{ formatCurrency(totalNetPay) }}</strong>
        </p>
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
          <p class="text-sm text-green-800">
            <i class="fas fa-info-circle mr-1"></i>
            This indicates that payments have been processed. This action cannot be undone.
          </p>
        </div>
        <div class="flex justify-end gap-2">
          <button @click="showMarkPaidModal = false" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
            Cancel
          </button>
          <button @click="markAsPaid" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
            Confirm Paid
          </button>
        </div>
      </div>
    </div>

    <!-- Edit Payslip Modal -->
    <div v-if="showEditModal && editingPayslip" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeEditModal">
      <div class="bg-white rounded-lg p-6 w-full max-w-lg">
        <h3 class="text-lg font-bold mb-4">Edit Payslip Adjustments</h3>
        <p class="text-sm text-gray-600 mb-4">
          Employee: <strong>{{ editingPayslip.employee?.first_name }} {{ editingPayslip.employee?.last_name }}</strong>
        </p>

        <form @submit.prevent="savePayslipAdjustments" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bonus (JMD)</label>
            <input v-model.number="editForm.bonus" type="number" step="0.01" min="0"
                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                   placeholder="0.00">
            <p class="text-xs text-gray-500 mt-1">Additional bonus amount</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Other Deductions (JMD)</label>
            <input v-model.number="editForm.other_deductions" type="number" step="0.01" min="0"
                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                   placeholder="0.00">
            <p class="text-xs text-gray-500 mt-1">Additional deductions (uniform, loans, etc.)</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea v-model="editForm.notes" rows="3"
                      class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none"
                      placeholder="Add any notes about adjustments..."></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <button type="button" @click="closeEditModal"
                    class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit" :disabled="savingEdit"
                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 disabled:opacity-50">
              {{ savingEdit ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Payslip Modal -->
    <div v-if="showViewModal && viewingPayslip" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeViewModal">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-bold text-gray-900">Payslip Details</h3>
          <button @click="closeViewModal" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="space-y-6">
          <!-- Employee Info -->
          <div class="border-b pb-4">
            <h4 class="font-semibold text-gray-900 mb-3">Employee Information</h4>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-600">Name</p>
                <p class="font-medium">{{ viewingPayslip.employee?.first_name }} {{ viewingPayslip.employee?.last_name }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Employee Number</p>
                <p class="font-medium">{{ viewingPayslip.employee?.employee_number || '-' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Department</p>
                <p class="font-medium">{{ viewingPayslip.employee?.department || '-' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Position</p>
                <p class="font-medium">{{ viewingPayslip.employee?.position || '-' }}</p>
              </div>
            </div>
          </div>

          <!-- Hours Worked -->
          <div class="border-b pb-4">
            <h4 class="font-semibold text-gray-900 mb-3">Hours Worked</h4>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-600">Regular Hours</p>
                <p class="text-lg font-medium">{{ formatHours(viewingPayslip.regular_hours) }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Overtime Hours</p>
                <p class="text-lg font-medium">{{ formatHours(viewingPayslip.overtime_hours) }}</p>
              </div>
            </div>
          </div>

          <!-- Earnings Breakdown -->
          <div class="border-b pb-4">
            <h4 class="font-semibold text-gray-900 mb-3">Earnings</h4>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-gray-700">Regular Pay</span>
                <span class="font-medium">JMD {{ formatCurrency(centsToAmount(viewingPayslip.regular_pay_cents)) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-700">Overtime Pay</span>
                <span class="font-medium">JMD {{ formatCurrency(centsToAmount(viewingPayslip.overtime_pay_cents)) }}</span>
              </div>
              <div v-if="viewingPayslip.bonus_cents > 0" class="flex justify-between">
                <span class="text-gray-700">Bonus</span>
                <span class="font-medium text-green-600">JMD {{ formatCurrency(centsToAmount(viewingPayslip.bonus_cents)) }}</span>
              </div>
              <div class="flex justify-between pt-2 border-t font-semibold text-lg">
                <span>Gross Pay</span>
                <span>JMD {{ formatCurrency(centsToAmount(viewingPayslip.gross_pay_cents)) }}</span>
              </div>
            </div>
          </div>

          <!-- Deductions -->
          <div class="border-b pb-4">
            <h4 class="font-semibold text-gray-900 mb-3">Deductions</h4>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-gray-700">NIS ({{ viewingPayslip.nis_rate }}%)</span>
                <span class="text-red-600">JMD {{ formatCurrency(centsToAmount(viewingPayslip.nis_cents)) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-700">NHT ({{ viewingPayslip.nht_rate }}%)</span>
                <span class="text-red-600">JMD {{ formatCurrency(centsToAmount(viewingPayslip.nht_cents)) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-700">Education Tax ({{ viewingPayslip.education_tax_rate }}%)</span>
                <span class="text-red-600">JMD {{ formatCurrency(centsToAmount(viewingPayslip.education_tax_cents)) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-700">PAYE</span>
                <span class="text-red-600">JMD {{ formatCurrency(centsToAmount(viewingPayslip.paye_cents)) }}</span>
              </div>
              <div v-if="viewingPayslip.other_deductions_cents > 0" class="flex justify-between">
                <span class="text-gray-700">Other Deductions</span>
                <span class="text-red-600">JMD {{ formatCurrency(centsToAmount(viewingPayslip.other_deductions_cents)) }}</span>
              </div>
              <div class="flex justify-between pt-2 border-t font-semibold">
                <span>Total Deductions</span>
                <span class="text-red-600">JMD {{ formatCurrency(calculateTotalDeductions(viewingPayslip)) }}</span>
              </div>
            </div>
          </div>

          <!-- Net Pay -->
          <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex justify-between items-center">
              <span class="text-lg font-semibold text-gray-900">Net Pay</span>
              <span class="text-2xl font-bold text-green-600">JMD {{ formatCurrency(centsToAmount(viewingPayslip.net_pay_cents)) }}</span>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="viewingPayslip.notes" class="border-t pt-4">
            <h4 class="font-semibold text-gray-900 mb-2">Notes</h4>
            <p class="text-gray-700 text-sm">{{ viewingPayslip.notes }}</p>
          </div>
        </div>

        <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
          <button @click="closeViewModal" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
            Close
          </button>
          <button @click="downloadPayslipPdf(viewingPayslip?.id)" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-download mr-2"></i>Download PDF
          </button>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
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
import { ref, computed, onMounted } from 'vue'

const props = defineProps<{
  id: string | number
}>()

const sidebarOpen = ref(false)
const loading = ref(true)
const loadingPayslips = ref(false)
const generating = ref(false)
const approving = ref(false)
const emailing = ref(false)
const markingPaid = ref(false)
const savingEdit = ref(false)

const showGenerateModal = ref(false)
const showApproveModal = ref(false)
const showEmailModal = ref(false)
const showMarkPaidModal = ref(false)
const showEditModal = ref(false)
const showViewModal = ref(false)

const includeStatutoryDeductions = ref(true)

type Employee = {
  id: number
  first_name: string
  last_name: string
  employee_number: string | null
  department: string | null
  position: string | null
}

type Payslip = {
  id: number
  employee_id: number
  employee?: Employee
  regular_hours: number
  overtime_hours: number
  regular_pay_cents: number
  overtime_pay_cents: number
  gross_pay_cents: number
  bonus_cents: number
  nis_cents: number
  nis_rate: number
  nht_cents: number
  nht_rate: number
  education_tax_cents: number
  education_tax_rate: number
  paye_cents: number
  other_deductions_cents: number
  net_pay_cents: number
  status: string
  notes: string | null
}

type PayrollPeriod = {
  id: number
  reference: string
  start_date: string
  end_date: string
  status: string
  created_at: string
}

const period = ref<PayrollPeriod | null>(null)
const payslips = ref<Payslip[]>([])
const editingPayslip = ref<Payslip | null>(null)
const viewingPayslip = ref<Payslip | null>(null)

const editForm = ref({
  bonus: 0,
  other_deductions: 0,
  notes: ''
})

async function loadPeriod() {
  loading.value = true
  try {
    const resp = await fetch(`/api/payroll/periods/${props.id}`)
    if (!resp.ok) throw new Error('Failed to load period')
    period.value = await resp.json()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load period', 'error')
  } finally {
    loading.value = false
  }
}

async function loadPayslips() {
  loadingPayslips.value = true
  try {
    const resp = await fetch(`/api/payroll/periods/${props.id}/payslips`)
    if (!resp.ok) throw new Error('Failed to load payslips')
    payslips.value = await resp.json()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load payslips', 'error')
  } finally {
    loadingPayslips.value = false
  }
}

const totalGrossPay = computed(() => {
  return payslips.value.reduce((sum, p) => sum + centsToAmount(p.gross_pay_cents), 0)
})

const totalNetPay = computed(() => {
  return payslips.value.reduce((sum, p) => sum + centsToAmount(p.net_pay_cents), 0)
})

function showGenerateConfirmation() {
  showGenerateModal.value = true
}

async function generatePayslips() {
  generating.value = true
  try {
    const resp = await fetch(`/api/payroll/periods/${props.id}/generate`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        include_statutory_deductions: includeStatutoryDeductions.value
      })
    })

    if (!resp.ok) throw new Error('Failed to generate payslips')

    const message = includeStatutoryDeductions.value
      ? 'Payslips generated with statutory deductions'
      : 'Payslips generated without statutory deductions'

    toast('Success', message, 'success')
    await loadPeriod()
    await loadPayslips()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to generate payslips', 'error')
  } finally {
    generating.value = false
    showGenerateModal.value = false
  }
}

function showApproveConfirmation() {
  showApproveModal.value = true
}

async function approvePeriod() {
  approving.value = true
  try {
    const resp = await fetch(`/api/payroll/periods/${props.id}/approve`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    })

    if (!resp.ok) throw new Error('Failed to approve period')

    toast('Success', 'Payroll period approved', 'success')
    await loadPeriod()
    await loadPayslips()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to approve period', 'error')
  } finally {
    approving.value = false
    showApproveModal.value = false
  }
}

function showEmailConfirmation() {
  showEmailModal.value = true
}

async function emailPayslips() {
  emailing.value = true
  try {
    const resp = await fetch(`/api/payroll/periods/${props.id}/email`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    })

    if (!resp.ok) throw new Error('Failed to email payslips')

    toast('Success', 'Payslips emailed successfully', 'success')
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to email payslips', 'error')
  } finally {
    emailing.value = false
    showEmailModal.value = false
  }
}

function showMarkPaidConfirmation() {
  showMarkPaidModal.value = true
}

async function markAsPaid() {
  markingPaid.value = true
  try {
    const resp = await fetch(`/api/payroll/periods/${props.id}/mark-paid`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    })

    if (!resp.ok) throw new Error('Failed to mark as paid')

    toast('Success', 'Period marked as paid', 'success')
    await loadPeriod()
    await loadPayslips()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to mark as paid', 'error')
  } finally {
    markingPaid.value = false
    showMarkPaidModal.value = false
  }
}

function editPayslip(payslip: Payslip) {
  editingPayslip.value = payslip
  editForm.value = {
    bonus: centsToAmount(payslip.bonus_cents),
    other_deductions: centsToAmount(payslip.other_deductions_cents),
    notes: payslip.notes || ''
  }
  showEditModal.value = true
}

async function savePayslipAdjustments() {
  if (!editingPayslip.value) return

  savingEdit.value = true
  try {
    const payload = {
      bonus_cents: amountToCents(editForm.value.bonus),
      other_deductions_cents: amountToCents(editForm.value.other_deductions),
      notes: editForm.value.notes
    }

    const resp = await fetch(`/api/payroll/periods/${props.id}/payslips/${editingPayslip.value.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })

    if (!resp.ok) throw new Error('Failed to update payslip')

    toast('Success', 'Payslip updated successfully', 'success')
    await loadPayslips()
    closeEditModal()
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to update payslip', 'error')
  } finally {
    savingEdit.value = false
  }
}

function closeEditModal() {
  showEditModal.value = false
  editingPayslip.value = null
  editForm.value = { bonus: 0, other_deductions: 0, notes: '' }
}

async function viewPayslip(payslipId: number) {
  try {
    const resp = await fetch(`/api/payroll/periods/${props.id}/payslips/${payslipId}`)
    if (!resp.ok) throw new Error('Failed to load payslip details')
    viewingPayslip.value = await resp.json()
    showViewModal.value = true
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to load payslip details', 'error')
  }
}

function closeViewModal() {
  showViewModal.value = false
  viewingPayslip.value = null
}

async function downloadPayslipPdf(payslipId: number | undefined) {
  if (!payslipId) return

  try {
    const url = `/api/payroll/periods/${props.id}/payslips/${payslipId}/pdf`

    // Open in new window to trigger download
    window.open(url, '_blank')

    toast('Success', 'Payslip PDF is downloading', 'success')
  } catch (e) {
    console.error(e)
    toast('Error', 'Failed to download PDF', 'error')
  }
}

function statusClass(status: string) {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    processing: 'bg-blue-100 text-blue-800',
    approved: 'bg-green-100 text-green-800',
    paid: 'bg-purple-100 text-purple-800'
  }
  return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800'
}

function payslipStatusClass(status: string) {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    paid: 'bg-purple-100 text-purple-800'
  }
  return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800'
}

function formatDate(date: string | undefined) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-JM', { year: 'numeric', month: 'short', day: 'numeric' })
}

function formatHours(hours: number | string | null | undefined): string {
  if (hours === null || hours === undefined) return '0.00'
  const num = typeof hours === 'string' ? parseFloat(hours) : hours
  if (isNaN(num)) return '0.00'
  return num.toFixed(2)
}

function formatCurrency(amount: number | string): string {
  const num = typeof amount === 'string' ? parseFloat(amount) : amount
  if (isNaN(num)) return '0.00'
  return num.toLocaleString('en-JM', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function centsToAmount(cents: number | string | null | undefined): number {
  if (cents === null || cents === undefined) return 0
  const num = typeof cents === 'string' ? parseFloat(cents) : cents
  if (isNaN(num)) return 0
  return num / 100
}

function amountToCents(amount: number | string): number {
  const num = typeof amount === 'string' ? parseFloat(amount) : amount
  if (isNaN(num)) return 0
  return Math.round(num * 100)
}

function calculateTotalDeductions(payslip: Payslip): number {
  return centsToAmount(
    payslip.nis_cents +
    payslip.nht_cents +
    payslip.education_tax_cents +
    payslip.paye_cents +
    payslip.other_deductions_cents
  )
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
  loadPeriod()
  loadPayslips()
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
