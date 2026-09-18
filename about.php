<?php

include("config/database.php");

/* =====================================================
   THỐNG KÊ DỮ LIỆU
===================================================== */

$totalLocations = 0;
$totalEvents = 0;
$totalCategories = 0;


/* Địa điểm */
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM locations
    WHERE status = 'active'
");

if ($result) {
    $row = $result->fetch_assoc();
    $totalLocations = (int)$row['total'];
}


/* Danh mục */
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM categories
");

if ($result) {
    $row = $result->fetch_assoc();
    $totalCategories = (int)$row['total'];
}


/* Sự kiện */
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM events
");

if ($result) {
    $row = $result->fetch_assoc();
    $totalEvents = (int)$row['total'];
}

?>

<!DOCTYPE html>

<html lang="vi">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Thông tin xã Hòa Long
</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const mainImage =
            document.querySelector(
                ".local-main-image img"
            );

        const thumbnails =
            document.querySelectorAll(
                ".local-thumb"
            );


        if (
            !mainImage
            ||
            thumbnails.length === 0
        ) {
            return;
        }


        thumbnails.forEach(
            function (thumbnail) {

                thumbnail.addEventListener(
                    "click",
                    function () {

                        const newImage =
                            this.dataset.image;


                        if (!newImage) {
                            return;
                        }


                        thumbnails.forEach(
                            function (item) {

                                item.classList.remove(
                                    "active"
                                );

                            }
                        );


                        this.classList.add(
                            "active"
                        );


                        mainImage.style.opacity =
                            "0";


                        setTimeout(
                            function () {

                                mainImage.src =
                                    newImage;

                                mainImage.style.opacity =
                                    "1";

                            },
                            200
                        );

                    }
                );

            }
        );

    }
);

</script>



  
<style>

/* =====================================================
   BỘ MÀU WEBSITE
===================================================== */

:root {
    --primary: #123c2c;
    --primary-dark: #081f17;
    --primary-light: #1d5a42;

    --page-bg: #edf3ef;

    --white: #ffffff;
    --text-dark: #14231c;
    --text-muted: #6f7c75;
}


body {
    background: var(--page-bg);
    color: var(--text-dark);
}


/* =====================================================
   CHUNG
===================================================== */

body {
    margin: 0;
    background: var(--page-bg);
    color: var(--text-dark);
}

.section-title {
    font-weight: 800;
    margin-bottom: 12px;
}

.section-description {
    color: #6c757d;
    line-height: 1.8;
}


/* =====================================================
   HERO
===================================================== */

.about-hero {
    position: relative;
    overflow: hidden;

    padding:
        85px 0
        100px 0;

    color: white;

    background:
        linear-gradient(
            135deg,
            #146c43,
            #198754,
            #20a56b
        );
}

.about-hero::before {
    content: "";

    position: absolute;

    width: 320px;
    height: 320px;

    border-radius: 50%;

    background:
        rgba(
            255,
            255,
            255,
            0.07
        );

    right: -80px;
    top: -100px;
}

.about-hero::after {
    content: "";

    position: absolute;

    width: 220px;
    height: 220px;

    border-radius: 50%;

    background:
        rgba(
            255,
            255,
            255,
            0.05
        );

    left: -70px;
    bottom: -100px;
}

.hero-content {
    position: relative;
    z-index: 2;
}

.about-hero h1 {
    font-size: 48px;
    font-weight: 800;
}

.about-hero p {
    max-width: 760px;

    font-size: 18px;
    line-height: 1.8;

    color:
        rgba(
            255,
            255,
            255,
            0.92
        );
}

.hero-icon {
    font-size: 150px;
    text-align: center;
}


/* =====================================================
   THỐNG KÊ
===================================================== */

.stats-wrapper {
    position: relative;
    z-index: 5;
    margin-top: -45px;
}

.stat-card {
    height: 100%;

    padding: 25px 15px;

    text-align: center;

    background: white;

    border-radius: 18px;

    box-shadow:
        0 5px 20px
        rgba(
            0,
            0,
            0,
            0.10
        );
}

.stat-icon {
    font-size: 34px;
    margin-bottom: 5px;
}

.stat-number {
    font-size: 30px;
    font-weight: 800;
    color: #123c2c;
}

.stat-label {
    color: #6c757d;
}


/* =====================================================
   GIỚI THIỆU
===================================================== */

.info-card {
    height: 100%;

    border: none;
    border-radius: 18px;

    background: white;

    box-shadow:
        0 4px 18px
        rgba(
            0,
            0,
            0,
            0.07
        );

    transition: 0.25s;
}

.info-card:hover {
    transform:
        translateY(-5px);

    box-shadow:
        0 10px 25px
        rgba(
            0,
            0,
            0,
            0.11
        );
}

.info-icon {
    width: 65px;
    height: 65px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #e9f7ef;

    font-size: 31px;

    margin-bottom: 18px;
}
/* =====================================================
   KHÁM PHÁ XÃ HÒA LONG
===================================================== */

.explore-info-section {
    padding: 80px 0 90px;

    background:
        linear-gradient(
            180deg,
            #f7faf8 0%,
            #edf4ef 100%
        );
}


/* TIÊU ĐỀ */

.explore-info-heading {
    max-width: 650px;

    margin:
        0 auto
        42px;

    text-align: center;
}


.explore-info-heading span {
    display: inline-block;

    margin-bottom: 10px;

    color: #1d5a42;

    font-size: 11px;

    font-weight: 800;

    letter-spacing: 2px;
}


.explore-info-heading h2 {
    margin-bottom: 10px;

    color: #0b2d21;

    font-size:
        clamp(
            32px,
            4vw,
            46px
        );

    font-weight: 800;

    letter-spacing: -1.5px;
}


.explore-info-heading p {
    margin: 0;

    color: #78867f;

    font-size: 14px;

    line-height: 1.7;
}


/* CARD */

