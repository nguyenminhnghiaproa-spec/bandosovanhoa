<?php

require_once("../auth.php");
require_once("../../config/database.php");


/* =====================================================
   LẤY DANH SÁCH SỰ KIỆN
===================================================== */

$sql = "
    SELECT
        e.event_id,
        e.event_name,
        e.event_date,
        e.description,
        e.image,

        l.location_id,
        l.name AS location_name

    FROM events e

    INNER JOIN locations l
        ON e.location_id = l.location_id

    ORDER BY
        e.event_date DESC,
        e.event_id DESC
";


$result =
    $conn->query($sql);


if (!$result) {

    die(
        "Không thể tải danh sách sự kiện: "
        . $conn->error
    );

}


/* =====================================================
   THÔNG BÁO TOAST
===================================================== */

$toastType = "";
$toastTitle = "";
$toastMessage = "";


/* =====================================================
   THÊM THÀNH CÔNG
===================================================== */

if (
    isset($_GET['success'])
    &&
    $_GET['success'] === 'add'
) {

    $toastType = "success";

    $toastTitle =
        "Thêm thành công";

    $toastMessage =
        "Sự kiện mới đã được thêm vào hệ thống.";

}


/* =====================================================
   SỬA THÀNH CÔNG
===================================================== */

elseif (
    isset($_GET['success'])
    &&
    $_GET['success'] === 'edit'
) {

    $toastType = "success";

    $toastTitle =
        "Cập nhật thành công";

    $toastMessage =
        "Thông tin sự kiện đã được cập nhật.";

}


/* =====================================================
   XÓA THÀNH CÔNG
===================================================== */

elseif (
    isset($_GET['success'])
    &&
    $_GET['success'] === 'delete'
) {

    $toastType = "success";

    $toastTitle =
        "Xóa thành công";

    $toastMessage =
        "Sự kiện đã được xóa khỏi hệ thống.";

}


/* =====================================================
   XỬ LÝ LỖI
===================================================== */

if (isset($_GET['error'])) {

    $toastType = "danger";

    $toastTitle =
        "Có lỗi xảy ra";


    switch ($_GET['error']) {

        case 'invalid':

            $toastMessage =
                "Mã sự kiện không hợp lệ.";

            break;


        case 'notfound':

            $toastMessage =
                "Không tìm thấy sự kiện.";

            break;


        case 'database':

            $toastMessage =
                "Không thể xử lý dữ liệu sự kiện.";

            break;


        case 'delete':

            $toastMessage =
                "Không thể xóa sự kiện. Vui lòng thử lại.";

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
    Quản lý sự kiện
</title>


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

.event-card {

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

    vertical-align: middle;

    white-space: nowrap;

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


.delete-event-name {

    background: #f8f9fa;

    border-radius: 10px;

    padding: 12px 15px;

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
        rgba(0, 0, 0, 0.18);

}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 767px) {

    .page-header {

        flex-direction: column;

        align-items:
            flex-start !important;

        gap: 15px;

    }


    .custom-toast {

        min-width: auto;

        width:
            calc(100vw - 30px);

    }

}

</style>

</head>


<body>

<?php

$currentAdminPage = 'events';

require_once(
    "../includes/navbar.php"
);

?>

<!-- =====================================================
     TOAST
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
        text-bg-<?= htmlspecialchars(
            $toastType,
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    "
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
>


<div class="d-flex">


<div class="toast-body">


<div class="fw-bold mb-1">


<?php if (
    $toastType === 'success'
): ?>

    ✅

<?php else: ?>

    ❌

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
     THANH MENU QUẢN TRỊ - SỰ KIỆN
===================================================== -->

<?php

$currentAdminPage = 'events';

require_once(
    "../includes/navbar.php"
);

?>

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

    QUẢN LÝ SỰ KIỆN

</h2>


<p class="text-muted mb-0">

    Hoạt động văn hóa, lễ hội
    và sự kiện địa phương

</p>




</div>



<a
    href="add.php"
    class="btn btn-success"
>

    ＋ Thêm sự kiện

</a>


</div>





<!-- =====================================================
     DANH SÁCH SỰ KIỆN
===================================================== -->

<div
    class="
        card
        event-card
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

    Tên sự kiện

</th>


<th>

    Địa điểm

</th>


<th>

    Ngày tổ chức

</th>


<th width="200">

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


<!-- =================================================
     STT
================================================== -->

<td>

<?= $stt++ ?>

</td>



<!-- =================================================
     TÊN SỰ KIỆN
================================================== -->

<td>


<strong>

<?= htmlspecialchars(
    $row['event_name'],
    ENT_QUOTES,
    'UTF-8'
) ?>

</strong>


</td>



<!-- =================================================
     ĐỊA ĐIỂM
================================================== -->

<td>

<?= htmlspecialchars(
    $row['location_name'],
    ENT_QUOTES,
    'UTF-8'
) ?>

</td>



<!-- =================================================
     NGÀY
================================================== -->

<td>


<?php if (
    !empty($row['event_date'])
): ?>


<span class="badge bg-light text-dark border">

📅

<?= date(
    "d/m/Y",
    strtotime(
        $row['event_date']
    )
) ?>

</span>


<?php else: ?>


<span class="text-muted">

    Chưa xác định

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



<!-- SỬA -->

<a
    href="edit.php?id=<?= (int)$row['event_id'] ?>"
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
        <?= (int)$row["event_id"] ?>,
        <?= json_encode(
            $row["event_name"],
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
            KHÔNG CÓ SỰ KIỆN
        ===================================================== -->

        <?php if (
            $result->num_rows === 0
        ): ?>


        <tr>


        <td
            colspan="5"
            class="
                text-center
                text-muted
                py-5
            "
        >


        <div style="font-size: 42px;">

            🎉

        </div>


        <div class="mt-2">

            Chưa có sự kiện nào.

        </div>




        <a
            href="add.php"
            class="
                btn
                btn-success
                btn-sm
                mt-3
            "
        >

            ＋ Thêm sự kiện đầu tiên

        </a>


        </td>


        </tr>


        <?php endif; ?>


        </tbody>


        </table>


        </div>


        </div>


        </div>



<!-- =====================================================
     QUAY LẠI DASHBOARD
===================================================== -->



<a
    href="../../events.php"
    class="btn btn-outline-success"
    target="_blank"
>

    🎉 Xem trang sự kiện

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

    Xác nhận xóa sự kiện

</h4>


<p class="text-muted mb-3">

    Bạn có chắc chắn muốn xóa
    sự kiện này không?

</p>


<div
    id="deleteEventName"
    class="delete-event-name"
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

    Sau khi xóa, sự kiện sẽ không còn
    hiển thị trên trang thông tin địa điểm
    và trang sự kiện dành cho người dùng.

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



<!-- =================================================
     FORM XÓA POST
================================================== -->

<form
    action="delete.php"
    method="POST"
    id="deleteEventForm"
>


<input
    type="hidden"
    name="id"
    id="deleteEventId"
>


<button
    type="submit"
    class="
        btn
        btn-danger
        px-4
    "
>

    🗑 Xóa sự kiện

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
    eventId,
    eventName
) {

    document
        .getElementById(
            "deleteEventId"
        )
        .value =
            eventId;


    document
        .getElementById(
            "deleteEventName"
        )
        .textContent =
            eventName;


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