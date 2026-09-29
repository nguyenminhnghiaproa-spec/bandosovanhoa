<?php
require_once("../auth.php");
require_once("../../config/database.php");
$currentAdminPage = "reviews";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int)($_POST["review_id"] ?? 0);
    $action = $_POST["action"] ?? "";

    if ($id > 0 && in_array($action, ["approve", "hide"], true)) {
        $status = $action === "approve" ? "approved" : "hidden";
        $stmt = $conn->prepare("UPDATE location_reviews SET status=? WHERE review_id=?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
    } elseif ($id > 0 && $action === "delete") {
        $stmt = $conn->prepare("DELETE FROM location_reviews WHERE review_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header("Location: index.php");
    exit;
}

$stats = $conn->query("SELECT COUNT(*) total, SUM(status='pending') pending_count, SUM(status='approved') approved_count, ROUND(AVG(CASE WHEN status='approved' THEN rating END),1) average_rating FROM location_reviews")->fetch_assoc();
$reviews = $conn->query("SELECT r.*, l.name location_name FROM location_reviews r JOIN locations l ON l.location_id=r.location_id ORDER BY (r.status='pending') DESC, r.created_at DESC");
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Quản lý đánh giá</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php require_once("../includes/navbar.php"); ?>
<div class="container py-4">
<h2 class="fw-bold">⭐ Đánh giá địa điểm</h2>
<p class="text-muted">Duyệt và quản lý phản hồi của khách tham quan.</p>

<div class="row g-3 mb-4">
<?php
$cards=[
["Tổng đánh giá",(int)($stats["total"]??0)],
["Chờ duyệt",(int)($stats["pending_count"]??0)],
["Đã duyệt",(int)($stats["approved_count"]??0)],
["Điểm trung bình",($stats["average_rating"]??"—")." ⭐"]
];
foreach($cards as $card):
?>
<div class="col-6 col-lg-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted"><?= $card[0] ?></div><h3 class="mb-0"><?= $card[1] ?></h3></div></div></div>
<?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead class="table-success"><tr><th>Khách</th><th>Địa điểm</th><th>Đánh giá</th><th>Nhận xét</th><th>Ngày</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
<tbody>
<?php if($reviews->num_rows===0): ?>
<tr><td colspan="7" class="text-center text-muted py-5">Chưa có đánh giá.</td></tr>
<?php else: while($row=$reviews->fetch_assoc()): ?>
<tr>
<td class="fw-semibold"><?= htmlspecialchars($row["reviewer_name"]) ?></td>
<td><?= htmlspecialchars($row["location_name"]) ?></td>
<td class="text-warning text-nowrap"><?= str_repeat("★",(int)$row["rating"]) ?><span class="text-secondary"><?= str_repeat("☆",5-(int)$row["rating"]) ?></span></td>
<td style="min-width:220px;max-width:400px"><?= nl2br(htmlspecialchars($row["comment"])) ?></td>
<td class="text-nowrap"><?= date("d/m/Y H:i",strtotime($row["created_at"])) ?></td>
<td><?php
$status=$row["status"];
$label=$status==="approved"?"Đã duyệt":($status==="pending"?"Chờ duyệt":"Đã ẩn");
$badge=$status==="approved"?"success":($status==="pending"?"warning":"secondary");
?><span class="badge text-bg-<?= $badge ?>"><?= $label ?></span></td>
<td class="text-nowrap">
<form method="post">
<input type="hidden" name="review_id" value="<?= (int)$row["review_id"] ?>">
<?php if($status!=="approved"): ?><button name="action" value="approve" class="btn btn-success btn-sm">✓ Duyệt</button><?php endif; ?>
<?php if($status!=="hidden"): ?><button name="action" value="hide" class="btn btn-outline-secondary btn-sm">Ẩn</button><?php endif; ?>
<button name="action" value="delete" class="btn btn-outline-danger btn-sm" onclick="return confirm('Xóa đánh giá này?')">Xóa</button>
</form>
</td>
</tr>
<?php endwhile; endif; ?>
</tbody></table></div></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body></html>