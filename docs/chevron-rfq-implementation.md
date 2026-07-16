# C&F RFQ Feature — Implementation Plan (Chevron Lines)

## Overview

Pre-job quotation module for Chevron Lines. Allows sales to quote freight + customs costs to prospects, track win/lose, and convert won quotes into ChevronJobs.

Research basis: CargoWise, Magaya, SAP TM, GoFreight, Descartes.

---

## Database Schema (2 tables)

### Table 1: `chevron_rfqs`

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| rfq_no | string unique | Auto: `RFQ000001` |
| branch_id | FK | `session('active_branch_id')` scope |
| customer_id | FK → chevron_customers | |
| rfq_date | date | |
| valid_until | date nullable | Default +30 days |
| type | enum('import','export') | |
| service_type | enum('FCL','LCL','Air','Truck') | |
| incoterms | string nullable | EXW/FOB/CIF/DDP/DAP/etc |
| currency | string default 'BDT' | USD/EUR/BDT etc |
| pol_id | FK → chevron_ports nullable | Port of Loading |
| pod_id | FK → chevron_ports nullable | Port of Discharge |
| place_of_receipt | string nullable | Pre-carriage origin |
| place_of_delivery | string nullable | Final destination |
| commodity_description | text nullable | General cargo description |
| remarks | text nullable | |
| status | enum('Draft','Pending','Win','Lose') default 'Draft' | |
| lost_reason | string nullable | Price/Transit/Relationship/Other |
| converted_job_id | FK → chevron_jobs nullable | Set on Win → Convert to Job |
| salesperson_id | FK → chevron_employees nullable | |
| timestamps | | |

### Table 2: `chevron_rfq_items` (cargo lines)

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| rfq_id | FK → chevron_rfqs | |
| item_type | enum('container','package') | |
| container_size | string nullable | 20GP/40GP/40HC/40RF/45HC |
| package_type | string nullable | Carton/Pallet/Drum/Bag/Crate |
| hs_code | string nullable | 6–10 digit, used for duty advisory |
| commodity | string nullable | Per-line commodity name |
| quantity | integer | |
| gross_weight | decimal(10,2) nullable | |
| weight_unit | enum('KG','MT') default 'KG' | |
| volume_cbm | decimal(10,2) nullable | Cubic metres |
| cargo_value | decimal(15,2) nullable | For insurance/duty advisory |
| country_of_origin | string nullable | FTA / duty rate impact |
| is_dangerous_goods | boolean default false | |
| special_handling | string nullable | Fragile/Reefer/etc |
| timestamps | | |

> **Note:** `chevron_rfq_charges` (buy/sell rate charge lines) is deferred. Implement later when needed.

---

## Status Lifecycle

```
Draft ──► Pending (Sent to client)
              ├──► Win  ──► [Convert to Job]
              └──► Lose (requires lost_reason)
```

| Status | Badge |
|---|---|
| Draft | `badge bg-secondary` |
| Pending | `badge bg-warning text-dark` |
| Win | `badge bg-success` |
| Lose | `badge bg-danger` |

---

## Files to Create / Modify

### 1. Migrations (2 files)
- `create_chevron_rfqs_table`
- `create_chevron_rfq_items_table`

### 2. Models

**`app/Models/Chevron/ChevronRfq.php`**
- `$fillable`: all header columns
- `casts()`: rfq_date, valid_until → `'date'`
- Relationships: `belongsTo` ChevronCustomer, ChevronPort (pol via `pol_id`, pod via `pod_id`), ChevronEmployee (salesperson), ChevronJob (convertedJob)
- `hasMany` ChevronRfqItem
- `static generateRfqNo()` — `RFQ` + 6-digit zero-pad with `lockForUpdate()->max()` (same pattern as `ChevronJob::generateJobNo()`)
- `static types()`, `static serviceTypes()`, `static statuses()`, `static incoterms()`, `static lostReasons()`

**`app/Models/Chevron/ChevronRfqItem.php`**
- `$fillable`, `belongsTo` ChevronRfq
- `casts()`: is_dangerous_goods → `'boolean'`
- `static containerSizes()`, `static packageTypes()`, `static weightUnits()`

### 3. Controller: `app/Http/Controllers/Chevron/RfqController.php`

| Method | Description |
|---|---|
| `index(Request)` | DataTables dual-response; status badge; scoped by branch |
| `create()` | calls `formData()` |
| `store(Request)` | DB::transaction → generate RFQ no → save header → sync items |
| `edit(ChevronRfq)` | `formData()` + load with items |
| `update(Request, ChevronRfq)` | DB::transaction → update header → delete+recreate items |
| `updateStatus(Request, ChevronRfq)` | PATCH → set Draft/Pending/Win/Lose + lost_reason |
| `convertToJob(ChevronRfq)` | POST → create ChevronJob from RFQ fields; set `converted_job_id`; only when status=Win |
| `destroy(ChevronRfq)` | JSON response |
| `searchCustomers(Request)` | Select2 AJAX (same pattern as CnfJobController) |
| `searchPorts(Request)` | Select2 AJAX — used for both POL and POD |
| `searchEmployees(Request)` | Select2 AJAX for salesperson |
| `private formData()` | returns: customers=[], ports, types, service types, statuses, incoterms, currencies, container sizes, package types, lost reasons |
| `private prepareData(Request)` | sanitise + null-ify empty strings |

