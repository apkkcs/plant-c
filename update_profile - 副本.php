<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../db_connection.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $avatar_path = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
        $avatar_dir = "../admin/avatar/";
        $avatar_file = basename($_FILES['avatar']['name']);
        $avatar_path = $avatar_dir . $avatar_file;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path)) {
            $avatar_path = "admin/avatar/" . $avatar_file; // 保存相对路径
        } else {
            $_SESSION['message'] = "<span style='color: red; font-weight: bold;'>Failed to upload avatar.</span>";
            header("Location: admin_dashboard.php?page=profile");
            exit();
        }
    }

    // 检查邮箱是否重复
    $query_admin = "SELECT COUNT(*) AS count FROM admin WHERE email = ? AND id != ?";
    $stmt_admin = $conn->prepare($query_admin);
    $stmt_admin->bind_param("si", $email, $admin_id);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();
    $row_admin = $result_admin->fetch_assoc();

    $query_students = "SELECT COUNT(*) AS count FROM students WHERE email = ?";
    $stmt_students = $conn->prepare($query_students);
    $stmt_students->bind_param("s", $email);
    $stmt_students->execute();
    $result_students = $stmt_students->get_result();
    $row_students = $result_students->fetch_assoc();

    if ($row_admin['count'] > 0 || $row_students['count'] > 0) {
        $_SESSION['message'] = "<span style='color: red; font-weight: bold;'>Error: Email is already in use.</span>";
        header("Location: admin_dashboard.php?page=profile");
        exit();
    }

    // 更新管理员信息
    $query = "UPDATE admin SET email = ?, phone = ?, address = ?" . ($avatar_path ? ", avatar = ?" : "") . " WHERE id = ?";
    if ($avatar_path) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $email, $phone, $address, $avatar_path, $admin_id);
    } else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $email, $phone, $address, $admin_id);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "<span style='color: blue; font-weight: bold;'>Profile updated successfully.</span>";
    } else {
        $_SESSION['message'] = "<span style='color: red; font-weight: bold;'>Error: Failed to update profile.</span>";
    }

    header("Location: admin_dashboard.php?page=profile");
    exit();
}
?>
