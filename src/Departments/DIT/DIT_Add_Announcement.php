<?php
// Include the database connection file
require_once('../../db.php');
// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if file was uploaded without errors
    if (isset($_FILES["pdfFile"]) && $_FILES["pdfFile"]["error"] == 0) {
        $allowed = ["pdf" => "application/pdf"];
        $fileName = $_FILES["pdfFile"]["name"];
        $fileType = $_FILES["pdfFile"]["type"];
        $fileSize = $_FILES["pdfFile"]["size"];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        // Verify file extension
        if (!array_key_exists($fileExt, $allowed)) {
            echo "<script>alert('Error: Please select a valid PDF file.'); window.location.href = 'DIT.php?tab=upload-announcements';</script>";
            exit;
        }
        // Verify file size - 5MB maximum
        $maxSize = 5 * 1024 * 1024;
        if ($fileSize > $maxSize) {
            echo "<script>alert('Error: File size is larger than the allowed limit (5MB).'); window.location.href = 'DIT.php?tab=upload-announcements';</script>";
            exit;
        }
        // Verify MIME type of the file
        if (in_array($fileType, $allowed)) {
            // Create uploads directory if it doesn't exist
            if (!file_exists('uploads')) {
                mkdir('uploads', 0777, true);
            }
            // Generate new filename: announcement_day_month_year_hour_minute_second.pdf
            date_default_timezone_set('Asia/Manila');
            $newFileName = 'announcement_' . date('d_m_Y_H_i_s') . '.' . $fileExt;
            $uploadPath = "uploads/" . $newFileName;
            // Remove any existing file with the same name
            if (file_exists($uploadPath)) {
                unlink($uploadPath);
            }
            if (move_uploaded_file($_FILES["pdfFile"]["tmp_name"], $uploadPath)) {
                // Get the description from the form
                $description = $_POST['description'];
                $status = 'pending';
                // Insert into database
                try {
                    $stmt = $conn->prepare("INSERT INTO DIT_post (title, content, file_path, status) VALUES ('announcement', ?, ?, ?)");
                    $stmt->bind_param('sss', $description, $newFileName, $status);
                    $stmt->execute();
                    echo "<script>window.location.href = 'DIT.php?tab=upload-announcements';</script>";
                } catch (PDOException $e) {
                    // Delete the uploaded file if database insertion fails
                    if (file_exists($uploadPath)) {
                        unlink($uploadPath);
                    }
                    echo "<script>alert('Database error: " . $e->getMessage() . "'); window.location.href = 'DIT.php?tab=upload-announcements';</script>";
                }
            } else {
                echo "<script>alert('Error: There was a problem uploading your file. Please try again.'); window.location.href = 'DIT.php?tab=upload-announcements';</script>";
            }
        } else {
            echo "<script>alert('Error: There was a problem with your file upload. Please try again.'); window.location.href = 'DIT.php?tab=upload-announcements';</script>";
        }
    } else {
        echo "<script>alert('Error: " . $_FILES["pdfFile"]["error"] . "'); window.location.href = 'DIT.php?tab=upload-announcements';</script>";
    }
}
?>