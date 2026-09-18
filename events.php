<?php

include("config/database.php");


/* =====================================================
   NHẬN DỮ LIỆU TÌM KIẾM
===================================================== */

$keyword =
    trim($_GET['keyword'] ?? '');

$location_id =
    isset($_GET['location_id'])
    ? (int)$_GET['location_id']
    : 0;


/* =====================================================
   DANH SÁCH ĐỊA ĐIỂM
===================================================== */

$locationList =
    $conn->query("
        SELECT
            location_id,
            name
        FROM locations
        WHERE status = 'active'
        ORDER BY name ASC
    ");


/* =====================================================
   QUERY SỰ KIỆN
===================================================== */

$sql = "
    SELECT
        e.event_id,
        e.event_name,
        e.event_date,
        e.description,

        l.location_id,
        l.name AS location_name,
        l.address,

        c.category_name

    FROM events e

    INNER JOIN locations l
        ON e.location_id = l.location_id

    LEFT JOIN categories c
        ON l.category_id = c.category_id

    WHERE l.status = 'active'
";


$params = [];
$types = "";


/* =====================================================
   TÌM THEO TỪ KHÓA
===================================================== */

if ($keyword !== "") {

    $sql .= "
        AND (
            e.event_name LIKE ?
            OR e.description LIKE ?
            OR l.name LIKE ?
        )
    ";

    $searchKeyword =
        "%" . $keyword . "%";

    $params[] = $searchKeyword;
    $params[] = $searchKeyword;
    $params[] = $searchKeyword;

    $types .= "sss";
}


/* =====================================================
   LỌC THEO ĐỊA ĐIỂM
===================================================== */

if ($location_id > 0) {

    $sql .= "
        AND e.location_id = ?
    ";

    $params[] = $location_id;

    $types .= "i";
}


/* =====================================================
   SẮP XẾP

   Sự kiện sắp tới lên trước.
   Sự kiện đã qua xuống dưới.
===================================================== */

$sql .= "
    ORDER BY

        CASE
            WHEN e.event_date >= CURDATE()
            THEN 0
            ELSE 1
        END ASC,

        CASE
            WHEN e.event_date >= CURDATE()
            THEN e.event_date
        END ASC,

        e.event_date DESC,

        e.event_id DESC
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

$events =
    $stmt->get_result();

$totalEvents =
    $events->num_rows;

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
    Sự kiện - Bản đồ số Văn hóa Du lịch
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


/* =====================================================
   HEADER
===================================================== */

.page-header {

    background:
        linear-gradient(
            135deg,
            #198754,
            #146c43
        );

    color: white;

    padding: 60px 0 75px;

}


.page-header h1 {

    font-weight: 800;

}


/* =====================================================
   SEARCH
===================================================== */

.search-box {

    background: white;

    border-radius: 16px;

    padding: 25px;

    margin-top: -40px;

    position: relative;

    z-index: 5;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,0.10);

}


/* =====================================================
   EVENT
===================================================== */

.event-card {

    border: none;

    border-radius: 16px;

    overflow: hidden;

    height: 100%;

    transition: 0.25s;

}


.event-card:hover {

    transform:
        translateY(-5px);

    box-shadow:
        0 10px 25px
        rgba(0,0,0,0.12);

}


.event-date-box {

    background: #e9f7ef;

    color: #198754;

    border-radius: 14px;

    text-align: center;

    padding: 15px 10px;

    min-width: 85px;

}


.event-day {

    font-size: 28px;

    font-weight: 800;

    line-height: 1;

}


.event-month {

    font-size: 14px;

    margin-top: 5px;

    font-weight: 600;

}


.event-description {

    color: #6c757d;

    display: -webkit-box;

    -webkit-line-clamp: 4;

    -webkit-box-orient: vertical;

    overflow: hidden;

}


.event-location {

    background: #f8f9fa;

    border-radius: 10px;

    padding: 12px;

}


.past-event {

    opacity: 0.78;

}


/* =====================================================
   FOOTER
===================================================== */

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


/* =====================================================
   MOBILE
===================================================== */

@media (
    max-width: 767px
) {

    .page-header {

        padding:
            45px 0
            65px;

    }

}

</style>

</head>


<body>

<?php

$currentPage = 'events';

require_once("includes/navbar.php");

?>


<!-- =====================================================
     HEADER
===================================================== -->

<section class="page-header">

<div class="container text-center">


<h1>

    🎉 Sự kiện & Hoạt động

</h1>


<p class="mb-0">

    Khám phá các sự kiện,
    hoạt động văn hóa và du lịch
    tại các địa điểm trên hệ thống

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
    action="events.php"
>


<div class="row g-3">


<div class="col-lg-6">


<input
    type="text"
    name="keyword"
    class="form-control"
    placeholder="🔎 Nhập tên sự kiện..."
    value="<?= htmlspecialchars(
        $keyword,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>


</div>



<div class="col-lg-3">


<select
    name="location_id"
    class="form-select"
>


<option value="0">

    Tất cả địa điểm

</option>


<?php if ($locationList): ?>


<?php while (
    $item =
        $locationList->fetch_assoc()
): ?>


<option
    value="<?= (int)$item['location_id'] ?>"

    <?=

    $location_id
    ===
    (int)$item['location_id']

    ? 'selected'
    : ''

    ?>
