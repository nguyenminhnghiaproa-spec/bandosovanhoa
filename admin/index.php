<?php

require_once("auth.php");
require_once("../config/database.php");


/* =====================================================
   HÀM ĐẾM DỮ LIỆU
===================================================== */

function getTotal($conn, $table)
{
    $allowedTables = [
        'locations',
        'categories',
        'images',
        'events'
    ];

    if (!in_array($table, $allowedTables, true)) {
        return 0;
    }

    $result = $conn->query(
        "SELECT COUNT(*) AS total FROM {$table}"
    );

    if (!$result) {
        return 0;
    }

    $row = $result->fetch_assoc();

    return (int)($row['total'] ?? 0);
}


/* =====================================================
   THỐNG KÊ TỔNG
===================================================== */

$totalLocations =
    getTotal($conn, 'locations');

$totalCategories =
    getTotal($conn, 'categories');

$totalImages =
    getTotal($conn, 'images');

$totalEvents =
    getTotal($conn, 'events');


/* =====================================================
   SỰ KIỆN SẮP DIỄN RA
===================================================== */

$upcomingEvents = 0;

$sqlUpcoming = "
    SELECT COUNT(*) AS total
    FROM events
    WHERE event_date IS NOT NULL
      AND event_date >= CURDATE()
";

$resultUpcoming =
    $conn->query($sqlUpcoming);

if ($resultUpcoming) {

    $rowUpcoming =
        $resultUpcoming->fetch_assoc();

    $upcomingEvents =
        (int)($rowUpcoming['total'] ?? 0);
}


/* =====================================================
   SỰ KIỆN ĐÃ DIỄN RA
===================================================== */

$pastEvents = 0;

$sqlPast = "
    SELECT COUNT(*) AS total
    FROM events
    WHERE event_date IS NOT NULL
      AND event_date < CURDATE()
";

$resultPast =
    $conn->query($sqlPast);

if ($resultPast) {

    $rowPast =
        $resultPast->fetch_assoc();

    $pastEvents =
        (int)($rowPast['total'] ?? 0);
}


/* =====================================================
   SỰ KIỆN CHƯA XÁC ĐỊNH NGÀY
===================================================== */

$undatedEvents = 0;

$sqlUndated = "
    SELECT COUNT(*) AS total
    FROM events
    WHERE event_date IS NULL
";

$resultUndated =
    $conn->query($sqlUndated);

if ($resultUndated) {

    $rowUndated =
        $resultUndated->fetch_assoc();

    $undatedEvents =
        (int)($rowUndated['total'] ?? 0);
}


/* =====================================================
   THỐNG KÊ ĐỊA ĐIỂM THEO DANH MỤC
===================================================== */

$categoryLabels = [];
$categoryTotals = [];

$sqlCategoryStats = "
    SELECT
        c.category_id,
        c.category_name,
        COUNT(l.location_id) AS total
    FROM categories c

    LEFT JOIN locations l
        ON c.category_id = l.category_id
        AND l.status = 'active'

    GROUP BY
        c.category_id,
        c.category_name

    ORDER BY
        total DESC,
        c.category_name ASC
";

$resultCategoryStats =
    $conn->query($sqlCategoryStats);

if ($resultCategoryStats) {

    while (
        $category =
            $resultCategoryStats->fetch_assoc()
    ) {

        $categoryLabels[] =
            $category['category_name'];

        $categoryTotals[] =
            (int)$category['total'];
    }
}


/* =====================================================
   LẤY 5 ĐỊA ĐIỂM MỚI NHẤT
===================================================== */

$sqlLatest = "
    SELECT
        l.location_id,
        l.name,
        l.address,
        l.status,
        c.category_name

    FROM locations l

    LEFT JOIN categories c
        ON l.category_id = c.category_id

    ORDER BY
        l.location_id DESC

    LIMIT 5
";

