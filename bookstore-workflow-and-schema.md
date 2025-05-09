````markdown
# 📚 Online Bookstore Management System

## 1. 🧑‍💻 User Flow

### a. Registration
- User visits registration page.
- Enters **name, email, password, phone number**.
- System:
  - Validates input.
  - Hashes password securely.
  - Stores user in the `Users` table with default role `customer`.

### b. Login
- User logs in with email and password.
- System:
  - Verifies credentials.
  - Starts session or returns JWT.
  - Grants access based on role.

### c. Browsing Books
- All users can:
  - View list of available books.
  - Filter/search by **title**, **author**, or **genre**.
  - See book details: title, author, price, stock, genre, description.

### d. Purchasing Books
- Customers can:
  - Add books to a cart or buy directly.
  - View and modify cart.
- On checkout:
  - System verifies stock.
  - Creates order with details.
  - Deducts stock.
  - Sends confirmation.

---

## 2. 🛠️ Admin Flow

### a. Book Management
- Admins can:
  - Add/edit/delete books.
  - Manage stock, price, and genre.
  - Upload/update cover images and descriptions.

### b. Order Management
- Admins can:
  - View all orders.
  - Filter by user, date, or status.
  - See order totals and book details.

---

## 3. 📏 Rules & Validations
- Book stock cannot go below 0.
- Passwords are hashed (e.g., bcrypt).
- Only logged-in users can purchase.
- Only admins can manage books or view all orders.
- Orders must include at least one item.
- Genres must be selected from predefined list.

---

## 4. 🗄️ Database Schema (SQL)
````


### Users

```sql
CREATE TABLE Users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Genres

```sql
CREATE TABLE Genres (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL
);
```

### Books

```sql
CREATE TABLE Books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255),
    author VARCHAR(255),
    genre_id INT,
    price DECIMAL(10, 2),
    stock INT DEFAULT 0,
    isbn VARCHAR(20),
    description TEXT,
    cover_image_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (genre_id) REFERENCES Genres(id)
);
```

### Orders

```sql
CREATE TABLE Orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    total DECIMAL(10, 2),
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(id)
);
```

### OrderDetails

```sql
CREATE TABLE OrderDetails (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    book_id INT,
    quantity INT,
    price DECIMAL(10, 2),
    FOREIGN KEY (order_id) REFERENCES Orders(id),
    FOREIGN KEY (book_id) REFERENCES Books(id)
);
```

### Cart

```sql
CREATE TABLE Cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    book_id INT,
    quantity INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(id),
    FOREIGN KEY (book_id) REFERENCES Books(id)
);
```
---
