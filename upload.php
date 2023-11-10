<?php
// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if files were uploaded
    if (isset($_FILES["image"])) {
        $uploadedFiles = $_FILES["image"];

        // Database configuration
        $dbHost = 'localhost';
        $dbUser = 'afnan'; // Replace with your MySQL username
        $dbPass = 'john_wick_77'; // Replace with your MySQL password
        $dbName = 'mywebsite_images'; // Replace with your database name
        $imageColumnName = 'images'; // Replace with your BLOB column name

        // Create a database connection
        $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

        // Check the connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Loop through the uploaded files
        foreach ($uploadedFiles["error"] as $key => $error) {
            // Check for file upload errors
            if ($error == 0) {
                // Get the selected folder and image data
                $folder = $_POST["folder"];
                $imageData = file_get_contents($uploadedFiles["tmp_name"][$key]);

                // Prepare and execute the database insertion
                $stmt = $conn->prepare("INSERT INTO $folder ($imageColumnName) VALUES (?)");
                $stmt->bind_param("b", $imageData); // Use "b" for binary data
                $stmt->send_long_data(0, $imageData); // Send binary data separately
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $message = "Images uploaded successfully!";
                } else {
                    $message = "Failed to upload images.";
                }

                // Close the statement
                $stmt->close();
            } else {
                $message = "File upload error: " . $error;
            }
        }

        // Close the database connection
        $conn->close();

        // Redirect back to index.php with the message
        header("Location: index.php?message=" . urlencode($message));
        exit();
    } else {
        $message = "No images uploaded.";
    }

    // Redirect back to index.php with the message
    header("Location: index.php?message=" . urlencode($message));
    exit();
}
?>
