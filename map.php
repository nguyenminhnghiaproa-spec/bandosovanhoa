<?php

include("config/database.php");
include("config/app.php");


/* =====================================================
   LẤY DANH SÁCH ĐỊA ĐIỂM
===================================================== */

$sql = "
    SELECT
        l.location_id,
        l.category_id,
        l.name,
        l.address,
        l.latitude,
        l.longitude,
        l.description,
        c.category_name
    FROM locations l
    LEFT JOIN categories c
        ON l.category_id = c.category_id
    WHERE l.status = 'active'
";

$result = $conn->query($sql);


/* =====================================================
   KIỂM TRA QUERY
===================================================== */

if (!$result) {

    die(
        "Lỗi truy vấn dữ liệu: "
        . $conn->error
    );

}


/* =====================================================
   LẤY DANH MỤC
===================================================== */

$categorySql = "
    SELECT
        category_id,
        category_name
    FROM categories
    ORDER BY category_name ASC
";

$categoryResult =
    $conn->query($categorySql);


if (!$categoryResult) {

    die(
        "Lỗi truy vấn danh mục: "
        . $conn->error
    );

}

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
        Bản đồ số Văn hóa - Du lịch
    </title>


    <!-- ==========================================
         LEAFLET CSS
    =========================================== -->

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >


    <!-- ==========================================
         BOOTSTRAP
    =========================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <style>

        /* ==========================================
           BODY
        ========================================== */

        html,
        body {

            margin: 0;

            padding: 0;

            width: 100%;

            min-height: 100%;

            background: #f5f5f5;

        }


        /* ==========================================
           NAVBAR
        ========================================== */

        .navbar-brand {

            font-weight: bold;

        }


        /* ==========================================
           THANH TÌM KIẾM
        ========================================== */

        .search-area {

            background: white;

            padding: 12px 15px;

            border-bottom: 1px solid #ddd;

            box-shadow:
                0 2px 5px rgba(0, 0, 0, 0.08);

            position: relative;

            z-index: 1000;

        }


        #searchInput,
        #categoryFilter,
        #searchButton,
        #resetButton {

            height: 42px;

        }


        /* ==========================================
           BẢN ĐỒ
        ========================================== */

        #map {

            width: 100%;

            height: calc(100vh - 123px);

            min-height: 450px;

            position: relative;

            z-index: 1;

        }


        /* ==========================================
           POPUP
        ========================================== */

        .location-popup {

            min-width: 270px;

        }


        .location-popup h6 {

            font-size: 16px;

            font-weight: 600;

            margin-bottom: 12px;

        }


        .location-popup p {

            margin-bottom: 8px;

        }


        .popup-buttons {

            display: flex;

            gap: 7px;

            flex-wrap: wrap;

            margin-top: 12px;

        }


        /* ==========================================
           QR CODE
        ========================================== */

        .qr-container {

            display: none;

            text-align: center;

            margin-top: 15px;

            padding-top: 15px;

            border-top: 1px solid #ddd;

        }


        .qr-title {

            font-weight: bold;

            margin-bottom: 10px;

        }


        .qr-code {

            display: flex;

            justify-content: center;

            margin-bottom: 10px;

        }


        .qr-code img,
        .qr-code canvas {

            border: 5px solid white;

        }


        .qr-note {

            font-size: 12px;

            color: #666;

            margin-bottom: 10px;

        }
        /* ==========================================
   MARKER TÙY CHỈNH
========================================== */

.custom-location-marker {
    background: transparent;
    border: none;
}

.marker-pin {
    width: 38px;
    height: 38px;

    border-radius: 50% 50% 50% 0;

    transform: rotate(-45deg);

    display: flex;
    align-items: center;
    justify-content: center;

    border: 3px solid white;

    box-shadow:
        0 2px 8px rgba(0, 0, 0, 0.35);
}

.marker-pin span {
    transform: rotate(45deg);
    font-size: 18px;
}


/* ==========================================
   CHÚ GIẢI
========================================== */

.map-legend {
    background: white;

    padding: 12px 14px;

    border-radius: 10px;

    box-shadow:
        0 2px 10px rgba(0, 0, 0, 0.20);

    font-size: 13px;

    line-height: 1.8;
}

.map-legend-title {
    font-weight: bold;
    margin-bottom: 5px;
}

.legend-dot {
    display: inline-block;

    width: 12px;
    height: 12px;

    border-radius: 50%;

    margin-right: 6px;
}