.explore-info-card {
    position: relative;

    height: 100%;

    padding:
        30px
        28px;

    overflow: hidden;

    border:
        1px solid
        rgba(18, 60, 44, 0.08);

    border-radius: 24px;

    background:
        rgba(255, 255, 255, 0.94);

    box-shadow:
        0 12px 32px
        rgba(16, 54, 40, 0.06);

    transition:
        transform 0.3s ease,
        box-shadow 0.3s ease,
        border-color 0.3s ease;
}


.explore-info-card::before {
    content: "";

    position: absolute;

    left: 0;
    top: 0;

    width: 4px;
    height: 0;

    border-radius:
        0
        4px
        4px
        0;

    background:
        linear-gradient(
            180deg,
            #123c2c,
            #2c7a57
        );

    transition:
        height 0.3s ease;
}


.explore-info-card:hover {
    transform:
        translateY(-7px);

    border-color:
        rgba(18, 60, 44, 0.18);

    box-shadow:
        0 20px 45px
        rgba(16, 54, 40, 0.12);
}


.explore-info-card:hover::before {
    height: 100%;
}


/* ICON */

.explore-info-icon {
    width: 54px;
    height: 54px;

    margin-bottom: 22px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 16px;

    background:
        linear-gradient(
            135deg,
            #e2f2e9,
            #f1f8f4
        );

    color: #123c2c;

    font-size: 25px;

    box-shadow:
        inset 0 0 0 1px
        rgba(18, 60, 44, 0.05);
}


/* NỘI DUNG */

.explore-info-card h3 {
    margin-bottom: 10px;

    color: #10291f;

    font-size: 18px;

    font-weight: 800;
}


.explore-info-card p {
    min-height: 62px;

    margin-bottom: 17px;

    color: #6f7c75;

    font-size: 13px;

    line-height: 1.75;
}


/* LINK */

.explore-info-link {
    display: inline-flex;

    align-items: center;

    gap: 7px;

    color: #123c2c;

    text-decoration: none;

    font-size: 12px;

    font-weight: 800;

    transition:
        gap 0.25s ease,
        color 0.25s ease;
}


.explore-info-link:hover {
    gap: 12px;

    color: #1d5a42;
}


/* MOBILE */

@media (max-width: 768px) {

    .explore-info-section {
        padding:
            55px 0
            65px;
    }


    .explore-info-heading {
        margin-bottom: 30px;
    }


    .explore-info-card {
        padding:
            25px
            22px;
    }

}

/* =====================================================
   KHÁM PHÁ
===================================================== */

.explore-card {
    border: none;

    border-radius: 20px;

    color: white;

    background:
        linear-gradient(
            135deg,
            #123c2c,
            #081f17
        );

    padding:
        50px
        30px;
}


/* =====================================================
   MOBILE
===================================================== */

@media (
    max-width: 767px
) {

    .about-hero {
        padding:
            60px 0
            85px 0;
    }

    .about-hero h1 {
        font-size: 35px;
    }

    .hero-icon {
        display: none;
    }

}

.modern-hero-section {
    padding: 18px 22px 25px;
    background: #edf3ef;
}


/* =====================================================
   SLIDER 3 ẢNH
===================================================== */

.hero-bg-slide {
    position: absolute;
    inset: 0;

    width: 100%;
    height: 100%;

    opacity: 0;

    transform: scale(1.04);

    transition:
        opacity 1.2s ease,
        transform 5s ease;

    pointer-events: none;
}

.hero-bg-slide.active {
    opacity: 1;

    transform: scale(1);

    z-index: 1;
}

.hero-bg-slide img {
    width: 100%;
    height: 100%;

    display: block;

    object-fit: cover;
    object-position: center;

    /* KHÔNG LÀM MÉO ẢNH */
}


    /* =====================================================
    OVERLAY
    ===================================================== */

    .hero-dark-overlay {
        position: absolute;

        inset: 0;

        z-index: 2;

        background:
            linear-gradient(
                90deg,
                rgba(4, 20, 14, 0.88) 0%,
                rgba(5, 27, 18, 0.68) 38%,
                rgba(5, 25, 17, 0.35) 68%,
                rgba(4, 15, 11, 0.30) 100%
            );

    }





/* =====================================================
   TOP BAR
===================================================== */

    .hero-topbar {
        position: absolute;

        z-index: 5;

        top: 0;
        left: 0;

        width: 100%;

        padding:
            30px
            38px;

        display: flex;

        align-items: center;

        color: white;
    }


.hero-brand {
    font-size: 15px;
    font-weight: 800;

    letter-spacing: 2px;
}


.hero-mini-menu {
    position: absolute;

    left: 50%;

    transform:
        translateX(-50%);

    display: flex;

    gap: 28px;

    font-size: 12px;

    color:
        rgba(
            255,
            255,
            255,
            0.85
        );
}


.hero-mini-menu span {
    position: relative;
}


.hero-mini-menu span::after {
    content: "";

    position: absolute;

    left: 0;
    bottom: -7px;

    width: 0;
    height: 1px;

    background: white;

    transition: 0.3s;
}


.hero-mini-menu span:hover::after {
    width: 100%;
}


.hero-map-small {
    margin-left: auto;

    padding:
        10px
        18px;

    border-radius: 30px;

    background: white;

    color: #17251e;

    text-decoration: none;

    font-size: 12px;
    font-weight: 700;

    transition: 0.25s;
}


.hero-map-small:hover {
    color: #123c2c;

    transform:
        translateY(-2px);
}


/* =====================================================
   MAIN CONTENT
===================================================== */

.hero-main-content {
    position: relative;

    z-index: 5;

    height: 100%;

    padding:
        115px
        42px
        145px;

    display: flex;

    align-items: center;

    justify-content:
        space-between;

    gap: 50px;
}


.hero-left {
    max-width: 700px;
}


.hero-label {
    margin-bottom: 18px;

    color:
        rgba(
            255,
            255,
            255,
            0.65
        );

    font-size: 11px;

    font-weight: 700;

    letter-spacing: 3px;
}


