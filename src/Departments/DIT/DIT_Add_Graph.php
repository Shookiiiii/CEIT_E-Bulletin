<?php
// DIT_Add_Graph.php
include "../../db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get tab state parameters
    $mainTab = isset($_POST['mainTab']) ? $_POST['mainTab'] : 'upload';
    $currentTab = isset($_POST['currentTab']) ? $_POST['currentTab'] : 'upload-graphs';
    
    // Check if this is a group submission
    $isGroup = isset($_POST['isGroup']) && $_POST['isGroup'] == '1';
    
    if ($isGroup) {
        // Handle group graph submission
        $groupTitle = $_POST['groupTitle'];
        $graphCount = $_POST['graphCount'];
        
        if (empty($groupTitle) || empty($graphCount)) {
            echo "<script>window.location.href = 'DIT.php?main=$mainTab&tab=$currentTab';</script>";
            exit;
        }
        
        // Create a directory for uploads if it doesn't exist
        $uploadDir = __DIR__ . '/uploads';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Process each graph in the group
        for ($i = 0; $i < $graphCount; $i++) {
            $graphTitle = $_POST['graphTitle'][$i];
            $graphType = $_POST['graphType'][$i];
            
            if (empty($graphTitle) || empty($graphType)) {
                continue; // Skip incomplete graphs
            }
            
            // Prepare graph data based on graph type
            if ($graphType === 'pie') {
                // Get graph data from the form
                $labels = $_POST['label'][$i];
                $values = $_POST['value'][$i];
                
                if (empty($labels) || empty($values)) {
                    continue; // Skip incomplete graphs
                }
                
                // Prepare graph data
                $graphData = [];
                for ($j = 0; $j < count($labels); $j++) {
                    if (!empty($labels[$j]) && isset($values[$j]) && is_numeric($values[$j])) {
                        $graphData[] = [
                            'label' => $labels[$j],
                            'value' => intval($values[$j])
                        ];
                    }
                }
            } else {
                // Get bar graph data from the form
                $categories = $_POST['bar_category'][$i];
                $series1 = $_POST['bar_series1'][$i];
                $series2 = $_POST['bar_series2'][$i];
                $series1Label = $_POST['series1Label'][$i];
                $series2Label = $_POST['series2Label'][$i];
                
                if (empty($categories) || empty($series1) || empty($series2)) {
                    continue; // Skip incomplete graphs
                }
                
                // Prepare graph data
                $graphData = [];
                for ($j = 0; $j < count($categories); $j++) {
                    if (!empty($categories[$j]) && isset($series1[$j]) && is_numeric($series1[$j]) && 
                        isset($series2[$j]) && is_numeric($series2[$j])) {
                        $graphData[] = [
                            'category' => $categories[$j],
                            'series1' => intval($series1[$j]),
                            'series2' => intval($series2[$j]),
                            'series1_label' => $series1Label,
                            'series2_label' => $series2Label
                        ];
                    }
                }
            }
            
            if (empty($graphData)) {
                continue; // Skip graphs with no data
            }
            
            // Generate a unique filename for this graph
            $timestamp = time();
            $randomString = bin2hex(random_bytes(4));
            $filename = "graph_{$timestamp}_{$randomString}_{$i}.csv";
            $filePath = $filename;
            
            // Create CSV content
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
            $uploadPath = $uploadDir . '/' . $filename;
            if (file_put_contents($uploadPath, $csvContent) === false) {
                continue; // Skip if file save fails
            }
            
            // Prepare data for database
            $jsonData = json_encode([
                'title' => $graphTitle,
                'data' => $graphData,
                'group_title' => $groupTitle
            ], JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS);
            
            // Insert into database - FIXED: Correct number of parameters
            $stmt = $conn->prepare("INSERT INTO DIT_post (title, content, file_path, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
            $title = 'graph';
            $stmt->bind_param("sss", $title, $jsonData, $filePath);
            $stmt->execute();
        }
        
        // Redirect back to the graphs tab
        echo "<script>window.location.href = 'DIT.php?main=$mainTab&tab=$currentTab';</script>";
        exit;
    } else {
        // Handle single graph submission (original code)
        $graphType = $_POST['graphType'];
        $graphTitle = $_POST['graphTitle'];
        
        if (empty($graphType) || empty($graphTitle)) {
            echo "<script>window.location.href = 'DIT.php?main=$mainTab&tab=$currentTab';</script>";
            exit;
        }
        
        // Create a directory for uploads if it doesn't exist
        $uploadDir = __DIR__ . '/uploads';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Prepare graph data based on graph type
        if ($graphType === 'pie') {
            // Get graph data from the form
            $labels = $_POST['label'];
            $values = $_POST['value'];
            
            if (empty($labels) || empty($values)) {
                echo "<script>window.location.href = 'DIT.php?main=$mainTab&tab=$currentTab';</script>";
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
                echo "<script>window.location.href = 'DIT.php?main=$mainTab&tab=$currentTab';</script>";
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
            echo "<script>window.location.href = 'DIT.php?main=$mainTab&tab=$currentTab';</script>";
            exit;
        }
        
        // Generate a unique filename
        $timestamp = time();
        $randomString = bin2hex(random_bytes(4));
        $filename = "graph_{$timestamp}_{$randomString}.csv";
        $filePath = $filename;
        
        // Create CSV content
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
        $uploadPath = $uploadDir . '/' . $filename;
        if (file_put_contents($uploadPath, $csvContent) === false) {
            echo "<script>window.location.href = 'DIT.php?main=$mainTab&tab=$currentTab';</script>";
            exit;
        }
        
        // Prepare data for database
        $jsonData = json_encode([
            'title' => $graphTitle,
            'data' => $graphData
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS);
        
        // Insert into database - FIXED: Correct number of parameters
        $stmt = $conn->prepare("INSERT INTO DIT_post (title, content, file_path, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
        $title = 'graph';
        $stmt->bind_param("sss", $title, $jsonData, $filePath);
        $stmt->execute();
        
        // Redirect back to the graphs tab
        echo "<script>window.location.href = 'DIT.php?main=$mainTab&tab=$currentTab';</script>";
        exit;
    }
}
?>