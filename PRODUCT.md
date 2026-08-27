# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack
Laravel 12, Livewire 4, MySQL v9.6.0, Bootstrap 5.3 (SCSS)

## Users
- **Fuel-Man:** Logs equipment and asset utilization, issues prescribed fuel orders based on calculated consumption.
- **Budgeteer:** Reviews and manages chargeable account budgets and sub-account allocations.
- **Moderator/Manager:** Manages users, assets, chargeable accounts, and overrides system configurations or issues waivers.
- **System Administrators:** Oversees system health, backups, and security.

## Product Purpose
The Fuel Monitoring Management System ensures precise, accountable, and auditable logging of vehicle and equipment utilization (kilometers/hours). It computes fuel consumption based on pre-defined factors to issue precise, authorized fuel orders, preventing unauthorized fuel dispensing and keeping fleet operations within budget limits.

## Positioning
An integrated, real-time fleet utilization and fuel dispensation tracking system. Unlike generic asset management software, it closes the loop between actual physical usage (kilometer/hour readings) and fuel order generation, enforcing budget and authorization policies automatically.

## Operating Context
- **Physical Environment:** Fuel stations, depots, construction sites, and remote project locations where equipment is active.
- **Operational Cycle:** Daily utilization entries logged by operators or fuel-men, triggering automated calculation of required fuel, followed by immediate fuel order generation and physical dispensation verification.
- **Budget Cycle:** Periodic (periodic/scoped) account budgeting reviewed by budgeteers to control operating expenses.

## Capabilities and Constraints
- **Utilization Tracking:** Supports both kilometer-based (for vehicles) and hour-based (for stationary/heavy equipment) tracking.
- **Automated Estimation:** Computes estimated fuel replenishment requirements dynamically using asset-specific consumption factors.
- **Role-Based Access Control:** Strict permission gating for fuel logs, budget management, and order status.
- **Database Safeguards:** Local testing is constrained by `RefreshDatabase`; configuration cache must be cleared (`php artisan config:clear`) before testing to prevent wiping the local development database.

## Brand Commitments
- **Name:** Fuel Monitoring Management System
- **Theme:** Strict Dark Mode theme using Bootstrap's `data-bs-theme="dark"` styling.
- **UI Elements:** Consistent look-and-feel across all tables, buttons, and clickable links.
- **Interaction Rules:** Clickable elements must have a pointer cursor on hover.
- **Assets:** `logo.svg`, `gas-pump.svg`

## Evidence on Hand
- **Codebase:** Full Laravel + Livewire codebase, including models (`Asset`, `FuelOrder`, `SubAccountBudget`, `UtilizationEntry`), controllers, and Livewire components (`CreateFuelOrder`).
- **Database Schema:** 50+ database migrations detailing structure for assets, accounts, budgets, and orders.
- **Test Coverage:** Extensive PHPUnit feature tests (`AssetFeatureTest`, `BudgeteerAccessTest`, `FuelOrderFeatureTest`, etc.).

## Product Principles
- **Precision Over Guesswork:** All fuel orders must align mathematically with verified physical utilization logs.
- **Auditability & Accountability:** Every fuel order must trace back to a specific asset, user, chargeable account, and budget line.
- **Data Safety First:** Keep configuration and test execution strictly separate to avoid accidental local database loss.

## Accessibility & Inclusion
- Readable typography and high-contrast color scheme tailored for outdoor environments (e.g., fuel-men logging on mobile devices under direct sunlight).
- Accessible form controls with clear visual error states.
