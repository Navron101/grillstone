# HR & Payroll System - Implementation Summary

## Overview
A comprehensive HR and Payroll management system for Grillstone POS with full employee lifecycle management, time tracking, and automated fortnightly payroll processing with Jamaican statutory deductions.

## Database Schema

### Tables Created

#### 1. **departments**
- Organizational units for employee grouping
- Fields: id, name, description, is_active, timestamps

#### 2. **employees**
- Complete employee records with all HR data
- Fields include:
  - **Personal**: first_name, last_name, email, phone, employee_number
  - **Tax Info**: trn (Tax Registration Number), nis (National Insurance)
  - **Address**: address, city, parish
  - **Employment**: department_id, position, hire_date, termination_date, employment_type, employment_status
  - **Compensation**: hourly_rate, salary_per_period, is_salaried
  - **Banking**: bank_name, bank_account, bank_branch
  - **Emergency Contact**: emergency_contact_name, emergency_contact_phone, emergency_contact_relationship
  - **Meta**: notes, is_active, timestamps, soft deletes

#### 3. **time_logs**
- Track employee work hours for payroll
- Fields: employee_id, work_date, clock_in, clock_out, regular_hours, overtime_hours, status (pending/approved/rejected), notes, approved_by, approved_at, timestamps
- Supports both clock-in/out and manual hour entry
- Requires approval before payroll processing

#### 4. **payroll_periods**
- Fortnightly pay periods
- Fields: start_date, end_date, pay_date, status (draft/processing/approved/paid), notes, processed_by, processed_at, timestamps

#### 5. **payslips**
- Individual employee payslips per period
- **Hours**: regular_hours, overtime_hours
- **Earnings** (in cents): regular_pay_cents, overtime_pay_cents, bonus_cents, gross_pay_cents
- **Statutory Deductions** (in cents):
  - nis_cents (3% National Insurance)
  - nht_cents (2% National Housing Trust)
  - education_tax_cents (2.25% Education Tax)
  - paye_cents (Pay As You Earn income tax)
- **Other**: other_deductions_cents, total_deductions_cents, net_pay_cents
- **Status**: draft/approved/sent/paid with timestamps

## API Endpoints

### Departments
- `GET /api/departments` - List all departments
- `POST /api/departments` - Create department
- `PUT /api/departments/{id}` - Update department
- `DELETE /api/departments/{id}` - Delete department

### Employees
- `GET /api/employees` - List all employees with department info
- `GET /api/employees/{id}` - Get employee details
- `POST /api/employees` - Create employee (auto-generates employee_number)
- `PUT /api/employees/{id}` - Update employee
- `DELETE /api/employees/{id}` - Soft delete employee

### Time Logs
- `GET /api/time-logs` - List time logs (filterable by employee, date range, status)
- `POST /api/time-logs` - Create time log
- `PUT /api/time-logs/{id}` - Update time log
- `DELETE /api/time-logs/{id}` - Delete time log
- `POST /api/time-logs/{id}/approve` - Approve time log
- `POST /api/time-logs/{id}/reject` - Reject time log
- `POST /api/time-logs/bulk-approve` - Bulk approve multiple logs

### Payroll
- `GET /api/payroll/periods` - List all payroll periods
- `POST /api/payroll/periods` - Create new period
- `POST /api/payroll/periods/{id}/generate` - Generate payslips for all employees
- `GET /api/payroll/periods/{periodId}/payslips` - List payslips for period
- `GET /api/payroll/periods/{periodId}/payslips/{payslipId}` - Get payslip details
- `PUT /api/payroll/periods/{periodId}/payslips/{payslipId}` - Update payslip (bonus, deductions)
- `POST /api/payroll/periods/{id}/approve` - Approve all payslips
- `POST /api/payroll/periods/{id}/email` - Email payslips to all employees
- `POST /api/payroll/periods/{id}/mark-paid` - Mark period as paid

## Web Routes (Frontend Pages)
- `/hr` - HR Dashboard
- `/hr/employees` - Employee List
- `/hr/employees/{id}` - Employee Detail
- `/hr/departments` - Department Management
- `/hr/time-logs` - Time Tracking
- `/payroll` - Payroll Dashboard
- `/payroll/periods/{id}` - Period Detail with Payslips

