```markdown
# PHP Website Project with Login, Registration, and Dashboard

This project is a simple PHP website that includes:
- User login and registration
- Dashboard with user info and menu
- MySQL database connection
- Simple session handling

---

## 📁 Project Structure


my_website_project/
│
├── connection.php
├── login.php
├── register.php
├── dashboard.php
├── changepassword.php
├── library.php
├── logout.php
└── css/
    └── style.css (optional)

---

## 🗄️ Database Setup

### 1. Create a Database

```sql
CREATE DATABASE website_db;
```

### 2. Use the Database

```sql
USE website_db;
```

### 3. Create the `Users` Table

```sql
CREATE TABLE Users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 🔌 `connection.php`

```php
<?php
$servername = "localhost";
$username = "root";
$password = ""; // your MySQL password
$database = "website_db";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();
?>
```

---

## 🔐 `login.php`

```php
<?php
include_once("connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));

    if (!empty($email) && !empty($password)) {
        $result = $conn->query("SELECT user_id, name, password FROM Users WHERE email = '$email'");

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION["user_id"] = $row['user_id'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No user found with that email.";
        }
    } else {
        echo "<p>Please fill in all fields.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <label>Email:</label>
        <input type="email" name="email" required><br><br>
        <label>Password:</label>
        <input type="password" name="password" required><br><br>
        <button type="submit">Login</button>
    </form>
    <br>
    <a href="register.php">Register</a>
</body>
</html>
```

---

## 📝 `register.php`

```php
<?php
include_once("connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $phone = htmlspecialchars(trim($_POST['phone']));

    if (!empty($name) && !empty($email) && !empty($_POST['password'])) {
        $stmt = $conn->prepare("INSERT INTO Users (name, email, password, phone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $phone);
        
        if ($stmt->execute()) {
            echo "<script>alert('Registration successful! Please login.'); window.location.href='login.php';</script>";
        } else {
            echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:red;'>Please fill in all required fields.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h2>User Registration</h2>
    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" required><br><br>
        <label>Email:</label>
        <input type="email" name="email" required><br><br>
        <label>Password:</label>
        <input type="password" name="password" required><br><br>
        <label>Phone:</label>
        <input type="text" name="phone"><br><br>
        <button type="submit">Register</button>
    </form>
    <br>
    <a href="login.php">Back to Login</a>
</body>
</html>
```

---

## 🖥️ `dashboard.php`

```php
<?php
include_once("connection.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$result = $conn->query("SELECT name FROM Users WHERE user_id = $user_id");
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body style="text-align:center;">
    <h2>Welcome, <?php echo $user['name']; ?>!</h2>
    <nav>
        <a href="library.php">Library</a> |
        <a href="changepassword.php">Change Password</a> |
        <a href="logout.php">Logout</a>
    </nav>
    <div style="background-color:#e0e0e0; padding:10px;">
        <h1>Online Bookstore Management System</h1>
        <h3>Welcome Panel</h3>
        <p>This is a standard dashboard for our website project. You can manage your projects, view the library, and change your password.</p>
    </div>
</body>
</html>
```

---

## 🔑 `changepassword.php`

```php
<?php
include_once("connection.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_pass = password_hash(trim($_POST['new_password']), PASSWORD_DEFAULT);
    $user_id = $_SESSION["user_id"];

    $stmt = $conn->prepare("UPDATE Users SET password = ? WHERE user_id = ?");
    $stmt->bind_param("si", $new_pass, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Password changed successfully.');</script>";
    } else {
        echo "<p style='color:red;'>Failed to update password.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Change Password</title></head>
<body>
    <h2>Change Password</h2>
    <form method="POST">
        <label>New Password:</label>
        <input type="password" name="new_password" required><br><br>
        <button type="submit">Update Password</button>
    </form>
    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
```

---

## 📚 `library.php`

```php
<?php
include_once("connection.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head><title>Library</title></head>
<body>
    <h2>Library</h2>
    <p>This is a placeholder for your library content. You can fill it with books, articles, or other resources.</p>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
```

---

## 🔓 `logout.php`

```php
<?php
session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit();
?>
```

---

## ✅ Done!

You now have a basic PHP website with:
- Login and registration
- Dashboard with menu
- Change password and logout

You can run this on **XAMPP** or **any local PHP server**. 

---
