<?php
include "../../db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $type = $_POST['type'];
    
    try {
        // Get the item details before deleting
        if ($type === 'announcement') {
            $query = "SELECT * FROM Archive WHERE id = ? AND type = 'announcement'";
        } else if ($type === 'graph') {
            $query = "SELECT * FROM Archive WHERE id = ? AND type = 'graph'";
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid type']);
            exit;
        }
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Delete the file if it exists
            if (!empty($row['file_path'])) {
                $filePath = $row['file_path'];
                
                // Check if the path is relative or absolute
                if (strpos($filePath, '/') === 0) {
                    // Absolute path
                    $fullPath = $filePath;
                } else {
                    // Relative path, prepend the current directory
                    $fullPath = __DIR__ . '/../' . $filePath;
                }
                
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            
            // Delete the record from the database
            $deleteQuery = "DELETE FROM Archive WHERE id = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $id);
            
            if ($deleteStmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting item: ' . $conn->error]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Item not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>