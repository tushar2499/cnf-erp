# Employee-Based Branch Access — Architecture Plan

**Date:** 2026-08-18 (updated)
**Status:** Planned (not yet implemented)

---

## Problem Statement

Current system stores branch permissions on the **user** (`user_branch_access` table).
This means an employee without a user account cannot have branch permissions configured.
Admin must create the user account first, then set branch permissions — two separate steps with a dependency gap.

---

## Core Rules (updated 2026-08-18)

1. **Every user must have an employee record.** No employee link = blocked login (except super admin).
2. **One employee can have multiple user accounts.** No unique constraint on `employee_id` in the users table.
3. **Super admin is the only exception.** Super admin users (`is_super = true`):
   - Have no branch assignment (branch section hidden/skipped in UI and validation)
   - Are displayed as "System Administrator" type — role_id section hidden in user form
   - **Cannot be deleted** — delete button hidden in index; delete route returns 403 for `is_super` users

---

## Proposed Solution

Move branch and company access to the **employee level**.
User carries only a **role** and an **employee link**.
All company/branch access is derived from the employee record.

---

## New Architecture

### Two Orthogonal Access Dimensions

These two tables answer different questions and are **both required**:

| Table | Question answered | Concern |
|---|---|---|
| `employee_branch_access` | **WHERE** can this employee go? (which company, which branch) | HR / physical access |
| `role_company` + `role_has_permissions` | **WHAT** can this user do inside each company? | IT / operational permissions |

**Why both are needed — Himal example:**

Himal is an employee registered in all three HR tables.
- `employee_branch_access` says: Himal can enter Company 1 (Branch 1, Branch 2) only.
- `role_company` says: Himal's role covers Company 1 only → permissions apply inside Company 1.
- Combined: Himal can enter Company 1 Branch 1 and Branch 2, and has full role permissions there.
- If HR later adds Company 2 branches to `employee_branch_access` but the role has no Company 2 entry in `role_company` → Himal could log in there but see nothing (empty menu). The intersection of both tables determines effective access.

Removing `role_company` would make all roles global — every role would apply to every company, bypassing company-level permission isolation.

### Access = Employee + Role

```
User logs in
  │
  ├─ WHERE to go:  employee_branch_access  →  company_id + branch_id list
  │
  └─ WHAT to do:   role_company → role covers company X
                   role_has_permissions → specific permission names inside company X

Final access:
  Visible companies  =  employee_branch_access companies  ∩  role_company companies
  Visible branches   =  employee_branch_access branches for selected company (no role filter)
  Allowed operations =  role_has_permissions scoped to selected company via role_company
  User record        =  role_id + employee_id (credentials only)
```

---

## Data Model Changes

### Tables to REMOVE

| Table | Reason |
|---|---|
| `user_branch_access` | Branch access moves to employee level |
| `company_user` pivot | User no longer has per-company assignment |

### Tables to KEEP (previously marked for removal — corrected)