.hero-left h1 {
    margin: 0;

    max-width: 720px;

    color: white;

    font-size:
        clamp(
            52px,
            6vw,
            90px
        );

    font-weight: 500;

    line-height: 0.98;

    letter-spacing: -4px;
}


.hero-left h1 span {
    display: block;

    color:
        rgba(
            255,
            255,
            255,
            0.62
        );

    font-weight: 300;
}


/* =====================================================
   GLASS CARD
===================================================== */
.hero-glass-card {
    width: 330px;

    flex-shrink: 0;

    padding: 27px;

    border:
        1px solid
        rgba(255, 255, 255, 0.24);

    border-radius: 22px;

    background:
        rgba(8, 31, 23, 0.72);

    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);

    box-shadow:
        0 20px 50px
        rgba(0, 0, 0, 0.30);

    color: #ffffff;
}


.glass-label {
    margin-bottom: 10px;

    color:
        rgba(
            255,
            255,
            255,
            0.65
        );

    font-size: 10px;

    font-weight: 700;

    letter-spacing: 1.5px;
}


.hero-glass-card h3 {
    margin-bottom: 12px;

    font-size: 26px;

    font-weight: 700;
}


.hero-glass-card p {
    margin-bottom: 22px;

    color:
        rgba(
            255,
            255,
            255,
            0.72
        );

    font-size: 13px;

    line-height: 1.7;
}


/* FEATURES */

.glass-features {
    display: grid;

    grid-template-columns:
        repeat(
            3,
            1fr
        );

    gap: 8px;

    margin-bottom: 20px;
}


.glass-features div {
    padding:
        12px
        5px;

    text-align: center;

    border-radius: 12px;

    background:
        rgba(
            255,
            255,
            255,
            0.08
        );
}


.glass-features span {
    display: block;

    margin-bottom: 4px;

    font-size: 20px;
}


.glass-features small {
    font-size: 10px;

    color:
        rgba(
            255,
            255,
            255,
            0.75
        );
}


/* BUTTON */

.glass-map-button {
    width: 100%;

    min-height: 46px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 12px;

    background: white;

    color: #17251e;

    text-decoration: none;

    font-size: 12px;

    font-weight: 800;

    transition: 0.25s;
}


.glass-map-button:hover {
    background: #198754;

    color: white;

    transform:
        translateY(-2px);
}


/* =====================================================
   BOTTOM
===================================================== */

.hero-bottom {
    position: absolute;

    z-index: 6;

    left: 42px;
    right: 42px;
    bottom: 32px;

    display: flex;

    align-items: flex-end;

    gap: 40px;

    color: white;
}


.hero-description {
    max-width: 360px;

    color:
        rgba(
            255,
            255,
            255,
            0.75
        );

    font-size: 12px;

    line-height: 1.7;
}


.hero-counter {
    display: flex;

    align-items: center;

    gap: 18px;

    margin-left: auto;
}


.counter-item {
    display: flex;

    flex-direction: column;
}


.counter-item strong {
    font-size: 25px;

    line-height: 1;
}


.counter-item span {
    margin-top: 5px;

    color:
        rgba(
            255,
            255,
            255,
            0.65
        );

    font-size: 10px;
}


.counter-line {
    width: 1px;
    height: 38px;

    background:
        rgba(
            255,
            255,
            255,
            0.25
        );
}


/* =====================================================
   SLIDER CONTROL
===================================================== */

.hero-slider-control {
    display: flex;

    align-items: center;

    gap: 12px;
}


.hero-slider-control button {
    width: 40px;
    height: 40px;

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            0.35
        );

    border-radius: 50%;

    background:
        rgba(
            255,
            255,
            255,
            0.08
        );

    color: white;

    transition: 0.25s;
}


.hero-slider-control button:hover {
    background: white;

    color: #17251e;
}


#heroSlideNumber {
    min-width: 48px;

    text-align: center;

    font-size: 11px;
}


/* =====================================================
   PROGRESS
===================================================== */

.hero-progress {
    position: absolute;

    z-index: 8;

    bottom: 0;
    left: 0;

    width: 100%;
    height: 3px;

    background:
        rgba(
            255,
            255,
            255,
            0.12
        );
}


.hero-progress-bar {
    width: 0;
    height: 100%;

    background: white;
}


.modern-hero {
    position: relative;

    width: 100%;
    max-width: none;
    height: 690px;

    margin: 0;

    overflow: hidden;

    border-radius: 30px;

    background: #081f17;

    box-shadow:
        0 20px 50px
        rgba(8, 31, 23, 0.18);
}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 576px) {

    .modern-hero-section {
        padding: 12px;
    }


    .modern-hero {
        height: 780px;

        border-radius: 20px;
    }


    .hero-topbar {
        padding:
            22px
            20px;
    }


    .hero-brand {
        font-size: 12px;
    }


    .hero-map-small {
        padding:
            8px
            13px;
    }


    .hero-main-content {
        padding:
            90px
            20px
            165px;
    }


    .hero-left h1 {
        font-size: 47px;

        letter-spacing: -2px;
    }


    .hero-glass-card {
        padding: 20px;
    }


    .hero-bottom {
        left: 20px;
        right: 20px;
        bottom: 25px;

        align-items: center;
    }


    .hero-description {
        display: none;
    }


    .hero-counter {
        margin-left: 0;
    }


    .hero-slider-control {
        margin-left: auto;
    }

}

/* =====================================================
   NÚT MÀU XANH ĐẬM
===================================================== */

.btn-success {
    background-color: #123c2c !important;
    border-color: #123c2c !important;
}


.btn-success:hover {
    background-color: #081f17 !important;
    border-color: #081f17 !important;
}


.btn-outline-success {
    color: #123c2c !important;
    border-color: #123c2c !important;
}


.btn-outline-success:hover {
    color: #ffffff !important;

    background-color: #123c2c !important;
    border-color: #123c2c !important;
}


.text-success {
    color: #123c2c !important;
}


.bg-success {
    background-color: #123c2c !important;
}


/* =====================================================
   NÚT TRÁI / PHẢI
===================================================== */

