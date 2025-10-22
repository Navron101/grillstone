# Payslip PDF Styling Update

## Changes Made

### 1. Updated Color Scheme to Grillstone Brand Colors

Changed from generic green theme to Grillstone's signature orange color palette:

**Before (Green):**
- Primary color: #059669 (emerald green)
- Background: #f0fdf4 (light green)
- Borders: #bbf7d0 (green)

**After (Orange - Grillstone Brand):**
- Primary color: #ea580c (vibrant orange)
- Secondary: #c2410c (darker orange)
- Background: #ffedd5 (light orange/peach)
- Borders: #fed7aa (orange)

### 2. Fixed Layout Issues

**Problem:** Values were being cut off on the right side of the PDF

**Solutions Applied:**

1. **Increased info label width:**
   ```css
   .info-label {
       width: 150px; /* was 120px */
   }
   ```

2. **Added word-wrap to values:**
   ```css
   .info-value {
       word-wrap: break-word;
   }
   ```

3. **Defined specific column widths:**
   ```css
   .col-description {
       width: 50%;
   }
   .col-hours {
       width: 20%;
       text-align: center;
   }
   .col-amount {
       width: 30%;
       text-align: right;
       white-space: nowrap; /* Prevents amount wrapping */
   }
   ```

4. **Applied column classes to all table cells:**
   - Description column: 50% width
   - Hours/Rate column: 20% width, centered
   - Amount column: 30% width, right-aligned, no-wrap

### 3. Visual Elements Updated

