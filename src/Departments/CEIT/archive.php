<?php
include "../../db.php";
// Create archive directory if it doesn't exist
 $archiveDir = 'uploads/archive';
if (!file_exists($archiveDir)) {
    mkdir($archiveDir, 0777, true);
}
// Get archived announcements
 $announcementQuery = "SELECT * FROM Archive WHERE type = 'announcement' ORDER BY archived_at DESC";
 $announcementResult = $conn->query($announcementQuery);
 $announcements = [];
while ($row = $announcementResult->fetch_assoc()) {
    $announcements[] = $row;
}
// Get archived graphs
 $graphQuery = "SELECT * FROM Archive WHERE type = 'graph' ORDER BY archived_at DESC";
 $graphResult = $conn->query($graphQuery);
 $graphs = [];
while ($row = $graphResult->fetch_assoc()) {
    $data = json_decode($row['data'], true);
    // Make sure we have the correct data structure
    if (isset($data['type']) && isset($data['original_data'])) {
        $graphs[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'type' => $data['type'],
            'data' => $data['original_data'],
            'file_path' => $row['file_path'],
            'archived_at' => $row['archived_at']
        ];
    } else {
        // Handle legacy data structure
        $graphs[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'type' => isset($data['type']) ? $data['type'] : 'pie',
            'data' => $data,
            'file_path' => $row['file_path'],
            'archived_at' => $row['archived_at']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEIT Archive</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
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
            border-bottom: 2px solid #f97316;
            color: #f97316;
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
            position: relative;
            cursor: pointer;
        }
        .thumbnail-preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .file-icon-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 3rem;
            color: rgba(255, 255, 255, 0.7);
            z-index: 1;
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
            background-color: #f97316;
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
            background-color: #f97316;
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
            background-color: #ea580c;
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
            border-top: 4px solid #f97316;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .file-icon {
            font-size: 4rem;
        }
        .file-icon.pdf {
            color: #dc2626;
        }
        .file-icon.doc, .file-icon.docx, .file-icon.wps {
            color: #2563eb;
        }
        .file-icon.xls, .file-icon.xlsx {
            color: #16a34a;
        }
        .file-icon.ppt, .file-icon.pptx {
            color: #ea580c;
        }
        .file-icon.jpg, .file-icon.jpeg, .file-icon.png, .file-icon.gif {
            color: #8b5cf6;
        }
        .file-icon.default {
            color: #6b7280;
        }
        .image-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background-color: #000;
        }
        .image-viewer {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: block;
        }
        .office-viewer {
            border: none;
            border-radius: 0;
            overflow: hidden;
            width: 100%;
            height: 100%;
            background-color: #fff;
        }
        .delete-archive-modal {
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
        .delete-archive-modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
        }
        .delete-archive-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .delete-archive-modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #dc2626;
        }
        .delete-archive-modal-close {
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
        }
        .delete-archive-modal-close:hover {
            color: #dc2626;
        }
        .delete-archive-modal-body {
            margin-bottom: 20px;
        }
        .delete-archive-modal-footer {
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
        /* Ensure modals are on top when opened */
        .modal-active {
            z-index: 1001 !important;
        }
        .pdf-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* DIT_Archive Styles - Applied to CEIT */
        .bg-white.shadow-md.rounded-lg.p-4.w-full.h-full.flex.flex-col.justify-between.border.border-blue-500.transition.duration-200.transform.hover\:scale-105 {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            border: 1px solid #f97316;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1rem;
        }
        .bg-white.shadow-md.rounded-lg.p-4.w-full.h-full.flex.flex-col.justify-between.border.border-blue-500.transition.duration-200.transform.hover\:scale-105:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .border-b.border-gray-200.bg-gray-50.px-6.py-4.flex.flex-col.sm\:flex-row.justify-between.items-center {
            border-bottom: 1px solid #e5e7eb;
            background-color: #f9fafb;
            padding: 1rem 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        @media (min-width: 640px) {
            .border-b.border-gray-200.bg-gray-50.px-6.py-4.flex.flex-col.sm\:flex-row.justify-between.items-center {
                flex-direction: row;
                align-items: center;
            }
        }
        .text-xl.font-semibold.text-gray-800.mb-2.sm\:mb-0 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        @media (min-width: 640px) {
            .text-xl.font-semibold.text-gray-800.mb-2.sm\:mb-0 {
                margin-bottom: 0;
            }
        }
        .p-6 {
            padding: 1.5rem;
        }
        .flex.space-x-2 {
            display: flex;
            gap: 0.5rem;
        }
        .p-2.border.rounded.border-gray-500.text-gray-500.hover\:bg-gray-500.hover\:text-white.transition.duration-200.transform.hover\:scale-110 {
            padding: 0.5rem;
            border: 1px solid #6b7280;
            border-radius: 0.25rem;
            color: #6b7280;
            transition: background-color 0.2s, color 0.2s, transform 0.2s;
        }
        .p-2.border.rounded.border-gray-500.text-gray-500.hover\:bg-gray-500.hover\:text-white.transition.duration-200.transform.hover\:scale-110:hover {
            background-color: #6b7280;
            color: white;
            transform: scale(1.1);
        }
        .p-2.border.border-red-500.text-red-500.rounded.hover\:bg-red-500.hover\:text-white.transition.duration-200.transform.hover\:scale-110 {
            padding: 0.5rem;
            border: 1px solid #ef4444;
            border-radius: 0.25rem;
            color: #ef4444;
            transition: background-color 0.2s, color 0.2s, transform 0.2s;
        }
        .p-2.border.border-red-500.text-red-500.rounded.hover\:bg-red-500.hover\:text-white.transition.duration-200.transform.hover\:scale-110:hover {
            background-color: #ef4444;
            color: white;
            transform: scale(1.1);
        }
        .mb-3.border.border-gray-300.rounded {
            margin-bottom: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
        }
        .card-body.flex-grow {
            flex-grow: 1;
        }
        .file-title.font-semibold.text-gray-800.text-lg.mb-1.truncate {
            font-weight: 600;
            color: #1f2937;
            font-size: 1.125rem;
            margin-bottom: 0.25rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .card-text.text-gray-600.text-sm.truncate {
            color: #4b5563;
            font-size: 0.875rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .text-xs.text-gray-500.mt-1 {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        .flex.justify-end.mt-4.space-x-2 {
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
            gap: 0.5rem;
        }
        .grid.grid-cols-1.sm\:grid-cols-2.md\:grid-cols-3.lg\:grid-cols-4.gap-6 {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.5rem;
        }
        @media (min-width: 640px) {
            .grid.grid-cols-1.sm\:grid-cols-2.md\:grid-cols-3.lg\:grid-cols-4.gap-6 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 768px) {
            .grid.grid-cols-1.sm\:grid-cols-2.md\:grid-cols-3.lg\:grid-cols-4.gap-6 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .grid.grid-cols-1.sm\:grid-cols-2.md\:grid-cols-3.lg\:grid-cols-4.gap-6 {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        .bg-white.shadow-md.rounded-lg.p-4.w-full.border.border-blue-500.transition.duration-200.transform.hover\:scale-105.graph-card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            border: 1px solid #f97316;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            width: 100%;
            padding: 1rem;
        }
        .bg-white.shadow-md.rounded-lg.p-4.w-full.border.border-blue-500.transition.duration-200.transform.hover\:scale-105.graph-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .flex.flex-col.md\:flex-row.gap-4 {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .flex.flex-col.md\:flex-row.gap-4 {
                flex-direction: row;
            }
        }
        .w-full.md\:w-1\/2 {
            width: 100%;
        }
        @media (min-width: 768px) {
            .w-full.md\:w-1\/2 {
                width: 50%;
            }
        }
        .overflow-x-auto {
            overflow-x: auto;
        }
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
        
        /* Properly sized chart containers */
        .chart-container {
            position: relative;
            height: 350px;
            width: 100%;
            margin: 0 auto;
            box-sizing: border-box;
        }
        .bar-chart-container {
            position: relative;
            width: 100%;
            height: 350px;
            margin: 0 auto;
            box-sizing: border-box;
            padding: 10px;
        }
        .graph-card-body {
            min-height: 350px;
            padding: 10px;
            box-sizing: border-box;
        }
        .announcement-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .announcement-card-body {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .announcement-card-footer {
            margin-top: auto;
        }
        .chart-canvas {
            width: 100% !important;
            height: 100% !important;
        }
        .bar-chart-wrapper {
            width: 100%;
            height: 100%;
            position: relative;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-orange-600 mb-4 md:mb-0">CEIT Archive</h1>
            <a href="CEIT.php" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>
        
        <!-- Announcements Archive -->
        <div class="archive-section">
            <h2 class="archive-title">Archived Announcements</h2>
            <?php if (empty($announcements)): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                No archived announcements found.
                            </p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="bg-white shadow-md rounded-lg p-4 w-full h-full flex flex-col justify-between border border-orange-500 transition duration-200 transform hover:scale-105 announcement-card">
                            <div class="mb-3 border border-gray-300 rounded">
                                <div id="file-preview-announcement-<?= $announcement['id'] ?>" class="file-preview">
                                    <?php
                                    $fileExtension = strtolower(pathinfo($announcement['file_path'], PATHINFO_EXTENSION));
                                    $filePath = $announcement['file_path'];
                                    
                                    // Make sure we have the correct path for archived files
                                    if (!strpos($filePath, 'uploads/')) {
                                        $filePath = 'uploads/' . $filePath;
                                    }
                                    
                                    if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])):
                                    ?>
                                        <img src="<?= htmlspecialchars($filePath) ?>" alt="Preview" class="thumbnail-preview">
                                    <?php elseif ($fileExtension === 'pdf'): ?>
                                        <div class="pdf-thumbnail-container w-full h-full" id="pdf-thumb-<?= $announcement['id'] ?>">
                                            <div class="loading-spinner"></div>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-full h-full flex flex-col items-center justify-center bg-gray-100">
                                            <i class="file-icon <?= in_array($fileExtension, ['doc', 'docx', 'wps']) ? 'doc' : (in_array($fileExtension, ['xls', 'xlsx']) ? 'xls' : (in_array($fileExtension, ['ppt', 'pptx']) ? 'ppt' : 'default')) ?> fa-<?= in_array($fileExtension, ['doc', 'docx', 'wps']) ? 'file-word' : (in_array($fileExtension, ['xls', 'xlsx']) ? 'file-excel' : (in_array($fileExtension, ['ppt', 'pptx']) ? 'file-powerpoint' : 'file')) ?>"></i>
                                            <span class="mt-2 text-sm text-gray-600"><?= strtoupper($fileExtension) ?> File</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body flex-grow announcement-card-body">
                                <div class="file-title font-semibold text-gray-800 text-lg mb-1 truncate">
                                    <?= htmlspecialchars(substr($announcement['title'], 0, 50)) ?><?php echo strlen($announcement['title']) > 50 ? '...' : ''; ?>
                                </div>
                                <p class="card-text text-gray-600 text-sm truncate">
                                    <?= htmlspecialchars(substr(strip_tags($announcement['content']), 0, 100)) ?><?php echo strlen(strip_tags($announcement['content'])) > 100 ? '...' : ''; ?>
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Posted on: <?= date('F j, Y', strtotime($announcement['archived_at'])) ?>
                                </p>
                            </div>
                            <div class="flex justify-end mt-4 space-x-2 announcement-card-footer">
                                <button onclick="openAnnouncementModal('<?= htmlspecialchars($announcement['file_path']) ?>', '<?= htmlspecialchars($announcement['title']) ?>', '<?= date('M j, Y', strtotime($announcement['archived_at'])) ?>')" class="p-2 border rounded border-gray-500 text-gray-500 hover:bg-gray-500 hover:text-white transition duration-200 transform hover:scale-110" title="View Full Document" data-file-type="pdf" data-file-path="<?= $announcement['file_path'] ?>"> 
                                    <i class="fas fa-eye fa-sm"></i>
                                    View
                                </button>
                                <button onclick="confirmDeleteArchive(<?= $announcement['id'] ?>, 'announcement')" class="p-2 border border-red-500 text-red-500 rounded hover:bg-red-500 hover:text-white transition duration-200 transform hover:scale-110 archive-delete-btn" data-id="<?= $announcement['id'] ?>" data-type="announcement" title="Delete">
                                    <i class="fas fa-trash fa-sm"></i>
                                    Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Graphs Archive -->
        <div class="archive-section">
            <h2 class="archive-title">Archived Graphs</h2>
            <?php if (empty($graphs)): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                No archived graphs found.
                            </p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-6">
                    <?php foreach ($graphs as $graph): ?>
                        <div class="bg-white shadow-md rounded-lg p-4 w-full border border-orange-500 transition duration-200 transform hover:scale-105 graph-card">
                            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 flex flex-col sm:flex-row justify-between items-center">
                                <h2 class="text-xl font-semibold text-gray-800 mb-2 sm:mb-0"><?= htmlspecialchars($graph['title']) ?></h2>
                                <div class="flex space-x-2">
                                    <button onclick="confirmDeleteArchive(<?= $graph['id'] ?>, 'graph')" class="p-2 border border-red-500 text-red-500 rounded hover:bg-red-500 hover:text-white transition duration-200 transform hover:scale-110 archive-delete-btn" data-id="<?= $graph['id'] ?>" data-type="graph" title="Delete">
                                        <i class="fas fa-trash fa-sm"></i>
                                        Delete
                                    </button>
                                </div>
                            </div>
                            <div class="p-6 graph-card-body">
                                <?php if ($graph['type'] === 'pie'): ?>
                                    <div class="flex flex-col md:flex-row gap-4">
                                        <!-- Table Section -->
                                        <div class="w-full md:w-1/2">
                                            <div class="overflow-x-auto">
                                                <table class="pie-chart-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Label</th>
                                                            <th>Value</th>
                                                            <th>%</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php 
                                                        // For pie charts, we need label and value
                                                        $pieData = [];
                                                        if (isset($graph['data'][0]['label']) && isset($graph['data'][0]['value'])) {
                                                            $pieData = $graph['data'];
                                                        } else {
                                                            // Try to extract label and value from any structure
                                                            foreach ($graph['data'] as $item) {
                                                                if (is_array($item) && isset($item['label']) && isset($item['value'])) {
                                                                    $pieData[] = $item;
                                                                }
                                                            }
                                                        }
                                                        
                                                        $total = array_sum(array_column($pieData, 'value'));
                                                        
                                                        foreach ($pieData as $item): 
                                                            $percentage = round(($item['value'] / $total) * 100, 2);
                                                        ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($item['label']) ?></td>
                                                                <td><?= $item['value'] ?></td>
                                                                <td><?= $percentage ?>%</td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        
                                        <!-- Chart Section -->
                                        <div class="w-full md:w-1/2">
                                            <div class="chart-container">
                                                <canvas id="graph<?= $graph['id'] ?>" class="chart-canvas"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Bar Chart Section - Properly sized -->
                                    <div class="bar-chart-container">
                                        <div class="bar-chart-wrapper">
                                            <canvas id="graph<?= $graph['id'] ?>" class="chart-canvas"></canvas>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                                <span class="text-xs text-gray-500">Archived: <?= date('M j, Y', strtotime($graph['archived_at'])) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Announcement Modal -->
    <div id="announcementModal" class="file-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Announcement</h3>
                <span class="modal-close" onclick="closeAnnouncementModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="pdfContainer" class="pdf-container">
                    <div class="loading-spinner"></div>
                    <p class="text-center text-gray-600">Loading announcement...</p>
                </div>
            </div>
            <div class="modal-footer">
                <div class="modal-meta" id="modalMeta"></div>
                <div class="page-navigation">
                    <button id="prevPageBtn" class="page-nav-btn" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="pageIndicator" class="page-indicator">Page 1 of 1</div>
                    <button id="nextPageBtn" class="page-nav-btn" disabled>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="delete-archive-modal" class="delete-archive-modal">
        <div class="delete-archive-modal-content">
            <div class="delete-archive-modal-header">
                <h3 class="delete-archive-modal-title">Confirm Delete</h3>
                <span class="delete-archive-modal-close" id="delete-close-btn">&times;</span>
            </div>
            <div class="delete-archive-modal-body">
                <p class="text-gray-600 mb-6">Are you sure you want to delete this archived item? This action cannot be undone.</p>
            </div>
            <div class="delete-archive-modal-footer">
                <button id="cancel-delete-btn" class="btn-cancel">Cancel</button>
                <button id="confirm-archive-delete-btn" class="btn-delete">Delete</button>
            </div>
        </div>
    </div>
    
    <script>
        // Initialize PDF.js worker
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';
        
        // Store the item to delete
        let archiveToDelete = null;
        
        // Function to confirm delete
        function confirmDeleteArchive(id, type) {
            archiveToDelete = { id: id, type: type };
            document.getElementById('delete-archive-modal').style.display = 'flex';
        }
        
        // Function to close delete modal
        function closeDeleteArchiveModal() {
            document.getElementById('delete-archive-modal').style.display = 'none';
            archiveToDelete = null;
        }
        
        // Delete button click handler
        document.getElementById('confirm-archive-delete-btn').addEventListener('click', function() {
            if (archiveToDelete) {
                // Disable the button to prevent multiple clicks
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Deleting...';
                
                // Send the delete request to the server
                fetch('delete_archive.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + encodeURIComponent(archiveToDelete.id) + '&type=' + encodeURIComponent(archiveToDelete.type)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hide modal and reload the page
                        closeDeleteArchiveModal();
                        location.reload();
                    } else {
                        alert('Error deleting archive item: ' + data.message);
                        // Re-enable the button
                        document.getElementById('confirm-archive-delete-btn').disabled = false;
                        document.getElementById('confirm-archive-delete-btn').innerHTML = 'Delete';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the archive item');
                    // Re-enable the button
                    document.getElementById('confirm-archive-delete-btn').disabled = false;
                    document.getElementById('confirm-archive-delete-btn').innerHTML = 'Delete';
                });
            }
        });
        
        // Cancel delete button
        document.getElementById('cancel-delete-btn').addEventListener('click', closeDeleteArchiveModal);
        
        // Close delete modal button
        document.getElementById('delete-close-btn').addEventListener('click', closeDeleteArchiveModal);
        
        // Announcement Modal Functions
        let currentPdfDoc = null;
        let currentPageNum = 1;
        let totalPages = 0;
        
        function openAnnouncementModal(filePath, title, postedOn) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalMeta').textContent = `Posted on: ${postedOn} | File: ${filePath.split('/').pop()}`;
            const modal = document.getElementById('announcementModal');
            const container = document.getElementById('pdfContainer');
            
            // Reset container
            container.innerHTML = `
                <div class="loading-spinner"></div>
                <p class="text-center text-gray-600">Loading announcement...</p>
            `;
            
            // Reset page navigation
            currentPageNum = 1;
            document.getElementById('prevPageBtn').disabled = true;
            document.getElementById('nextPageBtn').disabled = true;
            document.getElementById('pageIndicator').textContent = 'Page 1 of 1';
            
            modal.style.display = 'block';
            modal.classList.add('modal-active');
            
            // Get file extension
            const fileExtension = filePath.split('.').pop().toLowerCase();
            
            // Fix the file path to ensure it's correct
            let fullFilePath = filePath;
            if (!filePath.startsWith('http') && !filePath.startsWith('/')) {
                // Make sure we have the correct path for archived files
                if (!filePath.startsWith('uploads/')) {
                    fullFilePath = 'uploads/' + filePath;
                }
            }
            
            if (fileExtension === 'pdf') {
                // Load PDF
                pdfjsLib.getDocument(fullFilePath).promise.then(pdfDoc => {
                    currentPdfDoc = pdfDoc;
                    totalPages = pdfDoc.numPages;
                    
                    // Update page indicator
                    document.getElementById('pageIndicator').textContent = `Page 1 of ${totalPages}`;
                    
                    // Enable/disable navigation buttons
                    document.getElementById('prevPageBtn').disabled = true;
                    document.getElementById('nextPageBtn').disabled = totalPages <= 1;
                    
                    // Render first page
                    renderPage(1);
                }).catch(error => {
                    console.error('Error loading PDF:', error);
                    container.innerHTML = `
                        <div class="text-center py-8">
                            <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
                            <p class="text-lg text-gray-700 mb-2">Failed to load announcement</p>
                            <p class="text-gray-600 mb-4">${error.message}</p>
                            <button onclick="window.open('${fullFilePath}', '_blank')" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg">
                                <i class="fas fa-external-link-alt mr-2"></i> Open in New Tab
                            </button>
                        </div>
                    `;
                });
            } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                // Display image
                container.innerHTML = `
                    <div class="image-container">
                        <img src="${fullFilePath}" alt="Full view" class="image-viewer">
                    </div>
                `;
            } else if (['doc', 'docx', 'wps', 'xls', 'xlsx', 'ppt', 'pptx'].includes(fileExtension)) {
                // Use Microsoft Office Online viewer
                const fileUrl = encodeURIComponent(window.location.origin + '/' + fullFilePath);
                container.innerHTML = `
                    <div class="office-viewer" style="width: 100%; height: 80vh;">
                        <iframe 
                            src="https://view.officeapps.live.com/op/view.aspx?src=${fileUrl}" 
                            style="width: 100%; height: 100%; border: none;"
                            frameborder="0">
                        </iframe>
                    </div>
                    <div class="text-center mt-4">
                        <a href="${fullFilePath}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg inline-block">
                            <i class="fas fa-download mr-2"></i> Download File
                        </a>
                    </div>
                `;
            } else {
                // For other file types
                container.innerHTML = `
                    <div class="text-center p-8">
                        <i class="fas fa-file text-gray-400 text-6xl mb-4"></i>
                        <p class="text-lg text-gray-700 mb-2">Preview not available</p>
                        <p class="text-gray-600 mb-4">This file type cannot be previewed in the browser.</p>
                        <p class="text-gray-600 mb-4">File: ${filePath.split('/').pop()}</p>
                        <a href="${fullFilePath}" download class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-download mr-2"></i> Download File
                        </a>
                    </div>
                `;
            }
        }
        
        function renderPage(pageNum) {
            if (!currentPdfDoc) return;
            
            const container = document.getElementById('pdfContainer');
            const modalBody = document.querySelector('.modal-body');
            
            // Show loading indicator
            container.innerHTML = `
                <div class="loading-spinner"></div>
                <p class="text-center text-gray-600">Loading page ${pageNum}...</p>
            `;
            
            currentPdfDoc.getPage(pageNum).then(page => {
                // Get the dimensions of the modal body
                const modalRect = modalBody.getBoundingClientRect();
                const availableWidth = modalRect.width - 40; // Subtract padding
                const availableHeight = modalRect.height - 40; // Subtract padding
                
                // Get the viewport at a readable scale (1.5)
                const viewport = page.getViewport({
                    scale: 1.5
                });
                
                // Calculate the scale needed to fit the width
                const widthScale = availableWidth / viewport.width;
                
                // Calculate the scale needed to fit the height
                const heightScale = availableHeight / viewport.height;
                
                // Use the smaller scale to ensure the entire page fits
                let scale = Math.min(widthScale, heightScale);
                
                // Ensure the scale is not too small (minimum 0.8)
                scale = Math.max(scale, 0.8);
                
                // Get the scaled viewport
                const scaledViewport = page.getViewport({
                    scale
                });
                
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.height = scaledViewport.height;
                canvas.width = scaledViewport.width;
                canvas.className = 'pdf-page';
                
                // Clear container and add canvas
                container.innerHTML = '';
                container.appendChild(canvas);
                
                return page.render({
                    canvasContext: context,
                    viewport: scaledViewport
                }).promise;
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
        
        function goToPrevPage() {
            if (currentPageNum > 1) {
                currentPageNum--;
                renderPage(currentPageNum);
                
                // Update navigation
                document.getElementById('prevPageBtn').disabled = currentPageNum === 1;
                document.getElementById('nextPageBtn').disabled = false;
                document.getElementById('pageIndicator').textContent = `Page ${currentPageNum} of ${totalPages}`;
            }
        }
        
        function goToNextPage() {
            if (currentPageNum < totalPages) {
                currentPageNum++;
                renderPage(currentPageNum);
                
                // Update navigation
                document.getElementById('prevPageBtn').disabled = false;
                document.getElementById('nextPageBtn').disabled = currentPageNum === totalPages;
                document.getElementById('pageIndicator').textContent = `Page ${currentPageNum} of ${totalPages}`;
            }
        }
        
        function closeAnnouncementModal() {
            const modal = document.getElementById('announcementModal');
            modal.style.display = 'none';
            modal.classList.remove('modal-active');
            currentPdfDoc = null;
            currentPageNum = 1;
            totalPages = 0;
        }
        
        // Add event listeners for page navigation
        document.getElementById('prevPageBtn').addEventListener('click', goToPrevPage);
        document.getElementById('nextPageBtn').addEventListener('click', goToNextPage);
        
        // Close modal when clicking outside content
        window.onclick = function(event) {
            const modal = document.getElementById('announcementModal');
            const deleteModal = document.getElementById('delete-archive-modal');
            if (event.target === modal) {
                closeAnnouncementModal();
            }
            if (event.target === deleteModal) {
                closeDeleteArchiveModal();
            }
        }
        
        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAnnouncementModal();
                closeDeleteArchiveModal();
            }
        });
        
        // Function to generate PDF thumbnails
        function generatePdfThumbnail(pdfUrl, containerId) {
            const container = document.getElementById(containerId);
            if (!container) {
                console.error('Container not found:', containerId);
                return;
            }
            // Show loading indicator
            container.innerHTML = '<div class="loading-spinner"></div>';
            // Log the URL we are trying to load
            console.log('Loading PDF from:', pdfUrl);
            // Load the PDF
            pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
                console.log('PDF loaded successfully, pages:', pdf.numPages);
                // Get the first page
                return pdf.getPage(1);
            }).then(function(page) {
                // Create a thumbnail
                const scale = 0.5; // Adjust scale for thumbnail size
                const viewport = page.getViewport({ scale: scale });
                // Create a canvas element
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                canvas.className = 'pdf-thumbnail';
                // Render PDF page to canvas
                const renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };
                return page.render(renderContext).promise.then(function() {
                    // Replace loading indicator with thumbnail
                    container.innerHTML = '';
                    container.appendChild(canvas);
                    console.log('Thumbnail rendered successfully for:', containerId);
                });
            }).catch(function(error) {
                console.error('Error generating PDF thumbnail:', error);
                container.innerHTML = '<div class="text-red-500 text-center p-2">PDF preview unavailable</div>';
            });
        }
        
        // Initialize charts after DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all charts
            <?php foreach ($graphs as $graph): ?>
                (function() {
                    const canvasId = 'graph<?= $graph['id'] ?>';
                    const canvas = document.getElementById(canvasId);
                    if (!canvas) {
                        console.error('Canvas element not found:', canvasId);
                        return;
                    }
                    
                    // Check if Chart is loaded
                    if (typeof Chart === 'undefined') {
                        console.error('Chart.js library not loaded');
                        return;
                    }
                    
                    // Get the chart data
                    <?php 
                    // Get the chart data
                    $chartData = $graph['data'];
                    $chartType = $graph['type'];
                    
                    // Prepare data based on chart type
                    if ($chartType === 'pie') {
                        // For pie charts, we need label and value
                        $pieData = [];
                        if (isset($chartData[0]['label']) && isset($chartData[0]['value'])) {
                            $pieData = $chartData;
                        } else {
                            // Try to extract label and value from any structure
                            foreach ($chartData as $item) {
                                if (is_array($item) && isset($item['label']) && isset($item['value'])) {
                                    $pieData[] = $item;
                                }
                            }
                        }
                        
                        $labels = array_column($pieData, 'label');
                        $values = array_column($pieData, 'value');
                    } else {
                        // For bar charts, we need category, series1, and series2
                        $barData = [];
                        if (isset($chartData[0]['category']) && isset($chartData[0]['series1']) && isset($chartData[0]['series2'])) {
                            $barData = $chartData;
                        } else {
                            // Try to extract category, series1, and series2 from any structure
                            foreach ($chartData as $item) {
                                if (is_array($item) && isset($item['category']) && isset($item['series1']) && isset($item['series2'])) {
                                    $barData[] = $item;
                                }
                            }
                        }
                        
                        $categories = array_column($barData, 'category');
                        $series1 = array_column($barData, 'series1');
                        $series2 = array_column($barData, 'series2');
                        $series1Label = isset($barData[0]['series1_label']) ? $barData[0]['series1_label'] : 'Series 1';
                        $series2Label = isset($barData[0]['series2_label']) ? $barData[0]['series2_label'] : 'Series 2';
                    }
                    
                    if ($chartType === 'pie'): 
                    ?>
                        const labels = <?= json_encode($labels); ?>;
                        const values = <?= json_encode($values); ?>;
                        
                        new Chart(canvas, {
                            type: 'doughnut',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: values,
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
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            boxWidth: 15,
                                            padding: 15,
                                            font: {
                                                size: 12
                                            }
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                                        titleFont: {
                                            size: 14
                                        },
                                        bodyFont: {
                                            size: 14
                                        },
                                        padding: 10,
                                        cornerRadius: 4,
                                        displayColors: true
                                    }
                                }
                            }
                        });
                    <?php else: ?>
                        const categories = <?= json_encode($categories); ?>;
                        const series1 = <?= json_encode($series1); ?>;
                        const series2 = <?= json_encode($series2); ?>;
                        const series1Label = '<?= $series1Label ?>';
                        const series2Label = '<?= $series2Label ?>';
                        
                        console.log('Initializing bar chart with data:', {
                            categories,
                            series1,
                            series2,
                            series1Label,
                            series2Label
                        });
                        
                        // Create a new chart instance
                        const chart = new Chart(canvas, {
                            type: 'bar',
                            data: {
                                labels: categories,
                                datasets: [
                                    {
                                        label: series1Label,
                                        data: series1,
                                        backgroundColor: '#F97316',
                                        borderColor: '#EA580C',
                                        borderWidth: 1,
                                        borderRadius: 4,
                                        barPercentage: 0.7,
                                        categoryPercentage: 0.8
                                    },
                                    {
                                        label: series2Label,
                                        data: series2,
                                        backgroundColor: '#6B7280',
                                        borderColor: '#4B5563',
                                        borderWidth: 1,
                                        borderRadius: 4,
                                        barPercentage: 0.7,
                                        categoryPercentage: 0.8
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                layout: {
                                    padding: {
                                        top: 10,
                                        right: 10,
                                        bottom: 10,
                                        left: 10
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        grid: {
                                            color: 'rgba(0, 0, 0, 0.05)',
                                            drawBorder: false
                                        },
                                        ticks: {
                                            font: {
                                                size: 12
                                            },
                                            padding: 8,
                                            color: '#6B7280'
                                        }
                                    },
                                    x: {
                                        grid: {
                                            display: false,
                                            drawBorder: false
                                        },
                                        ticks: {
                                            font: {
                                                size: 12
                                            },
                                            padding: 8,
                                            color: '#6B7280'
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        position: 'top',
                                        labels: {
                                            boxWidth: 15,
                                            padding: 15,
                                            font: {
                                                size: 14
                                            }
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                        titleColor: '#fff',
                                        bodyColor: '#fff',
                                        titleFont: {
                                            size: 14
                                        },
                                        bodyFont: {
                                            size: 13
                                        },
                                        padding: 10,
                                        cornerRadius: 6,
                                        displayColors: true
                                    }
                                }
                            }
                        });
                        
                        // Force a resize after a short delay to ensure the chart renders properly
                        setTimeout(function() {
                            chart.resize();
                        }, 100);
                    <?php endif; ?>
                })();
            <?php endforeach; ?>
            
            // Generate PDF thumbnails for announcements
            <?php foreach ($announcements as $announcement): ?>
                <?php 
                $fileExtension = strtolower(pathinfo($announcement['file_path'], PATHINFO_EXTENSION));
                $filePath = $announcement['file_path'];
                
                // Make sure we have the correct path for archived files
                if (!strpos($filePath, 'uploads/')) {
                    $filePath = 'uploads/' . $filePath;
                }
                
                if ($fileExtension === 'pdf'): 
                ?>
                    generatePdfThumbnail('<?= htmlspecialchars($filePath) ?>', 'pdf-thumb-<?= $announcement['id'] ?>');
                <?php endif; ?>
            <?php endforeach; ?>
        });
    </script>
</body>
</html>