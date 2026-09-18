<?php

session_start();


/* XÓA SESSION */

$_SESSION = [];


/* XÓA COOKIE SESSION */

if (
    ini_get(
        "session.use_cookies"
    )
) {

    $params =
        session_get_cookie_params();


    setcookie(

        session_name(),

        '',

        time() - 42000,

        $params["path"],

        $params["domain"],

        $params["secure"],

        $params["httponly"]

    );

}


/* HỦY SESSION */

session_destroy();


/* QUAY VỀ LOGIN */

header(
    "Location: login.php"
);

exit();
?>