| Table | Why kept |
|---|---|
| `role_company` pivot | Answers WHAT (role's company scope) — orthogonal to `employee_branch_access` which answers WHERE. Without it, all roles are global and company-level permission isolation breaks. See architecture section. |

### Table to ADD

```sql
employee_branch_access:
  id               bigint unsigned PK
  employee_id      bigint unsigned        -- references whichever company's employee table
  company_id       FK → companies.id
  branch_id        bigint unsigned        -- company-specific branch (resolved via company_id)
  created_at
  updated_at

  UNIQUE (employee_id, company_id, branch_id)   -- one employee can have many rows, no duplicate per combo
  INDEX (employee_id)
  INDEX (company_id, branch_id)
```

One employee can have multiple rows — e.g., employee_id=3:
```
{ employee_id=3, company_id=1, branch_id=1 }
{ employee_id=3, company_id=1, branch_id=2 }
{ employee_id=3, company_id=2, branch_id=3 }
```

**No `employee_type` column in this table.** `employee_type` lives only on `users` table (to know which HR table to look up for profile data). When querying branch access, we join `users.employee_id = employee_branch_access.employee_id`.

> **Risk note:** If ChevronEmployee id=5 and NasFreightsEmployee id=5 are different real people, they would share the same `employee_branch_access` rows. This is safe only if the three employee tables are treated as sharing a single auto-increment sequence OR if in practice IDs don't collide. Confirm this assumption before implementation.

### `users` Table Modifications

```
ADD:    employee_type   varchar nullable  ('chevron' | 'nas_freights' | 'nas_trading')
ADD:    employee_id     bigint unsigned nullable
ADD:    role_id         bigint unsigned nullable FK → roles.id
REMOVE: (company/branch columns — none were here, cleanup company_user dependency)
```

**No unique constraint on `(employee_type, employee_id)` in users** — multiple users can reference the same employee.

### Tables UNCHANGED

- `roles` — name only
- `permissions` — `company_id` nullable column stays (scopes permission to a company)
- `role_has_permissions` — unchanged
- `role_company` — kept (see Tables to KEEP above)
- `chevron_employees` — unchanged
- `nas_freights_employees` — unchanged
- `nas_trading_employees` — unchanged
- `*_branches` tables — unchanged

---

## Access Determination Logic

```
User logs in
  │
  ├─ is_super = true  → full access to all companies/branches (bypass all checks)
  │
  ├─ No employee linked (employee_id NULL, is_super false)
  │    └─ Block with "Contact admin to link your employee account"
  │
  ├─ Has employee + has role
  │    │
  │    ├─ A = companies from employee_branch_access WHERE employee_id = user.employee_id
  │    │       (WHERE the employee has physical/HR access)
  │    │
  │    ├─ B = companies from role_company WHERE role_id = user.role_id
  │    │       (WHERE the role has operational scope)
  │    │
  │    └─ VISIBLE companies = A ∩ B  (intersection — must appear in BOTH)
  │
  └─ Inside an allowed company:
       Branch picker = branches from employee_branch_access for that company_id only
       Menu items    = permissions from role_has_permissions for that role + company scope
```

**Company picker rule:** A company appears only if:
1. Employee has at least one branch row in `employee_branch_access` for that company, **AND**
2. The user's role has an entry in `role_company` for that company.

If either is missing → company is invisible to that user.

**Branch picker rule:** Show only branches from `employee_branch_access` for the selected company. No role filter here — branch access is purely an HR/employee concern.

---

## Example — Himal

### User Record

```
name:          Himal
username:      himal
role_id:       2  (Accounts role)
employee_type: chevron
employee_id:   3
```

### Step 1 — employee_branch_access (SET A: WHERE employee has HR/physical access)

`employee_branch_access` rows for employee_id=3:

| employee_id | company_id | branch_id | company | branch |
|---|---|---|---|---|
| 3 | 1 | 1 | Chevron Lines | Main Branch |
| 3 | 2 | 1 | NAS Freights | Main Branch |
| 3 | 2 | 2 | NAS Freights | CTG Branch |
| 3 | 3 | 1 | NAS Trading | Main Branch |

Set A companies: {1, 2, 3}

### Step 2 — role_company (SET B: WHERE the Accounts role has operational scope)

`role_company` rows for role_id=2 (Accounts):

| role_id | company_id | company |
|---|---|---|
| 2 | 1 | Chevron Lines |

Set B companies: {1}

### Step 3 — INTERSECTION (what user actually sees)

Visible companies = A ∩ B = {1} → **Chevron Lines only**

| Company | In employee_branch_access? | In role_company? | Visible in picker? |
|---|---|---|---|
| Chevron Lines (1) | ✓ | ✓ | **Yes** |
| NAS Freights (2) | ✓ | ✗ | No — role has no scope here |
| NAS Trading (3) | ✓ | ✗ | No — role has no scope here |

### Step 4 — Branch picker (inside Chevron Lines)

Branches from `employee_branch_access` where employee_id=3 AND company_id=1:

| branch_id | branch |
|---|---|
| 1 | Main Branch |

Only Main Branch visible. No role filter applied at branch level.

### Step 5 — Menu / permissions (inside Chevron Lines)

Permissions from `role_has_permissions` where role_id=2, scoped to company_id=1.

---

## Admin UI Changes

### Employee Form (admin section) — NEW panel

Add **"Company & Branch Access"** section:

```
[ ] Chevron Lines
    [x] Main Branch
    [ ] CTG Branch

[ ] NAS Freights
    [x] Main Branch
    [x] CTG Branch

[ ] NAS Trading
    [x] Main Branch
```

- Works whether or not employee has a user account
- Saves to `employee_branch_access`
- Replaces the branch checkboxes in the user form

### User Form (admin section) — SIMPLIFIED

**Remove:**
- Company assignment section
- Branch access checkboxes

**Keep:**
- Name, username, email, password fields
- Role selector (hidden for super admin users)
- Employee link selector (hidden for super admin users; required for all other users)
- Active toggle

**Super Admin edit form special state:**
- Show yellow "System Administrator" callout banner at top
- Role selector = hidden
- Employee link selector = hidden
- Branch access section = hidden
- No delete button in index row for `is_super` users

User form becomes ~40% smaller and cleaner.

---

## Problems & Solutions

### Problem 1: Employee type is company-specific, but user is one person

**Problem:** ChevronEmployee, NasFreightsEmployee, NasTradingEmployee are separate
tables. How does one user link to all 3?

**Solution:** User links to ONE primary employee record via `employee_type + employee_id`.
The `employee_branch_access` table handles the cross-company branch mapping.
Himal is stored as ChevronEmployee but can have branches in all 3 companies via
the access table. The `employee_type` just identifies which HR table the record
lives in for HR purposes — it does not restrict which companies they can access.

---

### Problem 2: Super admin has no employee — but cannot be blocked

**Problem:** Super admin user (`is_super = true`) has no employee record.
Rule says "every user must have employee" — but super admin is the exception.

**Solution:** `is_super = true` bypasses all employee-based checks.
Super admin sees all companies, all branches.
Regular users without employee link are blocked with a clear message.
No branch UI or employee UI shown when editing super admin in admin panel.

---

### Problem 3: Same employee can have multiple users — design implications

**Problem:** One employee (e.g., ChevronEmployee id=3) can be linked to multiple user
accounts (e.g., day-shift login and a read-only audit login for the same person).
Old plan assumed one-to-one. New rule explicitly allows one-to-many.

**Solution:** `users.employee_id` must have NO unique constraint.
Multiple rows in `users` can reference the same `(employee_type, employee_id)`.
Branch access comes from the employee record — all users sharing that employee
automatically share the same branch access. Role differences between users
differentiate what each login can DO, not where they can go.
Employee delete check must use `ANY user linked` not `ONE user linked`.

---

### Problem 4: Super admin delete not blocked — code gap

**Problem:** `DestroyUserRequest::authorize()` only checks `admin.users.delete`
permission — no `is_super` check. `UserController::destroy()` calls `$user->delete()`
unconditionally. Index view renders delete button for every user including super admin.

**Three-point fix needed (none implemented yet):**
1. `DestroyUserRequest::authorize()` — add `!$this->route('user')->is_super` guard
2. `UserController::destroy()` — add `abort_if($user->is_super, 403)` before delete
3. `UserController::index()` action column — wrap delete button in `!$user->is_super` check

---

### Problem 5: Mandatory employee validation not enforced

**Problem:** `StoreUserRequest` has `employee_link` as `nullable`. No rule enforces
employee link for non-super users. A new user can be created with no employee and
gain no access (silently broken state).

**Solution:** Since `is_super` is never set from the user creation form (only seeder/DB),
all new users created through the form can safely have `employee_link` as `required`.
For edit form: add conditional — if editing a `is_super` user, skip employee_link
validation. Otherwise require it.

**Validation rule change:**
```php
// StoreUserRequest: always required (new users are never super)
'employee_link' => ['required', 'string', 'regex:/^(chevron|nas_freights|nas_trading):\d+$/'],

// UpdateUserRequest: required unless editing super admin
'employee_link' => [Rule::when(!$this->route('user')?->is_super, ['required']), ...],
```

---

### Problem 6: `employee_link` format must change

**Problem:** Current format is `"company_id:employee_id"` (e.g., `"1:3"`).
`parseEmployeeLink()` returns `[company_id, employee_id]`.
New architecture uses `employee_type` not `company_id` on users.

**Old format:** `"1:3"` = company_id=1 (Chevron), employee_id=3
**New format:** `"chevron:3"` = employee_type=chevron, employee_id=3

**Cascade of changes:**
- `buildCompaniesData()` grouped employees by company_id → rebuild as type-keyed map
- `parseEmployeeLink()` returns `[employee_type, employee_id]`
- Form employee dropdown groups by type ('chevron' | 'nas_freights' | 'nas_trading')
- Request validation regex changes from `/^\d+:\d+$/` to `/^(chevron|nas_freights|nas_trading):\d+$/`
- `syncRoleAndCompanies()` sets `users.employee_type + users.employee_id` instead of `company_user.employee_id`

---

### Problem 7: Role has no permissions for a company but employee has branch access there

**Problem:** User can enter NasFreights (employee has it) but sees nothing — confusing UX.

**RESOLVED — intersection rule applies.** Company picker shows ONLY companies in BOTH `employee_branch_access` AND `role_company`. If role has no `role_company` row for NasFreights, that company never appears in the picker — user never even sees it. No "empty dashboard" problem.

---

### Problem 8: Employee deleted — all users sharing that employee lose access

**Problem:** If employee record is deleted, all users with `employee_type + employee_id`
pointing to it have dead references. With one-to-many user-employee, this affects
multiple users at once.

**Solution:** On employee delete, check `User::where('employee_type', $type)->where('employee_id', $id)->exists()`.
Block deletion if ANY user is linked — show: "X user account(s) are linked to this employee. Unlink them first."
Alternatively: soft-delete employees only.

---

### Problem 9: Employee changed branch assignment — active session still has old access

**Problem:** Admin removes a branch from employee mid-day while user is logged in.
Multiple users sharing the same employee all get the wrong access simultaneously.

**Solution:** Branch access re-checked on every company/branch switch (not just login).
Current `EnsureCompanyAccess` and `EnsureBranchSelected` middleware already run
per-request — update them to query `employee_branch_access` live instead of session only.

---

### Problem 10: Migration — existing user_branch_access data must be preserved

**Problem:** Current users (himal id=2, new.admin id=3) already have branch access in
`user_branch_access`. All `company_user.employee_id` rows are NULL — no employee
links exist yet. Migration must handle both.

**Solution:** Write a migration script that:
1. Audits which users have NULL employee_id — these MUST be linked manually before migration
2. For users that do have employee links: copy `user_branch_access` rows into `employee_branch_access` via the user→employee link
3. For users with no employee link: flag them for admin to resolve (link to employee OR mark is_super)
4. Run as a one-time seeder BEFORE dropping old tables

**Current DB state (2026-08-18):**
- user id=1 (admin): `is_super=1`, no employee_id — super admin exception, skip
- user id=2 (himal): `is_super=0`, `company_user.employee_id = NULL` — must be linked before migration
- user id=3 (new.admin): `is_super=0`, `company_user.employee_id = NULL` — must be linked before migration

---

### Problem 11: Users with no employee link block migration go-live

**Problem:** himal and new.admin both have `employee_id = NULL` in company_user.
Enforcing "every user must have employee" immediately breaks their login.

**Solution:** Before any code enforcement is deployed:
1. Admin manually links himal → correct ChevronEmployee (or whichever HR table)
2. Admin manually links new.admin → correct employee record
3. Only then deploy the validation enforcement

This must happen in the user edit form (pre-refactor, using current UI).

---

### Problem 12: company_user pivot removal breaks role sync and permission resolution

**Problem:** Current `syncRoleAndCompanies()` uses `company_user` to assign role per
company. `resolvedPermissions()` in User model queries `companies()` pivot for `role_id`.
Removing the pivot breaks both.

**Solution:** Role moves directly to `users.role_id`. One role per user.
`resolvedPermissions()` changes from pivot-based to direct `$this->role_id` lookup.
If multi-role per company is needed in future, revisit — current system
effectively assigns one role per user already.

---

### Problem 13: Branch ID conflicts across companies

**Problem:** Chevron branch id=1 and NasFreights branch id=1 are different tables.
`employee_branch_access.branch_id` without company context is ambiguous.

**Solution:** `employee_branch_access` stores both `company_id` AND `branch_id`.
The middleware resolves the correct branch table using `company_id`.
No ambiguity since (company_id, branch_id) pair is always unique within a company.

---

### Problem 14: Permission check for company-scoped permissions

**Problem:** Current `hasPermission()` on User model checks `role_has_permissions`.
Some permissions have `company_id` — how to check these without `company_user`?

**Solution:** `hasPermission('chevron.jobs.create')` already uses permission name lookup.
The `company_id` on permissions is a filter for display (which company sees this
permission in role builder), not for the runtime check. Runtime check unchanged.

---

## UX Review Notes (from ui-ux-pro-max skill)

### Delete protection — destructive action clarity (Priority 1/8)
- Per `destructive-emphasis` rule: destructive actions (delete) must be visually separated and clearly identifiable.
- Super admin row in users index: delete button must be **absent** (not just disabled) — a disabled button still implies "this could work" and confuses intent.
- Per `confirmation-dialogs` rule: delete button for non-super users needs a SweetAlert2 confirmation before firing (already present in codebase, verify for all paths).

### Mandatory employee link UX (Priority 8 — Forms & Feedback)
- Per `error-clarity` rule: if a user submits with no employee_link, error must say "You must link an employee account to this user" — not generic "This field is required."
- Per `progressive-disclosure` rule: the employee link dropdown should be the most prominent required field in the form (move higher than role selector if role auto-derives from employee in future).
- Per `input-labels` rule: employee link dropdown needs visible label with required asterisk, not just a placeholder.

### Super admin edit form (Priority 8 — Forms)
- Per `read-only-distinction` rule: role_id and employee_link fields on super admin edit must be **absent** (not read-only / not disabled) to avoid confusion — hiding them entirely is correct.
- Per `empty-nav-state` rule: when branch access section is hidden for super admin, show a callout explaining WHY: "System administrators access all branches automatically."
- Per `progressive-disclosure` rule: super admin banner callout (yellow, with crown icon) should appear at the top of the right panel BEFORE any hidden sections — so the admin understands the context before noticing missing fields.

### Multiple-users-per-employee warning (Priority 8)
- Per `confirmation-dialogs` rule: when an admin creates a second user for the same employee, show a warning confirmation: "This employee already has X user account(s). Are you sure you want to add another?"
- This prevents accidental duplicates while allowing intentional multi-user setup.

---

## Migration Steps (when implementing)

1. **Pre-migration data prep:**
   - Link himal (user id=2) and new.admin (user id=3) to their employee records via current user form
   - Verify no other users have NULL employee_id (except is_super)

2. Add `employee_type`, `employee_id`, `role_id` columns to `users` table (no unique constraint on employee fields)

3. Migrate data: copy `company_user.role_id` → `users.role_id` per user (first non-null wins per user)

4. Migrate data: copy `company_user.employee_id + company_id` → determine employee_type, populate `users.employee_type + users.employee_id`

5. Create `employee_branch_access` table

6. Run migration script: `user_branch_access` → `employee_branch_access` (via user→employee link)

7. Update middleware: `EnsureCompanyAccess`, `EnsureBranchSelected` to use `employee_branch_access`

8. Update `User::resolvedPermissions()` to use `users.role_id` instead of pivot

9. **Fix super admin delete protection (3 locations — see Problem 4):**
   - `DestroyUserRequest::authorize()`
   - `UserController::destroy()`
   - `UserController::index()` action column

10. **Fix mandatory employee validation (see Problem 5):**
    - `StoreUserRequest` — make employee_link required
    - `UpdateUserRequest` — conditional required unless is_super

11. **Fix `employee_link` format (see Problem 6):**
    - Update `parseEmployeeLink()`, `buildCompaniesData()`, form dropdown, request regex

12. Update `UserController` — simplify user form (remove company/branch sections; add super admin callout)

13. Update `EmployeeController` admin — add branch access panel

14. **Add multiple-user-per-employee confirmation** in user create/store flow (UX)

15. **Block employee delete if any user linked** (see Problem 8)

16. Drop `user_branch_access`, `company_user` tables
    — `role_company` is **kept** (see Tables to KEEP)

17. Audit users with no employee link — resolve before go-live

---

## Open Questions (decide before implementation)

1. ~~**Role + company intersection**~~ — **RESOLVED.** Company picker = `employee_branch_access` ∩ `role_company`. Both must have the company for it to appear. Branch picker = `employee_branch_access` only (no role filter at branch level).

2. **Employee without any company branch access** — show error or allow login with
   empty company picker?

3. **Role with no company_id scoped permissions** — system-level admin roles currently
   have `company_id = NULL` permissions. These should work for all companies. Confirm.

4. **Second user for same employee confirmation** — show warning modal or just allow silently?

5. **is_super settable from UI?** — currently only set via DB/seeder. Confirm this
   should NEVER be toggleable from the user form (even for other super admins).
