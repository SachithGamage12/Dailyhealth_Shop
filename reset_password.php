<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }

        .container {
            width: 90%;
            max-width: 400px;
            padding: 15px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease-in-out;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h3 {
            color: #333;
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }

        .form-control {
            border-radius: 8px;
            padding: 8px;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .form-control:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 5px rgba(106, 17, 203, 0.5);
        }

        .btn-success {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
            margin-top: 10px;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-link {
            text-align: center;
            display: block;
            font-size: 14px;
        }

        .btn-link:hover {
            color: #6a11cb;
        }

        .form-label {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .alert {
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h3 class="text-center">Reset Password</h3>
        <form id="resetPasswordForm">
            <input type="hidden" name="username" value="<?php echo htmlspecialchars($_GET['username']); ?>">
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" name="new_password" id="new_password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-success mt-3"        style="background-color: #FF7C2A; border: none; color: white; font-weight: bold;">
 Reset Password</button>
        </form>
        <a href="login.php" class="btn-link">Back to Login</a>

        <!-- Success alert -->
        <div id="successAlert" class="alert alert-success" role="alert">
            Password reset successfully!
        </div>

        <!-- Error alert -->
        <div id="alertBox" class="alert alert-danger" role="alert">
            Passwords do not match or something went wrong. Please try again.
        </div>
    </div>

    <script>
    $(document).ready(function () {
        $("#resetPasswordForm").submit(function (e) {
            e.preventDefault(); // Prevent default form submission

            let newPassword = $("#new_password").val().trim();
            let confirmPassword = $("#confirm_password").val().trim();

            if (newPassword === "" || confirmPassword === "") {
                alert("Please fill in all fields.");
                return;
            }

            if (newPassword !== confirmPassword) {
                alert("Passwords do not match.");
                return;
            }

            $.ajax({
                type: "POST",
                url: "reset_password_process.php",
                data: $(this).serialize(),
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        $("#successAlert").show();
                        setTimeout(function () {
                            window.location.href = "login.php";
                        }, 2000);
                    } else {
                        $("#alertBox").show(); // Show error message
                    }
                },
                error: function () {
                    $("#alertBox").show(); // Show error alert in case of a request failure
                }
            });
        });
    });
    </script>
</body>
</html>