<?php

require_once("../auth.php");
require_once("../../config/database.php");


/* =========================
   KIỂM TRA ĐỊA ĐIỂM
========================= */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Địa điểm không hợp lệ!");
}

$location_id = (int) $_GET['id'];


/* =========================
   LẤY THÔNG TIN ĐỊA ĐIỂM
========================= */

$stmt = $conn->prepare(
    "SELECT * FROM locations WHERE location_id = ?"
);

$stmt->bind_param("i", $location_id);
$stmt->execute();

$locationResult = $stmt->get_result();

if ($locationResult->num_rows == 0) {
    die("Không tìm thấy địa điểm!");
}

$location = $locationResult->fetch_assoc();


/* =========================
   UPLOAD ẢNH
========================= */

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_FILES["image"])) {

    $caption = trim($_POST["caption"] ?? "");

    $file = $_FILES["image"];

    if ($file["error"] === UPLOAD_ERR_OK) {

        // Giới hạn 5MB
        if ($file["size"] > 5 * 1024 * 1024) {

            $error = "Ảnh không được vượt quá 5MB.";

        } else {

            /*
             * Không tin vào đuôi file do người dùng gửi.
             * Kiểm tra MIME thực tế của file.
             */
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file["tmp_name"]);

            $allowed = [
                "image/jpeg" => "jpg",
                "image/png"  => "png",
                "image/webp" => "webp"
            ];

            if (!isset($allowed[$mime])) {

                $error =
                    "Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.";

            } else {

                $extension = $allowed[$mime];

                // Tạo tên file ngẫu nhiên tránh trùng
                $newFileName =
                    bin2hex(random_bytes(16))
                    . "."
                    . $extension;

                $uploadDirectory =
                    "../../uploads/locations/";

                $destination =
                    $uploadDirectory . $newFileName;


                if (move_uploaded_file(
                    $file["tmp_name"],
                    $destination
                )) {

                    /*
                     * Chỉ lưu đường dẫn tương đối
                     * vào database.
                     */
                    $imageUrl =
                        "uploads/locations/"
                        . $newFileName;


                    $insert = $conn->prepare(
                        "
                        INSERT INTO images
                        (
                            location_id,
                            image_url,
                            caption
                        )
                        VALUES (?, ?, ?)
                        "
                    );

                    $insert->bind_param(
                        "iss",
                        $location_id,
                        $imageUrl,
                        $caption
                    );


                    if ($insert->execute()) {

                        $message =
                            "Upload hình ảnh thành công!";

                    } else {

                        // DB lỗi thì xóa file vừa upload
                        if (file_exists($destination)) {
                            unlink($destination);
                        }

                        $error =
                            "Không thể lưu hình ảnh.";
                    }

                } else {

                    $error =
                        "Không thể tải hình ảnh lên.";
                }
            }
        }

    } else {

        $error =
            "Vui lòng chọn một hình ảnh.";
    }
}


/* =========================
   LẤY DANH SÁCH ẢNH
========================= */

$stmtImages = $conn->prepare(
    "
    SELECT *
    FROM images
    WHERE location_id = ?
    ORDER BY image_id DESC
    "
);

$stmtImages->bind_param(
    "i",
    $location_id
);

$stmtImages->execute();

$images =
    $stmtImages->get_result();

?>

<!DOCTYPE html>
<html lang="vi">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>Quản lý hình ảnh</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<style>

.location-image {

    width: 100%;
    height: 200px;

    object-fit: cover;

    border-radius: 8px;

}

</style>

</head>


<body class="bg-light">

<?php

$currentAdminPage = 'locations';

require_once(
    "../includes/navbar.php"
);

?>