$latestLocations =
    $conn->query($sqlLatest);

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
    Dashboard - Quản trị bản đồ số
</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<style>

body {

    background: #f4f6f8;

    min-height: 100vh;

}


/* =====================================================
   NAVBAR
===================================================== */

.navbar-brand {

    font-weight: 700;

}


/* =====================================================
   TIÊU ĐỀ
===================================================== */

.page-title {

    font-weight: 700;

}


/* =====================================================
   CARD THỐNG KÊ
===================================================== */

.stat-card {

    border: none;

    border-radius: 15px;

    transition: 0.2s;

    height: 100%;

}


.stat-card:hover {

    transform:
        translateY(-4px);

    box-shadow:
        0 8px 20px
        rgba(0, 0, 0, 0.10);

}


.stat-icon {

    width: 60px;

    height: 60px;

    border-radius: 12px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 30px;

    background: #f1f3f5;

}


.stat-number {

    font-size: 32px;

    font-weight: 700;

}


.stat-label {

    color: #6c757d;

    margin-bottom: 5px;

}


/* =====================================================
   EVENT CARD
===================================================== */

.event-stat-card {

    border: none;

    border-radius: 15px;

    height: 100%;

}


.event-stat-icon {

    width: 62px;

    height: 62px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 14px;

    font-size: 30px;

    background: #f1f3f5;

}


/* =====================================================
   BIỂU ĐỒ
===================================================== */

.chart-card {

    border: none;

    border-radius: 15px;

    overflow: hidden;

}


.chart-container {

    position: relative;

    height: 360px;

}


/* =====================================================
   TABLE
===================================================== */

.table-card {

    border: none;

    border-radius: 15px;

    overflow: hidden;

}


.table thead th {

    background: #f8f9fa;

    white-space: nowrap;

}


/* =====================================================
   QUICK ACTION
===================================================== */

.quick-action {

    text-decoration: none;

    color: inherit;

}


.quick-action-card {

    border: none;

    border-radius: 12px;

    transition: 0.2s;

    height: 100%;

}


.quick-action-card:hover {

    transform:
        translateY(-3px);

    box-shadow:
        0 6px 16px
        rgba(0, 0, 0, 0.10);

}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 767px) {

    .chart-container {

        height: 300px;

    }

}

</style>

</head>


<body>

<?php

$currentAdminPage = 'dashboard';

require_once(
    "includes/navbar.php"
);

?>


<!-- =====================================================
     NỘI DUNG
===================================================== -->

<div class="container-fluid p-4">


<!-- =====================================================
     TIÊU ĐỀ
===================================================== -->

<div class="mb-4">

<h2 class="page-title">
    Hệ Thống
</h2>

<p class="text-muted mb-0">

    Tổng quan hệ thống bản đồ số
    văn hóa - du lịch địa phương

</p>

</div>



<!-- =====================================================
     4 THẺ THỐNG KÊ CHÍNH
===================================================== -->

<div class="row g-4 mb-4">


<!-- ĐỊA ĐIỂM -->

<div class="col-xl-3 col-md-6">

<div class="card stat-card shadow-sm">

<div class="card-body">

<div
    class="
        d-flex
        justify-content-between
        align-items-center
    "
>

<div>

<div class="stat-label">
    Tổng địa điểm
</div>

<div class="stat-number">

    <?= $totalLocations ?>

</div>

</div>


<div class="stat-icon">
    📍
</div>

</div>


<hr>


<a
    href="locations/index.php"
    class="
        text-success
        text-decoration-none
    "
>
    Quản lý địa điểm →
</a>

</div>

</div>

</div>



<!-- DANH MỤC -->

<div class="col-xl-3 col-md-6">

<div class="card stat-card shadow-sm">

<div class="card-body">

<div
    class="
        d-flex
        justify-content-between
        align-items-center
    "
>

<div>

<div class="stat-label">
    Danh mục
</div>

