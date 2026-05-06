# Fuel Monitoring System

A comprehensive fuel monitoring and management system built with Laravel 12, Livewire 4, and Bootstrap 5.3.

## Overview

This application allows authorized users to log the utilization of assets (kilometers and hours), calculate estimated fuel consumption, and issue fuel orders to replenish tanks.

## Tech Stack

- **Framework:** Laravel 12
- **Frontend:** Livewire 4
- **Styling:** Bootstrap 5.3 (SCSS)
- **Icons:** Bootstrap Icons
- **Database:** MySQL v9.6.0
- **Build Tool:** Vite 7

## Key Features

- User authentication and role-based access control (Administrator, Moderator, Data Logger, Budgeteer).
- Asset management and classification.
- Real-time utilization logging and fuel order processing.
- Chargeable account and budget tracking.
- Comprehensive reporting (Asset Utilization, Fuel Orders Summary, Chargeable Accounts).
- Material 3 inspired dark mode theme.

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL 9.6+

### Installation

1. Clone the repository.
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Install NPM dependencies:
   ```bash
   npm install
   ```
4. Copy `.env.example` to `.env` and configure your database.
5. Generate application key:
   ```bash
   php artisan key:generate
   ```
6. Run migrations:
   ```bash
   php artisan migrate
   ```
7. Build assets:
   ```bash
   npm run build
   ```

## Development & Testing

### Commands

- **Local Server:** `php artisan serve`
- **Vite Dev Server:** `npm run dev`
- **Run Tests:** `composer test`
- **Static Analysis:** `composer stan`

### Testing Warning
Always run `php artisan config:clear` before running tests manually to ensure the SQLite in-memory database is used correctly. Using `composer test` handles this automatically.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
