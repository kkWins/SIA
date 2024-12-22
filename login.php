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
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Login</h2>
        <div id="error-message" class="alert alert-danger d-none"></div>
        <form id="login-form">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
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
