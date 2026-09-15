# SalamaPharma — Business Scope

**Document status:** MVP Source of Truth  
**Product:** SalamaPharma  
**Market:** Tanzania  
**Primary customer:** Retail pharmacy businesses  
**Architecture context:** B2B multi-tenant SaaS  
**Version:** 0.1

---

## 1. Purpose

SalamaPharma is a multi-tenant SaaS platform for retail pharmacies in Tanzania.

The MVP focuses on the operational core required to run a retail pharmacy safely and efficiently:

- pharmacy business and branch management;
- users, dynamic roles, and permissions;
- medicine catalog and pharmacy-specific products;
- batch and expiry management;
- branch inventory;
- supplier management;
- simple stock receiving;
- prescriptions;
- point of sale;
- cash and mobile-network-operator payment recording;
- configurable medicine pricing;
- gross-profit reporting;
- subscriptions and package limits;
- in-app and email notifications;
- receipt printing and reprinting;
- auditability.

The MVP intentionally excludes broad ERP functionality such as accounting, HR/payroll, expenses, wholesaler marketplace functionality, stock-taking, offline POS, fiscalization, and direct payment-provider integrations.

---

## 2. Product Goals

SalamaPharma should help a retail pharmacy:

1. Know what medicine is available in each branch.
2. Track medicine by batch and expiry date.
3. Reduce losses caused by expired or near-expiry medicines.
4. Sell medicines in flexible retail units such as box, blister, bottle, or tablet where applicable.
5. Prevent cross-tenant data access.
6. Prevent overselling and duplicate checkout.
7. Support branch-specific prices and stock.
8. Track the exact cost consumed during each sale for gross-profit reporting.
9. Enforce prescription requirements for configured medicines.
10. Keep stock and sale operations auditable.
11. Allow the pharmacy owner/admin to control users through permissions rather than fixed operational role names.
12. Support commercial subscription packages with branch, user, device, stock, and transaction limits.
13. Provide a foundation that can later support mobile operations, B2B supply chain, fiscalization, payment integrations, and regional expansion.

---

## 3. Non-Goals for MVP

The following are explicitly outside the first MVP:

- wholesale/distributor workflows;
- B2B pharmacy marketplace;
- supplier credit balances;
- accounts payable;
- full accounting;
- expense management;
- HR/payroll;
- stock-taking / physical stock count;
- cashier cash drawer and shift reconciliation;
- offline POS;
- real-time mobile-money integration;
- TRA VFD/EFD fiscalization;
- insurance integration;
- NHIF integration;
- pharmacy regulatory verification workflows;
- controlled-drug special workflows;
- SMS notifications;
- WhatsApp notifications;
- Flutter operational application;
- multi-branch user assignment;
- formal purchase-order approval workflow;
- supplier invoice settlement workflows.

These may be added later without redesigning the core domains.

---

## 4. Tenant Model

The business hierarchy is:

```text
SalamaPharma Platform
    └── Pharmacy Business (Tenant)
          ├── Branch A
          ├── Branch B
          └── Branch N
                └── Users
```

### 4.1 Tenant

A tenant represents one pharmacy business.

A tenant may have one or multiple branches depending on its subscription package.

Every tenant-owned business record must belong to exactly one tenant.

### 4.2 Branch

Inventory, batch quantities, sales, prices, and transactions are branch-specific.

A branch cannot access another tenant's data.

### 4.3 User

For MVP, one operational user belongs to one branch.

Exceptions:

- Platform Superadmin is not a tenant user.
- Pharmacy Admin represents the tenant-level administrator and may require tenant-wide administration capabilities.

Future versions may support users assigned to multiple branches.

---

## 5. Actors

### 5.1 Platform Superadmin

The only fixed platform role.

Capabilities may include:

- create and manage tenants;
- activate, suspend, or restore tenants;
- manage subscription packages;
- manage package limits and feature entitlements;
- manage the global medicine catalog;
- view platform-level operational metrics;
- manage tenant subscriptions;
- support tenant onboarding;
- audit sensitive platform actions.

### 5.2 Pharmacy Admin

The tenant-level administrative actor.

This role is reserved as a fixed administrative concept.

Capabilities may include:

- manage branches;
- manage tenant users;
- create dynamic roles;
- assign permissions;
- configure pharmacy settings;
- manage products, prices, and stock policies;
- access reports;
- manage subscription-related information.

### 5.3 Dynamic Tenant Roles

Operational roles must not be hard-coded.

