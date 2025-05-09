# PHP CRUD Application with Cart and Orders

This guide will help you create a PHP application that allows users to:

- Browse a list of books and filter/search by title, author, or genre.
- View book details including title, author, price, stock, genre, and description.
- Add books to a cart, modify the cart, and checkout to place orders.
- Deduct stock when an order is placed.

---

## Prerequisites

Before you begin, ensure you have the following:

- **PHP** installed on your computer (XAMPP is a good option).
- **phpMyAdmin** or any MySQL tool to create and manage your database.
- A **text editor** (e.g., Notepad++, Visual Studio Code).

---

## Step 1: Set Up the Database

First, you’ll need to create a database and four tables. Use **phpMyAdmin** or MySQL to run these SQL queries.

### 1.1 Create the Tables

Now, create the tables for **Orders**, **OrderDetails**, and **Cart**.

```sql
CREATE TABLE Orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    total DECIMAL(10, 2),
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

CREATE TABLE OrderDetails (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    book_id INT,
    quantity INT,
    price DECIMAL(10, 2),
    FOREIGN KEY (order_id) REFERENCES Orders(id),
    FOREIGN KEY (book_id) REFERENCES Books(id)
);

CREATE TABLE Cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    book_id INT,
    quantity INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (book_id) REFERENCES Books(id)
);
```

---

## Step 2: Creating the PHP CRUD File

### 2.1 Create the PHP file `shop.php`

This PHP file will handle browsing books, adding books to the cart, and managing orders. You can create this file and place it inside the `htdocs` folder of your XAMPP installation.

### 2.2 Add the PHP Code

Below is the PHP code for handling browsing, cart actions, and placing orders. This includes features like displaying books, filtering by title/author/genre, adding books to a cart, and checking out.

```php
<?php
// Database connection settings
include_once("connection.php");

// Browsing books: Search and filter books by title, author, or genre
$search_title = isset($_GET['title']) ? $_GET['title'] : '';
$search_author = isset($_GET['author']) ? $_GET['author'] : '';
$search_genre = isset($_GET['genre']) ? $_GET['genre'] : '';

$sql_books = "SELECT * FROM Books WHERE title LIKE '%$search_title%' AND author LIKE '%$search_author%' AND genre_id LIKE '%$search_genre%'";
$books_result = $conn->query($sql_books);

// Add book to cart
if (isset($_POST['add_to_cart'])) {
    $user_id = 1;  // Assume a logged-in user with ID 1 for simplicity
    $book_id = $_POST['book_id'];
    $quantity = $_POST['quantity'];

    $sql_check_cart = "SELECT * FROM Cart WHERE user_id = $user_id AND book_id = $book_id";
    $cart_result = $conn->query($sql_check_cart);

    if ($cart_result->num_rows > 0) {
        $sql_update_cart = "UPDATE Cart SET quantity = quantity + $quantity WHERE user_id = $user_id AND book_id = $book_id";
        $conn->query($sql_update_cart);
    } else {
        $sql_add_to_cart = "INSERT INTO Cart (user_id, book_id, quantity) VALUES ($user_id, $book_id, $quantity)";
        $conn->query($sql_add_to_cart);
    }
}

// Checkout process: Create order and deduct stock
if (isset($_POST['checkout'])) {
    $user_id = 1;  // Assume a logged-in user with ID 1
    $total = $_POST['total'];

    // Create new order
    $sql_create_order = "INSERT INTO Orders (user_id, total) VALUES ($user_id, $total)";
    $conn->query($sql_create_order);
    $order_id = $conn->insert_id;  // Get the last inserted order ID

    // Add order details and update stock
    $sql_cart_items = "SELECT * FROM Cart WHERE user_id = $user_id";
    $cart_items = $conn->query($sql_cart_items);

    while ($item = $cart_items->fetch_assoc()) {
        $book_id = $item['book_id'];
        $quantity = $item['quantity'];
        $price = $item['price'];  // You can fetch the price from the Books table

        // Add to OrderDetails
        $sql_order_details = "INSERT INTO OrderDetails (order_id, book_id, quantity, price) VALUES ($order_id, $book_id, $quantity, $price)";
        $conn->query($sql_order_details);

        // Deduct stock
        $sql_update_stock = "UPDATE Books SET stock = stock - $quantity WHERE id = $book_id";
        $conn->query($sql_update_stock);
    }

    // Clear cart after order
    $sql_clear_cart = "DELETE FROM Cart WHERE user_id = $user_id";
    $conn->query($sql_clear_cart);

    echo "Order placed successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Bookstore</title>
</head>
<body>

<h1>Browse Books</h1>
<form method="GET">
    <input type="text" name="title" placeholder="Search by Title" value="<?php echo $search_title; ?>" />
    <input type="text" name="author" placeholder="Search by Author" value="<?php echo $search_author; ?>" />
    <input type="text" name="genre" placeholder="Search by Genre" value="<?php echo $search_genre; ?>" />
    <button type="submit">Search</button>
</form>

<h2>Books List</h2>
<table border="1">
    <tr>
        <th>Title</th>
        <th>Author</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Actions</th>
    </tr>
    <?php
    if ($books_result->num_rows > 0) {
        while ($row = $books_result->fetch_assoc()) {
            echo "<tr><td>" . $row['title'] . "</td><td>" . $row['author'] . "</td><td>" . $row['price'] . "</td><td>" . $row['stock'] . "</td>";
            echo "<td>
                    <form method='POST'>
                        <input type='hidden' name='book_id' value='" . $row['id'] . "' />
                        <input type='number' name='quantity' value='1' min='1' max='" . $row['stock'] . "' />
                        <button type='submit' name='add_to_cart'>Add to Cart</button>
                    </form>
                  </td></tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No books found</td></tr>";
    }
    ?>
</table>

<h2>Your Cart</h2>
<!-- Display cart contents here -->

<h2>Checkout</h2>
<!-- Display checkout form here -->
<form method="POST">
    <input type="hidden" name="total" value="100.00" />  <!-- Example total -->
    <button type="submit" name="checkout">Checkout</button>
</form>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
```

---

## Step 3: Explanation of the Code

1. **Browsing Books**: Users can search for books by title, author, or genre. The results are displayed in a table.
2. **Cart**: Users can add books to their cart with a specified quantity. If the book is already in the cart, the quantity is updated.
3. **Checkout**: When users checkout, an order is created in the **Orders** table, and the stock of books is updated. The cart is cleared after the purchase.

---

## Step 4: Testing

1. Save the `shop.php` file to the `htdocs` folder.
2. Start **XAMPP** (Apache and MySQL).
3. Navigate to `http://localhost/shop.php` in your browser.
4. Test browsing, adding to cart, and checkout functionalities.

---

### Conclusion

Congratulations! You’ve built a basic PHP application that allows users to browse books, filter them, add them to a cart, and place orders.
