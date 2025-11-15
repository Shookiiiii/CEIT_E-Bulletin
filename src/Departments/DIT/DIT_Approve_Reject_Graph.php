<?php
include "../../db.php";

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    
    // Handle single graph
    if (isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        
        if ($action == 'approve') {
            $stmt = $conn->prepare("UPDATE DIT_post SET status = 'approved' WHERE id = ? AND title='graph'");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Graph approved successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to approve graph']);
            }
        } 
        else if ($action == 'reject') {
            $reason = $_POST['reason'] ?? '';
            if (empty($reason)) {
                echo json_encode(['success' => false, 'message' => 'Please provide a rejection reason']);
                exit;
            }
            
            // Get original content first
            $stmt = $conn->prepare("SELECT content FROM DIT_post WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $originalContent = $row['content'];
            
            // Update with rejection
            $stmt = $conn->prepare("UPDATE DIT_post SET status = 'not approved', description = ?, content = ? WHERE id = ?");
            $stmt->bind_param("ssi", $originalContent, $reason, $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Graph rejected successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to reject graph']);
            }
        }
    }
    // Handle group graphs
    else if (isset($_POST['graphIds']) && is_array($_POST['graphIds'])) {
        $graphIds = array_map('intval', $_POST['graphIds']);
        $idsString = implode(',', $graphIds);
        
        if ($action == 'approve') {
            $sql = "UPDATE DIT_post SET status = 'approved' WHERE id IN ($idsString) AND title='graph'";
            
            if ($conn->query($sql)) {
                echo json_encode(['success' => true, 'message' => 'Graphs approved successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to approve graphs']);
            }
        } 
        else if ($action == 'reject') {
            $reason = $_POST['reason'] ?? '';
            if (empty($reason)) {
                echo json_encode(['success' => false, 'message' => 'Please provide a rejection reason']);
                exit;
            }
            
            // Get original content for all graphs
            $originalContents = [];
            foreach ($graphIds as $id) {
                $stmt = $conn->prepare("SELECT content FROM DIT_post WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $originalContents[$id] = $row['content'];
                }
            }
            
            // Update each graph
            $success = true;
            foreach ($graphIds as $id) {
                if (isset($originalContents[$id])) {
                    $stmt = $conn->prepare("UPDATE DIT_post SET status = 'not approved', description = ?, content = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $originalContents[$id], $reason, $id);
                    if (!$stmt->execute()) {
                        $success = false;
                    }
                } else {
                    $success = false;
                }
            }
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Graphs rejected successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to reject some graphs']);
            }
        }
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
}
?>