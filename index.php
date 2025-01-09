<?php
session_start();
if (!isset($_SESSION["Username"])) {
    header("Location: ./Login");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Transcriptor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }
        .form-title {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        /* Avatar styling */
        .avatar-container {
            position: fixed;
            top: 10px;
            right: 10px;
        }

        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid #007bff;
        }

        .tooltip-content {
            display: none;
            position: absolute;
            top: 60px;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px;
            text-align: center;
        }

        .tooltip-content p {
            margin: 0;
            font-weight: bold;
        }

        .tooltip-content a {
            margin-top: 8px;
            display: inline-block;
        }
    </style>
    <script>
        function toggleTooltip() {
            const tooltip = document.getElementById('tooltip');
            tooltip.style.display = tooltip.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</head>
<body>
    <!-- Avatar Section -->
    <div class="avatar-container">
        <img src="https://www.w3schools.com/howto/img_avatar.png" alt="User Avatar" class="avatar" onclick="toggleTooltip()">
        <div id="tooltip" class="tooltip-content">
            <p><?php echo htmlspecialchars($_SESSION["Username"]); ?></p>
            <a class="btn btn-danger btn-sm" href="./Logout">Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="form-container">
        <h1 class="form-title">AI Transcriptor</h1>
        <h5 class="form-title">Credit:   <?php echo $_SESSION["Points"];?></h5>
        <?php
            if(isset($_SESSION['error'])){
                $error = $_SESSION["error"];
                echo "<p class='text-danger'>$error</p>";
                unset($_SESSION["error"]);
            }
        ?>
        <form action="./APIs" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="file" class="form-label">Upload File</label>
                <input type="file" name="file" class="form-control" required>
            </div>
            <input name="api" type="hidden" value="transcript">
            <div class="mb-3">
                <label for="speakers_expected" class="form-label">Speakers Expected</label>
                <input name="speakers_expected" id="speakers_expected" type="number" min="1" required step="1" class="form-control">
            </div>
            <div class="mb-3">
                <label for="language" class="form-label">Language</label>
                <select id="language" class="form-control" name="language">
                    <option value="en_us" selected>English (US)</option>
                    <option value="de">German</option>
                    <option value="ar">Arabic</option>
                </select>
            </div>
            <div class="text-center">
                <button class="btn btn-success" type="submit">Submit</button>
            </div>
        </form>
        <div class="mt-4">
            <label for="response" class="form-label">Transcript Response</label>
            <textarea class="form-control" id="response" readonly><?php echo isset($_SESSION["response"]) ? htmlspecialchars($_SESSION["response"]) : ''; ?></textarea>
        </div>
    </div>
</body>
</html>
