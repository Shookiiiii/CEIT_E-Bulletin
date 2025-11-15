<?php
include "../../db.php";
// Check database structure for date field
$query = "SELECT * FROM Central_post WHERE title='announcement' ORDER BY id DESC";
$result = $conn->query($query);
$pdfs = [];
while ($row = $result->fetch_assoc()) {
    // Handle different possible date field names
    $datePosted = isset($row['date_posted']) ? $row['date_posted'] : (isset($row['created_at']) ? $row['created_at'] : (isset($row['date']) ? $row['date'] : date('Y-m-d H:i:s')));

    $fileExtension = pathinfo($row['file_path'], PATHINFO_EXTENSION);
    $pdfs[] = [
        'id' => $row['id'],
        'file_path' => 'uploads/' . $row['file_path'],
        'description' => $row['content'],
        'posted_on' => $datePosted,
        'file_type' => strtolower($fileExtension)
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
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
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .announcement-delete-modal {
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

        .announcement-delete-modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
        }

        .announcement-delete-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .announcement-delete-modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #dc2626;
        }

        .announcement-delete-modal-close {
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
        }

        .announcement-delete-modal-close:hover {
            color: #dc2626;
        }

        .announcement-delete-modal-body {
            margin-bottom: 20px;
        }

        .announcement-delete-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .file-icon {
            font-size: 4rem;
        }

        .file-icon.pdf {
            color: #dc2626;
        }

        .file-icon.doc,
        .file-icon.docx,
        .file-icon.wps {
            color: #2563eb;
        }

        .file-icon.xls,
        .file-icon.xlsx {
            color: #16a34a;
        }

        .file-icon.ppt,
        .file-icon.pptx {
            color: #ea580c;
        }

        .file-icon.jpg,
        .file-icon.jpeg,
        .file-icon.png,
        .file-icon.gif {
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

        /* Ensure modals are on top when opened */
        .modal-active {
            z-index: 1001 !important;
        }
    </style>
</head>

<body>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-orange-600">
            <i class="fas fa-bullhorn mr-3 w-5"></i> Announcements Management<h2>
                <button id="upload-announcement-btn"
                    class="border-2 border-orange-500 bg-white hover:bg-orange-500 text-orange-500 hover:text-white px-4 py-2 rounded-lg transition duration-200 transform hover:scale-110">
                    <i class="fas fa-upload mr-2"></i> Upload Announcement
                </button>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 justify-center">
        <?php if (empty($pdfs)): ?>
            <div class="col-span-full text-center py-10">
                <i class="fas fa-file text-gray-300 text-5xl mb-3"></i>
                <p class="text-gray-500">No announcements found. Upload your first announcement.</p>
            </div>
        <?php else: ?>
            <?php foreach ($pdfs as $index => $pdf): ?>
                <div class="bg-white shadow-md rounded-lg p-4 w-full h-full flex flex-col justify-between border border-gray-500 transition duration-200 transform hover:scale-105">
                    <div class="mb-3 border border-gray-300 rounded">
                        <div id="file-preview-<?= $index ?>" class="file-preview">
                            <?php if ($pdf['file_type'] === 'pdf'): ?>
                                <div class="loading-spinner"></div>
                            <?php elseif (in_array($pdf['file_type'], ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                <img src="<?= $pdf['file_path'] ?>" alt="Preview" class="max-h-full max-w-full object-contain">
                            <?php else: ?>
                                <i class="fas fa-file file-icon <?= $pdf['file_type'] ?>"></i>
                            <?php endif; ?>
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
                    <div class="flex justify-end mt-4 space-x-2 text-xs">
                        <button id="view-full-<?= $index ?>" class="p-2 border rounded-lg border-gray-500 text-gray-500 hover:bg-gray-500 hover:text-white transition duration-200 transform hover:scale-110" title="View Full Document" data-file-type="<?= $pdf['file_type'] ?>" data-file-path="<?= $pdf['file_path'] ?>">
                            <i class="fas fa-eye fa-sm"></i>
                            View
                        </button>
                        <button class="p-2 border border-blue-500 text-blue-500 rounded-lg hover:bg-blue-500 hover:text-white transition duration-200 transform hover:scale-110 edit-btn" data-index="<?= $index ?>" data-id="<?= $pdf['id'] ?>" data-description="<?= htmlspecialchars($pdf['description']) ?>" title="Edit">
                            <i class="fas fa-edit fa-sm"></i>
                            Edit
                        </button>
                        <button class="p-2 border border-red-500 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition duration-200 transform hover:scale-110 announcement-delete-btn" data-index="<?= $index ?>" data-id="<?= $pdf['id'] ?>" data-description="<?= htmlspecialchars($pdf['description']) ?>" title="Archive/Delete">
                            <i class="fas fa-trash fa-sm"></i>
                            Archive/Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- File View Modal -->
    <?php foreach ($pdfs as $index => $pdf): ?>
        <div id="file-modal-<?= $index ?>" class="file-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title"><?= htmlspecialchars($pdf['description']) ?></h3>
                    <span class="modal-close" onclick="closeFileModal(<?= $index ?>)">&times;</span>
                </div>
                <div class="modal-body">
                    <div id="pdfContainer-<?= $index ?>" class="pdf-container">
                        <div class="loading-spinner"></div>
                        <p class="text-center text-gray-600">Loading announcement...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="modal-meta">
                        Posted on: <?= date('F j, Y', strtotime($pdf['posted_on'])) ?> | File: <?= basename($pdf['file_path']) ?>
                    </div>
                    <?php if ($pdf['file_type'] === 'pdf'): ?>
                        <div class="page-navigation">
                            <button id="prevPageBtn-<?= $index ?>" class="page-nav-btn" disabled>
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <div id="pageIndicator-<?= $index ?>" class="page-indicator">Page 1 of 1</div>
                            <button id="nextPageBtn-<?= $index ?>" class="page-nav-btn" disabled>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Edit Modal -->
    <div id="edit-modal" class="hidden fixed inset-0 bg-black bg-opacity-70 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Announcement</h3>
                <form id="edit-form" class="mt-2 py-3" enctype="multipart/form-data">
                    <input type="hidden" id="edit-id" name="id">
                    <div class="mb-4">
                        <label for="edit-description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
                        <input type="text" id="edit-description" name="description"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div>
                        <label for="edit-file" class="block text-sm font-medium text-gray-700">Replace File (optional)</label>
                        <input
                            type="file"
                            id="edit-file"
                            name="file"
                            accept=".pdf,.doc,.docx,.wps,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" id="cancel-edit"
                            class="px-4 py-2 border border-gray-300 text-gray-500 hover:bg-gray-500 hover:text-white font-medium rounded-lg shadow-sm transition duration-200 transform hover:scale-110">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 border border-green-500 text-green-500 hover:bg-green-500 hover:text-white font-medium rounded-lg shadow-sm transition duration-200 transform hover:scale-110">
                            <i class="fas fa-save fa-sm"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Archive/Delete Confirmation Modal for Announcements -->
    <div id="announcement-delete-modal" class="announcement-delete-modal">
        <div class="announcement-delete-modal-content">
            <div class="announcement-delete-modal-header">
                <h3 class="announcement-delete-modal-title">Choose Action</h3>
                <span class="announcement-delete-modal-close" onclick="closeAnnouncementDeleteModal()">&times;</span>
            </div>
            <div class="announcement-delete-modal-body">
                <p>What would you like to do with this announcement?</p>
                <p class="font-semibold mt-2" id="delete-announcement-title"></p>
            </div>
            <div class="announcement-delete-modal-footer">
                <button class="px-4 py-2 border border-gray-500 text-gray-500 hover:bg-gray-500 hover:text-white rounded-lg transition duration-200 transform hover:scale-110" onclick="closeAnnouncementDeleteModal()">
                    Cancel
                </button>
                <button class="px-4 py-2 border border-yellow-500 text-yellow-500 hover:bg-yellow-500 hover:text-white rounded-lg transition duration-200 transform hover:scale-110" id="archive-announcement-btn">
                    <i class="fas fa-archive mr-2"></i> Archive
                </button>
                <button class="px-4 py-2 border border-red-500 text-red-500 hover:bg-red-500 hover:text-white rounded-lg transition duration-200 transform hover:scale-110" id="confirm-announcement-delete-btn">
                    <i class="fas fa-trash mr-2"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="upload-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Upload Announcement</h2>
            <form id="upload-form" action="AddAnnouncement.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <input
                        type="text"
                        id="description"
                        name="description"
                        required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="pdfFile" class="block text-sm font-medium text-gray-700">File</label>
                    <input
                        type="file"
                        id="pdfFile"
                        name="pdfFile"
                        accept=".pdf,.doc,.docx,.wps,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png"
                        required
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">

                </div>
                <div class="flex justify-end space-x-3 text-sm">
                    <button
                        type="button"
                        id="cancel-upload-btn"
                        class="px-4 py-2 border border-gray-500 text-gray-500 rounded-lg hover:bg-gray-500 hover:text-white transition duration-200 transform hover:scale-110">
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 border border-blue-500 text-blue-500 rounded-lg hover:bg-blue-500 hover:text-white transition duration-200 transform hover:scale-110">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
    <script>
        // Simple, self-contained JavaScript with no external dependencies
        document.addEventListener('DOMContentLoaded', function() {
            // Upload button functionality
            const uploadBtn = document.getElementById('upload-announcement-btn');
            const uploadModal = document.getElementById('upload-modal');
            const cancelUploadBtn = document.getElementById('cancel-upload-btn');
            const uploadForm = document.getElementById('upload-form');

            if (uploadBtn) {
                uploadBtn.addEventListener('click', function() {
                    uploadModal.classList.remove('hidden');
                });
            }

            if (cancelUploadBtn) {
                cancelUploadBtn.addEventListener('click', function() {
                    uploadModal.classList.add('hidden');
                });
            }

            if (uploadForm) {
                uploadForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.textContent;

                    // Show loading state
                    submitBtn.textContent = 'Uploading...';
                    submitBtn.disabled = true;

                    fetch('AddAnnouncement.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.text();
                        })
                        .then(data => {
                            if (data.trim() === 'success') {
                                // Close modal and reload
                                uploadModal.classList.add('hidden');
                                location.reload();
                            } else {
                                alert('Error uploading announcement: ' + data);
                                submitBtn.textContent = originalText;
                                submitBtn.disabled = false;
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while uploading the announcement');
                            submitBtn.textContent = originalText;
                            submitBtn.disabled = false;
                        });
                });
            }

            // PDF.js setup
            if (typeof pdfjsLib !== 'undefined') {
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';

                // PDF handling code
                const pdfDocs = {};
                const currentPageNum = {};
                const totalPages = {};

                <?php foreach ($pdfs as $index => $pdf): ?>
                    <?php if ($pdf['file_type'] === 'pdf'): ?>
                        // Render PDF preview
                        pdfjsLib.getDocument("<?= $pdf['file_path'] ?>").promise.then(function(pdf) {
                            return pdf.getPage(1);
                        }).then(function(page) {
                            const container = document.getElementById("file-preview-<?= $index ?>");
                            const scale = 0.5;
                            const viewport = page.getViewport({
                                scale: scale
                            });
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
                            document.getElementById("file-preview-<?= $index ?>").innerHTML =
                                '<div class="text-red-500 p-2 text-center">Could not load PDF preview</div>';
                        });
                    <?php endif; ?>
                <?php endforeach; ?>

                // Set up view buttons for each file
                <?php foreach ($pdfs as $index => $pdf): ?>
                    document.getElementById("view-full-<?= $index ?>").addEventListener("click", function() {
                        const modal = document.getElementById("file-modal-<?= $index ?>");
                        const container = document.getElementById("pdfContainer-<?= $index ?>");
                        const fileType = this.getAttribute('data-file-type');
                        const filePath = this.getAttribute('data-file-path');

                        // Set modal to highest z-index
                        modal.classList.add('modal-active');
                        modal.style.display = "block";

                        // Get file extension
                        const fileExtension = filePath.split('.').pop().toLowerCase();

                        if (fileExtension === 'pdf') {
                            // Load PDF
                            pdfjsLib.getDocument(filePath).promise.then(pdfDoc => {
                                pdfDocs[<?= $index ?>] = pdfDoc;
                                totalPages[<?= $index ?>] = pdfDoc.numPages;
                                currentPageNum[<?= $index ?>] = 1;

                                // Update page indicator
                                document.getElementById('pageIndicator-<?= $index ?>').textContent = `Page 1 of ${totalPages[<?= $index ?>]}`;

                                // Enable/disable navigation buttons
                                document.getElementById('prevPageBtn-<?= $index ?>').disabled = true;
                                document.getElementById('nextPageBtn-<?= $index ?>').disabled = totalPages[<?= $index ?>] <= 1;

                                // Render first page
                                renderPage(<?= $index ?>, 1);
                            }).catch(error => {
                                console.error('Error loading PDF:', error);
                                container.innerHTML = `
                                    <div class="text-center py-8">
                                        <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
                                        <p class="text-lg text-gray-700 mb-2">Failed to load announcement</p>
                                        <p class="text-gray-600 mb-4">${error.message}</p>
                                    </div>
                                `;
                            });
                        } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                            // Display image with perfect fit
                            container.innerHTML = `
                                <div class="image-container">
                                    <img src="${filePath}" alt="Full view" class="image-viewer">
                                </div>
                            `;

                            // Ensure image fits perfectly
                            const img = container.querySelector('img');
                            img.onload = function() {
                                const modalBody = document.querySelector(`#file-modal-<?= $index ?> .modal-body`);
                                const containerWidth = modalBody.clientWidth;
                                const containerHeight = modalBody.clientHeight;

                                // Calculate aspect ratio
                                const imgRatio = this.naturalWidth / this.naturalHeight;
                                const containerRatio = containerWidth / containerHeight;

                                if (imgRatio > containerRatio) {
                                    // Image is wider - fit to width
                                    this.style.width = containerWidth + 'px';
                                    this.style.height = 'auto';
                                } else {
                                    // Image is taller - fit to height
                                    this.style.height = containerHeight + 'px';
                                    this.style.width = 'auto';
                                }
                            };
                        } else if (['doc', 'docx', 'wps', 'xls', 'xlsx', 'ppt', 'pptx'].includes(fileExtension)) {
                            // Use Microsoft Office Online viewer with direct URL
                            const fullUrl = window.location.origin + '/' + filePath;
                            const encodedUrl = encodeURIComponent(fullUrl);
                            container.innerHTML = `
                                <div class="office-viewer">
                                    <iframe 
                                        src="https://view.officeapps.live.com/op/view.aspx?src=${encodedUrl}&wdStartOn=1&wdEmbed=1" 
                                        style="width: 100%; height: 100%; border: none;"
                                        frameborder="0"
                                        allowfullscreen>
                                    </iframe>
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
                                </div>
                            `;
                        }
                    });

                    <?php if ($pdf['file_type'] === 'pdf'): ?>
                        // Page navigation functions for this PDF
                        document.getElementById('prevPageBtn-<?= $index ?>').addEventListener('click', function() {
                            goToPrevPage(<?= $index ?>);
                        });

                        document.getElementById('nextPageBtn-<?= $index ?>').addEventListener('click', function() {
                            goToNextPage(<?= $index ?>);
                        });
                    <?php endif; ?>

                    // Close modal when clicking outside content
                    document.getElementById("file-modal-<?= $index ?>").addEventListener("click", function(e) {
                        if (e.target === this) {
                            closeFileModal(<?= $index ?>);
                        }
                    });
                <?php endforeach; ?>

                // Function to render a page
                function renderPage(index, pageNum) {
                    if (!pdfDocs[index]) return;

                    const container = document.getElementById("pdfContainer-" + index);
                    const modalBody = document.querySelector(`#file-modal-${index} .modal-body`);

                    // Show loading indicator
                    container.innerHTML = `
                        <div class="loading-spinner"></div>
                        <p class="text-center text-gray-600">Loading page ${pageNum}...</p>
                    `;

                    pdfDocs[index].getPage(pageNum).then(page => {
                        // Get the dimensions of the modal body
                        const modalRect = modalBody.getBoundingClientRect();
                        const availableWidth = modalRect.width;
                        const availableHeight = modalRect.height;

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

                // Function to go to previous page
                function goToPrevPage(index) {
                    if (!pdfDocs[index]) return;

                    if (currentPageNum[index] > 1) {
                        currentPageNum[index]--;
                        renderPage(index, currentPageNum[index]);

                        // Update navigation
                        document.getElementById(`prevPageBtn-${index}`).disabled = currentPageNum[index] === 1;
                        document.getElementById(`nextPageBtn-${index}`).disabled = false;
                        document.getElementById(`pageIndicator-${index}`).textContent = `Page ${currentPageNum[index]} of ${totalPages[index]}`;
                    }
                }

                // Function to go to next page
                function goToNextPage(index) {
                    if (!pdfDocs[index]) return;

                    if (currentPageNum[index] < totalPages[index]) {
                        currentPageNum[index]++;
                        renderPage(index, currentPageNum[index]);

                        // Update navigation
                        document.getElementById(`prevPageBtn-${index}`).disabled = false;
                        document.getElementById(`nextPageBtn-${index}`).disabled = currentPageNum[index] === totalPages[index];
                        document.getElementById(`pageIndicator-${index}`).textContent = `Page ${currentPageNum[index]} of ${totalPages[index]}`;
                    }
                }
            }

            // Edit button functionality
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const description = this.getAttribute('data-description');

                    // Set the values in the modal
                    document.getElementById('edit-id').value = id;
                    document.getElementById('edit-description').value = description;

                    // Show the modal
                    document.getElementById('edit-modal').classList.remove('hidden');
                });
            });

            // Cancel edit button
            document.getElementById('cancel-edit').addEventListener('click', function() {
                document.getElementById('edit-modal').classList.add('hidden');
            });

            // Form submission handling for edit
            document.getElementById('edit-form').addEventListener('submit', function(e) {
                e.preventDefault();

                const id = document.getElementById('edit-id').value;
                const description = document.getElementById('edit-description').value;
                const fileInput = document.getElementById('edit-file');

                const formData = new FormData();
                formData.append('id', id);
                formData.append('description', description);

                if (fileInput.files.length > 0) {
                    // Generate new filename with timestamp and random component for uniqueness
                    const now = new Date();
                    const day = now.getDate().toString().padStart(2, '0');
                    const month = (now.getMonth() + 1).toString().padStart(2, '0');
                    const year = now.getFullYear();
                    const hours = now.getHours().toString().padStart(2, '0');
                    const minutes = now.getMinutes().toString().padStart(2, '0');
                    const seconds = now.getSeconds().toString().padStart(2, '0'); // Add seconds
                    const random = Math.floor(Math.random() * 9000) + 1000; // Add a random number

                    // Get file extension
                    const fileName = fileInput.files[0].name;
                    const fileExtension = fileName.split('.').pop();

                    // Format: announcement_DD_MM_YYYY_HH_MM_SS_RANDOM.extension
                    const newFilename = `announcement_${day}_${month}_${year}_${hours}_${minutes}_${seconds}_${random}.${fileExtension}`;

                    // Get the original file and rename it
                    const originalFile = fileInput.files[0];
                    const renamedFile = new File([originalFile], newFilename, {
                        type: originalFile.type
                    });

                    formData.append('file', renamedFile);
                }

                // Send the data to the server
                fetch('update_announcement.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Hide modal and reload the page
                            document.getElementById('edit-modal').classList.add('hidden');
                            location.reload();
                        } else {
                            alert('Error updating announcement: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating the announcement');
                    });
            });

            // Archive/Delete button functionality for announcements
            let announcementToAction = null;

            document.querySelectorAll('.announcement-delete-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevent any default behavior
                    e.stopPropagation(); // Stop event propagation

                    const id = this.getAttribute('data-id');
                    const description = this.getAttribute('data-description');

                    announcementToAction = id;

                    // Set the announcement title in the delete confirmation modal
                    document.getElementById('delete-announcement-title').textContent = description;

                    // Show the delete confirmation modal
                    document.getElementById('announcement-delete-modal').style.display = 'flex';
                });
            });

            // Archive button
            document.getElementById('archive-announcement-btn').addEventListener('click', function() {
                if (announcementToAction) {
                    // Disable the button to prevent multiple clicks
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Archiving...';

                    // Send the archive request to the server
                    fetch('archive_announcement.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'id=' + encodeURIComponent(announcementToAction)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Hide modal and reload the page
                                closeAnnouncementDeleteModal();
                                location.reload();
                            } else {
                                alert('Error archiving announcement: ' + data.message);
                                // Re-enable the button
                                document.getElementById('archive-announcement-btn').disabled = false;
                                document.getElementById('archive-announcement-btn').innerHTML = '<i class="fas fa-archive mr-2"></i> Archive';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while archiving the announcement');
                            // Re-enable the button
                            document.getElementById('archive-announcement-btn').disabled = false;
                            document.getElementById('archive-announcement-btn').innerHTML = '<i class="fas fa-archive mr-2"></i> Archive';
                        });
                }
            });

            // Delete button
            document.getElementById('confirm-announcement-delete-btn').addEventListener('click', function() {
                if (announcementToAction) {
                    // Disable the button to prevent multiple clicks
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Deleting...';

                    // Send the delete request to the server
                    fetch('delete_announcement.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'id=' + encodeURIComponent(announcementToAction)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Hide modal and reload the page
                                closeAnnouncementDeleteModal();
                                location.reload();
                            } else {
                                alert('Error deleting announcement: ' + data.message);
                                // Re-enable the button
                                document.getElementById('confirm-announcement-delete-btn').disabled = false;
                                document.getElementById('confirm-announcement-delete-btn').innerHTML = '<i class="fas fa-trash mr-2"></i> Delete';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while deleting the announcement');
                            // Re-enable the button
                            document.getElementById('confirm-announcement-delete-btn').disabled = false;
                            document.getElementById('confirm-announcement-delete-btn').innerHTML = '<i class="fas fa-trash mr-2"></i> Delete';
                        });
                }
            });
        });

        // Global functions
        function closeUploadModal() {
            document.getElementById('upload-modal').classList.add('hidden');
        }

        function closeFileModal(index) {
            const modal = document.getElementById("file-modal-" + index);
            if (modal) {
                modal.classList.remove('modal-active');
                modal.style.display = "none";
            }
        }

        function closeAnnouncementDeleteModal() {
            document.getElementById('announcement-delete-modal').style.display = 'none';
        }
    </script>
</body>

</html>