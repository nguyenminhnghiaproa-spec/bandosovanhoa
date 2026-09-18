<?php

session_start();

include("../config/database.php");


/* ==========================================
   NẾU ĐÃ ĐĂNG NHẬP
========================================== */

if (isset($_SESSION['admin_id'])) {

    header("Location: index.php");
    exit();

}


$error = "";


/* ==========================================
   XỬ LÝ FORM
========================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username =
        trim($_POST['username'] ?? '');

    $password =
        $_POST['password'] ?? '';


    if (
        $username === ""
        ||
        $password === ""
    ) {

        $error =
            "Vui lòng nhập đầy đủ thông tin.";

    } else {

        $sql = "
            SELECT
                user_id,
                username,
                password,
                full_name,
                role
            FROM users
            WHERE username = ?
            LIMIT 1
        ";

        $stmt =
            $conn->prepare($sql);


        if (!$stmt) {

            die(
                "Lỗi prepare: "
                . $conn->error
            );

        }


        $stmt->bind_param(
            "s",
            $username
        );

        $stmt->execute();

        $result =
            $stmt->get_result();


        if ($result->num_rows === 1) {

            $user =
                $result->fetch_assoc();


            if (
                password_verify(
                    $password,
                    $user['password']
                )
            ) {

                session_regenerate_id(true);


                $_SESSION['admin_id'] =
                    $user['user_id'];

                $_SESSION['admin_username'] =
                    $user['username'];

                $_SESSION['admin_name'] =
                    $user['full_name'];

                $_SESSION['admin_role'] =
                    $user['role'];


                header(
                    "Location: index.php"
                );

                exit();

            }

        }


        $error =
            "Tên đăng nhập hoặc mật khẩu không đúng.";

    }

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
        Đăng nhập quản trị
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <style>

        body {

            min-height: 100vh;

            margin: 0;

            display: flex;

            justify-content: center;

            align-items: center;

            background: #f4f6f8;

        }


        .login-container {

            width: 100%;

            max-width: 430px;

            padding: 15px;

        }


        .login-card {

            border: none;

            border-radius: 18px;

        }


        .login-icon {

            font-size: 55px;

        }


        .login-title {

            font-weight: 700;

        }


        .form-control {

            height: 46px;

        }


        .login-button {

            height: 46px;

            font-weight: 600;

        }

    </style>

</head>


<body>


<div class="login-container">


    <div
        class="card login-card shadow"
    >


        <div
            class="card-body p-4 p-md-5"
        >


            <div
                class="text-center mb-4"
            >

                <div class="login-icon">

                    🗺️

                </div>


                <h3 class="login-title mt-2">

                    ĐĂNG NHẬP

                </h3>


                <p class="text-muted">

                    Quản trị bản đồ số
                    Văn hóa - Du lịch

                </p>

            </div>



            <?php if (
                $error !== ""
            ): ?>


                <div
                    class="alert alert-danger"
                >

                    <?= htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </div>


            <?php endif; ?>



            <form
                method="POST"
                action=""
            >


                <div class="mb-3">

                    <label
                        class="form-label"
                    >

                        Tên đăng nhập

                    </label>


                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        placeholder="Nhập tên đăng nhập"
                        value="<?= htmlspecialchars(
                            $_POST['username']
                            ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        autocomplete="username"
                        required
                    >

                </div>



                <div class="mb-4">

                    <label
                        class="form-label"
                    >

                        Mật khẩu

                    </label>


                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Nhập mật khẩu"
                        autocomplete="current-password"
                        required
                    >

                </div>



                <button
                    type="submit"
                    class="
                        btn
                        btn-success
                        login-button
                        w-100
                    "
                >

                    🔐 Đăng nhập

                </button>


            </form>



            <div
                class="text-center mt-4"
            >

                <a
                    href="../index.php"
                    class="text-decoration-none"
                >

                    ← Quay lại trang chủ

                </a>

            </div>


        </div>

    </div>

</div>


</body>

</html>