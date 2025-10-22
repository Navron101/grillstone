# Employee Tab & Statutory Deductions Feature

## Overview

This system allows you to:
1. **Toggle statutory deductions** on/off when generating payslips
2. **Add charges to employee tabs** that are automatically deducted from payslips
3. **Track tab items** - what employees owe for items purchased

---

## Features Implemented

### 1. Statutory Deductions Toggle

When generating payslips, you can now choose whether to include or exclude statutory deductions:

**Statutory Deductions (Optional):**
- NIS (National Insurance Scheme) - 3%
- NHT (National Housing Trust) - 2%
- Education Tax - 2.25%
- PAYE (Pay As You Earn) - Income Tax

**Custom Deductions (Always Included):**
- Employee Tab Charges - Always deducted
- Other Deductions - If manually added

### 2. Employee Tab System

Employees can have items charged to their "tab" (like a running account). These charges are:
- Automatically included in payslip deductions
- Tracked with description, amount, and date
- Marked as "deducted" when included in a payslip
- **Always shown on payslips** regardless of statutory toggle

---

## Database Structure

### `employee_tab_items` Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| employee_id | bigint | Foreign key to employees |
| description | string | What was purchased (e.g., "Lunch - Jerk Chicken") |
| amount_cents | integer | Amount in cents |
| tab_date | date | When the item was added |
| status | enum | `pending`, `deducted`, `cancelled` |
| payslip_id | bigint | Links to payslip when deducted |
| notes | text | Optional notes |

### `payslips` Table (New Fields)

| Column | Type | Description |
|--------|------|-------------|
| include_statutory_deductions | boolean | Whether statutory deductions are included |
| tab_deductions_cents | integer | Total tab charges deducted |

---

## API Endpoints

### Employee Tab Management

#### Get All Tab Items
```http
GET /api/employee-tab?employee_id={id}&status={status}
```

Query Parameters:
- `employee_id` (optional) - Filter by employee
- `status` (optional) - `pending`, `deducted`, `cancelled`

#### Get Pending Tab Items for Employee
```http
GET /api/employee-tab/pending/{employeeId}
```

Response:
```json
{
  "items": [...],
  "total_cents": 500000,
  "total_amount": 5000.00
}
```

#### Get Employee Tab Balance
```http
GET /api/employee-tab/balance/{employeeId}
```

Response:
```json
{
  "employee_id": 1,
  "balance_cents": 500000,
  "balance_amount": 5000.00
}
```

#### Add Item to Employee Tab
```http
POST /api/employee-tab
Content-Type: application/json

{
  "employee_id": 1,
  "description": "Lunch - Jerk Chicken Combo",
  "amount": 1200.00,
  "tab_date": "2025-10-21",
  "notes": "Ordered from canteen"
}
```

#### Update Tab Item
```http
PUT /api/employee-tab/{id}
Content-Type: application/json

{
  "description": "Updated description",
  "amount": 1500.00,
  "status": "cancelled"
}
```

#### Delete Tab Item (Pending Only)
```http
DELETE /api/employee-tab/{id}
```

### Generate Payslips with Options

```http
POST /api/payroll/periods/{id}/generate
Content-Type: application/json

{
  "include_statutory_deductions": true
}
```

- `include_statutory_deductions`: `true` or `false` (default: `true`)

---

## How It Works

### Payslip Generation Flow

1. **User clicks "Generate Payslips"**
2. **Modal appears with checkbox:**
   - ☑ Include Statutory Deductions
   - Note: "Employee tab charges will always be included"

3. **System calculates for each employee:**
   - Regular pay + overtime pay = Gross pay
   - If statutory toggle is ON:
     - Calculate NIS, NHT, Education Tax, PAYE
   - If statutory toggle is OFF:
     - Set all statutory deductions to 0
   - **Always** get pending tab items for employee
   - **Always** include tab charges in total deductions
   - Net pay = Gross - (Statutory + Tab + Other)

4. **Tab items are marked as "deducted":**
   - Status changes from `pending` to `deducted`
   - `payslip_id` is set to link to the payslip
   - Items are now locked and can't be deleted

