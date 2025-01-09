<?php
session_start();
require_once "./Database.php";

if(isset($_REQUEST["logout"])){
    session_unset();
    session_destroy();
    header("Location: ./");
}
if (isset($_POST["Login"])) {
    $Username = $_POST["Username"];
    $Password = $_POST["Password"];

    // Prepare the SQL statement to prevent SQL injection
    $query = "SELECT * FROM Users WHERE Username = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        // Bind the username parameter to the statement
        mysqli_stmt_bind_param($stmt, "s", $Username);
        
        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            // Check if the user exists
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $hashed_password = $row['Password'];

                // Verify the password
                if (password_verify($Password, $hashed_password)) {
                    // Password matches, login successful
                    $_SESSION["Username"] = $Username;

                    $query = "SELECT * FROM Points WHERE Username = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    if($stmt){
                        mysqli_stmt_bind_param($stmt, "s", $Username);
                        if(mysqli_stmt_execute($stmt)){
                            $result = mysqli_stmt_get_result($stmt);
                            if(mysqli_num_rows($result)){
                                $row = mysqli_fetch_assoc($result);
                                $_SESSION["Points"] = $row["Point"];
                                header("Location: ./");
                                exit();
                            }else{
                                $_SESSION["Points"] = 0;
                                header('Location: ./');
                            }
                        }
                    }
                    
                } else {
                    // Incorrect password
                    $_SESSION["error"] = "Invalid password or Username!";
                    header("Location: ./Login");
                    exit();
                }
            }
        }
    }
}

if(isset($_POST['Signup'])){
    $Username = $_POST["Username"];
    $Email = $_POST["Email"];
    $Password = $_POST["Password"];
    $Confirm_Password = $_POST["Confirm_Password"];
    $Gender = $_POST["Gender"];

    // Validation
    if (strlen($Password) < 8) {
        $_SESSION["error"] = "Password must be at least 8 characters long.";
        header("Location: ./Signup");
        exit();
    } elseif ($Password != $Confirm_Password) {
        $_SESSION["error"] = "Passwords do not match!";
        header("Location: ./Signup");
        exit();
    } else {
        // Hash the password correctly
        $hashed = password_hash($Password, PASSWORD_DEFAULT);

        // Using a prepared statement to avoid SQL injection
        $query = "INSERT INTO Users (Username, Email, Password, Gender) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssss", $Username, $Email, $hashed, $Gender);
            if (mysqli_stmt_execute($stmt)) {
                $query = "INSERT INTO Points (Username) VALUES (?)";
                $stmt = mysqli_prepare($conn, $query);
                if($stmt){
                    mysqli_stmt_bind_param($stmt, "s", $Username);
                    if(mysqli_stmt_execute($stmt)){
                        $_SESSION["Username"] = $Username;
                        $_SESSION["Points"] = 5;
                        // Clear session data on successful registration
                        unset($_SESSION["Input_Username"], $_SESSION["Input_Email"], $_SESSION["Input_Password"], $_SESSION["Input_Confirm_Password"], $_SESSION["Input_Gender"]);
                        $_SESSION["error"] = "Signup successful!";
                        header("Location: ./");
                        exit();
                    }else{
                        $_SESSION["error"] = "Database error: ";
                        header("Location: ./Signup" );
                        exit();
                    }
                }
            } else {
                $_SESSION["error"] = "Database error: " . mysqli_error($conn);
                header("Location: ./Signup" );
                exit();
            }
        } else {
            $_SESSION["error"] = "Failed to prepare SQL statement.";
            header("Location: ./Signup");
            exit();
        }
    }
}

?>