.modern-slider-arrow {
    position: absolute;

    top: 50%;

    z-index: 20;

    width: 54px;
    height: 54px;

    transform: translateY(-50%);

    border:
        1px solid
        rgba(255, 255, 255, 0.45);

    border-radius: 50%;

    background:
        rgba(5, 25, 17, 0.30);

    backdrop-filter: blur(10px);

    color: #ffffff;

    font-size: 22px;

    cursor: pointer;

    transition: 0.3s;
}

.modern-slider-arrow:hover {
    background: #ffffff;

    color: #123c2c;

    transform:
        translateY(-50%)
        scale(1.08);
}

.modern-slider-left {
    left: 25px;
}

.modern-slider-right {
    right: 25px;
}


/* =====================================================
   THỐNG KÊ HIỆN ĐẠI
===================================================== */

.modern-stats-section {
    position: relative;
    z-index: 20;

    padding: 0 0 45px;

    margin-top: -38px;

    background: transparent;
}


.modern-stat-card {
    position: relative;

    min-height: 155px;

    display: flex;
    align-items: center;

    gap: 20px;

    padding: 26px;

    overflow: hidden;

    border: 1px solid rgba(18, 60, 44, 0.07);

    border-radius: 24px;

    background: rgba(255, 255, 255, 0.96);

    box-shadow:
        0 14px 35px
        rgba(13, 48, 35, 0.07);

    transition:
        transform 0.3s ease,
        box-shadow 0.3s ease;
}

.modern-stat-card:hover {
    transform: translateY(-5px);

    box-shadow:
        0 20px 45px
        rgba(13, 48, 35, 0.12);
}

.modern-stat-content span {
    display: block;

    margin-bottom: 4px;

    color: #809087;

    font-size: 10px;

    font-weight: 800;

    letter-spacing: 1.7px;
}


.modern-stat-content strong {
    display: block;

    margin-bottom: 5px;

    color: #123c2c;

    font-size: 31px;

    line-height: 1;

    font-weight: 800;
}


.modern-stat-content p {
    margin: 0;

    max-width: 190px;

    color: #77837d;

    font-size: 12px;

    line-height: 1.5;
}


.modern-stat-link {
    position: absolute;

    top: 18px;
    right: 18px;

    width: 34px;
    height: 34px;

    display: flex;

    align-items: center;
    justify-content: center;

    border:
        1px solid
        #dbe6df;

    border-radius: 50%;

    color: #123c2c;

    text-decoration: none;

    transition: 0.25s;
}


.modern-stat-link:hover {
    color: white;

    background: #123c2c;

    border-color: #123c2c;

    transform:
        rotate(45deg);
}


/* =====================================================
   NỀN GIỚI THIỆU HÒA LONG - DÙNG ẢNH NỀN
   Đặt ảnh tại:
   /bandosovanhoa/uploads/about/nen-gioi-thieu.png
===================================================== */

.local-about-section {
    position: relative;
    overflow: hidden;
    padding: 65px 0 120px;

    background-image:
        linear-gradient(
            rgba(245, 250, 246, 0.18),
            rgba(237, 245, 239, 0.18)
        ),
        url("/bandosovanhoa/uploads/about/nen-gioi-thieu.png");

    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
}

/* Nội dung luôn nằm trên ảnh nền */
.local-about-section > .container-fluid {
    position: relative;
    z-index: 2;
}

/* =====================================================
   HEADING
===================================================== */

.local-about-heading {
    margin-bottom: 42px;

    display: flex;

    align-items: flex-end;
    justify-content: space-between;

    gap: 50px;
}


.local-eyebrow {
    display: block;

    margin-bottom: 12px;

    color: #1d5a42;

    font-size: 11px;

    font-weight: 800;

    letter-spacing: 2.4px;
}


.local-about-heading h2 {
    margin: 0;

    max-width: 600px;

    color: #10291f;

    font-size:
        clamp(
            36px,
            4vw,
            58px
        );

    line-height: 1.05;

    font-weight: 500;

    letter-spacing: -2px;
}


.local-about-heading h2 span {
    color: #1d5a42;

    font-weight: 800;
}


.local-about-heading > p {
    max-width: 460px;

    margin: 0;

    color: #708078;

    font-size: 14px;

    line-height: 1.8;
}


/* =====================================================
   MAIN CARD
===================================================== */

.local-about-card {
    position: relative;
    overflow: visible;

    border: none;
    border-radius: 0;

    background: transparent;

    backdrop-filter: none;
    -webkit-backdrop-filter: none;

    box-shadow: none;
}


/* =====================================================
   GALLERY
===================================================== */

.local-gallery {
    height: 100%;
    padding: 18px;
    background: transparent;
}


.local-main-image {
    position: relative;

    height: 480px;

    overflow: hidden;

    border-radius: 22px;
}


.local-main-image img {
    width: 100%;
    height: 100%;

    display: block;

    object-fit: cover;
    object-position: center;

    transition:
        opacity 0.3s ease,
        transform 0.6s ease;
}


.local-main-image:hover img {
    transform:
        scale(1.025);
}


/* BADGE TRÊN ẢNH */

.image-location-badge {
    position: absolute;

    left: 20px;
    bottom: 20px;

    display: flex;

    align-items: center;

    gap: 10px;

    padding:
        11px
        15px;

    border:
        1px solid
        rgba(255, 255, 255, 0.25);

    border-radius: 14px;

    color: white;

    background:
        rgba(8, 31, 23, 0.68);

    backdrop-filter:
        blur(12px);

    -webkit-backdrop-filter:
        blur(12px);
}


.image-location-badge > span {
    font-size: 19px;
}


.image-location-badge small {
    display: block;

    color:
        rgba(255, 255, 255, 0.65);

    font-size: 8px;

    font-weight: 700;

    letter-spacing: 1.3px;
}


.image-location-badge strong {
    display: block;

    margin-top: 2px;

    font-size: 12px;
}


/* 3 ẢNH NHỎ */