Examples may include:

- Pharmacist
- Pharmacist Manager
- Cashier
- Store Keeper
- Supervisor

These are examples only.

A Pharmacy Admin creates roles and assigns permissions.

Authorization must be permission-driven.

---

## 6. Tenant Onboarding

A tenant may be created through either:

1. Self-registration by the pharmacy business.
2. Manual creation by Platform Superadmin.

Pharmacy-license verification is not required in the MVP.

The onboarding process should collect the minimum business information required to create the tenant and first branch.

Suggested initial information:

- pharmacy/business name;
- owner/admin name;
- email;
- phone number;
- physical location;
- first branch name;
- first branch location;
- selected package;
- authentication credentials.

---

## 7. Subscription Model

SalamaPharma uses package-based subscriptions.

Initial commercial package names:

- Basic
- Standard
- Premium

Package configuration must remain data-driven rather than hard-coded.

A package may control limits such as:

- number of branches;
- number of users;
- number of devices;
- stock/product count;
- transaction count;
- enabled features.

### 7.1 Subscription Status

Recommended statuses:

```text
TRIAL
ACTIVE
EXPIRED
SUSPENDED
CANCELLED
```

A future grace-period state may be introduced if commercially required.

### 7.2 Expired Subscription Behavior

When a subscription expires, the tenant enters **read-only mode**.

In read-only mode:

Allowed:

- authentication;
- viewing existing information;
- viewing reports;
- viewing subscription details;
- accessing renewal flow.

Blocked:

- new sale;
- stock receipt;
- stock adjustment;
- stock transfer;
- medicine creation;
- price changes;
- user creation;
- other state-changing operational actions.

The exact set of read-only endpoints must be centrally enforced.

---

## 8. Master Drug Catalog

SalamaPharma maintains a global Master Drug Database controlled by Platform Superadmin.

The Master Drug Database provides reference information and must remain separate from a tenant's local sellable product.

### 8.1 Master Drug Concepts

The catalog must support both generic and branded concepts.

Example:

```text
Generic:
Paracetamol

Brand/Product:
Panadol 500mg Tablet
```

Possible master attributes:

- generic name;
- brand/trade name;
- dosage form;
- strength;
- manufacturer;
- standard barcode where available;
- category;
- descriptive metadata.

Regulatory reference data may be added later.

### 8.2 Tenant Product

A tenant selects or derives a local pharmacy product from the master catalog.

The tenant can then define operational information such as:

- enabled branches;
- retail units;
- conversion rules;
- reorder level per branch;
- selling price per branch;
- prescription requirement;
- expiry-sale policy;
- local barcode where needed;
- status.

The global catalog must never own tenant stock or tenant prices.

---

## 9. Medicine Units and Packaging

SalamaPharma must support retail sale in smaller units.

Example:

```text
1 Box = 10 Blisters
1 Blister = 10 Tablets
1 Box = 100 Tablets
```

A medicine must have a canonical base inventory unit.

Example:

```text
Base unit: Tablet
```

Purchases and sales may occur in alternate units, but all stock quantities must be converted to the base unit for authoritative inventory calculations.

### Business Rule

The system must not independently maintain contradictory balances for:

- boxes;
- blisters;
- tablets.

Instead:

```text
Stock = base-unit quantity
```

Packaging definitions are conversion metadata.

Example:

```text
Box     -> multiplier 100
Blister -> multiplier 10
Tablet  -> multiplier 1
```

A branch may then sell:

- 1 box;
- 2 blisters;
- 3 tablets;

while authoritative stock remains mathematically consistent.

---

## 10. Branch Inventory

Inventory is branch-specific.

Each branch owns its own:

- stock balance;
- batches;
- reorder level;
- selling price;
- stock movements;
- expiry alerts.

A product may have stock in Branch A and no stock in Branch B.

Stock must never be inferred from another branch.

---

## 11. Batch Management

A received medicine may create one or more batches.

A batch should capture:

- tenant;
- branch;
- pharmacy product;
- batch number;
- received date;
- manufacture date when supplied;
- expiry date;
- received quantity in base units;
- available quantity in base units;
- purchase cost;
- supplier/source;
- status.

Every stock-consuming sale must identify which batch quantities were consumed.

---

## 12. FEFO Stock Consumption

SalamaPharma uses:

**FEFO — First Expiry, First Out**

The default sale-allocation rule is:

> Sell from the eligible batch with the nearest expiry date first.

Example:

