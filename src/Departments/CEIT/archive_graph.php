<?php
// Include the database connection file
require_once('../../db.php');
// Create archive directory if it doesn't exist
$archiveDir = __DIR__ . '/uploads/archive';
if (!file_exists($archiveDir)) {
    mkdir($archiveDir, 0777, true);
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $graphId = $_POST['graph_id'];
    
    // Get tab state parameters
    $mainTab = isset($_POST['mainTab']) ? $_POST['mainTab'] : 'upload';
    $currentTab = isset($_POST['currentTab']) ? $_POST['currentTab'] : 'upload-graphs';
    
    try {
        // Get the graph details before archiving
        $query = "SELECT * FROM graphs WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $graphId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Copy file to archive directory if it exists
            $sourceFile = __DIR__ . '/uploads/' . $row['file_path'];
            $archiveFile = $archiveDir . '/' . $row['file_path'];
            
            if (file_exists($sourceFile)) {
                if (!copy($sourceFile, $archiveFile)) {
                    echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
                    exit;
                }
            }
            
            // Get the graph data
            $graphData = json_decode($row['data'], true);
            
            // Create archive data structure that preserves the original data and type
            $archiveData = [
                'type' => $row['type'],
                'original_data' => $graphData
            ];
            
            // Re-encode the data
            $formattedData = json_encode($archiveData);
            
            // Insert into archive table
            $archiveQuery = "INSERT INTO Archive (type, title, data, file_path, archived_at) VALUES (?, ?, ?, ?, NOW())";
            $archiveStmt = $conn->prepare($archiveQuery);
            
            $type = 'graph';
            $title = $row['title'];
            $data = $formattedData;
            $file_path = 'archive/' . $row['file_path']; // Update path to include archive folder
            
            $archiveStmt->bind_param("ssss", $type, $title, $data, $file_path);
            
            if ($archiveStmt->execute()) {
                // Now delete from graphs table
                $deleteQuery = "DELETE FROM graphs WHERE id = ?";
                $deleteStmt = $conn->prepare($deleteQuery);
                $deleteStmt->bind_param("i", $graphId);
                
                if ($deleteStmt->execute()) {
                    // Delete the original file after successful archive
                    if (file_exists($sourceFile)) {
                        unlink($sourceFile);
                    }
                    
                    echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
                } else {
                    echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
                }
            } else {
                echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
            }
        } else {
            echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
        }
    } catch (Exception $e) {
        echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
    }
} else {
    echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
}
?>