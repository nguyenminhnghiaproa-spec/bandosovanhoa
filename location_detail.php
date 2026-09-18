<?php

include("config/database.php");
include("config/app.php");

/* =====================================================
   KIỂM TRA ID
===================================================== */

if (
    !isset($_GET['id'])
    ||
    !is_numeric($_GET['id'])
) {

    die("Địa điểm không hợp lệ!");

}


$location_id =
    (int)$_GET['id'];


/* =====================================================
   LẤY THÔNG TIN ĐỊA ĐIỂM
===================================================== */

$sql = "
    SELECT
        l.*,
        c.category_name
    FROM locations l
    LEFT JOIN categories c
        ON l.category_id = c.category_id
    WHERE l.location_id = ?
    LIMIT 1
";


$stmt =
    $conn->prepare($sql);

$stmt->bind_param(
    "i",
    $location_id
);

$stmt->execute();

$result =
    $stmt->get_result();


if ($result->num_rows === 0) {

    die("Không tìm thấy địa điểm!");

}


$location =
    $result->fetch_assoc();


/* =====================================================
   URL QR
===================================================== */

$qrUrl =
    location_url($location_id);


/* =====================================================
   LẤY HÌNH ẢNH
===================================================== */

$imageSql = "
    SELECT
        image_id,
        image_url,
        caption
    FROM images
    WHERE location_id = ?
    ORDER BY image_id DESC
";


$imageStmt =
    $conn->prepare($imageSql);

$imageStmt->bind_param(
    "i",
    $location_id
);

$imageStmt->execute();

$images =
    $imageStmt->get_result();


/* =====================================================
   LẤY SỰ KIỆN
===================================================== */

$eventSql = "
    SELECT
        event_id,
        event_name,
        event_date,
        description
    FROM events
    WHERE location_id = ?
    ORDER BY event_date ASC
";


$eventStmt =
    $conn->prepare($eventSql);

$eventStmt->bind_param(
    "i",
    $location_id
);

$eventStmt->execute();

$events =
    $eventStmt->get_result();


/* =====================================================
   TỌA ĐỘ
===================================================== */

$latitude =
    (float)($location['latitude'] ?? 0);

$longitude =
    (float)($location['longitude'] ?? 0);

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
    <?= htmlspecialchars(
        $location['name'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>
</title>


<!-- BOOTSTRAP -->

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<!-- LEAFLET -->

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>


<style>

/* =====================================================
   CHUNG
===================================================== */

body {

    background: #f5f7f8;

    color: #212529;

}


.navbar-brand {

    font-weight: 700;

}


.page-container {

    max-width: 1200px;

}


/* =====================================================
   HEADER ĐỊA ĐIỂM
===================================================== */

.location-header {

    background:
        linear-gradient(
            135deg,
            #198754,
            #157347
        );

    color: white;

    border-radius: 18px;

    padding: 35px;

    margin-bottom: 25px;

}


.location-title {

    font-size: 34px;

    font-weight: 800;

}


.category-badge {

    background: white;

    color: #198754;

    padding: 7px 14px;

    border-radius: 20px;

    display: inline-block;

    font-weight: 600;

}


/* =====================================================
   CARD
===================================================== */

.info-card {

    border: none;

    border-radius: 16px;

    box-shadow:
        0 4px 16px
        rgba(0,0,0,0.07);

    margin-bottom: 25px;

}


.section-title {

    font-weight: 700;

    margin-bottom: 20px;

}


/* =====================================================
   THÔNG TIN
===================================================== */

.info-item {

    background: #f8f9fa;

    border-radius: 12px;

    padding: 15px;

    height: 100%;

}


.info-label {

    color: #6c757d;

    font-size: 14px;

    margin-bottom: 4px;

}


.info-value {

    font-weight: 600;

}


/* =====================================================
   HÌNH ẢNH
===================================================== */

.gallery-image {

    width: 100%;

    height: 240px;

    object-fit: cover;

    border-radius: 12px 12px 0 0;

}


.image-card {

    border: none;

    border-radius: 12px;

    overflow: hidden;

    height: 100%;

    box-shadow:
        0 3px 12px
        rgba(0,0,0,0.08);

}


/* =====================================================
   SỰ KIỆN
===================================================== */

.event-card {

    border: 1px solid #e9ecef;

    border-radius: 14px;

    height: 100%;

}


.event-date {

    color: #198754;

    font-weight: 600;

}


/* =====================================================
   MAP
===================================================== */

#map {

    width: 100%;

    height: 420px;

    border-radius: 14px;

    position: relative;

    z-index: 1;

}


/* =====================================================
   QR
===================================================== */

.qr-box {

    background: #f8f9fa;

    border-radius: 14px;

    padding: 25px;

    text-align: center;

}


/* =====================================================
   MOBILE
===================================================== */

@media (
    max-width: 767px
) {

    .location-header {

        padding: 25px 20px;

    }


    .location-title {

        font-size: 27px;

    }


    #map {

        height: 350px;

    }

}

</style>

</head>


<body>

<?php

