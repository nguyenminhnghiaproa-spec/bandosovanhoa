<?php

$currentPage =
    $currentPage ?? '';

?>

<nav
    class="
        navbar
        navbar-expand-lg
        navbar-dark
        bg-success
        shadow-sm
    "
>
    <div class="container">

        <!-- LOGO -->

        <a
            class="navbar-brand fw-bold"
            href="/bandosovanhoa/index.php"
        >
            🗺️ BẢN ĐỒ SỐ
        </a>


        <!-- NÚT MOBILE -->

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#customerNavbar"
            aria-controls="customerNavbar"
            aria-expanded="false"
            aria-label="Mở menu"
        >
            <span class="navbar-toggler-icon"></span>
        </button>


        <!-- MENU -->

        <div
            class="collapse navbar-collapse"
            id="customerNavbar"
        >

            <div class="navbar-nav ms-auto">

                <!-- TRANG CHỦ -->

                <a
                    class="nav-link
                    <?= $currentPage === 'home'
                        ? 'active fw-semibold'
                        : '' ?>"
                    href="/bandosovanhoa/index.php"
                >
                    🏠 Trang chủ
                </a>


                <!-- ĐỊA ĐIỂM -->

                <a
                    class="nav-link
                    <?= $currentPage === 'locations'
                        ? 'active fw-semibold'
                        : '' ?>"
                    href="/bandosovanhoa/locations.php"
                >
                    📍 Địa điểm
                </a>


                <!-- SỰ KIỆN -->

                <a
                    class="nav-link
                    <?= $currentPage === 'events'
                        ? 'active fw-semibold'
                        : '' ?>"
                    href="/bandosovanhoa/events.php"
                >
                    🎉 Sự kiện
                </a>


                <!-- THÔNG TIN XÃ HÒA LONG -->

                <a
                    class="nav-link
                    <?= $currentPage === 'about'
                        ? 'active fw-semibold'
                        : '' ?>"
                    href="/bandosovanhoa/about.php"
                >
                    🏘️ Thông tin xã Hòa Long
                </a>


                <!-- HÀNH TRÌNH KHÁM PHÁ -->

                <a
                    class="nav-link
                    <?= $currentPage === 'journey'
                        ? 'active fw-semibold'
                        : '' ?>"
                    href="/bandosovanhoa/journey.php"
                >
                    🌿 Hành trình
                </a>


                <!-- BẢN ĐỒ -->

                <a
                    class="nav-link
                    <?= $currentPage === 'map'
                        ? 'active fw-semibold'
                        : '' ?>"
                    href="/bandosovanhoa/map.php"
                >
                    🗺️ Bản đồ
                </a>

            </div>

        </div>

    </div>
</nav>