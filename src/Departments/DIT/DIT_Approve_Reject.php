<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../../db.php";
header('Content-Type: application/json');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $action = $_POST['action'];
    
    if ($action == 'approve') {
        // Update status to approved
        $stmt = $conn->prepare("UPDATE DIT_post SET status='approved' WHERE id=?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            exit;
        }
        
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'redirect' => '/CEIT_E-Bulletin/src/Departments/CEIT/CEIT.php?tab=DIT&main=manage&subtab=dit-announcement']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
        }
    } else if ($action == 'reject') {
        // Update status to not approved and store rejection reason
        if (!isset($_POST['reason']) || empty(trim($_POST['reason']))) {
            echo json_encode(['success' => false, 'message' => 'Rejection reason is required']);
            exit;
        }
        
        $reason = trim($_POST['reason']);
        
        // Get the current content to preserve it
        $stmt = $conn->prepare("SELECT content FROM DIT_post WHERE id=?");
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
        
        $row = $result->fetch_assoc();
        $currentContent = $row['content'];
        
        // Update with rejection reason and preserve original content
        $stmt = $conn->prepare("UPDATE DIT_post SET status='not approved', content=?, description=? WHERE id=?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            exit;
        }
        
        $stmt->bind_param("ssi", $reason, $currentContent, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'redirect' => '/CEIT_E-Bulletin/src/Departments/CEIT/CEIT.php?tab=DIT&main=manage&subtab=dit-announcement']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
$conn->close();
?>