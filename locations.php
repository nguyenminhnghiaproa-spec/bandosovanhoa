<?php

include("config/database.php");


/* =====================================================
   NHẬN TỪ KHÓA + DANH MỤC
===================================================== */

$keyword =
    trim($_GET['keyword'] ?? '');

$category_id =
    isset($_GET['category_id'])
    ? (int)$_GET['category_id']
    : 0;


/* =====================================================
   LẤY DANH MỤC
===================================================== */

$categories =
    $conn->query("
        SELECT
            category_id,
            category_name
        FROM categories
        ORDER BY category_name ASC
    ");


/* =====================================================
   LẤY ĐỊA ĐIỂM
===================================================== */

$sql = "
    SELECT
        l.location_id,
        l.name,
        l.address,
        l.description,
        l.phone,
        l.opening_hours,
        c.category_name,

        (
            SELECT i.image_url
            FROM images i
            WHERE i.location_id = l.location_id
            ORDER BY i.image_id DESC
            LIMIT 1
        ) AS image_url

    FROM locations l

    LEFT JOIN categories c
        ON l.category_id = c.category_id

    WHERE l.status = 'active'
";


$params = [];
$types = "";


/* TÌM KIẾM */

if ($keyword !== "") {

    $sql .= "
        AND (
            l.name LIKE ?
            OR l.address LIKE ?
            OR l.description LIKE ?
        )
    ";

    $searchKeyword =
        "%" . $keyword . "%";

    $params[] = $searchKeyword;
    $params[] = $searchKeyword;
    $params[] = $searchKeyword;

    $types .= "sss";
}


/* LỌC DANH MỤC */

if ($category_id > 0) {

    $sql .= "
        AND l.category_id = ?
    ";

    $params[] = $category_id;

    $types .= "i";
}


$sql .= "
    ORDER BY l.location_id DESC
";


$stmt =
    $conn->prepare($sql);


if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );

}


$stmt->execute();

$locations =
    $stmt->get_result();


$totalResults =
    $locations->num_rows;

?>

<!DOCTYPE html>

<html lang="vi">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Danh sách địa điểm
</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<style>

body {

    background: #f5f7f8;

}


.main-navbar {

    background: #198754;

}


.navbar-brand {

    font-weight: 700;

}


/* HEADER */

.page-header {

    background:
        linear-gradient(
            135deg,
            #198754,
            #146c43
        );

    color: white;

    padding: 55px 0;

}


.page-header h1 {

    font-weight: 800;

}


/* SEARCH */

.search-box {

    background: white;

    border-radius: 16px;

    padding: 25px;

    margin-top: -35px;

    position: relative;

    z-index: 5;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,0.10);

}


/* CARD */

.location-card {

    border: none;

    border-radius: 16px;

    overflow: hidden;

    height: 100%;

    transition: 0.25s;

}


.location-card:hover {

    transform:
        translateY(-5px);

    box-shadow:
        0 10px 25px
        rgba(0,0,0,0.12);

}


.location-image {

    width: 100%;

    height: 230px;

    object-fit: cover;

}


.no-image {

    width: 100%;

    height: 230px;

    background: #e9ecef;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 65px;

}


.location-description {

    color: #6c757d;

    display: -webkit-box;

    -webkit-line-clamp: 3;

    -webkit-box-orient: vertical;

    overflow: hidden;

    min-height: 72px;

}


.location-address {

    min-height: 48px;

}


/* FOOTER */

footer {

    background: #212529;

    color:
        rgba(
            255,
            255,
            255,
            0.8
        );

}

</style>

<link rel="stylesheet" href="/bandosovanhoa/assets/css/public-theme.css?v=1">

</head>


<body>

<?php

$currentPage = 'locations';

require_once("includes/navbar.php");

?>


<!-- =====================================================
     HEADER
===================================================== -->

<section class="page-header">

<div class="container text-center">


<h1>

    📍 Khám phá địa điểm

</h1>


<p class="mb-0">

    Tìm kiếm các địa điểm văn hóa,
    du lịch, di tích, ẩm thực,
    OCOP và điểm đến địa phương

</p>


</div>

</section>



<!-- =====================================================
     SEARCH
===================================================== -->

<div class="container">


<div class="search-box">


<form
    method="GET"
    action="locations.php"
>


<div class="row g-3">


<!-- TỪ KHÓA -->

<div class="col-lg-6">


<input
    type="text"
    name="keyword"
    class="form-control"
    placeholder="🔎 Nhập tên địa điểm, địa chỉ..."
    value="<?= htmlspecialchars(
        $keyword,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>


</div>



<!-- DANH MỤC -->

<div class="col-lg-3">


