<?php

require_once("../auth.php");
require_once("../../config/database.php");


/* =====================================================
   BIẾN FORM
===================================================== */

$error = "";

$location_id = 0;
$event_name = "";
$event_date = "";
$description = "";


/* =====================================================
   XỬ LÝ THÊM SỰ KIỆN
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $location_id =
        (int)($_POST["location_id"] ?? 0);

    $event_name =
        trim($_POST["event_name"] ?? "");

    $event_date =
        trim($_POST["event_date"] ?? "");

    $description =
        trim($_POST["description"] ?? "");


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
       KIỂM TRA NGÀY
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


            $checkLocation->execute();


            $locationResult =
                $checkLocation->get_result();


            if ($locationResult->num_rows === 0) {

                $error =
                    "Địa điểm tổ chức không tồn tại hoặc đã bị ẩn.";

            }


            $checkLocation->close();

        }

    }


    /* =================================================
       THÊM SỰ KIỆN
    ================================================= */

    if ($error === "") {

        $sql = "
            INSERT INTO events
            (
                location_id,
                event_name,
                event_date,
                description
            )

            VALUES (?, ?, ?, ?)
        ";


        $stmt =
            $conn->prepare($sql);


        if (!$stmt) {

            $error =
                "Không thể chuẩn bị dữ liệu sự kiện.";

        }

        else {

            $stmt->bind_param(
                "isss",
                $location_id,
                $event_name,
                $event_date,
                $description
            );


            if ($stmt->execute()) {

                $stmt->close();


                header(
                    "Location: index.php?success=add"
                );


                exit();

            }

            else {

                $error =
                    "Không thể thêm sự kiện. Vui lòng thử lại.";

            }


            $stmt->close();

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
    Thêm sự kiện
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
   CARD
===================================================== */

.main-card {

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

<div class="container py-5">


<div
    class="
        card
        main-card
        shadow
    "
>


<!-- =====================================================
     HEADER
===================================================== -->

<div
    class="
        card-header
        bg-success
        text-white
        p-3
    "
>


<h4 class="mb-0">

    🎉 THÊM SỰ KIỆN

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
    value="<?= (int)$location['location_id'] ?>"
    <?=

        $location_id
        ===
        (int)$location['location_id']

        ? 'selected'
        : ''

    ?>
>


<?= htmlspecialchars(
    $location['name'],
    ENT_QUOTES,
    'UTF-8'
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
        'UTF-8'
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
        'UTF-8'
    ) ?>"
>


</div>



<!-- =====================================================
     NỘI DUNG
===================================================== -->

<div class="mb-4">


<label class="form-label">

    Nội dung sự kiện

</label>


<textarea
    name="description"
    id="description"
    class="form-control"
    rows="6"
    placeholder="Nhập nội dung giới thiệu về sự kiện..."
><?= htmlspecialchars(
    $description,
    ENT_QUOTES,
    'UTF-8'
) ?></textarea>


</div>



<!-- =====================================================
     BUTTON
===================================================== -->

<div class="d-flex gap-2">


<button
    type="submit"
    class="btn btn-success"
>

    💾 Lưu sự kiện

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
   HÀM HIỆN THÔNG BÁO
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
   VALIDATION FORM
===================================================== */

document
.getElementById(
    "eventForm"
)
.addEventListener(
    "submit",
    function(event) {


        /* =============================================
           ĐỊA ĐIỂM
        ============================================= */

        const locationId =
            document
                .getElementById(
                    "location_id"
                )
                .value;


        /* =============================================
           TÊN
        ============================================= */

        const eventName =
            document
                .getElementById(
                    "event_name"
                )
                .value
                .trim();


        /* =============================================
           NGÀY
        ============================================= */

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