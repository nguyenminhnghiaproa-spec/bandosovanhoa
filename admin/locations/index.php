<?php

require_once("../auth.php");
require_once("../../config/database.php");


/* =====================================================
   LẤY DANH SÁCH ĐỊA ĐIỂM
===================================================== */

$sql = "
    SELECT
        l.location_id,
        l.name,
        l.address,
        l.latitude,
        l.longitude,
        l.status,
        c.category_name

    FROM locations l

    LEFT JOIN categories c
        ON l.category_id = c.category_id

    ORDER BY l.location_id DESC
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
   THÔNG BÁO
===================================================== */

$toastType = "";
$toastTitle = "";
$toastMessage = "";


/* CẬP NHẬT THÀNH CÔNG */

if (isset($_GET['updated'])) {

    $toastType = "success";

    $toastTitle =
        "Cập nhật thành công";

    $toastMessage =
        "Thông tin địa điểm đã được cập nhật.";

}


/* XÓA THÀNH CÔNG */

if (isset($_GET['deleted'])) {

    $toastType = "success";

    $toastTitle =
        "Xóa thành công";

    $toastMessage =
        "Địa điểm đã được xóa khỏi hệ thống.";

}


/* XỬ LÝ LỖI */

if (isset($_GET['error'])) {

    $toastType = "danger";

    $toastTitle =
        "Có lỗi xảy ra";


    switch ($_GET['error']) {

        case 'notfound':

            $toastMessage =
                "Không tìm thấy địa điểm.";

            break;


        case 'invalid':

            $toastMessage =
                "Mã địa điểm không hợp lệ.";

            break;


        case 'database':

            $toastMessage =
                "Có lỗi khi xử lý dữ liệu.";

            break;


        case 'delete':

            $toastMessage =
                "Không thể xóa địa điểm. Có thể địa điểm đang có dữ liệu liên quan.";

            break;


        default:

            $toastMessage =
                "Không thể thực hiện thao tác.";

            break;

    }

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

<title>
    Quản lý địa điểm
</title>

<?php

$currentAdminPage = 'locations';

require_once("../includes/navbar.php");

?>


<!-- =====================================================
     BOOTSTRAP
===================================================== -->

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<style>

/* =====================================================
   BODY
===================================================== */

body {

    background: #f4f6f8;

}


/* =====================================================
   TIÊU ĐỀ
===================================================== */

.page-title {

    font-weight: 700;

}


/* =====================================================
   CARD
===================================================== */

.location-card {

    border: none;

    border-radius: 15px;

    overflow: hidden;

}


/* =====================================================
   TABLE
===================================================== */

.table {

    margin-bottom: 0;

}


.table thead th {

    white-space: nowrap;

    vertical-align: middle;

}


.table tbody td {

    vertical-align: middle;

}


/* =====================================================
   BUTTON
===================================================== */

.action-buttons {

    display: flex;

    flex-wrap: wrap;

    gap: 5px;

}


/* =====================================================
   MODAL XÓA
===================================================== */

.delete-modal-content {

    border: none;

    border-radius: 20px;

    overflow: hidden;

}


.delete-icon {

    width: 80px;

    height: 80px;

    margin:
        0 auto
        18px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 38px;

    background: #fff0f0;

}


.delete-location-name {

    background: #f8f9fa;

    border-radius: 10px;

    padding: 10px 15px;

    font-weight: 700;

    margin-bottom: 15px;

}


/* =====================================================
   TOAST
===================================================== */

.toast-container {

    z-index: 2000;

}


.custom-toast {

    min-width: 330px;

    border: none;

    border-radius: 14px;

    overflow: hidden;

    box-shadow:
        0 8px 30px
        rgba(0,0,0,0.18);

}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 767px) {

    .page-header {

        align-items: flex-start !important;

        flex-direction: column;

        gap: 15px;

    }


    .custom-toast {

        min-width: auto;

        width: calc(100vw - 30px);

    }

}

</style>

</head>


<body>



<!-- =====================================================
     TOAST THÔNG BÁO
===================================================== -->

<?php if ($toastMessage !== ""): ?>

<div
    class="
        toast-container
        position-fixed
        top-0
        end-0
        p-3
    "
>

