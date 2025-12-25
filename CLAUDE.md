# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 10 PHP application with Blade templating for the frontend. It appears to be a business management system with features for:
- Worker/employee management
- Shop management
- Financial tracking (expenses, purchases, accountings)
- Task and schedule management
- Vehicle management
- Vacation tracking
- Correspondence (Moraslat) management

## Tech Stack

- **Backend**: Laravel 10, PHP 8.1+
- **Database**: Oracle (via yajra/laravel-oci8) or MySQL
- **Frontend**: Blade templates with Tailwind CSS
- **Build Tools**: Vite with React/TypeScript support (though primarily uses Blade)
- **Testing**: Pest PHP
- **PDF Generation**: DOMPDF and TCPDF

## Common Commands

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Run development server with Vite
npm run dev

# Build frontend assets
npm run build

# Run all tests
./vendor/bin/pest

# Run a specific test file
./vendor/bin/pest tests/Feature/ExampleTest.php

# Clear Laravel caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Run database migrations
php artisan migrate

# Generate IDE helper files
php artisan ide-helper:generate
```

## Architecture

### Route Structure
- `routes/web.php` - Main web routes, authentication, vehicles, tasks, services
- `routes/dashboard.php` - Dashboard module routes (workers, shops, financials, etc.)
- `routes/auth.php` - Authentication routes (Laravel Breeze)
- `routes/api.php` - API routes

### Controller Organization
- `app/Http/Controllers/` - Main controllers (Home, Profile, Vehicle, Task, Service)
- `app/Http/Controllers/Auth/` - Authentication controllers (Laravel Breeze)
- `app/Http/Controllers/Dashboard/` - Dashboard feature controllers:
  - `WorkersController` - Employee/worker management
  - `ShopController` - Shop management
  - `FinancialController` - Financial records
  - `ExpenseController` - Expense tracking
  - `PurchaseController` - Purchase management
  - `VacationController` - Vacation management
  - `MoraslatController` - Correspondence management
  - `ConstantController` - System constants (jobs, cities, etc.)
  - `ReportController` - Reporting features

### Helper Classes
Auto-loaded helpers in `composer.json`:
- `app/Helpers/helpers.php` - Date conversion utilities
- `app/Helpers/Perm.php` - Permission checking via `Perm::get_controll_access()` and `Perm::get_function_access()`

### View Structure
- `resources/views/layout/` - Multiple layout demos (demo1, demo2, demo7, docs)
- `resources/views/dashboard/` - Dashboard module views organized by feature
- `resources/views/components/` - Reusable Blade components

### Permission System
The app uses a role-based permission system. Check permissions using:
- `Perm::get_controll_access($parent_id)` - Controller-level access
- `Perm::get_function_access($function_id)` - Function-level access
- Users with `emp_job == 1` have admin access

## Database

The application supports both Oracle and MySQL. Database connection is configured via environment variables. Key tables include:
- `workers` - Employee records
- `shop` - Shop/store records
- `permission`, `role_per`, `per_function`, `per_controller` - Permission system
- `moraslat` - Correspondence records
- `expense`, `purchase`, `accountings` - Financial records
