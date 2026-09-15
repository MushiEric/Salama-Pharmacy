# SalamaPharma — Developer Documentation

**Document status:** Technical Source of Truth  
**Product:** SalamaPharma  
**Architecture:** Modular Monolith  
**Backend:** Laravel 11 / PHP 8.3+  
**Database:** PostgreSQL  
**Cache / Queue:** Redis  
**Web:** React + TypeScript + TailwindCSS  
**Mobile:** Flutter — later phase  
**Version:** 0.1

---

## 1. Engineering Objective

Build SalamaPharma as a production-grade modular monolith that provides strong tenant isolation, reliable stock accounting, safe concurrent checkout, auditable business operations, and clear extension points for future pharmacy integrations.

The system must prefer correctness and explicit domain rules over short-term coding convenience.

---

## 2. Architectural Principles

### 2.1 Modular Monolith

The backend is one deployable application but is divided into explicit business modules.

Modules should expose application services/contracts and must avoid arbitrary access to another module's internals.

### 2.2 Backend Is Authoritative

Never trust the client for:

- tenant ID;
- branch authorization;
- price;
- final total;
- discount permission;
- stock availability;
- package entitlements;
- prescription enforcement;
- batch allocation.

The client submits intent.

The backend validates and executes the business transaction.

### 2.3 Strong Domain Invariants

Important invariants should be enforced using:

- database constraints;
- transactions;
- locks;
- unique indexes;
- application services;
- authorization policies;
- idempotency.

Do not depend only on developer convention.

### 2.4 No Premature Microservices

Modules may later be extracted only when there is a concrete operational reason.

---

## 3. Proposed Modules

```text
app/
└── Modules/
    ├── Identity/
    ├── Tenancy/
    ├── Subscription/
    ├── DrugCatalog/
    ├── PharmacyProduct/
    ├── Supplier/
    ├── Procurement/
    ├── Inventory/
    ├── Prescription/
    ├── Customer/
    ├── Sales/
    ├── Payment/
    ├── Transfer/
    ├── Reporting/
    ├── Notification/
    ├── Device/
    ├── Receipt/
    └── Audit/
```

Each module may use:

```text
Module/
├── Application/
│   ├── Commands/
│   ├── Queries/
│   ├── DTOs/
│   ├── Services/
│   └── Contracts/
├── Domain/
│   ├── Entities/
│   ├── ValueObjects/
│   ├── Enums/
│   ├── Events/
│   ├── Exceptions/
│   └── Policies/
├── Infrastructure/
│   ├── Persistence/
│   ├── Repositories/
│   ├── Cache/
│   └── Integrations/
└── Presentation/
    ├── Http/
    │   ├── Controllers/
    │   ├── Requests/
    │   └── Resources/
    └── Routes/
```

Laravel-specific pragmatism is allowed. The goal is clear boundaries, not excessive abstraction.

---

## 4. Module Responsibilities

### Identity

Owns:

- users;
- authentication;
- roles;
- permissions;
- role assignment;
- password lifecycle;
- authentication sessions/tokens.

### Tenancy

Owns:

- tenants;
- branches;
- tenant configuration;
- tenant context;
- branch access context.

### Subscription

Owns:

- packages;
- package limits;
- tenant subscriptions;
- entitlement resolution;
- read-only state;
- usage-limit enforcement.

### DrugCatalog

Owns platform-level master drug information.

### PharmacyProduct

Owns tenant-level sellable product configuration:

- master-drug reference;
- units;
- conversions;
- branch price;
- branch reorder level;
- prescription-required flag;
- product status.

### Supplier

Owns supplier profiles.

### Procurement

MVP owns stock-receiving use cases.

Future:

- purchase orders;
- approvals;
- supplier invoices.

### Inventory

Owns:

- batches;
- stock movements;
- stock balance queries;
- FEFO allocation;
- adjustments;
- expiry status.

### Prescription

Owns:

- prescription record;
- prescription items;
- dispensing status;
- prescription validation contract.

### Customer

Owns tenant customer records.

### Sales

Owns:

- sale;
- sale items;
- checkout;
- discount validation;
- sale lifecycle;
- idempotent checkout.

### Payment

Owns:

- sale payment record;
- cash;
- MNO payment metadata.

No payment-gateway integration in MVP.

### Transfer

Owns branch-to-branch inventory transfers and lifecycle.

### Reporting

Owns optimized query/read models only.

Reporting must not own transactional truth.

### Notification

Owns:

- notification definition;
- channel routing;
- user notification inbox;
- delivery jobs;
- delivery status.

### Device

Owns:

- device registration;
- tenant device count;
- activation status;
- package-limit enforcement.

### Receipt

Owns:

- receipt view model;
- printable payload;
- reprint;
- PDF generation.

### Audit

Owns security-sensitive and business-sensitive audit records.

---

## 5. Dependency Direction

Recommended dependency policy:

```text
Presentation
    ↓
Application
    ↓
Domain

Infrastructure implements Application/Domain contracts.
```

Cross-module calls should use:

1. application service interfaces;
2. module contracts;
3. domain/application events for side effects.

Avoid:

```php
Inventory model directly importing Sales internals
```

Prefer:

```text
Sales Checkout Service
    ↓
Inventory Allocation Contract
    ↓
Inventory Module
```

---

## 6. Multi-Tenancy Strategy

### 6.1 Database Model

Use:

**Shared PostgreSQL database + shared schema + tenant_id isolation**

Tenant-owned tables include `tenant_id`.

Branch-owned records also include `branch_id`.

Examples:

