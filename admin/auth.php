<?php

if (
    session_status()
    === PHP_SESSION_NONE
) {

    session_start();

}


/* ==========================================
   CHƯA ĐĂNG NHẬP
========================================== */

if (
    !isset($_SESSION['admin_id'])
) {

    header(
        "Location: /bandosovanhoa/admin/login.php"
    );

    exit();

}
?>