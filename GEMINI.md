# Project Context: This is a fuel monitoring management system where authorized users can log the utilization of each asset or equipment based on their kilometer and hour reading. The utilization data is then used to compute the estimated consumed fuel based on the defined factor so the users can issue the prescribed fuel order to replenish the asset or equipment's fuel tank.

## Overview
This project is a fuel monitoring management system built with Laravel 12, Livewire 4, MySQL v9.6.0, and Tailwind CSS 4

## Key Features
*   User authentication via Laravel Sanctum for API access.
*   Real-time task updates using Laravel Echo and Pusher.
*   Image uploads handled by a dedicated `ImageUploadService` class.

## Code Standards
*   We follow [Laravel Pint](https://github.com) for code style.
*   All new features require accompanying unit tests located in the `tests/Feature` directory.

## UI design standards
*   Implement dark mode theme
*   Keep UI design of component and elements such as tables, buttons, and links consitent across all pages
*   Make all the cursor of clickable anchor links and buttons a pointer on hover   

## Testing & Development
Everytime there are changes in the CSS, HTML, or UI in general, run the "npm build" command 
To run the full test suite: `composer test`.
Static analysis check: `composer stan`.

**CRITICAL WARNING FOR AI AGENTS:** 
When running tests manually (e.g., using `php artisan test` or `phpunit`), you **MUST ALWAYS** run `php artisan config:clear` beforehand. Failure to clear the configuration cache may cause Laravel to ignore the `phpunit.xml` SQLite in-memory database configuration and fall back to the `.env` MySQL connection. Since tests utilize the `RefreshDatabase` trait, this will accidentally wipe the user's local development database. Always prefer using `composer test`, which is configured to clear the cache automatically.