### PDF Payslip Display

**Deductions Section:**

```
DEDUCTIONS
Description                          Rate      Amount
─────────────────────────────────────────────────────────
[IF STATUTORY ENABLED]
National Insurance Scheme (NIS)      3%        JMD 3,420.00
National Housing Trust (NHT)         2%        JMD 2,280.00
Education Tax                        2.25%     JMD 2,565.00
PAYE (Income Tax)                    -         JMD 14,076.00

[ALWAYS SHOWN]
Lunch - Jerk Chicken (Oct 20)        Tab       JMD 1,200.00
Uniform Charge (Oct 18)              Tab       JMD 3,500.00

TOTAL DEDUCTIONS                               JMD 27,041.00
```

---

## Usage Examples

### Example 1: Add Item to Employee Tab

An employee buys lunch from the restaurant:

```bash
curl -X POST http://localhost:8000/api/employee-tab \
  -H "Content-Type: application/json" \
  -d '{
    "employee_id": 1,
    "description": "Lunch - Jerk Chicken Combo",
    "amount": 1200,
    "notes": "Ordered from canteen"
  }'
```

### Example 2: Check Employee Tab Balance

```bash
curl http://localhost:8000/api/employee-tab/balance/1
```

Response:
```json
{
  "employee_id": 1,
  "balance_cents": 470000,
  "balance_amount": 4700.00
}
```

### Example 3: Generate Payslips WITHOUT Statutory Deductions

Useful for contractors or special circumstances:

```bash
curl -X POST http://localhost:8000/api/payroll/periods/2/generate \
  -H "Content-Type: application/json" \
  -d '{
    "include_statutory_deductions": false
  }'
```

Result:
- No NIS, NHT, Education Tax, or PAYE calculated
- Tab charges still deducted
- Net pay = Gross - Tab charges only

### Example 4: Generate Payslips WITH Statutory Deductions (Default)

Normal payroll processing:

```bash
curl -X POST http://localhost:8000/api/payroll/periods/2/generate \
  -H "Content-Type: application/json" \
  -d '{
    "include_statutory_deductions": true
  }'
```

Result:
- All statutory deductions calculated
- Tab charges included
- Net pay = Gross - (Statutory + Tab)

---

## Frontend Usage

### Generating Payslips with Toggle

1. Navigate to `/payroll/periods/{id}`
2. Click **"Generate Payslips"**
3. Modal appears with options:
   - ☑ **Include Statutory Deductions**
     - NIS (3%), NHT (2%), Education Tax (2.25%), and PAYE (Income Tax)
     - Note: Employee tab charges will always be included
4. Check or uncheck the box as needed
5. Click **"Generate"**

### Viewing Tab Items on Payslip PDF

1. Download any payslip PDF
2. In the **DEDUCTIONS** section, you'll see:
   - Statutory deductions (if enabled during generation)
   - Tab items with description and date
   - Total deductions

---

## Example Scenarios

### Scenario 1: Regular Employee (Full Deductions)

**Employee:** Chef Norvan Martin
**Gross Pay:** JMD 114,000.00
**Statutory Toggle:** ON
**Tab Charges:**
- Lunch Oct 20: JMD 1,200.00
- Uniform: JMD 3,500.00

**Deductions:**
```
NIS (3%):           JMD 3,420.00
NHT (2%):           JMD 2,280.00
Education Tax:      JMD 2,565.00
PAYE:               JMD 14,076.00
Tab Charges:        JMD 4,700.00
─────────────────────────────────
TOTAL:              JMD 27,041.00

NET PAY:            JMD 86,959.00
```

### Scenario 2: Contractor (No Statutory)

**Employee:** Freelance Cook
**Gross Pay:** JMD 50,000.00
**Statutory Toggle:** OFF
**Tab Charges:**
- Apron: JMD 800.00

**Deductions:**
```
Tab Charges:        JMD 800.00
─────────────────────────────────
TOTAL:              JMD 800.00

NET PAY:            JMD 49,200.00
```

