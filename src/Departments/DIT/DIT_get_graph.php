<?php
// DIT_get_graph.php
include "../../db.php";
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $id = $_GET['id'] ?? 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid graph ID']);
        exit;
    }
    
    // Get the graph data
    $query = "SELECT * FROM DIT_post WHERE id=? AND title='graph'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Graph not found']);
        exit;
    }
    
    $row = $result->fetch_assoc();
    $graphData = json_decode($row['content'], true);
    
    // Determine if this is a pie or bar chart
    $graphType = 'pie';
    if (isset($graphData['data'][0]) && isset($graphData['data'][0]['category'])) {
        $graphType = 'bar';
    }
    
    // Include group title if it exists
    $groupTitle = $graphData['group_title'] ?? null;
    
    echo json_encode([
        'success' => true,
        'id' => $row['id'],
        'title' => $graphData['title'] ?? 'Untitled Graph',
        'type' => $graphType,
        'data' => $graphData['data'] ?? [],
        'group_title' => $groupTitle // Add group title to response
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
$conn->close();
?>