<div
    id="systemToast"
    class="
        toast
        custom-toast
        align-items-center
        text-bg-<?= htmlspecialchars($toastType) ?>
    "
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
>

<div class="d-flex">


<div class="toast-body">

<div class="fw-bold mb-1">

<?php if ($toastType === 'success'): ?>

    ✅

<?php else: ?>

    ⚠️

<?php endif; ?>

<?= htmlspecialchars(
    $toastTitle,
    ENT_QUOTES,
    'UTF-8'
) ?>

</div>


<div>

<?= htmlspecialchars(
    $toastMessage,
    ENT_QUOTES,
    'UTF-8'
) ?>

</div>

</div>


<button
    type="button"
    class="
        btn-close
        btn-close-white
        me-2
        m-auto
    "
    data-bs-dismiss="toast"
    aria-label="Đóng"
></button>


</div>

</div>

</div>

<?php endif; ?>



<!-- =====================================================
     NỘI DUNG
===================================================== -->

<div class="container py-5">


<!-- =====================================================
     HEADER
===================================================== -->

<div
    class="
        page-header
        d-flex
        justify-content-between
        align-items-center
        mb-4
    "
>

<div>

<h2 class="page-title mb-1">

    QUẢN LÝ ĐỊA ĐIỂM

</h2>


<p class="text-muted mb-0">

    Danh sách các địa điểm
    trên bản đồ số

</p>

</div>


<a
    href="add.php"
    class="btn btn-success"
>
    ＋ Thêm địa điểm
</a>

</div>



<!-- =====================================================
     BẢNG ĐỊA ĐIỂM
===================================================== -->

<div
    class="
        card
        location-card
        shadow-sm
    "
>

<div class="card-body">

<div class="table-responsive">


<table
    class="
        table
        table-bordered
        table-hover
        align-middle
    "
>


<thead class="table-success">

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
    Tọa độ
</th>

<th>
    Trạng thái
</th>

<th width="210">
    Thao tác
</th>

</tr>

</thead>



<tbody>


<?php

$stt = 1;

?>


<?php while (
    $row =
        $result->fetch_assoc()
): ?>


<tr>


<!-- STT -->

<td>

    <?= $stt++ ?>

</td>



<!-- TÊN -->

<td>

<strong>

<?= htmlspecialchars(
    $row['name'],
    ENT_QUOTES,
    'UTF-8'
) ?>

</strong>

</td>



<!-- DANH MỤC -->

<td>

<?= htmlspecialchars(
    $row['category_name']
    ?? 'Chưa phân loại',
    ENT_QUOTES,
    'UTF-8'
) ?>

</td>



<!-- ĐỊA CHỈ -->

<td>

<?= htmlspecialchars(
    $row['address']
    ?? '',
    ENT_QUOTES,
    'UTF-8'
) ?>

</td>



<!-- TỌA ĐỘ -->

<td>

<small>

<?= htmlspecialchars(
    $row['latitude']
    ?? ''
) ?>

<br>

<?= htmlspecialchars(
    $row['longitude']
    ?? ''
) ?>

</small>

</td>



<!-- TRẠNG THÁI -->

<td>


<?php if (
    $row['status']
    === 'active'
): ?>


<span
    class="
        badge
        bg-success
    "
>
    Hiển thị
</span>


<?php else: ?>


<span
    class="
        badge
        bg-secondary
    "
>
    Đã ẩn
</span>


<?php endif; ?>


</td>



<!-- =================================================
     THAO TÁC
================================================== -->

<td>


<div class="action-buttons">


<!-- XEM -->

<a
    href="../../location_detail.php?id=<?= (int)$row['location_id'] ?>"
    class="
        btn
        btn-info
        btn-sm
    "
    target="_blank"
>
    👁 Xem
</a>



<!-- ẢNH -->

<a
    href="images.php?id=<?= (int)$row['location_id'] ?>"
    class="
        btn
        btn-primary
        btn-sm
    "
>
    📷 Ảnh
</a>



<!-- QR -->

<a
    href="qr.php?id=<?= (int)$row['location_id'] ?>"
    class="
        btn
        btn-dark
        btn-sm
    "
>
    📱 QR
</a>



<!-- SỬA -->

