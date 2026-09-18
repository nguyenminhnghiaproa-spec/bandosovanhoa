<?php

require_once("../auth.php");
require_once("../../config/database.php");


/* =====================================================
   BIẾN FORM
===================================================== */

$error = "";

$name = "";
$category_id = 0;
$address = "";
$latitude = "";
$longitude = "";
$description = "";
$phone = "";
$opening_hours = "";


/* =====================================================
   XỬ LÝ THÊM ĐỊA ĐIỂM
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name =
        trim($_POST["name"] ?? "");

    $category_id =
        (int)($_POST["category_id"] ?? 0);

    $address =
        trim($_POST["address"] ?? "");

    $latitudeRaw =
        trim($_POST["latitude"] ?? "");

    $longitudeRaw =
        trim($_POST["longitude"] ?? "");

    $description =
        trim($_POST["description"] ?? "");

    $phone =
        trim($_POST["phone"] ?? "");

    $opening_hours =
        trim($_POST["opening_hours"] ?? "");

    $status = "active";


    /* =================================================
       KIỂM TRA DỮ LIỆU
    ================================================= */

    if ($name === "") {

        $error =
            "Vui lòng nhập tên địa điểm.";

    }

    elseif ($category_id <= 0) {

        $error =
            "Vui lòng chọn loại địa điểm.";

    }

    elseif ($address === "") {

        $error =
            "Vui lòng nhập địa chỉ.";

    }

    elseif (
        $latitudeRaw === ""
        ||
        $longitudeRaw === ""
        ||
        !is_numeric($latitudeRaw)
        ||
        !is_numeric($longitudeRaw)
    ) {

        $error =
            "Vui lòng chọn vị trí của địa điểm trên bản đồ.";

    }

    else {

        $latitude =
            (float)$latitudeRaw;

        $longitude =
            (float)$longitudeRaw;


        if (
            $latitude < -90
            ||
            $latitude > 90
            ||
            $longitude < -180
            ||
            $longitude > 180
        ) {

            $error =
                "Tọa độ địa điểm không hợp lệ.";

        }

        elseif (
            abs($latitude) < 0.00000001
            &&
            abs($longitude) < 0.00000001
        ) {

            $error =
                "Vui lòng chọn vị trí của địa điểm trên bản đồ.";

        }

    }


    /* =================================================
       THÊM VÀO DATABASE
    ================================================= */

    if ($error === "") {

        $sql = "
            INSERT INTO locations
            (
                category_id,
                name,
                address,
                latitude,
                longitude,
                description,
                phone,
                opening_hours,
                status
            )

            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";


        $stmt =
            $conn->prepare($sql);


        if (!$stmt) {

            $error =
                "Không thể chuẩn bị dữ liệu.";

        }

        else {

            $stmt->bind_param(
                "issddssss",
                $category_id,
                $name,
                $address,
                $latitude,
                $longitude,
                $description,
                $phone,
                $opening_hours,
                $status
            );


            if ($stmt->execute()) {

                header(
                    "Location: index.php?added=1"
                );

                exit();

            }

            else {

                $error =
                    "Không thể thêm địa điểm. Vui lòng thử lại.";

            }


            $stmt->close();

        }

    }

}


/* =====================================================
   LẤY DANH MỤC
===================================================== */

$categoryQuery = "
    SELECT
        category_id,
        category_name
    FROM categories
    ORDER BY category_name ASC
";


$categories =
    $conn->query($categoryQuery);


if (!$categories) {

    die(
        "Không thể tải danh mục: "
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
    content="width=device-width, initial-scale=1"
>

<title>Thêm địa điểm</title>


<!-- BOOTSTRAP -->

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<!-- LEAFLET -->

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
>


<style>

/* =====================================================
   BODY
===================================================== */

body {

    background: #f4f6f8;

}


/* =====================================================
   CARD
===================================================== */

.main-card {

    border: none;

    border-radius: 15px;

    overflow: hidden;

}


/* =====================================================
   MAP
===================================================== */

#locationMap {

    height: 450px;

    width: 100%;

    border-radius: 10px;

    border: 1px solid #ddd;

}


#locationMap.map-error {

    border: 2px solid #dc3545;

}


/* =====================================================
   THÔNG BÁO ĐÃ CHỌN
===================================================== */

#positionSuccess {

    display: none;

}


/* =====================================================
   MODAL THÔNG BÁO
===================================================== */

.message-modal {

    border: none;

    border-radius: 20px;

    overflow: hidden;

}


.message-icon {

    width: 80px;

    height: 80px;

    margin: 0 auto 18px;

    border-radius: 50%;

    background: #fff3cd;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 38px;

}


.message-text {

    font-size: 15px;

    line-height: 1.6;

}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 767px) {

    #locationMap {

        height: 350px;

    }

}

</style>

</head>


<body>

<?php

$currentAdminPage = 'locations';

require_once(
    "../includes/navbar.php"
);

?>

<div class="container py-5">


<div
    class="
        card
        main-card
        shadow
    "
>


<div
    class="
        card-header
        bg-success
        text-white
        p-3
    "
>

