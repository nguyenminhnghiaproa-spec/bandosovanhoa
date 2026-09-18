<?php

include("config/database.php");


/* =====================================================
   LẤY DANH MỤC
===================================================== */

$categorySql = "
    SELECT
        c.category_id,
        c.category_name,
        c.description,
        COUNT(l.location_id) AS total_locations

    FROM categories c

    LEFT JOIN locations l
        ON c.category_id = l.category_id
        AND l.status = 'active'

    GROUP BY
        c.category_id,
        c.category_name,
        c.description

    ORDER BY c.category_id ASC
";

$categories =
    $conn->query($categorySql);


/* =====================================================
   LẤY 6 ĐỊA ĐIỂM MỚI
===================================================== */

$locationSql = "
    SELECT
        l.location_id,
        l.name,
        l.address,
        l.description,
        c.category_name,

        (
            SELECT i.image_url
            FROM images i
            WHERE i.location_id = l.location_id
            ORDER BY i.image_id DESC
            LIMIT 1
        ) AS image_url

    FROM locations l

    LEFT JOIN categories c
        ON l.category_id = c.category_id

    WHERE l.status = 'active'

    ORDER BY l.location_id DESC

    LIMIT 6
";

$locations =
    $conn->query($locationSql);


/* =====================================================
   THỐNG KÊ
===================================================== */

$totalLocations = 0;
$totalCategories = 0;
$totalEvents = 0;


$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM locations
    WHERE status = 'active'
");

if ($result) {

    $row = $result->fetch_assoc();

    $totalLocations =
        (int)$row['total'];

}


$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM categories
");

if ($result) {

    $row = $result->fetch_assoc();

    $totalCategories =
        (int)$row['total'];

}


$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM events
");

if ($result) {

    $row = $result->fetch_assoc();

    $totalEvents =
        (int)$row['total'];

}


/* =====================================================
   ICON DANH MỤC
===================================================== */

