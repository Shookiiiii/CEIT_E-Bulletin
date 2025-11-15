<?php
include "../../db.php";
// Create uploads directory if it doesn't exist
$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo "Error: Failed to create uploads directory";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = $_POST['description'];
    
    if (isset($_FILES['pdfFile']) && $_FILES['pdfFile']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['pdfFile']['tmp_name'];
        $fileName = $_FILES['pdfFile']['name'];
        $fileSize = $_FILES['pdfFile']['size'];
        $fileType = $_FILES['pdfFile']['type'];
        
        // Check file size (limit to 10MB)
        if ($fileSize > 10 * 1024 * 1024) {
            echo "Error: File size exceeds 10MB limit";
            exit;
        }
        
        // Check file type
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'image/jpeg',
            'image/png',
            'image/gif'
        ];
        
        if (!in_array($fileType, $allowedTypes)) {
            echo "Error: File type not allowed";
            exit;
        }
        
        // Generate new filename with timestamp and random component for uniqueness
        $now = new DateTime();
        $day = $now->format('d');
        $month = $now->format('m');
        $year = $now->format('Y');
        $hours = $now->format('H');
        $minutes = $now->format('i');
        $seconds = $now->format('s'); // Add seconds
        $random = mt_rand(1000, 9999); // Add a random number
        
        // Get file extension
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        
        // Format: announcement_DD_MM_YYYY_HH_MM_SS_RANDOM.extension
        $newFilename = "announcement_{$day}_{$month}_{$year}_{$hours}_{$minutes}_{$seconds}_{$random}.{$fileExtension}";
        
        // Move the file to the uploads directory
        $destPath = $uploadDir . $newFilename;
        
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // First, let's check what columns exist in the Central_post table
            $columnsQuery = "SHOW COLUMNS FROM Central_post";
            $columnsResult = $conn->query($columnsQuery);
            $columns = [];
            while ($column = $columnsResult->fetch_assoc()) {
                $columns[] = $column['Field'];
            }
            
            // Determine the correct date column name
            $dateColumn = 'created_at'; // Default
            if (in_array('date_posted', $columns)) {
                $dateColumn = 'date_posted';
            } elseif (in_array('date', $columns)) {
                $dateColumn = 'date';
            } elseif (in_array('post_date', $columns)) {
                $dateColumn = 'post_date';
            }
            
            // Insert the record into the database with the correct column name
            $query = "INSERT INTO Central_post (title, content, file_path, $dateColumn) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($query);
            $title = 'announcement';
            $stmt->bind_param("sss", $title, $description, $newFilename);
            
            if ($stmt->execute()) {
                // Return success response
                echo "success";
            } else {
                echo "Error: " . $conn->error;
            }
        } else {
            echo "Error uploading file: " . error_get_last()['message'];
        }
    } else {
        $error_message = "Error: No file uploaded or file upload error";
        if (isset($_FILES['pdfFile']['error'])) {
            switch ($_FILES['pdfFile']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $error_message = "Error: The uploaded file exceeds the upload_max_filesize directive in php.ini";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $error_message = "Error: The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error_message = "Error: The uploaded file was only partially uploaded";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $error_message = "Error: No file was uploaded";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $error_message = "Error: Missing a temporary folder";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $error_message = "Error: Failed to write file to disk";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $error_message = "Error: A PHP extension stopped the file upload";
                    break;
                default:
                    $error_message = "Error: Unknown upload error";
            }
        }
        echo $error_message;
    }
} else {
    echo "Invalid request method";
}
?>