```text
products
product_branch_settings
customers
suppliers
batches
inventory_movements
sales
sale_items
payments
prescriptions
stock_transfers
```

### 6.2 Tenant Context

`tenant_id` must never be accepted as authoritative request input.

Resolve tenant context from authenticated identity.

Pseudo-flow:

```text
HTTP Request
    ↓
Authenticate User
    ↓
Resolve Tenant Context
    ↓
Resolve Authorized Branch Context
    ↓
Apply Tenant-Scoped Queries
    ↓
Execute Use Case
```

### 6.3 Global Scope

Laravel global scopes may be used for tenant-owned Eloquent models, but they must not be the only isolation defense.

Recommended layered protection:

- tenant context service;
- global Eloquent scope;
- repository constraints;
- authorization;
- database composite constraints;
- integration tests for cross-tenant access.

### 6.4 Superadmin

Superadmin operations should use explicit elevated query paths.

Never disable tenancy implicitly in ordinary request flow.

Use an explicit construct such as:

```text
PlatformContext
```

or

```text
withoutTenantScopeForAuthorizedPlatformAction()
```

and audit its usage.

---

## 7. Branch Authorization

Every branch-scoped request must validate that:

1. branch belongs to current tenant;
2. current user may access that branch;
3. requested operation is permitted.

For MVP, regular operational users belong to one branch.

Tenant Pharmacy Admin may receive tenant-wide management access depending on permissions.

---

## 8. Authentication

Recommended:

### Web SPA

Use Laravel Sanctum with secure HTTP-only cookie-based session authentication where deployment topology allows it.

Benefits:

- avoids exposing long-lived access tokens to JavaScript;
- integrates well with first-party SPA;
- supports CSRF protection.

If frontend/backend domains require token auth, define a separate documented token strategy.

### Future Flutter

Use short-lived access tokens + refresh-token strategy or another mobile-appropriate authentication flow.

Do not force the mobile authentication shape onto the web SPA.

---

## 9. Authorization

Use RBAC with dynamic tenant roles.

Fixed concepts:

- Platform Superadmin
- Pharmacy Admin

Operational role names are tenant-defined.

### 9.1 Permission Examples

```text
branch.view
branch.manage

user.view
user.create
user.update
role.manage

drug.view
product.create
product.update
product.price.update

supplier.view
supplier.manage

inventory.view
inventory.receive
inventory.adjust

transfer.create
transfer.approve
transfer.dispatch
transfer.receive

prescription.create
prescription.dispense

sale.create
sale.discount.apply
sale.discount.override
sale.view

report.sales.view
report.inventory.view
report.profit.view

settings.manage
subscription.view
```

Authorization should use Laravel Policies/Gates backed by permission resolution.

Cache permission lookups carefully by:

```text
tenant_id + user_id
```

and invalidate after role/permission updates.

---

## 10. Subscription Enforcement

Implement a central Subscription Guard.

Responsibilities:

- resolve active package;
- resolve current subscription status;
- determine read-only mode;
- enforce feature entitlement;
- enforce quantitative limits.

Recommended service:

```php
SubscriptionEntitlementService
```

Example methods:

```text
canUse(feature)
assertWriteAllowed()
assertBranchLimit()
assertUserLimit()
assertDeviceLimit()
assertStockLimit()
assertTransactionLimit()
```

Do not scatter package-name checks such as:

```php
if ($package === 'premium')
```

Instead:

```text
if ($entitlements->has('advanced_reports'))
```

---

## 11. Package Limits

Potential limits:

```text
max_branches
max_users
max_devices
max_stock_items
max_transactions_per_period
```

Track package usage with authoritative database queries or reliable counters.

Do not depend solely on Redis counters for billing enforcement.

Redis may cache usage but PostgreSQL remains the source of truth.

---

## 12. Core Data Model

The following is a conceptual model, not final migration syntax.

### tenants

```text
id UUID
name
status
settings JSONB
created_at
updated_at
```

### branches

```text
id UUID
tenant_id UUID
name
phone
address
status
created_at
updated_at
```

Constraint:

```text
UNIQUE (tenant_id, id)
```

### users

```text
id UUID
tenant_id UUID nullable for platform admin
branch_id UUID nullable
name
email
phone
password
status
created_at
updated_at
```

Use appropriate uniqueness rules for email/phone according to authentication policy.

### roles

```text
id UUID
tenant_id UUID nullable for platform roles
name
is_system
created_at
updated_at
```

### permissions

```text
id
code UNIQUE
description
```

### role_permissions

```text
role_id
permission_id
```

### user_roles

```text
user_id
role_id
```

---

## 13. Master Drug Catalog Model

### generic_drugs

```text
id UUID
name
status
```

### master_drugs

```text
id UUID
generic_drug_id
brand_name
dosage_form
strength
manufacturer
barcode nullable
category nullable
status
created_at
updated_at
```

Master catalog is platform-level and has no tenant_id.

---

## 14. Tenant Product Model

### pharmacy_products

```text
id UUID
tenant_id UUID
master_drug_id UUID nullable
local_name
prescription_required BOOLEAN
status
created_at
updated_at
```

Allow a future local-only product path when no master match exists, if business later approves it.

### product_units

```text
id UUID
tenant_id UUID
pharmacy_product_id UUID
name
symbol
multiplier_to_base NUMERIC
is_base BOOLEAN
status
```

Example:

```text
Tablet  multiplier = 1
Blister multiplier = 10
Box     multiplier = 100
```

Constraint:

- exactly one base unit per pharmacy product;
- multiplier must be > 0.

### product_branch_settings

