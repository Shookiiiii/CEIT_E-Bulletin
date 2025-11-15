<?php
// DIT_Archive.php
include "../../db.php";
// Get archived items by type
 $archived_announcements = [];
 $archived_memos = [];
 $archived_graphs = [];

// Debug function
function debug_query($query, $params = []) {
    global $conn;
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Get archived announcements
 $query = "SELECT * FROM DIT_post WHERE title='announcement' AND status='archived' ORDER BY created_at DESC";
 $archived_announcements = debug_query($query);

// Get archived memos
 $query = "SELECT * FROM DIT_post WHERE title='Memo' AND status='archived' ORDER BY created_at DESC";
 $archived_memos = debug_query($query);

// Get archived graphs
 $query = "SELECT * FROM DIT_post WHERE title='graph' AND status='archived' ORDER BY created_at DESC";
 $archived_graphs_raw = debug_query($query);

// Process graph data and group by group title
 $archived_graphs = [];
 $graph_groups = [];

foreach ($archived_graphs_raw as $row) {
    $graphData = json_decode($row['content'], true);
    $group_title = $graphData['group_title'] ?? null;
    
    // Determine graph type
    $graph_type = 'pie'; // Default to pie
    if (isset($graphData['data']) && !empty($graphData['data'])) {
        // Check if first data point has 'category' key (bar chart) or 'label' key (pie chart)
        if (isset($graphData['data'][0]['category'])) {
            $graph_type = 'bar';
        }
    }
    
    $graph_info = [
        'id' => $row['id'],
        'title' => $graphData['title'] ?? 'Untitled Graph',
        'data' => $graphData['data'] ?? [],
        'file_path' => $row['file_path'],
        'posted_on' => $row['created_at'] ?? date('Y-m-d H:i:s'),
        'group_title' => $group_title,
        'graph_type' => $graph_type
    ];
    
    if ($group_title) {
        // This graph belongs to a group
        if (!isset($graph_groups[$group_title])) {
            $graph_groups[$group_title] = [
                'title' => $group_title,
                'graphs' => [],
                'ids' => []
            ];
        }
        $graph_groups[$group_title]['graphs'][] = $graph_info;
        $graph_groups[$group_title]['ids'][] = $row['id'];
    } else {
        // Individual graph
        $archived_graphs[] = $graph_info;
    }
}

// Process announcements and memos - FIXED: Use archive directory for archived items
 $processed_announcements = [];
foreach ($archived_announcements as $row) {
    $processed_announcements[] = [
        'file_path' => 'uploads/archive/' . $row['file_path'], // Changed to archive directory
        'description' => $row['content'],
        'id' => $row['id'],
        'posted_on' => $row['created_at'] ?? date('Y-m-d H:i:s')
    ];
}
 $processed_memos = [];
foreach ($archived_memos as $row) {
    $processed_memos[] = [
        'file_path' => 'uploads/archive/' . $row['file_path'], // Changed to archive directory
        'description' => $row['content'],
        'id' => $row['id'],
        'posted_on' => $row['created_at'] ?? date('Y-m-d H:i:s')
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIT Archive</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <style>
        .archive-section {
            margin-bottom: 40px;
            padding: 20px;
            border-radius: 8px;
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
        }
        .archive-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2563eb;
            color: #2563eb;
        }
        .file-preview {
            width: 100%;
            height: 200px;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f9fafb;
        }
        .file-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }
        .modal-content {
            position: relative;
            background-color: white;
            margin: 2% auto;
            padding: 0;
            width: 90%;
            max-width: 1200px;
            max-height: 90vh;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .modal-header {
            padding: 15px 20px;
            background-color: #3b82f6;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
        }
        .modal-close {
            font-size: 2rem;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .modal-close:hover {
            transform: scale(1.2);
        }
        .modal-body {
            padding: 0;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            height: calc(100% - 140px);
        }
        .pdf-container {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .pdf-page {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 100%;
            max-height: 100%;
        }
        .modal-footer {
            padding: 15px 20px;
            background-color: #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-meta {
            font-size: 0.9rem;
            color: #6b7280;
        }
        .page-navigation {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .page-nav-btn {
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s;
        }
        .page-nav-btn:hover {
            background-color: #2563eb;
            transform: scale(1.1);
        }
        .page-nav-btn:disabled {
            background-color: #d1d5db;
            cursor: not-allowed;
            transform: scale(1);
        }
        .page-indicator {
            font-weight: 600;
            color: #4b5563;
            min-width: 80px;
            text-align: center;
        }
        .loading-spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top: 4px solid #3b82f6;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            margin: 0 auto;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .graph-card {
            max-width: 900px;
            margin: 0 auto;
        }
        .archive-delete-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 100000;
            align-items: center;
            justify-content: center;
        }
        .archive-delete-modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
        }
        .archive-delete-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .archive-delete-modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #dc2626;
        }
        .archive-delete-modal-close {
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
            transition: color 0.2s;
        }
        .archive-delete-modal-close:hover {
            color: #dc2626;
        }
        .archive-delete-modal-body {
            margin-bottom: 20px;
        }
        .archive-delete-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn-cancel {
            padding: 8px 16px;
            background-color: #f3f4f6;
            color: #4b5563;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-cancel:hover {
            background-color: #e5e7eb;
        }
        .btn-delete {
            padding: 8px 16px;
            background-color: #dc2626;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-delete:hover {
            background-color: #b91c1c;
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            transform: translateX(150%);
        }
        .notification.show {
            transform: translateX(0);
        }
        .notification.success {
            background-color: #10b981;
        }
        .notification.error {
            background-color: #ef4444;
        }
        /* Group graph card styles */
        .group-graph-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-left: 4px solid #8b5cf6;
            min-height: 400px;
            display: flex;
            flex-direction: column;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .group-graph-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .group-graph-header {
            background-color: #f5f3ff;
            border-bottom: 1px solid #e9d5ff;
            border-radius: 12px 12px 0 0;
        }
        .group-graph-title {
            color: #6d28d9;
        }
        .group-graph-content {
            max-height: 500px;
            overflow-y: auto;
            flex-grow: 1;
        }
        /* Nested graph styles */
        .nested-graph {
            margin-bottom: 32px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1rem;
            background-color: #f9fafb;
            min-height: 300px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        .nested-graph-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .nested-graph-title {
            font-weight: 600;
            color: #4b5563;
        }
        .nested-graph-actions {
            display: flex;
            gap: 0.5rem;
        }
        .nested-graph-chart {
            height: 300px;
        }
        /* Table styles for pie charts - Updated to match DIT_Graph.php */
        .pie-chart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        .pie-chart-table th, .pie-chart-table td {
            padding: 0.75rem 1rem;
            text-align: center;
            border: 1px solid #e5e7eb;
        }
        .pie-chart-table th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #4b5563;
            text-transform: uppercase;
            font-size: 0.75rem;
        }
        .pie-chart-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .pie-chart-table tr:hover {
            background-color: #f3f4f6;
        }
        /* Graph card styles */
        .graph-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .graph-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 5% auto;
            }
            .modal-header {
                padding: 10px 15px;
            }
            .modal-title {
                font-size: 1.2rem;
            }
            .modal-footer {
                flex-direction: column;
                gap: 10px;
            }
            .page-navigation {
                width: 100%;
                justify-content: center;
            }
            .modal-meta {
                text-align: center;
                width: 100%;
            }
            .archive-delete-modal-content {
                width: 95%;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-blue-600 mb-4 md:mb-0">DIT Archive</h1>
            <button onclick="window.location.href='DIT.php?tab=upload-announcements'"
                class="border-2 border-blue-500 bg-white hover:bg-blue-500 text-blue-500 hover:text-white px-4 py-2 rounded transition duration-200 transform hover:scale-110">
                <i class="fas fa-arrow-left"></i> Back to DIT
            </button>
        </div>
        
        <!-- Notification Container -->
        <div id="notification" class="notification"></div>
        
        <!-- Archived Announcements -->
        <div class="archive-section">
            <h2 class="archive-title">Archived Announcements</h2>
            <?php if (count($processed_announcements) > 0): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <?php foreach ($processed_announcements as $index => $pdf): ?>
                        <div class="bg-white shadow-md rounded-lg p-4 w-full h-full flex flex-col justify-between border border-blue-500 transition duration-200 transform hover:scale-105">
                            <div class="mb-3 border border-gray-300 rounded">
                                <div id="file-preview-announcement-<?= $index ?>" class="file-preview">
                                    <div class="loading-spinner"></div>
                                </div>
                            </div>
                            <div class="card-body flex-grow">
                                <div class="file-title font-semibold text-gray-800 text-lg mb-1 truncate">
                                    <?= htmlspecialchars($pdf['description']) ?>
                                </div>
                                <p class="card-text text-gray-600 text-sm truncate">
                                    <?= basename($pdf['file_path']) ?>
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Posted on: <?= date('F j, Y', strtotime($pdf['posted_on'])) ?>
                                </p>
                            </div>
                            <div class="flex justify-end mt-4 space-x-2">
                                <button id="view-full-announcement-<?= $index ?>" class="p-2 border rounded border-gray-500 text-gray-500 hover:bg-gray-500 hover:text-white transition duration-200 transform hover:scale-110" title="View Full Document" data-file-type="pdf" data-file-path="<?= $pdf['file_path'] ?>"> 
                                    <i class="fas fa-eye fa-sm"></i>
                                    View
                                </button>
                                <button class="p-2 border border-red-500 text-red-500 rounded hover:bg-red-500 hover:text-white transition duration-200 transform hover:scale-110 archive-delete-btn" data-id="<?= $pdf['id'] ?>" data-type="announcement" title="Delete">
                                    <i class="fas fa-trash fa-sm"></i>
                                    Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-archive fa-3x mb-4"></i>
                    <p class="text-lg">No archived announcements</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Archived Memos -->
        <div class="archive-section">
            <h2 class="archive-title">Archived Memos</h2>
            <?php if (count($processed_memos) > 0): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <?php foreach ($processed_memos as $index => $pdf): ?>
                        <div class="bg-white shadow-md rounded-lg p-4 w-full h-full flex flex-col justify-between border border-blue-500 transition duration-200 transform hover:scale-105">
                            <div class="mb-3 border border-gray-300 rounded">
                                <div id="file-preview-memo-<?= $index ?>" class="file-preview">
                                    <div class="loading-spinner"></div>
                                </div>
                            </div>
                            <div class="card-body flex-grow">
                                <div class="file-title font-semibold text-gray-800 text-lg mb-1 truncate">
                                    <?= htmlspecialchars($pdf['description']) ?>
                                </div>
                                <p class="card-text text-gray-600 text-sm truncate">
                                    <?= basename($pdf['file_path']) ?>
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Posted on: <?= date('F j, Y', strtotime($pdf['posted_on'])) ?>
                                </p>
                            </div>
                            <div class="flex justify-end mt-4 space-x-2">
                                <button id="view-full-memo-<?= $index ?>" class="p-2 border rounded border-gray-500 text-gray-500 hover:bg-gray-500 hover:text-white transition duration-200 transform hover:scale-110" title="View Full Document" data-file-type="pdf" data-file-path="<?= $pdf['file_path'] ?>"> 
                                    <i class="fas fa-eye fa-sm"></i>
                                    View
                                </button>
                                <button class="p-2 border border-red-500 text-red-500 rounded hover:bg-red-500 hover:text-white transition duration-200 transform hover:scale-110 archive-delete-btn" data-id="<?= $pdf['id'] ?>" data-type="memo" title="Delete">
                                    <i class="fas fa-trash fa-sm"></i>
                                    Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-archive fa-3x mb-4"></i>
                    <p class="text-lg">No archived memos</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Archived Graphs (Combined Section) -->
        <div class="archive-section">
            <h2 class="archive-title">Archived Graphs</h2>
            <?php if (!empty($graph_groups) || count($archived_graphs) > 0): ?>
                <div class="grid grid-cols-1 gap-6">
                    <?php 
                    // First, display the groups
                    foreach ($graph_groups as $group): 
                    ?>
                        <div class="bg-white shadow-md rounded-lg p-4 w-full border border-blue-500 transition duration-200 transform hover:scale-105 group-graph-card">
                            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 flex flex-col sm:flex-row justify-between items-center group-graph-header">
                                <h2 class="text-xl font-semibold group-graph-title mb-2 sm:mb-0"><?= htmlspecialchars($group['title']) ?></h2>
                                <div class="flex space-x-2">
                                    <button class="p-2 border border-red-500 text-red-500 rounded hover:bg-red-500 hover:text-white transition duration-200 transform hover:scale-110 archive-delete-btn" data-id="<?= implode(',', $group['ids']) ?>" data-type="graph-group" title="Delete Group">
                                        <i class="fas fa-trash fa-sm"></i>
                                        Delete
                                    </button>
                                </div>
                            </div>
                            <div class="p-6 group-graph-content">
                                <?php foreach ($group['graphs'] as $index => $graph): ?>
                                    <div class="nested-graph">
                                        <div class="nested-graph-header">
                                            <h3 class="nested-graph-title"><?= htmlspecialchars($graph['title']) ?></h3>
                                            <div class="nested-graph-actions">
                                                <!-- No individual actions for graphs in groups -->
                                            </div>
                                        </div>
                                        <div class="nested-graph-chart">
                                            <?php if ($graph['graph_type'] === 'bar'): ?>
                                                <!-- Bar Chart Only -->
                                                <div class="chart-container">
                                                    <canvas id="chart-group-<?= md5($group['title']) ?>-<?= $index ?>"></canvas>
                                                </div>
                                            <?php else: ?>
                                                <!-- Pie Chart with Table -->
                                                <div class="flex flex-col md:flex-row gap-4">
                                                    <div class="w-full md:w-1/2">
                                                        <div class="table-responsive">
                                                            <table class="pie-chart-table w-full divide-y divide-gray-200 text-sm">
                                                                <thead class="bg-gray-50">
                                                                    <tr>
                                                                        <th class="py-3 px-4 text-center font-medium text-gray-500 uppercase">Category</th>
                                                                        <th class="py-3 px-4 text-center font-medium text-gray-500 uppercase">Count</th>
                                                                        <th class="py-3 px-4 text-center font-medium text-gray-500 uppercase">%</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="bg-white divide-y divide-gray-200">
                                                                    <?php 
                                                                    $total = array_sum(array_column($graph['data'], 'value'));
                                                                    foreach ($graph['data'] as $item): 
                                                                        $percentage = round(($item['value'] / $total) * 100, 2);
                                                                    ?>
                                                                        <tr class="hover:bg-gray-50">
                                                                            <td class="py-3 px-4 font-medium text-gray-900 text-center break-words max-w-[100px]" title="<?= htmlspecialchars($item['label'] ?? '') ?>"><?= htmlspecialchars($item['label'] ?? '') ?></td>
                                                                            <td class="py-3 px-4 text-gray-500 text-center"><?= htmlspecialchars($item['value'] ?? '') ?></td>
                                                                            <td class="py-3 px-4 text-gray-500 text-center"><?= $percentage ?>%</td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="w-full md:w-1/2">
                                                        <div class="chart-container">
                                                            <canvas id="chart-group-<?= md5($group['title']) ?>-<?= $index ?>"></canvas>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php 
                    // Then, display the individual graphs
                    foreach ($archived_graphs as $index => $graph): 
                    ?>
                        <div class="bg-white shadow-md rounded-lg p-4 w-full border border-blue-500 transition duration-200 transform hover:scale-105 graph-card">
                            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 flex flex-col sm:flex-row justify-between items-center">
                                <h2 class="text-xl font-semibold text-gray-800 mb-2 sm:mb-0"><?= htmlspecialchars($graph['title']) ?></h2>
                                <div class="flex space-x-2">
                                    <button class="p-2 border border-red-500 text-red-500 rounded hover:bg-red-500 hover:text-white transition duration-200 transform hover:scale-110 archive-delete-btn" data-id="<?= $graph['id'] ?>" data-type="graph" title="Delete">
                                        <i class="fas fa-trash fa-sm"></i>
                                        Delete
                                    </button>
                                </div>
                            </div>
                            <div class="p-6">
                                <?php if ($graph['graph_type'] === 'bar'): ?>
                                    <!-- Bar Chart Only -->
                                    <div class="chart-container">
                                        <canvas id="chart-<?= $index ?>"></canvas>
                                    </div>
                                <?php else: ?>
                                    <!-- Pie Chart with Table -->
                                    <div class="flex flex-col md:flex-row gap-4">
                                        <div class="w-full md:w-1/2">
                                            <div class="table-responsive">
                                                <table class="pie-chart-table w-full divide-y divide-gray-200 text-sm">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th class="py-3 px-4 text-center font-medium text-gray-500 uppercase">Category</th>
                                                            <th class="py-3 px-4 text-center font-medium text-gray-500 uppercase">Count</th>
                                                            <th class="py-3 px-4 text-center font-medium text-gray-500 uppercase">%</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        <?php 
                                                        $total = array_sum(array_column($graph['data'], 'value'));
                                                        foreach ($graph['data'] as $item): 
                                                            $percentage = round(($item['value'] / $total) * 100, 2);
                                                        ?>
                                                            <tr class="hover:bg-gray-50">
                                                                <td class="py-3 px-4 font-medium text-gray-900 text-center break-words max-w-[100px]" title="<?= htmlspecialchars($item['label'] ?? '') ?>"><?= htmlspecialchars($item['label'] ?? '') ?></td>
                                                                <td class="py-3 px-4 text-gray-500 text-center"><?= htmlspecialchars($item['value'] ?? '') ?></td>
                                                                <td class="py-3 px-4 text-gray-500 text-center"><?= $percentage ?>%</td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="w-full md:w-1/2">
                                            <div class="chart-container">
                                                <canvas id="chart-<?= $index ?>"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-archive fa-3x mb-4"></i>
                    <p class="text-lg">No archived graphs</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- File View Modals for Announcements -->
    <?php foreach ($processed_announcements as $index => $pdf): ?>
        <div id="file-modal-announcement-<?= $index ?>" class="file-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title"><?= htmlspecialchars($pdf['description']) ?></h3>
                    <span class="modal-close" onclick="closeFileModal('announcement', <?= $index ?>)">&times;</span>
                </div>
                <div class="modal-body">
                    <div id="pdfContainer-announcement-<?= $index ?>" class="pdf-container">
                        <div class="loading-spinner"></div>
                        <p class="text-center text-gray-600">Loading announcement...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="modal-meta">
                        Posted on: <?= date('F j, Y', strtotime($pdf['posted_on'])) ?> | File: <?= basename($pdf['file_path']) ?>
                    </div>
                    <div class="page-navigation">
                        <button id="prevPageBtn-announcement-<?= $index ?>" class="page-nav-btn" disabled>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div id="pageIndicator-announcement-<?= $index ?>" class="page-indicator">Page 1 of 1</div>
                        <button id="nextPageBtn-announcement-<?= $index ?>" class="page-nav-btn" disabled>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <!-- File View Modals for Memos -->
    <?php foreach ($processed_memos as $index => $pdf): ?>
        <div id="file-modal-memo-<?= $index ?>" class="file-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title"><?= htmlspecialchars($pdf['description']) ?></h3>
                    <span class="modal-close" onclick="closeFileModal('memo', <?= $index ?>)">&times;</span>
                </div>
                <div class="modal-body">
                    <div id="pdfContainer-memo-<?= $index ?>" class="pdf-container">
                        <div class="loading-spinner"></div>
                        <p class="text-center text-gray-600">Loading memo...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="modal-meta">
                        Posted on: <?= date('F j, Y', strtotime($pdf['posted_on'])) ?> | File: <?= basename($pdf['file_path']) ?>
                    </div>
                    <div class="page-navigation">
                        <button id="prevPageBtn-memo-<?= $index ?>" class="page-nav-btn" disabled>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div id="pageIndicator-memo-<?= $index ?>" class="page-indicator">Page 1 of 1</div>
                        <button id="nextPageBtn-memo-<?= $index ?>" class="page-nav-btn" disabled>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <!-- Delete Confirmation Modal -->
    <div id="archive-delete-modal" class="archive-delete-modal">
        <div class="archive-delete-modal-content">
            <div class="archive-delete-modal-header">
                <h3 class="archive-delete-modal-title">Confirm Deletion</h3>
                <span class="archive-delete-modal-close">&times;</span>
            </div>
            <div class="archive-delete-modal-body">
                <p>Are you sure you want to permanently delete this item? This action cannot be undone.</p>
                
            </div>
            <div class="archive-delete-modal-footer">
                <button class="btn-cancel" id="archive-cancel-btn">Cancel</button>
                <button class="btn-delete" id="archive-confirm-delete-btn">
                    <i class="fas fa-trash mr-2"></i> Delete
                </button>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';
        
        document.addEventListener('DOMContentLoaded', function() {
            // PDF handling code
            const pdfDocs = {};
            const currentPageNum = {};
            const totalPages = {};
            
            // Render PDF previews for announcements
            function renderPdfPreviews(pdfs, type) {
                pdfs.forEach((pdf, index) => {
                    // Render first page as preview
                    pdfjsLib.getDocument(pdf.file_path).promise.then(function(pdfDoc) {
                        return pdfDoc.getPage(1);
                    }).then(function(page) {
                        const container = document.getElementById(`file-preview-${type}-${index}`);
                        const scale = 0.5;
                        const viewport = page.getViewport({ scale: scale });
                        const canvas = document.createElement("canvas");
                        const context = canvas.getContext("2d");
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;
                        container.innerHTML = "";
                        container.appendChild(canvas);
                        const renderContext = {
                            canvasContext: context,
                            viewport: viewport
                        };
                        page.render(renderContext);
                    }).catch(function(error) {
                        console.error('PDF preview error:', error);
                        document.getElementById(`file-preview-${type}-${index}`).innerHTML =
                            '<div class="text-red-500 p-2 text-center">Could not load PDF preview</div>';
                    });
                    
                    // Set up view buttons for each file
                    const viewButton = document.getElementById(`view-full-${type}-${index}`);
                    if (viewButton) {
                        viewButton.addEventListener("click", function() {
                            const modal = document.getElementById(`file-modal-${type}-${index}`);
                            const container = document.getElementById(`pdfContainer-${type}-${index}`);
                            const fileType = this.getAttribute('data-file-type');
                            const filePath = this.getAttribute('data-file-path');
                            
                            // Set modal to highest z-index
                            modal.classList.add('modal-active');
                            modal.style.display = "block";
                            
                            // Load PDF
                            pdfjsLib.getDocument(filePath).promise.then(pdfDoc => {
                                pdfDocs[`${type}-${index}`] = pdfDoc;
                                totalPages[`${type}-${index}`] = pdfDoc.numPages;
                                currentPageNum[`${type}-${index}`] = 1;
                                
                                // Update page indicator
                                const pageIndicator = document.getElementById(`pageIndicator-${type}-${index}`);
                                if (pageIndicator) {
                                    pageIndicator.textContent = `Page 1 of ${totalPages[`${type}-${index}`]}`;
                                }
                                
                                // Enable/disable navigation buttons
                                const prevBtn = document.getElementById(`prevPageBtn-${type}-${index}`);
                                const nextBtn = document.getElementById(`nextPageBtn-${type}-${index}`);
                                if (prevBtn) prevBtn.disabled = true;
                                if (nextBtn) nextBtn.disabled = totalPages[`${type}-${index}`] <= 1;
                                
                                // Render first page
                                renderPage(type, index, 1);
                            }).catch(error => {
                                console.error('Error loading PDF:', error);
                                container.innerHTML = `
                                    <div class="text-center py-8">
                                        <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
                                        <p class="text-lg text-gray-700 mb-2">Failed to load document</p>
                                        <p class="text-gray-600 mb-4">${error.message}</p>
                                    </div>
                                `;
                            });
                        });
                    }
                    
                    // Page navigation functions for this PDF
                    const prevBtn = document.getElementById(`prevPageBtn-${type}-${index}`);
                    const nextBtn = document.getElementById(`nextPageBtn-${type}-${index}`);
                    
                    if (prevBtn) {
                        prevBtn.addEventListener('click', function() {
                            goToPrevPage(type, index);
                        });
                    }
                    
                    if (nextBtn) {
                        nextBtn.addEventListener('click', function() {
                            goToNextPage(type, index);
                        });
                    }
                    
                    // Close modal when clicking outside content
                    const modal = document.getElementById(`file-modal-${type}-${index}`);
                    if (modal) {
                        modal.addEventListener("click", function(e) {
                            if (e.target === this) {
                                closeFileModal(type, index);
                            }
                        });
                    }
                });
            }
            
            // Function to render a page
            function renderPage(type, index, pageNum) {
                const key = `${type}-${index}`;
                if (!pdfDocs[key]) return;
                
                const container = document.getElementById(`pdfContainer-${type}-${index}`);
                const modalBody = document.querySelector(`#file-modal-${type}-${index} .modal-body`);
                
                // Show loading indicator
                container.innerHTML = `
                    <div class="loading-spinner"></div>
                    <p class="text-center text-gray-600">Loading page ${pageNum}...</p>
                `;
                
                pdfDocs[key].getPage(pageNum).then(page => {
                    // Get the dimensions of the modal body
                    const modalRect = modalBody.getBoundingClientRect();
                    const availableWidth = modalRect.width;
                    const availableHeight = modalRect.height;
                    
                    // Get the viewport at a readable scale (1.5)
                    const viewport = page.getViewport({ scale: 1.5 });
                    
                    // Calculate the scale needed to fit the width
                    const widthScale = availableWidth / viewport.width;
                    // Calculate the scale needed to fit the height
                    const heightScale = availableHeight / viewport.height;
                    
                    // Use the smaller scale to ensure the entire page fits
                    let scale = Math.min(widthScale, heightScale);
                    
                    // Ensure the scale is not too small (minimum 0.8)
                    scale = Math.max(scale, 0.8);
                    
                    // Get the scaled viewport
                    const scaledViewport = page.getViewport({ scale });
                    
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.height = scaledViewport.height;
                    canvas.width = scaledViewport.width;
                    canvas.className = 'pdf-page';
                    
                    // Clear container and add canvas
                    container.innerHTML = '';
                    container.appendChild(canvas);
                    
                    return page.render({ canvasContext: context, viewport: scaledViewport }).promise;
                }).catch(error => {
                    console.error('Error rendering page:', error);
                    container.innerHTML = `
                        <div class="text-center py-8">
                            <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
                            <p class="text-lg text-gray-700 mb-2">Failed to load page ${pageNum}</p>
                            <p class="text-gray-600">${error.message}</p>
                        </div>
                    `;
                });
            }
            
            // Function to go to previous page
            function goToPrevPage(type, index) {
                const key = `${type}-${index}`;
                if (!pdfDocs[key]) return;
                
                if (currentPageNum[key] > 1) {
                    currentPageNum[key]--;
                    renderPage(type, index, currentPageNum[key]);
                    
                    // Update navigation
                    const prevBtn = document.getElementById(`prevPageBtn-${type}-${index}`);
                    const nextBtn = document.getElementById(`nextPageBtn-${type}-${index}`);
                    const pageIndicator = document.getElementById(`pageIndicator-${type}-${index}`);
                    
                    if (prevBtn) prevBtn.disabled = currentPageNum[key] === 1;
                    if (nextBtn) nextBtn.disabled = false;
                    if (pageIndicator) pageIndicator.textContent = `Page ${currentPageNum[key]} of ${totalPages[key]}`;
                }
            }
            
            // Function to go to next page
            function goToNextPage(type, index) {
                const key = `${type}-${index}`;
                if (!pdfDocs[key]) return;
                
                if (currentPageNum[key] < totalPages[key]) {
                    currentPageNum[key]++;
                    renderPage(type, index, currentPageNum[key]);
                    
                    // Update navigation
                    const prevBtn = document.getElementById(`prevPageBtn-${type}-${index}`);
                    const nextBtn = document.getElementById(`nextPageBtn-${type}-${index}`);
                    const pageIndicator = document.getElementById(`pageIndicator-${type}-${index}`);
                    
                    if (prevBtn) prevBtn.disabled = false;
                    if (nextBtn) nextBtn.disabled = currentPageNum[key] === totalPages[key];
                    if (pageIndicator) pageIndicator.textContent = `Page ${currentPageNum[key]} of ${totalPages[key]}`;
                }
            }
            
            // Render PDFs for each type
            renderPdfPreviews(<?= json_encode($processed_announcements) ?>, 'announcement');
            renderPdfPreviews(<?= json_encode($processed_memos) ?>, 'memo');
            
            // Initialize charts for graph groups
            <?php foreach ($graph_groups as $group_title => $group): ?>
                <?php foreach ($group['graphs'] as $index => $graph): ?>
                    const ctxGroup<?= md5($group_title) ?><?= $index ?> = document.getElementById('chart-group-<?= md5($group_title) ?>-<?= $index ?>');
                    if (ctxGroup<?= md5($group_title) ?><?= $index ?>) {
                        <?php 
                        $isBarChart = $graph['graph_type'] === 'bar';
                        if ($isBarChart): ?>
                            new Chart(ctxGroup<?= md5($group_title) ?><?= $index ?>, {
                                type: 'bar',
                                data: {
                                    labels: <?= json_encode(array_column($graph['data'], 'category')) ?>,
                                    datasets: [
                                        {
                                            label: 'Series 1',
                                            data: <?= json_encode(array_column($graph['data'], 'series1')) ?>,
                                            backgroundColor: '#60A5FA',
                                            borderWidth: 2,
                                            borderColor: '#ffffff'
                                        },
                                        {
                                            label: 'Series 2',
                                            data: <?= json_encode(array_column($graph['data'], 'series2')) ?>,
                                            backgroundColor: '#34D399',
                                            borderWidth: 2,
                                            borderColor: '#ffffff'
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        }
                                    },
                                    plugins: {
                                        legend: {
                                            position: 'top',
                                            labels: {
                                                boxWidth: 15,
                                                padding: 15,
                                                font: {
                                                    size: 12
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        <?php else: ?>
                            new Chart(ctxGroup<?= md5($group_title) ?><?= $index ?>, {
                                type: 'doughnut',
                                data: {
                                    labels: <?= json_encode(array_column($graph['data'], 'label')) ?>,
                                    datasets: [{
                                        data: <?= json_encode(array_column($graph['data'], 'value')) ?>,
                                        backgroundColor: [
                                            '#60A5FA', '#34D399', '#FBBF24', '#F87171', '#A78BFA', '#F472B6', '#4ADE80'
                                        ],
                                        borderWidth: 2,
                                        borderColor: '#ffffff'
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    cutout: '65%',
                                    plugins: {
                                        datalabels: {
                                            color: '#111827',
                                            formatter: (value, ctx) => {
                                                const sum = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                                return ((value / sum) * 100).toFixed(1) + "%";
                                            },
                                            anchor: 'center',
                                            align: 'center',
                                            offset: 0,
                                            textAlign: 'center',
                                            font: {
                                                weight: 'bold',
                                                size: 12
                                            }
                                        },
                                        legend: {
                                            position: 'bottom',
                                            labels: {
                                                boxWidth: 15,
                                                padding: 15,
                                                font: {
                                                    size: 12
                                                }
                                            }
                                        }
                                    }
                                },
                                plugins: [ChartDataLabels]
                            });
                        <?php endif; ?>
                    }
                <?php endforeach; ?>
            <?php endforeach; ?>
            
            // Initialize charts for individual graphs
            <?php foreach ($archived_graphs as $index => $graph): ?>
                const ctx<?= $index ?> = document.getElementById('chart-<?= $index ?>');
                if (ctx<?= $index ?>) {
                    <?php 
                    $isBarChart = $graph['graph_type'] === 'bar';
                    if ($isBarChart): ?>
                        new Chart(ctx<?= $index ?>, {
                            type: 'bar',
                            data: {
                                labels: <?= json_encode(array_column($graph['data'], 'category')) ?>,
                                datasets: [
                                    {
                                        label: 'Series 1',
                                        data: <?= json_encode(array_column($graph['data'], 'series1')) ?>,
                                        backgroundColor: '#60A5FA',
                                        borderWidth: 2,
                                        borderColor: '#ffffff'
                                    },
                                    {
                                        label: 'Series 2',
                                        data: <?= json_encode(array_column($graph['data'], 'series2')) ?>,
                                        backgroundColor: '#34D399',
                                        borderWidth: 2,
                                        borderColor: '#ffffff'
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                },
                                plugins: {
                                    legend: {
                                        position: 'top',
                                        labels: {
                                            boxWidth: 15,
                                            padding: 15,
                                            font: {
                                                size: 12
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    <?php else: ?>
                        new Chart(ctx<?= $index ?>, {
                            type: 'doughnut',
                            data: {
                                labels: <?= json_encode(array_column($graph['data'], 'label')) ?>,
                                datasets: [{
                                    data: <?= json_encode(array_column($graph['data'], 'value')) ?>,
                                    backgroundColor: [
                                        '#60A5FA', '#34D399', '#FBBF24', '#F87171', '#A78BFA', '#F472B6', '#4ADE80'
                                    ],
                                    borderWidth: 2,
                                    borderColor: '#ffffff'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '65%',
                                plugins: {
                                    datalabels: {
                                        color: '#111827',
                                        formatter: (value, ctx) => {
                                            const sum = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                            return ((value / sum) * 100).toFixed(1) + "%";
                                        },
                                        anchor: 'center',
                                        align: 'center',
                                        offset: 0,
                                        textAlign: 'center',
                                        font: {
                                            weight: 'bold',
                                            size: 12
                                        }
                                    },
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            boxWidth: 15,
                                            padding: 15,
                                            font: {
                                                size: 12
                                            }
                                        }
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });
                    <?php endif; ?>
                }
            <?php endforeach; ?>
            
            // Show notification function
            function showNotification(message, type = 'success') {
                const notification = document.getElementById('notification');
                notification.textContent = message;
                notification.className = 'notification ' + type;
                notification.classList.add('show');
                
                setTimeout(() => {
                    notification.classList.remove('show');
                }, 3000);
            }
            
            // Delete button functionality
            let deleteId = null;
            let deleteType = null;
            
            document.querySelectorAll('.archive-delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const type = this.getAttribute('data-type');
                    
                    deleteId = id;
                    deleteType = type;
                    
                    // Set the item title in the delete confirmation modal
                    const deleteTitle = document.getElementById('archive-delete-title');
                    if (deleteTitle) {
                        if (type === 'graph-group') {
                            deleteTitle.textContent = `Graph Group: ${id.split(',').length} graphs`;
                        } else {
                            // Just show the type without ID number
                            deleteTitle.textContent = `${type.charAt(0).toUpperCase() + type.slice(1)}`;
                        }
                    }
                    
                    // Show the delete confirmation modal
                    const deleteModal = document.getElementById('archive-delete-modal');
                    if (deleteModal) {
                        deleteModal.style.display = 'flex';
                    }
                });
            });
            
            // Confirm delete button
            const confirmDeleteBtn = document.getElementById('archive-confirm-delete-btn');
            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', function() {
                    if (deleteId) {
                        // Disable the button to prevent multiple clicks
                        this.disabled = true;
                        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Deleting...';
                        
                        let url, data;
                        if (deleteType === 'graph-group') {
                            // For group deletion, we use the graph delete endpoint with group flag
                            url = 'DIT_Delete_Graph.php';
                            data = 'isGroup=1&graphIds[]=' + deleteId.split(',').join('&graphIds[]=');
                        } else {
                            // For individual items
                            url = `DIT_Delete_${deleteType.charAt(0).toUpperCase() + deleteType.slice(1)}.php`;
                            data = 'id=' + encodeURIComponent(deleteId);
                        }
                        
                        // Send the delete request to the server
                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: data
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification(`${deleteType === 'graph-group' ? 'Graph group' : (deleteType.charAt(0).toUpperCase() + deleteType.slice(1))} deleted successfully!`);
                                // Hide modal and reload the page
                                closeArchiveDeleteModal();
                                setTimeout(() => {
                                    location.reload();
                                }, 1500);
                            } else {
                                showNotification(`Error deleting ${deleteType === 'graph-group' ? 'graph group' : deleteType}: ${data.message}`, 'error');
                                // Re-enable the button
                                document.getElementById('archive-confirm-delete-btn').disabled = false;
                                document.getElementById('archive-confirm-delete-btn').innerHTML = '<i class="fas fa-trash mr-2"></i> Delete';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification(`An error occurred while deleting the ${deleteType === 'graph-group' ? 'graph group' : deleteType}`, 'error');
                            // Re-enable the button
                            document.getElementById('archive-confirm-delete-btn').disabled = false;
                            document.getElementById('archive-confirm-delete-btn').innerHTML = '<i class="fas fa-trash mr-2"></i> Delete';
                        });
                    }
                });
            }
            
            // Fix for the close button in the delete confirmation modal
            const closeDeleteModalBtn = document.querySelector('.archive-delete-modal-close');
            if (closeDeleteModalBtn) {
                closeDeleteModalBtn.addEventListener('click', closeArchiveDeleteModal);
            }
            
            // Fix for the cancel button in the delete confirmation modal
            const cancelDeleteBtn = document.getElementById('archive-cancel-btn');
            if (cancelDeleteBtn) {
                cancelDeleteBtn.addEventListener('click', closeArchiveDeleteModal);
            }
            
            // Also close when clicking outside the modal
            const deleteModal = document.getElementById('archive-delete-modal');
            if (deleteModal) {
                deleteModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeArchiveDeleteModal();
                    }
                });
            }
            
            // Global functions
            function closeFileModal(type, index) {
                const modal = document.getElementById(`file-modal-${type}-${index}`);
                if (modal) {
                    modal.classList.remove('modal-active');
                    modal.style.display = "none";
                }
            }
            
            function closeArchiveDeleteModal() {
                const modal = document.getElementById('archive-delete-modal');
                if (modal) {
                    modal.style.display = 'none';
                }
                // Reset delete variables
                deleteId = null;
                deleteType = null;
            }
        });
    </script>
</body>
</html>