>

    <?= htmlspecialchars(
        $item['name'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>

</option>


<?php endwhile; ?>


<?php endif; ?>


</select>


</div>



<div class="col-lg-3">


<div class="d-flex gap-2">


<button
    type="submit"
    class="
        btn
        btn-success
        flex-grow-1
    "
>

    🔍 Tìm kiếm

</button>


<a
    href="events.php"
    class="
        btn
        btn-outline-secondary
    "
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
     DANH SÁCH SỰ KIỆN
===================================================== -->

<section class="py-5">


<div class="container">


<div
    class="
        d-flex
        justify-content-between
        flex-wrap
        align-items-center
        gap-2
        mb-4
    "
>


<div>


<h3 class="fw-bold mb-1">

    Danh sách sự kiện

</h3>


<div class="text-muted">

    Tìm thấy

    <strong>

        <?= $totalEvents ?>

    </strong>

    sự kiện

</div>


</div>


<a
    href="map.php"
    class="btn btn-outline-success"
>

    🗺️ Khám phá bản đồ

</a>


</div>



<div class="row g-4">


<?php if (
    $totalEvents > 0
): ?>


<?php while (
    $event =
        $events->fetch_assoc()
): ?>


<?php

$hasDate =
    !empty(
        $event['event_date']
    );

$isPast = false;


if ($hasDate) {

    $eventTimestamp =
        strtotime(
            $event['event_date']
        );

    $todayTimestamp =
        strtotime(
            date('Y-m-d')
        );

    $isPast =
        $eventTimestamp
        <
        $todayTimestamp;

}

?>


<div
    class="
        col-lg-6
    "
>


<div
    class="
        card
        event-card
        shadow-sm

        <?= $isPast
            ? 'past-event'
            : ''
        ?>
    "
>


<div class="card-body p-4">


<div class="d-flex gap-3">


<!-- NGÀY -->

<div>


<div class="event-date-box">


<?php if ($hasDate): ?>


<div class="event-day">

    <?= date(
        'd',
        strtotime(
            $event['event_date']
        )
    ) ?>

</div>


<div class="event-month">

    THÁNG

    <?= date(
        'm',
        strtotime(
            $event['event_date']
        )
    ) ?>

</div>


<?php else: ?>


<div style="font-size:30px;">

    📅

</div>


<div class="event-month">

    Chưa xác định

</div>


<?php endif; ?>


</div>


</div>



<!-- NỘI DUNG -->

<div class="flex-grow-1">


<div
    class="
        d-flex
        flex-wrap
        gap-2
        mb-2
    "
>


<?php if ($isPast): ?>


<span
    class="
        badge
        bg-secondary
    "
>

    Đã diễn ra

</span>


<?php elseif ($hasDate): ?>


<span
    class="
        badge
        bg-success
    "
>

    Sắp diễn ra

</span>


<?php else: ?>


<span
    class="
        badge
        bg-warning
        text-dark
    "
>

    Chưa xác định ngày

</span>


<?php endif; ?>


<?php if (
    !empty(
        $event['category_name']
    )
): ?>


<span
    class="
        badge
        bg-light
        text-success
        border
    "
>

    <?= htmlspecialchars(
        $event['category_name'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>

</span>


<?php endif; ?>


</div>



<h4 class="fw-bold">

    <?= htmlspecialchars(
        $event['event_name'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>

</h4>


<?php if ($hasDate): ?>


<div
    class="
        text-success
        fw-semibold
        mb-3
    "
>

    📅 Ngày tổ chức:

    <?= date(
        'd/m/Y',
        strtotime(
            $event['event_date']
        )
    ) ?>

</div>


<?php endif; ?>


</div>


</div>



<?php if (
    !empty(
        $event['description']
    )
): ?>


<p
    class="
        event-description
        mt-3
    "
>

    <?= htmlspecialchars(
        $event['description'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>

</p>


<?php endif; ?>



<!-- ĐỊA ĐIỂM -->

<div
    class="
        event-location
        mt-3
    "
>


<div class="fw-bold">

    📍

    <?= htmlspecialchars(
        $event['location_name'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>

</div>


<?php if (
    !empty(
        $event['address']
    )
): ?>


<div
    class="
        text-muted
        small
        mt-1
    "
>

    <?= htmlspecialchars(
        $event['address'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>

</div>


<?php endif; ?>


</div>



<div
    class="
        d-flex
        flex-wrap
        gap-2
        mt-3
    "
>


<a
    href="location_detail.php?id=<?= (int)$event['location_id'] ?>"
    class="
        btn
        btn-outline-success
        btn-sm
    "
>

    👁 Xem địa điểm

</a>


<a
    href="map.php?location=<?= (int)$event['location_id'] ?>"
    class="
        btn
        btn-outline-secondary
        btn-sm
    "
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


<div style="font-size:60px;">

    📅

</div>


<h4 class="mt-3">

    Không tìm thấy sự kiện

</h4>


<p class="text-muted">

    Hiện chưa có sự kiện
    phù hợp với điều kiện tìm kiếm.

</p>


<a
    href="events.php"
    class="btn btn-success"
>

    Xem tất cả sự kiện

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
        text-center
        py-4
    "
>


<div class="fw-bold text-white">

    🗺️ Bản đồ số Văn hóa - Du lịch

</div>


<div class="small mt-2">

    Khám phá văn hóa,
    du lịch và điểm đến địa phương

</div>


</div>


</footer>



<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>