<?php
include "../../db.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    
    // Get the file path to delete it
    $query = "SELECT file_path FROM Central_post WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $dbFilePath = $row['file_path'];
        
        // Check if the path already includes 'uploads/' prefix
        if (strpos($dbFilePath, 'uploads/') === 0) {
            // Path already includes uploads folder
            $filePath = $dbFilePath;
        } else {
            // Need to add uploads folder prefix
            $filePath = 'uploads/' . $dbFilePath;
        }
        
        // Debug output - remove this after confirming it works
        error_log("Attempting to delete file: " . $filePath);
        
        // Delete the file if it exists
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                error_log("File deleted successfully: " . $filePath);
            } else {
                error_log("Failed to delete file: " . $filePath);
            }
        } else {
            error_log("File does not exist: " . $filePath);
        }
        
        // Delete the record from the database
        $query = "DELETE FROM Central_post WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Announcement deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Announcement not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>