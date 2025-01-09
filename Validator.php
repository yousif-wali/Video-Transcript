<?php
session_start();

// Logout logic corrected
if (isset($_REQUEST["Logout"])) {
    session_unset();
    session_destroy();
    header("Location: ./");
    exit();
}

try {
    $url = "http://localhost:8085";
    $data = [];

    if (!isset($_POST["api"])) {
        throw new Exception("Invalid API Request");
    }

    switch ($_POST["api"]) {
        case "signup":
            if ($_POST["Password"] != $_POST["Confirm_Password"]) {
                setcookie("passwordsDonotMatch", true, time() + 15, "/");
                header("Location: ./Signup");
                exit("Passwords do not match");
            }
            $data = [
                "Username" => $_POST['Username'],
                "Password" => $_POST["Password"],
                "Email"    => $_POST["Email"],
                "Gender"   => $_POST["Gender"]
            ];
            break;

        case "login":
            $data = [
                "Username" => $_POST["Username"],
                "Password" => $_POST["Password"]
            ];
            break;

        case "transcript":
            if (isset($_FILES["file"]) && $_FILES["file"]["error"] === UPLOAD_ERR_OK) {
                $speakers = $_POST["speakers_expected"];
                $language = $_POST["language"];
                $url .= "/transcript?Username=Rayan&speakers_expected=$speakers&language=$language";
                $data = [
                    "file" => new CURLFile($_FILES["file"]["tmp_name"], $_FILES["file"]["type"], $_FILES["file"]["name"])
                ];
            } else {
                throw new Exception("File upload error: " . $_FILES["file"]["error"]);
            }
            break;

        default:
            echo "Error Code: 102";
            exit();
    }

    // Initialize cURL request
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);

    // Check if it's a file upload for transcript
    if ($_POST["api"] == "transcript") {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    } else {
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    // Execute cURL and handle response
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        setcookie("error", "Could not connect to the server", time() + 15, "/");
    } else {
        $_SESSION["response"] = $response;
    }

    curl_close($ch);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit();
}finally{
    header("Location: ./");
}
?>
