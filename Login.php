<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }

        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .login-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #333;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.75rem;
            font-size: 1rem;
        }

        .login-btn {
            background: #fda085;
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-size: 1.2rem;
            color: white;
            width: 100%;
            cursor: pointer;
            transition: background 0.3s;
        }

        .login-btn:hover {
            background: #f6d365;
        }

        .form-footer {
            margin-top: 1rem;
        }

        .form-footer a {
            color: #333;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-title">Login</div>
        <form action="validator.php" method="POST">
            <div class="mb-3">
                <input type="text" class="form-control" name="Username" placeholder="Username" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" name="Password" placeholder="Password" required>
            </div>
            <button class="login-btn" type="submit">Login</button>
        </form>
        <div class="form-footer">
            <p>Don't have an account? <a href="./Signup">Sign Up</a></p>
        </div>
    </div>
</body>
</html>
