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

$files = $_FILES["review_images"] ?? null;
$validFiles = [];
$allowed = ["image/jpeg"=>"jpg", "image/png"=>"png", "image/webp"=>"webp"];

if ($files && is_array($files["name"])) {
    $selectedCount = 0;
    foreach ($files["error"] as $error) {
        if ($error !== UPLOAD_ERR_NO_FILE) $selectedCount++;
    }
    if ($selectedCount > 6) {
        $_SESSION["review_error"] = "Mỗi đánh giá được tải tối đa 6 ảnh.";
        header("Location: location_detail.php?id=" . $locationId . "#reviews");
        exit;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    foreach ($files["name"] as $i => $originalName) {
        if ($files["error"][$i] === UPLOAD_ERR_NO_FILE) continue;
        if ($files["error"][$i] !== UPLOAD_ERR_OK || $files["size"][$i] > 5 * 1024 * 1024) {
            $_SESSION["review_error"] = "Mỗi ảnh phải tải thành công và không lớn hơn 5 MB.";
            header("Location: location_detail.php?id=" . $locationId . "#reviews");
            exit;
        }
        $mime = $finfo->file($files["tmp_name"][$i]);
        if (!isset($allowed[$mime])) {
            $_SESSION["review_error"] = "Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.";
            header("Location: location_detail.php?id=" . $locationId . "#reviews");
            exit;
        }
        $validFiles[] = ["tmp"=>$files["tmp_name"][$i], "ext"=>$allowed[$mime]];
    }
}

$stmt = $conn->prepare("INSERT INTO location_reviews (location_id, reviewer_name, rating, comment, status) VALUES (?, ?, ?, ?, 'pending')");
$stmt->bind_param("isis", $locationId, $name, $rating, $comment);

if (!$stmt->execute()) {
    $_SESSION["review_error"] = "Không thể lưu đánh giá. Vui lòng thử lại.";
    header("Location: location_detail.php?id=" . $locationId . "#reviews");
    exit;
}

$reviewId = $conn->insert_id;
$savedPaths = [];

if ($validFiles) {
    $uploadDir = __DIR__ . "/uploads/reviews/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $imageStmt = $conn->prepare("INSERT INTO review_images (review_id, image_url) VALUES (?, ?)");
    foreach ($validFiles as $file) {
        $fileName = "review_" . $reviewId . "_" . bin2hex(random_bytes(8)) . "." . $file["ext"];
        $target = $uploadDir . $fileName;
        if (move_uploaded_file($file["tmp"], $target)) {
            $path = "uploads/reviews/" . $fileName;
            $imageStmt->bind_param("is", $reviewId, $path);
            $imageStmt->execute();
            $savedPaths[] = $path;
        }
    }
}

$_SESSION["review_success"] = "Cảm ơn bạn! Đánh giá" . ($savedPaths ? " và " . count($savedPaths) . " ảnh" : "") . " đã được gửi, đang chờ quản trị viên duyệt.";
header("Location: location_detail.php?id=" . $locationId . "#reviews");
exit;