.local-small-images {
    display: grid;

    grid-template-columns:
        repeat(
            3,
            1fr
        );

    gap: 10px;

    margin-top: 10px;
}


.local-thumb {
    height: 100px;

    padding: 0;

    overflow: hidden;

    border:
        3px solid
        transparent;

    border-radius: 14px;

    background: transparent;

    cursor: pointer;

    transition: 0.25s;
}


.local-thumb img {
    width: 100%;
    height: 100%;

    display: block;

    object-fit: cover;

    transition:
        transform 0.3s ease;
}


.local-thumb:hover img {
    transform:
        scale(1.08);
}


.local-thumb.active {
    border-color: #123c2c;
}


/* =====================================================
   INFORMATION
===================================================== */

.local-information {
    height: 100%;

    padding: 55px;

    display: flex;
    flex-direction: column;
    justify-content: center;

    background:
        linear-gradient(
            90deg,
            rgba(238, 248, 241, 0.20),
            rgba(238, 248, 241, 0.48)
        );

    border-radius: 0 28px 28px 0;
}

.local-number {
    display: block;
    margin-bottom: 14px;

    color: #123c2c;

    font-size: 11px;
    font-weight: 900;
    letter-spacing: 2px;
}


.local-information h3 {
    margin-bottom: 12px;

    color: #071f17;

    font-size:
        clamp(
            35px,
            4vw,
            52px
        );

    font-weight: 800;
    letter-spacing: -2px;
}


.local-address {
    margin-bottom: 25px;

    display: flex;
    align-items: center;

    gap: 7px;

    color: #284c3c;

    font-size: 12px;
    font-weight: 600;
}


.local-address span {
    color: #123c2c;

    font-size: 18px;
    font-weight: 800;
}


.local-information p {
    color: #18382b;

    font-size: 13.5px;

    line-height: 1.9;

    font-weight: 600;
}

.local-intro {
    color: #102f23 !important;

    font-size: 15px !important;

    font-weight: 700 !important;

    line-height: 1.9 !important;
}


/* =====================================================
   FEATURES
===================================================== */

.local-features {
    display: grid;

    grid-template-columns:
        repeat(
            2,
            1fr
        );

    gap: 10px;

    margin:
        20px 0
        28px;
}


.local-feature {
    min-height: 75px;

    display: flex;

    align-items: center;

    gap: 12px;

    padding:
        13px;

    border:
        1px solid
        #e2ebe5;

    border-radius: 15px;

    background: #f7faf8;

    transition: 0.25s;
}


.local-feature:hover {
    border-color:
        rgba(
            18,
            60,
            44,
            0.25
        );

    background: #edf5f0;

    transform:
        translateY(-2px);
}


.feature-icon {
    width: 43px;
    height: 43px;

    flex-shrink: 0;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 12px;

    background: white;

    font-size: 20px;

    box-shadow:
        0 5px 15px
        rgba(13, 48, 35, 0.06);
}


.local-feature strong {
    display: block;

    color: #18382b;

    font-size: 12px;
}


.local-feature small {
    display: block;

    margin-top: 3px;

    color: #8a9790;

    font-size: 9px;
}


/* =====================================================
   ACTION BUTTONS
===================================================== */

.local-actions {
    display: flex;

    flex-wrap: wrap;

    gap: 10px;
}


.local-primary-button {
    min-height: 50px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    gap: 20px;

    padding:
        0
        22px;

    border-radius: 12px;

    color: white;

    background: #123c2c;

    text-decoration: none;

    font-size: 12px;

    font-weight: 700;

    transition: 0.25s;
}


.local-primary-button span {
    transition: 0.25s;
}


.local-primary-button:hover {
    color: white;

    background: #081f17;

    transform:
        translateY(-2px);
}


.local-primary-button:hover span {
    transform:
        translateX(5px);
}


.local-secondary-button {
    min-height: 50px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    padding:
        0
        22px;

    border:
        1px solid
        #d5e1da;

    border-radius: 12px;

    color: #123c2c;

    background: white;

    text-decoration: none;

    font-size: 12px;

    font-weight: 700;

    transition: 0.25s;
}


.local-secondary-button:hover {
    color: #123c2c;

    border-color: #123c2c;

    background: #edf5f0;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 991px) {

    .modern-stats-section {
        margin-top: 20px;
    }


    .local-about-heading {
        align-items: flex-start;

        flex-direction: column;

        gap: 18px;
    }


    .local-information {
        padding:
            40px
            30px;
    }


    .local-main-image {
        height: 420px;
    }

}


@media (max-width: 576px) {

    .local-about-section {
        padding:
            45px 0
            65px;
    }


    .local-about-heading h2 {
        font-size: 37px;

        letter-spacing: -1px;
    }


    .local-main-image {
        height: 330px;
    }


    .local-small-images {
        gap: 6px;
    }


    .local-thumb {
        height: 75px;
    }


    .local-information {
        padding:
            35px
            22px;
    }


    .local-features {
        grid-template-columns: 1fr;
    }


    .local-primary-button,
    .local-secondary-button {
        width: 100%;
    }

}





</style>

<link rel="stylesheet" href="/bandosovanhoa/assets/css/public-theme.css?v=1">

</head>

<body>


<?php

/*
 * Giữ nguyên navbar khách hiện tại.
 * Không thêm "Thông tin xã" vào menu.
 */

$currentPage = '';

require_once("includes/navbar.php");

?>
<!-- =====================================================
     MODERN HERO - XÃ HÒA LONG
===================================================== -->