<div class="container py-5">


    <div class="mb-4">

        <a
            href="index.php"
            class="btn btn-secondary btn-sm"
        >
            ← Quay lại
        </a>

    </div>


    <h2>
        HÌNH ẢNH ĐỊA ĐIỂM
    </h2>

    <p class="text-muted">

        <?= htmlspecialchars(
            $location["name"]
        ) ?>

    </p>


    <?php if ($message): ?>

        <div class="alert alert-success">

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>


    <?php if ($error): ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <!-- FORM UPLOAD -->

    <div class="card shadow-sm mb-5">

        <div class="card-header bg-success text-white">

            <strong>
                Thêm hình ảnh
            </strong>

        </div>


        <div class="card-body">

            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <div class="mb-3">

                    <label class="form-label">

                        Chọn hình ảnh

                    </label>

                    <input
                        type="file"
                        name="image"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp"
                        required
                    >

                    <small class="text-muted">

                        JPG, PNG hoặc WEBP.
                        Tối đa 5MB.

                    </small>

                </div>


                <div class="mb-3">

                    <label class="form-label">

                        Chú thích

                    </label>

                    <input
                        type="text"
                        name="caption"
                        class="form-control"
                        placeholder="Ví dụ: Cổng chính của địa điểm"
                    >

                </div>


                <button
                    type="submit"
                    class="btn btn-success"
                >

                    📷 Upload ảnh

                </button>

            </form>

        </div>

    </div>


    <!-- DANH SÁCH ẢNH -->

    <h4 class="mb-3">
        Thư viện hình ảnh
    </h4>


    <div class="row">

    <?php if ($images->num_rows > 0): ?>


        <?php while ($image = $images->fetch_assoc()): ?>

            <div class="col-md-4 mb-4">

                <div class="card h-100 shadow-sm">

                    <img
                        src="../../<?= htmlspecialchars(
                            $image["image_url"]
                        ) ?>"
                        class="location-image"
                        alt=""
                    >


                    <div class="card-body">

                        <p>

                            <?= htmlspecialchars(
                                $image["caption"]
                                ?? ""
                            ) ?>

                        </p>


                       <button
                            type="button"
                            class="btn btn-danger btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteImageModal"
                            data-image-id="<?= (int)$image["image_id"] ?>"
                            data-location-id="<?= (int)$location_id ?>"
                            data-image-name="<?= htmlspecialchars(
                                $image["caption"] ?: "Hình ảnh",
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >
                            🗑 Xóa ảnh
                        </button>

                    </div>

                </div>

            </div>

        <?php endwhile; ?>


    <?php else: ?>

        <div class="col-12">

            <div class="alert alert-info">

                Địa điểm này chưa có hình ảnh.

            </div>

        </div>

    <?php endif; ?>

    </div>


</div>



<!-- =========================================
     MODAL XÁC NHẬN XÓA ẢNH
========================================= -->

<div
    class="modal fade"
    id="deleteImageModal"
    tabindex="-1"
    aria-labelledby="deleteImageModalTitle"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div
            class="
                modal-content
                border-0
                shadow
                rounded-4
            "
        >

            <div class="modal-body text-center p-4">

                <div
                    class="mb-3"
                    style="font-size:55px;"
                >
                    🗑️
                </div>

                <h4
                    class="fw-bold mb-2"
                    id="deleteImageModalTitle"
                >
                    Xác nhận xóa hình ảnh
                </h4>

                <p class="text-muted mb-2">
                    Bạn có chắc chắn muốn xóa
                    hình ảnh này không?
                </p>

                <p
                    class="fw-semibold mb-4"
                    id="deleteImageName"
                ></p>

                <form
                    method="POST"
                    action="delete_image.php"
                >

                    <input
                        type="hidden"
                        name="image_id"
                        id="deleteImageId"
                    >

                    <input
                        type="hidden"
                        name="location_id"
                        id="deleteLocationId"
                    >

                    <div
                        class="
                            d-flex
                            justify-content-center
                            gap-2
                        "
                    >

                        <button
                            type="button"
                            class="btn btn-light px-4"
                            data-bs-dismiss="modal"
                        >
                            Hủy
                        </button>

                        <button
                            type="submit"
                            class="btn btn-danger px-4"
                        >
                            🗑 Xóa ảnh
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>
</div>


<!-- BOOTSTRAP -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>


<script>

/* =========================================
   ĐƯA THÔNG TIN ẢNH VÀO MODAL
========================================= */

const deleteImageModal =
    document.getElementById("deleteImageModal");

deleteImageModal.addEventListener(
    "show.bs.modal",
    function (event) {

        const button =
            event.relatedTarget;

        const imageId =
            button.getAttribute(
                "data-image-id"
            );

        const locationId =
            button.getAttribute(
                "data-location-id"
            );

        const imageName =
            button.getAttribute(
                "data-image-name"
            );

        document.getElementById(
            "deleteImageId"
        ).value = imageId;

        document.getElementById(
            "deleteLocationId"
        ).value = locationId;

        document.getElementById(
            "deleteImageName"
        ).textContent = imageName;
    }
);

</script>







</body>
</html>