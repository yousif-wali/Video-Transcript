<?php
session_start();


try {
    include_once "./Database.php";
    $url = "http://localhost:8085";
    $data = [];

    $speakers = $_POST["speakers_expected"];
    $language = $_POST["language"];
    $username = $_SESSION["Username"];

    if (!isset($_POST["api"])) {
        throw new Exception("Invalid API Request");
    }

    if($_SESSION["Points"] == 0){
        throw new Exception("Not enough credit left");
    }
    $query = "call DecrementPoints(?)";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        if (mysqli_stmt_execute($stmt)) {
            echo "Points decremented successfully!";
        } else {
            throw new Exception("Could not process #106");
        }
    } else {
        throw new Exception("Could not process #107");
    }

    switch ($_POST["api"]) {
        case "transcript":
            if (isset($_FILES["file"]) && $_FILES["file"]["error"] === UPLOAD_ERR_OK) {
                $url .= "/transcript?Username=$username&speakers_expected=$speakers&language=$language";
                $data = [
                    "file" => new CURLFile($_FILES["file"]["tmp_name"], $_FILES["file"]["type"], $_FILES["file"]["name"])
                ];
            } else {
                throw new Exception("File upload error: " . $_FILES["file"]["error"]);
            }
            break;

        default:
            throw new Exception("Code: 102");
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
    $query = "SELECT * FROM Points WHERE Username = ?";
    $stmt = mysqli_prepare($conn, $query);
    if($stmt){
    mysqli_stmt_bind_param($stmt, "s", $username);
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        if(mysqli_num_rows($result)){
            $row = mysqli_fetch_assoc($result);
            $_SESSION["Points"] = $row["Point"];
        }else{
             $_SESSION["Points"] = 0;
                               
        }
    }
    }

    $query = "INSERT INTO txn (Username, Filename, Result) Values(?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    if($stmt){
        mysqli_stmt_bind_param($stmt, "sss", $username, $_FILES["file"]["name"], $response);
        if(!mysqli_stmt_execute($stmt)){
            throw new Exception("Could not process #110");
        }
    }

    curl_close($ch);
} catch (Exception $e) {
    $_SESSION["error"] = "Error: " . $e->getMessage();
    header("Location: ./");
    exit();
}finally{
    header("Location: ./");
}
?>
