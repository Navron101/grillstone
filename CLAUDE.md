# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Grillstone is a restaurant POS (Point of Sale) and inventory management system built with:
- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Vue 3 + Inertia.js + TypeScript
- **UI**: Tailwind CSS + Reka UI components
- **Database**: SQLite (default, configurable to MySQL/PostgreSQL)

The system handles order taking, payment processing, inventory tracking with FIFO costing, recipe management with ingredient consumption, and goods receipt processing.

## Development Commands

### Start Development Server
```bash
composer dev
```
This starts 4 concurrent processes: Laravel server, queue worker, logs (pail), and Vite dev server.

### Alternative: Start Components Individually
```bash
php artisan serve          # Laravel server on port 8000
npm run dev                # Vite dev server
php artisan queue:listen   # Queue worker
php artisan pail           # Real-time logs
```

### Frontend Commands
```bash
npm run build              # Production build (client-side only)
npm run build:ssr          # Production build with SSR
npm run format             # Format code with Prettier
npm run format:check       # Check formatting without changing files
npm run lint               # Run ESLint with auto-fix
```

### Backend Commands
```bash
composer test              # Run PHPUnit tests
php artisan test           # Alternative test runner
./vendor/bin/pint          # Format PHP code (Laravel Pint)
php artisan migrate:fresh --seed  # Reset database with seeders
```

### SSR Development
```bash
composer dev:ssr
```
Builds SSR bundle and starts server with Inertia SSR process.

## Architecture

### Backend Layer Structure

**Domain Models** (app/Models/)
- `Product`: Menu items (dishes) and raw ingredients (type: 'dish' | 'ingredient')
- `Category`: Product categorization
- `Order` / `OrderItem`: Sales transactions
- `Payment`: Payment records linked to orders
- `InventoryLot`: FIFO lot tracking with unit costs and quantities
- `StockMovement`: Audit trail for all inventory changes
- `GoodsReceipt` / `GoodsReceiptLine`: Stock receiving from vendors

**Recipe System**
- `recipe_variants`: A dish can have multiple variants (sizes/preparations)
- `recipe_components`: Ingredient quantities per variant (e.g., 0.5 lb beef per burger)
- Products table has `type` field distinguishing dishes from ingredients

**Service Layer** (app/Services/)
- `InventoryService`: Stock movement creation and FIFO lot management
- `PosService`: POS logic (if exists, check for implementation)
- `Sales/RecipeConsumption`: Consumes ingredients FIFO when order is finalized
- `Sales/FinalizeSale`: Order completion and payment processing
- `Inventory/FifoInventoryEngine`: FIFO consumption engine (implements `InventoryEngine` contract)

**API Controllers** (app/Http/Controllers/Api/)
- `ProductController`: Menu items for POS
- `OrderController`: Create orders, hold orders, send to kitchen
- `OrderPaymentController`: Process payments
- `StockController`: Stock summaries and low-stock alerts
- `GoodsReceiptController`: Receive goods from vendors
- `InventoryController`: CRUD for ingredients, dishes, variants, recipe components

### Frontend Structure

**Pages** (resources/js/pages/)
- `POS/Index.vue`: Main POS interface
- `Inventory/Index.vue`: Ingredient management
- `Inventory/Dishes.vue`: Dish and recipe management
- `Inventory/GRN.vue`: Goods Receipt Note (receiving stock)
- `auth/*`: Authentication pages
- `settings/*`: User profile, password, appearance

**Layouts** (resources/js/layouts/)
- `AppLayout.vue`: Main authenticated layout
- `AuthLayout.vue`: Authentication pages layout
- `app/AppSidebarLayout.vue`: Sidebar navigation layout
- `app/AppHeaderLayout.vue`: Header-based navigation layout

**Components** (resources/js/components/)
- `ui/`: Reusable UI components (sidebar, tooltip, sheet, etc.)
- App-specific components (NavMain, NavUser, AppShell, etc.)

**Page Resolution** (resources/js/app.ts)
- Pages are loaded eagerly from both `./Pages/` and `./pages/` directories (case-insensitive)
- Missing pages show error component instead of crashing

### Routes

**Web Routes** (routes/web.php)
- `/`: Redirects guests to login, authenticated users to POS
- `/pos`: Main POS interface (Inertia)
- `/inventory`: Inventory management UIs
- Auth routes in `routes/auth.php`

**API Routes** (routes/api.php)
- All API routes are unauthenticated by default (add auth middleware as needed)
- POS endpoints: `/api/products`, `/api/orders`, `/api/orders/{order}/pay`
- Inventory endpoints: `/api/stock/*`, `/api/grn`, `/api/inventory/*`

### Database

**Default**: SQLite (`database/database.sqlite`)
**Migrations**: Located in `database/migrations/`
**Seeders**: `CategorySeeder`, `ProductSeeder`, `AdminUserSeeder`

The schema includes extensive inventory management with FIFO lot tracking, recipe management, and multi-location support (though currently single-location).

### Key Business Logic Flows

**Order Processing**:
1. Create order via `POST /api/orders` (OrderController)
2. Payment processed via `POST /api/orders/{order}/pay`
3. On payment, `RecipeConsumption::apply()` consumes ingredients FIFO from inventory_lots
4. Stock movements created for each ingredient consumed

**Inventory Receiving**:
1. Create goods receipt via `POST /api/grn`
2. Creates `GoodsReceipt` header and `GoodsReceiptLine` items
3. Creates `InventoryLot` records with unit costs for FIFO tracking
4. Creates positive stock movements

**FIFO Costing**:
- `RecipeConsumption` consumes oldest lots first (ordered by received_at)
- Calculates COGS (Cost of Goods Sold) based on lot unit costs
- Updates lot quantities and creates stock movement records

## Testing

- PHPUnit tests in `tests/Feature` and `tests/Unit`
- Run with `composer test` or `php artisan test`
- Use in-memory SQLite for testing (configured in phpunit.xml)

## Utilities

**Ziggy**: Laravel route names available in Vue via `route()` helper
**Inertia.js**: Renders Vue pages with Laravel backend (no API calls for page loads)
**VueUse**: Composables library for common Vue patterns

## Important Notes

- Inertia pages use both `Pages/` and `pages/` directories (case-insensitive resolution)
- Stock movements track all inventory changes with reason codes
- Products can be either 'dish' or 'ingredient' type
- Recipe variants allow different sizes/preparations per dish
- FIFO inventory engine is contract-based for potential alternative implementations
- Currently single-location, but schema supports multi-location