<div class="stat-number">

    <?= $totalCategories ?>

</div>

</div>


<div class="stat-icon">
    🏷️
</div>

</div>


<hr>


<a
    href="categories/index.php"
    class="
        text-success
        text-decoration-none
    "
>
    Quản lý danh mục →
</a>

</div>

</div>

</div>



<!-- HÌNH ẢNH -->

<div class="col-xl-3 col-md-6">

<div class="card stat-card shadow-sm">

<div class="card-body">

<div
    class="
        d-flex
        justify-content-between
        align-items-center
    "
>

<div>

<div class="stat-label">
    Hình ảnh
</div>

<div class="stat-number">

    <?= $totalImages ?>

</div>

</div>


<div class="stat-icon">
    📷
</div>

</div>


<hr>


<span class="text-muted">
    Hình ảnh địa điểm
</span>

</div>

</div>

</div>



<!-- SỰ KIỆN -->

<div class="col-xl-3 col-md-6">

<div class="card stat-card shadow-sm">

<div class="card-body">

<div
    class="
        d-flex
        justify-content-between
        align-items-center
    "
>

<div>

<div class="stat-label">
    Sự kiện
</div>

<div class="stat-number">

    <?= $totalEvents ?>

</div>

</div>


<div class="stat-icon">
    🎉
</div>

</div>


<hr>


<a
    href="events/index.php"
    class="
        text-success
        text-decoration-none
    "
>
    Quản lý sự kiện →
</a>

</div>

</div>

</div>


</div>



<!-- =====================================================
     THỐNG KÊ SỰ KIỆN
===================================================== -->

<div class="row g-4 mb-4">


<div class="col-lg-4">

<div
    class="
        card
        event-stat-card
        shadow-sm
    "
>

<div class="card-body p-4">

<div
    class="
        d-flex
        align-items-center
        gap-3
    "
>

<div class="event-stat-icon">
    📅
</div>


<div>

<div class="text-muted">
    Sự kiện sắp diễn ra
</div>

<div
    class="
        fs-2
        fw-bold
        text-success
    "
>

    <?= $upcomingEvents ?>

</div>

</div>

</div>

</div>

</div>

</div>



<div class="col-lg-4">

<div
    class="
        card
        event-stat-card
        shadow-sm
    "
>

<div class="card-body p-4">

<div
    class="
        d-flex
        align-items-center
        gap-3
    "
>

<div class="event-stat-icon">
    📚
</div>


<div>

<div class="text-muted">
    Sự kiện đã diễn ra
</div>

<div
    class="
        fs-2
        fw-bold
        text-secondary
    "
>

    <?= $pastEvents ?>

</div>

</div>

</div>

</div>

</div>

</div>



<div class="col-lg-4">

<div
    class="
        card
        event-stat-card
        shadow-sm
    "
>

<div class="card-body p-4">

<div
    class="
        d-flex
        align-items-center
        gap-3
    "
>

<div class="event-stat-icon">
    🕒
</div>


<div>

<div class="text-muted">
    Chưa xác định ngày
</div>

<div
    class="
        fs-2
        fw-bold
        text-warning
    "
>

    <?= $undatedEvents ?>

</div>

</div>

</div>

</div>

</div>

</div>


</div>



<!-- =====================================================
     BIỂU ĐỒ
===================================================== -->

<div
    class="
        card
        chart-card
        shadow-sm
        mb-4
    "
>

<div
    class="
        card-header
        bg-white
        py-3
    "
>

<h5 class="fw-bold mb-0">

    📊 Thống kê địa điểm theo danh mục

</h5>

</div>


<div class="card-body">

<?php if (
    count($categoryLabels) > 0
): ?>

<div class="chart-container">

<canvas
    id="categoryChart"
></canvas>

</div>

<?php else: ?>

<div
    class="
        text-center
        text-muted
        py-5
    "
>
    Chưa có dữ liệu danh mục để thống kê.
