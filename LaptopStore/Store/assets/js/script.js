
//search




// Đóng modal khi bấm ra ngoài
window.onclick = function(event) {
  const modal = document.getElementById("loginModal");
  if (event.target == modal) {
    modal.style.display = "none";
  }
}


function showmenuMobile() {
    var list = document.getElementById('menuMobile_list');
    list.style.display = "block";
    var s = '<div onclick="closemenuMobile()"><i class="fa-regular fa-rectangle-xmark"></i></div>';
    document.getElementById('menuMobile').innerHTML = s;
}

function closemenuMobile() {
    var list = document.getElementById('menuMobile_list');
    list.style.display = "none";
    var s = '<div onclick="showmenuMobile()"><i class="fa-solid fa-bars"></i></div>';
    document.getElementById('menuMobile').innerHTML = s;
}


    // Đóng menu khi nhấp liên kết
    document.querySelectorAll('#menuMobile_list a').forEach(link => {
        link.addEventListener('click', () => {
            closemenuMobile();
        });
    });
function searchProduct() {
    const query = document.getElementById('search').value.trim();
    if (query) {
        window.location.href = `index.php?search=${encodeURIComponent(query)}`;
    }
}
//form đăng nhập dk
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

        function checkLoginAndRedirectToCart(event) {
            event.preventDefault();
            $.ajax({
                type: 'POST',
                url: '/LaptopStore-master/LaptopStore/Store/layout/check_login.php',
                dataType: 'json',
                success: function(response) {
                    if (response.loggedIn) {
                        window.location.href = '/LaptopStore-master/LaptopStore/Store/index.php?page=cart';
                    } else {
                        sessionStorage.setItem('loginAction', 'cart');
                        showLoginForm('login');
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi server',
                        text: 'Không thể kết nối đến server. Vui lòng thử lại sau.'
                    });
                }
            });
        }

$(document).ready(function() {
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
                url: '/LaptopStore-master/LaptopStore/Store/layout/login_ajax.php',
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
                                window.location.href = '/LaptopStore-master/LaptopStore/Store/index.php?page=cart';
                            } else if (loginAction === 'add_cart' && sessionStorage.getItem('addCartData')) {
                                var formData = sessionStorage.getItem('addCartData');
                                $.ajax({
                                    type: 'POST',
                                    url: '/LaptopStore-master/LaptopStore/Store/layout/add_cart.php',
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
                        url: '/LaptopStore-master/LaptopStore/Store/layout/register_ajax.php',
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
                    url: '/LaptopStore-master/LaptopStore/Store/layout/get_cart_count.php',
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
        

    
function addToCart(masp, spec, color, size, quantity) {
    $.ajax({
        url: '/LaptopStore-master/LaptopStore/Store/layout/add_cart.php',
        type: 'POST',
        dataType: 'json',
        data: {
            masp: masp,
            spec: spec,
            color: color,
            size: size,
            quantity: quantity
        },
        success: function(response) {
            if (response.success) {
                alert(response.message);
                // Nếu muốn chuyển đến trang giỏ hàng
                // window.location.href = response.redirect;
            } else {
                if (response.error.includes("đăng nhập")) {
                    // Nếu chưa đăng nhập, gọi form hiển thị
                    showLoginForm('login');
                } else {
                    alert("Lỗi: " + response.error);
                }
            }
        },
        error: function() {
            alert("Có lỗi xảy ra khi thêm vào giỏ hàng.");
        }
    });
}


