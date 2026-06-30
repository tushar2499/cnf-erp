# NAS Group ERP

A multi-company ERP system built for **NAS Group**, managing three business entities from a single codebase with shared authentication and company-scoped access control.

---

## Companies

| Company | Type | Slug | Description |
|---|---|---|---|
| **Chevron Lines Ltd.** | C&F Agent (CNF) | `chevron` | Customs & freight clearing, job expenses, billing |
| **NAS Freights & Logistics** | Freight / Transport | `nas-freights` | Booking management, supplier/customer billing |
| **NAS Trading** | Trading / Import | `nas-trading` | LC management, shipments, customer bills |

---

## Tech Stack

- **Backend:** PHP 8.2 / Laravel 12
- **Frontend:** Bootstrap 5, jQuery, DataTables (server-side), SweetAlert2, Select2
- **Database:** MySQL
- **Auth:** Custom session-based (no Laravel Breeze/Jetstream)
- **PDF:** DomPDF (via `barryvdh/laravel-dompdf`)
- **Excel:** PhpSpreadsheet (via `maatwebsite/excel`)

---

## Key Features

### Chevron Lines (C&F)
- C&F Job management (create, edit, show)
- Job Expense tracking with expense heads and categories
- Customer billing & money receipts
- **Job Expense Summary Report** (filterable by date, job no, employee)
- Branch-scoped access

### NAS Freights & Logistics
- Booking management with cover van / supplier / customer linking
- Supplier bills & customer bills
- **Booking Report** with billed status and bill number
- Print / PDF / Excel export

### NAS Trading
- Employee, department, expense head master data
- LC entry and shipment tracking *(in progress)*

### Shared / Cross-Company
- Single `users` table with `company_user` pivot (role, status, employee link)
- User CRUD per company with employee assignment (Select2)
- Employee sync across all 3 company tables from a single import
- Company/branch session switching

---

## Project Structure

```
app/
  Http/Controllers/
    Chevron/          # Chevron Lines controllers
    NasFreights/      # NAS Freights controllers
    NasTrading/       # NAS Trading controllers
    Shared/           # Abstract base controllers (UserManagementController)
  Models/
    Chevron/          # ChevronJob, ChevronEmployee, ChevronJobExpense, ...
    NasFreights/      # NasFreightsBooking, NasFreightsEmployee, ...
    NasTrading/       # NasTradingEmployee, NasTradingExpenseHead, ...
routes/
  chevron.php
  nas-freights.php
  nas-trading.php
resources/views/
  chevron/
  nas-freights/
  nas-trading/
  shared/             # Shared Blade templates (users, etc.)
```

---

## Setup

```bash
git clone https://github.com/tushar2499/cnf-erp.git
cd cnf-erp
composer install
cp .env.example .env
php artisan key:generate
# Configure DB in .env
php artisan migrate
php artisan storage:link
```

---

## Development

```bash
php artisan serve
```

Access at `http://localhost:8000`. Select company after login.

---

## Built By

**Advertising For Business – A4B**
