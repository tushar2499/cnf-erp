# Employee-Based Branch Access — Architecture Plan

**Date:** 2026-08-17
**Status:** Planned (not yet implemented)

---

## Problem Statement

Current system stores branch permissions on the **user** (`user_branch_access` table).
This means an employee without a user account cannot have branch permissions configured.
Admin must create the user account first, then set branch permissions — two separate steps with a dependency gap.

---

## Proposed Solution

Move branch and company access to the **employee level**.
User carries only a **role** and an **employee link**.
All company/branch access is derived from the employee record.

---

## New Architecture

### Access = Employee + Role

```
Employee  →  which companies + which branches (HR concern)
Role      →  which operations/modules allowed (IT/access concern)
User      →  role_id + employee_id only (credentials layer)

Final access:
  Company/Branch  =  from employee_branch_access table
  Operations      =  from role_has_permissions table
```

---

## Data Model Changes

### Tables to REMOVE

| Table | Reason |
|---|---|
| `user_branch_access` | Branch access moves to employee level |
| `company_user` pivot | User no longer has per-company assignment |
| `role_company` pivot | Role no longer tied to specific companies |

### Table to ADD

```sql
employee_branch_access:
  id               bigint unsigned PK
  employee_type    varchar  →  'chevron' | 'nas_freights' | 'nas_trading'
  employee_id      bigint unsigned
  company_id       FK → companies.id
  branch_id        bigint unsigned  (company-specific branch)
  created_at
  updated_at

  INDEX (employee_type, employee_id)
  INDEX (company_id, branch_id)
```

### `users` Table Modifications

```
ADD:    employee_type   varchar nullable
ADD:    employee_id     bigint unsigned nullable
ADD:    role_id         bigint unsigned nullable FK → roles.id
REMOVE: (company/branch columns — none were here, cleanup company_user dependency)
```

### Tables UNCHANGED

- `roles` — name only
- `permissions` — `company_id` nullable column stays (scopes permission to a company)
- `role_has_permissions` — unchanged
- `chevron_employees` — unchanged
- `nas_freights_employees` — unchanged
- `nas_trading_employees` — unchanged
- `*_branches` tables — unchanged

---

## Access Determination Logic

```
User logs in
  │
  ├─ No employee linked?
  │    └─ is_super=true  → full access (bypass)
  │    └─ is_super=false → block with "contact admin" message
  │
  ├─ Has employee → query employee_branch_access
  │    └─ Returns: company_id list + branch_id list per company
  │
  └─ Has role → query role_has_permissions
       └─ Returns: permission names (some scoped to company_id)

Company picker shows: only companies from employee_branch_access
Branch selection shows: only branches for that company from employee_branch_access
Menu items show: only items user's role has permission for
```

---

## Example — Himal

### Employee Branch Access (configured in admin, no user needed)

| employee_type | employee_id | company | branch |
|---|---|---|---|
| chevron | 3 | Chevron Lines | Main Branch |
| chevron | 3 | NAS Freights | Main Branch |
| chevron | 3 | NAS Freights | CTG Branch |
| chevron | 3 | NAS Trading | Main Branch |

### User Record

```
name:          Himal
username:      himal
role_id:       2  (Accounts role)
employee_type: chevron
employee_id:   3
```

### Accounts Role Permissions

Permissions tagged `company_id = 1` (Chevron) — billing, job sheets, reports.

### Result at Login

| Company | Branch Access | Can Enter? | What Can Do |
|---|---|---|---|
| Chevron Lines | Main Branch | ✓ | Full Accounts role permissions |
| NAS Freights | Main + CTG | ✓ | Only Accounts permissions scoped to NasFreights |
| NAS Trading | Main Branch | ✓ | Only Accounts permissions scoped to NasTrading |

> **Note:** If Accounts role has zero NasFreights/NasTrading permissions, user enters
> those companies but sees empty/blocked menus. This is Interpretation B (role
> restricts operations, not company entry). Clarify with team whether company
> entry should also be blocked if role has no permissions for that company.

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
- Role selector
- Employee link selector (dropdown — pick from all 3 company employees)
- Active toggle

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

### Problem 2: Super users / admins with no employee

**Problem:** Some users (like the system admin) may have no employee record.

**Solution:** `is_super = true` flag bypasses all employee-based checks.
Super users see all companies, all branches.
Regular users without employee link are blocked with a clear message.

