<?php

require_once("../auth.php");
require_once("../../config/database.php");


/* =====================================================
   KIỂM TRA ID
===================================================== */

if (
    !isset($_GET['id'])
    ||
    !is_numeric($_GET['id'])
) {

    header(
        "Location: index.php?error=invalid"
    );

    exit();

}


$location_id =
    (int)$_GET['id'];


if ($location_id <= 0) {

    header(
        "Location: index.php?error=invalid"
    );

    exit();

}


/* =====================================================
   LẤY ĐỊA ĐIỂM
===================================================== */

$stmt =
    $conn->prepare("
        SELECT *
        FROM locations
        WHERE location_id = ?
        LIMIT 1
    ");


$stmt->bind_param(
    "i",
    $location_id
);


$stmt->execute();


$result =
    $stmt->get_result();


if ($result->num_rows === 0) {

    header(
        "Location: index.php?error=notfound"
    );

    exit();

}


$location =
    $result->fetch_assoc();


$stmt->close();


$error = "";


/* =====================================================
   XỬ LÝ CẬP NHẬT
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name =
        trim($_POST['name'] ?? '');

    $category_id =
        (int)($_POST['category_id'] ?? 0);

    $address =
        trim($_POST['address'] ?? '');

    $latitudeRaw =
        trim($_POST['latitude'] ?? '');

    $longitudeRaw =
        trim($_POST['longitude'] ?? '');

    $description =
        trim($_POST['description'] ?? '');

    $phone =
        trim($_POST['phone'] ?? '');

    $opening_hours =
        trim($_POST['opening_hours'] ?? '');

    $status =
        $_POST['status'] ?? 'active';


    /* =================================================
       VALIDATION
    ================================================= */

    if ($name === '') {

        $error =
            "Vui lòng nhập tên địa điểm.";

    }

    elseif ($category_id <= 0) {

        $error =
            "Vui lòng chọn loại địa điểm.";

    }

    elseif ($address === '') {

        $error =
            "Vui lòng nhập địa chỉ.";

    }

    elseif (
        $latitudeRaw === ''
        ||
        $longitudeRaw === ''
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
       STATUS
    ================================================= */

    if (
        !in_array(
            $status,
            [
                'active',
                'inactive'
            ],
            true
        )
    ) {

        $status =
            'active';

    }


    /* =================================================
       UPDATE
    ================================================= */

    if ($error === '') {

        $sql = "
            UPDATE locations

            SET
                category_id = ?,
                name = ?,
                address = ?,
                latitude = ?,
                longitude = ?,
                description = ?,
                phone = ?,
                opening_hours = ?,
                status = ?

            WHERE location_id = ?
        ";


        $stmt =
            $conn->prepare($sql);


        if (!$stmt) {

            $error =
                "Không thể chuẩn bị dữ liệu cập nhật.";

        }

        else {

            $stmt->bind_param(
                "issddssssi",
                $category_id,
                $name,
                $address,
                $latitude,
                $longitude,
                $description,
                $phone,
                $opening_hours,
                $status,
                $location_id
            );


            if ($stmt->execute()) {

                header(
                    "Location: index.php?updated=1"
                );

                exit();

            }

            else {

                $error =
                    "Không thể cập nhật địa điểm. Vui lòng thử lại.";

            }


            $stmt->close();

        }

    }


    /* =================================================
       GIỮ DỮ LIỆU KHI CÓ LỖI
    ================================================= */

    $location['name'] =
        $name;

    $location['category_id'] =
        $category_id;

    $location['address'] =
        $address;

    $location['latitude'] =
        $latitudeRaw;

    $location['longitude'] =
        $longitudeRaw;

    $location['description'] =
        $description;

    $location['phone'] =
        $phone;

    $location['opening_hours'] =
        $opening_hours;

    $location['status'] =
        $status;

}


/* =====================================================
   DANH MỤC
===================================================== */

$categories =
    $conn->query("
        SELECT
            category_id,
            category_name
        FROM categories
        ORDER BY category_name ASC
    ");


if (!$categories) {

    die(
        "Không thể tải danh mục: "
        . $conn->error
    );

}


/* =====================================================
   KIỂM TRA TỌA ĐỘ HIỆN TẠI
===================================================== */

$currentLatitude =
    is_numeric(
        $location['latitude'] ?? null
    )
    ? (float)$location['latitude']
    : 0;


$currentLongitude =
    is_numeric(
        $location['longitude'] ?? null
    )
    ? (float)$location['longitude']
    : 0;


$hasValidPosition =
    $currentLatitude >= -90
    &&
    $currentLatitude <= 90
    &&
    $currentLongitude >= -180
    &&
    $currentLongitude <= 180
    &&
    !(
        abs($currentLatitude) < 0.00000001
        &&
        abs($currentLongitude) < 0.00000001
    );

?>

<!DOCTYPE html>

<html lang="vi">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>Sửa địa điểm</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
>


<style>

body {

    background: #f4f6f8;

}


.main-card {

    border: none;

    border-radius: 15px;

    overflow: hidden;

}


#locationMap {

    width: 100%;

    height: 450px;

    border-radius: 10px;

    border: 1px solid #ddd;

}


#locationMap.map-error {

    border: 2px solid #dc3545;

}


#positionSuccess {

    display: none;

}


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
        bg-warning
        p-3
    "
