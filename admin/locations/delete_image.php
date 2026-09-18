<?php

require_once("../auth.php");
require_once("../../config/database.php");


/* =========================================
   CHỈ CHẤP NHẬN POST
========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header(
        "Location: index.php"
    );

    exit();
}


/* =========================================
   KIỂM TRA DỮ LIỆU
========================================= */

$image_id =
    filter_input(
        INPUT_POST,
        "image_id",
        FILTER_VALIDATE_INT
    );

$location_id =
    filter_input(
        INPUT_POST,
        "location_id",
        FILTER_VALIDATE_INT
    );


if (!$image_id || !$location_id) {

    header(
        "Location: index.php"
    );

    exit();
}


/* =========================================
   LẤY THÔNG TIN ẢNH
========================================= */

$stmt = $conn->prepare(
    "
        SELECT image_url
        FROM images
        WHERE image_id = ?
          AND location_id = ?
        LIMIT 1
    "
);

$stmt->bind_param(
    "ii",
    $image_id,
    $location_id
);

$stmt->execute();

$result =
    $stmt->get_result();


if ($result->num_rows === 0) {

    header(
        "Location: images.php?id="
        . $location_id
        . "&error=not_found"
    );

    exit();
}


$image =
    $result->fetch_assoc();

$stmt->close();


/* =========================================
   XÓA KHỎI DATABASE
========================================= */

$delete = $conn->prepare(
    "
        DELETE FROM images
        WHERE image_id = ?
          AND location_id = ?
    "
);

$delete->bind_param(
    "ii",
    $image_id,
    $location_id
);


if ($delete->execute()) {

    $delete->close();


    /* =====================================
       XÓA FILE ẢNH THẬT
    ===================================== */

    $filePath =
        "../../"
        . $image["image_url"];


    if (
        file_exists($filePath)
        &&
        is_file($filePath)
    ) {

        unlink($filePath);
    }


    /* =====================================
       QUAY VỀ TRANG HÌNH ẢNH
    ===================================== */

    header(
        "Location: images.php?id="
        . $location_id
        . "&deleted=1"
    );

    exit();
}


$delete->close();


header(
    "Location: images.php?id="
    . $location_id
    . "&error=delete"
);

exit();

?>