$currentPage = 'locations';

require_once("includes/navbar.php");

?>


<!-- =====================================================
     NỘI DUNG
===================================================== -->

<div
    class="
        container
        page-container
        py-4
    "
>


<!-- =====================================================
     HEADER
===================================================== -->

<div class="location-header">


    <div class="mb-3">

        <span class="category-badge">

            🏷️

            <?= htmlspecialchars(
                $location['category_name']
                ?? 'Chưa phân loại',
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </span>

    </div>


    <h1 class="location-title">

        <?= htmlspecialchars(
            $location['name'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>

    </h1>


    <?php if (
        !empty($location['address'])
    ): ?>

        <div class="mt-3">

            📍

            <?= htmlspecialchars(
                $location['address'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </div>

    <?php endif; ?>


</div>



<!-- =====================================================
     THÔNG TIN
===================================================== -->

<div class="card info-card">

<div class="card-body p-4">


<h4 class="section-title">

    ℹ️ Thông tin địa điểm

</h4>


<div class="row g-3">


    <!-- ĐỊA CHỈ -->

    <div class="col-md-6">

        <div class="info-item">

            <div class="info-label">

                📍 Địa chỉ

            </div>


            <div class="info-value">

                <?= htmlspecialchars(
                    $location['address']
                    ?: 'Chưa cập nhật',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        </div>

    </div>



    <!-- ĐIỆN THOẠI -->

    <div class="col-md-3">

        <div class="info-item">

            <div class="info-label">

                ☎ Điện thoại

            </div>


            <div class="info-value">

                <?= htmlspecialchars(
                    $location['phone']
                    ?: 'Chưa cập nhật',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        </div>

    </div>



    <!-- GIỜ MỞ CỬA -->

    <div class="col-md-3">

        <div class="info-item">

            <div class="info-label">

                🕐 Giờ hoạt động

            </div>


            <div class="info-value">

                <?= htmlspecialchars(
                    $location['opening_hours']
                    ?: 'Chưa cập nhật',
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        </div>

    </div>


</div>


</div>

</div>



<!-- =====================================================
     GIỚI THIỆU
===================================================== -->

<div class="card info-card">

<div class="card-body p-4">


<h4 class="section-title">

    📖 Giới thiệu địa điểm

</h4>


<?php if (
    !empty($location['description'])
): ?>


    <div
        style="
            white-space:pre-line;
            line-height:1.8;
        "
    ><?= htmlspecialchars(
        $location['description'],
        ENT_QUOTES,
        'UTF-8'
    ) ?></div>


<?php else: ?>


    <div class="text-muted">

        Nội dung giới thiệu
        đang được cập nhật.

    </div>


<?php endif; ?>


</div>

</div>



<!-- =====================================================
     HÌNH ẢNH
===================================================== -->

<?php if (
    $images->num_rows > 0
): ?>


<div class="card info-card">

<div class="card-body p-4">


<h4 class="section-title">

    📷 Hình ảnh địa điểm

</h4>


<div class="row g-4">


<?php while (
    $image =
        $images->fetch_assoc()
): ?>


<div
    class="
        col-lg-4
        col-md-6
    "
>


<div class="card image-card">


<img
    src="<?= htmlspecialchars(
        $image['image_url'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    class="gallery-image"
    alt="<?= htmlspecialchars(
        $image['caption']
        ?? $location['name'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>


<?php if (
    !empty($image['caption'])
): ?>


<div class="card-body">

    <?= htmlspecialchars(
        $image['caption'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>

</div>


<?php endif; ?>


</div>

</div>


<?php endwhile; ?>


</div>


</div>

</div>


<?php endif; ?>



<!-- =====================================================
     SỰ KIỆN
===================================================== -->

<?php if (
    $events->num_rows > 0
): ?>


<div class="card info-card">

<div class="card-body p-4">


<h4 class="section-title">

    🎉 Sự kiện và hoạt động

</h4>


<div class="row g-3">


<?php while (
    $event =
        $events->fetch_assoc()
): ?>


<div class="col-md-6">


<div class="card event-card">

<div class="card-body">


<h5
    class="
        fw-bold
        text-success
    "
>

    <?= htmlspecialchars(
        $event['event_name'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>

</h5>



<?php if (
    !empty($event['event_date'])
): ?>


<div class="event-date mb-3">

    📅

    <?= date(
        "d/m/Y",
        strtotime(
            $event['event_date']
        )
    ) ?>

</div>


<?php endif; ?>



<?php if (
    !empty($event['description'])
): ?>


<div
    style="
        white-space:pre-line;
    "
><?= htmlspecialchars(
    $event['description'],
    ENT_QUOTES,
    'UTF-8'
) ?></div>


<?php endif; ?>


</div>

</div>

</div>


<?php endwhile; ?>


</div>


</div>

</div>


<?php endif; ?>



<!-- =====================================================
     BẢN ĐỒ
===================================================== -->

<div class="card info-card">

<div class="card-body p-4">


<div
    class="
        d-flex
        flex-wrap
        justify-content-between
        align-items-center
        gap-2
        mb-3
    "
>


<h4 class="section-title mb-0">

    🗺️ Vị trí trên bản đồ

</h4>


<a
    target="_blank"
    rel="noopener noreferrer"
    href="https://www.google.com/maps/dir/?api=1&destination=<?= urlencode(
        $latitude . ',' . $longitude
    ) ?>"
    class="btn btn-primary"
>

    🚗 Chỉ đường

</a>


</div>


<div id="map"></div>


</div>

</div>



<!-- =====================================================
     QR + CHIA SẺ
===================================================== -->

<div class="card info-card">

<div class="card-body p-4">


<h4 class="section-title">

    📱 Chia sẻ địa điểm

</h4>


<div class="row align-items-center g-4">


<div class="col-md-4">


<div class="qr-box">

    <div
        id="publicQr"
        class="
            d-flex
            justify-content-center
        "
    ></div>

    <div
        class="
            text-muted
            small
            mt-3
        "
    >

        Quét mã để mở
        thông tin địa điểm

    </div>

</div>


</div>



<div class="col-md-8">


<h5 class="fw-bold">

    <?= htmlspecialchars(
        $location['name'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>

</h5>


<p class="text-muted">

    Bạn có thể quét mã QR
    hoặc chia sẻ địa điểm này
    cho người khác.

</p>


<div
    class="
        d-flex
        flex-wrap
        gap-2
    "
>


<button
    type="button"
    id="shareButton"
    class="btn btn-success"
>

    📤 Chia sẻ địa điểm

</button>


<button
    type="button"
    id="copyButton"
    class="
        btn
        btn-outline-success
    "
>

    🔗 Sao chép liên kết

</button>


<a
    href="map.php?location=<?= (int)$location_id ?>"
    class="
        btn
        btn-outline-secondary
    "
>
    🗺️ Xem trên bản đồ
</a>


</div>


<div
    id="copyMessage"
    class="
        text-success
        mt-3
        d-none
    "
>

    ✅ Đã sao chép liên kết.

</div>


</div>


</div>


</div>

</div>


</div>



<!-- =====================================================
     LEAFLET
===================================================== -->

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
></script>


<!-- QR CODE -->

<script
    src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"
></script>


<!-- BOOTSTRAP -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>



<script>

/* =====================================================
   BẢN ĐỒ
===================================================== */

const latitude =
    <?= json_encode($latitude) ?>;

const longitude =
    <?= json_encode($longitude) ?>;


const locationName =
    <?= json_encode(
        $location['name'],
        JSON_UNESCAPED_UNICODE
    ) ?>;


const map =
    L.map("map").setView(
        [
            latitude,
            longitude
        ],
        16
    );


L.tileLayer(
    "https://tile.openstreetmap.org/{z}/{x}/{y}.png",
    {
        maxZoom: 19,

        attribution:
            "&copy; OpenStreetMap contributors"
    }
).addTo(map);


L.marker(
    [
        latitude,
        longitude
    ]
)
.addTo(map)
.bindPopup(
    locationName
)
.openPopup();



/* =====================================================
   QR CODE
===================================================== */

const qrUrl =
    <?= json_encode(
        $qrUrl,
        JSON_UNESCAPED_SLASHES
        |
        JSON_UNESCAPED_UNICODE
    ) ?>;


new QRCode(
    document.getElementById(
        "publicQr"
    ),
    {
        text: qrUrl,

        width: 200,

        height: 200
    }
);



/* =====================================================
   CHIA SẺ
===================================================== */

document
.getElementById(
    "shareButton"
)
.addEventListener(
    "click",
    async function () {

        if (
            navigator.share
        ) {

            try {

                await navigator.share(
                    {
                        title:
                            locationName,

                        text:
                            "Xem thông tin địa điểm: "
                            + locationName,

                        url:
                            qrUrl
                    }
                );

            } catch (error) {

                /* Người dùng đóng cửa sổ chia sẻ */

            }

        } else {

            try {

                await navigator.clipboard.writeText(
                    qrUrl
                );

                showCopyMessage();

            } catch (error) {

                alert(
                    "Không thể chia sẻ liên kết."
                );

            }

        }

    }
);



/* =====================================================
   COPY LINK
===================================================== */

document
.getElementById(
    "copyButton"
)
.addEventListener(
    "click",
    async function () {

        try {

            await navigator.clipboard.writeText(
                qrUrl
            );

            showCopyMessage();

        } catch (error) {

            alert(
                "Không thể sao chép liên kết."
            );

        }

    }
);



function showCopyMessage()
{

    const message =
        document.getElementById(
            "copyMessage"
        );


    message.classList.remove(
        "d-none"
    );


    setTimeout(
        function () {

            message.classList.add(
                "d-none"
            );

        },
        2500
    );

}


/* =====================================================
   FIX KÍCH THƯỚC MAP
===================================================== */

window.addEventListener(
    "load",
    function () {

        setTimeout(
            function () {

                map.invalidateSize();

            },
            200
        );

    }
);

</script>


</body>

</html>