/* ==========================================
   VỊ TRÍ NGƯỜI DÙNG
========================================== */

/* ==========================================
   HÀNH TRÌNH KHÁM PHÁ
========================================== */
.journey-map-marker {
    background: transparent;
    border: none;
}
.journey-map-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #0b4a36;
    color: #fff;
    border: 4px solid #fff;
    font-weight: 800;
    font-size: 15px;
    box-shadow: 0 4px 14px rgba(7,31,23,.35);
}
.journey-map-info {
    background: rgba(255,255,255,.96);
    padding: 13px 15px;
    border-radius: 14px;
    box-shadow: 0 5px 18px rgba(7,31,23,.20);
    border: 1px solid rgba(18,60,44,.10);
    min-width: 210px;
}
.journey-map-info strong {
    color: #0b4a36;
    display: block;
    margin-bottom: 4px;
}
.journey-map-info small {
    color: #65756d;
}
.journey-active-bar {
    background: #e8f2ec;
    border-bottom: 1px solid rgba(18,60,44,.12);
    padding: 8px 15px;
    color: #0b4a36;
    font-size: 13px;
    font-weight: 700;
    text-align: center;
}

.user-location-dot {
    width: 18px;
    height: 18px;

    background: #0d6efd;

    border: 4px solid white;

    border-radius: 50%;

    box-shadow:
        0 0 0 3px rgba(13, 110, 253, 0.25);
}




        /* ==========================================
           MOBILE
        ========================================== */

        @media (max-width: 767px) {

            .navbar-brand {

                font-size: 16px;

            }


            .search-area {

                padding: 10px;

            }


            #map {

                height: 65vh;

                min-height: 450px;

            }

        }

    </style>

<link rel="stylesheet" href="/bandosovanhoa/assets/css/public-theme.css?v=1">

</head>


<body>


<?php

$currentPage = 'map';

require_once("includes/navbar.php");

?>




