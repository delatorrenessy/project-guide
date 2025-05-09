# PHP CRUD Admin Order Management Guide

In this guide, you will learn how to create a simple **PHP application** where an **Admin** can manage orders. The Admin will be able to:

1. View all orders.
2. Filter orders by **user**, **date**, or **status**.
3. See order totals and the details of books in each order.

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
// Database connection settings
include_once("connection.php");

// Admin authentication (simple for demonstration)
session_start();
if (!isset($_SESSION['admin_id'])) {
    die("You must be logged in as an admin to view this page.");
}

// Filtering orders based on user, date, and status
$filter_user = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';

// Query to get orders based on filters
$sql_orders = "SELECT * FROM Orders WHERE 1=1";

if ($filter_user) {
    $sql_orders .= " AND user_id = '$filter_user'";
}

if ($filter_status) {
    $sql_orders .= " AND status = '$filter_status'";
}

if ($filter_date) {
    $sql_orders .= " AND DATE(created_at) = '$filter_date'";
}

$orders_result = $conn->query($sql_orders);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Order Management</title>
</head>
<body>

<h1>Admin Order Management</h1>

<!-- Order Filter Form -->
<h2>Filter Orders</h2>
<form method="GET">
    <label for="user_id">User ID:</label>
    <input type="text" id="user_id" name="user_id" value="<?php echo $filter_user; ?>"><br>

    <label for="status">Status:</label>
    <select id="status" name="status">
        <option value="">Select Status</option>
        <option value="pending" <?php if ($filter_status == 'pending') echo 'selected'; ?>>Pending</option>
        <option value="completed" <?php if ($filter_status == 'completed') echo 'selected'; ?>>Completed</option>
        <option value="cancelled" <?php if ($filter_status == 'cancelled') echo 'selected'; ?>>Cancelled</option>
    </select><br>

    <label for="date">Order Date:</label>
    <input type="date" id="date" name="date" value="<?php echo $filter_date; ?>"><br>

    <button type="submit">Apply Filter</button>
</form>

<h2>Orders List</h2>

<!-- Display Orders -->
<table border="1">
    <tr>
        <th>Order ID</th>
        <th>User ID</th>
        <th>Total</th>
        <th>Status</th>
        <th>Date</th>
        <th>Actions</th>
    </tr>
    <?php
    if ($orders_result->num_rows > 0) {
        while ($order = $orders_result->fetch_assoc()) {
            echo "<tr><td>" . $order['id'] . "</td><td>" . $order['user_id'] . "</td><td>" . $order['total'] . "</td><td>" . $order['status'] . "</td><td>" . $order['created_at'] . "</td>";
            echo "<td><a href='?view_order=" . $order['id'] . "'>View Details</a></td></tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No orders found</td></tr>";
    }
    ?>
</table>

<?php
// If an order is selected to view details
if (isset($_GET['view_order'])) {
    $order_id = $_GET['view_order'];

    // Fetch order details
    $sql_order_details = "SELECT od.*, b.title, b.author FROM OrderDetails od
                          JOIN Books b ON od.book_id = b.id WHERE od.order_id = $order_id";
    $order_details_result = $conn->query($sql_order_details);

    echo "<h2>Order Details (Order ID: $order_id)</h2>";
    echo "<table border='1'>
            <tr>
                <th>Book Title</th>
                <th>Author</th>
                <th>Quantity</th>
                <th>Price</th>
            </tr>";

    if ($order_details_result->num_rows > 0) {
        while ($detail = $order_details_result->fetch_assoc()) {
            echo "<tr>
                    <td>" . $detail['title'] . "</td>
                    <td>" . $detail['author'] . "</td>
                    <td>" . $detail['quantity'] . "</td>
                    <td>" . $detail['price'] . "</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<tr><td colspan='4'>No book details found</td></tr></table>";
    }
}
?>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
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
