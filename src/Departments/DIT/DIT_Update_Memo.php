<?php
include "../../db.php";
header('Content-Type: application/json');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $description = $_POST['description'];
    
    // Get the current memo data
    $query = "SELECT * FROM DIT_post WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Memo not found']);
        exit;
    }
    
    $updateData = ['content' => $description];
    
    // Handle file upload if provided
    if (!empty($_FILES['file']['name'])) {
        $targetDir = "uploads/";
        
        // Remove old file if it exists
        if (file_exists($targetDir . $row['file_path'])) {
            unlink($targetDir . $row['file_path']);
        }
        
        // Generate new filename
        $now = new DateTime();
        $day = $now->format('d');
        $month = $now->format('m');
        $year = $now->format('Y');
        $hours = $now->format('H');
        $minutes = $now->format('i');
        $newFileName = "memo_${day}_${month}_${year}_${hours}_${minutes}.pdf";
        
        // Move new file
        $targetFilePath = $targetDir . $newFileName;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFilePath)) {
            $updateData['file_path'] = $newFileName;
        } else {
            echo json_encode(['success' => false, 'message' => 'File upload failed']);
            exit;
        }
    }
    
    // Update the database
    $updateQuery = "UPDATE DIT_post SET ";
    $updateParts = [];
    foreach ($updateData as $key => $value) {
        $updateParts[] = "$key = '" . $conn->real_escape_string($value) . "'";
    }
    $updateQuery .= implode(', ', $updateParts);
    $updateQuery .= " WHERE id = " . $row['id'];
    
    if ($conn->query($updateQuery) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
$conn->close();
?>