<div class="search-area">

    <div class="container">

        <div class="row g-2">

    <div class="col-lg-4 col-md-12">

        <input
            type="text"
            id="searchInput"
            class="form-control"
            placeholder="🔎 Nhập tên địa điểm..."
        >

    </div>

    <div class="col-lg-3 col-md-6">

        <select
            id="categoryFilter"
            class="form-select"
        >

            <option value="">
                Tất cả loại địa điểm
            </option>

            <?php while (
                $category = $categoryResult->fetch_assoc()
            ): ?>

                <?php
                $categoryValue =
                    mb_strtolower(
                        $category['category_name'],
                        'UTF-8'
                    );
                ?>

                <option
                    value="<?= htmlspecialchars(
                        $categoryValue,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >
                    <?= htmlspecialchars(
                        $category['category_name'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </option>

            <?php endwhile; ?>

        </select>

    </div>

    <div class="col-lg-2 col-md-3 col-6">

        <button
            type="button"
            id="searchButton"
            class="btn btn-success w-100"
        >
            🔍 Tìm
        </button>

    </div>

    <div class="col-lg-1 col-md-3 col-6">

        <button
            type="button"
            id="resetButton"
            class="btn btn-outline-secondary w-100"
            title="Đặt lại"
        >
            ↻
        </button>

    </div>

    <div class="col-lg-2 col-md-12">

        <button
            type="button"
            id="myLocationButton"
            class="btn btn-primary w-100"
        >
            📍 Vị trí của tôi
        </button>


        </div>

    </div>

</div>


<!-- =====================================================
     BẢN ĐỒ
===================================================== -->

<div id="map"></div>



<!-- =====================================================
     LEAFLET JS
===================================================== -->

<!-- BOOTSTRAP JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>



<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
></script>



<!-- =====================================================
     QR CODE JS
===================================================== -->

<script
    src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"
></script>



<script>


/* =====================================================
   KHỞI TẠO BẢN ĐỒ
===================================================== */

var defaultCenter =
    [10.4938, 105.6882];


var defaultZoom = 12;


var map =
    L.map(
        "map"
    ).setView(

        defaultCenter,

        defaultZoom

    );



/* =====================================================
   OPENSTREETMAP
===================================================== */

L.tileLayer(

    "https://tile.openstreetmap.org/{z}/{x}/{y}.png",

    {

        maxZoom: 19,

        attribution:
            "&copy; OpenStreetMap contributors"

    }

).addTo(map);



/* =====================================================
   DANH SÁCH MARKER
===================================================== */

var locationMarkers = [];

/* =====================================================
   THÔNG TIN MARKER THEO DANH MỤC
===================================================== */

function getCategoryMarker(category) {

    category =
        (category || "")
        .toLocaleLowerCase("vi-VN");


    if (category.includes("văn hóa")) {

        return {
            color: "#6f42c1",
            icon: "🎭"
        };

    }


    if (category.includes("du lịch")) {

        return {
            color: "#198754",
            icon: "🏞️"
        };

    }


    if (category.includes("di tích")) {

        return {
            color: "#dc3545",
            icon: "🏛️"
        };

    }


    if (category.includes("ẩm thực")) {

        return {
            color: "#fd7e14",
            icon: "🍜"
        };

    }


    if (category.includes("ocop")) {

        return {
            color: "#ffc107",
            icon: "🎁"
        };

    }


    if (category.includes("sự kiện")) {

        return {
            color: "#0dcaf0",
            icon: "🎉"
        };

    }


    return {
        color: "#6c757d",
        icon: "📍"
    };

}


function createLocationIcon(category) {

    var markerInfo =
        getCategoryMarker(category);


    return L.divIcon({

        className:
            "custom-location-marker",

        html:
            '<div class="marker-pin" '
            +
            'style="background:'
            +
            markerInfo.color
            +
            ';">'
            +
            '<span>'
            +
            markerInfo.icon
            +
            '</span>'
            +
            '</div>',

        iconSize:
            [38, 38],

        iconAnchor:
            [19, 38],

        popupAnchor:
            [0, -38]

    });

}

/* =====================================================
   HÀM CHỐNG CHÈN HTML
===================================================== */

function escapeHtml(text) {

    var div =
        document.createElement(
            "div"
        );


    div.textContent =
        text || "";


    return div.innerHTML;

}



/* =====================================================
   HIỂN THỊ QR CODE
===================================================== */

function showLocationQR(
    event,
    id,
    url,
    name
) {

    if (event) {

        event.preventDefault();

        event.stopPropagation();

    }


    var qrBox =
        document.getElementById(
            "qr-" + id
        );


    if (!qrBox) {

        return false;

    }


    /* ==========================================
       QR ĐANG MỞ -> ĐÓNG
    ========================================== */

    if (
        qrBox.style.display
        === "block"
    ) {

        qrBox.style.display =
            "none";

        return false;

    }


    /* ==========================================
       HIỂN THỊ QR
    ========================================== */

    qrBox.style.display =
        "block";


    /* ==========================================
       ĐÃ TẠO QR RỒI
    ========================================== */

    if (
        qrBox.getAttribute(
            "data-created"
        ) === "1"
    ) {

        return false;

    }


    /* ==========================================
       TẠO GIAO DIỆN QR
    ========================================== */

    qrBox.innerHTML = `

        <div class="qr-title">

            📱 Mã QR địa điểm

        </div>


        <div
            id="qr-code-${id}"
            class="qr-code"
        >
        </div>


        <div class="qr-note">

            Dùng thiết bị khác quét mã QR

            <br>

            ${escapeHtml(name)}

        </div>


        <a
            href="${url}"
            class="btn btn-success btn-sm w-100 mb-2"
        >

            📲 Mở thông tin địa điểm

        </a>


        <button
            type="button"
            id="share-location-${id}"
            class="btn btn-primary btn-sm w-100 mb-2"
        >

            📤 Chia sẻ địa điểm

        </button>


        <button
            type="button"
            id="close-qr-${id}"
            class="btn btn-outline-secondary btn-sm w-100"
        >

            ✖ Đóng mã QR

        </button>

    `;



    /* ==========================================
       KIỂM TRA THƯ VIỆN QR
    ========================================== */

    if (
        typeof QRCode
        === "undefined"
    ) {

        qrBox.innerHTML = `

            <div
                class="alert alert-danger p-2"
            >

                Không thể tải thư viện
                QR Code.

            </div>

        `;

        return false;

    }



    /* ==========================================
       TẠO QR
    ========================================== */

    new QRCode(

        document.getElementById(
            "qr-code-" + id
        ),

        {

            text: url,

            width: 160,

            height: 160,

            correctLevel:
                QRCode.CorrectLevel.H

        }

    );



    /* ==========================================
       NÚT CHIA SẺ
    ========================================== */

    var shareButton =
        document.getElementById(
            "share-location-" + id
        );


    if (shareButton) {

        shareButton.addEventListener(

            "click",

            function(e) {

                e.preventDefault();

                e.stopPropagation();


                shareLocation(
                    name,
                    url
                );

            }

        );

    }



    /* ==========================================
       NÚT ĐÓNG QR
    ========================================== */

    var closeButton =
        document.getElementById(
            "close-qr-" + id
        );


    if (closeButton) {

        closeButton.addEventListener(

            "click",

            function(e) {

                e.preventDefault();

                e.stopPropagation();


                qrBox.style.display =
                    "none";

            }

        );

    }



    /* ==========================================
       ĐÁNH DẤU QR ĐÃ TẠO
    ========================================== */

    qrBox.setAttribute(
        "data-created",
        "1"
    );


    return false;

}



/* =====================================================
   CHIA SẺ ĐỊA ĐIỂM
===================================================== */

function shareLocation(
    name,
    url
) {

    if (navigator.share) {

        navigator.share({

            title: name,

            text:
                "Xem thông tin địa điểm: "
                + name,

            url: url

        }).catch(
            function() {

                // Người dùng đóng hộp chia sẻ.

            }
        );

    }

    else if (
        navigator.clipboard
    ) {

        navigator.clipboard
            .writeText(url)
            .then(
                function() {

                    alert(
                        "Đã sao chép đường dẫn địa điểm!"
                    );

                }
            );

    }

    else {

        alert(
            "Thiết bị này chưa hỗ trợ chia sẻ."
        );

    }

}



/* =====================================================
   TẠO MARKER TỪ MYSQL
===================================================== */

<?php while (
    $location =
        $result->fetch_assoc()
): ?>


<?php


/* -----------------------------------------------------
   CHUẨN BỊ DỮ LIỆU
----------------------------------------------------- */

$locationId =
    (int) $location['location_id'];


$latitude =
    (float) $location['latitude'];


$longitude =
    (float) $location['longitude'];


$name =
    $location['name'] ?? '';


$address =
    $location['address'] ?? '';


$category =
    $location['category_name']
    ?? 'Chưa phân loại';


$nameSearch =
    mb_strtolower(
        $name,
        'UTF-8'
    );


$categorySearch =
    mb_strtolower(
        $category,
        'UTF-8'
    );


/* -----------------------------------------------------
   URL CHI TIẾT

   Sau này đưa lên hosting thì đổi localhost
   thành tên miền thật.
----------------------------------------------------- */

$detailUrl =
    location_url($locationId);

?>



/* -----------------------------------------------------
   MARKER <?= $locationId ?>

----------------------------------------------------- */

var marker<?= $locationId ?> =
    L.marker(
        [
            <?= $latitude ?>,
            <?= $longitude ?>
        ],
        {
            icon: createLocationIcon(
                <?= json_encode(
                    $category,
                    JSON_UNESCAPED_UNICODE |
                    JSON_HEX_TAG |
                    JSON_HEX_APOS |
                    JSON_HEX_QUOT |
                    JSON_HEX_AMP
                ) ?>
            )
        }
    ).addTo(map);



/* -----------------------------------------------------
   POPUP <?= $locationId ?>

----------------------------------------------------- */

var popupContent<?= $locationId ?> = `

    <div class="location-popup">


        <h6>

            <?= htmlspecialchars(
                $name,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </h6>


        <p>

            <strong>

                Loại:

            </strong>

            <?= htmlspecialchars(
                $category,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </p>


        <p>

            <strong>

                Địa chỉ:

            </strong>

            <?= htmlspecialchars(
                $address,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </p>



        <div class="popup-buttons">


            <!-- XEM CHI TIẾT -->

            <a
                href="location_detail.php?id=<?= $locationId ?>"
                class="btn btn-success btn-sm"
            >

                👁 Xem chi tiết

            </a>

            <a
                href="https://www.google.com/maps/dir/?api=1&destination=<?= urlencode(
                    $latitude . ',' . $longitude
                ) ?>"
                target="_blank"
                rel="noopener noreferrer"
                class="btn btn-warning btn-sm"
            >
                🚗 Chỉ đường
            </a>



            <!-- QR -->

            <button

                type="button"

                class="btn btn-primary btn-sm"

                onclick='return showLocationQR(

                    event,

                    <?= $locationId ?>,

                    <?= json_encode(
                        $detailUrl,
                        JSON_HEX_TAG |
                        JSON_HEX_APOS |
                        JSON_HEX_QUOT |
                        JSON_HEX_AMP
                    ) ?>,

                    <?= json_encode(
                        $name,
                        JSON_HEX_TAG |
                        JSON_HEX_APOS |
                        JSON_HEX_QUOT |
                        JSON_HEX_AMP
                    ) ?>

                );'

            >

                📱 Mã QR

            </button>


        </div>



        <!-- ======================================
             VÙNG QR
        ======================================= -->

        <div

            id="qr-<?= $locationId ?>"

            class="qr-container"

            data-created="0"

        >
        </div>


    </div>

`;



/* -----------------------------------------------------
   GẮN POPUP
----------------------------------------------------- */

marker<?= $locationId ?>.bindPopup(

    popupContent<?= $locationId ?>,

    {

        maxWidth: 350,

        minWidth: 280

    }

);



/* -----------------------------------------------------
   LƯU MARKER CHO TÌM KIẾM
----------------------------------------------------- */

locationMarkers.push({

    id: <?= (int)$locationId ?>,

    marker:
        marker<?= (int)$locationId ?>,

    name:
        <?= json_encode(
            $nameSearch,
            JSON_UNESCAPED_UNICODE |
            JSON_HEX_TAG |
            JSON_HEX_APOS |
            JSON_HEX_QUOT |
            JSON_HEX_AMP
        ) ?>,

    category:
        <?= json_encode(
            $categorySearch,
            JSON_UNESCAPED_UNICODE |
            JSON_HEX_TAG |
            JSON_HEX_APOS |
            JSON_HEX_QUOT |
            JSON_HEX_AMP
        ) ?>,

    categoryDisplay:
        <?= json_encode(
            $category,
            JSON_UNESCAPED_UNICODE |
            JSON_HEX_TAG |
            JSON_HEX_APOS |
            JSON_HEX_QUOT |
            JSON_HEX_AMP
        ) ?>,

    lat: <?= $latitude ?>,
    lng: <?= $longitude ?>

});


<?php endwhile; ?>

/* =====================================================
   CHÚ GIẢI BẢN ĐỒ
===================================================== */

var legend =
    L.control({
        position: "bottomright"
    });


legend.onAdd =
    function() {

        var div =
            L.DomUtil.create(
                "div",
                "map-legend"
            );


        div.innerHTML = `
            <div class="map-legend-title">
                🗺️ Chú giải
            </div>

            <div>
                <span
                    class="legend-dot"
                    style="background:#6f42c1">
                </span>
                🎭 Văn hóa
            </div>

            <div>
                <span
                    class="legend-dot"
                    style="background:#198754">
                </span>
                🏞️ Du lịch
            </div>

            <div>
                <span
                    class="legend-dot"
                    style="background:#dc3545">
                </span>
                🏛️ Di tích
            </div>

            <div>
                <span
                    class="legend-dot"
                    style="background:#fd7e14">
                </span>
                🍜 Ẩm thực
            </div>

            <div>
                <span
                    class="legend-dot"
                    style="background:#ffc107">
                </span>
                🎁 OCOP
            </div>

            <div>
                <span
                    class="legend-dot"
                    style="background:#0dcaf0">
                </span>
                🎉 Sự kiện
            </div>
        `;


        L.DomEvent.disableClickPropagation(
            div
        );


        return div;

    };


legend.addTo(map);

/* =====================================================
   VỊ TRÍ CỦA TÔI
===================================================== */

var userLocationMarker = null;


document
.getElementById(
    "myLocationButton"
)
.addEventListener(
    "click",
    function() {


        if (
            !navigator.geolocation
        ) {

            alert(
                "Trình duyệt không hỗ trợ định vị."
            );

            return;

        }


        var button = this;


        button.disabled = true;

        button.innerHTML =
            "⏳ Đang xác định...";


        navigator.geolocation.getCurrentPosition(

            function(position) {


                var lat =
                    position.coords.latitude;

                var lng =
                    position.coords.longitude;


                if (
                    userLocationMarker
                ) {

                    map.removeLayer(
                        userLocationMarker
                    );

                }


                var userIcon =
                    L.divIcon({

                        className: "",

                        html:
                            '<div class="user-location-dot"></div>',

                        iconSize:
                            [18, 18],

                        iconAnchor:
                            [9, 9]

                    });


                userLocationMarker =
                    L.marker(
                        [lat, lng],
                        {
                            icon: userIcon
                        }
                    )
                    .addTo(map)
                    .bindPopup(
                        "<strong>📍 Vị trí của bạn</strong>"
                    );


                map.setView(
                    [lat, lng],
                    16
                );


                userLocationMarker
                    .openPopup();


                button.disabled =
                    false;

                button.innerHTML =
                    "📍 Vị trí của tôi";

            },


            function(error) {


                button.disabled =
                    false;

                button.innerHTML =
                    "📍 Vị trí của tôi";


                if (
                    error.code === 1
                ) {

                    alert(
                        "Bạn chưa cho phép website truy cập vị trí."
                    );

                } else {

                    alert(
                        "Không thể xác định vị trí hiện tại."
                    );

                }

            },


            {
                enableHighAccuracy:
                    true,

                timeout:
                    10000,

                maximumAge:
                    30000
            }

        );

    }
);

/* =====================================================
   HÀM TÌM KIẾM
===================================================== */

function filterLocations() {

    var searchInput =
        document.getElementById(
            "searchInput"
        );


    var categoryFilter =
        document.getElementById(
            "categoryFilter"
        );


    var keyword =
        searchInput
            .value
            .toLocaleLowerCase("vi-VN")
            .trim();


    var selectedCategory =
        categoryFilter
            .value
            .toLocaleLowerCase("vi-VN")
            .trim();


    var visibleMarkers = [];



    /* ==========================================
       DUYỆT TẤT CẢ MARKER
    ========================================== */

    locationMarkers.forEach(

        function(item) {


            var matchName =

                keyword === ""

                ||

                item.name.includes(
                    keyword
                );



            var matchCategory =

                selectedCategory === ""

                ||

                item.category ===
                    selectedCategory;



            /* ==================================
               PHÙ HỢP
            ================================== */

            if (
                matchName
                &&
                matchCategory
            ) {


                if (
                    !map.hasLayer(
                        item.marker
                    )
                ) {

                    item.marker.addTo(
                        map
                    );

                }


                visibleMarkers.push(
                    item.marker
                );

            }


            /* ==================================
               KHÔNG PHÙ HỢP
            ================================== */

            else {


                if (
                    map.hasLayer(
                        item.marker
                    )
                ) {

                    map.removeLayer(
                        item.marker
                    );

                }

            }

        }

    );



    /* ==========================================
       KHÔNG TÌM THẤY
    ========================================== */

    if (
        visibleMarkers.length === 0
    ) {

        alert(
            "Không tìm thấy địa điểm phù hợp!"
        );

        return;

    }



    /* ==========================================
       CHỈ CÓ 1 KẾT QUẢ
       -> ZOOM TỚI MARKER
    ========================================== */

    if (
        visibleMarkers.length === 1
    ) {

        var marker =
            visibleMarkers[0];


        map.setView(

            marker.getLatLng(),

            16

        );


        marker.openPopup();


        return;

    }



    /* ==========================================
       CÓ NHIỀU KẾT QUẢ
       -> ZOOM VỪA TẤT CẢ MARKER
    ========================================== */

    var group =
        L.featureGroup(
            visibleMarkers
        );


    map.fitBounds(

        group.getBounds(),

        {

            padding:
                [40, 40],

            maxZoom: 16

        }

    );

}



/* =====================================================
   NÚT TÌM KIẾM
===================================================== */

var searchButton =
    document.getElementById(
        "searchButton"
    );


searchButton.addEventListener(

    "click",

    function() {

        filterLocations();

    }

);



/* =====================================================
   NHẤN ENTER ĐỂ TÌM
===================================================== */

var searchInput =
    document.getElementById(
        "searchInput"
    );


searchInput.addEventListener(

    "keydown",

    function(event) {

        if (
            event.key === "Enter"
        ) {

            event.preventDefault();

            filterLocations();

        }

    }

);



/* =====================================================
   NÚT ĐẶT LẠI
===================================================== */

var resetButton =
    document.getElementById(
        "resetButton"
    );


resetButton.addEventListener(

    "click",

    function() {


        /* XÓA Ô TÌM KIẾM */

        document
            .getElementById(
                "searchInput"
            )
            .value = "";



        /* TRỞ VỀ TẤT CẢ DANH MỤC */

        document
            .getElementById(
                "categoryFilter"
            )
            .value = "";



        /* XÓA TUYẾN HÀNH TRÌNH NẾU ĐANG HIỂN THỊ */

        if (journeyLine && map.hasLayer(journeyLine)) {
            map.removeLayer(journeyLine);
            journeyLine = null;
        }

        if (journeyInfoControl) {
            map.removeControl(journeyInfoControl);
            journeyInfoControl = null;
        }


        /* HIỆN LẠI TẤT CẢ MARKER */

        locationMarkers.forEach(

            function(item) {

                item.marker.setIcon(
                    createLocationIcon(item.categoryDisplay)
                );


                if (
                    !map.hasLayer(
                        item.marker
                    )
                ) {

                    item.marker.addTo(
                        map
                    );

                }

            }

        );



        /* ĐÓNG POPUP */

        map.closePopup();



        /* TRỞ VỀ VỊ TRÍ BAN ĐẦU */

        map.setView(

            defaultCenter,

            defaultZoom

        );

    }

);

/* =====================================================
   NHẬN DANH MỤC TỪ TRANG CHỦ
   Ví dụ:
   map.php?category=Du%20lịch
===================================================== */

var urlParams =
    new URLSearchParams(
        window.location.search
    );

var categoryFromUrl =
    urlParams.get("category");


if (categoryFromUrl) {

    var categoryFilter =
        document.getElementById(
            "categoryFilter"
        );

    var normalizedCategory =
        categoryFromUrl
            .toLocaleLowerCase("vi-VN")
            .trim();


    /* TÌM OPTION PHÙ HỢP */

    var categoryExists = false;

    Array.from(
        categoryFilter.options
    ).forEach(
        function(option) {

            if (
                option.value
                    .toLocaleLowerCase("vi-VN")
                    .trim()
                ===
                normalizedCategory
            ) {

                categoryFilter.value =
                    option.value;

                categoryExists = true;

            }

        }
    );


    /* TỰ ĐỘNG LỌC */

    if (categoryExists) {

        filterLocations();

    }

}
/* =====================================================
   HÀNH TRÌNH KHÁM PHÁ TỪ URL
   Ví dụ: map.php?journey=van-hoa
===================================================== */

var journeyFromUrl = urlParams.get("journey");
var journeyLine = null;
var journeyInfoControl = null;

var journeyThemes = {
    "van-hoa": {
        label: "Văn hóa",
        icon: "🎭",
        keywords: ["văn hóa", "di tích"]
    },
    "am-thuc": {
        label: "Ẩm thực",
        icon: "🍜",
        keywords: ["ẩm thực", "ăn uống"]
    },
    "ocop": {
        label: "OCOP",
        icon: "🎁",
        keywords: ["ocop"]
    },
    "du-lich": {
        label: "Du lịch - Điểm đến",
        icon: "🏞️",
        keywords: ["du lịch", "vui chơi", "điểm đến"]
    }
};

function createJourneyNumberIcon(number) {
    return L.divIcon({
        className: "journey-map-marker",
        html: '<div class="journey-map-number">' + number + '</div>',
        iconSize: [40, 40],
        iconAnchor: [20, 20],
        popupAnchor: [0, -22]
    });
}

async function showJourneyOnMap(themeKey) {

    var theme = journeyThemes[themeKey];

    if (!theme) {
        return;
    }

    var journeyMarkers = locationMarkers
        .filter(function(item) {
            return theme.keywords.some(function(keyword) {
                return item.category.includes(
                    keyword.toLocaleLowerCase("vi-VN")
                );
            });
        })
        .sort(function(a, b) {
            return b.id - a.id;
        })
        .slice(0, 6);

    if (journeyMarkers.length === 0) {
        alert("Chưa có địa điểm phù hợp cho hành trình này.");
        return;
    }

    locationMarkers.forEach(function(item) {
        if (map.hasLayer(item.marker)) {
            map.removeLayer(item.marker);
        }
    });

    var routePoints = [];

    journeyMarkers.forEach(function(item, index) {

        item.marker.setIcon(
            createJourneyNumberIcon(index + 1)
        );

        item.marker.addTo(map);

        routePoints.push(
            item.marker.getLatLng()
        );
    });

    /* Tìm đường giao thông thực tế qua các điểm bằng OSRM */
    if (routePoints.length >= 2) {

        var coordinates = routePoints.map(function(point) {
            return point.lng + "," + point.lat;
        }).join(";");

        var routeUrl =
            "https://router.project-osrm.org/route/v1/driving/" +
            coordinates +
            "?overview=full&geometries=geojson&steps=false";

        try {

            var response = await fetch(routeUrl);

            if (!response.ok) {
                throw new Error("Không thể kết nối dịch vụ chỉ đường.");
            }

            var routeData = await response.json();

            if (
                routeData.code === "Ok" &&
                routeData.routes &&
                routeData.routes.length > 0
            ) {

                var route = routeData.routes[0];

                var roadPoints =
                    route.geometry.coordinates.map(
                        function(coord) {
                            return [coord[1], coord[0]];
                        }
                    );

                journeyLine = L.polyline(
                    roadPoints,
                    {
                        color: "#1d6b4d",
                        weight: 6,
                        opacity: 0.88,
                        lineJoin: "round",
                        lineCap: "round"
                    }
                ).addTo(map);

                journeyLine.bringToBack();

                map.fitBounds(
                    journeyLine.getBounds(),
                    {
                        padding: [55, 55],
                        maxZoom: 16
                    }
                );

                var distanceKm =
                    (route.distance / 1000).toFixed(1);

                var durationMinutes =
                    Math.max(
                        1,
                        Math.round(route.duration / 60)
                    );

                if (journeyInfoControl) {
                    map.removeControl(journeyInfoControl);
                }

                journeyInfoControl = L.control({
                    position: "topleft"
                });

                journeyInfoControl.onAdd = function() {

                    var div = L.DomUtil.create(
                        "div",
                        "journey-map-info"
                    );

                    div.innerHTML =
                        "<strong>" +
                        theme.icon +
                        " Hành trình " +
                        escapeHtml(theme.label) +
                        "</strong>" +
                        "<small>" +
                        journeyMarkers.length +
                        " điểm · " +
                        distanceKm +
                        " km · khoảng " +
                        durationMinutes +
                        " phút di chuyển</small>";

                    L.DomEvent.disableClickPropagation(div);

                    return div;
                };

                journeyInfoControl.addTo(map);

                return;
            }

            throw new Error("Không tìm thấy tuyến đường phù hợp.");

        } catch (error) {

            console.error(
                "Lỗi tìm đường hành trình:",
                error
            );

            /* Nếu dịch vụ chỉ đường lỗi, vẫn giữ marker và zoom tới các điểm */
            var fallbackGroup = L.featureGroup(
                journeyMarkers.map(function(item) {
                    return item.marker;
                })
            );

            map.fitBounds(
                fallbackGroup.getBounds(),
                {
                    padding: [55, 55],
                    maxZoom: 15
                }
            );
        }
    }

    if (journeyInfoControl) {
        map.removeControl(journeyInfoControl);
    }

    journeyInfoControl = L.control({
        position: "topleft"
    });

    journeyInfoControl.onAdd = function() {

        var div = L.DomUtil.create(
            "div",
            "journey-map-info"
        );

        div.innerHTML =
            "<strong>" +
            theme.icon +
            " Hành trình " +
            escapeHtml(theme.label) +
            "</strong>" +
            "<small>" +
            journeyMarkers.length +
            " điểm · Các số 1 → " +
            journeyMarkers.length +
            " là thứ tự khám phá</small>";

        L.DomEvent.disableClickPropagation(div);

        return div;
    };

    journeyInfoControl.addTo(map);
}

if (journeyFromUrl) {
    showJourneyOnMap(journeyFromUrl);
}


/* =====================================================
   MỞ ĐỊA ĐIỂM TỪ URL
   Ví dụ: map.php?location=3
===================================================== */

var mapUrlParams =
    new URLSearchParams(
        window.location.search
    );

var locationFromUrl =
    parseInt(
        mapUrlParams.get("location"),
        10
    );


if (
    Number.isInteger(locationFromUrl)
    &&
    locationFromUrl > 0
) {

    var selectedLocation =
        locationMarkers.find(
            function(item) {

                return item.id ===
                    locationFromUrl;

            }
        );


    if (selectedLocation) {

        map.setView(
            selectedLocation.marker.getLatLng(),
            16
        );

        selectedLocation.marker.openPopup();

    }

}

/* =====================================================
   FIX LEAFLET KHI TRANG VỪA LOAD
===================================================== */

window.addEventListener(

    "load",

    function() {

        setTimeout(

            function() {

                map.invalidateSize();

            },

            200

        );

    }

);


</script>


</body>

</html>