<section class="modern-hero-section">

    <div class="container-fluid px-0">

        <div class="modern-hero">

            <!-- ẢNH SLIDER -->

            <div class="hero-bg-slide active">
                <img
                    src="uploads/about/hoa-long-1.jpg"
                    alt="Hình ảnh xã Hòa Long"
                >
            </div>

            <div class="hero-bg-slide">
                <img
                    src="uploads/about/hoa-long-2.png"
                    alt="Hình ảnh xã Hòa Long"
                >
            </div>

            <div class="hero-bg-slide">
                <img
                    src="uploads/about/hoa-long-3.png"
                    alt="Hình ảnh xã Hòa Long"
                >
            </div>


            <!-- LỚP PHỦ -->

            <div class="hero-dark-overlay"></div>


            <!-- THANH NHỎ PHÍA TRÊN -->

            <div class="hero-topbar">

                <div class="hero-brand">
                    HÒA LONG
                </div>

                

                

            </div>


            <!-- NỘI DUNG CHÍNH -->

            <div class="hero-main-content">

                <div class="hero-left">

                    <div class="hero-label">
                        KHÁM PHÁ ĐỊA PHƯƠNG
                    </div>

                    <h1>
                        Khám phá
                        <span>Xã Hòa Long</span>
                        trên nền tảng số
                    </h1>

                </div>


                <!-- GLASS CARD -->

                <div class="hero-glass-card">

                    <div class="glass-label">
                        📍 ĐỊA PHƯƠNG
                    </div>

                    <h3>
                        Xã Hòa Long
                    </h3>

                    <p>
                        Khám phá các giá trị
                        văn hóa, du lịch,
                        điểm đến và sản phẩm
                        đặc trưng của địa phương.
                    </p>


                    <div class="glass-features">

                        <div>
                            <span>🎭</span>
                            <small>Văn hóa</small>
                        </div>

                        <div>
                            <span>🏞️</span>
                            <small>Du lịch</small>
                        </div>

                        <div>
                            <span>🎁</span>
                            <small>OCOP</small>
                        </div>

                    </div>


                    <a
                        href="map.php"
                        class="glass-map-button"
                    >
                        🗺️ Khám phá bản đồ
                    </a>

                </div>

            </div>


            <!-- PHẦN DƯỚI -->

            <div class="hero-bottom">

                <div class="hero-description">

                    Khám phá những nét đặc trưng,
                    các điểm đến và giá trị
                    văn hóa của xã Hòa Long
                    thông qua bản đồ số.

                </div>


                <div class="hero-counter">

                    <div class="counter-item">

                        <strong>
                            <?= $totalLocations ?>
                        </strong>

                        <span>
                            Địa điểm
                        </span>

                    </div>


                    <div class="counter-line"></div>


                    <div class="counter-item">

                        <strong>
                            <?= $totalEvents ?>
                        </strong>

                        <span>
                            Sự kiện
                        </span>

                    </div>

                </div>


                <!-- ĐIỀU KHIỂN SLIDER -->

                <div class="hero-slider-control">

                    <button
                        type="button"
                        id="heroPrev"
                        aria-label="Ảnh trước"
                    >
                        ←
                    </button>

                    <span id="heroSlideNumber">
                        01 / 03
                    </span>

                    <button
                        type="button"
                        id="heroNext"
                        aria-label="Ảnh tiếp theo"
                    >
                        →
                    </button>

                </div>

            </div>


            <!-- THANH TIẾN TRÌNH -->

            <div class="hero-progress">

                <div
                    class="hero-progress-bar"
                    id="heroProgressBar"
                ></div>

            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     THỐNG KÊ HIỆN ĐẠI
===================================================== -->

<section 
    class="modern-stats-section">


    <div class="container-fluid px-lg-5">

        <div class="row g-4">

            <!-- ĐỊA ĐIỂM -->
            <div class="col-lg-4 col-md-6">

                <div class="modern-stat-card">

                    <div class="modern-stat-icon">
                        📍
                    </div>

                    <div class="modern-stat-content">

                        <span>
                            ĐỊA ĐIỂM
                        </span>

                        <strong>
                            <?= $totalLocations ?>
                        </strong>

                        <p>
                            Điểm văn hóa, du lịch
                            và địa điểm địa phương
                        </p>

                    </div>

                    <a
                        href="locations.php"
                        class="modern-stat-link"
                    >
                        ↗
                    </a>

                </div>

            </div>


            <!-- DANH MỤC -->
            <div class="col-lg-4 col-md-6">

                <div class="modern-stat-card">

                    <div class="modern-stat-icon">
                        🏷️
                    </div>

                    <div class="modern-stat-content">

                        <span>
                            DANH MỤC
                        </span>

                        <strong>
                            <?= $totalCategories ?>
                        </strong>

                        <p>
                            Nhóm nội dung để
                            khám phá xã Hòa Long
                        </p>

                    </div>

                    <a
                        href="map.php"
                        class="modern-stat-link"
                    >
                        ↗
                    </a>

                </div>

            </div>


            <!-- SỰ KIỆN -->
            <div class="col-lg-4 col-md-6">

                <div class="modern-stat-card">

                    <div class="modern-stat-icon">
                        🎉
                    </div>

                    <div class="modern-stat-content">

                        <span>
                            SỰ KIỆN
                        </span>

                        <strong>
                            <?= $totalEvents ?>
                        </strong>

                        <p>
                            Hoạt động và sự kiện
                            nổi bật tại địa phương
                        </p>

                    </div>

                    <a
                        href="events.php"
                        class="modern-stat-link"
                    >
                        ↗
                    </a>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     GIỚI THIỆU XÃ HÒA LONG
===================================================== -->
<!-- =====================================================
     GIỚI THIỆU XÃ HÒA LONG
===================================================== -->

<section
    class="local-about-section"
    id="about-content"
