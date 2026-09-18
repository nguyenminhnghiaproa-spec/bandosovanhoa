<?php

require_once("auth.php");
include("../config/database.php");

$error = "";
$success = "";

$user_id = $_SESSION['admin_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password != $confirm_password) {

        $error = "Mật khẩu xác nhận không khớp.";

    } elseif (strlen($new_password) < 6) {

        $error = "Mật khẩu phải có ít nhất 6 ký tự.";

    } else {

        $stmt = $conn->prepare("
            SELECT password
            FROM users
            WHERE user_id = ?
        ");

        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!password_verify($old_password, $user['password'])) {

            $error = "Mật khẩu hiện tại không đúng.";

        } else {

            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

            $update = $conn->prepare("
                UPDATE users
                SET password = ?
                WHERE user_id = ?
            ");

            $update->bind_param("si", $new_hash, $user_id);

            if ($update->execute()) {

                $success = "Đổi mật khẩu thành công.";

            } else {

                $error = "Không thể cập nhật mật khẩu.";

            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Đổi mật khẩu</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{

background:#f4f6f8;

}

.card{

border:none;

border-radius:18px;

}

</style>

</head>
<body>

<nav class="navbar navbar-dark bg-success">

<div class="container">

<a class="navbar-brand fw-bold" href="index.php">

🗺️ QUẢN TRỊ BẢN ĐỒ SỐ

</a>

<a href="index.php" class="btn btn-outline-light btn-sm">

← Quay lại

</a>

</div>

</nav>

<div class="container py-5">

<div class="row justify-content-center">

<div class="col-md-6">

<div class="card shadow">

<div class="card-body p-4">

<h3 class="fw-bold mb-4">

🔐 Đổi mật khẩu

</h3>

<?php if($error!=""): ?>

<div class="alert alert-danger">

<?= $error ?>

</div>

<?php endif; ?>

<?php if($success!=""): ?>

<div class="alert alert-success">

<?= $success ?>

</div>

<?php endif; ?>

<form method="POST">

<div class="mb-3">

<label class="form-label">

Mật khẩu hiện tại

</label>

<input type="password" name="old_password" class="form-control" required>

</div>

<div class="mb-3">

<label class="form-label">

Mật khẩu mới

</label>

<input type="password" name="new_password" class="form-control" required>

</div>

<div class="mb-4">

<label class="form-label">

Xác nhận mật khẩu mới

</label>

<input type="password" name="confirm_password" class="form-control" required>

</div>

<button class="btn btn-success w-100">

💾 Đổi mật khẩu

</button>

</form>

</div>

</div>

</div>

</div>

</div>

</body>
</html>