```text
Batch A expires: 2026-10-01
Batch B expires: 2027-03-01

Consume Batch A before Batch B.
```

The system should be able to split a sale quantity across multiple batches when the first eligible batch is insufficient.

Example:

```text
Customer buys 15 tablets.

Batch A available = 8
Batch B available = 20

Allocation:
Batch A -> 8
Batch B -> 7
```

Each allocation must be preserved for audit and profit calculations.

---

## 13. Expiry Policy

Expiry thresholds are configurable per tenant.

Suggested default:

- RED: less than 30 days to expiry;
- YELLOW: 30–90 days;
- GREEN: more than 90 days.

These thresholds are defaults, not fixed business constants.

### 13.1 Selling Expired Medicines

Each tenant has a setting controlling expired-batch sale behavior.

Recommended setting:

```text
block_expired_sales = true | false
```

When `true`:

- expired batches are ineligible for POS allocation.

When `false`:

- system may allow the sale;
- the user must receive a clear warning;
- the event should be auditable.

The platform does not currently define regulatory legality of selling expired medicines. Regulatory verification is outside the MVP scope.

---

## 14. Low Stock

Low-stock threshold is configured per product per branch.

Example:

```text
Branch A:
Paracetamol reorder level = 500 tablets

Branch B:
Paracetamol reorder level = 200 tablets
```

The system should generate a low-stock alert when available stock reaches or falls below the configured threshold.

---

## 15. Suppliers

The MVP includes basic supplier management.

Supplier information may include:

- supplier name;
- contact person;
- phone;
- email;
- address;
- status;
- notes.

Supplier credit and payable balances are not part of MVP.

---

## 16. Purchasing and Stock Receiving

The long-term product may support a complete supply-chain workflow.

For MVP, SalamaPharma supports a simplified purchasing/receiving workflow.

### MVP Flow

```text
Supplier
    ↓
Receive Stock
    ↓
Capture medicine/product
    ↓
Capture batch
    ↓
Capture expiry
    ↓
Capture quantity/unit
    ↓
Capture purchase cost
    ↓
Convert to base units
    ↓
Create inventory movement
    ↓
Increase branch stock
```

Formal purchase-order approval, supplier invoice settlement, and supplier-credit accounting are outside MVP.

A stock receipt must be atomic: stock quantity and its inventory movement must be created together.

---

## 17. Prescriptions

Prescription management is part of MVP.

A product may be configured as:

```text
prescription_required = true | false
```

When a product requires a prescription, checkout must enforce the required prescription workflow.

Suggested prescription data:

- patient/customer;
- prescriber name;
- prescription date;
- reference number where applicable;
- notes;
- attachment/image when applicable;
- prescribed items;
- dispensing status.

### Recommended Statuses

```text
OPEN
PARTIALLY_DISPENSED
DISPENSED
CANCELLED
```

Detailed regulatory requirements are not part of the current scope.

---

## 18. Customers

Anonymous walk-in checkout is not enabled in the confirmed MVP scope.

A sale must therefore be associated with a customer record.

Minimum customer fields should remain lightweight so POS remains fast.

Suggested minimum:

- name;
- phone number where available.

This rule should be revisited during usability testing because mandatory customer capture can slow high-volume retail checkout.

---

## 19. Branch Pricing

The same product may have different selling prices in different branches.

Example:

```text
Paracetamol / Tablet

Branch A -> TZS 200
Branch B -> TZS 250
```

The backend is authoritative for price.

The frontend must not be trusted to submit the final authoritative selling price.

---

## 20. Discounts

Discounting is permission-controlled.

Do not hard-code discounts by role name.

Possible permissions:

```text
sale.discount.apply
sale.discount.override
```

Future implementation may optionally support a maximum discount threshold per role/user.

Any applied discount must be stored with the sale for audit and reporting.

---

## 21. Point of Sale

The POS is a core MVP module.

### 21.1 POS Flow

```text
Select branch
    ↓
Select customer
    ↓
Search/scan medicine
    ↓
Choose sale unit
    ↓
Enter quantity
    ↓
Validate prescription if required
    ↓
Calculate authoritative price
    ↓
Validate stock
    ↓
Allocate FEFO batches
    ↓
Apply permitted discount
    ↓
Choose payment method
    ↓
Confirm checkout
    ↓
Create sale
    ↓
Create sale items
    ↓
Create batch allocations
    ↓
Create stock movements
    ↓
Record payment
    ↓
Generate receipt
```

### 21.2 Concurrency

