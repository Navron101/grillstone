# Updates: Pay Frequency & Menu Enhancements

## Summary

This update adds comprehensive pay frequency support (hourly, weekly, fortnightly) for employees, fixes the payroll period viewing error, and enhances the POS menu with HR/Payroll subpages.

---

## 1. Fixed "Failed to Load Period" Error

### Issue
When viewing a payroll period detail page, the frontend was calling `/api/payroll/periods/{id}` but the backend route didn't exist, causing a "Failed to load period" error.

### Fix
**Added `showPeriod` method to PayrollController** (`app/Http/Controllers/Api/PayrollController.php:56-77`)
```php
public function showPeriod($id)
{
    $period = DB::table('payroll_periods as pp')
        ->leftJoin('users as u', 'u.id', '=', 'pp.processed_by')
        ->where('pp.id', $id)
        ->select([
            'pp.*',
            DB::raw("CONCAT(u.name) as processed_by_name"),
        ])
        ->first();

    if (!$period) {
        return response()->json(['error' => 'Period not found'], 404);
    }

    $period->payslip_count = DB::table('payslips')
        ->where('payroll_period_id', $period->id)
        ->count();

    return response()->json($period);
}
```

**Added route** (`routes/api.php:116`)
```php
Route::get('/payroll/periods/{id}', [PayrollController::class, 'showPeriod']);
```

---

## 2. Employee Pay Frequency Configuration

### Database Changes

**Migration:** `database/migrations/2025_10_20_220044_add_pay_frequency_to_employees_table.php`

Added new fields to `employees` table:
- `pay_frequency` - enum('hourly', 'weekly', 'fortnightly') - Default: 'hourly'
- Renamed `salary_per_period` to `salary_amount` for clarity

```bash
php artisan migrate
```

### Backend Updates

**EmployeeController** (`app/Http/Controllers/Api/EmployeeController.php`)

Added validation for new fields:
```php
'pay_frequency' => ['nullable', Rule::in(['hourly', 'weekly', 'fortnightly'])],
'salary_amount' => 'nullable|numeric|min:0|max:99999999.99',
'standard_hours_per_day' => 'nullable|numeric|min:0|max:24',
'overtime_rate_multiplier' => 'nullable|numeric|min:1|max:5',
'clock_system_enabled' => 'nullable|boolean',
```

Set defaults in `store()` method:
```php
$data['pay_frequency'] = $data['pay_frequency'] ?? 'hourly';
$data['standard_hours_per_day'] = $data['standard_hours_per_day'] ?? 8.00;
$data['overtime_rate_multiplier'] = $data['overtime_rate_multiplier'] ?? 1.5;
$data['clock_system_enabled'] = $data['clock_system_enabled'] ?? true;
```

**PayrollService** (`app/Services/PayrollService.php:46-80`)

Updated `calculatePayslip()` to handle all three pay frequencies:

**Hourly:**
```php
$regularPayCents = round($employee->hourly_rate * $regularHours * 100);
$overtimePayCents = round($employee->hourly_rate * $overtimeMultiplier * $overtimeHours * 100);
```

**Weekly:**
```php
$weeklyRate = $employee->salary_amount ?? 0;
$fortnightlySalary = $weeklyRate * 2;
$regularPayCents = round($fortnightlySalary * 100);

// Calculate hourly rate for overtime
$standardHoursPerWeek = ($employee->standard_hours_per_day ?? 8) * 5; // 5 days
$effectiveHourlyRate = $weeklyRate / $standardHoursPerWeek;
$overtimePayCents = $overtimeHours > 0
    ? round($effectiveHourlyRate * $overtimeMultiplier * $overtimeHours * 100)
    : 0;
```

**Fortnightly:**
```php
$regularPayCents = round(($employee->salary_amount ?? 0) * 100);

// Calculate hourly rate for overtime
$standardHoursPerFortnight = ($employee->standard_hours_per_day ?? 8) * 10; // 10 working days
$effectiveHourlyRate = ($employee->salary_amount ?? 0) / $standardHoursPerFortnight;
$overtimePayCents = $overtimeHours > 0
    ? round($effectiveHourlyRate * $overtimeMultiplier * $overtimeHours * 100)
    : 0;
```

### Frontend Updates

**Employees.vue** (`resources/js/pages/HR/Employees.vue`)

**Updated TypeScript Interface:**
```typescript
interface EmployeeForm {
  // ... existing fields
  pay_frequency: string
  salary_amount: number | null
  standard_hours_per_day: number
  overtime_rate_multiplier: number
  clock_system_enabled: boolean
}
```

**New Compensation Section UI:**
- Radio buttons for pay frequency selection (Hourly, Weekly, Fortnightly)
- Conditional input fields based on selected frequency
- Standard hours per day input
- Overtime rate multiplier input
- Clock system enable/disable checkbox

**View Modal Updates:**
Shows all compensation details including:
- Pay frequency
- Appropriate salary/rate field
- Standard hours per day
- Overtime multiplier
- Clock system status

---

## 3. Enhanced POS Menu with HR Submenu

**Updated:** `resources/js/pages/POS/Index.vue`

**Added dropdown submenu for HR/Payroll:**
```vue
<li>
  <button @click="hrMenuOpen = !hrMenuOpen"
          class="w-full flex items-center gap-3 rounded-xl px-3 py-2">
    <i class="fas fa-users"></i>
    <span v-if="sidebarOpen">HR / Payroll</span>
    <i class="fas fa-chevron-down" :class="hrMenuOpen ? 'rotate-180' : ''"></i>
  </button>

  <ul v-if="hrMenuOpen && sidebarOpen" class="mt-1 ml-6 space-y-1">
    <li><a href="/hr/employees">Employees</a></li>
    <li><a href="/hr/departments">Departments</a></li>
    <li><a href="/hr/time-logs">Time Logs</a></li>
    <li><a href="/hr/clock">Clock In/Out</a></li>
    <li><a href="/payroll">Payroll</a></li>
  </ul>
</li>
```

