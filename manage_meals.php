<?php
// 数据库连接
include_once('db_connection.php');

// 处理添加菜品
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_meal'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];

    // 图片路径
    $imagePath = "../assets/images/" . basename($_FILES['image']['name']);
    $dbImagePath = "assets/images/" . basename($_FILES['image']['name']); // 存入数据库的路径

    // 确保文件夹路径存在
    if (!is_dir("../assets/images/")) {
        mkdir("../assets/images/", 0777, true);
    }

    if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
        $sql = "INSERT INTO menu_items (name, category, price, image_path) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssds", $name, $category, $price, $dbImagePath);
        $stmt->execute();
    } else {
        echo "Failed to upload image. Check file permissions or path.";
    }
}

// 处理编辑菜品
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_meal'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];

    // 检查新名字是否已经存在，且不为 "invalid"
    $checkSql = "SELECT COUNT(*) as count FROM menu_items WHERE name = ? AND id != ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("si", $name, $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $row = $checkResult->fetch_assoc();

    if ($row['count'] > 0 && $name !== 'invalid') {
        echo "<span style='color: red; font-weight: bold;'>Error: Name already exists. Please choose a different name.</span>";
    } else {
        // 更新图片逻辑
        if (!empty($_FILES['image']['name'])) {
            $imagePath = "../assets/images/" . basename($_FILES['image']['name']);
            $dbImagePath = "assets/images/" . basename($_FILES['image']['name']);

            if (!is_dir("../assets/images/")) {
                mkdir("../assets/images/", 0777, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                $sql = "UPDATE menu_items SET name = ?, category = ?, price = ?, image_path = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdsi", $name, $category, $price, $dbImagePath, $id);
                $stmt->execute();
            }
        } else {
            $sql = "UPDATE menu_items SET name = ?, category = ?, price = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdi", $name, $category, $price, $id);
            $stmt->execute();
        }

        echo "<span style='color: blue; font-weight: bold;'>Meal updated successfully.</span>";
    }
}

// 处理删除菜品
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    echo "Delete ID received: " . htmlspecialchars($id) . "<br>";

    $sql = "DELETE FROM menu_items WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "Meal deleted successfully.";
        } else {
            echo "Error: Meal not found or could not be deleted. Affected rows: " . $stmt->affected_rows;
        }
    } else {
        echo "Error in delete query: " . $conn->error;
    }
}

// 获取菜品列表
$sql = "SELECT * FROM menu_items";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meals Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        table th {
            background-color: #f4f4f4;
        }

        img {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <h1>Meals Management</h1>

    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Meal Name" required>
       <!-- <input type="text" name="category" placeholder="Category" required> -->
        <select name="category" required>
    <option value="drink" >Drink</option>
    <option value="meal"  >Meal</option>
</select>
        <input type="number" step="0.01" name="price" placeholder="Price" required>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit" name="add_meal">Add Meal</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><img src="../<?php echo htmlspecialchars($row['image_path']); ?>" alt="Meal Image"></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo htmlspecialchars($row['price']); ?></td>
                    <td>
                        <form method="POST" enctype="multipart/form-data" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                            <select name="category" required>
    <option value="drink" <?php if ($row['category'] === 'drink') echo 'selected'; ?>>Drink</option>
    <option value="meal"  <?php if ($row['category'] === 'meal')  echo 'selected'; ?>>Meal</option>
</select>
                            <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($row['price']); ?>" required>
                            <input type="file" name="image" accept="image/*">
                            <button type="submit" name="edit_meal">Save</button>
                        </form>
                     <!--  <a href="?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>  -->
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