<h4 class="mb-0">

    📍 THÊM ĐỊA ĐIỂM MỚI

</h4>

</div>


<div class="card-body p-4">


<form
    method="POST"
    id="locationForm"
    novalidate
>


<!-- =====================================================
     TÊN ĐỊA ĐIỂM
===================================================== -->

<div class="mb-3">

<label class="form-label">

    Tên địa điểm
    <span class="text-danger">*</span>

</label>


<input
    type="text"
    name="name"
    id="name"
    class="form-control"
    value="<?= htmlspecialchars(
        $name,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>

</div>



<!-- =====================================================
     LOẠI ĐỊA ĐIỂM
===================================================== -->

<div class="mb-3">

<label class="form-label">

    Loại địa điểm
    <span class="text-danger">*</span>

</label>


<select
    name="category_id"
    id="category_id"
    class="form-select"
>

<option value="">

    -- Chọn loại địa điểm --

</option>


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


</select>

</div>



<!-- =====================================================
     ĐỊA CHỈ
===================================================== -->

<div class="mb-3">

<label class="form-label">

    Địa chỉ
    <span class="text-danger">*</span>

</label>


<input
    type="text"
    name="address"
    id="address"
    class="form-control"
    value="<?= htmlspecialchars(
        $address,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>

</div>



<!-- =====================================================
     BẢN ĐỒ
===================================================== -->

<div class="mb-3">


<label class="form-label fw-bold">

    📍 Chọn vị trí trên bản đồ
    <span class="text-danger">*</span>

</label>


<p class="text-muted mb-2">

    Nhấn trực tiếp vào bản đồ để đánh dấu
    vị trí chính xác của địa điểm.

</p>


<div id="locationMap"></div>


<div
    id="positionSuccess"
    class="
        alert
        alert-success
        mt-2
        mb-0
    "
>

    ✅ Đã chọn vị trí trên bản đồ.

</div>


</div>



<!-- =====================================================
     TỌA ĐỘ
===================================================== -->

<div class="row">


<div class="col-md-6 mb-3">

<label class="form-label">

    Vĩ độ

</label>


<input
    type="text"
    id="latitude"
    name="latitude"
    class="form-control"
    value="<?= htmlspecialchars(
        (string)$latitude,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    readonly
>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

    Kinh độ

</label>


<input
    type="text"
    id="longitude"
    name="longitude"
    class="form-control"
    value="<?= htmlspecialchars(
        (string)$longitude,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    readonly
>

</div>


</div>



<!-- =====================================================
     MÔ TẢ
===================================================== -->

<div class="mb-3">

<label class="form-label">

    Mô tả địa điểm

</label>


<textarea
    name="description"
    class="form-control"
    rows="5"
><?= htmlspecialchars(
    $description,
    ENT_QUOTES,
    'UTF-8'
) ?></textarea>

</div>



<!-- =====================================================
     SỐ ĐIỆN THOẠI
===================================================== -->

<div class="mb-3">

<label class="form-label">

    Số điện thoại

</label>


<input
    type="text"
    name="phone"
    class="form-control"
    value="<?= htmlspecialchars(
        $phone,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>

</div>



<!-- =====================================================
     THỜI GIAN HOẠT ĐỘNG
===================================================== -->

<div class="mb-4">

<label class="form-label">

    Thời gian hoạt động

</label>


<input
    type="text"
    name="opening_hours"
    class="form-control"
    value="<?= htmlspecialchars(
        $opening_hours,
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    placeholder="Ví dụ: 07:00 - 17:00"
>

</div>



<!-- =====================================================
     BUTTON
===================================================== -->

<button
    type="submit"
    class="btn btn-success"
>

    💾 Lưu địa điểm

</button>


<a
    href="index.php"
    class="btn btn-secondary"
>

    Hủy

</a>


<a
    href="../../map.php"
    class="btn btn-outline-success"
    target="_blank"
>

    🗺 Xem bản đồ

</a>


</form>


</div>

</div>

</div>



<!-- =====================================================
     MODAL THÔNG BÁO CHUNG
===================================================== -->

<div
    class="modal fade"
    id="messageModal"
    tabindex="-1"
    aria-labelledby="messageModalTitle"
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
        message-modal
        shadow
    "
>


<div class="modal-body text-center p-4">


<div class="message-icon">

    ⚠️

</div>


<h4
    class="fw-bold mb-3"
    id="messageModalTitle"
>

    Thông báo

</h4>


<p
    id="messageModalText"
    class="
        message-text
        text-muted
        mb-4
    "
>
</p>


<button
    type="button"
    class="
        btn
        btn-warning
        px-4
    "
    data-bs-dismiss="modal"
>

    Đã hiểu

</button>


</div>

</div>

</div>

</div>



<!-- =====================================================
     LEAFLET JS
===================================================== -->

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
></script>


<!-- =====================================================
     BOOTSTRAP JS
===================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>


<script>

/* =====================================================
   MODAL THÔNG BÁO
===================================================== */

function showMessage(message) {

    document
        .getElementById(
            "messageModalText"
        )
        .textContent =
            message;


    const modalElement =
        document.getElementById(
            "messageModal"
        );


    const modal =
        bootstrap.Modal
        .getOrCreateInstance(
            modalElement
        );


    modal.show();

}



