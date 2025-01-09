<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #89f7fe 0%, #66a6ff 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }

        .signup-container {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .signup-title {
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

        .signup-btn {
            background: #66a6ff;
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-size: 1.2rem;
            color: white;
            width: 100%;
            cursor: pointer;
            transition: background 0.3s;
        }

        .signup-btn:hover {
            background: #89f7fe;
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
    <div class="signup-container">
        <div class="text-danger">
            <?php 
            if(isset($_SESSION["error"])) {
                echo $_SESSION["error"];
                unset($_SESSION["error"]);
            }
            ?>
        </div>
        <div class="signup-title">Signup</div>
        <form action="./Functions" method="POST">
            <input type="hidden" name="api" value="signup">
            <div class="mb-3">
                <input type="text" class="form-control" name="Username" placeholder="Username" required >
            </div>
            <div class="mb-3">
                <input type="email" class="form-control" name="Email" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" name="Password" placeholder="Password" minlength="8" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" name="Confirm_Password" placeholder="Confirm Password" required>
            </div>
            <div class="mb-3">
                <label for="gender">Gender:</label>
                <select class="form-control" name="Gender" id="gender" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <button class="signup-btn" type="submit" name="Signup">Sign Up</button>
        </form>
        <div class="form-footer">
            <p>Already have an account? <a href="./Login">Login Here</a></p>
        </div>
    </div>
</body>
</html>