```text
id UUID
tenant_id UUID
branch_id UUID
pharmacy_product_id UUID
selling_price NUMERIC(18,2)
reorder_level_base NUMERIC(18,3)
is_active BOOLEAN
created_at
updated_at
```

Constraint:

```text
UNIQUE (tenant_id, branch_id, pharmacy_product_id)
```

---

## 15. Tenant Settings

Recommended JSON/domain settings include:

```text
expiry.red_days
expiry.yellow_days
inventory.block_expired_sales
currency
timezone
```

Defaults:

```text
currency = TZS
timezone = Africa/Dar_es_Salaam
```

Prefer typed accessors/value objects instead of uncontrolled JSON reads throughout the codebase.

---

## 16. Suppliers

### suppliers

```text
id UUID
tenant_id UUID
name
contact_person nullable
phone nullable
email nullable
address nullable
status
created_at
updated_at
```

---

## 17. Stock Receiving

### stock_receipts

```text
id UUID
tenant_id UUID
branch_id UUID
supplier_id UUID nullable
reference_no
received_by_user_id
received_at
status
created_at
updated_at
```

### stock_receipt_items

```text
id UUID
tenant_id UUID
stock_receipt_id UUID
pharmacy_product_id UUID
product_unit_id UUID
quantity_in_unit NUMERIC
quantity_base NUMERIC
batch_number
manufactured_at nullable
expires_at nullable
unit_cost_base NUMERIC(18,6)
created_at
updated_at
```

Receipt posting should create inventory batches/movements within one database transaction.

---

## 18. Batch Model

### inventory_batches

```text
id UUID
tenant_id UUID
branch_id UUID
pharmacy_product_id UUID
supplier_id UUID nullable
batch_number
received_at
manufactured_at nullable
expires_at nullable
initial_quantity_base NUMERIC(18,3)
available_quantity_base NUMERIC(18,3)
unit_cost_base NUMERIC(18,6)
status
created_at
updated_at
```

Recommended indexes:

```text
(tenant_id, branch_id, pharmacy_product_id)
(tenant_id, branch_id, pharmacy_product_id, expires_at)
(tenant_id, branch_id, expires_at)
```

Database constraint:

```text
available_quantity_base >= 0
```

---

## 19. Inventory Movement Ledger

Never update stock without recording a movement.

### inventory_movements

```text
id UUID
tenant_id UUID
branch_id UUID
pharmacy_product_id UUID
batch_id UUID nullable
type
quantity_delta_base NUMERIC(18,3)
reference_type
reference_id UUID
reason nullable
actor_user_id UUID
occurred_at
metadata JSONB nullable
created_at
```

Movement types may include:

```text
RECEIPT
SALE
ADJUSTMENT_IN
ADJUSTMENT_OUT
TRANSFER_OUT
TRANSFER_IN
REVERSAL
RETURN_IN     // future
RETURN_OUT    // future
```

---

## 20. Stock Balance Strategy

Authoritative batch quantity exists on `inventory_batches.available_quantity_base`.

The movement ledger provides traceability.

Do not recompute live POS availability by summing the entire movement ledger on every request.

Use:

```text
batch.available_quantity_base
```

for transactional availability and use movements for audit/reconciliation.

---

## 21. FEFO Allocation Algorithm

Candidate batches:

```text
tenant_id = current tenant
branch_id = current branch
pharmacy_product_id = requested product
available_quantity_base > 0
eligible according to expiry policy
```

Order:

```text
expires_at ASC NULLS LAST
received_at ASC
id ASC
```

The exact query used during checkout must lock candidate rows.

Pseudo-code:

```php
DB::transaction(function () use ($productId, $requiredQty) {
    $batches = InventoryBatch::query()
        ->where('tenant_id', tenant()->id)
        ->where('branch_id', branch()->id)
        ->where('pharmacy_product_id', $productId)
        ->where('available_quantity_base', '>', 0)
        ->eligibleForSale()
        ->orderBy('expires_at')
        ->orderBy('received_at')
        ->lockForUpdate()
        ->get();

    $remaining = $requiredQty;

    foreach ($batches as $batch) {
        if ($remaining <= 0) {
            break;
        }

        $take = min($batch->available_quantity_base, $remaining);

        // record allocation
        // decrement batch
        // create movement

        $remaining -= $take;
    }

    if ($remaining > 0) {
        throw new InsufficientStockException();
    }
});
```

Actual code should live in the Inventory module, not directly in the controller.

---

## 22. Expired Batch Eligibility

Inventory should expose a domain policy:

```text
BatchSaleEligibilityPolicy
```

Inputs:

- expiry date;
- tenant setting;
- current date;
- product status.

Output:

```text
eligible
warning
blocked
```

If tenant permits expired sale:

- return warning;
- record audit metadata;
- preserve explicit user acknowledgment if desired.

---

## 23. Customers

### customers

```text
id UUID
tenant_id UUID
name
phone nullable
email nullable
status
created_at
updated_at
```

MVP currently requires a customer association for checkout.

Keep customer creation lightweight to avoid POS friction.

---

## 24. Prescriptions

### prescriptions

```text
id UUID
tenant_id UUID
branch_id UUID
customer_id UUID
prescriber_name
prescription_date
reference_no nullable
status
notes nullable
attachment_path nullable
created_by_user_id UUID
created_at
updated_at
```

### prescription_items

```text
id UUID
tenant_id UUID
prescription_id UUID
pharmacy_product_id UUID
prescribed_quantity_base nullable
dispensed_quantity_base default 0
instructions nullable
```

Checkout should request prescription context only when one or more items require it.

Prescription validation must be performed server-side.

---

## 25. Sales Model

### sales