</div>

<?php endif; ?>

</div>

</div>



<!-- =====================================================
     THAO TÁC NHANH
===================================================== -->

<div class="mb-4">


<h5 class="mb-3">

    ⚡ Thao tác nhanh

</h5>


<div class="row g-3">


<!-- THÊM ĐỊA ĐIỂM -->

<div class="col-md-4">

<a
    href="locations/add.php"
    class="quick-action"
>

<div
    class="
        card
        quick-action-card
        shadow-sm
    "
>

<div class="card-body">

<h5>
    ➕ Thêm địa điểm
</h5>

<small class="text-muted">

    Thêm địa điểm mới
    lên bản đồ.

</small>

</div>

</div>

</a>

</div>



<!-- THÊM SỰ KIỆN -->

<div class="col-md-4">

<a
    href="events/add.php"
    class="quick-action"
>

<div
    class="
        card
        quick-action-card
        shadow-sm
    "
>

<div class="card-body">

<h5>
    🎉 Thêm sự kiện
</h5>

<small class="text-muted">

    Thêm hoạt động hoặc
    sự kiện mới.

</small>

</div>

</div>

</a>

</div>



<!-- XEM BẢN ĐỒ -->

<div class="col-md-4">

<a
    href="../map.php"
    target="_blank"
    class="quick-action"
>

<div
    class="
        card
        quick-action-card
        shadow-sm
    "
>

<div class="card-body">

<h5>
    🗺️ Xem bản đồ
</h5>

<small class="text-muted">

    Kiểm tra bản đồ
    dành cho người dùng.

</small>

</div>

</div>

</a>

</div>


</div>

</div>



<!-- =====================================================
     ĐỊA ĐIỂM MỚI NHẤT
===================================================== -->

<div
    class="
        card
        table-card
        shadow-sm
    "
>


<div
    class="
        card-header
        bg-white
        py-3
        d-flex
        justify-content-between
        align-items-center
    "
>

<h5 class="mb-0">

    📍 Địa điểm mới nhất

</h5>


<a
    href="locations/index.php"
    class="
        btn
        btn-outline-success
        btn-sm
    "
>
    Xem tất cả
</a>

</div>



<div class="card-body">


<div class="table-responsive">


<table
    class="
        table
        table-hover
        align-middle
    "
>


<thead>

<tr>

<th>
    STT
</th>

<th>
    Tên địa điểm
</th>

<th>
    Loại
</th>

<th>
    Địa chỉ
</th>

<th>
    Trạng thái
</th>

<th>
    Thao tác
</th>

</tr>

</thead>


<tbody>


<?php

if (
    $latestLocations
    &&
    $latestLocations->num_rows > 0
):

    $stt = 1;

?>


<?php while (
    $location =
        $latestLocations->fetch_assoc()
): ?>


<tr>


<td>

    <?= $stt++ ?>

</td>


<td>

