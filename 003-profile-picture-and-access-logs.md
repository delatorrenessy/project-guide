---

## 🖼️ 1. Add **Profile Pictures**

### 📌 a. Update Database

```sql
ALTER TABLE Users ADD profile_picture VARCHAR(255) DEFAULT 'default.png';
```

### 📁 b. Create `uploads/` Directory

- Make sure it’s **writable** (`chmod 755` or `777` on Linux if needed)

### 📝 c. Update Registration / Profile Page

Create `update_profile.php`:

```php
<?php
include_once("connection.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $target_dir = "uploads/";
    $file = $_FILES["profile_picture"];
    $filename = basename($file["name"]);
    $target_file = $target_dir . $filename;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("UPDATE Users SET profile_picture = ? WHERE user_id = ?");
        $stmt->bind_param("si", $filename, $user_id);
        $stmt->execute();
        echo "<p>Profile picture updated!</p>";
    } else {
        echo "<p>Upload failed.</p>";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <label>Upload Profile Picture:</label><br>
    <input type="file" name="profile_picture"><br><br>
    <button type="submit">Upload</button>
</form>
```

---

### ✅ d. Show Picture in Dashboard

In `dashboard.php`:

```php
$result = $conn->query("SELECT name, profile_picture FROM Users WHERE user_id = $user_id");
$user = $result->fetch_assoc();
echo "<img src='uploads/" . $user['profile_picture'] . "' width='100' height='100'><br>";
```

---

## 🕵️‍♂️ 2. Add **Access Logs**

Track user login or actions like role changes.

### 📌 a. Create `AccessLogs` Table

```sql
CREATE TABLE AccessLogs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(255),
    log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);
```

### 🧠 b. Log Function

In `functions.php`:

```php
function logAction($conn, $user_id, $action) {
    $stmt = $conn->prepare("INSERT INTO AccessLogs (user_id, action) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $action);
    $stmt->execute();
}
```

### 📝 c. Log Login & Role Changes

- In `login.php` after successful login:

```php
logAction($conn, $_SESSION['user_id'], "Logged in");
```

- In `manage_users.php` after role update:

```php
logAction($conn, $_SESSION['user_id'], "Changed role of user ID $user_id to $new_role");
```

### 📋 d. View Logs (optional)

Create `view_logs.php`:

```php
<?php
include_once("connection.php");
include_once("functions.php");

if (!isAdmin()) {
    header("Location: dashboard.php");
    exit();
}

$logs = $conn->query("
    SELECT l.log_time, u.name, l.action 
    FROM AccessLogs l 
    JOIN Users u ON l.user_id = u.user_id 
    ORDER BY l.log_time DESC
");
?>

<h2>Access Logs</h2>
<table border="1">
    <tr><th>Time</th><th>User</th><th>Action</th></tr>
    <?php while ($log = $logs->fetch_assoc()): ?>
        <tr>
            <td><?= $log['log_time'] ?></td>
            <td><?= htmlspecialchars($log['name']) ?></td>
            <td><?= htmlspecialchars($log['action']) ?></td>
        </tr>
    <?php endwhile; ?>
</table>
```

---

## 🧩 Summary of Expansion

| Feature            | What's New                                                  |
|--------------------|-------------------------------------------------------------|
| Profile Pictures   | Upload avatar, store path in DB, display in dashboard       |
| Access Logs        | Track logins/actions, viewable by admins                    |

---