<select
    name="category_id"
    class="form-select"
>


<option value="0">

    Tất cả danh mục

</option>


<?php if ($categories): ?>


<?php while (
    $category =
        $categories->fetch_assoc()
): ?>


<option
    value="<?= (int)$category['category_id'] ?>"

    <?=

    $category_id
    ===
    (int)$category['category_id']

    ? 'selected'
    : ''

    ?>
>

    <?= htmlspecialchars(
        $category['category_name'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>

</option>


<?php endwhile; ?>


<?php endif; ?>


</select>


</div>



<!-- BUTTON -->

<div class="col-lg-3">


<div class="d-flex gap-2">


<button
    type="submit"
    class="btn btn-success flex-grow-1"
>

    🔍 Tìm kiếm

</button>


<a
    href="locations.php"
    class="btn btn-outline-secondary"
    title="Đặt lại"
>

    ↻

</a>


</div>


</div>


</div>


</form>


</div>


</div>



<!-- =====================================================
     DANH SÁCH
===================================================== -->

<section class="py-5">


<div class="container">


<div
    class="
        d-flex
        flex-wrap
        justify-content-between
        align-items-center
        gap-2
        mb-4
    "
>


<div>


<h3 class="fw-bold mb-1">

    Danh sách địa điểm

</h3>


<div class="text-muted">

    Tìm thấy

    <strong>

        <?= $totalResults ?>

    </strong>

    địa điểm

</div>


</div>



<a
    href="map.php"
    class="btn btn-outline-success"
>

    🗺️ Xem trên bản đồ

</a>


</div>



<div class="row g-4">


<?php if (
    $totalResults > 0
): ?>


<?php while (
    $location =
        $locations->fetch_assoc()
): ?>


<div
    class="
        col-xl-4
        col-md-6
    "
>


<div
    class="
        card
        location-card
        shadow-sm
    "
>


<!-- HÌNH ẢNH -->

<?php if (
    !empty($location['image_url'])
): ?>


<img
    src="<?= htmlspecialchars(
        $location['image_url'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    class="location-image"
    alt="<?= htmlspecialchars(
        $location['name'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>


<?php else: ?>


<div class="no-image">

    📍

</div>


<?php endif; ?>



<div class="card-body p-4">


<!-- DANH MỤC -->

<div class="mb-2">


<span class="badge bg-success">

    <?= htmlspecialchars(
        $location['category_name']
        ?? 'Chưa phân loại',
        ENT_QUOTES,
        'UTF-8'
    ) ?>

</span>


</div>



<!-- TÊN -->

<h5 class="fw-bold">

    <?= htmlspecialchars(
        $location['name'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>

</h5>



<!-- ĐỊA CHỈ -->

<div
    class="
        text-muted
        small
        location-address
        mb-3
    "
>

    📍

    <?= htmlspecialchars(
        $location['address']
        ?: 'Chưa cập nhật địa chỉ',
        ENT_QUOTES,
        'UTF-8'
    ) ?>

</div>



<!-- MÔ TẢ -->

<p class="location-description">

    <?= htmlspecialchars(
        $location['description']
        ?: 'Thông tin địa điểm đang được cập nhật.',
        ENT_QUOTES,
        'UTF-8'
    ) ?>

</p>



<div class="d-grid gap-2">

    <a
        href="location_detail.php?id=<?= (int)$location['location_id'] ?>"
        class="btn btn-outline-success"
    >
        👁 Xem chi tiết
    </a>

    <a
        href="map.php?location=<?= (int)$location['location_id'] ?>"
        class="btn btn-success"
    >
        🗺️ Xem trên bản đồ
    </a>

</div>

</div>


</div>


</div>


<?php endwhile; ?>


<?php else: ?>


<div class="col-12">


<div
    class="
        card
        border-0
        shadow-sm
    "
>


<div
    class="
        card-body
        text-center
        py-5
    "
>


<div
    style="
        font-size:60px;
    "
>

    🔍

</div>


<h4 class="mt-3">

    Không tìm thấy địa điểm

</h4>


<p class="text-muted">

    Hãy thử từ khóa khác
    hoặc chọn danh mục khác.

</p>


<a
    href="locations.php"
    class="btn btn-success"
>

    Xem tất cả địa điểm

</a>


</div>


</div>


</div>


<?php endif; ?>


</div>


</div>


</section>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer>


<div
    class="
        container
        py-4
        text-center
    "
>


<div class="fw-bold text-white">

    🗺️ Bản đồ số Văn hóa - Du lịch

</div>


<div class="small mt-2">

    Hệ thống giới thiệu
    văn hóa, du lịch và
    điểm đến địa phương

</div>


</div>


</footer>



<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>