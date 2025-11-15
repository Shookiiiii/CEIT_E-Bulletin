<?php
// DIT_Archive_Announcement.php
include "../../db.php";
header('Content-Type: application/json');
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create archive directory if it doesn't exist
$archiveDir = 'uploads/archive';
if (!file_exists($archiveDir)) {
    if (!mkdir($archiveDir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create archive directory']);
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    
    // First, check if the announcement exists and is approved
    $checkQuery = "SELECT * FROM DIT_post WHERE id=? AND title='announcement' AND (status='approved' OR status='Approved')";
    $stmt = $conn->prepare($checkQuery);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Announcement not found or not in approved status']);
        exit;
    }
    
    // Get the announcement details
    $row = $result->fetch_assoc();
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Move file to archive directory if it exists
        $sourceFile = 'uploads/' . $row['file_path'];
        $archiveFile = $archiveDir . '/' . $row['file_path'];
        
        if (file_exists($sourceFile)) {
            if (!copy($sourceFile, $archiveFile)) {
                throw new Exception('Failed to copy file to archive');
            }
            
            // Delete the original file after successful copy
            if (!unlink($sourceFile)) {
                throw new Exception('Failed to delete original file');
            }
        }
        
        // Update status to archived
        $updateQuery = "UPDATE DIT_post SET status='archived' WHERE id=?";
        $stmt = $conn->prepare($updateQuery);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Check if any rows were affected
        if ($stmt->affected_rows === 0) {
            throw new Exception('No rows were affected during update');
        }
        
        // Verify the update was successful
        $verifyQuery = "SELECT status FROM DIT_post WHERE id=?";
        $stmt = $conn->prepare($verifyQuery);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if (!$row) {
            throw new Exception('Could not retrieve updated record');
        }
        
        if ($row['status'] !== 'archived') {
            throw new Exception('Verification failed: Status is "' . $row['status'] . '" instead of "archived"');
        }
        
        // If we got here, everything succeeded
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Announcement archived successfully']);
        
    } catch (Exception $e) {
        // Something went wrong, rollback
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
$conn->close();
?>