<?php
session_start();
require_once("config/database.php");

$success = $_SESSION["feedback_success"] ?? "";
$error = $_SESSION["feedback_error"] ?? "";
unset($_SESSION["feedback_success"], $_SESSION["feedback_error"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["visitor_name"] ?? "");
    $rating = (int)($_POST["rating"] ?? 0);
    $category = trim($_POST["category"] ?? "Trải nghiệm chung");
    $comment = trim($_POST["comment"] ?? "");
    $allowed = ["Trải nghiệm chung","Giao diện","Thông tin địa điểm","Bản đồ","Hành trình khám phá"];

    if (strlen($name) < 2 || $rating < 1 || $rating > 5 || strlen($comment) < 3 || !in_array($category,$allowed,true)) {
        $_SESSION["feedback_error"] = "Vui lòng nhập đầy đủ thông tin và chọn mức hài lòng.";
    } else {
        $stmt=$conn->prepare("INSERT INTO site_feedback (visitor_name,rating,category,comment,status) VALUES (?,?,?,?,'pending')");
        $stmt->bind_param("siss",$name,$rating,$category,$comment);
        $stmt->execute();
        $_SESSION["feedback_success"] = "Cảm ơn bạn! Góp ý đã được gửi đến quản trị viên.";
    }
    header("Location: feedback.php");
    exit;
}

$summary=$conn->query("SELECT COUNT(*) total, ROUND(AVG(rating),1) average_rating FROM site_feedback WHERE status='approved'")->fetch_assoc();
$currentPage="feedback";
?>
<!doctype html><html lang="vi"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Góp ý và đánh giá website</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/bandosovanhoa/assets/css/public-theme.css?v=2">
<style>
.feedback-hero{background:linear-gradient(135deg,#0b2d21,#2f7658);color:#fff;border-radius:24px;padding:38px}
.rating-input{display:flex;flex-direction:row-reverse;justify-content:flex-end;gap:5px}
.rating-input input{display:none}.rating-input label{font-size:40px;color:#ced4da;cursor:pointer;line-height:1}
.rating-input label:hover,.rating-input label:hover~label,.rating-input input:checked~label{color:#ffc107}
</style></head><body>
<?php require_once("includes/navbar.php"); ?>
<div class="container py-4">
<div class="feedback-hero mb-4">
<span class="badge bg-light text-success mb-3">💬 Ý KIẾN CỘNG ĐỒNG</span>
<h1 class="fw-bold">Đánh giá trải nghiệm website</h1>
<p class="mb-0 opacity-75">Ý kiến của bạn giúp bản đồ số Hòa Long cải thiện thông tin, bản đồ và trải nghiệm khám phá địa phương.</p>
</div>
<div class="row g-4">
<div class="col-lg-7">
<div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4">
<h4 class="fw-bold mb-3">Bạn thấy website thế nào?</h4>
<?php if($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="post">
<div class="mb-3"><label class="form-label fw-semibold">Tên của bạn</label><input class="form-control" name="visitor_name" maxlength="100" required></div>
<div class="mb-3"><label class="form-label fw-semibold">Bạn muốn góp ý về</label>
<select class="form-select" name="category">
<option>Trải nghiệm chung</option><option>Giao diện</option><option>Thông tin địa điểm</option><option>Bản đồ</option><option>Hành trình khám phá</option>
</select></div>
<div class="mb-3"><label class="form-label fw-semibold d-block">Mức độ hài lòng</label>
<div class="rating-input">
<?php for($i=5;$i>=1;$i--): ?><input type="radio" name="rating" id="site-star<?= $i ?>" value="<?= $i ?>" <?= $i===5?'required':'' ?>><label for="site-star<?= $i ?>">★</label><?php endfor; ?>
</div></div>
<div class="mb-3"><label class="form-label fw-semibold">Góp ý</label><textarea class="form-control" name="comment" rows="5" maxlength="1500" required placeholder="Điều gì bạn thích hoặc muốn website cải thiện?"></textarea></div>
<button class="btn btn-success px-4 fw-semibold">💬 Gửi góp ý</button>
</form></div></div></div>
<div class="col-lg-5">
<div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-body p-4 text-center">
<div class="display-5">⭐</div><h2 class="fw-bold mb-1"><?= ($summary["total"]??0)>0 ? htmlspecialchars($summary["average_rating"])."/5" : "Chưa có" ?></h2>
<div class="text-muted"><?= (int)($summary["total"]??0) ?> phản hồi đã được duyệt</div>
</div></div>
<div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4">
<h5 class="fw-bold">Phản hồi được sử dụng để làm gì?</h5>
<p class="text-muted mb-0">Giúp quản trị viên nhận biết phần nào của website cần cải thiện và theo dõi mức độ hài lòng của người sử dụng.</p>
</div></div></div>
</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body></html>