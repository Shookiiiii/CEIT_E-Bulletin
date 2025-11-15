<?php
// We need to include the data arrays here for the modals
// Get announcements data
$pending = [];
$query = "SELECT * FROM DIT_post WHERE title='announcement' AND status='pending'";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $pending[] = [
        'file_path' => '../DIT/uploads/' . $row['file_path'],
        'content' => $row['content'],
        'description' => $row['content'],
        'id' => $row['id'],
        'posted_on' => $row['created_at'] ?? date('Y-m-d H:i:s')
    ];
}

$approved = [];
$query = "SELECT * FROM DIT_post WHERE title='announcement' AND status='approved'";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $approved[] = [
        'file_path' => '../DIT/uploads/' . $row['file_path'],
        'content' => $row['content'],
        'description' => $row['content'],
        'id' => $row['id'],
        'posted_on' => $row['created_at'] ?? date('Y-m-d H:i:s')
    ];
}

$not_approved = [];
$query = "SELECT * FROM DIT_post WHERE title='announcement' AND status='not approved'";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $not_approved[] = [
        'file_path' => '../DIT/uploads/' . $row['file_path'],
        'content' => $row['content'],
        'description' => $row['description'],
        'id' => $row['id'],
        'posted_on' => $row['created_at'] ?? date('Y-m-d H:i:s')
    ];
}

// Get memos data
$pending_memos = [];
$query = "SELECT * FROM DIT_post WHERE title='memo' AND status='pending'";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $pending_memos[] = [
        'file_path' => '../DIT/uploads/' . $row['file_path'],
        'content' => $row['content'],
        'description' => $row['content'],
        'id' => $row['id'],
        'posted_on' => $row['created_at'] ?? date('Y-m-d H:i:s')
    ];
}

$approved_memos = [];
$query = "SELECT * FROM DIT_post WHERE title='memo' AND status='approved'";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $approved_memos[] = [
        'file_path' => '../DIT/uploads/' . $row['file_path'],
        'content' => $row['content'],
        'description' => $row['content'],
        'id' => $row['id'],
        'posted_on' => $row['created_at'] ?? date('Y-m-d H:i:s')
    ];
}

$not_approved_memos = [];
$query = "SELECT * FROM DIT_post WHERE title='memo' AND status='not approved'";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $not_approved_memos[] = [
        'file_path' => '../DIT/uploads/' . $row['file_path'],
        'content' => $row['content'],
        'description' => $row['description'],
        'id' => $row['id'],
        'posted_on' => $row['created_at'] ?? date('Y-m-d H:i:s')
    ];
}
?>

<!-- File View Modals for Announcements -->
<?php foreach ($approved as $index => $pdf): ?>
    <div id="file-modal-approved-<?= $index ?>" class="file-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?= htmlspecialchars($pdf['description']) ?></h3>
                <span class="modal-close" onclick="window.closeDITFileModal('approved', <?= $index ?>)">&times;</span>
            </div>
            <div class="modal-body">
                <div id="pdfContainer-approved-<?= $index ?>" class="pdf-container">
                    <div class="loading-spinner"></div>
                    <p class="text-center text-gray-600">Loading announcement...</p>
                </div>
            </div>
            <div class="modal-footer">
                <div class="modal-meta">
                    Posted on: <?= date('F j, Y', strtotime($pdf['posted_on'])) ?> | File: <?= basename($pdf['file_path']) ?>
                </div>
                <div class="page-navigation">
                    <button id="prevPageBtn-approved-<?= $index ?>" class="page-nav-btn" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="pageIndicator-approved-<?= $index ?>" class="page-indicator">Page 1 of 1</div>
                    <button id="nextPageBtn-approved-<?= $index ?>" class="page-nav-btn" disabled>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php foreach ($pending as $index => $pdf): ?>
    <div id="file-modal-pending-<?= $index ?>" class="file-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?= htmlspecialchars($pdf['description']) ?></h3>
                <span class="modal-close" onclick="window.closeDITFileModal('pending', <?= $index ?>)">&times;</span>
            </div>
            <div class="modal-body">
                <div id="pdfContainer-pending-<?= $index ?>" class="pdf-container">
                    <div class="loading-spinner"></div>
                    <p class="text-center text-gray-600">Loading announcement...</p>
                </div>
            </div>
            <div class="modal-footer">
                <div class="modal-meta">
                    Posted on: <?= date('F j, Y', strtotime($pdf['posted_on'])) ?> | File: <?= basename($pdf['file_path']) ?>
                </div>
                <div class="page-navigation">
                    <button id="prevPageBtn-pending-<?= $index ?>" class="page-nav-btn" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="pageIndicator-pending-<?= $index ?>" class="page-indicator">Page 1 of 1</div>
                    <button id="nextPageBtn-pending-<?= $index ?>" class="page-nav-btn" disabled>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php foreach ($not_approved as $index => $pdf): ?>
    <div id="file-modal-not-approved-<?= $index ?>" class="file-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?= htmlspecialchars($pdf['description']) ?></h3>
                <span class="modal-close" onclick="window.closeDITFileModal('not-approved', <?= $index ?>)">&times;</span>
            </div>
            <div class="modal-body">
                <div id="pdfContainer-not-approved-<?= $index ?>" class="pdf-container">
                    <div class="loading-spinner"></div>
                    <p class="text-center text-gray-600">Loading announcement...</p>
                </div>
            </div>
            <div class="modal-footer">
                <div class="modal-meta">
                    Posted on: <?= date('F j, Y', strtotime($pdf['posted_on'])) ?> | File: <?= basename($pdf['file_path']) ?>
                </div>
                <div class="page-navigation">
                    <button id="prevPageBtn-not-approved-<?= $index ?>" class="page-nav-btn" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="pageIndicator-not-approved-<?= $index ?>" class="page-indicator">Page 1 of 1</div>
                    <button id="nextPageBtn-not-approved-<?= $index ?>" class="page-nav-btn" disabled>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- File View Modals for Memos -->
