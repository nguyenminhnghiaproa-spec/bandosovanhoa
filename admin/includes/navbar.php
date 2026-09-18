<?php

$currentAdminPage =
    $currentAdminPage ?? '';

/* ==============================
   THÔNG TIN ADMIN
================================ */

$adminName =
    $_SESSION['admin_name']
    ?? $_SESSION['admin_username']
    ?? 'Quản trị viên';

?>

<nav
    class="
        navbar
        navbar-expand-lg
        navbar-dark
        bg-success
        shadow-sm
        no-print
    "
>
    <div class="container-fluid px-lg-4">

        <!-- LOGO -->

        <a
            class="navbar-brand fw-bold"
            href="/bandosovanhoa/admin/index.php"
        >
            🗺️ QUẢN TRỊ BẢN ĐỒ SỐ
        </a>


        <!-- NÚT MOBILE -->

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#adminNavbar"
            aria-controls="adminNavbar"
            aria-expanded="false"
            aria-label="Mở menu"
        >
            <span class="navbar-toggler-icon"></span>
        </button>


        <!-- MENU -->

        <div
            class="collapse navbar-collapse"
            id="adminNavbar"
        >

            <div class="navbar-nav ms-auto align-items-lg-center">

                <!-- TỔNG QUAN -->

                <a
                    class="nav-link
                    <?= $currentAdminPage === 'dashboard'
                        ? 'active fw-semibold'
                        : '' ?>"
                    href="/bandosovanhoa/admin/index.php"
                >
                    🏠 Tổng quan
                </a>


                <!-- ĐỊA ĐIỂM -->

                <a
                    class="nav-link
                    <?= $currentAdminPage === 'locations'
                        ? 'active fw-semibold'
                        : '' ?>"
                    href="/bandosovanhoa/admin/locations/index.php"
                >
                    📍 Địa điểm
                </a>


                <!-- DANH MỤC -->

                <a
                    class="nav-link
                    <?= $currentAdminPage === 'categories'
                        ? 'active fw-semibold'
                        : '' ?>"
                    href="/bandosovanhoa/admin/categories/index.php"
                >
                    🏷️ Danh mục
                </a>


                <!-- SỰ KIỆN -->

                <a
                    class="nav-link
                    <?= $currentAdminPage === 'events'
                        ? 'active fw-semibold'
                        : '' ?>"
                    href="/bandosovanhoa/admin/events/index.php"
                >
                    🎉 Sự kiện
                </a>


                <!-- XEM TRANG KHÁCH -->

                <a
                    class="nav-link"
                    href="/bandosovanhoa/index.php"
                    target="_blank"
                >
                    🌐 Xem trang khách
                </a>


                <!-- TÀI KHOẢN ADMIN -->

                <div class="nav-item dropdown ms-lg-2">

                    <a
                        class="
                            nav-link
                            dropdown-toggle
                            fw-semibold
                        "
                        href="#"
                        id="adminAccountDropdown"
                        role="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                        👤 <?= htmlspecialchars(
                            $adminName,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </a>


                    <ul
                        class="
                            dropdown-menu
                            dropdown-menu-end
                            shadow
                            border-0
                        "
                        aria-labelledby="adminAccountDropdown"
                    >

                        <!-- THÔNG TIN -->

                        <li>
                            <span
                                class="
                                    dropdown-item-text
                                    text-muted
                                    small
                                "
                            >
                                Đăng nhập với quyền quản trị
                            </span>
                        </li>


                        <li>
                            <hr class="dropdown-divider">
                        </li>


                        <!-- ĐỔI MẬT KHẨU -->

                        <li>
                            <a
                                class="dropdown-item"
                                href="/bandosovanhoa/admin/change_password.php"
                            >
                                🔐 Đổi mật khẩu
                            </a>
                        </li>


                        <li>
                            <hr class="dropdown-divider">
                        </li>


                        <!-- ĐĂNG XUẤT -->

                        <li>
                            <button
                                type="button"
                                class="
                                    dropdown-item
                                    text-danger
                                "
                                data-bs-toggle="modal"
                                data-bs-target="#globalLogoutModal"
                            >
                                🚪 Đăng xuất
                            </button>
                        </li>

                    </ul>

                </div>

            </div>

        </div>

    </div>

</nav>


<!-- =====================================================
     MODAL XÁC NHẬN ĐĂNG XUẤT
===================================================== -->

<div
    class="modal fade"
    id="globalLogoutModal"
    tabindex="-1"
    aria-labelledby="globalLogoutModalTitle"
    aria-hidden="true"
>
    <div
        class="
            modal-dialog
            modal-dialog-centered
        "
    >
        <div
            class="
                modal-content
                border-0
                shadow
                rounded-4
            "
        >

            <div class="modal-body text-center p-4">

                <div
                    class="mb-3"
                    style="font-size: 55px;"
                >
                    🚪
                </div>


                <h4
                    class="fw-bold mb-2"
                    id="globalLogoutModalTitle"
                >
                    Xác nhận đăng xuất
                </h4>


                <p class="text-muted mb-4">
                    Bạn có chắc chắn muốn đăng xuất
                    khỏi trang quản trị không?
                </p>


                <div
                    class="
                        d-flex
                        justify-content-center
                        gap-2
                    "
                >

                    <button
                        type="button"
                        class="btn btn-light px-4"
                        data-bs-dismiss="modal"
                    >
                        Hủy
                    </button>


                    <a
                        href="/bandosovanhoa/admin/logout.php"
                        class="btn btn-danger px-4"
                    >
                        🚪 Đăng xuất
                    </a>

                </div>

            </div>

        </div>

    </div>
</div>