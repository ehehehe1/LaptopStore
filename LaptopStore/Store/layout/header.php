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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
    <link type="text/css" rel="stylesheet" href="/LaptopStore-master/LaptopStore/Store/assets/css/style.css">
    <link type="text/css" rel="stylesheet" href="/LaptopStore-master/LaptopStore/Store/assets/css/form.css">
    <link rel="stylesheet" href="/LaptopStore-master/LaptopStore/Store/assets/css/responsive.css">
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
                <a href="#" onclick="checkLoginAndRedirectToCart(event)"><img class="shopping" src="assets/img/shopping.png" alt="shopping"></a>
            </div>
            <?php
            if (isset($_SESSION['username'])) {
                echo '<a href="/LaptopStore-master/LaptopStore/Store/layout/logout.php" style="color: red; margin-right: 10px;">Đăng xuất</a>';
            } 
            ?>
            <div id="user__login-change">
                <div id="user" onclick="showForm()"><img class="user" src="assets/img/user.png" alt="user"></div>
                <?php if (isset($_SESSION['username'])): ?>
                    <div class="user__info">
                        <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            <div id="menuMobile" onclick="showmenuMobile()">
                <div><i class="fa-solid fa-bars"></i></div>
            </div>
            <div id="menuMobile_list">
                <ul>
                    <li><a href="index.php">TRANG CHỦ</a></li>
                    <li><a href="#" onclick="showproductMenu('LSP001'); return false;">Laptop Gaming</a></li>
                    <li><a href="#" onclick="showproductMenu('LSP002'); return false;">Laptop Văn phòng</a></li>
                    <li><a href="#" onclick="showproductMenu('LSP003'); return false;">Laptop Đồ họa</a></li>
                    <li><a href="#" onclick="showproductMenu('LSP004'); return false;">Laptop Mỏng Nhẹ</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Form overlay -->
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
    <script>
            function showForm() {
            <?php if (isset($_SESSION['username'])): ?>
                // Hiển thị user__info (đã có logic CSS hover)
            <?php else: ?>
                showLoginForm('login');
            <?php endif; ?>
        }
    </script>
     <script src="/LaptopStore-master/LaptopStore/Store/assets/js/script.js"></script>
</body>
</html>

<?php ob_end_flush(); ?>