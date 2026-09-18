<?php

require_once("../auth.php");
require_once("../../config/database.php");


/* =====================================================
   LẤY ID DANH MỤC
===================================================== */

$category_id =
    isset($_GET["id"])
    ? (int)$_GET["id"]
    : 0;


if ($category_id <= 0) {

    header(
        "Location: index.php?error=invalid"
    );

    exit();

}


/* =====================================================
   LẤY THÔNG TIN DANH MỤC
===================================================== */

$stmt =
    $conn->prepare("
        SELECT
            category_id,
            category_name,
            description

        FROM categories

        WHERE category_id = ?

        LIMIT 1
    ");


if (!$stmt) {

    header(
        "Location: index.php?error=database"
    );

    exit();

}


$stmt->bind_param(
    "i",
    $category_id
);


if (!$stmt->execute()) {

    $stmt->close();

    header(
        "Location: index.php?error=database"
    );

    exit();

}


$result =
    $stmt->get_result();


if ($result->num_rows === 0) {

    $stmt->close();

    header(
        "Location: index.php?error=notfound"
    );

    exit();

}


$category =
    $result->fetch_assoc();


$stmt->close();


/* =====================================================
   BIẾN FORM
===================================================== */

$error = "";


$category_name =
    $category["category_name"] ?? "";


$description =
    $category["description"] ?? "";


/* =====================================================
   XỬ LÝ CẬP NHẬT
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /* =================================================
       LẤY DỮ LIỆU
    ================================================= */

    $category_name =
        trim(
            $_POST["category_name"]
            ?? ""
        );


    $description =
        trim(
            $_POST["description"]
            ?? ""
        );


    /* =================================================
       KIỂM TRA TÊN
    ================================================= */

    if ($category_name === "") {

        $error =
            "Vui lòng nhập tên danh mục.";

    }


    /* =================================================
       KIỂM TRA TRÙNG TÊN
    ================================================= */

    else {

        $check =
            $conn->prepare("
                SELECT category_id

                FROM categories

                WHERE category_name = ?
                AND category_id != ?

                LIMIT 1
            ");


        if (!$check) {

            $error =
                "Không thể kiểm tra tên danh mục.";

        }

        else {

            $check->bind_param(
                "si",
                $category_name,
                $category_id
            );


            if (!$check->execute()) {

                $error =
                    "Không thể kiểm tra tên danh mục.";

            }

            else {

                $checkResult =
                    $check->get_result();


                if (
                    $checkResult->num_rows > 0
                ) {

                    $error =
                        "Tên danh mục này đã tồn tại.";

                }

            }


            $check->close();

        }

    }


    /* =================================================
       CẬP NHẬT DANH MỤC
    ================================================= */

    if ($error === "") {

        $update =
            $conn->prepare("
                UPDATE categories

                SET
                    category_name = ?,
                    description = ?

                WHERE category_id = ?
            ");


        if (!$update) {

            $error =
                "Không thể chuẩn bị dữ liệu cập nhật.";

        }

        else {

            $update->bind_param(
                "ssi",
                $category_name,
                $description,
                $category_id
            );


            if ($update->execute()) {

                $update->close();


                header(
                    "Location: index.php?success=edit"
                );


                exit();

            }

            else {

                $error =
                    "Không thể cập nhật danh mục. Vui lòng thử lại.";

            }


            $update->close();

        }

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
    Sửa danh mục
</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<style>

body {

    background: #f4f6f8;

}


.main-card {

    max-width: 700px;

    margin: 0 auto;

    border: none;

    border-radius: 15px;

    overflow: hidden;

}


.form-label {

    font-weight: 500;

}


/* =====================================================
   MODAL
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

</style>

</head>


<body>

<?php

$currentAdminPage = 'categories';

require_once(
    "../includes/navbar.php"
);

?>

<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar navbar-dark bg-success">


<div class="container-fluid">


<a
    href="../index.php"
    class="navbar-brand fw-bold"
>

    🗺️ QUẢN TRỊ BẢN ĐỒ SỐ

</a>


<a
    href="index.php"
    class="btn btn-outline-light btn-sm"
>

    ← Danh sách danh mục

</a>


</div>


</nav>



<!-- =====================================================
     NỘI DUNG
===================================================== -->

<div class="container py-5">


<div class="card main-card shadow-sm">


<!-- =====================================================
     HEADER
===================================================== -->

<div class="card-header bg-warning p-3">


<h4 class="mb-0">

    ✏️ SỬA DANH MỤC

</h4>


</div>



<!-- =====================================================
     BODY
===================================================== -->

<div class="card-body p-4">


<form
    method="POST"
    id="categoryForm"
    novalidate
>


<!-- =====================================================
     TÊN DANH MỤC
===================================================== -->

<div class="mb-3">


<label class="form-label">

    Tên danh mục

    <span class="text-danger">
        *
    </span>

</label>


<input
    type="text"
    name="category_name"
    id="category_name"
    class="form-control"

    value="<?= htmlspecialchars(
        $category_name,
        ENT_QUOTES,
        "UTF-8"
    ) ?>"

    placeholder="Ví dụ: Làng nghề"
>


</div>



<!-- =====================================================
     MÔ TẢ
===================================================== -->

<div class="mb-4">


<label class="form-label">

    Mô tả

</label>


<textarea
    name="description"
    id="description"
    class="form-control"
    rows="5"
    placeholder="Nhập mô tả danh mục..."
><?= htmlspecialchars(
    $description,
    ENT_QUOTES,
    "UTF-8"
) ?></textarea>


</div>



<!-- =====================================================
     BUTTON
===================================================== -->

<div class="d-flex gap-2">


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

    ← Quay lại

</a>


</div>


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


<div class="modal-dialog modal-dialog-centered">


<div class="modal-content message-modal shadow">


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
    class="message-text text-muted mb-4"
>
</p>


<button
    type="button"
    class="btn btn-warning px-4"
    data-bs-dismiss="modal"
>

    Đã hiểu

</button>


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
   HÀM HIỂN THỊ THÔNG BÁO
===================================================== */

function showMessage(message) {

    const messageElement =
        document.getElementById(
            "messageModalText"
        );


    messageElement.textContent =
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
   HIỂN THỊ LỖI TỪ PHP
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
   KIỂM TRA FORM
===================================================== */

document
    .getElementById(
        "categoryForm"
    )
    .addEventListener(
        "submit",
        function(event) {


            const categoryName =
                document
                    .getElementById(
                        "category_name"
                    )
                    .value
                    .trim();


            if (categoryName === "") {

                event.preventDefault();


                showMessage(
                    "Vui lòng nhập tên danh mục."
                );


                return;

            }

        }
    );

</script>


</body>

</html>