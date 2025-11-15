<?php
include "../../db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $description = $_POST['description'];
    
    // Check if a new file was uploaded
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];
        
        // Check file size (limit to 10MB)
        if ($fileSize > 10 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File size exceeds 10MB limit']);
            exit;
        }
        
        // Check file type
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'image/jpeg',
            'image/png',
            'image/gif'
        ];
        
        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'File type not allowed']);
            exit;
        }
        
        // Get the current file path to delete it
        $query = "SELECT file_path FROM Central_post WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $oldFilePath = 'uploads/' . $row['file_path'];
            
            // Delete the old file if it exists
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }
        
        // Get the new filename (already generated in the JavaScript)
        $newFilename = $_FILES['file']['name'];
        
        // Move the file to the uploads directory
        $destPath = 'uploads/' . $newFilename;
        
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // Update the record in the database
            $query = "UPDATE Central_post SET content = ?, file_path = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $description, $newFilename, $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Announcement updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating announcement: ' . $conn->error]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error uploading file: ' . error_get_last()['message']]);
        }
    } else {
        // No new file, just update the description
        $query = "UPDATE Central_post SET content = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $description, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Announcement updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating announcement: ' . $conn->error]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>