---

### Problem 3: Role has no permissions for a company but employee has branch access there

**Problem:** User can enter NasFreights (employee has it) but sees nothing — confusing UX.

**Solution A (recommended):** Only show company in picker if role has at least one permission for that company. Intersection of employee companies AND role-covered companies.

**Solution B:** Show all employee companies in picker but show empty dashboard with "No access configured for this company" message.

---

### Problem 4: Employee deleted — user loses all access

**Problem:** If employee record is deleted, user's `employee_type + employee_id` becomes a dead reference.

**Solution:** On employee delete, check if any user is linked. Block deletion if linked user exists. Show: "Unlink user first before deleting employee."
Alternatively: soft-delete employees only.

---

### Problem 5: Employee changed branch assignment — active session still has old access

**Problem:** Admin removes a branch from employee mid-day while user is logged in.

**Solution:** Branch access re-checked on every company/branch switch (not just login).
Current `EnsureBranchSelected` middleware already runs per-request — update it to
query `employee_branch_access` live instead of reading session only.

---

### Problem 6: Migration — existing user_branch_access data must be preserved

**Problem:** Current users already have branch access in `user_branch_access`.
If table is dropped, all existing access is lost.

**Solution:** Write a migration script that:
1. Reads existing `user_branch_access` rows
2. Finds the user's linked employee via `company_user.employee_id`
3. Creates corresponding `employee_branch_access` rows
4. Runs as a one-time seeder before dropping old tables

---

### Problem 7: Users with no employee link in current system

**Problem:** Some users exist in `users` table with no `company_user.employee_id`.
After migration they will have no access.

**Solution:** Before migration, audit: `SELECT u.id, u.name FROM users u WHERE NOT EXISTS (SELECT 1 FROM company_user cu WHERE cu.user_id = u.id AND cu.employee_id IS NOT NULL)`.
Admin must either link these users to employees or mark them `is_super`.

---

### Problem 8: company_user pivot removal breaks role sync

**Problem:** Current `syncRoleAndCompanies()` in UserController uses `company_user` to
assign role per company. Removing the pivot breaks this.

**Solution:** Role moves directly to `users.role_id`. One role per user.
If multi-role per company is needed in future, revisit — but current system
effectively assigns one role per user already.

---

### Problem 9: Branch ID conflicts across companies

**Problem:** Chevron branch id=1 and NasFreights branch id=1 are different tables.
`employee_branch_access.branch_id` without company context is ambiguous.

**Solution:** `employee_branch_access` stores both `company_id` AND `branch_id`.
The middleware resolves the correct branch table using `company_id`.
No ambiguity since (company_id, branch_id) pair is always unique within a company.

---

### Problem 10: Permission check for company-scoped permissions

**Problem:** Current `hasPermission()` on User model checks `role_has_permissions`.
Some permissions have `company_id` — how to check these without `company_user`?

**Solution:** `hasPermission('chevron.jobs.create')` already uses permission name lookup.
The `company_id` on permissions is a filter for display (which company sees this
permission in role builder), not for the runtime check. Runtime check unchanged.

---

## Migration Steps (when implementing)

1. Add `employee_type`, `employee_id`, `role_id` columns to `users` table
2. Migrate data: copy `company_user.role_id` → `users.role_id` per user
3. Migrate data: copy `company_user.employee_id + company_id` → populate correctly
4. Create `employee_branch_access` table
5. Run migration script: `user_branch_access` → `employee_branch_access` (via user→employee link)
6. Update middleware: `EnsureCompanyAccess`, `EnsureBranchSelected`
7. Update `User::hasPermission()` if needed
8. Update `UserController` — simplify user form (remove company/branch sections)
9. Update `EmployeeController` admin — add branch access panel
10. Drop `user_branch_access`, `company_user`, `role_company` tables
11. Audit users with no employee link — resolve before go-live

---

## Open Questions (decide before implementation)

1. **Role + company intersection** — does role restrict which companies user can ENTER,
   or only what they can DO inside each company? (Interpretation A vs B above)

2. **Multi-employee link** — can one user be linked to multiple employee records
   (e.g., different HR records in different company tables)? Or always one?

3. **Employee without any company branch access** — show error or allow login with
   empty company picker?

4. **Role with no company_id scoped permissions** — system-level admin roles currently
   have `company_id = NULL` permissions. These should work for all companies. Confirm.
