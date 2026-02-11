# market-place

A comprehensive marketplace application built with Laravel and Vue.js.

## Database Schema

The application uses the following database structure with entity relationships:

![Database ER Diagram](docs/images/database-schema.png)

### Entities

- **roles**: User role definitions (id, name, created_at, updated_at)
- **users**: User accounts (id, role_id, name, email, password, created_at, updated_at) linked to roles
- **categories**: Product categories (id, name, parent_id, created_at, updated_at) with hierarchical structure
- **products**: Product listings (id, vendor_id, category_id, name, description, price, stock, created_at, updated_at) created by vendors
- **reviews**: Product reviews (id, product_id, customer_id, rating, comment, created_at, updated_at) written by customers
- **orders**: Customer orders (id, customer_id, total_price, status, created_at, updated_at)
- **order_items**: Individual items in orders (id, order_id, product_id, amount, method, status, created_at, updated_at)
- **payments**: Payment records (id, order_id, amount, method, status, created_at, updated_at) for orders
- **commissions**: Vendor commissions (id, order_id, vendor_id, amount, created_at, updated_at) from sales
