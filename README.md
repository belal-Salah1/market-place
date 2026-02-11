# market-place

A comprehensive marketplace application built with Laravel and Vue.js.

## Database Schema

The application uses the following database structure with entity relationships:

![Database ER Diagram](https://i.imgur.com/your-diagram-link.png)

### Entities

- **USER**: Stores user information (id, name, email, password) with relationships to roles, products, categories, reviews, and orders
- **ROLE**: Defines user roles (id, name) linked to users
- **CATEGORY**: Product categories (id, name) that users can create and products belong to
- **PRODUCT**: Product listings (id, name, desc, stock) created by users and belonging to categories
- **REVIEW**: Product reviews (id, comment, rating) written by users for products
- **ORDER**: User orders (id, total_price, status) containing order items and linked to payments
- **ORDER_ITEMS**: Individual items in orders (id, price, quantity)
- **PAYMENTS**: Payment records (id, amount, method, status) for orders
