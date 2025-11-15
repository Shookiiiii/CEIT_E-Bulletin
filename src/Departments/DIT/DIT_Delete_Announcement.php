<?php
// DIT_Delete_Announcement.php
include "../../db.php";
header('Content-Type: application/json');
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    
    // First, check if the announcement exists by ID only (regardless of status or title)
    $checkQuery = "SELECT * FROM DIT_post WHERE id=?";
    $stmt = $conn->prepare($checkQuery);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Announcement not found']);
        exit;
    }
    
    // Get the file path and status from the database
    $query = "SELECT file_path, status FROM DIT_post WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Announcement not found']);
        exit;
    }
    
    $filePath = $row['file_path'];
    $status = $row['status'];
    
    // Determine the correct file path based on status
    if ($status === 'archived') {
        // For archived items, check the archive directory
        $fullFilePath = "uploads/archive/" . $filePath;
    } else {
        // For non-archived items, check the regular uploads directory
        $fullFilePath = "uploads/" . $filePath;
    }
    
    // Delete the file from the server if it exists
    if (file_exists($fullFilePath)) {
        if (!unlink($fullFilePath)) {
            // Log the error but don't stop the process
            error_log("Failed to delete file: " . $fullFilePath);
        }
    }
    
    // Delete the record from the database
    $query = "DELETE FROM DIT_post WHERE id=?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Announcement deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No rows were affected during deletion']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
$conn->close();
?>