Checkout must prevent two simultaneous sales from consuming the same stock.

### 21.3 Atomicity

A sale must not exist as completed if its stock deduction failed.

Stock must not be deducted if sale creation failed.

### 21.4 Idempotency

Retrying a checkout request must not create a duplicate sale.

---

## 22. Payment Methods

MVP supports:

- Cash
- Mobile Network Operator (MNO)

No direct payment gateway/API integration is required.

The cashier manually records the relevant mobile-money information.

Suggested fields:

- payment method;
- MNO/provider;
- external reference;
- amount.

The list of supported MNO providers should be configurable rather than hard-coded into transaction logic.

---

## 23. Offline Operation

Offline POS is **not part of MVP**.

All checkout operations require connectivity to the backend.

The architecture should avoid choices that would make offline synchronization impossible later, but no offline queue/sync engine is required now.

---

## 24. Stock Adjustments

Stock adjustment is supported.

Every adjustment must require:

- product;
- branch;
- quantity delta;
- reason;
- actor;
- timestamp.

Recommended reasons:

```text
DAMAGED
EXPIRED
LOST
CORRECTION
OTHER
```

Free-text notes may be required for `OTHER`.

Stock must never be edited directly without a stock movement.

---

## 25. Stock Transfers

Inventory transfer between branches is supported.

A transfer is only allowed between branches belonging to the same tenant.

Recommended lifecycle:

```text
DRAFT
    ↓
SUBMITTED
    ↓
APPROVED
    ↓
DISPATCHED
    ↓
RECEIVED
```

The system must distinguish:

- source available stock;
- stock in transit;
- destination received stock.

Transfer operations must preserve batch traceability where applicable.

---

## 26. Stock Taking

Physical stock-taking / cycle counting is not included in MVP.

The architecture may add it later as a separate inventory workflow.

---

## 27. Sales Returns

The user's response to the return requirement was not sufficiently specific to define the workflow safely.

Therefore:

**Sales-return functionality is not considered confirmed for MVP.**

Until explicitly defined:

- completed sales cannot be deleted;
- inventory must not be automatically restored by editing a completed sale;
- any future return must be implemented as a separate auditable transaction.

Open questions remain:

1. Who may initiate a return?
2. Is manager approval required?
3. Which medicines are returnable?
4. Does returned stock require inspection?
5. When can returned stock become sellable again?

---

## 28. Sale Cancellation and Deletion

Completed sales must never be deleted.

If cancellation/voiding is introduced, it must use an auditable reversal/void workflow.

The original transaction must remain preserved.

Minimum audit data:

- original sale;
- reason;
- actor;
- timestamp;
- reversal stock movements;
- payment impact.

---

## 29. Gross Profit

Gross-profit reporting is required.

Profit must be based on the actual stock cost consumed from batch allocations.

Example:

```text
Sold:
10 tablets at TZS 500 = TZS 5,000 revenue

Consumed batches:
5 tablets @ TZS 250 cost
5 tablets @ TZS 300 cost

COGS:
(5 × 250) + (5 × 300)
= 2,750

Gross Profit:
5,000 - 2,750
= 2,250
```

Historical sale cost must not change if the current purchase cost later changes.

---

## 30. Reporting

MVP reporting should include:

- daily sales;
- sales by branch;
- sales by user;
- sales by product;
- sales by payment method;
- gross profit;
- inventory balance;
- low stock;
- near expiry;
- expired stock;
- stock movements;
- stock adjustments;
- stock transfers;
- purchase/receipt history;
- prescription activity;
- subscription usage/limits.

Reports must always enforce tenant and branch authorization.

---

## 31. Notifications

The notification module must be designed around pluggable channels.

Initial channels:

- In-App
- Email

Future channels:

- SMS
- WhatsApp
- Push Notification

Candidate notification events include:

- low stock;
- near-expiry medicine;
- expired medicine;
- unusual stock adjustment;
- transfer status changes;
- subscription nearing expiration;
- subscription expired;
- package limit nearing exhaustion.

Notification delivery failure must not roll back the originating business transaction.

---

## 32. Receipt Management

The MVP supports:

- thermal receipt printing;
- receipt reprinting;
- digital/PDF receipt generation.

Target physical environments are mixed:

- Windows desktops/laptops;
- standard POS computers;
- USB printers;
- Bluetooth printers;
- other compatible counter devices.

Receipt printing must be independent from sale completion.

A successful sale remains successful even if printing fails.

