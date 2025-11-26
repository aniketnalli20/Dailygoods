# Dailygoods (PHP + MySQL)

Dailygoods is a subscription-based daily delivery web app for milk and add-ons. It digitizes the relationship between consumers and local vendors: customers subscribe once, adjust quantity and schedule on the fly, pause/resume, and vendors get predictable orders and route planning. This repository provides a working MVP built on PHP (PDO) and MySQL, designed for XAMPP on Windows.

## Features

- Authentication: register, login, session-based user handling.
- Product Catalog: milk types (whole, skim, organic, A2, flavored) and add-ons (eggs, butter, cheese, yogurt, ghee). Packaging options (glass, tetra, pouch, eco).
- Subscriptions: create/update plans (`monthly`, `pay_per_delivery`), frequency (`daily`, `alternate`, `weekly`), and items/quantities per product.
- Pause/Resume: temporarily suspend deliveries until a configured date.
- Delivery Calendar: per-subscription monthly calendar with:
  - Day-by-day delivery determination based on frequency, pause, and overrides.
  - Mark holidays (no delivery) for specific dates.
  - Add guest extras per date (e.g., additional milk or add-ons).
- Vendor Manifest: daily manifest grouped by zone, merging base subscription items with same-day extras.
- Admin Analytics: subscription counts, activity, wallet totals, frequency distribution, popular products.
- Admin Product Management (CRUD): create/update products, toggle active state; manage packaging options.
- UI Font: Inter variable font (local); loader prefers `woff2` when available with fallback to `ttf`.

## Project Structure

- `index.php` – app router and landing page (`home`, `login`, `register`, `dashboard`, `admin`, `vendor`).
- `lib/DB.php` – MySQL connection via PDO.
- `lib/Auth.php` – session-based authentication helpers.
- `pages/*.php` – views for login, register, dashboard, admin, vendor.
- `actions/*.php` – POST endpoints (login/register/logout, subscriptions CRUD, calendar actions, product/packaging admin).
- `db/schema.sql` – database tables.
- `db/seed.sql` – initial data for products and packaging options.
- `install.php` – runs schema + seed on the configured database.
- `styles.css` – base styling and font loader.
- `assets/fonts/Inter/*` – local Inter font files extracted from zip.

## Prerequisites

- XAMPP for Windows with Apache and PHP.
- MySQL server accessible locally.
- PHP extensions enabled in `php.ini`:
  - `extension=mysqli` (optional)
  - `extension=pdo_mysql`
- Restart Apache after enabling extensions.

## Configuration

- Update settings in `config/config.php`:
  - `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`
  - `CDN_BASE_URL` (optional) for hosted assets; when empty, local assets are used
- Keep secrets out of version control and logs.

## Installation

1. Place the project under `c:\xampp\htdocs\Dailygoods`.
2. Inter font is included under `assets/fonts/Inter`. The app uses local `ttf` variable fonts; you can add `woff2` if desired.
3. Initialize the database:
   - Visit `http://localhost/Dailygoods/install.php` (or dev server URL) in the browser.
   - Ensures the `dailygoods` database exists, applies schema, seeds default products/packaging.
   - If a legacy DB named `milkride` exists, data is migrated into `dailygoods` (best effort).

## Running

- Preview address: `http://localhost/Dailygoods/`
- Default routes:
  - `index.php?page=login` – login form
  - `index.php?page=register` – register form
  - `index.php?page=dashboard` – customer dashboard
  - `index.php?page=admin` – admin analytics & product/packaging management
  - `index.php?page=vendor` – vendor manifest

## Using The App

- Register, login, and create a subscription with address and items.
- Update plan/frequency and items anytime.
- Pause/resume deliveries via dashboard.
- Calendar:
  - Navigate months and manage per-day overrides.
  - Add extras per date; remove as needed.
- Admin:
  - Create/edit products and toggle active state.
  - Add/delete packaging options.
- Vendor:
  - Review daily manifest with merged extras.

## Multiple Subscriptions Per User

- The dashboard lists all subscriptions with a selector.
- Calendar and actions operate on the selected subscription.

## Font Loader (Inter)

- `styles.css` defines `@font-face` entries using local variable `ttf` files:
  - `assets/fonts/Inter/Inter-VariableFont_opsz,wght.ttf`
  - `assets/fonts/Inter/Inter-Italic-VariableFont_opsz,wght.ttf`
- You may add `woff2` files and update `styles.css` if you prefer smaller assets.

## Notes

- Payments, invoices, delivery route optimization, and notification integrations are modular and can be added.
- For production, consider CSRF protection, input validation, and granular role-based access checks.
- Ensure appropriate indexes if your data grows (e.g., on `delivery_dates.delivery_date`, `subscription_items.subscription_id`).

## Extending

- Switch font loader fully to `woff2` by adding the variable `woff2` files and updating `styles.css` paths if needed.
- Dedicated per-subscription calendar is already implemented via the dashboard `sid` selector and URL parameter; expand further to show multiple calendars at once if desired.

## Troubleshooting

- If `install.php` shows `could not find driver`, ensure `pdo_mysql` is enabled and Apache restarted.
- If fonts don’t render, verify the files under `assets/fonts/Inter`.

## Recent Changes

- Rebranded app to Dailygoods (titles, headings, and auth pages).
- Database switched to MySQL; default DB name set to `dailygoods`.
- `install.php` now ensures DB creation and attempts migration from legacy `milkride`.
- Login/Register pages redesigned with a two-column layout and hero image.
- Added optional `CDN_BASE_URL` configuration for hosted assets; local fallback if unset.
- Icons8 LaGuardia favicon and brand icon integrated.
- Default password policy: new registrations, imports, and enrichments set password to `demo1234`.