```text
id UUID
tenant_id UUID
branch_id UUID
customer_id UUID
receipt_no
idempotency_key
subtotal
discount_total
total
status
sold_by_user_id
completed_at
created_at
updated_at
```

Recommended status:

```text
PENDING
COMPLETED
VOIDED   // future/controlled use
```

Unique constraint:

```text
UNIQUE (tenant_id, idempotency_key)
```

Receipt number should be unique within an explicitly chosen scope, such as:

```text
UNIQUE (tenant_id, branch_id, receipt_no)
```

---

## 26. Sale Items

### sale_items

```text
id UUID
tenant_id UUID
sale_id UUID
pharmacy_product_id UUID
product_unit_id UUID
quantity_in_unit NUMERIC
quantity_base NUMERIC
unit_price NUMERIC(18,2)
gross_amount NUMERIC(18,2)
discount_amount NUMERIC(18,2)
net_amount NUMERIC(18,2)
cogs_amount NUMERIC(18,2)
created_at
```

Store sale-time price and cost snapshots.

Never calculate historical reports from current product price.

---

## 27. Sale Batch Allocations

### sale_item_batch_allocations

```text
id UUID
tenant_id UUID
sale_item_id UUID
inventory_batch_id UUID
quantity_base NUMERIC(18,3)
unit_cost_base NUMERIC(18,6)
cost_amount NUMERIC(18,2)
created_at
```

This table preserves:

- exact FEFO allocation;
- cost of goods sold;
- batch traceability.

---

## 28. Checkout Transaction

Recommended application service:

```text
CheckoutSaleService
```

Transaction sequence:

```text
1. Validate idempotency key.
2. Resolve tenant/branch/user.
3. Assert subscription allows writes.
4. Assert user has sale.create.
5. Resolve customer.
6. Load products.
7. Resolve branch prices.
8. Validate requested units.
9. Convert quantity to base unit.
10. Validate prescription requirements.
11. Validate discount permission.
12. Lock eligible inventory batches.
13. Allocate stock using FEFO.
14. Calculate authoritative totals.
15. Create sale.
16. Create sale items.
17. Create batch allocations.
18. Decrement batch quantities.
19. Create inventory movements.
20. Create payment record.
21. Commit transaction.
22. Dispatch receipt/notification events after commit.
```

Receipt printing is not part of the transaction.

---

## 29. Idempotency

All state-changing financial/stock endpoints that may be retried should support idempotency where appropriate.

Checkout request header:

```text
Idempotency-Key: <UUID>
```

Server behavior:

- first request executes transaction;
- duplicate key with same semantic request returns original result;
- conflicting payload for the same key returns conflict.

Do not rely only on Redis for idempotency.

Persist critical idempotency state in PostgreSQL.

---

## 30. Payments

### sale_payments

```text
id UUID
tenant_id UUID
sale_id UUID
method
provider nullable
external_reference nullable
amount NUMERIC(18,2)
created_at
```

Method enum:

```text
CASH
MNO
```

Provider should use configurable reference data.

No provider API verification is performed in MVP.

---

## 31. Discounts

The backend decides whether a user can apply a discount.

Potential model:

```text
discount_type
discount_value
discount_amount
discount_reason
authorized_by
```

Do not accept `final_total` from the frontend as truth.

---

## 32. Stock Adjustments

### inventory_adjustments

```text
id UUID
tenant_id UUID
branch_id UUID
pharmacy_product_id UUID
batch_id UUID nullable
quantity_delta_base NUMERIC(18,3)
reason
notes nullable
created_by_user_id UUID
created_at
```

Posting an adjustment must:

1. validate permission;
2. validate batch/product;
3. lock affected batch if needed;
4. ensure non-negative resulting stock;
5. update quantity;
6. create inventory movement;
7. create audit event.

---

## 33. Stock Transfers

### stock_transfers

```text
id UUID
tenant_id UUID
source_branch_id UUID
destination_branch_id UUID
status
created_by_user_id UUID
approved_by_user_id nullable
dispatched_by_user_id nullable
received_by_user_id nullable
created_at
updated_at
```

### stock_transfer_items

```text
id UUID
tenant_id UUID
stock_transfer_id UUID
pharmacy_product_id UUID
requested_quantity_base
approved_quantity_base nullable
dispatched_quantity_base nullable
received_quantity_base nullable
```

### stock_transfer_batch_allocations

Track exact source batches.

Lifecycle:

```text
DRAFT
SUBMITTED
APPROVED
DISPATCHED
RECEIVED
CANCELLED
```

Rules:

- source and destination must belong to same tenant;
- source != destination;
- stock is deducted from source on dispatch, not on draft;
- destination stock increases on receive;
- batch traceability must be preserved.

A future "in transit" representation can be a dedicated ledger state or movement classification.

---

## 34. Sales Return Strategy

Do not implement sales returns until business rules are clarified.

Completed sales cannot be edited to restore stock.

When implemented later, introduce:

```text
sale_returns
sale_return_items
return_inspections
```

and use reversal stock movements.

---

## 35. Receipt Architecture

The backend exposes a normalized receipt representation.

Example endpoint:

```text
GET /api/v1/sales/{sale}/receipt
```

Response contains:

- pharmacy;
- branch;
- receipt number;
- cashier;
- customer;
- items;
- quantities;
- prices;
- discounts;
- totals;
- payment;
- date/time.

### Browser Printing

Support a printing adapter layer.

Potential strategies:

1. browser print/PDF;
2. WebUSB;
3. WebSerial;
4. local print bridge/agent;
5. vendor-specific integration.

Do not put printer-specific commands inside Sales domain services.