<a
    href="edit.php?id=<?= (int)$row['location_id'] ?>"
    class="
        btn
        btn-warning
        btn-sm
    "
>
    ✏️ Sửa
</a>



<!-- XÓA -->

<button
    type="button"
    class="
        btn
        btn-danger
        btn-sm
    "
    onclick='openDeleteModal(
        <?= (int)$row["location_id"] ?>,
        <?= json_encode(
            $row["name"],
            JSON_UNESCAPED_UNICODE |
            JSON_HEX_TAG |
            JSON_HEX_APOS |
            JSON_HEX_QUOT |
            JSON_HEX_AMP
        ) ?>
    )'
>
    🗑 Xóa
</button>


</div>


</td>


</tr>


<?php endwhile; ?>



<!-- =====================================================
     KHÔNG CÓ DỮ LIỆU
===================================================== -->

<?php if (
    $result->num_rows === 0
): ?>


<tr>

<td
    colspan="7"
    class="
        text-center
        text-muted
        py-5
    "
>

<div style="font-size:40px;">
    📍
</div>

<div class="mt-2">

    Chưa có địa điểm nào.

</div>

</td>

</tr>


<?php endif; ?>


</tbody>


</table>


</div>

</div>

</div>



<!-- =====================================================
     NÚT XEM BẢN ĐỒ
===================================================== -->

<div class="mt-3">

<a
    href="../../map.php"
    class="
        btn
        btn-outline-success
    "
    target="_blank"
>
    🗺 Xem bản đồ
</a>

</div>


</div>



<!-- =====================================================
     MODAL XÁC NHẬN XÓA
===================================================== -->

<div
    class="modal fade"
    id="deleteModal"
    tabindex="-1"
    aria-labelledby="deleteModalTitle"
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
        delete-modal-content
        shadow
    "
>


<div class="modal-body p-4">


<div class="text-center">


<div class="delete-icon">

    🗑️

</div>


<h4
    class="fw-bold mb-2"
    id="deleteModalTitle"
>

    Xác nhận xóa địa điểm

</h4>


<p class="text-muted mb-3">

    Bạn có chắc chắn muốn xóa
    địa điểm này không?

</p>


<div
    id="deleteLocationName"
    class="delete-location-name"
>
</div>


</div>



<div
    class="
        alert
        alert-warning
        mb-4
    "
>

<div class="fw-bold mb-1">

    ⚠️ Lưu ý

</div>

<div class="small">

    Hình ảnh, sự kiện và mã QR
    liên quan đến địa điểm này
    cũng có thể bị xóa.

</div>

</div>



<div
    class="
        d-flex
        justify-content-end
        gap-2
    "
>


<button
    type="button"
    class="
        btn
        btn-light
        px-4
    "
    data-bs-dismiss="modal"
>
    Hủy
</button>



<form
    action="delete.php"
    method="POST"
    id="deleteLocationForm"
>


<input
    type="hidden"
    name="id"
    id="deleteLocationId"
>


<button
    type="submit"
    class="
        btn
        btn-danger
        px-4
    "
>
    🗑 Xóa địa điểm
</button>


</form>


</div>


</div>


</div>


</div>


</div>



<!-- =====================================================
     BOOTSTRAP JS
===================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>



<script>

/* =====================================================
   MỞ MODAL XÓA
===================================================== */

function openDeleteModal(
    locationId,
    locationName
) {

    /* ID */

    document
        .getElementById(
            "deleteLocationId"
        )
        .value =
            locationId;


    /* TÊN ĐỊA ĐIỂM */

    document
        .getElementById(
            "deleteLocationName"
        )
        .textContent =
            locationName;


    /* MỞ MODAL */

    const modalElement =
        document.getElementById(
            "deleteModal"
        );


    const deleteModal =
        bootstrap.Modal
        .getOrCreateInstance(
            modalElement
        );


    deleteModal.show();

}



/* =====================================================
   TOAST
===================================================== */

document.addEventListener(
    "DOMContentLoaded",
    function() {

        const toastElement =
            document.getElementById(
                "systemToast"
            );


        if (toastElement) {

            const toast =
                new bootstrap.Toast(
                    toastElement,
                    {
                        delay: 3500,
                        autohide: true
                    }
                );


            toast.show();

        }

    }
);

</script>


</body>

</html>