<?php foreach ($approved_memos as $index => $pdf): ?>
    <div id="file-modal-approved-memo-<?= $index ?>" class="file-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?= htmlspecialchars($pdf['description']) ?></h3>
                <span class="modal-close" onclick="window.closeDITFileModal('approved-memo', <?= $index ?>)">&times;</span>
            </div>
            <div class="modal-body">
                <div id="pdfContainer-approved-memo-<?= $index ?>" class="pdf-container">
                    <div class="loading-spinner"></div>
                    <p class="text-center text-gray-600">Loading memo...</p>
                </div>
            </div>
            <div class="modal-footer">
                <div class="modal-meta">
                    Posted on: <?= date('F j, Y', strtotime($pdf['posted_on'])) ?> | File: <?= basename($pdf['file_path']) ?>
                </div>
                <div class="page-navigation">
                    <button id="prevPageBtn-approved-memo-<?= $index ?>" class="page-nav-btn" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="pageIndicator-approved-memo-<?= $index ?>" class="page-indicator">Page 1 of 1</div>
                    <button id="nextPageBtn-approved-memo-<?= $index ?>" class="page-nav-btn" disabled>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php foreach ($pending_memos as $index => $pdf): ?>
    <div id="file-modal-pending-memo-<?= $index ?>" class="file-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?= htmlspecialchars($pdf['description']) ?></h3>
                <span class="modal-close" onclick="window.closeDITFileModal('pending-memo', <?= $index ?>)">&times;</span>
            </div>
            <div class="modal-body">
                <div id="pdfContainer-pending-memo-<?= $index ?>" class="pdf-container">
                    <div class="loading-spinner"></div>
                    <p class="text-center text-gray-600">Loading memo...</p>
                </div>
            </div>
            <div class="modal-footer">
                <div class="modal-meta">
                    Posted on: <?= date('F j, Y', strtotime($pdf['posted_on'])) ?> | File: <?= basename($pdf['file_path']) ?>
                </div>
                <div class="page-navigation">
                    <button id="prevPageBtn-pending-memo-<?= $index ?>" class="page-nav-btn" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="pageIndicator-pending-memo-<?= $index ?>" class="page-indicator">Page 1 of 1</div>
                    <button id="nextPageBtn-pending-memo-<?= $index ?>" class="page-nav-btn" disabled>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php foreach ($not_approved_memos as $index => $pdf): ?>
    <div id="file-modal-not-approved-memo-<?= $index ?>" class="file-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?= htmlspecialchars($pdf['description']) ?></h3>
                <span class="modal-close" onclick="window.closeDITFileModal('not-approved-memo', <?= $index ?>)">&times;</span>
            </div>
            <div class="modal-body">
                <div id="pdfContainer-not-approved-memo-<?= $index ?>" class="pdf-container">
                    <div class="loading-spinner"></div>
                    <p class="text-center text-gray-600">Loading memo...</p>
                </div>
            </div>
            <div class="modal-footer">
                <div class="modal-meta">
                    Posted on: <?= date('F j, Y', strtotime($pdf['posted_on'])) ?> | File: <?= basename($pdf['file_path']) ?>
                </div>
                <div class="page-navigation">
                    <button id="prevPageBtn-not-approved-memo-<?= $index ?>" class="page-nav-btn" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="pageIndicator-not-approved-memo-<?= $index ?>" class="page-indicator">Page 1 of 1</div>
                    <button id="nextPageBtn-not-approved-memo-<?= $index ?>" class="page-nav-btn" disabled>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>