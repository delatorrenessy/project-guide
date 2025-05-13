# PHP CRUD Admin Order Management Guide

In this guide, you will learn how to create a simple **PHP application** where an **Admin** can manage orders. The Admin will be able to:

1. View all orders.
2. Filter orders by **user**, **date**, or **status**.
3. See order totals and the details of books in each order.
4. Update order status

---

## Prerequisites

Before starting, ensure that you have:

- **PHP** installed on your computer (XAMPP is recommended).
- **phpMyAdmin** or any MySQL tool to create and manage your database.
- A **text editor** (e.g., Notepad++, Visual Studio Code).

---

## Step 1: Set Up the Database

We assume you already have a database that contains the **Orders** and **OrderDetails** tables, which store information about orders and the books included in them.

## Step 2: Create the PHP File for Admin Order Management

### 2.1 Create the PHP File `admin_order_management.php`

This PHP file will allow an **Admin** to view all orders, filter them by user, date, or status, and see the details of the books in each order.

Create a new file called `admin_order_management.php` in the `htdocs` folder of your XAMPP installation or your web server directory.

### 2.2 Add the PHP Code

Here is the PHP code that will handle displaying the orders and filtering by different criteria:

```php
<?php

include_once("connection.php");

// Admin check
if (!isAdmin()) {
    header("Location: dashboard.php");
    exit();
}

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $sql_update = "UPDATE Orders SET status = '$new_status' WHERE id = $order_id";
    $conn->query($sql_update);
    echo "<p style='color:green;'>Order #$order_id updated to '$new_status'.</p>";
}

// Filters
$filter_user = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';

// Query orders with filters + join Users for customer names
$sql_orders = "SELECT Orders.*, Users.name AS customer_name 
               FROM Orders 
               JOIN Users ON Orders.user_id = Users.user_id 
               WHERE 1=1";

if ($filter_user) {
    $sql_orders .= " AND Orders.user_id = '$filter_user'";
}
if ($filter_status) {
    $sql_orders .= " AND Orders.status = '$filter_status'";
}
if ($filter_date) {
    $sql_orders .= " AND DATE(Orders.created_at) = '$filter_date'";
}

$sql_orders .= " ORDER BY Orders.created_at DESC";
$orders_result = $conn->query($sql_orders);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Order Management</title>
</head>
<body>

<h1>🛒 Admin Order Management</h1>
<a href="dashboard.php">← Back to Dashboard</a><br><br>

<!-- 🔍 Filter Form -->
<h2>Filter Orders</h2>
<form method="GET">
    <label for="user_id">User ID:</label>
    <input type="text" id="user_id" name="user_id" value="<?php echo htmlspecialchars($filter_user); ?>"><br>

    <label for="status">Status:</label>
    <select id="status" name="status">
        <option value="">Select Status</option>
        <option value="pending" <?php if ($filter_status == 'pending') echo 'selected'; ?>>Pending</option>
        <option value="completed" <?php if ($filter_status == 'completed') echo 'selected'; ?>>Completed</option>
        <option value="cancelled" <?php if ($filter_status == 'cancelled') echo 'selected'; ?>>Cancelled</option>
    </select><br>

    <label for="date">Order Date:</label>
    <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($filter_date); ?>"><br>

    <button type="submit">Apply Filter</button>
</form>

<hr>

<!-- 📋 Orders List -->
<h2>Orders List</h2>
<?php
if ($orders_result->num_rows > 0) {
    while ($order = $orders_result->fetch_assoc()) {
        echo "<h3>Order #{$order['id']} - <em>{$order['status']}</em> - by <strong>{$order['customer_name']}</strong> ({$order['created_at']})</h3>";

        // ✅ Status update form
        echo "<form method='POST' style='margin-bottom:10px;'>
                <input type='hidden' name='order_id' value='{$order['id']}' />
                <label for='status'>Update Status:</label>
                <select name='status'>
                    <option value='pending' " . ($order['status'] == 'pending' ? 'selected' : '') . ">Pending</option>
                    <option value='completed' " . ($order['status'] == 'completed' ? 'selected' : '') . ">Completed</option>
                    <option value='cancelled' " . ($order['status'] == 'cancelled' ? 'selected' : '') . ">Cancelled</option>
                </select>
                <button type='submit' name='update_status'>Update</button>
              </form>";

        // 📦 Order item details
        $order_id = $order['id'];
        $sql_items = "SELECT OrderDetails.*, Books.title, Books.author 
                      FROM OrderDetails 
                      JOIN Books ON OrderDetails.book_id = Books.id 
                      WHERE OrderDetails.order_id = $order_id";
        $result_items = $conn->query($sql_items);

        echo "<table border='1' cellpadding='5'>
                <tr><th>Book Title</th><th>Author</th><th>Quantity</th><th>Price</th><th>Total</th></tr>";
        $order_total = 0;
        if ($result_items->num_rows > 0) {
            while ($item = $result_items->fetch_assoc()) {
                $item_total = $item['quantity'] * $item['price'];
                $order_total += $item_total;
                echo "<tr>
                        <td>{$item['title']}</td>
                        <td>{$item['author']}</td>
                        <td>{$item['quantity']}</td>
                        <td>₱{$item['price']}</td>
                        <td>₱{$item_total}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No book items found</td></tr>";
        }
        echo "<tr><td colspan='4'><strong>Total</strong></td><td><strong>₱$order_total</strong></td></tr>";
        echo "</table><hr>";
    }
} else {
    echo "<p>No orders found.</p>";
}

$conn->close();
?>

</body>
</html>
```

---

## Step 3: Explanation of the Code

### 3.1 **Admin Authentication**

We check if the admin is logged in using `$_SESSION['admin_id']`. If not, we show an error message and stop the script.

### 3.2 **Filter Orders**

Admins can filter orders by:

- **User ID**: Filters orders based on the user who made the order.
- **Status**: Filters by order status (`pending`, `completed`, or `cancelled`).
- **Order Date**: Filters orders based on the order creation date.

### 3.3 **View Orders and Order Details**

Once the orders are filtered, we display them in a table. Each order has a link to view the order details, which shows the books included in that order, their quantities, and their prices.

### 3.4 **Order Details Table**

When an Admin clicks the “View Details” link, the application queries the **OrderDetails** table and displays the list of books in the order with their quantity and price.

---

## Step 4: Testing

1. Save the `admin_order_management.php` file to the `htdocs` folder.
2. Start **XAMPP** (Apache and MySQL).
3. Open **phpMyAdmin** and ensure you have some test orders in the database.
4. Open your browser and go to `http://localhost/admin_order_management.php`.
5. Test filtering orders by **user**, **status**, and **date**.
6. Test viewing order details by clicking the **View Details** link for each order.

---

### Conclusion

Congratulations! You’ve successfully created an **Admin Order Management System** where you can filter and view orders and their details.
