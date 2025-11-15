<?php
require_once('../../db.php');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $graphId = $_POST['graph_id'];
    $title = $_POST['graphTitle'];
    $graphType = $_POST['graphType'];
    
    // Get tab state parameters
    $mainTab = isset($_POST['mainTab']) ? $_POST['mainTab'] : 'upload';
    $currentTab = isset($_POST['currentTab']) ? $_POST['currentTab'] : 'upload-graphs';
    
    // Validate data
    if (empty($title) || empty($graphType)) {
        echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
        exit;
    }
    
    // Prepare graph data based on graph type
    if ($graphType === 'pie') {
        // Get graph data from the form
        $labels = $_POST['label'];
        $values = $_POST['value'];
        
        if (empty($labels) || empty($values)) {
            echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
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
        $categories = $_POST['bar_category'];
        $series1 = $_POST['bar_series1'];
        $series2 = $_POST['bar_series2'];
        $series1Label = $_POST['series1Label'];
        $series2Label = $_POST['series2Label'];
        
        if (empty($categories) || empty($series1) || empty($series2)) {
            echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
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
        echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
        exit;
    }
    
    // Get the current file path
    try {
        $stmt = $conn->prepare("SELECT file_path FROM graphs WHERE id = ?");
        $stmt->bind_param('i', $graphId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $filePath = $row['file_path'];
            
            // Update the CSV file
            $uploadDir = __DIR__ . '/uploads';
            $uploadPath = $uploadDir . '/' . $filePath;
            
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
            
            if (file_put_contents($uploadPath, $csvContent) === false) {
                echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
                exit;
            }
            
            // Prepare data for database
            $jsonData = json_encode($graphData);
            
            // Update database
            $stmt = $conn->prepare("UPDATE graphs SET title = ?, type = ?, data = ? WHERE id = ?");
            $stmt->bind_param('sssi', $title, $graphType, $jsonData, $graphId);
            $stmt->execute();
            
            echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
        } else {
            echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
        }
    } catch (Exception $e) {
        echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
    }
}
?>