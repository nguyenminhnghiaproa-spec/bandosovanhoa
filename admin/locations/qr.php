<?php
require_once("../auth.php");
include("../../config/database.php");
require_once("../../config/app.php");

/* ==============================
   KIỂM TRA ID
================================ */

if (
    !isset($_GET['id'])
    ||
    !is_numeric($_GET['id'])
) {

    die("Địa điểm không hợp lệ!");

}

$location_id = (int) $_GET['id'];


/* ==============================
   LẤY ĐỊA ĐIỂM
================================ */

$stmt = $conn->prepare(
    "
    SELECT
        location_id,
        name,
        address
    FROM locations
    WHERE location_id = ?
    "
);

$stmt->bind_param(
    "i",
    $location_id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows == 0) {

    die("Không tìm thấy địa điểm!");

}

$location = $result->fetch_assoc();


/* ==============================
   TẠO URL CHI TIẾT
================================ */


$detailUrl = location_url($location_id);


/* ==============================
   LƯU URL VÀO BẢNG QR_CODES
================================ */

$check = $conn->prepare(
    "
    SELECT qr_id
    FROM qr_codes
    WHERE location_id = ?
    "
);

$check->bind_param(
    "i",
    $location_id
);

$check->execute();

$checkResult = $check->get_result();


if ($checkResult->num_rows == 0) {

    $insert = $conn->prepare(
        "
        INSERT INTO qr_codes
        (
            location_id,
            qr_url
        )
        VALUES (?, ?)
        "
    );

    $insert->bind_param(
        "is",
        $location_id,
        $detailUrl
    );

    $insert->execute();

} else {

    $update = $conn->prepare(
        "
        UPDATE qr_codes
        SET qr_url = ?
        WHERE location_id = ?
        "
    );

    $update->bind_param(
        "si",
        $detailUrl,
        $location_id
    );

    $update->execute();

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
    QR Code - <?= htmlspecialchars($location['name']) ?>
</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<style>

body {
    background: #f5f5f5;
}

.qr-box {
    max-width: 600px;
    margin: auto;
}

#qrcode {
    display: flex;
    justify-content: center;
    margin: 25px 0;
}

#qrcode img,
#qrcode canvas {
    border: 10px solid white;
}

@media print {

    .no-print {
        display: none !important;
    }

    body {
        background: white;
    }

    .card {
        border: none;
        box-shadow: none !important;
    }

}


@media print {

    body {
        background: white !important;
    }

    .no-print {
        display: none !important;
    }

    .qr-print-card {
        box-shadow: none !important;
        border: 2px solid #198754 !important;
        max-width: 500px;
        margin: 0 auto;
    }
}

</style>

</head>


<body>

<?php

$currentAdminPage = 'locations';

require_once(
    "../includes/navbar.php"
);

?>

<div class="container py-5">

<div class="qr-box">

<div class="card shadow">

<div class="card-header bg-success text-white">

    <h4 class="mb-0">
        QR CODE ĐỊA ĐIỂM
    </h4>

</div>


<div class="card-body text-center p-4">

    <h3 class="text-success">

        <?= htmlspecialchars(
            $location['name']
        ) ?>

    </h3>


    <p class="text-muted">

        <?= htmlspecialchars(
            $location['address']
            ?? ''
        ) ?>

    </p>


    <p>
        Quét mã QR để xem thông tin địa điểm
    </p>


    <!-- QR -->

    <div id="qrcode"></div>


    <p class="small text-muted">

        <?= htmlspecialchars($detailUrl) ?>

    </p>


    <div class="mt-4 no-print">

        <button
            type="button"
            onclick="window.print()"
            class="btn btn-success no-print"
        >
            🖨️ In mã QR
        </button>


        <a
            href="index.php"
            class="btn btn-secondary"
        >
            ← Quay lại
        </a>

    </div>

</div>

</div>

</div>

</div>


<!-- QR CODE JS -->

<script
    src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js">
</script>


<script>

const qrText =
    <?= json_encode(
        $detailUrl,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
    ) ?>;


new QRCode(
    document.getElementById("qrcode"),
    {
        text: qrText,
        width: 250,
        height: 250,
        correctLevel:
            QRCode.CorrectLevel.H
    }
);

</script>

</body>
</html>