Recommended frontend contract:

```ts
interface ReceiptPrinter {
  print(receipt: ReceiptDocument): Promise<PrintResult>;
}
```

Implement adapters separately.

---

## 36. API Design

Base:

```text
/api/v1
```

Use resource-oriented routes.

Examples:

```text
POST   /api/v1/auth/login
POST   /api/v1/auth/logout

GET    /api/v1/branches

GET    /api/v1/master-drugs
GET    /api/v1/products
POST   /api/v1/products

GET    /api/v1/suppliers
POST   /api/v1/suppliers

POST   /api/v1/stock-receipts
GET    /api/v1/inventory
GET    /api/v1/inventory/expiring
POST   /api/v1/inventory/adjustments

POST   /api/v1/transfers
POST   /api/v1/transfers/{id}/submit
POST   /api/v1/transfers/{id}/approve
POST   /api/v1/transfers/{id}/dispatch
POST   /api/v1/transfers/{id}/receive

GET    /api/v1/customers
POST   /api/v1/customers

POST   /api/v1/prescriptions
GET    /api/v1/prescriptions/{id}

POST   /api/v1/sales/checkout
GET    /api/v1/sales/{id}
GET    /api/v1/sales/{id}/receipt

GET    /api/v1/reports/sales
GET    /api/v1/reports/inventory
GET    /api/v1/reports/profit
```

---

## 37. API Response Shape

Recommended success:

```json
{
  "data": {},
  "meta": {}
}
```

Recommended validation/error:

```json
{
  "error": {
    "code": "INSUFFICIENT_STOCK",
    "message": "Requested quantity is not available.",
    "details": {}
  }
}
```

Use stable machine-readable error codes.

Examples:

```text
TENANT_SUSPENDED
SUBSCRIPTION_EXPIRED
PACKAGE_LIMIT_REACHED
BRANCH_FORBIDDEN
PERMISSION_DENIED
INSUFFICIENT_STOCK
PRESCRIPTION_REQUIRED
INVALID_UNIT
EXPIRED_BATCH_BLOCKED
IDEMPOTENCY_CONFLICT
```

---

## 38. Validation

Use Laravel Form Requests for transport validation.

Business validation belongs in application/domain services.

Example:

Form Request:

```text
quantity is numeric > 0
product_id is UUID
```

Domain:

```text
product belongs to tenant
product active in branch
unit belongs to product
stock available
prescription requirement satisfied
```

Do not overload controllers with business rules.

---

## 39. Database Constraints

Use the database as a final protection layer.

Examples:

```text
CHECK available_quantity_base >= 0
CHECK multiplier_to_base > 0
CHECK selling_price >= 0
CHECK amount >= 0
```

Use composite foreign-key strategies where practical to prevent cross-tenant relationships.

For sensitive entities consider composite uniqueness:

```text
(tenant_id, id)
```

and ensure child rows carry the same tenant.

---

## 40. Indexing Strategy

Every frequently filtered tenant-owned table should begin with tenant-aware indexes.

Examples:

```text
sales:
(tenant_id, branch_id, completed_at)
(tenant_id, customer_id)
(tenant_id, idempotency_key)

inventory_batches:
(tenant_id, branch_id, pharmacy_product_id, expires_at)
(tenant_id, branch_id, expires_at)

inventory_movements:
(tenant_id, branch_id, pharmacy_product_id, occurred_at)
(reference_type, reference_id)

product_branch_settings:
(tenant_id, branch_id, pharmacy_product_id)

prescriptions:
(tenant_id, branch_id, customer_id, status)
```

Use `EXPLAIN ANALYZE` before adding speculative indexes.

---

## 41. N+1 Prevention

Rules:

- explicitly eager-load only needed relationships;
- use API Resources;
- use projections/select clauses;
- paginate all unbounded lists;
- prohibit accidental lazy loading in non-production/test environments where useful;
- include query-count tests for heavy endpoints.

---

## 42. Caching Strategy

Use Redis for:

- permission resolution;
- package entitlement caching;
- master-drug reference caching;
- low-volatility configuration;
- dashboard/report cache where appropriate.

Do not cache mutable stock availability for checkout authorization.

Transactional stock decisions must use PostgreSQL.

Cache key rule:

```text
salama:{environment}:{tenant_id}:{namespace}:{key}
```

For global catalog:

```text
salama:{environment}:global:drug-catalog:{key}
```

Always make tenant context explicit in cache keys.

---

## 43. Queues

Use Redis-backed Laravel queues for non-critical side effects:

- email notifications;
- notification fan-out;
- PDF generation where asynchronous behavior is acceptable;
- report generation;
- cleanup;
- future integrations.

Do not queue the authoritative stock deduction of a checkout.

Sale + stock deduction must complete synchronously in the database transaction.

---

## 44. Domain Events

Useful events:

```text
TenantRegistered
SubscriptionActivated
SubscriptionExpired

StockReceived
StockAdjusted
LowStockDetected
MedicineNearExpiry
MedicineExpired

TransferSubmitted
TransferApproved
TransferDispatched
TransferReceived

PrescriptionCreated
PrescriptionDispensed

SaleCompleted
ExpiredMedicineSold

PackageLimitApproaching
```

Events should trigger secondary behavior.

Do not use events to obscure core transactional invariants.

---

## 45. Notification Design

Define channel contract:

```php
interface NotificationChannel
{
    public function send(NotificationMessage $message): DeliveryResult;
}
```

Initial implementations:

```text
InAppNotificationChannel
EmailNotificationChannel
```

Future:

```text
SmsNotificationChannel
WhatsAppNotificationChannel
PushNotificationChannel
```

