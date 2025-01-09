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

    // Check if the user has enough points
    if ($_SESSION["Points"] - 1 < 0) {
        throw new Exception("Not enough credit left");
    }

    // Ensure connection is alive, reconnect if needed
    if (!mysqli_ping($conn)) {
        mysqli_close($conn);
        include_once "./Database.php"; // Reconnect
    }

    // Call the stored procedure to decrement points
    $queryPoint = "CALL DecrementPoints(?)";
    $stmtPoint = mysqli_prepare($conn, $queryPoint);
    if ($stmtPoint) {
        mysqli_stmt_bind_param($stmtPoint, "s", $username);
        mysqli_stmt_execute($stmtPoint);
        // Fetch all results to avoid 'Commands out of sync'
        do {
            mysqli_stmt_store_result($stmtPoint);
        } while (mysqli_stmt_next_result($stmtPoint));

        mysqli_stmt_close($stmtPoint);
    } else {
        throw new Exception("Could not process #107");
    }

    // Handle API requests and file upload
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
    }

    // Initialize cURL request
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);

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

    $query = "SELECT Point FROM Points WHERE Username = ?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $_SESSION["Points"] = $row["Point"];
        } else {
            $_SESSION["Points"] = 0;
        }

        mysqli_stmt_free_result($stmt);
        mysqli_stmt_close($stmt);
    }

    // Insert transaction data into the txn table
    $queryTxn = "INSERT INTO txn (Username, Filename, Result) VALUES (?, ?, ?)";
    $stmtTxn = mysqli_prepare($conn, $queryTxn);
    if ($stmtTxn) {
        mysqli_stmt_bind_param($stmtTxn, "sss", $username, $_FILES["file"]["name"], $response);
        if (!mysqli_stmt_execute($stmtTxn)) {
            throw new Exception("Could not process transaction #110");
        }
        mysqli_stmt_close($stmtTxn);
    }

    curl_close($ch);
} catch (Exception $e) {
    $_SESSION["error"] = "Error: " . $e->getMessage();
    header("Location: ./");
    exit();
} finally {
    header("Location: ./");
    exit();
}
?>
