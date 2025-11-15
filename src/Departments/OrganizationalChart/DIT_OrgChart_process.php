<?php
include '../../db.php';
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($conn->query("DELETE FROM DIT_Organization WHERE id = $id")) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['member_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $role = $conn->real_escape_string($_POST['role']);
    $position = $conn->real_escape_string($_POST['position_code']);

    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../OrganizationalChart/uploadDIT/";
        $photo = basename($_FILES["photo"]["name"]);
        $targetFile = $targetDir . $photo;

        // Create directory if needed
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile);
    }
    if ($id > 0) {
        // Update existing
        if ($photo) {
            $stmt = $conn->prepare("UPDATE DIT_Organization SET name=?, role=?, photo=?, position_code=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $role, $photo, $position, $id);
        } else {
            // Keep existing photo
            $stmt = $conn->prepare("UPDATE DIT_Organization SET name=?, role=?, position_code=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $role, $position, $id);
        }
    } else {
        // Insert new - use the position_code from the form
        $stmt = $conn->prepare("INSERT INTO DIT_Organization (name, role, photo, position_code) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $role, $photo, $position);
    }
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    exit;
}
echo json_encode(['success' => false, 'error' => 'Invalid request']);