### 4. Routes — add inside `cnf.` group in `routes/chevron.php`

```php
use App\Http\Controllers\Chevron\RfqController;

// RFQ
Route::get('/rfqs/search-customers',    [RfqController::class, 'searchCustomers'])->name('rfqs.search-customers');
Route::get('/rfqs/search-ports',        [RfqController::class, 'searchPorts'])->name('rfqs.search-ports');
Route::get('/rfqs/search-employees',    [RfqController::class, 'searchEmployees'])->name('rfqs.search-employees');
Route::get('/rfqs',                     [RfqController::class, 'index'])->name('rfqs.index');
Route::get('/rfqs/create',              [RfqController::class, 'create'])->name('rfqs.create');
Route::post('/rfqs',                    [RfqController::class, 'store'])->name('rfqs.store');
Route::get('/rfqs/{rfq}/edit',          [RfqController::class, 'edit'])->name('rfqs.edit');
Route::put('/rfqs/{rfq}',               [RfqController::class, 'update'])->name('rfqs.update');
Route::patch('/rfqs/{rfq}/status',      [RfqController::class, 'updateStatus'])->name('rfqs.update-status');
Route::post('/rfqs/{rfq}/convert-job',  [RfqController::class, 'convertToJob'])->name('rfqs.convert-job');
Route::delete('/rfqs/{rfq}',            [RfqController::class, 'destroy'])->name('rfqs.destroy');
```

### 5. Views: `resources/views/chevron/cnf/rfqs/`

**`index.blade.php`**
- DataTable columns: RFQ No, Date, Customer, Type, Service, POL → POD, Valid Until, Status badge, Actions
- Status filter tabs: All / Draft / Pending / Win / Lose

**`create.blade.php`** (shared for create + edit; `$rfq = null` for create)

Section layout:
1. **Header** — RFQ No (readonly auto), Date, Valid Until, Salesperson (Select2 AJAX), Customer (Select2 AJAX), Type (Import/Export), Service Type (FCL/LCL/Air/Truck), Incoterms, Currency
2. **Routing** — POL (Select2 AJAX), POD (Select2 AJAX), Place of Receipt, Place of Delivery, Remarks
3. **Cargo Items** (dynamic JS rows — add/remove)
   - item_type toggle (container / package) → conditional container_size OR package_type
   - HS Code, Commodity, Quantity, Weight + unit, Volume CBM, Cargo Value
   - Country of Origin, DG checkbox, Special Handling
4. **Status** (edit only) — Win / Lose / Pending buttons + lost reason dropdown (required on Lose)
5. **Convert to Job** button (edit only, visible when status = Win and not yet converted)

### 6. Sidebar nav link
Add "RFQ" link under C&F section in `resources/views/chevron/layouts/app.blade.php`.

---

## Patterns to Reuse

| Pattern | Source file |
|---|---|
| `generateXxxNo()` lockForUpdate | `app/Models/Chevron/ChevronJob.php` |
| `formData()` + `prepareData()` | `app/Http/Controllers/Chevron/BillController.php` |
| DataTables dual-response + status badge | `app/Http/Controllers/Chevron/CnfJobController.php` |
| `searchCustomers()` Select2 AJAX | `app/Http/Controllers/Chevron/CnfJobController.php` |
| DB::transaction header + child rows | `app/Http/Controllers/Chevron/BillController.php` |
| Dynamic JS add/remove rows | `resources/views/chevron/cnf/bills/create.blade.php` |

---

## Future: Charge Lines (`chevron_rfq_charges`)

Deferred to a later phase. When needed, add a third table with:
- `rfq_id`, `charge_group` (origin/freight/destination/customs/other)
- `charge_description`, `basis` (per_container/per_kg/per_cbm/per_bl/flat/pct)
- `quantity`, `buy_rate`, `sell_rate`, `currency`, `sort_order`, `remarks`

This enables buy/sell margin tracking per charge line — industry standard in CargoWise and Magaya.

---

## Verification Checklist

1. `php artisan migrate` — 2 new tables created
2. `/chevron/cnf/rfqs` — DataTable loads; status tabs filter correctly
3. Create RFQ with 2 cargo rows (1 container 40HC, 1 pallet package) — saves correctly
4. Edit → change cargo rows → updates correctly
5. Status flow: Draft → Pending → Win → "Convert to Job" creates ChevronJob with `converted_job_id` set
6. Status → Lose → lost_reason required before saving
7. Delete RFQ → row removed from list
8. `vendor/bin/pint --dirty --format agent` — no style errors
9. `php artisan test --compact` — full suite green