<strong>

    <?= htmlspecialchars(
        $location['name'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>

</strong>

</td>


<td>

    <?= htmlspecialchars(
        $location['category_name']
        ?? 'Chưa phân loại',
        ENT_QUOTES,
        'UTF-8'
    ) ?>

</td>


<td>

    <?= htmlspecialchars(
        $location['address']
        ?? '',
        ENT_QUOTES,
        'UTF-8'
    ) ?>

</td>


<td>

<?php if (
    $location['status']
    === 'active'
): ?>

<span
    class="
        badge
        bg-success
    "
>
    Hoạt động
</span>

<?php else: ?>

<span
    class="
        badge
        bg-secondary
    "
>
    Tạm ẩn
</span>

<?php endif; ?>

</td>


<td>

<div
    class="
        d-flex
        gap-1
        flex-wrap
    "
>

<a
    href="../location_detail.php?id=<?= (int)$location['location_id'] ?>"
    target="_blank"
    class="
        btn
        btn-info
        btn-sm
    "
>
    👁 Xem
</a>


<a
    href="locations/edit.php?id=<?= (int)$location['location_id'] ?>"
    class="
        btn
        btn-warning
        btn-sm
    "
>
    ✏️ Sửa
</a>

</div>

</td>


</tr>


<?php endwhile; ?>


<?php else: ?>


<tr>

<td
    colspan="6"
    class="
        text-center
        text-muted
        py-4
    "
>

    Chưa có địa điểm nào.

</td>

</tr>


<?php endif; ?>


</tbody>

</table>


</div>

</div>

</div>


</div>

<!-- =====================================================
     MODAL XÁC NHẬN ĐĂNG XUẤT
===================================================== -->

<div
    class="modal fade"
    id="logoutModal"
    tabindex="-1"
    aria-labelledby="logoutModalTitle"
    aria-hidden="true"
>

    <div
        class="
            modal-dialog
            modal-dialog-centered
        "
    >

        <div
            class="
                modal-content
                border-0
                shadow
                rounded-4
            "
        >

            <div
                class="
                    modal-body
                    text-center
                    p-4
                "
            >

                <div
                    class="mb-3"
                    style="font-size:55px;"
                >
                    🚪
                </div>


                <h4
                    class="fw-bold mb-2"
                    id="logoutModalTitle"
                >
                    Xác nhận đăng xuất
                </h4>


                <p class="text-muted mb-4">

                    Bạn có chắc chắn muốn
                    đăng xuất khỏi trang quản trị không?

                </p>


                <div
                    class="
                        d-flex
                        justify-content-center
                        gap-2
                    "
                >

                    <button
                        type="button"
                        class="btn btn-light px-4"
                        data-bs-dismiss="modal"
                    >
                        Hủy
                    </button>


                    <a
                        href="logout.php"
                        class="btn btn-danger px-4"
                    >
                        🚪 Đăng xuất
                    </a>

                </div>

            </div>

        </div>

    </div>

</div>



<!-- =====================================================
     BOOTSTRAP
===================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>



<!-- =====================================================
     CHART.JS
===================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/chart.js"
></script>


<script>

/* =====================================================
   DỮ LIỆU PHP -> JAVASCRIPT
===================================================== */

const categoryLabels =
    <?= json_encode(
        $categoryLabels,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ) ?>;


const categoryTotals =
    <?= json_encode(
        $categoryTotals
    ) ?>;


/* =====================================================
   VẼ BIỂU ĐỒ
===================================================== */

const chartElement =
    document.getElementById(
        "categoryChart"
    );


if (
    chartElement
    &&
    categoryLabels.length > 0
) {

    new Chart(
        chartElement,
        {

            type: "bar",

            data: {

                labels:
                    categoryLabels,

                datasets: [

                    {

                        label:
                            "Số địa điểm",

                        data:
                            categoryTotals,

                        backgroundColor: [
                            "#198754",
                            "#0d6efd",
                            "#ffc107",
                            "#dc3545",
                            "#6f42c1",
                            "#fd7e14",
                            "#20c997",
                            "#0dcaf0"
                        ],

                        borderRadius:
                            7,

                        borderSkipped:
                            false

                    }

                ]

            },


            options: {

                responsive:
                    true,

                maintainAspectRatio:
                    false,


                plugins: {

                    legend: {

                        display:
                            false

                    },

                    tooltip: {

                        callbacks: {

                            label:
                                function(context) {

                                    return (
                                        "Số địa điểm: "
                                        +
                                        context.raw
                                    );

                                }

                        }

                    }

                },


                scales: {

                    x: {

                        grid: {

                            display:
                                false

                        }

                    },


                    y: {

                        beginAtZero:
                            true,

                        ticks: {

                            precision:
                                0,

                            stepSize:
                                1

                        }

                    }

                }

            }

        }
    );

}

</script>


</body>

</html>