## Key Features

### 1. Employee Management
- Auto-generated employee numbers (EMP-001, EMP-002, etc.)
- Full employee lifecycle (hire to termination)
- Support for hourly and salaried employees
- Department assignment
- Comprehensive personal and employment data
- Soft deletes (maintain historical data)

### 2. Time Tracking
- Clock in/out or manual hour entry
- Automatic overtime calculation (> 8 hours = overtime)
- Approval workflow (pending → approved/rejected)
- Bulk approval functionality
- Filter by employee, date range, and status

### 3. Payroll Processing
- **Fortnightly pay periods**
- **Automated payslip generation** for all active employees
- **Jamaican statutory deductions** (NIS, NHT, Education Tax, PAYE)
- **PAYE calculation** with progressive tax brackets
- Manual adjustments (bonuses, other deductions)
- Status workflow: draft → processing → approved → sent → paid

### 4. Statutory Calculations (Jamaica)
- **NIS (National Insurance)**: 3% of gross pay
- **NHT (National Housing Trust)**: 2% of gross pay
- **Education Tax**: 2.25% of gross pay
- **PAYE (Income Tax)**:
  - Annual threshold: JMD 1,500,096 (JMD 57,696 fortnightly)
  - First JMD 6,000,000: 25%
  - Above JMD 6,000,000: 30%

### 5. Email Notifications
- Automated payslip emails on fortnightly basis
- Plain text format with full breakdown:
  - Hours worked (regular & overtime)
  - Gross pay
  - All deductions
  - Net pay
- Sent to employee email addresses
- Tracks sent status and timestamp

### 6. Compensation Models
- **Hourly employees**: hourly_rate × hours worked
- **Salaried employees**: fixed salary_per_period
- **Overtime**: 1.5× rate for both types
- **Bonuses**: Manual per-payslip additions

## Service Layer

### PayrollService (`app/Services/PayrollService.php`)
Core business logic for payroll calculations:

#### Methods:
- `calculatePayslip($employeeId, $payrollPeriodId)` - Calculate single payslip
- `generatePayslipsForPeriod($payrollPeriodId)` - Generate for all employees
- `calculatePAYE($grossPayFortnightly)` - Jamaica income tax calculation

#### Calculation Logic:
1. Fetch approved time logs for period
2. Calculate regular and overtime hours
3. Calculate gross pay (hourly or salaried)
4. Calculate statutory deductions (NIS, NHT, Education Tax, PAYE)
5. Calculate net pay
6. Return payslip data array

## Controllers

### 1. **EmployeeController**
Full CRUD for employees with:
- Auto-generation of employee numbers
- Validation for all fields
- Department join on list/show
- Soft delete implementation

### 2. **DepartmentController**
Department management with:
- Prevent deletion if has employees
- Simple CRUD operations

### 3. **TimeLogController**
Time tracking with:
- Automatic hour calculation from clock times
- Approval/rejection workflow
- Bulk operations
- Filtering capabilities

### 4. **PayrollController**
Comprehensive payroll management with:
- Period creation and management
- Payslip generation using PayrollService
- Approval workflow
- Email distribution
- Manual adjustments

## Workflow Example: Fortnightly Payroll

### Step 1: Time Entry (Throughout Period)
```
POST /api/time-logs
{
  "employee_id": 1,
  "work_date": "2025-10-20",
  "clock_in": "09:00",
  "clock_out": "17:30",
  "notes": "Regular shift"
}
```

### Step 2: Approve Time Logs
```
POST /api/time-logs/bulk-approve
{
  "ids": [1, 2, 3, 4, 5]
}
```

### Step 3: Create Payroll Period
```
POST /api/payroll/periods
{
  "start_date": "2025-10-07",
  "end_date": "2025-10-20",
  "pay_date": "2025-10-22"
}
```

### Step 4: Generate Payslips
```
POST /api/payroll/periods/1/generate
```
Response shows generated count and any errors.

### Step 5: Review & Adjust (Optional)
```
PUT /api/payroll/periods/1/payslips/5
{
  "bonus_cents": 5000,
  "notes": "Performance bonus"
}
```

### Step 6: Approve Period
```
POST /api/payroll/periods/1/approve
```

