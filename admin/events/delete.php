<?php

require_once("../auth.php");
require_once("../../config/database.php");


/* =====================================================
   CHỈ CHO PHÉP XÓA BẰNG POST
===================================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header(
        "Location: index.php?error=invalid"
    );

    exit();

}


/* =====================================================
   LẤY ID SỰ KIỆN
===================================================== */

$event_id =
    isset($_POST["id"])
    ? (int) $_POST["id"]
    : 0;


/* =====================================================
   KIỂM TRA ID
===================================================== */

if ($event_id <= 0) {

    header(
        "Location: index.php?error=invalid"
    );

    exit();

}


/* =====================================================
   KIỂM TRA SỰ KIỆN CÓ TỒN TẠI KHÔNG
===================================================== */

$checkSql = "
    SELECT
        event_id
    FROM events
    WHERE event_id = ?
    LIMIT 1
";


$check =
    $conn->prepare($checkSql);


if (!$check) {

    header(
        "Location: index.php?error=database"
    );

    exit();

}


$check->bind_param(
    "i",
    $event_id
);


if (!$check->execute()) {

    $check->close();

    header(
        "Location: index.php?error=database"
    );

    exit();

}


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
   XÓA SỰ KIỆN
===================================================== */

$deleteSql = "
    DELETE FROM events
    WHERE event_id = ?
";


$stmt =
    $conn->prepare($deleteSql);


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


/* =====================================================
   THỰC HIỆN XÓA
===================================================== */

if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: index.php?success=delete"
    );

    exit();

}


/* =====================================================
   XÓA THẤT BẠI
===================================================== */

$stmt->close();


header(
    "Location: index.php?error=delete"
);

exit();

?>