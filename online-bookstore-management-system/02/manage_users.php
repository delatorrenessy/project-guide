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