>

<h4 class="mb-0">

    ✏️ SỬA ĐỊA ĐIỂM

</h4>

</div>


<div class="card-body p-4">


<?php if (!$hasValidPosition): ?>

<div class="alert alert-warning">

<strong>

    ⚠️ Địa điểm này chưa có vị trí hợp lệ.

</strong>

<br>

Hãy chấm vị trí chính xác trên bản đồ
trước khi cập nhật.

</div>

<?php endif; ?>


<form
    method="POST"
    id="locationForm"
    novalidate
>


<!-- TÊN -->

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
        $location['name'] ?? '',
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>

</div>



<!-- DANH MỤC -->

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

        (int)$category['category_id']
        ===
        (int)$location['category_id']

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



<!-- ĐỊA CHỈ -->

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
        $location['address'] ?? '',
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>

</div>



<!-- BẢN ĐỒ -->

<div class="mb-3">


<label class="form-label fw-bold">

    📍 Vị trí trên bản đồ
    <span class="text-danger">*</span>

</label>


<?php if ($hasValidPosition): ?>

<p class="text-muted mb-2">

    Marker đang hiển thị vị trí hiện tại.
    Nhấn vào bản đồ nếu muốn thay đổi vị trí.

</p>

<?php else: ?>

<p class="text-danger fw-semibold mb-2">

    Địa điểm chưa có vị trí.
    Hãy nhấn vào bản đồ để chọn vị trí.

</p>

<?php endif; ?>


<div
    id="locationMap"
    class="<?= !$hasValidPosition
        ? 'map-error'
        : ''
    ?>"
></div>


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



<!-- TỌA ĐỘ -->

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
    value="<?= $hasValidPosition
        ? htmlspecialchars(
            number_format(
                $currentLatitude,
                8,
                '.',
                ''
            )
        )
        : ''
    ?>"
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
    value="<?= $hasValidPosition
        ? htmlspecialchars(
            number_format(
                $currentLongitude,
                8,
                '.',
                ''
            )
        )
        : ''
    ?>"
    readonly
>

</div>


</div>



<!-- MÔ TẢ -->

<div class="mb-3">

<label class="form-label">

    Mô tả

</label>


<textarea
    name="description"
    class="form-control"
    rows="5"
><?= htmlspecialchars(
    $location['description'] ?? '',
    ENT_QUOTES,
    'UTF-8'
) ?></textarea>

</div>



<!-- PHONE -->

<div class="mb-3">

<label class="form-label">

    Số điện thoại

</label>


<input
    type="text"
    name="phone"
    class="form-control"
    value="<?= htmlspecialchars(
        $location['phone'] ?? '',
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>

</div>



<!-- OPENING HOURS -->

<div class="mb-3">

<label class="form-label">

    Thời gian hoạt động

</label>


<input
    type="text"
    name="opening_hours"
    class="form-control"
    value="<?= htmlspecialchars(
        $location['opening_hours'] ?? '',
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    placeholder="Ví dụ: 07:00 - 17:00"
>

</div>



<!-- TRẠNG THÁI -->

<div class="mb-4">

<label class="form-label">

    Trạng thái

</label>


<select
    name="status"
    class="form-select"
>


<option
    value="active"
    <?=

        ($location['status'] ?? '')
        === 'active'

        ? 'selected'
        : ''

    ?>
>

    Hiển thị

</option>


<option
    value="inactive"
    <?=

        ($location['status'] ?? '')
        === 'inactive'

        ? 'selected'
        : ''

    ?>
>

    Ẩn

</option>


</select>

</div>



<!-- BUTTON -->

<button
    type="submit"
    class="btn btn-warning"
>

    💾 Cập nhật

</button>


<a
    href="index.php"
    class="btn btn-secondary"
>

    Hủy

</a>


</form>


</div>

</div>

</div>



<!-- =====================================================
     MODAL THÔNG BÁO
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



<!-- LEAFLET -->

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
></script>


<!-- BOOTSTRAP -->

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


    const modal =
        bootstrap.Modal
        .getOrCreateInstance(

            document.getElementById(
                "messageModal"
            )

        );


    modal.show();

}



/* =====================================================
   LỖI TỪ PHP
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
   VỊ TRÍ
===================================================== */

const hasValidPosition =
    <?= $hasValidPosition
        ? 'true'
        : 'false'
    ?>;


const oldLatitude =
    <?= json_encode(
        $currentLatitude
    ) ?>;


const oldLongitude =
    <?= json_encode(
        $currentLongitude
    ) ?>;


const defaultLatitude =
    10.4938;


const defaultLongitude =
    105.6882;



/* =====================================================
   MAP
===================================================== */

const locationMap =
    L.map(
        "locationMap"
    );


if (hasValidPosition) {

    locationMap.setView(

        [
            oldLatitude,
            oldLongitude
        ],

        16

    );

}

else {

    locationMap.setView(

        [
            defaultLatitude,
            defaultLongitude
        ],

        12

    );

}


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

let positionSelected =
    hasValidPosition;


if (hasValidPosition) {

    selectedMarker =
        L.marker(

            [
                oldLatitude,
                oldLongitude
            ]

        )
        .addTo(locationMap)
        .bindPopup(
            "<strong>📍 Vị trí hiện tại</strong>"
        );

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
   VALIDATION
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