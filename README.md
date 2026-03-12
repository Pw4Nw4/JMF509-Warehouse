# JMF 509 Warehouse

Online store and logistics platform for essential goods (solar products, electronics, survival supplies, household items) for customers in the U.S. and Haiti. Supports diaspora ordering—sending items to family in Haiti—with delivery or pickup.

## Purpose

- **Selling products** – Browse, add to cart, checkout, pay online.
- **Managing inventory** – Admin dashboard: product stock, warehouse quantities.
- **Logistics / shipping to Haiti** – Orders can be shipped to Haiti or picked up; destination and recipient (e.g. family) are captured.
- **Diaspora ordering** – Customers can order and ship to recipients in Haiti.

## Features

### Online store
- Browse by category: Solar products, Phones & electronics, Survival supplies, Essential household items.
- Add to cart, update quantities, remove items.
- Checkout with shipping destination (US / Haiti), delivery type (delivery / pickup), recipient name, and address.
- **Payment:** Haiti or "pay by transfer" → submit order, then you contact the customer (Zelle, transfer). US → set `STRIPE_ENABLED` in `config.php` when Stripe is set up to show card payment; otherwise US also uses "submit order, we'll contact you."

### Inventory (admin)
- View and update product stock.
- Summary: total products, low stock, out of stock.

### Shipments (admin)
- **Incoming:** Record received shipments; stock is updated automatically.
- **Outgoing:** View recent orders (destination, delivery type, recipient).

## Setup

### Option A: MySQL (PHP app)

1. **PHP** (7.4+) with MySQL PDO and `mbstring`.
2. **MySQL:** Create a database and run the schema:
   ```bash
   mysql -u user -p your_database < schema.sql
   ```
3. **Config:** Copy `.env.example` to `.env` and set your database credentials. Optional: set `MAIL_ENABLED=1` and `MAIL_FROM_EMAIL` / `MAIL_FROM_NAME` to send real email (via PHP `mail()`); otherwise only logged.
4. **Web server:** Point document root (or a vhost) to the `JMF509-Warehouse` folder so that `index.php` runs at the site root.

### Option B: PostgreSQL + PostgREST + pgAdmin

1. **Docker:** Generate secure credentials, then start the stack:
   ```bash
   ./setup-docker-env.sh   # first time - creates .env with random passwords
   docker compose up -d
   ```
   Or use `./run-postgrest.sh` which runs setup automatically if needed.
2. **PostgREST API** at `http://localhost:3000`:
   - `GET /products` – list products
   - `GET /products?id=eq.1` – single product
   - `POST /orders` – create order
   - etc. (auto-generated from schema)
3. **pgAdmin** at `http://localhost:5050` (login: `admin@jmf509.com` / `admin`):
   - Add server: host=`db`, user=`postgres`, password=`postgres`, database=`jmf509_warehouse`
4. **Manual setup (no Docker):** Install PostgreSQL, run `schema_postgres.sql`, then [install PostgREST](https://postgrest.org/en/stable/install.html) and use `postgrest.conf`.

## Default admin

- **Email:** `admin@jmf509.com`
- **Password:** `admin123`

Change the password immediately after first deploy. Admin access is controlled by `ADMIN_EMAILS` in `config.php` (comma-separated emails); you can add more admins there without editing multiple files.

## Reference

This project was built using the structure and patterns from the **Frederic_Assign5** e-commerce folder in the same workspace.
