---

## 🔐 Add Role-Based User Management

This update adds the ability to assign roles to users and restrict access to certain features based on those roles.

---

### 🛠️ 1. Modify `Users` Table to Add Role

Update your `schema.sql`:

```sql
ALTER TABLE Users ADD role ENUM('user', 'admin') DEFAULT 'user';
```

---

### 🧠 2. Role Helpers (create `functions.php`)

```php
<?php
function isAdmin() {
    return isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin';
}

function getUserById($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM Users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
?>
```

---

### 📥 3. Load Role After Login (`login.php`)

Update this line:

```php
$result = $conn->query("SELECT user_id, name, password FROM Users WHERE email = '$email'");
```

To this:

```php
$result = $conn->query("SELECT user_id, name, password, role FROM Users WHERE email = '$email'");
```

And add after password is verified:

```php
$_SESSION["role"] = $row['role'];
```

---

### 🧑‍💼 4. Create `manage_users.php` (Admin-Only)

```php
<?php
include_once("connection.php");
include_once("functions.php");

if (!isAdmin()) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE Users SET role = ? WHERE user_id = ?");
    $stmt->bind_param("si", $new_role, $user_id);
    $stmt->execute();
}

$users = $conn->query("SELECT user_id, name, email, role FROM Users");
?>

<!DOCTYPE html>
<html>
<head><title>Manage Users</title></head>
<body>
    <h2>User Role Management</h2>
    <table border="1" cellpadding="8">
        <tr><th>Name</th><th>Email</th><th>Role</th><th>Change Role</th></tr>
        <?php while ($user = $users->fetch_assoc()): ?>
            <tr>
                <form method="POST">
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                        <select name="role">
                            <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <button type="submit">Update</button>
                    </td>
                </form>
            </tr>
        <?php endwhile; ?>
    </table>
    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
```

---

### 🖥️ 5. Update `dashboard.php` to Show Admin Link

Add this to your `dashboard.php` navigation:

```php
<?php if (isAdmin()): ?>
    | <a href="manage_users.php">Manage Users</a>
<?php endif; ?>
```

---


## 💡 Done!

You now have:
- User login, registration, and dashboard
- Role-based access (`admin` / `user`)
- Admin-only **User Role Management Page**

---
