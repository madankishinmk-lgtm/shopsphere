# Web Application Development: Final E-Commerce Project Report

**Group Members:**
- [Group Member 1 Name] - Implemented: Database Schema, Authentication, Cart Logic
- [Group Member 2 Name] - Implemented: Product Listing, Pagination, Filters
- [Group Member 3 Name] - Implemented: Admin Dashboard, Order Management
*(Please update with your actual group members and roles)*

---

## 1. Entity Relationship Diagram (ERD)

```mermaid
erDiagram
    users {
        INT id PK
        VARCHAR name
        VARCHAR email
        VARCHAR password_hash
        ENUM role "admin, customer"
        TIMESTAMP created_at
    }

    categories {
        INT id PK
        VARCHAR name
        VARCHAR slug
        TEXT description
    }

    products {
        INT id PK
        INT category_id FK
        VARCHAR name
        VARCHAR slug
        TEXT description
        DECIMAL price
        INT stock
        VARCHAR image_url
        TIMESTAMP created_at
    }

    carts {
        INT id PK
        INT user_id FK
        TIMESTAMP created_at
    }

    cart_items {
        INT id PK
        INT cart_id FK
        INT product_id FK
        INT quantity
    }

    orders {
        INT id PK
        INT user_id FK
        DECIMAL total_amount
        ENUM status "pending,paid,shipped,etc"
        TIMESTAMP created_at
    }

    order_items {
        INT id PK
        INT order_id FK
        INT product_id FK
        INT quantity
        DECIMAL unit_price
    }

    %% Relationships
    users ||--o{ carts : "owns"
    users ||--o{ orders : "places"
    categories ||--o{ products : "contains"
    carts ||--o{ cart_items : "contains"
    products ||--o{ cart_items : "part of"
    orders ||--o{ order_items : "contains"
    products ||--o{ order_items : "part of"
```

---

## 2. Application Flow Diagram: Customer Purchase Journey

```mermaid
flowchart TD
    %% Define styles
    classDef startend fill:#f9f,stroke:#333,stroke-width:2px;
    classDef page fill:#bbf,stroke:#333,stroke-width:1px;
    classDef db fill:#ff9,stroke:#333,stroke-width:1px;

    Start((Start)):::startend --> Home[index.php / Home Page]:::page
    Home -->|Click Shop| Shop[shop.php / Product Catalogue]:::page
    Shop -->|Search / Filter| Shop
    Shop -->|Click Product| ProductDetail[product.php / Single View]:::page
    
    ProductDetail -->|Click 'Add to Cart'| CartAction{cart_action.php}
    
    CartAction -->|Check Login| AuthCheck{Is Logged In?}
    AuthCheck -->|No| Login[login.php]:::page
    Login -->|Success| ProductDetail
    
    AuthCheck -->|Yes| CartDBUpdate[(Update cart_items)]:::db
    CartDBUpdate -->|AJAX Response| Cart[cart.php / View Cart]:::page
    
    Cart -->|Update Qty| CartDBUpdate
    Cart -->|Proceed to Checkout| Checkout[checkout.php]:::page
    
    Checkout -->|Place Order| DB_Order[(Create Order & order_items)]:::db
    DB_Order -->|Clear Cart| NextStep
    NextStep -->|Redirect| OrderHistory[orders.php / Order History]:::page
    OrderHistory --> End((End)):::startend
```

---

## 3. CRUD Operations Matrix

| Table | Operation | Responsible File | SQL Command Summary |
|-------|-----------|-----------------|---------------------|
| **`users`** | **C**reate | `register.php` | `INSERT INTO users (name, email, password_hash, role) VALUES (...)` |
| | **R**ead | `admin/users.php`<br>`login.php` | `SELECT id, name, email, role... FROM users...`<br>`SELECT * FROM users WHERE email = ?` |
| | **U**pdate | `profile.php` | `UPDATE users SET name = ?, email = ?... WHERE id = ?` |
| | **D**elete | `admin/users.php` | `DELETE FROM users WHERE id = ? AND role != 'admin'` |
| **`products`** | **C**reate | `admin/edit_product.php` | `INSERT INTO products (name, slug, category_id...) VALUES (...)` |
| | **R**ead | `shop.php`<br>`admin/products.php` | `SELECT p.*, c.name FROM products p JOIN categories... WHERE ... LIMIT...OFFSET...` |
| | **U**pdate | `admin/edit_product.php` | `UPDATE products SET name=?, slug=? ... WHERE id=?` |
| | **D**elete | `admin/products.php` | `DELETE FROM products WHERE id = ?` |
| **`categories`**| **C**reate | *Seed SQL* | N/A (Handled via DB creation) |
| | **R**ead | `shop.php`<br>`index.php` | `SELECT * FROM categories ORDER BY name ASC` |
| | **U**pdate | *N/A* | N/A |
| | **D**elete | *N/A* | N/A |
| **`carts`** &<br>**`cart_items`** | **C**reate | `register.php`<br>`cart_action.php` | `INSERT INTO carts (user_id)`<br>`INSERT INTO cart_items (cart_id, product_id, quantity)` |
| | **R**ead | `cart.php` | `SELECT ci.*, p.name, p.price... FROM cart_items ci JOIN...` |
| | **U**pdate | `cart_action.php` | `UPDATE cart_items SET quantity = ? WHERE id = ?` |
| | **D**elete | `cart_action.php`<br>`checkout.php` | `DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?` |
| **`orders`** &<br>**`order_items`**| **C**reate | `checkout.php` | `INSERT INTO orders ...`<br>`INSERT INTO order_items ...` |
| | **R**ead | `orders.php`<br>`admin/orders.php` | `SELECT * FROM orders WHERE user_id = ?`<br>`SELECT oi.*, p.name FROM order_items...` |
| | **U**pdate | `admin/orders.php`<br>`orders.php` | `UPDATE orders SET status = ? WHERE id = ?` |
| | **D**elete | *N/A* | (Soft "cancelled" status is used instead of hard deletion) |

---

## 4. Screenshots
> [!NOTE]
> Please launch your local development server (`php -S localhost:8000` from the project root) and insert your screenshots here.

- `index.php` (Home Hero & Featured)
- `shop.php` (With Filters and Pagination applied)
- `cart.php` (Showing item calculations)
- `checkout.php` (Form and Summary)
- `admin/index.php` (Dashboard Metrics)