**Header:**
- Company name: Orange (#ea580c)
- Border: Orange (#ea580c)
- Payslip title: Orange (#ea580c)

**Period Box:**
- Background: Light orange (#ffedd5)
- Left border: Orange (#ea580c)
- Labels: Orange (#ea580c)

**Tables:**
- Headers: Light orange background (#ffedd5), orange text
- Section headers: Orange background (#ea580c), white text
- Total rows: Light orange background (#ffedd5)
- Border on total rows: Orange (#ea580c)

**Net Pay Box:**
- Gradient: Orange to darker orange (#ea580c → #c2410c)
- Large, prominent display
- White text

### 4. Layout Improvements

- All currency amounts now properly aligned to the right
- No values cut off or truncated
- Consistent spacing and padding
- Professional appearance

---

## Color Reference

### Grillstone Brand Colors Used

| Element | Color | Hex Code |
|---------|-------|----------|
| Primary Orange | Vibrant Orange | `#ea580c` |
| Dark Orange | Darker Shade | `#c2410c` |
| Light Orange | Background/Highlights | `#ffedd5` |
| Orange Border | Light Border | `#fed7aa` |

These match the colors used in the Grillstone POS interface.

---

## PDF Structure

### Header Section
- ✅ Company name (GRILLSTONE) in large orange text
- ✅ Company address and contact info
- ✅ "EMPLOYEE PAYSLIP" title
- ✅ Orange bottom border

### Period Information
- ✅ Light orange box with orange left border
- ✅ Pay period dates
- ✅ Pay date
- ✅ Orange labels

### Employee Information
- ✅ 2-column grid layout
- ✅ Employee name, number, position, department
- ✅ TRN and NIS numbers
- ✅ Wider labels (150px) to prevent overlap

### Earnings Table
- ✅ Orange header bar
- ✅ Light orange column headers
- ✅ Regular pay, overtime pay, bonuses
- ✅ Hours worked displayed
- ✅ All amounts right-aligned with no wrapping
- ✅ Gross pay total row (orange border)

### Deductions Table
- ✅ Orange header bar
- ✅ NIS (3%), NHT (2%), Education Tax (2.25%)
- ✅ PAYE (income tax)
- ✅ Rate percentages displayed
- ✅ Total deductions row (orange border)

### Net Pay Display
- ✅ Large prominent box
- ✅ Orange gradient background
- ✅ White text
- ✅ 24pt font size for amount
- ✅ Rounded corners with shadow

### Footer
- ✅ Generated timestamp
- ✅ Professional note

---

## Example Payslip Preview

```
╔═══════════════════════════════════════════════════════════════╗
║                       GRILLSTONE                              ║
║           123 Kingston Road, Kingston, Jamaica                ║
║         Phone: (876) 555-1234 | Email: payroll@...          ║
║                   EMPLOYEE PAYSLIP                            ║
╠═══════════════════════════════════════════════════════════════╣
║ Pay Period: Oct 20 - Nov 02, 2025  |  Pay Date: Nov 04, 2025 ║
╠═══════════════════════════════════════════════════════════════╣
║ Employee Name: Norvan Martin    Employee Number: 1           ║
║ Position: Chef                  Department: Kitchen           ║
║ TRN: 123-456-789               NIS: 987-654-321              ║
╠═══════════════════════════════════════════════════════════════╣
║                         EARNINGS                              ║
╠═══════════════════════════════════════════════════════════════╣
║ Description          | Hours      | Amount                   ║
║ Regular Pay          | 80.00      | JMD 96,000.00           ║
║ Overtime Pay (1.5x)  | 10.00      | JMD 18,000.00           ║
║ GROSS PAY                        | JMD 114,000.00           ║
╠═══════════════════════════════════════════════════════════════╣
║                       DEDUCTIONS                              ║
╠═══════════════════════════════════════════════════════════════╣
║ Description          | Rate       | Amount                   ║
║ NIS                  | 3%         | JMD 3,420.00            ║
║ NHT                  | 2%         | JMD 2,280.00            ║
║ Education Tax        | 2.25%      | JMD 2,565.00            ║
║ PAYE                 | -          | JMD 14,076.00           ║
║ TOTAL DEDUCTIONS                 | JMD 22,341.00           ║
╠═══════════════════════════════════════════════════════════════╣
║                                                               ║
║                      NET PAY                                  ║
║                   JMD 91,659.00                              ║
║                                                               ║
╠═══════════════════════════════════════════════════════════════╣
║         This is a computer-generated payslip.                 ║
║         Generated on: Oct 21, 2025 10:51 AM                  ║
╚═══════════════════════════════════════════════════════════════╝
```

---

## Files Modified

- `resources/views/pdf/payslip.blade.php` (lines 28-236)
  - Updated all color references
  - Added column width classes
  - Fixed layout spacing
  - Applied brand colors throughout

---

## Testing the Updated PDF

### Download a Payslip:

**Via Web Interface:**
1. Go to `/payroll`
2. Click on a period
3. Click the green download icon 📥 next to any payslip
4. PDF will open/download with new Grillstone orange styling

**Via API:**
```bash
curl -O -J http://localhost:8000/api/payroll/periods/2/payslips/3/pdf
```

**Expected Result:**
- PDF downloads successfully
- All text visible and properly aligned
- Orange Grillstone branding throughout
- No values cut off on the right
- Professional, clean appearance

---

## Customization Guide

### To Change Company Information:

Edit `resources/views/pdf/payslip.blade.php` around line 227:

```html
<div class="company-name">YOUR COMPANY NAME</div>
<div class="company-address">
    Your Address Here<br>
    Phone: Your Phone | Email: Your Email
</div>
```

### To Add Company Logo:

Replace the text header (around line 227) with:

```html
<div style="text-align: center; margin-bottom: 15px;">
    <img src="{{ public_path('images/grillstone-logo.png') }}"
         alt="Grillstone Logo"
         style="max-width: 200px; height: auto;">
</div>
<div class="company-address">
    Your address here
</div>
```

Then place your logo at `public/images/grillstone-logo.png`.

### To Adjust Colors:

All Grillstone orange colors are in the CSS section (lines 28-220). To change:

```css
/* Find and replace these values: */
#ea580c  /* Main orange */
#c2410c  /* Dark orange */
#ffedd5  /* Light orange background */
#fed7aa  /* Orange borders */
```

---

## System Status

✅ Payslip colors updated to Grillstone brand
✅ Layout issues fixed - all values visible
✅ Column widths properly defined
✅ Professional appearance maintained
✅ PDF generation working correctly

**Ready for production use!**
