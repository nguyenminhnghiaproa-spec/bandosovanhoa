<?php
require_once("../auth.php");
require_once("../../config/database.php");
$currentAdminPage="feedback";

if($_SERVER["REQUEST_METHOD"]==="POST"){
$id=(int)($_POST["feedback_id"]??0); $action=$_POST["action"]??"";
if($id>0 && in_array($action,["approve","hide"],true)){
$status=$action==="approve"?"approved":"hidden";
$stmt=$conn->prepare("UPDATE site_feedback SET status=? WHERE feedback_id=?");$stmt->bind_param("si",$status,$id);$stmt->execute();
}elseif($id>0 && $action==="delete"){
$stmt=$conn->prepare("DELETE FROM site_feedback WHERE feedback_id=?");$stmt->bind_param("i",$id);$stmt->execute();
}
header("Location: index.php");exit;
}
$stats=$conn->query("SELECT COUNT(*) total,SUM(status='pending') pending_count,SUM(status='approved') approved_count,ROUND(AVG(CASE WHEN status='approved' THEN rating END),1) average_rating FROM site_feedback")->fetch_assoc();
$rows=$conn->query("SELECT * FROM site_feedback ORDER BY (status='pending') DESC,created_at DESC");
?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Góp ý website</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><?php require_once("../includes/navbar.php"); ?>
<div class="admin-page"><h2 class="fw-bold">💬 Góp ý website</h2><p class="text-muted">Theo dõi mức độ hài lòng và ý kiến cải thiện từ người sử dụng.</p>
<div class="row g-3 mb-4">
<?php foreach([["Tổng phản hồi",(int)($stats["total"]??0)],["Chờ duyệt",(int)($stats["pending_count"]??0)],["Đã duyệt",(int)($stats["approved_count"]??0)],["Hài lòng TB",($stats["average_rating"]??"—")." ⭐"]] as $x): ?>
<div class="col-6 col-lg-3"><div class="card admin-card admin-stat-card"><div class="card-body"><div class="text-muted"><?= $x[0] ?></div><h3><?= $x[1] ?></h3></div></div></div><?php endforeach; ?></div>
<div class="card admin-card admin-table-card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
<thead class="table-success"><tr><th>Khách</th><th>Nội dung góp ý</th><th>Sao</th><th>Nhận xét</th><th>Ngày</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
<?php if($rows->num_rows===0): ?><tr><td colspan="7" class="text-center text-muted py-5">Chưa có góp ý.</td></tr>
<?php else: while($row=$rows->fetch_assoc()): $status=$row["status"]; ?>
<tr><td class="fw-semibold"><?= htmlspecialchars($row["visitor_name"]) ?></td><td><?= htmlspecialchars($row["category"]) ?></td>
<td class="text-warning text-nowrap"><?= str_repeat("★",(int)$row["rating"]) ?><span class="text-secondary"><?= str_repeat("☆",5-(int)$row["rating"]) ?></span></td>
<td style="min-width:220px"><?= nl2br(htmlspecialchars($row["comment"])) ?></td><td class="text-nowrap"><?= date("d/m/Y H:i",strtotime($row["created_at"])) ?></td>
<td><span class="badge text-bg-<?= $status==="approved"?"success":($status==="pending"?"warning":"secondary") ?>"><?= $status==="approved"?"Đã duyệt":($status==="pending"?"Chờ duyệt":"Đã ẩn") ?></span></td>
<td class="text-nowrap"><form method="post"><input type="hidden" name="feedback_id" value="<?= (int)$row["feedback_id"] ?>">
<?php if($status!=="approved"): ?><button class="btn btn-success btn-sm" name="action" value="approve">✓ Duyệt</button><?php endif; ?>
<?php if($status!=="hidden"): ?><button class="btn btn-outline-secondary btn-sm" name="action" value="hide">Ẩn</button><?php endif; ?>
<button class="btn btn-outline-danger btn-sm" name="action" value="delete" onclick="return confirm('Xóa góp ý này?')">Xóa</button></form></td></tr>
<?php endwhile; endif; ?></tbody></table></div></div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script></body></html>