### Step 7: Email Payslips
```
POST /api/payroll/periods/1/email
```
Emails are sent to all employees, status updated to 'sent'.

### Step 8: Mark as Paid
```
POST /api/payroll/periods/1/mark-paid
```

## Frontend Pages (To Be Built)

The following Vue/Inertia pages need to be created to complete the system:

### HR Module
1. **HR/Index.vue** - Dashboard with employee count, department breakdown, active employees
2. **HR/Employees.vue** - Employee list with search, filter, add employee
3. **HR/EmployeeDetail.vue** - Single employee view/edit with all details
4. **HR/Departments.vue** - Department management (similar to Categories.vue)
5. **HR/TimeLogs.vue** - Time log entry, approval interface

### Payroll Module
1. **Payroll/Index.vue** - List of payroll periods, create new period
2. **Payroll/PeriodDetail.vue** - View payslips, generate, approve, email

## Email Configuration

Ensure your `.env` has mail configuration:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@grillstone.com
MAIL_FROM_NAME="Grillstone Management"
```

For production, use a real SMTP service (SendGrid, Mailgun, AWS SES, etc.)

## Testing the System

### 1. Create Departments
```bash
curl -X POST http://localhost:8000/api/departments \
  -H "Content-Type: application/json" \
  -d '{"name": "Kitchen", "description": "Kitchen staff"}'
```

### 2. Create Employees
```bash
curl -X POST http://localhost:8000/api/employees \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Smith",
    "email": "john.smith@example.com",
    "phone": "876-555-1234",
    "position": "Chef",
    "hire_date": "2025-01-15",
    "department_id": 1,
    "hourly_rate": 1200.00,
    "is_salaried": false,
    "trn": "123-456-789",
    "nis": "987654321"
  }'
```

### 3. Log Time
```bash
curl -X POST http://localhost:8000/api/time-logs \
  -H "Content-Type: application/json" \
  -d '{
    "employee_id": 1,
    "work_date": "2025-10-20",
    "regular_hours": 8,
    "overtime_hours": 2
  }'
```

### 4. Run Payroll
See workflow example above.

## Security Notes

- All routes currently unauthenticated (as per existing pattern)
- Add `auth` middleware for production
- Consider role-based access (only HR/Managers can approve, run payroll)
- Payslip data contains sensitive information - secure appropriately

## Future Enhancements

1. **Leave Management** - Vacation, sick leave tracking
2. **Performance Reviews** - Employee evaluation system
3. **Document Storage** - Upload contracts, certifications
4. **Payslip PDFs** - Generate PDF payslips instead of plain text
5. **Attendance Kiosk** - Clock in/out interface for employees
6. **Scheduled Payroll** - Automatic fortnightly generation via Laravel scheduler
7. **Tax Filing** - Generate statutory filing reports (NIS, NHT, PAYE)
8. **Multi-location** - Support for multiple restaurant locations
9. **Employee Self-Service** - Portal for employees to view payslips, request leave

## Files Created

### Migrations
- `2025_10_20_145439_create_departments_table.php`
- `2025_10_20_145439_create_employees_table.php`
- `2025_10_20_145439_create_time_logs_table.php`
- `2025_10_20_145439_create_payroll_periods_table.php`
- `2025_10_20_145439_create_payslips_table.php`

### Services
- `app/Services/PayrollService.php`

### Controllers
- `app/Http/Controllers/Api/DepartmentController.php`
- `app/Http/Controllers/Api/EmployeeController.php`
- `app/Http/Controllers/Api/TimeLogController.php`
- `app/Http/Controllers/Api/PayrollController.php`

### Routes
- Updated `routes/api.php` with HR & Payroll endpoints
- Updated `routes/web.php` with HR & Payroll page routes

## Next Steps

1. **Build Frontend Pages** - Create the Vue components listed above
2. **Test Email** - Configure mail settings and test payslip emails
3. **Add Sample Data** - Create seed data for testing
4. **Add Authentication** - Protect routes with auth middleware
5. **Scheduled Jobs** - Set up automatic payroll generation every fortnight
6. **PDF Generation** - Implement PDF payslips using Laravel Snappy or similar

---

**System Status**: Backend Complete ✅ | Frontend Pending 🔨 | Email Configured Pending ⚙️
