<?php

require_once("../auth.php");
require_once("../../config/database.php");


/* =====================================================
   KIỂM TRA ID SỰ KIỆN
===================================================== */

$event_id =
    isset($_GET["id"])
    ? (int)$_GET["id"]
    : 0;


if ($event_id <= 0) {

    header(
        "Location: index.php?error=invalid"
    );

    exit();

}


/* =====================================================
   LẤY THÔNG TIN SỰ KIỆN
===================================================== */

$stmt =
    $conn->prepare("
        SELECT *
        FROM events
        WHERE event_id = ?
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
    $event_id
);


$stmt->execute();


$result =
    $stmt->get_result();


if ($result->num_rows === 0) {

    $stmt->close();

    header(
        "Location: index.php?error=notfound"
    );

    exit();

}


$event =
    $result->fetch_assoc();


$stmt->close();


/* =====================================================
   BIẾN FORM
===================================================== */

$error = "";


$location_id =
    (int)($event["location_id"] ?? 0);


$event_name =
    $event["event_name"] ?? "";


$event_date =
    $event["event_date"] ?? "";


$description =
    $event["description"] ?? "";


/* =====================================================
   XỬ LÝ CẬP NHẬT
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /* =================================================
       LẤY DỮ LIỆU
    ================================================= */

    $location_id =
        (int)(
            $_POST["location_id"]
            ?? 0
        );


    $event_name =
        trim(
            $_POST["event_name"]
            ?? ""
        );


    $event_date =
        trim(
            $_POST["event_date"]
            ?? ""
        );


    $description =
        trim(
            $_POST["description"]
            ?? ""
        );


    /* =================================================
       KIỂM TRA ĐỊA ĐIỂM
    ================================================= */

    if ($location_id <= 0) {

        $error =
            "Vui lòng chọn địa điểm tổ chức.";

    }


    /* =================================================
       KIỂM TRA TÊN SỰ KIỆN
    ================================================= */

    elseif ($event_name === "") {

        $error =
            "Vui lòng nhập tên sự kiện.";

    }


    /* =================================================
       KIỂM TRA NGÀY TỔ CHỨC
       
       Phần Thêm đang bắt buộc ngày,
       nên phần Sửa cũng thống nhất bắt buộc ngày.
    ================================================= */

    elseif ($event_date === "") {

        $error =
            "Vui lòng chọn ngày tổ chức sự kiện.";

    }


    /* =================================================
       KIỂM TRA ĐỊA ĐIỂM CÓ TỒN TẠI
    ================================================= */

    else {

        $checkLocation =
            $conn->prepare("
                SELECT location_id
                FROM locations
                WHERE location_id = ?
                  AND status = 'active'
                LIMIT 1
            ");


        if (!$checkLocation) {

            $error =
                "Không thể kiểm tra địa điểm tổ chức.";

        }

        else {

            $checkLocation->bind_param(
                "i",
                $location_id
            );


            if (
                !$checkLocation->execute()
            ) {

                $error =
                    "Không thể kiểm tra địa điểm tổ chức.";

            }

            else {

                $locationResult =
                    $checkLocation
                        ->get_result();


                if (
                    $locationResult->num_rows
                    === 0
                ) {

                    $error =
                        "Địa điểm tổ chức không tồn tại hoặc đã bị ẩn.";

                }

            }


            $checkLocation->close();

        }

    }


    /* =================================================
       CẬP NHẬT DATABASE
    ================================================= */

    if ($error === "") {

        $update =
            $conn->prepare("
                UPDATE events

                SET
                    location_id = ?,
                    event_name = ?,
                    event_date = ?,
                    description = ?

                WHERE event_id = ?
            ");


        if (!$update) {

            $error =
                "Không thể chuẩn bị dữ liệu cập nhật.";

        }

        else {

            $update->bind_param(
                "isssi",
                $location_id,
                $event_name,
                $event_date,
                $description,
                $event_id
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
                    "Không thể cập nhật sự kiện. Vui lòng thử lại.";

            }


            $update->close();

        }

    }

}


/* =====================================================
   LẤY DANH SÁCH ĐỊA ĐIỂM
===================================================== */

$locations =
    $conn->query("
        SELECT
            location_id,
            name

        FROM locations

        WHERE status = 'active'

        ORDER BY name ASC
    ");


if (!$locations) {

    die(
        "Không thể tải danh sách địa điểm: "
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

<title>
    Sửa sự kiện
</title>


<!-- =====================================================
     BOOTSTRAP CSS
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
   CARD
===================================================== */

.main-card {

    max-width: 800px;

    margin: 0 auto;

    border: none;

    border-radius: 15px;

    overflow: hidden;

}


/* =====================================================
   FORM
===================================================== */

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

    margin:
        0 auto
        18px;

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

$currentAdminPage = 'events';

require_once(
    "../includes/navbar.php"
);

?>


<nav
    class="
        navbar
        navbar-dark
        bg-success
    "
>


<div class="container-fluid">


<a
    href="../index.php"
    class="navbar-brand"
>

    🗺️ QUẢN TRỊ BẢN ĐỒ SỐ

</a>


<a
    href="index.php"
    class="
        btn
        btn-outline-light
        btn-sm
    "
>

    ← Danh sách sự kiện

</a>


</div>


</nav>



<!-- =====================================================
     NỘI DUNG
===================================================== -->

<div class="container py-5">


<div
    class="
        card
        main-card
        shadow-sm
    "
>


<!-- =====================================================
     HEADER
===================================================== -->

<div
    class="
        card-header
        bg-warning
        p-3
    "
>


<h4 class="mb-0">

    ✏️ SỬA SỰ KIỆN

</h4>


</div>



<!-- =====================================================
     BODY
===================================================== -->

<div class="card-body p-4">


<form
    method="POST"
    id="eventForm"
    novalidate
>


<!-- =====================================================
     ĐỊA ĐIỂM
===================================================== -->

<div class="mb-3">


<label class="form-label">

    Địa điểm tổ chức

    <span class="text-danger">
        *
    </span>

</label>


<select
    name="location_id"
    id="location_id"
    class="form-select"
>


<option value="">

    -- Chọn địa điểm --

</option>


<?php while (
    $location =
        $locations->fetch_assoc()
): ?>


<option
    value="<?= (int)$location["location_id"] ?>"

    <?=

        $location_id
        ===
        (int)$location["location_id"]

        ? "selected"
        : ""

    ?>
>


<?= htmlspecialchars(
    $location["name"],
    ENT_QUOTES,
    "UTF-8"
) ?>


</option>


<?php endwhile; ?>


</select>


</div>



<!-- =====================================================
     TÊN SỰ KIỆN
===================================================== -->

<div class="mb-3">


<label class="form-label">

    Tên sự kiện

    <span class="text-danger">
        *
    </span>

</label>


<input
    type="text"
    name="event_name"
    id="event_name"
    class="form-control"

    value="<?= htmlspecialchars(
        $event_name,
        ENT_QUOTES,
        "UTF-8"
    ) ?>"

    placeholder="Ví dụ: Ngày hội Văn hóa - Du lịch"
>


</div>



<!-- =====================================================
     NGÀY TỔ CHỨC
===================================================== -->

<div class="mb-3">


<label class="form-label">

    Ngày tổ chức

    <span class="text-danger">
        *
    </span>

</label>


<input
    type="date"
    name="event_date"
    id="event_date"
    class="form-control"

    value="<?= htmlspecialchars(
        $event_date,
        ENT_QUOTES,
        "UTF-8"
    ) ?>"
>


</div>



<!-- =====================================================
     MÔ TẢ
===================================================== -->

<div class="mb-4">


<label class="form-label">

    Mô tả sự kiện

</label>


<textarea
    name="description"
    id="description"
    class="form-control"
    rows="6"
    placeholder="Nhập nội dung sự kiện..."
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


<!-- ICON -->

<div class="message-icon">

    ⚠️

</div>



<!-- TITLE -->

<h4
    class="fw-bold mb-3"
    id="messageModalTitle"
>

    Thông báo

</h4>



<!-- MESSAGE -->

<p
    id="messageModalText"
    class="
        message-text
        text-muted
        mb-4
    "
>
</p>



<!-- BUTTON -->

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
     BOOTSTRAP JS
===================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>


<script>

/* =====================================================
   HÀM HIỂN THỊ MODAL
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
   KIỂM TRA FORM
===================================================== */

document
.getElementById(
    "eventForm"
)
.addEventListener(
    "submit",
    function(event) {


        const locationId =
            document
                .getElementById(
                    "location_id"
                )
                .value;


        const eventName =
            document
                .getElementById(
                    "event_name"
                )
                .value
                .trim();


        const eventDate =
            document
                .getElementById(
                    "event_date"
                )
                .value;



        /* =============================================
           CHƯA CHỌN ĐỊA ĐIỂM
        ============================================= */

        if (locationId === "") {

            event.preventDefault();


            showMessage(
                "Vui lòng chọn địa điểm tổ chức."
            );


            return;

        }



        /* =============================================
           CHƯA NHẬP TÊN
        ============================================= */

        if (eventName === "") {

            event.preventDefault();


            showMessage(
                "Vui lòng nhập tên sự kiện."
            );


            return;

        }



        /* =============================================
           CHƯA CHỌN NGÀY
        ============================================= */

        if (eventDate === "") {

            event.preventDefault();


            showMessage(
                "Vui lòng chọn ngày tổ chức sự kiện."
            );


            return;

        }

    }
);

</script>


</body>

</html>