/* =====================================================
   NẾU PHP PHÁT HIỆN LỖI
===================================================== */

<?php if ($error !== ""): ?>

document.addEventListener(
    "DOMContentLoaded",
    function() {

        showMessage(
            <?= json_encode(
                $error,
                JSON_UNESCAPED_UNICODE |
                JSON_HEX_TAG |
                JSON_HEX_APOS |
                JSON_HEX_QUOT |
                JSON_HEX_AMP
            ) ?>
        );

    }
);

<?php endif; ?>



/* =====================================================
   KHỞI TẠO BẢN ĐỒ
===================================================== */

const defaultLatitude =
    10.4938;

const defaultLongitude =
    105.6882;


const locationMap =
    L.map(
        "locationMap"
    ).setView(

        [
            defaultLatitude,
            defaultLongitude
        ],

        12

    );


L.tileLayer(

    "https://tile.openstreetmap.org/{z}/{x}/{y}.png",

    {

        maxZoom: 19,

        attribution:
            "&copy; OpenStreetMap contributors"

    }

).addTo(locationMap);



/* =====================================================
   MARKER
===================================================== */

let selectedMarker = null;

let positionSelected = false;


/* =====================================================
   GIỮ TỌA ĐỘ NẾU PHP TRẢ FORM VỀ
===================================================== */

const savedLatitude =
    parseFloat(
        document
            .getElementById(
                "latitude"
            )
            .value
    );


const savedLongitude =
    parseFloat(
        document
            .getElementById(
                "longitude"
            )
            .value
    );


if (
    Number.isFinite(savedLatitude)
    &&
    Number.isFinite(savedLongitude)
    &&
    !(
        Math.abs(savedLatitude) < 0.00000001
        &&
        Math.abs(savedLongitude) < 0.00000001
    )
) {

    positionSelected =
        true;


    selectedMarker =
        L.marker(
            [
                savedLatitude,
                savedLongitude
            ]
        )
        .addTo(locationMap);


    locationMap.setView(

        [
            savedLatitude,
            savedLongitude
        ],

        16

    );


    document
        .getElementById(
            "positionSuccess"
        )
        .style.display =
            "block";

}



/* =====================================================
   CLICK BẢN ĐỒ
===================================================== */

locationMap.on(
    "click",
    function(e) {

        const latitude =
            e.latlng.lat;

        const longitude =
            e.latlng.lng;


        document
            .getElementById(
                "latitude"
            )
            .value =
                latitude.toFixed(8);


        document
            .getElementById(
                "longitude"
            )
            .value =
                longitude.toFixed(8);


        if (selectedMarker) {

            selectedMarker
                .setLatLng(
                    e.latlng
                );

        }

        else {

            selectedMarker =
                L.marker(
                    e.latlng
                )
                .addTo(
                    locationMap
                );

        }


        selectedMarker
            .bindPopup(
                "<strong>📍 Vị trí đã chọn</strong>"
                +
                "<br>Vĩ độ: "
                +
                latitude.toFixed(6)
                +
                "<br>Kinh độ: "
                +
                longitude.toFixed(6)
            )
            .openPopup();


        positionSelected =
            true;


        document
            .getElementById(
                "locationMap"
            )
            .classList
            .remove(
                "map-error"
            );


        document
            .getElementById(
                "positionSuccess"
            )
            .style.display =
                "block";

    }
);



/* =====================================================
   VALIDATION FORM
===================================================== */

document
.getElementById(
    "locationForm"
)
.addEventListener(
    "submit",
    function(event) {

        const name =
            document
                .getElementById(
                    "name"
                )
                .value
                .trim();


        const category =
            document
                .getElementById(
                    "category_id"
                )
                .value;


        const address =
            document
                .getElementById(
                    "address"
                )
                .value
                .trim();


        if (name === "") {

            event.preventDefault();

            showMessage(
                "Vui lòng nhập tên địa điểm."
            );

            return;

        }


        if (category === "") {

            event.preventDefault();

            showMessage(
                "Vui lòng chọn loại địa điểm."
            );

            return;

        }


        if (address === "") {

            event.preventDefault();

            showMessage(
                "Vui lòng nhập địa chỉ."
            );

            return;

        }


        if (!positionSelected) {

            event.preventDefault();


            document
                .getElementById(
                    "locationMap"
                )
                .classList
                .add(
                    "map-error"
                );


            showMessage(
                "Vui lòng chọn vị trí của địa điểm trên bản đồ."
            );


            setTimeout(
                function() {

                    document
                        .getElementById(
                            "locationMap"
                        )
                        .scrollIntoView({

                            behavior:
                                "smooth",

                            block:
                                "center"

                        });

                },

                300
            );


            return;

        }

    }
);



/* =====================================================
   FIX LEAFLET
===================================================== */

window.addEventListener(
    "load",
    function() {

        setTimeout(
            function() {

                locationMap
                    .invalidateSize();

            },

            200
        );

    }
);

</script>


</body>

</html>