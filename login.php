<?php
session_start();

if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Employee Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
            max-width: 400px;
            width: 90%;
            margin: 2rem auto;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header i {
            font-size: 3rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
        .form-floating {
            margin-bottom: 1rem;
        }
        .btn-login {
            width: 100%;
            padding: 0.8rem;
            font-size: 1.1rem;
        }
        .alert {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-container">
                    <div class="login-header">
                        <i class="fas fa-user-circle"></i>
                        <h2 class="fw-bold">Employee Login</h2>
                        <p class="text-muted">Please enter your credentials</p>
                    </div>
                    
                    <div id="error-message" class="alert alert-danger d-none"></div>
                    
                    <form id="login-form">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                            <label for="username"><i class="fas fa-user me-2"></i>Username</label>
                        </div>
                        
                        <div class="form-floating mb-4">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $("#login-form").submit(function(e) {
                e.preventDefault(); // Prevent default form submission
                
                let username = $("#username").val();
                let password = $("#password").val();

                $.ajax({
                    url: 'authenticate.php',
                    type: 'POST',
                    data: { username: username, password: password },
                    success: function(response) {
    console.log(response);  // Log the raw response to check its format
    try {
        let data = JSON.parse(response);
        if (data.status === 'success') {
            window.location.href = 'index.php';
        } else {
            $("#error-message").text(data.message).removeClass('d-none');
        }
    } catch (e) {
        console.error("Failed to parse JSON:", e);
    }
},
                    error: function() {
                        alert('Error occurred during login. Please try again later.');
                    }
                });
            });
        });
    </script>
</body>
</html>