>

    <!-- NỘI DUNG -->
    <div class="container-fluid px-lg-5">

        <!-- KHỐI CHÍNH -->


        <div class="local-about-card">

            <div class="row g-0">


                <!-- =========================
                     HÌNH ẢNH
                ========================== -->

                <div class="col-lg-6">

                    <div class="local-gallery">

                        <div class="local-main-image">

                            <img
                                src="uploads/about/hoa-long-1.jpg"
                                alt="Hình ảnh xã Hòa Long"
                            >

                            <div class="image-location-badge">

                                <span>📍</span>

                                <div>
                                    <small>
                                        ĐỊA PHƯƠNG
                                    </small>

                                    <strong>
                                        Xã Hòa Long
                                    </strong>
                                </div>

                            </div>

                        </div>


                        <!-- ẢNH NHỎ -->

                        <div class="local-small-images">

                            <button
                                type="button"
                                class="local-thumb active"
                                data-image="uploads/about/hoa-long-1.jpg"
                            >
                                <img
                                    src="uploads/about/hoa-long-1.jpg"
                                    alt="Hòa Long 1"
                                >
                            </button>


                            <button
                                type="button"
                                class="local-thumb"
                                data-image="uploads/about/hoa-long-2.png"
                            >
                                <img
                                    src="uploads/about/hoa-long-2.png"
                                    alt="Hòa Long 2"
                                >
                            </button>


                            <button
                                type="button"
                                class="local-thumb"
                                data-image="uploads/about/hoa-long-3.png"
                            >
                                <img
                                    src="uploads/about/hoa-long-3.png"
                                    alt="Hòa Long 3"
                                >
                            </button>

                        </div>

                    </div>

                </div>


                <!-- =========================
                     THÔNG TIN
                ========================== -->

                <div class="col-lg-6">

                    <div class="local-information">

                        <span class="local-number">
                            01 / GIỚI THIỆU
                        </span>


                        <h3>
                            Xã Hòa Long
                        </h3>


                        <div class="local-address">

                            <span>⌖</span>

                            Quốc lộ 80, Ấp 1,
                            Xã Hòa Long, Đồng Tháp

                        </div>


    


                        <p class="local-intro">

                            Xã Hòa Long được thành lập theo Nghị quyết số 1663/NQ-UBTVQH15 của Ủy ban Thường vụ Quốc hội. Xã chính thức đi vào hoạt động từ ngày 01/07/2025.Đơn vị sáp nhập: Hợp nhất toàn bộ diện tích và dân số của thị trấn Lai Vung cùng 3 xã gồm Long Hậu, Long Thắng và Hòa Long (cũ).Trụ sở Ủy ban Nhân dân (UBND): Đặt tại trụ sở của UBND thị trấn Lai Vung cũ.Quản lý hành chính: Được chia thành 22 ấp trực thuộc.Lãnh đạo Đảng ủy: Ông Huỳnh Minh Thức giữ chức vụ Bí thư Đảng ủy xã (nhiệm kỳ 2025–2030).

                        </p>

                        <p class="local-intro">

                        Tâm điểm nông nghiệp công nghệ cao: Thừa hưởng thế mạnh vùng Lai Vung, xã Hòa Long định vị trở thành hạt nhân phát triển nông nghiệp xanh, sạch và ứng dụng công nghệ cao. Mục tiêu đạt thu nhập bình quân đầu người trên 125 triệu đồng/năm vào năm 2030.
                        
                        </p>


                        <p class="local-intro">

                        Hiện đại hóa dịch vụ công: Mọi thủ tục hành chính như đăng ký cư trú, lý lịch tư pháp, cấp đổi giấy tờ đều được tích hợp xử lý trực tuyến qua cổng dịch vụ công trực tuyến và liên kết chặt chẽ với ứng dụng VNeID, giúp người dân sau sáp nhập giải quyết thủ tục nhanh chóng.
                        </p>

                        <p class="local-intro">

                        Xây dựng Nông thôn mới: Phấn đấu đạt chuẩn nông thôn mới kiểu mẫu giai đoạn 2025–2030.

                        </p>

                        <p class="local-intro">

                            Xã Hòa Long là địa phương
                            có những giá trị đặc trưng
                            về văn hóa, đời sống,
                            nông nghiệp và các sản phẩm
                            địa phương.

                        </p>





                        <!-- ĐẶC TRƯNG -->

                        <div class="local-features">

                            <div class="local-feature">

                                <div class="feature-icon">
                                    🎭
                                </div>

                                <div>
                                    <strong>
                                        Văn hóa
                                    </strong>

                                    <small>
                                        Giá trị địa phương
                                    </small>
                                </div>

                            </div>


                            <div class="local-feature">

                                <div class="feature-icon">
                                    🏞️
                                </div>

                                <div>
                                    <strong>
                                        Điểm đến
                                    </strong>

                                    <small>
                                        Khám phá địa phương
                                    </small>
                                </div>

                            </div>


                            <div class="local-feature">

                                <div class="feature-icon">
                                    🎁
                                </div>

                                <div>
                                    <strong>
                                        OCOP
                                    </strong>

                                    <small>
                                        Sản phẩm đặc trưng
                                    </small>
                                </div>

                            </div>


                            <div class="local-feature">

                                <div class="feature-icon">
                                    🎉
                                </div>

                                <div>
                                    <strong>
                                        Sự kiện
                                    </strong>

                                    <small>
                                        Hoạt động cộng đồng
                                    </small>
                                </div>

                            </div>

                        </div>


        

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     CÁC NHÓM THÔNG TIN
===================================================== -->
<section class="explore-info-section">

    <div class="container">

        <div class="explore-info-heading">

            <span>
                KHÁM PHÁ ĐỊA PHƯƠNG
            </span>

            <h2>
                Khám phá xã Hòa Long
            </h2>

            <p>
                Các nhóm thông tin nổi bật về địa phương
            </p>

        </div>


        <div class="row g-4">


            <!-- THÔNG TIN CHUNG -->
            <div class="col-lg-4 col-md-6">

                <div class="explore-info-card">

                    <div class="explore-info-icon">
                        📍
                    </div>

                    <h3>
                        Thông tin chung
                    </h3>

                    <p>
                        Giới thiệu vị trí, đặc điểm địa phương
                        và các thông tin tổng quan về xã Hòa Long.
                    </p>

                </div>

            </div>


            <!-- VĂN HÓA -->
            <div class="col-lg-4 col-md-6">

                <div class="explore-info-card">

                    <div class="explore-info-icon">
                        🎭
                    </div>

                    <h3>
                        Văn hóa - Lịch sử
                    </h3>

                    <p>
                        Tìm hiểu những nét văn hóa, truyền thống
                        và các giá trị lịch sử tiêu biểu của địa phương.
                    </p>

                </div>

            </div>


            <!-- DU LỊCH -->
            <div class="col-lg-4 col-md-6">

                <div class="explore-info-card">

                    <div class="explore-info-icon">
                        🏞️
                    </div>

                    <h3>
                        Du lịch - Điểm đến
                    </h3>

                    <p>
                        Khám phá các địa điểm, điểm tham quan
                        và không gian đặc trưng của xã Hòa Long.
                    </p>

                    <a
                        href="locations.php"
                        class="explore-info-link"
                    >
                        Xem địa điểm
                        <span>→</span>
                    </a>

                </div>

            </div>


            <!-- ẨM THỰC -->
            <div class="col-lg-4 col-md-6">

                <div class="explore-info-card">

                    <div class="explore-info-icon">
                        🍜
                    </div>

                    <h3>
                        Ẩm thực địa phương
                    </h3>

                    <p>
                        Giới thiệu các món ăn, đặc sản
                        và những nét ẩm thực đặc trưng của địa phương.
                    </p>

                    <a
                        href="locations.php"
                        class="explore-info-link"
                    >
                        Khám phá
                        <span>→</span>
                    </a>

                </div>

            </div>


            <!-- OCOP -->
            <div class="col-lg-4 col-md-6">

                <div class="explore-info-card">

                    <div class="explore-info-icon">
                        🎁
                    </div>

                    <h3>
                        Sản phẩm OCOP
                    </h3>

                    <p>
                        Giới thiệu sản phẩm OCOP
                        và các sản phẩm đặc trưng gắn với địa phương.
                    </p>

                    <a
                        href="locations.php"
                        class="explore-info-link"
                    >
                        Xem sản phẩm
                        <span>→</span>
                    </a>

                </div>

            </div>


            <!-- SỰ KIỆN -->
            <div class="col-lg-4 col-md-6">

                <div class="explore-info-card">

                    <div class="explore-info-icon">
                        🎉
                    </div>

                    <h3>
                        Sự kiện - Hoạt động
                    </h3>

                    <p>
                        Theo dõi các sự kiện, hoạt động văn hóa
                        và những chương trình diễn ra tại địa phương.
                    </p>

                    <a
                        href="events.php"
                        class="explore-info-link"
                    >
                        Xem sự kiện
                        <span>→</span>
                    </a>

                </div>

            </div>


        </div>

    </div>

