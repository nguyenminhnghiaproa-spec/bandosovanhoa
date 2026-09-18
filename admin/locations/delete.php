<?php

require_once("../auth.php");
require_once("../../config/database.php");


/* =====================================================
   CHỈ CHO PHÉP POST
===================================================== */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: index.php");
    exit();

}


/* =====================================================
   KIỂM TRA ID
===================================================== */

if (
    !isset($_POST['id'])
    ||
    !is_numeric($_POST['id'])
) {

    header(
        "Location: index.php?error=invalid"
    );

    exit();

}


$location_id =
    (int)$_POST['id'];


if ($location_id <= 0) {

    header(
        "Location: index.php?error=invalid"
    );

    exit();

}


/* =====================================================
   KIỂM TRA ĐỊA ĐIỂM CÓ TỒN TẠI KHÔNG
===================================================== */

$check =
    $conn->prepare("
        SELECT location_id
        FROM locations
        WHERE location_id = ?
        LIMIT 1
    ");


$check->bind_param(
    "i",
    $location_id
);


$check->execute();


$result =
    $check->get_result();


if ($result->num_rows === 0) {

    $check->close();

    header(
        "Location: index.php?error=notfound"
    );

    exit();

}


$check->close();


/* =====================================================
   XÓA ĐỊA ĐIỂM
===================================================== */

$stmt =
    $conn->prepare("
        DELETE FROM locations
        WHERE location_id = ?
    ");


if (!$stmt) {

    header(
        "Location: index.php?error=database"
    );

    exit();

}


$stmt->bind_param(
    "i",
    $location_id
);


if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: index.php?deleted=1"
    );

    exit();

}


/* =====================================================
   KHÔNG XÓA ĐƯỢC
===================================================== */

$stmt->close();


header(
    "Location: index.php?error=delete"
);

exit();

?>