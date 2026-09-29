<?php
include("config/database.php");

$allowedThemes = [
    'van-hoa' => ['label' => 'Văn hóa', 'icon' => '🎭', 'keywords' => ['văn hóa', 'di tích']],
    'am-thuc' => ['label' => 'Ẩm thực', 'icon' => '🍜', 'keywords' => ['ẩm thực', 'ăn uống']],
    'ocop' => ['label' => 'OCOP', 'icon' => '🎁', 'keywords' => ['ocop']],
    'du-lich' => ['label' => 'Du lịch - Điểm đến', 'icon' => '🏞️', 'keywords' => ['du lịch', 'vui chơi', 'điểm đến']]
];

$theme = $_GET['theme'] ?? 'van-hoa';
if (!isset($allowedThemes[$theme])) {
    $theme = 'van-hoa';
}

$themeInfo = $allowedThemes[$theme];

$duration = (int)($_GET['duration'] ?? 180);
if (!in_array($duration, [60, 120, 180, 240, 360], true)) {
    $duration = 180;
}

$stopCount = (int)($_GET['stops'] ?? 3);
$stopCount = max(1, min(6, $stopCount));
$conditions = [];
$params = [];
$types = '';

foreach ($themeInfo['keywords'] as $keyword) {
    $conditions[] = "LOWER(c.category_name) LIKE ?";
    $params[] = '%' . mb_strtolower($keyword, 'UTF-8') . '%';
    $types .= 's';
}

$sql = "
    SELECT
        l.location_id,
        l.name,
        l.address,
        l.description,
        l.latitude,
        l.longitude,
        c.category_name,
        (
            SELECT i.image_url
            FROM images i
            WHERE i.location_id = l.location_id
            ORDER BY i.image_id DESC
            LIMIT 1
        ) AS image_url
    FROM locations l
    LEFT JOIN categories c ON l.category_id = c.category_id
    WHERE l.status = 'active'
      AND (" . implode(' OR ', $conditions) . ")
    ORDER BY l.location_id DESC
    LIMIT ?
";

$params[] = $stopCount;
$types .= 'i';

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$journeyLocations = [];
while ($row = $result->fetch_assoc()) {
    $journeyLocations[] = $row;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hành trình khám phá Hòa Long</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/bandosovanhoa/assets/css/public-theme.css?v=2">
</head>
<body>
<?php
$currentPage = 'journey';
require_once("includes/navbar.php");
?>

<section class="journey-hero">
    <div class="container text-center">
        <div class="journey-kicker">HÀNH TRÌNH SỐ</div>
        <h1>🌿 Hành trình khám phá Hòa Long</h1>
        <p>Chọn chủ đề bạn yêu thích, hệ thống sẽ gợi ý các điểm phù hợp và kết nối chúng với bản đồ số.</p>
    </div>
</section>

<section class="journey-content py-5">
<div class="container">

    <div class="journey-theme-panel mb-4">
        <div class="text-center mb-4">
            <span class="journey-small-title">BẠN MUỐN KHÁM PHÁ GÌ?</span>
            <h2 class="fw-bold mt-2">Chọn một hành trình</h2>
        </div>

        <div class="row g-3 justify-content-center">
            <?php foreach ($allowedThemes as $key => $item): ?>
                <div class="col-6 col-lg-3">
                    <a href="journey.php?theme=<?= urlencode($key) ?>"
                       class="journey-theme-btn <?= $theme === $key ? 'active' : '' ?>">
                        <span><?= $item['icon'] ?></span>
                        <strong><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-5">
        <div class="card-body p-4">
            <div class="journey-small-title mb-2">CÁ NHÂN HÓA HÀNH TRÌNH</div>
            <h3 class="fw-bold mb-3">⏱️ Bạn có bao nhiêu thời gian?</h3>
            <form method="get" class="row g-3 align-items-end">
                <input type="hidden" name="theme" value="<?= htmlspecialchars($theme) ?>">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Thời gian khám phá</label>
                    <select name="duration" class="form-select">
                        <?php foreach ([60=>'1 giờ',120=>'2 giờ',180=>'3 giờ',240=>'4 giờ',360=>'Cả buổi (~6 giờ)'] as $minutes=>$label): ?>
                            <option value="<?= $minutes ?>" <?= $duration===$minutes?'selected':'' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Số điểm muốn tham quan</label>
                    <select name="stops" class="form-select">
                        <?php for($i=1;$i<=6;$i++): ?>
                            <option value="<?= $i ?>" <?= $stopCount===$i?'selected':'' ?>><?= $i ?> điểm</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success w-100">✨ Gợi ý</button>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <div class="journey-small-title">GỢI Ý THEO CHỦ ĐỀ</div>
            <h2 class="fw-bold mb-1"><?= $themeInfo['icon'] ?> <?= htmlspecialchars($themeInfo['label'], ENT_QUOTES, 'UTF-8') ?></h2>
            <div class="text-muted">Có <?= count($journeyLocations) ?> điểm trong hành trình gợi ý.</div>
        </div>
        <?php if (!empty($journeyLocations)): ?>
            <a href="map.php?journey=<?= urlencode($theme) ?>&stops=<?= $stopCount ?>&duration=<?= $duration ?>" class="btn btn-success btn-lg">
                🗺️ Xem toàn bộ hành trình trên bản đồ
            </a>
        <?php endif; ?>
    </div>

    <?php if (!empty($journeyLocations)): ?>
        <div class="journey-timeline">
            <?php foreach ($journeyLocations as $index => $location): ?>
                <article class="journey-stop">
                    <div class="journey-number"><?= $index + 1 ?></div>

                    <div class="journey-stop-card">
                        <div class="journey-stop-image">
                            <?php if (!empty($location['image_url'])): ?>
                                <img src="<?= htmlspecialchars($location['image_url'], ENT_QUOTES, 'UTF-8') ?>"
                                     alt="<?= htmlspecialchars($location['name'], ENT_QUOTES, 'UTF-8') ?>">
                            <?php else: ?>
                                <div class="journey-no-image">📍</div>
                            <?php endif; ?>
                        </div>

                        <div class="journey-stop-body">
                            <span class="badge bg-success mb-2">
                                <?= htmlspecialchars($location['category_name'] ?? 'Địa điểm', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <h3><?= htmlspecialchars($location['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="journey-address">📍 <?= htmlspecialchars($location['address'] ?: 'Chưa cập nhật địa chỉ', ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="journey-desc"><?= htmlspecialchars($location['description'] ?: 'Thông tin địa điểm đang được cập nhật.', ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="location_detail.php?id=<?= (int)$location['location_id'] ?>" class="btn btn-outline-success">Xem chi tiết</a>
                                <a href="map.php?location=<?= (int)$location['location_id'] ?>" class="btn btn-success">📍 Xem trên bản đồ</a>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="journey-empty text-center">
            <div>🌱</div>
            <h3>Chưa có địa điểm phù hợp</h3>
            <p class="text-muted">Bạn có thể chọn một chủ đề khác hoặc bổ sung địa điểm trong trang quản trị.</p>
        </div>
    <?php endif; ?>

</div>
</section>

<footer>
<div class="container py-4 text-center">
    <div class="fw-bold text-white">🗺️ Bản đồ số Văn hóa - Du lịch</div>
    <div class="small mt-2 text-white-50">Khám phá Hòa Long trên nền tảng số</div>
</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
