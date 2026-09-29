<?php
session_start();
require_once("config/database.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$locationId = (int)($_POST["location_id"] ?? 0);
$name = trim($_POST["reviewer_name"] ?? "");
$rating = (int)($_POST["rating"] ?? 0);
$comment = trim($_POST["comment"] ?? "");

if ($locationId < 1 || $rating < 1 || $rating > 5 || strlen($name) < 2 || strlen($comment) < 3) {
    $_SESSION["review_error"] = "Vui lòng nhập đầy đủ tên, số sao và nhận xét.";
    header("Location: location_detail.php?id=" . $locationId . "#reviews");
    exit;
}

$check = $conn->prepare("SELECT location_id FROM locations WHERE location_id = ? LIMIT 1");
$check->bind_param("i", $locationId);
$check->execute();

if ($check->get_result()->num_rows === 0) {
    header("Location: locations.php");
    exit;
}

$stmt = $conn->prepare("INSERT INTO location_reviews (location_id, reviewer_name, rating, comment, status) VALUES (?, ?, ?, ?, 'pending')");
$stmt->bind_param("isis", $locationId, $name, $rating, $comment);
$stmt->execute();

$_SESSION["review_success"] = "Cảm ơn bạn! Đánh giá đã được gửi và đang chờ quản trị viên duyệt.";
header("Location: location_detail.php?id=" . $locationId . "#reviews");
exit;
