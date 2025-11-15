<?php
// Include the database connection file
require_once('../../db.php');
// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if this is a group submission
    $isGroup = isset($_POST['isGroup']) && $_POST['isGroup'] == '1';
    
    // Get tab state parameters
    $mainTab = isset($_POST['mainTab']) ? $_POST['mainTab'] : 'upload';
    $currentTab = isset($_POST['currentTab']) ? $_POST['currentTab'] : 'upload-graphs';
    
    if ($isGroup) {
        // Handle group submission
        $groupTitle = $_POST['groupTitle'];
        $graphCount = $_POST['graphCount'];
        
        if (empty($groupTitle) || empty($graphCount)) {
            echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
            exit;
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
                $labels = $_POST['label'][$i];
                $values = $_POST['value'][$i];
                
                if (empty($labels) || empty($values)) {
                    continue; // Skip graphs without data
                }
                
                // Prepare graph data
                $graphData = [];
                for ($j = 0; $j < count($labels); $j++) {
                    if (!empty($labels[$j]) && isset($values[$j])) {
                        // Parse value - handle percentage, decimal, and integer formats
                        $parsedValue = parseValue($values[$j]);
                        if ($parsedValue !== false) {
                            $graphData[] = [
                                'label' => $labels[$j],
                                'value' => $parsedValue['value'],
                                'format' => $parsedValue['format']
                            ];
                        }
                    }
                }
            } else {
                $categories = $_POST['bar_category'][$i];
                $series1 = $_POST['bar_series1'][$i];
                $series2 = $_POST['bar_series2'][$i];
                $series1Label = $_POST['series1Label'][$i];
                $series2Label = $_POST['series2Label'][$i];
                
                if (empty($categories) || empty($series1) || empty($series2)) {
                    continue; // Skip graphs without data
                }
                
                // Prepare graph data
                $graphData = [];
                for ($j = 0; $j < count($categories); $j++) {
                    if (!empty($categories[$j]) && isset($series1[$j]) && isset($series2[$j])) {
                        // Parse values - handle percentage, decimal, and integer formats
                        $parsedValue1 = parseValue($series1[$j]);
                        $parsedValue2 = parseValue($series2[$j]);
                        
                        if ($parsedValue1 !== false && $parsedValue2 !== false) {
                            $graphData[] = [
                                'category' => $categories[$j],
                                'series1' => $parsedValue1['value'],
                                'series1_format' => $parsedValue1['format'],
                                'series2' => $parsedValue2['value'],
                                'series2_format' => $parsedValue2['format'],
                                'series1_label' => $series1Label,
                                'series2_label' => $series2Label
                            ];
                        }
                    }
                }
            }
            
            if (empty($graphData)) {
                continue; // Skip graphs without valid data
            }
            
            // Check for duplicate graph in the same group (only check title, type, and group_title)
            $checkQuery = "SELECT id FROM graphs WHERE title = ? AND type = ? AND group_title = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param('sss', $graphTitle, $graphType, $groupTitle);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                continue; // Skip duplicate graphs
            }
            
            // Create uploads directory if it doesn't exist
            $uploadDir = __DIR__ . '/uploads';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generate new filename: graph_day_month_year_hour_minute_second_index.xlsx
            date_default_timezone_set('Asia/Manila');
            $newFileName = 'graph_' . date('d_m_Y_H_i_s') . "_{$i}.xlsx";
            $uploadPath = $uploadDir . '/' . $newFileName;
            
            // Create a simple CSV file
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
            if (file_put_contents($uploadPath, $csvContent) === false) {
                continue; // Skip if file creation fails
            }
            
            // Prepare data for database with proper JSON encoding
            $jsonData = json_encode($graphData, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS);
            
            // Insert into database
            try {
                $stmt = $conn->prepare("INSERT INTO graphs (title, type, data, file_path, group_title) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param('sssss', $graphTitle, $graphType, $jsonData, $newFileName, $groupTitle);
                $stmt->execute();
            } catch (Exception $e) {
                // Delete the uploaded file if database insertion fails
                if (file_exists($uploadPath)) {
                    unlink($uploadPath);
                }
                // Continue with next graph
                continue;
            }
        }
        
        // Redirect back to the graphs page with tab state
        echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
    } else {
        // Handle single graph submission
        $title = $_POST['graphTitle'];
        $graphType = $_POST['graphType'];
        
        if (empty($title) || empty($graphType)) {
            echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
            exit;
        }
        
        // Prepare graph data based on graph type
        if ($graphType === 'pie') {
            $labels = $_POST['label'];
            $values = $_POST['value'];
            
            if (empty($labels) || empty($values)) {
                echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
                exit;
            }
            
            // Prepare graph data
            $graphData = [];
            for ($i = 0; $i < count($labels); $i++) {
                if (!empty($labels[$i]) && isset($values[$i])) {
                    // Parse value - handle percentage, decimal, and integer formats
                    $parsedValue = parseValue($values[$i]);
                    if ($parsedValue !== false) {
                        $graphData[] = [
                            'label' => $labels[$i],
                            'value' => $parsedValue['value'],
                            'format' => $parsedValue['format']
                        ];
                    }
                }
            }
        } else {
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
                if (!empty($categories[$i]) && isset($series1[$i]) && isset($series2[$i])) {
                    // Parse values - handle percentage, decimal, and integer formats
                    $parsedValue1 = parseValue($series1[$i]);
                    $parsedValue2 = parseValue($series2[$i]);
                    
                    if ($parsedValue1 !== false && $parsedValue2 !== false) {
                        $graphData[] = [
                            'category' => $categories[$i],
                            'series1' => $parsedValue1['value'],
                            'series1_format' => $parsedValue1['format'],
                            'series2' => $parsedValue2['value'],
                            'series2_format' => $parsedValue2['format'],
                            'series1_label' => $series1Label,
                            'series2_label' => $series2Label
                        ];
                    }
                }
            }
        }
        
        if (empty($graphData)) {
            echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
            exit;
        }
        
        // Check for duplicate graph (only check title, type, and group_title)
        $checkQuery = "SELECT id FROM graphs WHERE title = ? AND type = ? AND group_title IS NULL";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param('ss', $title, $graphType);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            // Graph already exists, redirect back
            echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
            exit;
        }
        
        // Create uploads directory if it doesn't exist
        $uploadDir = __DIR__ . '/uploads';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate new filename: graph_day_month_year_hour_minute_second.xlsx
        date_default_timezone_set('Asia/Manila');
        $newFileName = 'graph_' . date('d_m_Y_H_i_s') . '.xlsx';
        $uploadPath = $uploadDir . '/' . $newFileName;
        
        // Create a simple CSV file
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
        if (file_put_contents($uploadPath, $csvContent) === false) {
            echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
            exit;
        }
        
        // Prepare data for database with proper JSON encoding
        $jsonData = json_encode($graphData, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS);
        
        // Insert into database
        try {
            $stmt = $conn->prepare("INSERT INTO graphs (title, type, data, file_path) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $title, $graphType, $jsonData, $newFileName);
            $stmt->execute();
            
            // Redirect back to the graphs page with tab state
            echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
        } catch (Exception $e) {
            // Delete the uploaded file if database insertion fails
            if (file_exists($uploadPath)) {
                unlink($uploadPath);
            }
            echo "<script>window.location.href = 'CEIT.php?main=$mainTab&tab=$currentTab';</script>";
        }
    }
}
// Function to parse value that can be in different formats (integer, decimal, or percentage)
function parseValue($value) {
    // Trim whitespace
    $value = trim($value);
    
    // Check if it's a percentage
    if (strpos($value, '%') !== false) {
        // Remove % sign and convert to decimal
        $numericValue = str_replace('%', '', $value);
        if (is_numeric($numericValue)) {
            return [
                'value' => floatval($numericValue),
                'format' => 'percentage'
            ];
        }
    }
    
    // Check if it's a regular number (integer or decimal)
    if (is_numeric($value)) {
        // Check if it's an integer (no decimal point or decimal point followed by zeros)
        if (strpos($value, '.') === false || (floatval($value) == intval($value))) {
            return [
                'value' => floatval($value),
                'format' => 'integer'
            ];
        } else {
            return [
                'value' => floatval($value),
                'format' => 'decimal'
            ];
        }
    }
    
    // If we get here, the value is not in a valid format
    return false;
}
?>