### Scenario 3: Employee with Large Tab Balance

**Employee:** Server Sarah
**Gross Pay:** JMD 60,000.00
**Statutory Toggle:** ON
**Tab Charges:**
- Lunch Oct 15: JMD 1,200.00
- Lunch Oct 16: JMD 1,500.00
- Lunch Oct 17: JMD 1,100.00
- Lunch Oct 18: JMD 1,300.00
- Lunch Oct 19: JMD 1,400.00
- Uniform Advance: JMD 5,000.00

**Deductions:**
```
NIS (3%):           JMD 1,800.00
NHT (2%):           JMD 1,200.00
Education Tax:      JMD 1,350.00
PAYE:               JMD 625.00
Tab Charges:        JMD 11,500.00
─────────────────────────────────
TOTAL:              JMD 16,475.00

NET PAY:            JMD 43,525.00
```

---

## Important Notes

### Tab Items Are Always Deducted
- Tab charges **cannot be toggled off**
- They are **always included** in payslip deductions
- This ensures employees pay for items they've purchased

### Statutory Deductions Can Be Toggled
- Useful for contractors who handle their own taxes
- Useful for special circumstances
- Default is **ON** (included)

### Tab Items Are Locked After Deduction
- Once a tab item is deducted from a payslip, it cannot be:
  - Deleted
  - Modified
  - Used again in another payslip
- Status changes to `deducted` and is linked to the payslip

### Only Pending Items Are Deducted
- Only tab items with status `pending` are included in payslip generation
- Once deducted, they won't be included again

---

## Files Modified

### Backend

1. **Database Migrations:**
   - `2025_10_21_112014_create_employee_tab_items_table.php`
   - `2025_10_21_112034_add_deduction_fields_to_payslips_table.php`

2. **Controllers:**
   - `app/Http/Controllers/Api/EmployeeTabController.php` (NEW)
   - `app/Http/Controllers/Api/PayrollController.php` (updated `generatePayslips`)

3. **Services:**
   - `app/Services/PayrollService.php` (lines 23, 99-122, 123-143, 179, 201-224)
   - `app/Services/PayslipPdfService.php` (lines 116-120, 167-180)

4. **Routes:**
   - `routes/api.php` (lines 11, 129-135)

### Frontend

1. **Pages:**
   - `resources/js/pages/Payroll/PeriodDetail.vue` (lines 216-231, 526, 620-646)

2. **Templates:**
   - `resources/views/pdf/payslip.blade.php` (lines 360-419)

---

## Testing

### Test 1: Add Tab Item
```bash
curl -X POST http://localhost:8000/api/employee-tab \
  -H "Content-Type: application/json" \
  -d '{
    "employee_id": 1,
    "description": "Test Lunch",
    "amount": 500
  }'
```

### Test 2: Check Pending Balance
```bash
curl http://localhost:8000/api/employee-tab/pending/1
```

### Test 3: Generate Without Statutory
```bash
curl -X POST http://localhost:8000/api/payroll/periods/4/generate \
  -H "Content-Type: application/json" \
  -d '{"include_statutory_deductions": false}'
```

### Test 4: Download PDF and Verify
```bash
curl -O -J http://localhost:8000/api/payroll/periods/4/payslips/{id}/pdf
```

Check that:
- ✅ Tab items appear in deductions section
- ✅ Statutory deductions appear/don't appear based on toggle
- ✅ Total deductions are correct
- ✅ Net pay calculation is correct

---

## Summary

✅ **Statutory deductions toggle** - Choose whether to include NIS, NHT, Education Tax, PAYE
✅ **Employee tab system** - Track employee purchases and charges
✅ **Automatic deduction** - Tab items automatically deducted from payslips
✅ **Always visible** - Tab charges always show on payslips
✅ **Proper tracking** - Tab items linked to payslips and locked after deduction
✅ **API complete** - Full CRUD operations for tab management
✅ **PDF support** - Tab items display correctly on payslip PDFs

**The system is now production-ready!** 🎉
