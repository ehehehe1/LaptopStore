<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng ký tài khoản</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .container { max-width: 400px; margin: 0 auto; }
    input, button { width: 100%; padding: 10px; margin: 5px 0; }
    .error { color: red; }
    .success { color: green; }
    .hide { display: none; }
  </style>
</head>
<body>

<div class="container">
  <div id="message"></div>
  <div class="register__info">
    <h1>Tạo tài khoản</h1>
    <form id="register-form" method="post">
      <div class="register__info--input register__info--input__username">
        <label for="registerUsername">Tên đăng nhập</label>
        <input type="text" name="registerUsername" id="registerUsername" class="register__info--input-username" placeholder="Nhập tên đăng nhập của bạn" required />
      </div>
      <div id="Username_reg--error" class="error-message"></div>

      <div class="register__info--input register__info--input__full-name">
        <label for="registerName">Họ tên</label>
        <input type="text" name="registerName" id="registerName" class="register__info--input-name" placeholder="Nhập họ tên của bạn" required />
      </div>
      <div id="Name_reg--error" class="error-message"></div>

      <div class="register__info--input register__info--input__password">
        <label for="registerPassword">Mật khẩu</label>
        <div class="password__container">
          <input type="password" class="register__info--input-password" id="registerPassword" name="registerPassword" placeholder="Nhập mật khẩu của bạn" required />
          <button type="button" class="showEyeRegister"><i class="fa-solid fa-eye"></i></button>
          <button type="button" class="hideEyeRegister hide"><i class="fa-solid fa-eye-slash"></i></button>
        </div>
      </div>
      <div id="Pass_reg--error" class="error-message"></div>

      <div class="register__info--input register__info--input__phone">
        <label for="registerPhone">Số điện thoại</label>
        <input type="text" name="registerPhone" id="registerPhone" class="register__info--input-phone" placeholder="Nhập số điện thoại" required />
      </div>
      <div id="Phone_reg--error" class="error-message"></div>

      <div class="register__info--input register__info--input__email">
        <label for="registerEmail">Email</label>
        <input type="email" name="registerEmail" id="registerEmail" class="register__info--input-email" placeholder="Nhập email của bạn" required />
      </div>
      <div id="Email_reg--error" class="error-message"></div>

      <div class="register__info--input register__info--input__address">
        <label for="registerAddress">Địa chỉ</label>
        <input type="text" name="registerAddress" id="registerAddress" class="register__info--input-address" placeholder="Nhập địa chỉ của bạn" required />
      </div>
      <div id="Address_reg--error" class="error-message"></div>

      <p class="policy">
        Bằng việc đăng ký, bạn đồng ý về
        <a href="">Điều khoản dịch vụ</a> và
        <a href="">Chính sách bảo mật</a>.
      </p>

      <button type="submit" class="register__info--submit">Đăng ký</button>
    </form>

    <div class="signin" style="display: none">
      <p>Bạn đã có tài khoản?</p>
      <button>Đăng nhập</button>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
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

$(document).ready(function() {
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

        usernameError.text('').hide();
        nameError.text('').hide();
        emailError.text('').hide();
        passError.text('').hide();
        phoneError.text('').hide();
        addressError.text('').hide();

        if (!Username_reg) {
            usernameError.text('*Chưa nhập tên đăng nhập!').show();
            isValid = false;
        }
        if (!Name_reg) {
            nameError.text('*Chưa nhập họ tên!').show();
            isValid = false;
        }
        if (!Email_reg) {
            emailError.text('*Chưa nhập email!').show();
            isValid = false;
        } else if (!isValidEmail(Email_reg)) {
            emailError.text('*Email không hợp lệ!').show();
            isValid = false;
        }
        if (!Pass_reg) {
            passError.text('*Mật khẩu không được để trống!').show();
            isValid = false;
        } else if (!isValidPassword(Pass_reg)) {
            passError.text('*Mật khẩu phải gồm ít nhất 8 kí tự, bao gồm chữ cái viết thường, chữ cái viết hoa, số và một ký tự đặc biệt!').show();
            isValid = false;
        }
        if (!Phone_reg) {
            phoneError.text('*Chưa nhập số điện thoại!').show();
            isValid = false;
        } else if (!isValidPhone(Phone_reg)) {
            phoneError.text('*Số điện thoại không hợp lệ!').show();
            isValid = false;
        }
        if (!Address_reg) {
            addressError.text('*Chưa nhập địa chỉ!').show();
            isValid = false;
        }

        if (isValid) {
            $.ajax({
                type: "POST",
                url: "register_ajax.php",
                data: {
                    registerUsername: Username_reg,
                    registerName: Name_reg,
                    registerEmail: Email_reg,
                    registerPassword: Pass_reg,
                    registerPhone: Phone_reg,
                    registerAddress: Address_reg
                },
                dataType: "json",
                success: function(response) {
                    var messageDiv = $('#message');
                    if (response.success) {
                        messageDiv.html('<p class="success">' + response.message + '</p>');
                        setTimeout(function() { window.location.href = 'login.php'; }, 2000);
                    } else {
                        messageDiv.html('<p class="error">' + response.error + '</p>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#message').html('<p class="error">Lỗi: ' + xhr.responseText + '</p>');
                }
            });
        }
    });

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
});
</script>
</body>
</html>