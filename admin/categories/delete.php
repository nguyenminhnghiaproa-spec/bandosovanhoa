<?php

require_once("../auth.php");
require_once("../../config/database.php");


/* =====================================================
   CHỈ CHẤP NHẬN PHƯƠNG THỨC POST
===================================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header(
        "Location: index.php?error=invalid"
    );

    exit();

}


/* =====================================================
   LẤY ID DANH MỤC
===================================================== */

$category_id =
    isset($_POST["id"])
    ? (int)$_POST["id"]
    : 0;


/* =====================================================
   KIỂM TRA ID
===================================================== */

if ($category_id <= 0) {

    header(
        "Location: index.php?error=invalid"
    );

    exit();

}


/* =====================================================
   KIỂM TRA DANH MỤC CÓ TỒN TẠI KHÔNG
===================================================== */

$checkCategory =
    $conn->prepare("
        SELECT
            category_id
        FROM categories
        WHERE category_id = ?
        LIMIT 1
    ");


if (!$checkCategory) {

    header(
        "Location: index.php?error=database"
    );

    exit();

}


$checkCategory->bind_param(
    "i",
    $category_id
);


if (!$checkCategory->execute()) {

    $checkCategory->close();

    header(
        "Location: index.php?error=database"
    );

    exit();

}


$categoryResult =
    $checkCategory->get_result();


if ($categoryResult->num_rows === 0) {

    $checkCategory->close();

    header(
        "Location: index.php?error=notfound"
    );

    exit();

}


$checkCategory->close();


/* =====================================================
   KIỂM TRA DANH MỤC ĐANG CÓ ĐỊA ĐIỂM SỬ DỤNG KHÔNG
===================================================== */

$checkLocation =
    $conn->prepare("
        SELECT
            COUNT(*) AS total
        FROM locations
        WHERE category_id = ?
    ");


if (!$checkLocation) {

    header(
        "Location: index.php?error=database"
    );

    exit();

}


$checkLocation->bind_param(
    "i",
    $category_id
);


if (!$checkLocation->execute()) {

    $checkLocation->close();

    header(
        "Location: index.php?error=database"
    );

    exit();

}


$locationResult =
    $checkLocation->get_result();


$locationData =
    $locationResult->fetch_assoc();


$totalLocations =
    (int)($locationData["total"] ?? 0);


$checkLocation->close();


/* =====================================================
   NẾU ĐANG CÓ ĐỊA ĐIỂM SỬ DỤNG
===================================================== */

if ($totalLocations > 0) {

    header(
        "Location: index.php?error=used"
    );

    exit();

}


/* =====================================================
   XÓA DANH MỤC
===================================================== */

$delete =
    $conn->prepare("
        DELETE FROM categories
        WHERE category_id = ?
    ");


if (!$delete) {

    header(
        "Location: index.php?error=database"
    );

    exit();

}


$delete->bind_param(
    "i",
    $category_id
);


/* =====================================================
   THỰC HIỆN XÓA
===================================================== */

if ($delete->execute()) {

    $delete->close();

    header(
        "Location: index.php?success=delete"
    );

    exit();

}


/* =====================================================
   XÓA THẤT BẠI
===================================================== */

$delete->close();


header(
    "Location: index.php?error=delete"
);

exit();

?>