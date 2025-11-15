<?php
include "../../db.php";

header('Content-Type: application/json');

// Create archive directory if it doesn't exist
 $archiveDir = __DIR__ . '/uploads/archive';
if (!file_exists($archiveDir)) {
    mkdir($archiveDir, 0755, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle single graph
    if (isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        
        // Get graph details
        $stmt = $conn->prepare("SELECT file_path, content FROM DIT_post WHERE id = ? AND title='graph'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $filePath = $row['file_path'];
            $content = $row['content'];
            
            // Move file if it exists
            if (!empty($filePath)) {
                $sourceFile = __DIR__ . '/uploads/' . $filePath;
                $archiveFile = $archiveDir . '/' . $filePath;
                
                if (file_exists($sourceFile)) {
                    copy($sourceFile, $archiveFile);
                    unlink($sourceFile);
                }
            }
            
            // Update status
            $stmt = $conn->prepare("UPDATE DIT_post SET status = 'archived' WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Graph archived successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to archive graph']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Graph not found']);
        }
    }
    // Handle group graphs
    else if (isset($_POST['graphIds']) && is_array($_POST['graphIds'])) {
        $graphIds = array_map('intval', $_POST['graphIds']);
        $idsString = implode(',', $graphIds);
        
        // Get all file paths and content
        $stmt = $conn->prepare("SELECT id, file_path, content FROM DIT_post WHERE id IN ($idsString) AND title='graph'");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $filesToMove = [];
        $graphContents = [];
        
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['file_path'])) {
                $filesToMove[$row['id']] = $row['file_path'];
            }
            $graphContents[$row['id']] = $row['content'];
        }
        
        // Move files
        foreach ($filesToMove as $id => $filePath) {
            $sourceFile = __DIR__ . '/uploads/' . $filePath;
            $archiveFile = $archiveDir . '/' . $filePath;
            
            if (file_exists($sourceFile)) {
                copy($sourceFile, $archiveFile);
                unlink($sourceFile);
            }
        }
        
        // Update status for all graphs
        $sql = "UPDATE DIT_post SET status = 'archived' WHERE id IN ($idsString)";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Graphs archived successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to archive graphs']);
        }
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
}
?>