function getCategoryIcon($name)
{

    $name =
        mb_strtolower(
            trim($name),
            'UTF-8'
        );


    if (
        str_contains(
            $name,
            'văn hóa'
        )
    ) {
        return '🎭';
    }


    if (
        str_contains(
            $name,
            'du lịch'
        )
    ) {
        return '🏞️';
    }


    if (
        str_contains(
            $name,
            'di tích'
        )
    ) {
        return '🏛️';
    }


    if (
        str_contains(
            $name,
            'ẩm thực'
        )
    ) {
        return '🍜';
    }


    if (
        str_contains(
            $name,
            'ocop'
        )
    ) {
        return '🎁';
    }


    if (
        str_contains(
            $name,
            'sự kiện'
        )
    ) {
        return '🎉';
    }


    return '📍';

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
    Bản đồ số Văn hóa - Du lịch địa phương
</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<style>

/* =====================================================
   CHUNG
===================================================== */

html {

    scroll-behavior: smooth;

}


body {

    margin: 0;

    background: #f7f8fa;

    color: #212529;

}


.section-title {

    font-weight: 700;

    margin-bottom: 8px;

}


.section-description {

    color: #6c757d;

}


/* =====================================================
   NAVBAR
===================================================== */

.main-navbar {

    background: #198754;

    box-shadow:
        0 2px 10px
        rgba(0, 0, 0, 0.10);

}


.navbar-brand {

    font-weight: 700;

    font-size: 20px;

}


.navbar .nav-link {

    color:
        rgba(
            255,
            255,
            255,
            0.9
        );

}


.navbar .nav-link:hover {

    color: white;

}


/* =====================================================
   HERO
===================================================== */

.hero {

    min-height: 540px;

    display: flex;

    align-items: center;

    position: relative;

    overflow: hidden;

    color: white;

    background:
        linear-gradient(
            135deg,
            #146c43,
            #198754,
            #20a56b
        );

}


.hero::after {

    content: "";

    position: absolute;

    width: 430px;

    height: 430px;

    border-radius: 50%;

    background:
        rgba(
            255,
            255,
            255,
            0.08
        );

    right: -100px;

    top: -130px;

}


.hero::before {

    content: "";

    position: absolute;

    width: 300px;

    height: 300px;

    border-radius: 50%;

    background:
        rgba(
            255,
            255,
            255,
            0.06
        );

    left: -100px;

    bottom: -120px;

}


.hero-content {

    position: relative;

    z-index: 2;

}


.hero h1 {

    font-size: 48px;

    font-weight: 800;

    line-height: 1.2;

}


.hero-description {

    max-width: 700px;

    font-size: 18px;

    line-height: 1.7;

    color:
        rgba(
            255,
            255,
            255,
            0.92
        );

}


.hero-icon {

    font-size: 170px;

    text-align: center;

}


/* =====================================================
   THỐNG KÊ
===================================================== */

.stats-section {

    margin-top: -55px;

    position: relative;

    z-index: 5;

}


.stat-box {

    border: none;

    border-radius: 16px;

    background: white;

    height: 100%;

    text-align: center;

    padding: 25px 15px;

    box-shadow:
        0 5px 22px
        rgba(
            0,
            0,
            0,
            0.10
        );

}


.stat-icon {

    font-size: 35px;

}


.stat-number {

    font-size: 30px;

    font-weight: 800;

    color: #198754;

}


.stat-text {

    color: #6c757d;

}


/* =====================================================
   DANH MỤC
===================================================== */

.category-card {

    border: none;

    border-radius: 16px;

    height: 100%;

    transition: 0.25s;

}


.category-card:hover {

    transform:
        translateY(-6px);

    box-shadow:
        0 10px 25px
        rgba(
            0,
            0,
            0,
            0.12
        );

}


.category-icon {

    width: 70px;

    height: 70px;

    border-radius: 50%;

    background: #e9f7ef;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 35px;

    margin-bottom: 18px;

}


/* =====================================================
   ĐỊA ĐIỂM
===================================================== */

.location-card {

    border: none;

    border-radius: 16px;

    overflow: hidden;

    height: 100%;

    transition: 0.25s;

}


.location-card:hover {

    transform:
        translateY(-5px);

    box-shadow:
        0 10px 25px
        rgba(
            0,
            0,
            0,
            0.12
        );

}


.location-image {

    width: 100%;

    height: 220px;

    object-fit: cover;

}


.location-placeholder {

    width: 100%;

    height: 220px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 65px;

    background: #e9ecef;

}


.location-description {

    color: #6c757d;

    display: -webkit-box;

    -webkit-line-clamp: 3;

    -webkit-box-orient: vertical;

    overflow: hidden;

    min-height: 72px;

}


/* =====================================================
   CTA
===================================================== */

.map-cta {

    background:
        linear-gradient(
            135deg,
            #198754,
            #157347
        );

    border-radius: 22px;

    color: white;

    padding: 55px 30px;

}


/* =====================================================
   FOOTER
===================================================== */

footer {

    background: #212529;

    color:
        rgba(
            255,
            255,
            255,
            0.8
        );

}


.footer-title {

    color: white;

    font-weight: 700;

}


/* =====================================================
   MOBILE
===================================================== */

@media (
    max-width: 767px
) {

    .hero {

        min-height: auto;

        padding:
            70px 0
            100px 0;

    }


    .hero h1 {

        font-size: 34px;

    }


    .hero-icon {

        display: none;

    }


    .stats-section {

        margin-top: -50px;

    }

}

</style>

<link rel="stylesheet" href="/bandosovanhoa/assets/css/public-theme.css?v=1">

</head>


<body>

<?php

$currentPage = 'home';

require_once("includes/navbar.php");

?>


<!-- =====================================================
     HERO
===================================================== -->

<section class="hero">

<div class="container hero-content">


<div class="row align-items-center">


    <div class="col-lg-8">


        <div
            class="
                badge
                bg-light
                text-success
                mb-3
                px-3
                py-2
            "
        >

            📍 Khám phá địa phương trên nền tảng số

        </div>


        <h1>

            Bản đồ số
            <br>

            Văn hóa - Du lịch
            <br>

            địa phương

        </h1>


        <p
            class="
                hero-description
                mt-4
                mb-4
            "
        >

            Khám phá các địa điểm văn hóa,
            du lịch, di tích, ẩm thực,
            sản phẩm OCOP và các hoạt động
            đặc trưng của địa phương trên
            bản đồ trực quan.

        </p>


        <div
            class="
                d-flex
                flex-wrap
                gap-2
            "
        >


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

                🗺️ Khám phá bản đồ

            </a>


            <a
                href="#locations"
                class="
                    btn
                    btn-outline-light
                    btn-lg
                "
            >

                📍 Xem địa điểm

            </a>

            <a
                href="about.php"
                class="
                    btn
                    btn-outline-light
                    btn-lg
                "
            >
                🏘️ Thông tin xã Hòa Long
            </a>


        </div>


    </div>



    <div class="col-lg-4">

        <div class="hero-icon">

            🗺️

        </div>

    </div>


</div>

</div>

</section>



<!-- =====================================================
     THỐNG KÊ
===================================================== -->

<section class="stats-section">

<div class="container">

<div class="row g-3 justify-content-center">


    <div class="col-md-4">

        <div class="stat-box">

            <div class="stat-icon">
                📍
            </div>

            <div class="stat-number">

                <?= $totalLocations ?>

            </div>

            <div class="stat-text">

                Địa điểm

            </div>

        </div>

    </div>


    <div class="col-md-4">

        <div class="stat-box">

            <div class="stat-icon">
                🏷️
            </div>

            <div class="stat-number">

                <?= $totalCategories ?>

            </div>

            <div class="stat-text">

                Danh mục

            </div>

        </div>

    </div>


    <div class="col-md-4">

        <div class="stat-box">

            <div class="stat-icon">
                🎉
            </div>

            <div class="stat-number">

                <?= $totalEvents ?>

            </div>

            <div class="stat-text">

                Sự kiện

            </div>

        </div>

    </div>


</div>

</div>

</section>



<!-- =====================================================
     GIỚI THIỆU
===================================================== -->

<section class="py-5 mt-4">

<div class="container">


    <div class="row align-items-center g-5">


        <div class="col-lg-6">

            <div
                style="
                    font-size:150px;
                    text-align:center;
                "
            >

                🌏

            </div>

        </div>


        <div class="col-lg-6">


            <div
                class="
                    text-success
                    fw-bold
                    mb-2
                "
            >

                GIỚI THIỆU

            </div>


            <h2 class="section-title">

                Khám phá giá trị
                văn hóa và du lịch địa phương

            </h2>


            <p
                class="
                    section-description
                    mt-3
                "
            >

                Hệ thống bản đồ số hỗ trợ
                người dân và du khách tìm kiếm,
                tra cứu và tiếp cận thông tin
                về các địa điểm văn hóa,
                du lịch và điểm đến đặc trưng
                của địa phương.

            </p>


            <p class="section-description">

                Mỗi địa điểm có thể cung cấp
                vị trí trên bản đồ, địa chỉ,
                hình ảnh, nội dung giới thiệu,
                sự kiện liên quan và mã QR
                để thuận tiện tra cứu.

            </p>


            <a
                href="map.php"
                class="btn btn-success mt-2"
            >

                Xem bản đồ →

            </a>


        </div>


    </div>


</div>

</section>



<!-- =====================================================
     DANH MỤC
===================================================== -->

<section
    id="categories"
    class="py-5 bg-white"
>

<div class="container">


    <div
        class="
            text-center
            mb-5
        "
    >

        <h2 class="section-title">

            Danh mục khám phá

        </h2>


        <p class="section-description">

            Khám phá các nhóm địa điểm
            văn hóa và du lịch địa phương

        </p>

    </div>



    <div class="row g-4">


        <?php if (
            $categories
            &&
            $categories->num_rows > 0
        ): ?>


            <?php while (
                $category =
                    $categories->fetch_assoc()
            ): ?>


                <div
                    class="
                        col-lg-4
                        col-md-6
                    "
                >


                    <div
                        class="
                            card
                            category-card
                            shadow-sm
                        "
                    >


                        <div class="card-body p-4">


                            <div
                                class="category-icon"
                            >

                                <?= getCategoryIcon(
                                    $category[
                                        'category_name'
                                    ]
                                ) ?>

                            </div>


                            <h4 class="fw-bold">

                                <?= htmlspecialchars(
                                    $category[
                                        'category_name'
                                    ],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </h4>


                            <p class="text-muted">

                                <?= htmlspecialchars(
                                    $category[
                                        'description'
                                    ]
                                    ?: 'Khám phá các địa điểm thuộc danh mục này.',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </p>


                            <div
                                class="
                                    d-flex
                                    justify-content-between
                                    align-items-center
                                "
                            >


                                <span
                                    class="
                                        badge
                                        bg-success
                                    "
                                >

                                    <?= (int)$category[
                                        'total_locations'
                                    ] ?>

                                    địa điểm

                                </span>


                                <a
                                    href="map.php?category=<?= urlencode(
                                        $category['category_name']
                                    ) ?>"
                                    class="
                                        text-success
                                        text-decoration-none
                                        fw-bold
                                    "
                                >
                                    Khám phá →
                                </a>


                            </div>


                        </div>


                    </div>


                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <div class="col-12">

                <div
                    class="
                        alert
                        alert-info
                        text-center
                    "
                >

                    Chưa có danh mục.

                </div>

            </div>


        <?php endif; ?>


    </div>


</div>

</section>



<!-- =====================================================
     ĐỊA ĐIỂM
===================================================== -->

<section
    id="locations"
    class="py-5"
>

<div class="container">


    <div
        class="
            d-flex
            flex-wrap
            justify-content-between
            align-items-end
            gap-3
            mb-4
        "
    >


        <div>

            <h2 class="section-title">

                📍 Địa điểm khám phá

            </h2>


            <p
                class="
                    section-description
                    mb-0
                "
            >

                Một số địa điểm đang có
                trên hệ thống

            </p>

        </div>


        <a
            href="locations.php"
            class="btn btn-outline-success"
        >
            Xem tất cả địa điểm →
        </a>


    </div>



    <div class="row g-4">


    <?php if (
        $locations
        &&
        $locations->num_rows > 0
    ): ?>


        <?php while (
            $location =
                $locations->fetch_assoc()
        ): ?>


            <div
                class="
                    col-lg-4
                    col-md-6
                "
            >


                <div
                    class="
                        card
                        location-card
                        shadow-sm
                    "
                >


                    <?php if (
                        !empty(
                            $location['image_url']
                        )
                    ): ?>


                        <img
                            src="<?= htmlspecialchars(
                                $location['image_url'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            class="location-image"
                            alt="<?= htmlspecialchars(
                                $location['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >


                    <?php else: ?>


                        <div
                            class="location-placeholder"
                        >

                            📍

                        </div>


                    <?php endif; ?>



                    <div
                        class="
                            card-body
                            p-4
                        "
                    >


                        <div class="mb-2">

                            <span
                                class="
                                    badge
                                    bg-success
                                "
                            >

                                <?= htmlspecialchars(
                                    $location[
                                        'category_name'
                                    ]
                                    ?? 'Địa điểm',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </span>

                        </div>


                        <h5 class="fw-bold">

                            <?= htmlspecialchars(
                                $location['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </h5>


                        <div
                            class="
                                text-muted
                                small
                                mb-3
                            "
                        >

                            📍

                            <?= htmlspecialchars(
                                $location[
                                    'address'
                                ]
                                ?? 'Chưa cập nhật địa chỉ',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </div>


                        <p
                            class="
                                location-description
                            "
                        >

                            <?= htmlspecialchars(
                                $location[
                                    'description'
                                ]
                                ?: 'Thông tin địa điểm đang được cập nhật.',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </p>


                        <a
                            href="location_detail.php?id=<?= (int)$location['location_id'] ?>"
                            class="
                                btn
                                btn-outline-success
                                w-100
                            "
                        >

                            👁 Xem chi tiết

                        </a>


                    </div>


                </div>


            </div>


        <?php endwhile; ?>


    <?php else: ?>


        <div class="col-12">

            <div
                class="
                    alert
                    alert-info
                    text-center
                "
            >

                Chưa có địa điểm
                để hiển thị.

            </div>

        </div>


    <?php endif; ?>


    </div>


</div>

</section>



<!-- =====================================================
     KHÁM PHÁ BẢN ĐỒ
===================================================== -->

<section class="py-5">

<div class="container">


    <div
        class="
            map-cta
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


        <h2
            class="
                fw-bold
                mt-3
            "
        >

            Khám phá địa phương
            trên bản đồ số

        </h2>


        <p
            class="
                mb-4
                mx-auto
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

            Tìm kiếm địa điểm,
            lọc theo danh mục,
            xem vị trí trên bản đồ
            và truy cập nhanh thông tin
            bằng mã QR.

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
     KHÁM PHÁ THÊM
===================================================== -->

<section class="home-more-section py-5">
<div class="container">

    <div class="text-center mb-5">
        <div class="text-success fw-bold mb-2">KHÁM PHÁ HÒA LONG</div>
        <h2 class="section-title">Trải nghiệm địa phương trên nền tảng số</h2>
        <p class="section-description mx-auto" style="max-width:720px;">
            Tìm hiểu thông tin xã, khám phá điểm đến, theo dõi sự kiện và tra cứu vị trí trực quan trên bản đồ số.
        </p>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <a href="about.php" class="home-feature-card text-decoration-none">
                <div class="home-feature-icon">🏘️</div>
                <div>
                    <span class="home-feature-label">GIỚI THIỆU</span>
                    <h3>Thông tin xã Hòa Long</h3>
                    <p>Tìm hiểu tổng quan, văn hóa, du lịch, ẩm thực và những nét đặc trưng của địa phương.</p>
                    <strong>Khám phá Hòa Long →</strong>
                </div>
            </a>
        </div>

        <div class="col-lg-4">
            <a href="events.php" class="home-feature-card text-decoration-none">
                <div class="home-feature-icon">🎉</div>
                <div>
                    <span class="home-feature-label">HOẠT ĐỘNG</span>
                    <h3>Sự kiện địa phương</h3>
                    <p>Theo dõi các sự kiện và hoạt động nổi bật được cập nhật trên hệ thống.</p>
                    <strong>Xem sự kiện →</strong>
                </div>
            </a>
        </div>

        <div class="col-lg-4">
            <a href="map.php" class="home-feature-card text-decoration-none">
                <div class="home-feature-icon">🗺️</div>
                <div>
                    <span class="home-feature-label">BẢN ĐỒ SỐ</span>
                    <h3>Khám phá trên bản đồ</h3>
                    <p>Tra cứu nhanh vị trí các địa điểm văn hóa, du lịch và điểm đến của Hòa Long.</p>
                    <strong>Mở bản đồ →</strong>
                </div>
            </a>
        </div>
    </div>




    <div class="home-journey-banner mt-5">
        <div class="home-journey-icon">🌿</div>
        <div class="home-journey-copy">
            <span class="home-feature-label text-white-50">TRẢI NGHIỆM SÁNG TẠO</span>
            <h2>Hành trình khám phá Hòa Long</h2>
            <p>Chọn Văn hóa, Ẩm thực, OCOP hoặc Du lịch để hệ thống gợi ý các điểm phù hợp và xem hành trình trực tiếp trên bản đồ số.</p>
        </div>
        <a href="journey.php" class="btn btn-light btn-lg fw-bold text-success">Bắt đầu hành trình →</a>
    </div>

</div>
</section>


<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="mt-4">

<div class="container py-5">


    <div class="row g-4">


        <div class="col-md-7">


            <h5 class="footer-title">

                🗺️ Bản đồ số
                Văn hóa - Du lịch

            </h5>


            <p class="mb-0">

                Hệ thống hỗ trợ giới thiệu,
                quảng bá và tra cứu thông tin
                văn hóa, du lịch và điểm đến
                địa phương trên nền tảng số.

            </p>


        </div>


        <div class="col-md-5">


            <h6 class="footer-title">

                Truy cập nhanh

            </h6>


            <div class="mb-2">

                <a
                    href="map.php"
                    class="
                        text-white
                        text-decoration-none
                    "
                >

                    🗺️ Bản đồ địa điểm

                </a>

            </div>


            <div>

                <a
                    href="admin/login.php"
                    class="
                        text-white
                        text-decoration-none
                    "
                >

                    🔐 Trang quản trị

                </a>

            </div>


        </div>


    </div>


    <hr
        style="
            border-color:
                rgba(
                    255,
                    255,
                    255,
                    0.2
                );
        "
    >


    <div class="text-center">

        © <?= date('Y') ?>

        Bản đồ số Văn hóa - Du lịch địa phương

    </div>


</div>

</footer>



<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>