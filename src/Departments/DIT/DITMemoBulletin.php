<?php
include "../../db.php";
// Debug: Check database connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// Debug: Check total number of memos
$debugQuery = "SELECT * FROM DIT_Post WHERE title='memo'";
$debugResult = $conn->query($debugQuery);
$totalMemos = $debugResult->num_rows;
// Debug: Check approved memos with different case variations
$debugQueryApproved = "SELECT * FROM DIT_Post WHERE title='memo' AND status='Approved'";
$debugResultApproved = $conn->query($debugQueryApproved);
$approvedMemos = $debugResultApproved->num_rows;
$debugQueryLower = "SELECT * FROM DIT_Post WHERE title='memo' AND status='approved'";
$debugResultLower = $conn->query($debugQueryLower);
$approvedMemosLower = $debugResultLower->num_rows;
// Main query - try both title variations
$query = "SELECT * FROM DIT_Post WHERE (title='memo' OR title='Memo') AND (status='Approved' OR status='approved')";
$result = $conn->query($query);
$pdfs = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $file_path = 'uploads/' . $row['file_path'];
        
        // Check if file exists
        $file_exists = file_exists($file_path);
        
        $pdfs[] = [
            'file_path' => $file_path,
            'description' => $row['content'],
            'posted_on' => date("F j, Y", strtotime($row['created_at'])),
            'file_exists' => $file_exists,
            'id' => $row['id']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Memo Carousel Viewer</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';
    </script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .carousel-container {
            width: 100%;
            height: 100%;
            max-height: 600px;
            overflow: hidden;
        }
        .pdf-preview-area {
            width: 100%;
            height: 400px;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            cursor: pointer;
        }
        canvas {
            max-width: 100%;
            max-height: 100%;
        }
        .view-hint {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: rgba(249, 115, 22, 0.9);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
            opacity: 0;
            transition: opacity 0.2s;
            pointer-events: none;
        }
        .pdf-preview-area:hover .view-hint {
            opacity: 1;
        }
        .loading-indicator {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }
        .spinner {
            border: 3px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top: 3px solid #f97316;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .no-memos-message {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            padding: 20px;
            text-align: center;
        }
        .debug-info {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8d7da;
            border-radius: 5px;
            text-align: left;
            font-size: 12px;
            color: #721c24;
        }
    </style>
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="carousel-container bg-white shadow-lg rounded-lg p-4 flex flex-col justify-between h-full">
        <?php if (count($pdfs) > 0): ?>
            <div id="memo-viewer" class="pdf-preview-area rounded mb-4 bg-gray-50">
                <div class="loading-indicator">
                    <div class="spinner"></div>
                    <p class="text-gray-500 text-sm">Loading preview...</p>
                </div>
                <div class="view-hint">Click to view full memo</div>
            </div>
            <div class="text-center mb-2">
                <p id="memo-description" class="text-base font-semibold text-gray-800 truncate px-2"></p>
                <p id="memo-filename" class="text-sm text-gray-600 italic"></p>
            </div>
            <div class="text-center text-sm text-gray-500">
                <span id="memo-posted-date"></span>
            </div>
            <div class="flex justify-center space-x-2 mt-2">
                <button id="memo-prev-btn" class="bg-orange-500 text-white p-2 rounded-full hover:bg-orange-600 transition-colors duration-200 shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="6" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button id="memo-next-btn" class="bg-orange-500 text-white p-2 rounded-full hover:bg-orange-600 transition-colors duration-200 shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="6" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        <?php else: ?>
            <div class="no-memos-message">
                <i class="fas fa-file-alt text-gray-400 text-5xl mb-4"></i>
                <p class="text-gray-600 text-lg">No approved memos available</p>
                
                <div class="debug-info">
                    <p><strong>Debug information:</strong></p>
                    <p>Total memos in database: <?= $totalMemos ?></p>
                    <p>Memos with 'Approved' status: <?= $approvedMemos ?></p>
                    <p>Memos with 'approved' status: <?= $approvedMemosLower ?></p>
                    <p>Query result count: <?= count($pdfs) ?></p>
                    <p>Database error: <?= $conn->error ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Pass PDF data to parent window
        const memoDataLocal = <?= json_encode($pdfs) ?>;
        console.log("Memo Data:", memoDataLocal); // Debug: Log PDF data to console
        
        // Set the memo data in the parent window
        if (window.parent) {
            window.parent.memoData = memoDataLocal;
            console.log("Memo data passed to parent window"); // Debug: Confirm data transfer
        } else {
            console.error("Parent window not accessible"); // Debug: Check if parent window is accessible
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            // Only proceed if there are memos to show
            if (memoDataLocal.length === 0) {
                console.log("No memo data available"); // Debug: Log if no data is available
                return;
            }
            
            console.log("Initializing Memo viewer with", memoDataLocal.length, "items"); // Debug: Log initialization
            
            let currentIndex = 0;
            const viewer = document.getElementById('memo-viewer');
            const desc = document.getElementById('memo-description');
            const filename = document.getElementById('memo-filename');
            const postedDate = document.getElementById('memo-posted-date');
            
            function renderMemo(index) {
                const pdf = memoDataLocal[index];
                console.log("Rendering Memo:", pdf); // Debug: Log current PDF
                
                // Check if file exists
                if (!pdf.file_exists) {
                    viewer.innerHTML = `
                        <div class="text-center p-4">
                            <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                            <p class="text-red-500">File not found</p>
                            <p class="text-gray-600 text-sm">${pdf.file_path}</p>
                        </div>
                    `;
                    desc.textContent = pdf.description;
                    filename.textContent = pdf.file_path.split('/').pop();
                    postedDate.textContent = "Posted on: " + pdf.posted_on;
                    return;
                }
                
                // Show loading indicator
                viewer.innerHTML = `
                    <div class="loading-indicator">
                        <div class="spinner"></div>
                        <p class="text-gray-500 text-sm">Loading preview...</p>
                    </div>
                    <div class="view-hint">Click to view full memo</div>
                `;
                
                desc.textContent = pdf.description;
                filename.textContent = pdf.file_path.split('/').pop();
                postedDate.textContent = "Posted on: " + pdf.posted_on;
                
                // Get file extension
                const fileExtension = pdf.file_path.split('.').pop().toLowerCase();
                console.log("File extension:", fileExtension); // Debug: Log file extension
                
                if (fileExtension === 'pdf') {
                    try {
                        pdfjsLib.getDocument(pdf.file_path).promise.then(pdfDoc => {
                            console.log("PDF document loaded successfully"); // Debug: Confirm PDF loading
                            return pdfDoc.getPage(1);
                        }).then(page => {
                            console.log("First page retrieved successfully"); // Debug: Confirm page retrieval
                            
                            // Calculate scale to fit the container
                            const containerWidth = viewer.clientWidth;
                            const containerHeight = viewer.clientHeight;
                            const viewport = page.getViewport({ scale: 1.0 });
                            
                            // Calculate scale to fit container while maintaining aspect ratio
                            const scale = Math.min(
                                containerWidth / viewport.width,
                                containerHeight / viewport.height
                            ) * 0.9; // 90% of container size for padding
                            
                            const scaledViewport = page.getViewport({ scale });
                            const canvas = document.createElement('canvas');
                            const context = canvas.getContext('2d');
                            canvas.height = scaledViewport.height;
                            canvas.width = scaledViewport.width;
                            
                            // Clear viewer and add canvas
                            viewer.innerHTML = '';
                            viewer.appendChild(canvas);
                            
                            // Add view hint back
                            const hint = document.createElement('div');
                            hint.className = 'view-hint';
                            hint.textContent = 'Click to view full memo';
                            viewer.appendChild(hint);
                            
                            return page.render({
                                canvasContext: context,
                                viewport: scaledViewport
                            }).promise;
                        }).then(() => {
                            console.log("PDF rendered successfully"); // Debug: Confirm rendering
                        }).catch(error => {
                            console.error('Preview error:', error); // Debug: Log any errors
                            viewer.innerHTML = `
                                <div class="text-center p-4">
                                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                                    <p class="text-red-500">Failed to load preview</p>
                                    <p class="text-gray-600 text-sm">${error.message}</p>
                                </div>
                                <div class="view-hint">Click to view full memo</div>
                            `;
                        });
                    } catch (error) {
                        console.error('PDF loading error:', error); // Debug: Log any errors
                        viewer.innerHTML = `
                            <div class="text-center p-4">
                                <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                                <p class="text-red-500">Failed to load PDF</p>
                                <p class="text-gray-600 text-sm">${error.message}</p>
                            </div>
                            <div class="view-hint">Click to view full memo</div>
                        `;
                    }
                } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                    // Display image preview
                    viewer.innerHTML = `
                        <img src="${pdf.file_path}" alt="Preview" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        <div class="view-hint">Click to view full memo</div>
                    `;
                } else {
                    // For other file types, show icon
                    viewer.innerHTML = `
                        <div class="text-center">
                            <i class="fas fa-file text-gray-400 text-6xl mb-2"></i>
                            <p class="text-gray-600">No preview available</p>
                            <p class="text-gray-500 text-sm">${fileExtension.toUpperCase()} file</p>
                        </div>
                        <div class="view-hint">Click to view full memo</div>
                    `;
                }
            }
            
            // Add click event to open modal
            viewer.addEventListener('click', () => {
                console.log("Memo viewer clicked, opening modal for index:", currentIndex); // Debug: Log click event
                // Use postMessage to communicate with parent window
                if (window.parent) {
                    window.parent.postMessage({
                        type: 'openMemoModal',
                        index: currentIndex
                    }, '*');
                } else {
                    console.error("Parent window not accessible"); // Debug: Check if parent window is accessible
                }
            });
            
            document.getElementById('memo-prev-btn').addEventListener('click', () => {
                currentIndex = (currentIndex - 1 + memoDataLocal.length) % memoDataLocal.length;
                console.log("Memo Previous button clicked, new index:", currentIndex); // Debug: Log navigation
                renderMemo(currentIndex);
            });
            
            document.getElementById('memo-next-btn').addEventListener('click', () => {
                currentIndex = (currentIndex + 1) % memoDataLocal.length;
                console.log("Memo Next button clicked, new index:", currentIndex); // Debug: Log navigation
                renderMemo(currentIndex);
            });
            
            // Initial render
            renderMemo(currentIndex);
        });
    </script>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</body>
</html>