Added state variable:
```typescript
const hrMenuOpen = ref(false)
```

---

## 4. Bug Fix: GenerateFortnightlyPayslips Command

**Fixed:** `app/Console/Commands/GenerateFortnightlyPayslips.php:89`

Syntax error with function call in string:
```php
// Before (syntax error):
$this->warn("⚠️  {count($result['errors'])} errors occurred:");

// After (correct):
$this->warn("⚠️  " . count($result['errors']) . " errors occurred:");
```

---

## How to Use

### 1. Create an Employee with Pay Frequency

Navigate to `/hr/employees` and click "Add Employee":

**Hourly Employee:**
- Select "Hourly" pay frequency
- Enter hourly rate (e.g., 1200 JMD/hour)
- Set standard hours per day (default: 8)
- Set overtime multiplier (default: 1.5)

**Weekly Employee:**
- Select "Weekly" pay frequency
- Enter weekly salary (e.g., 40,000 JMD/week)
- Set standard hours per day (default: 8)
- Payroll will automatically calculate fortnightly pay as weekly × 2

**Fortnightly Employee:**
- Select "Fortnightly" pay frequency
- Enter fortnightly salary (e.g., 80,000 JMD/fortnight)
- Set standard hours per day (default: 8)
- Payroll will use this fixed amount per pay period

### 2. Generate Payslips

The payroll system will now correctly calculate pay based on each employee's pay frequency:

```bash
php artisan payroll:generate-fortnightly --approve --auto-email
```

### 3. Navigate the Enhanced Menu

From the POS page:
1. Hover over sidebar to expand
2. Click "HR / Payroll" to expand submenu
3. Access any HR page directly:
   - Employees
   - Departments
   - Time Logs
   - Clock In/Out
   - Payroll

---

## Testing Checklist

- [x] Payroll period detail page loads without error
- [x] Can create hourly employee and generate correct payslip
- [x] Can create weekly employee and generate correct payslip (weekly × 2)
- [x] Can create fortnightly employee and generate correct payslip
- [x] Overtime calculated correctly for all pay frequencies
- [x] HR submenu displays all pages correctly
- [x] HR submenu toggles open/closed
- [x] Employee form shows correct fields based on pay frequency
- [x] Employee view modal displays all compensation details

---

## Database Schema

### employees table (updated)

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| pay_frequency | enum | 'hourly' | hourly, weekly, or fortnightly |
| salary_amount | decimal(10,2) | NULL | Salary amount (weekly or fortnightly) |
| hourly_rate | decimal(10,2) | NULL | Hourly rate for hourly employees |
| standard_hours_per_day | decimal(5,2) | 8.00 | Hours per day for overtime calculation |
| overtime_rate_multiplier | decimal(3,2) | 1.5 | Overtime multiplier (1.5 = time-and-a-half) |
| clock_system_enabled | boolean | true | Enable/disable clock in/out for employee |

---

## Payroll Calculation Examples

### Example 1: Hourly Employee
- Pay frequency: Hourly
- Hourly rate: 1,200 JMD
- Regular hours: 80
- Overtime hours: 10
- Overtime multiplier: 1.5

**Calculation:**
- Regular pay: 1,200 × 80 = 96,000 JMD
- Overtime pay: 1,200 × 1.5 × 10 = 18,000 JMD
- **Total: 114,000 JMD**

### Example 2: Weekly Employee
- Pay frequency: Weekly
- Weekly salary: 40,000 JMD
- Standard hours/day: 8 (40 hours/week)
- Overtime hours: 10
- Overtime multiplier: 2.0

**Calculation:**
- Regular pay: 40,000 × 2 weeks = 80,000 JMD
- Effective hourly rate: 40,000 ÷ 40 = 1,000 JMD/hour
- Overtime pay: 1,000 × 2.0 × 10 = 20,000 JMD
- **Total: 100,000 JMD**

### Example 3: Fortnightly Employee
- Pay frequency: Fortnightly
- Fortnightly salary: 85,000 JMD
- Standard hours/day: 8 (80 hours/fortnight)
- Overtime hours: 5
- Overtime multiplier: 1.5

**Calculation:**
- Regular pay: 85,000 JMD
- Effective hourly rate: 85,000 ÷ 80 = 1,062.50 JMD/hour
- Overtime pay: 1,062.50 × 1.5 × 5 = 7,968.75 JMD
- **Total: 92,968.75 JMD**

---

## Files Modified

### Backend
- `app/Http/Controllers/Api/PayrollController.php` - Added showPeriod method
- `app/Http/Controllers/Api/EmployeeController.php` - Updated validation and defaults
- `app/Services/PayrollService.php` - Added pay frequency logic
- `app/Console/Commands/GenerateFortnightlyPayslips.php` - Fixed syntax error
- `routes/api.php` - Added period detail route
- `database/migrations/2025_10_20_220044_add_pay_frequency_to_employees_table.php` - New migration

### Frontend
- `resources/js/pages/POS/Index.vue` - Added HR submenu
- `resources/js/pages/HR/Employees.vue` - Updated forms and views

---

## System Status

✅ All features implemented and tested
✅ Database migrations run successfully
✅ Frontend rebuilt and running
✅ All API endpoints working correctly

**Ready for production use!**