Notifications should be created from domain/application events and delivered asynchronously.

---

## 46. Audit Logging

Audit records are append-only.

Suggested model:

### audit_logs

```text
id UUID
tenant_id UUID nullable
branch_id UUID nullable
actor_user_id UUID nullable
action
entity_type
entity_id
before JSONB nullable
after JSONB nullable
metadata JSONB nullable
ip_address nullable
user_agent nullable
created_at
```

Do not store secrets or full sensitive payloads.

---

## 47. Logging

Use structured application logs.

Include identifiers:

```text
request_id
tenant_id
branch_id
user_id
sale_id
transfer_id
```

Never log:

- passwords;
- access tokens;
- session cookies;
- raw payment secrets.

---

## 48. Rate Limiting

Apply separate limits by endpoint risk.

Examples:

### Authentication

Strict:

```text
login
password reset
OTP if introduced
```

### Normal API

Tenant/user-based limit.

### Checkout

Rate limit carefully without breaking legitimate POS usage.

Idempotency protects duplicates; rate limiting protects abuse.

Redis-backed rate limiting is appropriate.

---

## 49. Security Controls

Required:

- HTTPS;
- secure cookies;
- CSRF for cookie-based auth;
- Laravel request validation;
- password hashing;
- authorization policies;
- tenant enforcement;
- rate limiting;
- secure headers;
- upload validation;
- MIME/content restrictions;
- object-storage access control;
- audit logging;
- dependency vulnerability monitoring;
- secrets via environment/secret manager;
- least-privilege database credentials;
- separate production environment.

---

## 50. React Architecture

Use React + TypeScript.

Recommended structure:

```text
src/
├── app/
│   ├── router/
│   ├── providers/
│   └── config/
├── features/
│   ├── auth/
│   ├── dashboard/
│   ├── pos/
│   ├── products/
│   ├── inventory/
│   ├── suppliers/
│   ├── prescriptions/
│   ├── customers/
│   ├── transfers/
│   ├── reports/
│   ├── users/
│   ├── roles/
│   ├── subscription/
│   └── settings/
├── shared/
│   ├── api/
│   ├── components/
│   ├── hooks/
│   ├── utils/
│   └── types/
└── main.tsx
```

---

## 51. React State Strategy

Separate three state categories.

### Server State

Use a server-state library such as TanStack Query.

Use for:

- products;
- inventory;
- reports;
- customers;
- subscriptions.

### UI State

Use component state or a small state store where needed.

Examples:

- active modal;
- POS cart;
- printer selection.

### Persisted Local State

Use carefully for:

- non-sensitive UI preferences;
- printer selection;
- draft UI state.

Offline sale queue is not required in MVP.

---

## 52. Optimistic Updates

Use optimistic UI only for low-risk operations.

Avoid optimistic checkout completion.

A sale must show `completed` only after the server transaction succeeds.

Safe candidates:

- certain non-critical preferences;
- notification read status.

---

## 53. PWA Scope

The web application may be installable as a PWA for convenience.

MVP PWA does not imply offline checkout.

Service worker may cache:

- static application shell;
- non-sensitive assets.

Do not cache authenticated API responses indiscriminately.

---

## 54. Flutter Strategy

Flutter is a later phase.

Backend APIs should remain client-independent.

The future app is expected to support operational actions based on permissions, with initial emphasis on owner/manager use.

Likely future features:

- sales dashboard;
- branch overview;
- inventory health;
- low-stock alerts;
- expiry alerts;
- transfers;
- selected operational actions;
- notifications.

Do not design backend endpoints exclusively around current web screens.

---

## 55. Testing Strategy

### Unit Tests

Test domain rules:

- unit conversion;
- FEFO allocation;
- expiry eligibility;
- subscription state;
- permission checks;
- gross-profit calculations.

### Feature Tests

Test HTTP/application workflows:

- tenant isolation;
- branch authorization;
- stock receipt;
- checkout;
- insufficient stock;
- expired stock rule;
- prescription requirement;
- package limits;
- read-only tenant behavior.

### Concurrency Tests

Critical.

Simulate simultaneous checkout requests against limited stock.

Expected invariant:

```text
available stock never becomes negative
```

### Idempotency Tests

Send duplicate checkout request with same idempotency key.

Expected:

```text
one sale
one stock deduction
same result returned
```

### Cross-Tenant Security Tests

For every major module:

```text
Tenant A must not read/update Tenant B data.
```

This should be part of the automated test suite.

---

## 56. Example Critical Checkout Test

Scenario:

```text
Available stock: 10 tablets

Cashier A requests 7
Cashier B requests 7 simultaneously
```

Expected:

- one transaction succeeds for 7;
- the second fails or receives only a policy-approved result;
- stock never becomes -4;
- only successful checkout creates completed sale/payment/movement.

---

## 57. Reporting Architecture

Do not make operational endpoints run huge analytical queries.

For MVP:

- optimized PostgreSQL aggregation;
- report-specific query services;
- date-range restrictions;
- indexes;
- pagination;
- caching of expensive read-only reports.

Later:

- materialized views;
- read replicas;
- analytics store if justified.

Do not add them prematurely.

---

## 58. Gross-Profit Query

Gross profit must use snapshot data:

```text
sale_items.net_amount - sale_items.cogs_amount
```

Do not join current batch cost to calculate historical profit.

---

## 59. Expiry Jobs

Use scheduled commands for proactive alert detection.

Example:

```text
php artisan schedule:run
```

Scheduled process:

```text
scan eligible batches
    ↓
calculate expiry state using tenant thresholds
    ↓
identify state transitions/alert conditions
    ↓
emit notification events
```

