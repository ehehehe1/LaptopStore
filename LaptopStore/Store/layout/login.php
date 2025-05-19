<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng nhập</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
      background-color: #f4f4f4;
    }
    .container {
      max-width: 400px;
      margin: 0 auto;
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    h1 {
      text-align: center;
      color: #333;
    }
    .login__info--input {
      margin-bottom: 15px;
    }
    label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #555;
    }
    input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      box-sizing: border-box;
      font-size: 14px;
    }
    input:focus {
      outline: none;
      border-color: #007bff;
      box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
    }
    .password__container {
      position: relative;
    }
    .password__container button {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      padding: 0;
    }
    .error-message {
      color: red;
      font-size: 12px;
      margin-top: 5px;
      display: none;
    }
    .error {
      color: red;
      text-align: center;
      margin-bottom: 15px;
    }
    .success {
      color: green;
      text-align: center;
      margin-bottom: 15px;
    }
    .hide {
      display: none;
    }
    .login__info--submit {
      background: #007bff;
      color: #fff;
      border: none;
      padding: 12px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
      width: 100%;
      transition: background 0.3s;
    }
    .login__info--submit:hover {
      background: #0056b3;
    }
    .signup {
      text-align: center;
      margin-top: 15px;
    }
    .signup a {
      color: #007bff;
      text-decoration: none;
    }
    .signup a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="container">
  <div id="message"></div>
  <div class="login__info">
    <h1>Đăng nhập</h1>
    <form id="login-form" method="post">
      <div class="login__info--input login__info--input__username">
        <label for="loginUsername">Tên đăng nhập</label>
        <input type="text" name="loginUsername" id="loginUsername" class="login__info--input-username" placeholder="Nhập tên đăng nhập của bạn" required />
      </div>
      <div id="Username_login--error" class="error-message"></div>

      <div class="login__info--input login__info--input__password">
        <label for="loginPassword">Mật khẩu</label>
        <div class="password__container">
          <input type="password" class="login__info--input-password" id="loginPassword" name="loginPassword" placeholder="Nhập mật khẩu của bạn" required />
          <button type="button" class="showEyeLogin"><i class="fa-solid fa-eye"></i></button>
          <button type="button" class="hideEyeLogin hide"><i class="fa-solid fa-eye-slash"></i></button>
        </div>
      </div>
      <div id="Pass_login--error" class="error-message"></div>

      <button type="submit" class="login__info--submit">Đăng nhập</button>
    </form>

    <div class="signup">
      <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
$(document).ready(function() {
    $('#login-form').on('submit', function(event) {        event.preventDefault();
        var isValid = true;

        var Username_login = $('#loginUsername').val().trim();
        var Pass_login = $('#loginPassword').val().trim();

        var usernameError = $('#Username_login--error');
        var passError = $('#Pass_login--error');

        usernameError.text('').hide();
        passError.text('').hide();

        if (!Username_login) {
            usernameError.text('*Chưa nhập tên đăng nhập!').show();
            isValid = false;
        }
        if (!Pass_login) {
            passError.text('*Chưa nhập mật khẩu!').show();
            isValid = false;
        }

        if (isValid) {
            $.ajax({
                type: "POST",
                url: "login_ajax.php",
                data: {
                    loginUsername: Username_login,
                    loginPassword: Pass_login
                },
                dataType: "json",
                success: function(response) {
                    var messageDiv = $('#message');
                    if (response.success) {
                        messageDiv.html('<p class="success">' + response.message + '</p>');
                        setTimeout(function() { window.location.href = '../index.php'; }, 2000);
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
});
</script>
</body>
</html>