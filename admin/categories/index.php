<?php

require_once("../auth.php");
require_once("../../config/database.php");


/* =====================================================
   LẤY DANH SÁCH DANH MỤC
===================================================== */

$sql = "
    SELECT
        c.category_id,
        c.category_name,
        c.description,
        COUNT(l.location_id) AS total_locations

    FROM categories c

    LEFT JOIN locations l
        ON c.category_id = l.category_id

    GROUP BY
        c.category_id,
        c.category_name,
        c.description

    ORDER BY c.category_id DESC
";


$result = $conn->query($sql);


if (!$result) {

    die(
        "Không thể tải danh sách danh mục: "
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
   THÔNG BÁO THÀNH CÔNG
===================================================== */

if (isset($_GET["success"])) {

    switch ($_GET["success"]) {

        case "add":

            $toastType = "success";

            $toastTitle =
                "Thêm thành công";

            $toastMessage =
                "Danh mục mới đã được thêm vào hệ thống.";

            break;


        case "edit":

            $toastType = "success";

            $toastTitle =
                "Cập nhật thành công";

            $toastMessage =
                "Thông tin danh mục đã được cập nhật.";

            break;


        case "delete":

            $toastType = "success";

            $toastTitle =
                "Xóa thành công";

            $toastMessage =
                "Danh mục đã được xóa khỏi hệ thống.";

            break;

    }

}


/* =====================================================
   THÔNG BÁO LỖI
===================================================== */

if (isset($_GET["error"])) {

    $toastType = "danger";

    $toastTitle =
        "Có lỗi xảy ra";


    switch ($_GET["error"]) {

        case "used":

            $toastTitle =
                "Không thể xóa";

            $toastMessage =
                "Danh mục này đang có địa điểm sử dụng nên không thể xóa.";

            break;


        case "invalid":

            $toastMessage =
                "Mã danh mục không hợp lệ.";

            break;


        case "notfound":

            $toastMessage =
                "Không tìm thấy danh mục.";

            break;


        case "database":

            $toastMessage =
                "Không thể xử lý dữ liệu danh mục.";

            break;


        case "delete":

            $toastMessage =
                "Không thể xóa danh mục. Vui lòng thử lại.";

            break;


        default:

            $toastMessage =
                "Không thể thực hiện thao tác. Vui lòng thử lại.";

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
    Quản lý danh mục
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
   NAVBAR
===================================================== */

.navbar-brand {

    font-weight: 700;

}


/* =====================================================
   PAGE HEADER
===================================================== */

.page-title {

    font-weight: 700;

}


/* =====================================================
   CARD
===================================================== */

.category-card {

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
   ACTION BUTTONS
===================================================== */

.action-buttons {

    display: flex;

    flex-wrap: wrap;

    gap: 5px;

}


/* =====================================================
   BADGE
===================================================== */

.location-count {

    min-width: 40px;

    font-size: 13px;

}


/* =====================================================
   DELETE MODAL
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

    background: #fff0f0;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 38px;

}


.delete-category-name {

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
            "UTF-8"
        ) ?>
    "
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
>


<div class="d-flex">


<div class="toast-body">


<div class="fw-bold mb-1">


<?php if ($toastType === "success"): ?>

    ✅

<?php else: ?>

    ❌

<?php endif; ?>


<?= htmlspecialchars(
    $toastTitle,
    ENT_QUOTES,
    "UTF-8"
) ?>


</div>


<div>

<?= htmlspecialchars(
    $toastMessage,
    ENT_QUOTES,
    "UTF-8"
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
     THANH MENU QUẢN TRỊ
===================================================== -->

<?php

$currentAdminPage = 'categories';

require_once(
    "../includes/navbar.php"
);

?>



<!-- =====================================================
     CONTENT
===================================================== -->

<div class="container py-4">


<!-- =====================================================
     PAGE HEADER
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


<h3 class="page-title mb-1">

    🏷️ Quản lý danh mục

</h3>


<div class="text-muted">

    Quản lý các loại địa điểm trên bản đồ

</div>


</div>



<a
    href="add.php"
    class="btn btn-success"
>

    ➕ Thêm danh mục

</a>


</div>



<!-- =====================================================
     CARD
===================================================== -->

<div
    class="
        card
        category-card
        shadow-sm
    "
>


<div class="card-body">


<div class="table-responsive">


<table
    class="
        table
        table-hover
        align-middle
    "
>


<thead class="table-light">


<tr>


<th>

    STT

</th>


<th>

    Tên danh mục

</th>


<th>

    Mô tả

</th>


<th class="text-center">

    Số địa điểm

</th>


<th width="180">

    Thao tác

</th>


</tr>


</thead>



<tbody>


<?php if (
    $result->num_rows > 0
): ?>


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
     TÊN DANH MỤC
================================================== -->

<td>


<strong>

<?= htmlspecialchars(
    $row["category_name"],
    ENT_QUOTES,
    "UTF-8"
) ?>

</strong>


</td>



<!-- =================================================
     MÔ TẢ
================================================== -->

<td>


<?php if (
    !empty($row["description"])
): ?>


<?= htmlspecialchars(
    $row["description"],
    ENT_QUOTES,
    "UTF-8"
) ?>


<?php else: ?>


<span class="text-muted">

    Chưa có mô tả

</span>


<?php endif; ?>


</td>



<!-- =================================================
     SỐ ĐỊA ĐIỂM
================================================== -->

<td class="text-center">


<?php if (
    (int)$row["total_locations"] > 0
): ?>


<span
    class="
        badge
        bg-primary
        location-count
    "
>

<?= (int)$row["total_locations"] ?>

</span>


<?php else: ?>


<span
    class="
        badge
        bg-secondary
        location-count
    "
>

    0

</span>


<?php endif; ?>


</td>



<!-- =================================================
     THAO TÁC
================================================== -->

<td>


<div class="action-buttons">


<!-- SỬA -->

<a
    href="edit.php?id=<?= (int)$row["category_id"] ?>"
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
        <?= (int)$row["category_id"] ?>,
        <?= json_encode(
            $row["category_name"],
            JSON_UNESCAPED_UNICODE |
            JSON_HEX_TAG |
            JSON_HEX_APOS |
            JSON_HEX_QUOT |
            JSON_HEX_AMP
        ) ?>,
        <?= (int)$row["total_locations"] ?>
    )'
>

    🗑️ Xóa

</button>


</div>


</td>


</tr>


<?php endwhile; ?>


<?php else: ?>


<!-- =====================================================
     KHÔNG CÓ DANH MỤC
===================================================== -->

<tr>


<td
    colspan="5"
    class="
        text-center
        text-muted
        py-5
    "
>


<div style="font-size:42px;">

    🏷️

</div>


<div class="mt-2">

    Chưa có danh mục nào.

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

    ➕ Thêm danh mục đầu tiên

</a>


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


<div
    class="delete-icon"
    id="deleteIcon"
>

    🗑️

</div>


<h4
    class="fw-bold mb-2"
    id="deleteModalTitle"
>

    Xác nhận xóa danh mục

</h4>


<p
    class="text-muted mb-3"
    id="deleteMessage"
>

    Bạn có chắc chắn muốn xóa
    danh mục này không?

</p>


<div
    id="deleteCategoryName"
    class="delete-category-name"
>
</div>


</div>



<!-- =================================================
     CẢNH BÁO
================================================== -->

<div
    class="
        alert
        alert-warning
        mb-4
    "
    id="deleteWarning"
>


<div class="fw-bold mb-1">

    ⚠️ Lưu ý

</div>


<div
    class="small"
    id="deleteWarningText"
>

    Danh mục chỉ có thể xóa khi
    chưa có địa điểm nào sử dụng.

</div>


</div>



<!-- =================================================
     BUTTON
================================================== -->

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
    id="deleteCategoryForm"
>


<input
    type="hidden"
    name="id"
    id="deleteCategoryId"
>


<button
    type="submit"
    class="
        btn
        btn-danger
        px-4
    "
    id="confirmDeleteButton"
>

    🗑️ Xóa danh mục

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
    categoryId,
    categoryName,
    totalLocations
) {


    /* =============================================
       GÁN ID
    ============================================= */

    document
        .getElementById(
            "deleteCategoryId"
        )
        .value =
            categoryId;



    /* =============================================
       TÊN DANH MỤC
    ============================================= */

    document
        .getElementById(
            "deleteCategoryName"
        )
        .textContent =
            categoryName;



    const title =
        document.getElementById(
            "deleteModalTitle"
        );


    const message =
        document.getElementById(
            "deleteMessage"
        );


    const warning =
        document.getElementById(
            "deleteWarning"
        );


    const warningText =
        document.getElementById(
            "deleteWarningText"
        );


    const deleteButton =
        document.getElementById(
            "confirmDeleteButton"
        );



    /* =============================================
       DANH MỤC ĐANG ĐƯỢC SỬ DỤNG
    ============================================= */

    if (totalLocations > 0) {


        title.textContent =
            "Không thể xóa danh mục";


        message.textContent =
            "Danh mục này hiện đang được sử dụng.";


        warning.className =
            "alert alert-danger mb-4";


        warningText.textContent =
            "Hiện có "
            +
            totalLocations
            +
            " địa điểm đang sử dụng danh mục này. "
            +
            "Bạn cần chuyển hoặc xóa các địa điểm đó trước.";


        deleteButton.style.display =
            "none";

    }


    /* =============================================
       DANH MỤC CÓ THỂ XÓA
    ============================================= */

    else {


        title.textContent =
            "Xác nhận xóa danh mục";


        message.textContent =
            "Bạn có chắc chắn muốn xóa danh mục này không?";


        warning.className =
            "alert alert-warning mb-4";


        warningText.textContent =
            "Danh mục sau khi xóa sẽ không thể khôi phục.";


        deleteButton.style.display =
            "inline-block";

    }



    /* =============================================
       MỞ MODAL
    ============================================= */

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
   HIỂN THỊ TOAST
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