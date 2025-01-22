<?php
// 数据库连接
$servername = "localhost";
$username = "root";
$password = "";
$database = "canteen_system";

// 创建连接
$conn = new mysqli($servername, $username, $password);

// 检查连接
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 创建数据库
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists.<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// 选择数据库
$conn->select_db($database);

// 创建 students 表
$sql = "CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) UNIQUE NOT NULL,  -- 学号
    name VARCHAR(255) NOT NULL,              -- 姓名
    email VARCHAR(255) UNIQUE NOT NULL,      -- 邮箱
    phone VARCHAR(20),                       -- 手机号
    address TEXT,                            -- 地址
    password VARCHAR(255) NOT NULL,          -- 密码
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'students' created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// 创建 admin 表（管理员账户）
$sql = "CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,    -- 管理员用户名
    password VARCHAR(255) NOT NULL,          -- 密码（加密存储）
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'admin' created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// 创建 menu_items 表（菜品管理）
$sql = "CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,              -- 菜品名称
    price DECIMAL(10, 2) NOT NULL,           -- 菜品价格
    image_path VARCHAR(255),                 -- 菜品图片路径
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'menu_items' created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// 创建 purchase_records 表（购买记录）
$sql = "CREATE TABLE IF NOT EXISTS purchase_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,         -- 学号（外键关联）
    menu_item_id INT NOT NULL,               -- 菜品 ID（外键关联）
    quantity INT NOT NULL,                   -- 购买数量
    total_price DECIMAL(10, 2) NOT NULL,     -- 总价
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
)";
if ($conn->query($sql) === TRUE) {
    echo "Table 'purchase_records' created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// 关闭连接
$conn->close();
?>
