# Guide for Creating a PHP CRUD Application for Genres table

## This guide will help you create a simple PHP CRUD (Create, Read, Update, Delete) application that works with a **Genres** table in a MySQL database.

### Prerequisites

Before you start, you will need:

1. **PHP** installed on your computer. (You can download XAMPP, which includes PHP, MySQL, and Apache together.)
2. **MySQL Database** where you can create the **Genres** table.
3. **A text editor** (like Notepad++ or Visual Studio Code) to write your PHP code.

---

### Step 1: Create the Database and Table

First, you need to create the database and the table for storing genres.

1. Open **phpMyAdmin** (this comes with XAMPP if you installed it).
2. Click on **Databases** and select your project database `db_lastname_project`.
3. Now, create a table inside this database named `Genres`. Run the following SQL query:

```sql
CREATE TABLE Genres (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL
);
```

This will create a table that has two fields:

- `id`: A unique number for each genre (it will auto-increment).
- `name`: The name of the genre, like "Fiction", "Fantasy", etc.

---

### Step 2: Create the PHP File

Now, let’s write the PHP code for the CRUD operations.

1. Open your text editor and create a new file.
2. Save it as `crud_genre.php`.

Here’s the PHP code to get started:

```php
<?php
// Database connection
include_once("connection.php");

// CREATE operation: Add a new genre
if (isset($_POST['create'])) {
    $name = $_POST['name'];
    $sql = "INSERT INTO Genres (name) VALUES ('$name')";
    if ($conn->query($sql) === TRUE) {
        echo "New genre added successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

// READ operation: Show all genres
$sql = "SELECT * FROM Genres";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genres CRUD Application</title>
</head>
<body>

<h1>Genres CRUD Application</h1>

<!-- Form to add a new genre -->
<h2>Add a Genre</h2>
<form method="POST">
    <label for="name">Genre Name:</label>
    <input type="text" id="name" name="name" required>
    <button type="submit" name="create">Add Genre</button>
</form>

<!-- Display all genres -->
<h2>All Genres</h2>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Actions</th>
    </tr>
    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row['id'] . "</td><td>" . $row['name'] . "</td><td>";
            echo "<a href='?edit=" . $row['id'] . "'>Edit</a> | ";
            echo "<a href='?delete=" . $row['id'] . "'>Delete</a>";
            echo "</td></tr>";
        }
    } else {
        echo "<tr><td colspan='3'>No genres found</td></tr>";
    }
    ?>
</table>

<?php
// DELETE operation: Delete a genre
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM Genres WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        echo "Genre deleted successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

// UPDATE operation: Edit a genre
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM Genres WHERE id=$id");
    $row = $result->fetch_assoc();

    if (isset($_POST['update'])) {
        $name = $_POST['name'];
        $sql = "UPDATE Genres SET name='$name' WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            echo "Genre updated successfully!";
        } else {
            echo "Error: " . $conn->error;
        }
    }
    ?>

    <h2>Edit Genre</h2>
    <form method="POST">
        <input type="text" name="name" value="<?php echo $row['name']; ?>" required>
        <button type="submit" name="update">Update Genre</button>
    </form>

    <?php
}

$conn->close();
?>

</body>
</html>
```

---

### Step 3: Explanation of Code

1. **Database Connection**:
   We connect to the MySQL database using the `mysqli` function. Make sure to replace `localhost`, `root`, and the database name with the correct details for your setup.

2. **CREATE Operation**:
   If you want to add a genre, fill out the form and click the "Add Genre" button. It will insert the genre into the database.

3. **READ Operation**:
   This displays all genres from the `Genres` table in a simple table format.

4. **DELETE Operation**:
   If you click the "Delete" link next to a genre, it will remove that genre from the database.

5. **UPDATE Operation**:
   If you click "Edit" next to a genre, it will show a form where you can change the genre name and save the update.

---

### Step 4: Testing the Application

1. Put the `crud_genre.php` file in the `htdocs` folder if you're using XAMPP. The `htdocs` folder is usually found in `C:\xampp\htdocs` on your computer.
2. Start **Apache** and **MySQL** from the XAMPP control panel.
3. Open your web browser and go to `http://localhost/crud_genre.php`.
4. You should see the form to add a genre and a table showing all the genres.
5. Test the CRUD operations: Add, Edit, and Delete genres.

---
