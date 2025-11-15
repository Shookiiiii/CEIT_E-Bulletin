<?php
include "../../db.php";
// Create archive directory if it doesn't exist
$archiveDir = 'uploads/archive';
if (!file_exists($archiveDir)) {
    if (!mkdir($archiveDir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create archive directory']);
        exit;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    
    // Get the announcement details before archiving
    $query = "SELECT * FROM Central_post WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Copy file to archive directory if it exists
        $sourceFile = 'uploads/' . $row['file_path'];
        $archiveFile = $archiveDir . '/' . $row['file_path'];
        
        if (file_exists($sourceFile)) {
            if (!copy($sourceFile, $archiveFile)) {
                echo json_encode(['success' => false, 'message' => 'Failed to copy file to archive']);
                exit;
            }
            
            // Delete the original file after successful copy
            if (!unlink($sourceFile)) {
                echo json_encode(['success' => false, 'message' => 'Failed to delete original file']);
                exit;
            }
        }
        
        // Insert into archive table with archive path
        $archiveQuery = "INSERT INTO Archive (type, title, content, file_path, archived_at) VALUES (?, ?, ?, ?, NOW())";
        $archiveStmt = $conn->prepare($archiveQuery);
        
        $type = 'announcement';
        $title = $row['content']; // Using content as title for archive
        $content = $row['content'];
        $file_path = 'archive/' . $row['file_path']; // Update path to include archive folder
        
        $archiveStmt->bind_param("ssss", $type, $title, $content, $file_path);
        
        if ($archiveStmt->execute()) {
            // Now delete from Central_post
            $deleteQuery = "DELETE FROM Central_post WHERE id = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $id);
            
            if ($deleteStmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Announcement archived successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting announcement: ' . $conn->error]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error archiving announcement: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Announcement not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>