Reprinting must never create a new sale.

---

## 33. Devices

Subscription packages may limit the number of devices.

A device-registration model should exist so the tenant's package can enforce its allowed device count.

Exact device-trust and activation rules are technical concerns documented separately.

---

## 34. Audit Requirements

The following events must be auditable:

- stock receipt;
- stock adjustment;
- stock transfer;
- price change;
- role/permission change;
- prescription dispensing;
- sale;
- sale void/reversal when introduced;
- expired-medicine sale when permitted;
- subscription change;
- tenant suspension;
- package change;
- sensitive setting change.

Audit data should capture:

- tenant;
- branch when applicable;
- actor;
- action;
- entity;
- entity identifier;
- timestamp;
- meaningful before/after values where appropriate.

---

## 35. Regulatory Scope

Tanzanian pharmacy-specific legal and regulatory validation is intentionally deferred.

Items to verify in a later compliance phase include:

- Pharmacy Council requirements;
- TMDA-related medicine information;
- controlled medicines;
- prescription retention rules;
- fiscal receipt requirements;
- TRA fiscalization.

No unverified regulatory rule should be treated as a product invariant.

---

## 36. Future Scope

Potential future capabilities:

- formal purchase orders;
- supplier invoice processing;
- supplier credit;
- full procurement workflow;
- stock taking;
- cashier shift/cash drawer;
- sales returns;
- Flutter operational app;
- multi-branch user assignment;
- offline POS;
- B2B supplier marketplace;
- distributor integrations;
- mobile-money API integration;
- SMS;
- WhatsApp;
- push notifications;
- TRA fiscalization;
- controlled-drug workflows;
- regulatory integrations;
- insurance;
- accounting;
- expenses;
- HR/payroll;
- e-commerce;
- East African market expansion.

---

## 37. Critical Business Invariants

The implementation must protect these invariants:

1. One tenant must never access another tenant's data.
2. A user must never operate on an unauthorized branch.
3. A sale must never deduct stock twice because of retry.
4. A completed sale must never disappear through destructive deletion.
5. Every inventory change must have a corresponding stock movement.
6. Branch stock must remain independent.
7. FEFO must be applied when allocating eligible batches.
8. A prescription-required product must not bypass the prescription rule.
9. Historical sale cost must remain stable.
10. Package limits must be enforced server-side.
11. Expired tenants must not perform write operations.
12. Prices and final totals must be validated by the backend.
13. Permission checks must be enforced by the backend.
14. Printing failure must never invalidate a completed sale.
15. A transfer must never create stock at the destination without a valid source movement.
16. Batch quantity must never become negative.

---

## 38. Remaining Business Clarifications

The following are deliberately unresolved:

### 38.1 Sales Returns

Return workflow and stock-restoration rules need a separate decision.

### 38.2 Mandatory Customer Capture

The current requirement disallows anonymous walk-in sales.

This means every sale requires a customer record.

This is treated as confirmed for now but should be validated against real pharmacy checkout speed.

### 38.3 Prescription Detail Rules

Prescription support is confirmed, but detailed rules still need future clarification:

- mandatory fields;
- document attachment requirement;
- partial dispensing behavior;
- repeat dispensing;
- prescription validity period.

### 38.4 Stock Transfer Approval

Transfers are required, but the exact permission/approval matrix can remain configurable through permissions.

### 38.5 Mobile Money Provider List

Payment type `MNO` is confirmed.

The specific provider list should be maintained as configurable reference data.

---

## 39. MVP Definition

SalamaPharma MVP is considered complete when a retail pharmacy can:

1. Create a tenant and branch.
2. Subscribe to a package.
3. Create users, roles, and permissions.
4. Select medicines from the master catalog.
5. Configure retail units and conversions.
6. Configure branch prices and reorder levels.
7. Register suppliers.
8. Receive stock with batch, expiry, and cost.
9. View branch stock and expiry alerts.
10. Configure expired-sale behavior.
11. Register customers.
12. Register prescriptions.
13. Sell products using FEFO.
14. Deduct stock safely under concurrent checkout.
15. Record cash or MNO payment.
16. Print or reprint a receipt.
17. Generate a digital/PDF receipt.
18. Adjust stock with reasons.
19. Transfer stock between branches.
20. View core sales, inventory, expiry, and gross-profit reports.
21. Receive in-app/email notifications.
22. Enter read-only mode when subscription expires.
23. Enforce package limits.
24. Preserve an audit trail for sensitive operations.
