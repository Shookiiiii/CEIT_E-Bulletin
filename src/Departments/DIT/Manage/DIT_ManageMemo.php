<?php
// Get pending memos
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

// Get approved memos
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

// Get not approved memos
$not_approved_memos = [];
$query = "SELECT * FROM DIT_post WHERE title='memo' AND status='not approved'";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $not_approved_memos[] = [
        'file_path' => '../DIT/uploads/' . $row['file_path'],
        'content' => $row['content'], // This contains the rejection reason
        'description' => $row['description'], // This contains the original content
        'id' => $row['id'],
        'posted_on' => $row['created_at'] ?? date('Y-m-d H:i:s')
    ];
}
?>

<h2 class="text-xl font-semibold text-orange-600 text-center mb-4">DIT Memo Updates</h2>

<!-- Pending Memos (for approval/rejection) -->
<div class="status-section pending">
    <h2 class="status-title">Pending Memos</h2>
    <?php if (count($pending_memos) > 0): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($pending_memos as $index => $pdf): ?>
                <div class="bg-white shadow-md rounded-lg p-4 w-full h-full flex flex-col justify-between border border-yellow-500 transition duration-200 transform hover:scale-105">
                    <div class="mb-3 border border-gray-300 rounded">
                        <div id="file-preview-pending-memo-<?= $index ?>" class="file-preview">
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
                    <div class="flex justify-end mt-4 space-x-2 text-xs">
                        <button id="view-full-pending-memo-<?= $index ?>" class="p-2 border rounded-lg border-gray-500 text-gray-500 hover:bg-gray-500 hover:text-white transition duration-200 transform hover:scale-110" title="View Full Document" data-file-type="pdf" data-file-path="<?= $pdf['file_path'] ?>">
                            <i class="fas fa-eye fa-sm"></i>
                            View
                        </button>
                        <button class="p-2 border border-green-500 text-green-500 rounded-lg hover:bg-green-500 hover:text-white transition duration-200 transform hover:scale-110 memo-approve-btn" data-id="<?= $pdf['id'] ?>" title="Approve">
                            <i class="fas fa-check fa-sm"></i>
                            Approve
                        </button>
                        <button class="p-2 border border-red-500 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition duration-200 transform hover:scale-110 memo-reject-btn" data-id="<?= $pdf['id'] ?>" title="Reject">
                            <i class="fas fa-times fa-sm"></i>
                            Reject
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-inbox fa-3x mb-4"></i>
            <p class="text-lg">No pending memos</p>
        </div>
    <?php endif; ?>
</div>

<!-- Approved Memos -->
<div class="status-section approved">
    <h2 class="status-title">Approved Memos</h2>
    <?php if (count($approved_memos) > 0): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($approved_memos as $index => $pdf): ?>
                <div class="bg-white shadow-md rounded-lg p-4 w-full h-full flex flex-col justify-between border border-green-500 transition duration-200 transform hover:scale-105">
                    <div class="mb-3 border border-gray-300 rounded">
                        <div id="file-preview-approved-memo-<?= $index ?>" class="file-preview">
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
                    <div class="flex justify-end mt-4 space-x-2 text-xs">
                        <button id="view-full-approved-memo-<?= $index ?>" class="p-2 border rounded-lg border-gray-500 text-gray-500 hover:bg-gray-500 hover:text-white transition duration-200 transform hover:scale-110" title="View Full Document" data-file-type="pdf" data-file-path="<?= $pdf['file_path'] ?>">
                            <i class="fas fa-eye fa-sm"></i>
                            View
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-inbox fa-3x mb-4"></i>
            <p class="text-lg">No approved memos yet</p>
        </div>
    <?php endif; ?>
</div>

<!-- Not Approved Memos -->
<div class="status-section not-approved">
    <h2 class="status-title">Not Approved Memos</h2>
    <?php if (count($not_approved_memos) > 0): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($not_approved_memos as $index => $pdf): ?>
                <div class="bg-white shadow-md rounded-lg p-4 w-full h-full flex flex-col justify-between border border-red-500 transition duration-200 transform hover:scale-105">
                    <div class="mb-3 border border-gray-300 rounded">
                        <div id="file-preview-not-approved-memo-<?= $index ?>" class="file-preview">
                            <div class="loading-spinner"></div>
                        </div>
                    </div>
                    <div class="card-body flex-grow">
                        <div class="file-title font-semibold text-gray-800 text-lg mb-1 truncate">
                            <?= htmlspecialchars($pdf['description']) ?>
                        </div>
                        <p class="card-text text-gray-600 text-sm overflow-hidden">
                            Reason: <?= htmlspecialchars($pdf['content']) ?>
                        </p>
                        <p class="card-text text-gray-600 text-sm truncate">
                            <?= basename($pdf['file_path']) ?>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Posted on: <?= date('F j, Y', strtotime($pdf['posted_on'])) ?>
                        </p>
                    </div>
                    <div class="flex justify-end mt-4 space-x-2 text-xs">
                        <button id="view-full-not-approved-memo-<?= $index ?>" class="p-2 border rounded-lg border-gray-500 text-gray-500 hover:bg-gray-500 hover:text-white transition duration-200 transform hover:scale-110" title="View Full Document" data-file-type="pdf" data-file-path="<?= $pdf['file_path'] ?>">
                            <i class="fas fa-eye fa-sm"></i>
                            View
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-inbox fa-3x mb-4"></i>
            <p class="text-lg">No not approved memos</p>
        </div>
    <?php endif; ?>
</div>