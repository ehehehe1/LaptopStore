<?php
session_start();
require 'db.php';
ob_start();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laptop Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link type="text/css" rel="stylesheet" href="/LaptopStore/Store/assets/css/style.css">
    <link type="text/css" rel="stylesheet" href="/LaptopStore/Store/assets/css/form.css">
    <link rel="stylesheet" href="/LaptopStore/Store/assets/css/responsive.css">
    <style>
        
        .box_search {
            display: flex;
            align-items: center;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 5px;
        }
        .box_search input {
            border: none;
            outline: none;
            padding: 5px;
            width: 200px;
        }
        .logo_search {
            cursor: pointer;
            width: 24px;
            height: 24px;
        }
        .class1 {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .shopping, .user {
            width: 24px;
            height: 24px;
        }
        .header__cart {
            position: relative;
        }
        .header__cart #cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #e63946;
            color: #fff;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
        }
        .user__info {
            position: absolute;
            top: 40px;
            right: 0;
            background: #fff;
            border: 1px solid #ccc;
            padding: 10px;
            display: none;
        }
        #user:hover .user__info {
            display: block;
        }
        .form-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }
        .form-content {
            padding: 20px;
        }
        .form-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            z-index: 999;
            animation: fadeIn 0.3s ease;
        }
        .close-form-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            cursor: pointer;
            font-size: 24px;
            color: #333;
            font-weight: bold;
            background: #f0f0f0;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
        }
        .close-form-btn:hover {
            background: #ddd;
            color: #e63946;
        }
        .form-container h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.5rem;
            text-align: center;
        }
        .form-container label {
            display: block;
            margin-top: 10px;
            font-weight: 500;
            color: #555;
        }
        .form-container input, .form-container textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-container button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-container button:hover {
            background: #0056b3;
        }
        .form-container .error {
            color: red;
            margin-bottom: 10px;
            text-align: center;
            font-size: 0.9rem;
        }
        .form-container .signup a, .form-container .signin a {
            color: #007bff;
            text-decoration: none;
        }
        .form-container .signup a:hover, .form-container .signin a:hover {
            text-decoration: underline;
        }
        .password__container {
            position: relative;
        }
        .showEyeLogin, .hideEyeLogin, .showEyeRegister, .hideEyeRegister {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
        }
        .hide {
            display: none;
        }
        .policy {
            margin: 10px 0;
            font-size: 14px;
            text-align: center;
        }
        @keyframes slideIn {
            from { transform: translate(-50%, -60%); opacity: 0; }
            to { transform: translate(-50%, -50%); opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @media (max-width: 768px) {
            .form-container {
                width: 95%;
                max-height: 70vh;
            }
            .box_search input {
                width: 150px;
            }
        }
        @media (max-width: 480px) {
            .form-container {
                width: 98%;
                max-height: 60vh;
            }
            .close-form-btn {
                width: 32px;
                height: 32px;
                font-size: 20px;
            }
            .box_search input {
                width: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <a href="index.php"><img width="100%" src="assets/img/logo.png" alt="logo"></a>
        </div>
        <div class="box_search">
            <input type="text" placeholder="Tìm kiếm sản phẩm" id="search">
            <img src="assets/img/search.png" alt="search" class="logo_search" onclick="searchProduct();">
        </div>
        <div class="class1">
            <div id="shopping__icon">
                <a href="" onclick="checkLoginAndRedirectToCart()"><img class="shopping" src="assets/img/shopping.png" alt="shopping"></a>
            </div>
            <?php
            if (isset($_SESSION['username'])) {
                echo '<a href="/LaptopStore/Store/layout/logout.php" style="color: red; margin-right: 10px;">Đăng xuất</a>';
            } 
            ?>
            <div id="user__login-change">
                <div id="user" onclick="showForm()"><img class="user" src="assets/img/user.png" alt="user"></div>
                <?php if (isset($_SESSION['username'])): ?>
                    <div class="user__info">
                        <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="/LaptopStore/Store/index.php?page=logout">Đăng xuất</a>
                    </div>
                <?php endif; ?>
            </div>
            <div id="menuMobile">
                <div onclick="showmenuMobile()"><i class="fa-solid fa-bars"></i></div>
            </div>
        </div>
    </div>

    <!-- Form overlay (tùy chọn) -->
    <div class="form-overlay" id="form-overlay"></div>

    <!-- Form đăng nhập -->
    <div class="form-container" id="login-form-container">
        <span class="close-form-btn" onclick="hideLoginForm()">×</span>
        <div class="form-content">
            <h2>Đăng nhập</h2>
            <div id="login-message" class="error" style="display: none;"></div>
            <form id="login-form">
                <label for="loginUsername">Tên đăng nhập:</label>
                <input type="text" name="loginUsername" id="loginUsername" placeholder="Nhập tên đăng nhập của bạn" required>
                <div id="Username_login--error" class="error" style="display: none;"></div>
                <label for="loginPassword">Mật khẩu:</label>
                <div class="password__container">
                    <input type="password" name="loginPassword" id="loginPassword" placeholder="Nhập mật khẩu của bạn" required>
                    <button type="button" class="showEyeLogin">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                    <button type="button" class="hideEyeLogin hide">
                        <i class="fa-solid fa-eye-slash"></i>
                    </button>
                </div>
                <div id="Pass_login--error" class="error" style="display: none;"></div>
                <button type="submit">Đăng nhập</button>
            </form>
            <div class="signup" style="margin-top: 15px; text-align: center;">
                <p>Chưa có tài khoản? <a href="#" onclick="showLoginForm('register')">Đăng ký ngay</a></p>
            </div>
        </div>
    </div>

    <!-- Form đăng ký -->
    <div class="form-container" id="register-form-container">
        <span class="close-form-btn" onclick="hideLoginForm()">×</span>
        <div class="form-content">
            <h2>Tạo tài khoản</h2>
            <div id="register-message" class="error" style="display: none;"></div>
            <form id="register-form">
                <label for="registerUsername">Tên đăng nhập:</label>
                <input type="text" name="registerUsername" id="registerUsername" placeholder="Nhập tên đăng nhập của bạn" required>
                <div id="Username_reg--error" class="error" style="display: none;"></div>
                <label for="registerName">Họ tên:</label>
                <input type="text" name="registerName" id="registerName" placeholder="Nhập họ tên của bạn" required>
                <div id="Name_reg--error" class="error" style="display: none;"></div>
                <label for="registerEmail">Email:</label>
                <input type="email" name="registerEmail" id="registerEmail" placeholder="Nhập email của bạn" required>
                <div id="Email_reg--error" class="error" style="display: none;"></div>
                <label for="registerPassword">Mật khẩu:</label>
                <div class="password__container">
                    <input type="password" name="registerPassword" id="registerPassword" placeholder="Nhập mật khẩu của bạn" required>
                    <button type="button" class="showEyeRegister">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                    <button type="button" class="hideEyeRegister hide">
                        <i class="fa-solid fa-eye-slash"></i>
                    </button>
                </div>
                <div id="Pass_reg--error" class="error" style="display: none;"></div>
                <label for="registerPhone">Số điện thoại:</label>
                <input type="text" name="registerPhone" id="registerPhone" placeholder="Nhập số điện thoại" required>
                <div id="Phone_reg--error" class="error" style="display: none;"></div>
                <label for="registerAddress">Địa chỉ:</label>
                <textarea name="registerAddress" id="registerAddress" rows="3" placeholder="Nhập địa chỉ của bạn" required></textarea>
                <div id="Address_reg--error" class="error" style="display: none;"></div>
                <p class="policy">
                    Bằng việc đăng ký, bạn đồng ý với
                    <a href="#">Điều khoản dịch vụ</a> và
                    <a href="#">Chính sách bảo mật</a>.
                </p>
                <button type="submit">Đăng ký</button>
            </form>
            <div class="signin" style="margin-top: 15px; text-align: center;">
                <p>Bạn đã có tài khoản? <a href="#" onclick="showLoginForm('login')">Đăng nhập</a></p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="/LaptopStore/Store/assets/js/script.js"></script>
    <script>
        function showLoginForm(action = 'login') {
            sessionStorage.setItem('loginAction', action);
            document.getElementById('login-form-container').style.display = action === 'login' ? 'block' : 'none';
            document.getElementById('register-form-container').style.display = action === 'register' ? 'block' : 'none';
            document.getElementById('form-overlay').style.display = 'block';
        }

        function hideLoginForm() {
            document.getElementById('login-form-container').style.display = 'none';
            document.getElementById('register-form-container').style.display = 'none';
            document.getElementById('form-overlay').style.display = 'none';
            sessionStorage.removeItem('loginAction');
            sessionStorage.removeItem('addCartData');
        }

        function isValidPhone(phone) {
            var phoneRegex = /^0[0-9]{9}$/;
            return phoneRegex.test(phone);
        }

        function isValidPassword(password) {
            var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            return passwordRegex.test(password);
        }

        function isValidEmail(email) {
            var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            return emailRegex.test(email);
        }

        function checkLoginAndRedirectToCart() {
            $.ajax({
                type: 'POST',
                url: '/LaptopStore/Store/layout/check_login.php',
                dataType: 'json',
                success: function(response) {
                    if (response.loggedIn) {
                        window.location.href = '/LaptopStore/Store/index.php?page=cart';
                    } else {
                        showLoginForm('cart');
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi server',
                        text: 'Lỗi: ' + xhr.responseText
                    });
                }
            });
        }

        function showForm() {
            <?php if (isset($_SESSION['username'])): ?>
                // Hiển thị user__info (đã có logic CSS hover)
            <?php else: ?>
                showLoginForm('login');
            <?php endif; ?>
        }

        $(document).ready(function() {
            // Xử lý form đăng nhập
            $('#login-form').on('submit', function(event) {
                event.preventDefault();
                var isValid = true;

                var Username_login = $('#loginUsername').val().trim();
                var Pass_login = $('#loginPassword').val().trim();

                var usernameError = $('#Username_login--error');
                var passError = $('#Pass_login--error');
                var messageDiv = $('#login-message');

                usernameError.text('').hide();
                passError.text('').hide();
                messageDiv.text('').hide();

                if (!Username_login) {
                    usernameError.text('Chưa nhập tên đăng nhập!').show();
                    isValid = false;
                }
                if (!Pass_login) {
                    passError.text('Chưa nhập mật khẩu!').show();
                    isValid = false;
                }

                if (isValid) {
                    $.ajax({
                        type: 'POST',
                        url: '/LaptopStore/Store/layout/login_ajax.php',
                        data: {
                            loginUsername: Username_login,
                            loginPassword: Pass_login
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                hideLoginForm();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Thành công',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(function() {
                                    var loginAction = sessionStorage.getItem('loginAction');
                                    if (loginAction === 'cart') {
                                        window.location.href = '/LaptopStore/Store/index.php?page=cart';
                                    } else if (loginAction === 'add_cart' && sessionStorage.getItem('addCartData')) {
                                        var formData = sessionStorage.getItem('addCartData');
                                        $.ajax({
                                            type: 'POST',
                                            url: '/LaptopStore/Store/layout/add_cart.php',
                                            data: formData,
                                            dataType: 'json',
                                            success: function(response) {
                                                if (response.success) {
                                                    Swal.fire({
                                                        icon: 'success',
                                                        title: 'Thành công',
                                                        text: response.message,
                                                        timer: 1500,
                                                        showConfirmButton: false
                                                    });
                                                    updateCartCount();
                                                } else {
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: 'Lỗi',
                                                        text: response.error
                                                    });
                                                }
                                            },
                                            error: function(xhr) {
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Lỗi server',
                                                    text: 'Lỗi: ' + xhr.responseText
                                                });
                                            }
                                        });
                                    } else {
                                        location.reload();
                                    }
                                    sessionStorage.removeItem('loginAction');
                                    sessionStorage.removeItem('addCartData');
                                });
                            } else {
                                messageDiv.text(response.error).show();
                            }
                        },
                        error: function(xhr) {
                            messageDiv.text('Lỗi: ' + xhr.responseText).show();
                        }
                    });
                }
            });

            // Xử lý form đăng ký
            $('#register-form').on('submit', function(event) {
                event.preventDefault();
                var isValid = true;

                var Username_reg = $('#registerUsername').val().trim();
                var Name_reg = $('#registerName').val().trim();
                var Email_reg = $('#registerEmail').val().trim();
                var Pass_reg = $('#registerPassword').val().trim();
                var Phone_reg = $('#registerPhone').val().trim();
                var Address_reg = $('#registerAddress').val().trim();

                var usernameError = $('#Username_reg--error');
                var nameError = $('#Name_reg--error');
                var emailError = $('#Email_reg--error');
                var passError = $('#Pass_reg--error');
                var phoneError = $('#Phone_reg--error');
                var addressError = $('#Address_reg--error');
                var messageDiv = $('#register-message');

                usernameError.text('').hide();
                nameError.text('').hide();
                emailError.text('').hide();
                passError.text('').hide();
                phoneError.text('').hide();
                addressError.text('').hide();
                messageDiv.text('').hide();

                if (!Username_reg) {
                    usernameError.text('Chưa nhập tên đăng nhập!').show();
                    isValid = false;
                }
                if (!Name_reg) {
                    nameError.text('Chưa nhập họ tên!').show();
                    isValid = false;
                }
                if (!Email_reg) {
                    emailError.text('Chưa nhập email!').show();
                    isValid = false;
                } else if (!isValidEmail(Email_reg)) {
                    emailError.text('Email không hợp lệ!').show();
                    isValid = false;
                }
                if (!Pass_reg) {
                    passError.text('Mật khẩu không được để trống!').show();
                    isValid = false;
                } else if (!isValidPassword(Pass_reg)) {
                    passError.text('Mật khẩu phải gồm ít nhất 8 ký tự, bao gồm chữ cái thường, hoa, số và ký tự đặc biệt!').show();
                    isValid = false;
                }
                if (!Phone_reg) {
                    phoneError.text('Chưa nhập số điện thoại!').show();
                    isValid = false;
                } else if (!isValidPhone(Phone_reg)) {
                    phoneError.text('Số điện thoại không hợp lệ!').show();
                    isValid = false;
                }
                if (!Address_reg) {
                    addressError.text('Chưa nhập địa chỉ!').show();
                    isValid = false;
                }

                if (isValid) {
                    $.ajax({
                        type: 'POST',
                        url: '/LaptopStore/Store/layout/register_ajax.php',
                        data: {
                            registerUsername: Username_reg,
                            registerName: Name_reg,
                            registerEmail: Email_reg,
                            registerPassword: Pass_reg,
                            registerPhone: Phone_reg,
                            registerAddress: Address_reg
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                hideLoginForm();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Thành công',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(function() {
                                    showLoginForm('login');
                                });
                            } else {
                                messageDiv.text(response.error).show();
                            }
                        },
                        error: function(xhr) {
                            messageDiv.text('Lỗi: ' + xhr.responseText).show();
                        }
                    });
                }
            });

            // Hiển thị/ẩn mật khẩu (đăng nhập)
            $('.showEyeLogin').click(function() {
                $('#loginPassword').attr('type', 'text');
                $(this).addClass('hide');
                $('.hideEyeLogin').removeClass('hide');
            });
            $('.hideEyeLogin').click(function() {
                $('#loginPassword').attr('type', 'password');
                $(this).addClass('hide');
                $('.showEyeLogin').removeClass('hide');
            });

            // Hiển thị/ẩn mật khẩu (đăng ký)
            $('.showEyeRegister').click(function() {
                $('#registerPassword').attr('type', 'text');
                $(this).addClass('hide');
                $('.hideEyeRegister').removeClass('hide');
            });
            $('.hideEyeRegister').click(function() {
                $('#registerPassword').attr('type', 'password');
                $(this).addClass('hide');
                $('.showEyeRegister').removeClass('hide');
            });

            // Cập nhật số lượng giỏ hàng
            function updateCartCount() {
                $.ajax({
                    type: 'POST',
                    url: '/LaptopStore/Store/layout/get_cart_count.php',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#cart-count').text(response.count);
                        } else {
                            $('#cart-count').text('0');
                        }
                    }
                });
            }
            updateCartCount();
        });
    </script>
</body>
</html>

<?php ob_end_flush(); ?>