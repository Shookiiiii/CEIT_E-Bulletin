
<?php
include "../../db.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>DIT POSTS</title>
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
            z-index: 9999;
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
            z-index: 10000;
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

        .btn-archive {
            padding: 8px 16px;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-archive:hover {
            background-color: #2563eb;
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

        .file-icon {
            font-size: 4rem;
        }

        .file-icon.pdf {
            color: #dc2626;
        }

        .status-section {
            margin-bottom: 40px;
            padding: 20px;
            border-radius: 8px;
        }

        .approved {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
        }

        .pending {
            background-color: #fffbeb;
            border: 1px solid #fef08a;
        }

        .not-approved {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
        }

        .status-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid;
        }

        .approved .status-title {
            color: #16a34a;
            border-color: #16a34a;
        }

        .pending .status-title {
            color: #d97706;
            border-color: #d97706;
        }

        .not-approved .status-title {
            color: #dc2626;
            border-color: #dc2626;
        }

        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        .table-responsive {
            overflow-x: auto;
        }

        @media (min-width: 1024px) {
            .chart-container {
                height: 300px;
            }
        }
    </style>
</head>

<body class="bg-white text-gray-800">
    <div class="w-full">
        <nav class="flex justify-center space-x-10 border-b-2 border-gray-200 mb-6">
            <button class="dit-tab-btn text-orange-600 border-orange-600 hover:border-b-2 hover:border-orange-400 hover:text-orange-400 border-b-2 font-bold hover:font-bold pb-2" data-subtab="dit-announcement">Announcement</button>
            <button class="dit-tab-btn text-gray-700 hover:border-b-2 hover:border-orange-400 hover:text-orange-400 hover:font-bold pb-2" data-subtab="dit-memo">Memo Updates</button>
            <button class="dit-tab-btn text-gray-700 hover:border-b-2 hover:border-orange-400 hover:text-orange-400 hover:font-bold pb-2" data-subtab="dit-graphs">Graphs</button>
        </nav>

        <div id="dit-announcement" class="dit-subtab">
            <?php include 'Manage/DIT_ManageAnnouncements.php'; ?>
        </div>

        <div id="dit-memo" class="dit-subtab hidden">
            <?php include 'Manage/DIT_ManageMemo.php'; ?>
        </div>

        <div id="dit-graphs" class="dit-subtab hidden">
            <?php include 'Manage/DIT_ManageGraphs.php'; ?>
        </div>
    </div>

    <!-- Reject Modals -->
    <?php include 'Manage/DIT_reject_modals.php'; ?>

    <!-- File View Modals -->
    <?php include 'Manage/DIT_file_modals.php'; ?>
    <script>
        // Initialize PDF.js worker
        if (typeof pdfjsLib !== 'undefined') {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';
        }

        // Get URL parameters
        function getUrlParameter(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            var results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        }
        // Create global namespace for DIT functions to avoid conflicts
        window.DITPosts = {
            pdfDocs: {},
            currentPageNum: {},
            totalPages: {},
            currentRejectId: null,
            currentGraphRejectId: null,
            currentMemoRejectId: null,
            currentGraphRejectIsGroup: false,
            currentGraphRejectIds: [],
            init: function() {
                // Set initial tab based on URL parameter
                const subtab = getUrlParameter('subtab') || 'dit-announcement';
                this.setActiveTab(subtab);

                // Tab switching
                document.querySelectorAll('.dit-tab-btn').forEach(button => {
                    button.addEventListener('click', () => {
                        const target = button.getAttribute('data-subtab');
                        this.setActiveTab(target);

                        // Update URL without reloading the page
                        const url = new URL(window.location);
                        url.searchParams.set('subtab', target);
                        window.history.pushState({}, '', url);
                    });
                });

                // Render PDF previews
                this.renderPdfPreviews(<?= json_encode($approved) ?>, 'approved');
                this.renderPdfPreviews(<?= json_encode($pending) ?>, 'pending');
                this.renderPdfPreviews(<?= json_encode($not_approved) ?>, 'not-approved');
                this.renderPdfPreviews(<?= json_encode($approved_memos) ?>, 'approved-memo');
                this.renderPdfPreviews(<?= json_encode($pending_memos) ?>, 'pending-memo');
                this.renderPdfPreviews(<?= json_encode($not_approved_memos) ?>, 'not-approved-memo');

                // Setup approve/reject buttons for announcements
                this.setupAnnouncementApproveRejectButtons();
                // Setup approve/reject buttons for memos
                this.setupMemoApproveRejectButtons();
                // Setup approve/reject buttons for graphs
                this.setupGraphApproveRejectButtons();
            },
            setActiveTab: function(subtab) {
                // Hide all content areas
                document.querySelectorAll('.dit-subtab').forEach(tab => {
                    tab.classList.add('hidden');
                });

                // Show selected content
                const selectedTab = document.getElementById(subtab);
                if (selectedTab) {
                    selectedTab.classList.remove('hidden');
                    
                    // Initialize DIT graphs if the graphs tab is selected
                    if (subtab === 'dit-graphs' && typeof window.initializeDITGraphs === 'function') {
                        // Use a small delay to ensure the tab is fully visible
                        setTimeout(window.initializeDITGraphs, 100);
                    }
                }

                // Remove all active states from buttons
                document.querySelectorAll('.dit-tab-btn').forEach(btn => {
                    btn.classList.remove('text-orange-600', 'border-b-2', 'border-orange-600', 'font-bold');
                    btn.classList.add('text-gray-700');
                });

                // Add active to the matching button
                const activeBtn = document.querySelector(`[data-subtab="${subtab}"]`);
                if (activeBtn) {
                    activeBtn.classList.add('text-orange-600', 'border-b-2', 'border-orange-600', 'font-bold');
                    activeBtn.classList.remove('text-gray-700');
                }
            },
            renderPdfPreviews: function(pdfs, status) {
                const self = this;
                pdfs.forEach((pdf, index) => {
                    // Render first page as preview
                    if (typeof pdfjsLib !== 'undefined') {
                        pdfjsLib.getDocument(pdf.file_path).promise.then(function(pdfDoc) {
                            return pdfDoc.getPage(1);
                        }).then(function(page) {
                            const container = document.getElementById(`file-preview-${status}-${index}`);
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
                            document.getElementById(`file-preview-${status}-${index}`).innerHTML =
                                '<div class="text-red-500 p-2 text-center">Could not load PDF preview</div>';
                        });
                        // Set up view buttons for each file
                        const viewButton = document.getElementById(`view-full-${status}-${index}`);
                        if (viewButton) {
                            viewButton.addEventListener("click", function() {
                                const modal = document.getElementById(`file-modal-${status}-${index}`);
                                const container = document.getElementById(`pdfContainer-${status}-${index}`);
                                const fileType = this.getAttribute('data-file-type');
                                const filePath = this.getAttribute('data-file-path');
                                // Set modal to highest z-index
                                modal.classList.add('modal-active');
                                modal.style.display = "block";
                                // Load PDF
                                if (typeof pdfjsLib !== 'undefined') {
                                    pdfjsLib.getDocument(filePath).promise.then(pdfDoc => {
                                        const key = `${status}-${index}`;
                                        self.pdfDocs[key] = pdfDoc;
                                        self.totalPages[key] = pdfDoc.numPages;
                                        self.currentPageNum[key] = 1;
                                        // Update page indicator
                                        const pageIndicator = document.getElementById(`pageIndicator-${status}-${index}`);
                                        if (pageIndicator) {
                                            pageIndicator.textContent = `Page 1 of ${self.totalPages[key]}`;
                                        }
                                        // Enable/disable navigation buttons
                                        const prevBtn = document.getElementById(`prevPageBtn-${status}-${index}`);
                                        const nextBtn = document.getElementById(`nextPageBtn-${status}-${index}`);
                                        if (prevBtn) prevBtn.disabled = true;
                                        if (nextBtn) nextBtn.disabled = self.totalPages[key] <= 1;
                                        // Render first page
                                        self.renderPage(status, index, 1);
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
                                }
                            });
                        }
                        // Page navigation functions for this PDF
                        const prevBtn = document.getElementById(`prevPageBtn-${status}-${index}`);
                        const nextBtn = document.getElementById(`nextPageBtn-${status}-${index}`);
                        if (prevBtn) {
                            prevBtn.addEventListener('click', function() {
                                self.goToPrevPage(status, index);
                            });
                        }
                        if (nextBtn) {
                            nextBtn.addEventListener('click', function() {
                                self.goToNextPage(status, index);
                            });
                        }
                        // Close modal when clicking outside content
                        const modal = document.getElementById(`file-modal-${status}-${index}`);
                        if (modal) {
                            modal.addEventListener("click", function(e) {
                                if (e.target === this) {
                                    window.closeDITFileModal(status, index);
                                }
                            });
                        }
                    }
                });
            },
            renderPage: function(status, index, pageNum) {
                const key = `${status}-${index}`;
                if (!this.pdfDocs[key]) return;
                const container = document.getElementById(`pdfContainer-${status}-${index}`);
                const modalBody = document.querySelector(`#file-modal-${status}-${index} .modal-body`);
                // Show loading indicator
                container.innerHTML = `
        <div class="loading-spinner"></div>
        <p class="text-center text-gray-600">Loading page ${pageNum}...</p>
    `;
                this.pdfDocs[key].getPage(pageNum).then(page => {
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
            },
            goToPrevPage: function(status, index) {
                const key = `${status}-${index}`;
                if (!this.pdfDocs[key]) return;
                if (this.currentPageNum[key] > 1) {
                    this.currentPageNum[key]--;
                    this.renderPage(status, index, this.currentPageNum[key]);
                    // Update navigation
                    const prevBtn = document.getElementById(`prevPageBtn-${status}-${index}`);
                    const nextBtn = document.getElementById(`nextPageBtn-${status}-${index}`);
                    const pageIndicator = document.getElementById(`pageIndicator-${status}-${index}`);
                    if (prevBtn) prevBtn.disabled = this.currentPageNum[key] === 1;
                    if (nextBtn) nextBtn.disabled = false;
                    if (pageIndicator) pageIndicator.textContent = `Page ${this.currentPageNum[key]} of ${this.totalPages[key]}`;
                }
            },
            goToNextPage: function(status, index) {
                const key = `${status}-${index}`;
                if (!this.pdfDocs[key]) return;
                if (this.currentPageNum[key] < this.totalPages[key]) {
                    this.currentPageNum[key]++;
                    this.renderPage(status, index, this.currentPageNum[key]);
                    // Update navigation
                    const prevBtn = document.getElementById(`prevPageBtn-${status}-${index}`);
                    const nextBtn = document.getElementById(`nextPageBtn-${status}-${index}`);
                    const pageIndicator = document.getElementById(`pageIndicator-${status}-${index}`);
                    if (prevBtn) prevBtn.disabled = false;
                    if (nextBtn) nextBtn.disabled = this.currentPageNum[key] === this.totalPages[key];
                    if (pageIndicator) pageIndicator.textContent = `Page ${this.currentPageNum[key]} of ${this.totalPages[key]}`;
                }
            },
            setupAnnouncementApproveRejectButtons: function() {
                const self = this;
                // Approve buttons for announcements
                document.querySelectorAll('.announcement-approve-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        self.approveAnnouncement(id, this);
                    });
                });
                // Reject buttons for announcements
                document.querySelectorAll('.announcement-reject-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        self.rejectAnnouncement(id);
                    });
                });
            },
            setupMemoApproveRejectButtons: function() {
                const self = this;
                // Approve buttons for memos
                document.querySelectorAll('.memo-approve-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        self.approveMemo(id, this);
                    });
                });
                // Reject buttons for memos
                document.querySelectorAll('.memo-reject-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        self.rejectMemo(id);
                    });
                });
            },
            setupGraphApproveRejectButtons: function() {
                // Graph approve/reject buttons are now handled in the initializeDITGraphs function
                // This function is kept for compatibility but can be removed if not needed
            },
            approveAnnouncement: function(id, buttonElement) {
                if (confirm('Are you sure you want to approve this announcement?')) {
                    // Show loading state
                    const originalText = buttonElement.innerHTML;
                    buttonElement.innerHTML = '<div class="loading-spinner"></div>';
                    buttonElement.disabled = true;

                    fetch('../DIT/DIT_Approve_Reject.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `id=${id}&action=approve`
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                alert('Announcement approved successfully!');
                                window.location.href = data.redirect;
                            } else {
                                alert('Error approving announcement: ' + data.message);
                                buttonElement.innerHTML = originalText;
                                buttonElement.disabled = false;
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while approving the announcement. Please check the console for details.');
                            buttonElement.innerHTML = originalText;
                            buttonElement.disabled = false;
                        });
                }
            },

            rejectAnnouncement: function(id) {
                this.currentRejectId = id;
                const modal = document.getElementById('reject-modal-announcement');
                if (modal) {
                    modal.style.display = 'flex';
                    const textarea = document.getElementById('reject-reason-announcement');
                    if (textarea) textarea.value = '';
                }
            },

            submitAnnouncementRejection: function(event) {
                if (!this.currentRejectId) {
                    alert('Error: No announcement selected for rejection');
                    return;
                }

                const reason = document.getElementById('reject-reason-announcement');
                if (!reason || !reason.value.trim()) {
                    alert('Please provide a reason for rejection');
                    return;
                }

                const submitBtn = event.target;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<div class="loading-spinner"></div>';
                submitBtn.disabled = true;

                fetch('../DIT/DIT_Approve_Reject.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${this.currentRejectId}&action=reject&reason=${encodeURIComponent(reason.value.trim())}`
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Announcement rejected successfully!');
                            const modal = document.getElementById('reject-modal-announcement');
                            if (modal) modal.style.display = 'none';
                            window.location.href = data.redirect;
                        } else {
                            alert('Error rejecting announcement: ' + data.message);
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while rejecting the announcement');
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
            },

            approveMemo: function(id, buttonElement) {
                if (confirm('Are you sure you want to approve this memo?')) {
                    // Show loading state
                    const originalText = buttonElement.innerHTML;
                    buttonElement.innerHTML = '<div class="loading-spinner"></div>';
                    buttonElement.disabled = true;

                    fetch('../DIT/DIT_Approve_Reject_Memo.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `id=${id}&action=approve`
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                alert('Memo approved successfully!');
                                window.location.href = data.redirect;
                            } else {
                                alert('Error approving memo: ' + data.message);
                                buttonElement.innerHTML = originalText;
                                buttonElement.disabled = false;
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while approving the memo. Please check the console for details.');
                            buttonElement.innerHTML = originalText;
                            buttonElement.disabled = false;
                        });
                }
            },

            rejectMemo: function(id) {
                this.currentMemoRejectId = id;
                const modal = document.getElementById('reject-modal-memo');
                if (modal) {
                    modal.style.display = 'flex';
                    const textarea = document.getElementById('reject-reason-memo');
                    if (textarea) textarea.value = '';
                }
            },

            submitMemoRejection: function(event) {
                if (!this.currentMemoRejectId) {
                    alert('Error: No memo selected for rejection');
                    return;
                }

                const reason = document.getElementById('reject-reason-memo');
                if (!reason || !reason.value.trim()) {
                    alert('Please provide a reason for rejection');
                    return;
                }

                const submitBtn = event.target;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<div class="loading-spinner"></div>';
                submitBtn.disabled = true;

                fetch('../DIT/DIT_Approve_Reject_Memo.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${this.currentMemoRejectId}&action=reject&reason=${encodeURIComponent(reason.value.trim())}`
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Memo rejected successfully!');
                            const modal = document.getElementById('reject-modal-memo');
                            if (modal) modal.style.display = 'none';
                            window.location.href = data.redirect;
                        } else {
                            alert('Error rejecting memo: ' + data.message);
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while rejecting the memo');
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
            },

            approveGraph: function(id, buttonElement) {
                if (confirm('Are you sure you want to approve this graph?')) {
                    // Show loading state
                    const originalText = buttonElement.innerHTML;
                    buttonElement.innerHTML = '<div class="loading-spinner"></div>';
                    buttonElement.disabled = true;

                    fetch('../DIT/DIT_Approve_Reject_Graph.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `id=${id}&action=approve`
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                alert('Graph approved successfully!');
                                window.location.href = data.redirect;
                            } else {
                                alert('Error approving graph: ' + data.message);
                                buttonElement.innerHTML = originalText;
                                buttonElement.disabled = false;
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while approving the graph. Please check the console for details.');
                            buttonElement.innerHTML = originalText;
                            buttonElement.disabled = false;
                        });
                }
            },

            rejectGraph: function(id) {
                this.currentGraphRejectId = id;
                const modal = document.getElementById('reject-modal-graph');
                if (modal) {
                    modal.style.display = 'flex';
                    const textarea = document.getElementById('reject-reason-graph');
                    if (textarea) textarea.value = '';
                }
            },

            submitGraphRejection: function(event) {
                if (!this.currentGraphRejectId) {
                    alert('Error: No graph selected for rejection');
                    return;
                }

                const reason = document.getElementById('reject-reason-graph');
                if (!reason || !reason.value.trim()) {
                    alert('Please provide a reason for rejection');
                    return;
                }

                const submitBtn = event.target;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<div class="loading-spinner"></div>';
                submitBtn.disabled = true;

                let body = `id=${this.currentGraphRejectId}&action=reject&reason=${encodeURIComponent(reason.value.trim())}`;
                
                if (this.currentGraphRejectIsGroup) {
                    body += `&isGroup=1&ids=${this.currentGraphRejectIds.join(',')}`;
                }

                fetch('../DIT/DIT_Approve_Reject_Graph.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: body
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Graph rejected successfully!');
                            const modal = document.getElementById('reject-modal-graph');
                            if (modal) modal.style.display = 'none';
                            window.location.href = data.redirect;
                        } else {
                            alert('Error rejecting graph: ' + data.message);
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while rejecting the graph');
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
            }
        };
        // Global functions to be called from HTML
        window.closeDITFileModal = function(status, index) {
            const modal = document.getElementById(`file-modal-${status}-${index}`);
            if (modal) {
                modal.classList.remove('modal-active');
                modal.style.display = "none";
            }
        };
        window.submitDITAnnouncementRejection = function(event) {
            window.DITPosts.submitAnnouncementRejection(event);
        };
        window.submitDITMemoRejection = function(event) {
            window.DITPosts.submitMemoRejection(event);
        };
        window.submitDITGraphRejection = function(event) {
            window.DITPosts.submitGraphRejection(event);
        };
        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            window.DITPosts.init();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
</body>

</html>