Avoid sending duplicate alerts every scheduler run.

Track alert state or deduplication key.

---

## 60. Read-Only Subscription Middleware

Recommended request pipeline:

```text
Authenticate
    ↓
ResolveTenant
    ↓
ResolveBranch
    ↓
CheckTenantStatus
    ↓
ResolveSubscription
    ↓
EnforceReadOnlyForMutations
    ↓
AuthorizePermission
    ↓
Controller
```

GET access can remain available after expiration, subject to permissions.

Write verbs/commands are blocked except explicit subscription-renewal flows.

---

## 61. Device Model

### devices

```text
id UUID
tenant_id UUID
branch_id UUID nullable
name
device_identifier
type
status
last_seen_at
registered_by_user_id
created_at
updated_at
```

Subscription module enforces device limit during activation/registration.

Do not use browser fingerprinting as the only device identity.

---

## 62. File Storage

Prescription attachments and generated documents should use object storage abstraction.

Examples:

- S3;
- S3-compatible storage.

Store path/reference in PostgreSQL.

Do not store large file blobs directly in primary transactional tables.

Validate uploads:

- size;
- MIME;
- extension;
- authorization.

---

## 63. PDF Receipts

PDF generation should consume the same receipt view model used by printer adapters.

Architecture:

```text
Sale
    ↓
ReceiptAssembler
    ↓
ReceiptDocument
       ├── HTML/PDF Renderer
       ├── ESC/POS Renderer
       └── Browser Renderer
```

This avoids duplicating sale formatting logic.

---

## 64. Time and Currency

Use:

```text
Currency: TZS
Timezone: Africa/Dar_es_Salaam
```

Store timestamps in UTC in the database where practical.

Convert to tenant/local timezone at the application/UI boundary.

Money should use decimal/fixed precision or a Money value object.

Never use binary floating point for authoritative financial calculations.

---

## 65. IDs

Prefer UUID/ULID identifiers for public-facing distributed-safe IDs.

If using bigint internally, never expose predictable IDs where unnecessary.

Be consistent across modules.

---

## 66. Soft Delete Policy

Do not use soft deletes as a substitute for transactional history.

Suitable candidates:

- configurable reference entities;
- users;
- suppliers;
- products.

Transactional records:

- sales;
- stock movements;
- batch allocations;
- payments;

should generally remain immutable and not be destructively deleted.

---

## 67. Migration Strategy

Rules:

- migration files are forward-only deployment artifacts;
- avoid editing already-deployed migrations;
- add constraints explicitly;
- add indexes intentionally;
- test production-size migrations;
- avoid long table locks where possible.

---

## 68. Seed Data

Seed only stable platform-level references.

Examples:

- permission definitions;
- Basic/Standard/Premium package templates;
- initial system settings;
- development-only fake catalog separately.

Do not mix test/demo data with production seeders.

---

## 69. Observability

Minimum production observability:

- structured logs;
- error tracking;
- queue monitoring;
- slow query monitoring;
- health endpoints;
- request IDs;
- uptime checks.

Recommended health endpoints:

```text
/health/live
/health/ready
```

Readiness should verify critical dependencies such as PostgreSQL and optionally Redis.

---

## 70. Performance Guardrails

Required:

- no unbounded collection endpoints;
- cursor or page pagination;
- max report date ranges where needed;
- API resources with selective fields;
- query profiling;
- no stock authorization from cache;
- no large eager-loaded graphs;
- background processing for slow non-transactional tasks.

---

## 71. Backup and Recovery

Production must define:

- PostgreSQL automated backups;
- point-in-time recovery where supported;
- object-storage backup/version policy;
- encrypted backup storage;
- tested restoration procedure.

A backup strategy is incomplete until restoration is tested.

---

## 72. Deployment

Initial practical deployment:

```text
Reverse Proxy / Load Balancer
        ↓
Laravel App Containers
        ↓
PostgreSQL
Redis
Object Storage
```

Use separate queue workers.

Use Docker for reproducible deployment.

Do not run scheduler and queue workloads accidentally inside every web replica.

---

## 73. CI/CD

Pipeline should run:

1. dependency install;
2. lint/static checks;
3. unit tests;
4. feature tests;
5. tenant-isolation tests;
6. build frontend;
7. security/dependency checks;
8. migration validation;
9. deploy;
10. health check.

---

## 74. Code Quality

Recommended PHP tooling:

- Laravel Pint;
- PHPStan/Larastan;
- Pest or PHPUnit.

Recommended frontend:

- TypeScript strict mode;
- ESLint;
- formatter;
- component/unit tests where valuable.

---

## 75. Coding Rule

Controllers should remain thin.

Bad:

```text
Controller:
- permission
- inventory query
- FEFO
- sale calculation
- payment
- notification
```

Good:

```text
Controller
    ↓
CheckoutSaleService
        ↓
InventoryAllocationService
        ↓
PrescriptionPolicy
        ↓
PaymentRecorder
```

---

## 76. Transaction Boundaries

Use short database transactions.

Do inside checkout transaction:

- lock stock;
- calculate authoritative values;
- create sale;
- create payment;
- update batches;
- create movements.

Do outside transaction:

- email;
- PDF rendering;
- notifications;
- analytics refresh.

Use after-commit event dispatch when needed.

---

## 77. Concurrency Policy

PostgreSQL is the authoritative concurrency boundary.

For critical stock consumption use:

```text
SELECT ... FOR UPDATE
```

or an equally strong atomic-update strategy.

Do not use Redis distributed locks as the primary stock correctness mechanism.

