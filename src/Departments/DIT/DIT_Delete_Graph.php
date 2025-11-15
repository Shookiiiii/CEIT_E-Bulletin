<?php
// DIT_Delete_Graph.php
include "../../db.php";
header('Content-Type: application/json');
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if this is a group operation
    $isGroup = isset($_POST['isGroup']) && $_POST['isGroup'] == '1';
    
    if ($isGroup) {
        // Handle group deletion
        if (!isset($_POST['graphIds']) || !is_array($_POST['graphIds'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid graph IDs']);
            exit;
        }
        
        $graphIds = $_POST['graphIds'];
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Get file paths for all graphs in the group
            $placeholders = implode(',', array_fill(0, count($graphIds), '?'));
            $types = str_repeat('i', count($graphIds));
            
            $stmt = $conn->prepare("SELECT id, file_path, status FROM DIT_post WHERE id IN ($placeholders) AND title='graph'");
            $stmt->bind_param($types, ...$graphIds);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Graphs not found');
            }
            
            // Delete files and database records
            while ($row = $result->fetch_assoc()) {
                $id = $row['id'];
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
                $deleteStmt = $conn->prepare("DELETE FROM DIT_post WHERE id=? AND title='graph'");
                $deleteStmt->bind_param("i", $id);
                $deleteStmt->execute();
            }
            
            // If we got here, everything succeeded
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Graphs deleted successfully']);
            
        } catch (Exception $e) {
            // Something went wrong, rollback
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        // Handle single graph deletion (original code)
        if (!isset($_POST['id'])) {
            echo json_encode(['success' => false, 'message' => 'Graph ID not provided']);
            exit;
        }
        
        $id = $_POST['id'];
        
        // First, check if the graph exists by ID only (regardless of status or title)
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
            echo json_encode(['success' => false, 'message' => 'Graph not found']);
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
            echo json_encode(['success' => false, 'message' => 'Graph not found']);
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
        $query = "DELETE FROM DIT_post WHERE id=? AND title='graph'";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Graph deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No rows were affected during deletion']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
$conn->close();
?>