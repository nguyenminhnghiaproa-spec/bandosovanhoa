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

$imagePath = null;

if (isset($_FILES["review_image"]) && $_FILES["review_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES["review_image"]["error"] !== UPLOAD_ERR_OK) {
        $_SESSION["review_error"] = "Không thể tải ảnh lên. Vui lòng thử lại.";
        header("Location: location_detail.php?id=" . $locationId . "#reviews");
        exit;
    }

    if ($_FILES["review_image"]["size"] > 5 * 1024 * 1024) {
        $_SESSION["review_error"] = "Ảnh đánh giá không được lớn hơn 5 MB.";
        header("Location: location_detail.php?id=" . $locationId . "#reviews");
        exit;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES["review_image"]["tmp_name"]);
    $allowed = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    if (!isset($allowed[$mime])) {
        $_SESSION["review_error"] = "Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.";
        header("Location: location_detail.php?id=" . $locationId . "#reviews");
        exit;
    }

    $uploadDir = __DIR__ . "/uploads/reviews/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = "review_" . $locationId . "_" . bin2hex(random_bytes(8)) . "." . $allowed[$mime];
    $target = $uploadDir . $fileName;

    if (!move_uploaded_file($_FILES["review_image"]["tmp_name"], $target)) {
        $_SESSION["review_error"] = "Không thể lưu ảnh đánh giá.";
        header("Location: location_detail.php?id=" . $locationId . "#reviews");
        exit;
    }

    $imagePath = "uploads/reviews/" . $fileName;
}

$stmt = $conn->prepare("INSERT INTO location_reviews (location_id, reviewer_name, rating, comment, review_image, status) VALUES (?, ?, ?, ?, ?, 'pending')");
$stmt->bind_param("isiss", $locationId, $name, $rating, $comment, $imagePath);

if (!$stmt->execute()) {
    if ($imagePath && file_exists(__DIR__ . "/" . $imagePath)) {
        unlink(__DIR__ . "/" . $imagePath);
    }
    $_SESSION["review_error"] = "Không thể lưu đánh giá. Vui lòng thử lại.";
} else {
    $_SESSION["review_success"] = "Cảm ơn bạn! Đánh giá và hình ảnh đã được gửi, đang chờ quản trị viên duyệt.";
}

header("Location: location_detail.php?id=" . $locationId . "#reviews");
exit;