Redis locks may complement some workflows but cannot replace transactional database constraints.

---

## 78. Future Fiscalization Integration Boundary

TRA fiscalization is not MVP but should be isolated later behind:

```php
interface FiscalizationGateway
{
    public function fiscalize(SaleFiscalDocument $document): FiscalizationResult;
}
```

Sales should not directly depend on a specific TRA implementation.

Possible future implementations:

```text
TraVfdGateway
MockFiscalizationGateway
```

---

## 79. Future Payment Integration Boundary

Manual MNO recording is MVP.

Future payment APIs should implement:

```php
interface PaymentProviderGateway
{
    public function initiate(...);
    public function verify(...);
    public function status(...);
}
```

The current `Payment` domain should not assume every payment is externally verified.

---

## 80. Future B2B Supply Chain Boundary

Procurement should be designed so a future marketplace can submit supplier orders without directly modifying inventory.

Future flow:

```text
Marketplace Order
    ↓
Procurement
    ↓
Goods Receipt
    ↓
Inventory
```

Inventory must only increase when goods are actually received, not merely ordered.

---

## 81. Recommended Implementation Order

### Phase 0 — Foundation

1. repository standards;
2. Docker;
3. Laravel 11;
4. PostgreSQL;
5. Redis;
6. CI;
7. logging/error tracking;
8. module skeleton.

### Phase 1 — Identity & Tenancy

1. authentication;
2. tenants;
3. branches;
4. tenant context;
5. branch context;
6. users;
7. roles;
8. permissions;
9. cross-tenant tests.

### Phase 2 — Subscription

1. packages;
2. entitlements;
3. subscriptions;
4. read-only enforcement;
5. package limits.

### Phase 3 — Drug/Product Foundation

1. generic drugs;
2. master drugs;
3. tenant products;
4. units;
5. branch pricing;
6. branch reorder levels;
7. tenant expiry settings.

### Phase 4 — Supplier & Inventory

1. suppliers;
2. stock receipt;
3. batches;
4. inventory ledger;
5. expiry calculation;
6. low stock;
7. stock adjustments.

### Phase 5 — Customers & Prescriptions

1. customer CRUD;
2. prescription;
3. prescription items;
4. prescription validation.

### Phase 6 — POS

1. cart contract;
2. server price resolution;
3. FEFO allocation;
4. row locking;
5. checkout;
6. idempotency;
7. payments;
8. receipts;
9. concurrency tests.

### Phase 7 — Transfers

1. transfer lifecycle;
2. permissions;
3. dispatch stock deduction;
4. receive stock creation.

### Phase 8 — Reporting

1. sales;
2. inventory;
3. expiry;
4. low stock;
5. profit;
6. purchases;
7. transfers.

### Phase 9 — Notifications

1. in-app;
2. email;
3. scheduled expiry alerts;
4. subscription alerts;
5. package-limit alerts.

### Phase 10 — React SPA

Build screens after backend contracts stabilize.

### Phase 11 — Hardening

1. load testing;
2. tenant isolation audit;
3. authorization audit;
4. concurrency tests;
5. DB query review;
6. backup restore test;
7. deployment runbook.

---

## 82. Required Architectural Decision Records

Create ADRs for at least:

```text
ADR-001 Modular Monolith
ADR-002 Shared-Schema Multi-Tenancy
ADR-003 Dynamic RBAC
ADR-004 Base-Unit Inventory
ADR-005 FEFO Allocation
ADR-006 PostgreSQL Row Locking for Checkout
ADR-007 Checkout Idempotency
ADR-008 Subscription Read-Only Mode
ADR-009 Pluggable Notification Channels
ADR-010 Printer Adapter Architecture
```

Each ADR:

```text
Context
Options
Decision
Reason
Trade-offs
Consequences
```

---

## 83. Definition of Done for a Backend Feature

A backend feature is not complete until it includes:

- domain rule implementation;
- tenant enforcement;
- branch authorization where applicable;
- permission check;
- validation;
- database constraints where applicable;
- appropriate indexes;
- audit behavior;
- tests;
- API resource/response;
- error codes;
- logging consideration;
- documentation.

---

## 84. MVP Technical Invariants

The codebase must preserve:

```text
Tenant isolation
Branch isolation
Non-negative batch stock
One checkout per idempotency key
Atomic sale + stock deduction
Stable historical sale price
Stable historical COGS
FEFO allocation
Prescription enforcement
No completed-sale deletion
Inventory movement for every stock mutation
Read-only expired subscription
Server-side package limits
Server-side permissions
Server-side price calculation
Printer failure independent of sale success
```

---

## 85. Open Technical Decisions

The following should be resolved before their respective implementation stage:

1. Exact authentication topology for SPA deployment.
2. Whether UUID or ULID is the project-wide identifier.
3. Whether tenant-local products may exist without a master-drug link.
4. Exact mandatory customer fields.
5. Exact prescription mandatory fields and attachment policy.
6. Sales-return workflow.
7. Exact transfer approval permissions.
8. Exact MNO provider reference list.
9. Exact device activation/trust model.
10. Exact subscription billing/renewal payment flow.
11. Whether product pricing supports time-based price history in MVP.
12. Whether discount limits are percentage-based, amount-based, or both.

---

## 86. Final Development Rule

Before implementing any use case, write down:

```text
What invariant could this feature violate?
```

Then identify:

```text
Application validation
Authorization
Database constraint
Transaction boundary
Concurrency control
Audit requirement
Test proving the invariant
```

For SalamaPharma, correctness of stock, tenant isolation, money, and audit history takes priority over implementation shortcuts.
