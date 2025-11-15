<?php
// DIT_Update_Graph.php
include "../../db.php";
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['graph_id'] ?? 0;
    $title = $_POST['graphTitle'] ?? '';
    $graphType = $_POST['graphType'] ?? '';
    $groupTitle = $_POST['groupTitle'] ?? ''; // Get group title if provided
    
    // Validate data
    if (empty($id) || empty($title) || empty($graphType)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    // Get the current file path and existing content
    $query = "SELECT file_path, content FROM DIT_post WHERE id=? AND title='graph'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Graph not found']);
        exit;
    }
    
    $row = $result->fetch_assoc();
    $filePath = __DIR__ . '/uploads/' . $row['file_path'];
    $existingContent = json_decode($row['content'], true);
    
    // Preserve group title if it exists
    if (empty($groupTitle) && isset($existingContent['group_title'])) {
        $groupTitle = $existingContent['group_title'];
    }
    
    // Prepare graph data based on graph type
    if ($graphType === 'pie') {
        // Get graph data from the form
        $labels = $_POST['label'] ?? [];
        $values = $_POST['value'] ?? [];
        
        if (empty($labels) || empty($values)) {
            echo json_encode(['success' => false, 'message' => 'Label and value arrays are required for pie charts']);
            exit;
        }
        
        // Prepare graph data
        $graphData = [];
        for ($i = 0; $i < count($labels); $i++) {
            if (!empty($labels[$i]) && isset($values[$i]) && is_numeric($values[$i])) {
                $graphData[] = [
                    'label' => $labels[$i],
                    'value' => intval($values[$i])
                ];
            }
        }
    } else {
        // Get bar graph data from the form
        $categories = $_POST['bar_category'] ?? [];
        $series1 = $_POST['bar_series1'] ?? [];
        $series2 = $_POST['bar_series2'] ?? [];
        $series1Label = $_POST['series1Label'] ?? 'Series 1';
        $series2Label = $_POST['series2Label'] ?? 'Series 2';
        
        if (empty($categories) || empty($series1) || empty($series2)) {
            echo json_encode(['success' => false, 'message' => 'Category and series arrays are required for bar charts']);
            exit;
        }
        
        // Prepare graph data
        $graphData = [];
        for ($i = 0; $i < count($categories); $i++) {
            if (!empty($categories[$i]) && isset($series1[$i]) && is_numeric($series1[$i]) && 
                isset($series2[$i]) && is_numeric($series2[$i])) {
                $graphData[] = [
                    'category' => $categories[$i],
                    'series1' => intval($series1[$i]),
                    'series2' => intval($series2[$i]),
                    'series1_label' => $series1Label,
                    'series2_label' => $series2Label
                ];
            }
        }
    }
    
    if (empty($graphData)) {
        echo json_encode(['success' => false, 'message' => 'No valid graph data provided']);
        exit;
    }
    
    // Create updated CSV file
    $csvContent = "";
    if ($graphType === 'pie') {
        $csvContent = "Label,Value\n";
        foreach ($graphData as $item) {
            $csvContent .= "{$item['label']},{$item['value']}\n";
        }
    } else {
        $csvContent = "Category,Series 1,Series 2\n";
        foreach ($graphData as $item) {
            $csvContent .= "{$item['category']},{$item['series1']},{$item['series2']}\n";
        }
    }
    
    // Save CSV file
    if (file_put_contents($filePath, $csvContent) === false) {
        echo json_encode(['success' => false, 'message' => 'Failed to update data file']);
        exit;
    }
    
    // Prepare data for database - include group title if it exists
    $jsonDataArray = [
        'title' => $title,
        'data' => $graphData
    ];
    
    // Add group title if it exists
    if (!empty($groupTitle)) {
        $jsonDataArray['group_title'] = $groupTitle;
    }
    
    $jsonData = json_encode($jsonDataArray);
    
    // Update the database
    $query = "UPDATE DIT_post SET content=? WHERE id=? AND title='graph'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $jsonData, $id);
    
    if ($stmt->execute()) {
        // Return success with redirect URL to stay on the same page
        echo json_encode([
            'success' => true, 
            'redirect' => 'DIT.php?main=upload&tab=upload-graphs'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
$conn->close();
?>