</section>


<!-- =====================================================
     BẢN ĐỒ
===================================================== -->

<section class="py-5">

<div class="container">

<div
    class="
        explore-card
        text-center
        shadow
    "
>

    <div
        style="
            font-size:55px;
        "
    >
        🗺️
    </div>


    <h2 class="fw-bold mt-3">

        Khám phá xã Hòa Long
        trên bản đồ số

    </h2>


    <p
        class="
            mx-auto
            mt-3
            mb-4
        "
        style="
            max-width:700px;
            color:
                rgba(
                    255,
                    255,
                    255,
                    0.9
                );
        "
    >

        Tìm kiếm các địa điểm văn hóa,
        du lịch, ẩm thực, sản phẩm OCOP
        và những điểm đến tiêu biểu
        trên bản đồ trực quan.

    </p>


    <a
        href="map.php"
        class="
            btn
            btn-light
            btn-lg
            text-success
            fw-bold
        "
    >
        🗺️ Mở bản đồ
    </a>

</div>

</div>

</section>


<!-- =====================================================
     FOOTER
===================================================== -->

<footer
    class="
        bg-dark
        text-white
        mt-4
    "
>

<div class="container py-4">

    <div
        class="
            d-flex
            flex-wrap
            justify-content-between
            align-items-center
            gap-3
        "
    >

        <div>

            <div class="fw-bold">

                🗺️ Bản đồ số
                Văn hóa - Du lịch

            </div>

            <small class="text-white-50">

                Thông tin và điểm đến
                xã Hòa Long

            </small>

        </div>


        <a
            href="index.php"
            class="
                btn
                btn-outline-light
                btn-sm
            "
        >
            ← Về trang chủ
        </a>

    </div>

</div>

</footer>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const slides = document.querySelectorAll(".hero-bg-slide");
    const prevButton = document.getElementById("heroPrev");
    const nextButton = document.getElementById("heroNext");
    const slideNumber = document.getElementById("heroSlideNumber");
    const progressBar = document.getElementById("heroProgressBar");

    if (!slides.length) {
        return;
    }

    let currentSlide = 0;
    let sliderTimer = null;
    const slideDuration = 5000;

    function restartProgress() {
        if (!progressBar) return;

        progressBar.style.transition = "none";
        progressBar.style.width = "0%";
        void progressBar.offsetWidth;
        progressBar.style.transition = "width " + slideDuration + "ms linear";
        progressBar.style.width = "100%";
    }

    function showSlide(index) {
        if (index >= slides.length) index = 0;
        if (index < 0) index = slides.length - 1;

        slides.forEach(function (slide) {
            slide.classList.remove("active");
        });

        currentSlide = index;
        slides[currentSlide].classList.add("active");

        if (slideNumber) {
            slideNumber.textContent =
                String(currentSlide + 1).padStart(2, "0") +
                " / " +
                String(slides.length).padStart(2, "0");
        }

        restartProgress();
    }

    function nextSlide() {
        showSlide(currentSlide + 1);
    }

    function previousSlide() {
        showSlide(currentSlide - 1);
    }

    function startSlider() {
        clearInterval(sliderTimer);
        sliderTimer = setInterval(nextSlide, slideDuration);
    }

    if (nextButton) {
        nextButton.addEventListener("click", function () {
            nextSlide();
            startSlider();
        });
    }

    if (prevButton) {
        prevButton.addEventListener("click", function () {
            previousSlide();
            startSlider();
        });
    }

    showSlide(0);
    startSlider();
});
</script>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>