<?php

/* =====================================================
   CẤU HÌNH URL WEBSITE
===================================================== */

/*
 * Tự nhận http hoặc https
 */
$isHttps =
    (!empty($_SERVER['HTTPS'])
    && $_SERVER['HTTPS'] !== 'off')
    ||
    (isset($_SERVER['SERVER_PORT'])
    && $_SERVER['SERVER_PORT'] == 443);

$protocol =
    $isHttps
    ? 'https'
    : 'http';


/*
 * Tên miền hiện tại
 *
 * Local:
 * localhost
 *
 * Hosting:
 * tenmiencuaban.vn
 */
$host =
    $_SERVER['HTTP_HOST']
    ?? 'localhost';


/*
 * Thư mục chứa website.
 *
 * Hiện tại:
 * localhost/bandosovanhoa/
 */
$basePath =
    '/bandosovanhoa';


/*
 * URL đầy đủ
 */
$baseUrl =
    $protocol
    . '://'
    . $host
    . $basePath;


/* =====================================================
   HÀM TẠO URL
===================================================== */

function app_url($path = '')
{
    global $baseUrl;

    return
        rtrim($baseUrl, '/')
        . '/'
        . ltrim($path, '/');
}


/* =====================================================
   URL CHI TIẾT ĐỊA ĐIỂM
===================================================== */

function location_url($locationId)
{
    return app_url(
        'location_detail.php?id='
        . (int)$locationId
    );
}