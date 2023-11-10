<!DOCTYPE html>
<html>
<head>
    <title>Image Upload and Viewer</title>
</head>
<body>
    <h1>Upload Images</h1>
    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <!-- Allow multiple file selection -->
        <label for="image">Choose image(s) to upload:</label>
        <input type="file" name="image[]" id="image" accept="image/*" multiple>
        <br>
        <label for="folder">Select a folder:</label>
        <select name="folder" id="folder">
            <option value="Case001">Case001</option>
            <option value="Case002">Case002</option>
            <option value="Case003">Case003</option>
        </select>
        <br>
        <input type="submit" value="Upload">
    </form>

    <!-- The image viewing section -->
    <h1>Image Viewer</h1>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
        <label for="view_folder">Select a folder to view images:</label>
        <select name="folder" id="view_folder">
            <option value="Case001">Case001</option>
            <option value="Case002">Case002</option>
            <option value="Case003">Case003</option>
        </select>
        <input type="submit" name="view_images" value="View Images">
    </form>

    <?php
    // Function to sanitize the folder name input
    function sanitize_folder($folder) {
        // Implement appropriate sanitization for the folder input
        // For example, you might want to ensure that only valid folder names are processed
        return filter_var($folder, FILTER_SANITIZE_STRING);
    }

    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['view_images'])) {
        // Your image_viewer.php logic here
        $selectedFolder = sanitize_folder($_GET['folder']);
        // Database configuration - Update with your actual database credentials
        $dbHost = 'localhost';
        $dbUser = 'afnan';
        $dbPass = 'john_wick_77';
        $dbName = 'mywebsite_images';

        // Create a database connection
        $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

        // Check the connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Query to retrieve image data from the selected folder table
        $sql = "SELECT id, images FROM $selectedFolder";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<div class='image-container'>";
            while ($row = $result->fetch_assoc()) {
                $imageId = $row["id"];
                $imageData = $row["images"];
                $base64Image = base64_encode($imageData);
                echo "<div class='image-item'>";
                echo "<h2>Image $imageId</h2>";
                echo "<img src='data:image/jpeg;base64,$base64Image' alt='Image $imageId'>";
                echo "</div>";
            }
            echo "</div>";
            // Add a button to download all images as a zip file
            echo "<form action='" . $_SERVER['PHP_SELF'] . "' method='POST'>";
            echo "<input type='hidden' name='folder' value='" . htmlspecialchars($selectedFolder) . "'>";
            echo "<input type='submit' name='download_all' value='Download All Images as Zip'>";
            echo "</form>";
        } else {
            echo "No images found in $selectedFolder.";
        }

        $conn->close();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['download_all'])) {
        $selectedFolder = sanitize_folder($_POST['folder']);
        // Database configuration - Update with your actual database credentials
        $dbHost = 'localhost';
        $dbUser = 'afnan';
        $dbPass = 'john_wick_77';
        $dbName = 'mywebsite_images';

        // Create a database connection
        $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

        // Check the connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Fetch all images from the selected folder
        $sql = "SELECT images FROM $selectedFolder";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Create a new zip file
            $zip = new ZipArchive();
            $zipFileName = $selectedFolder . '.zip';
            if ($zip->open($zipFileName, ZipArchive::CREATE) === TRUE) {
                // Add files to zip
                while ($row = $result->fetch_assoc()) {
                    $zip->addFromString("image{$row['id']}.jpg", $row['images']);
                }
                $zip->close();

                // Set headers to download the file
                header('Content-Type: application/zip');
                header('Content-disposition: attachment; filename=' . $zipFileName);
                header('Content-Length: ' . filesize($zipFileName));
                readfile($zipFileName);

                // Remove the zip file from the server
                unlink($zipFileName);
            } else {
                echo 'Failed to create a zip file';
            }
        } else {
            echo "No images found in $selectedFolder to download.";
        }

        $conn->close();
    }
    ?>

    <!-- Feedback area for displaying messages -->
    <div id="upload-feedback">
        <?php
        if (isset($_GET['message'])) {
            echo '<p>' . htmlspecialchars($_GET['message']) . '</p>';
        }
        ?>
    </div>
</body>
</html>
