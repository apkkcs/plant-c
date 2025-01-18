<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 检查是否登录
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../db_connection.php';

$admin_id = $_SESSION['admin_id'];
$query = "SELECT * FROM admin WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "No admin found with this ID.";
    exit();
}

$admin = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h2>Manage Profile</h2>
    <div class="profile-card">
    <h2>Manage Profile</h2>
    <!-- 动态加载头像 -->
    <img src="/restaurant_ordering_system/<?= htmlspecialchars($admin['avatar'] ?: 'admin/avatar/default.jpg') ?>" alt="Avatar" class="profile-avatar">
    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
        <!-- 上传头像 -->
        <div class="form-group">
            <label for="avatar">Upload New Avatar:</label>
            <input type="file" name="avatar" id="avatar">
        </div>

        <!-- 静态信息 -->
        <div class="form-static">
            <p><strong>Name:</strong> <?= htmlspecialchars($admin['name']) ?></p>
            <p><strong>ID:</strong> <?= htmlspecialchars($admin['admin_id']) ?></p>
        </div>

        <!-- 更新信息 -->
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone:</label>
            <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($admin['phone']) ?>" required>
        </div>
        <div class="form-group">
            <label for="address">Address:</label>
            <input type="text" name="address" id="address" value="<?= htmlspecialchars($admin['address']) ?>" required>
        </div>

        <!-- 提交按钮 -->
        <div class="form-actions">
            <button type="submit">Update Profile</button>
        </div>
    </form>
</div>

<style>
    .profile-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 20px;
        max-width: 600px;
        margin: 0 auto;
    }
    .profile-card h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }
    .profile-avatar {
        display: block;
        margin: 0 auto 20px;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #007bff;
    }
    .form-group {
        margin-bottom: 15px;
        text-align: left;
    }
    .form-group label {
        font-weight: bold;
        margin-bottom: 5px;
        display: block;
        color: #555;
    }
    .form-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    .form-static p {
        margin: 10px 0;
        color: #333;
    }
    .form-actions {
        text-align: center;
        margin-top: 20px;
    }
    .form-actions button {
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
    }
    .form-actions button:hover {
        background-color: #0056b3;
    }
</style>

</body>
</html>
