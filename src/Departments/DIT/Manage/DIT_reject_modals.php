<!-- Reject Reason Modal for Announcements -->
<div id="reject-modal-announcement" class="announcement-delete-modal">
    <div class="announcement-delete-modal-content">
        <div class="announcement-delete-modal-header">
            <h3 class="announcement-delete-modal-title">Reject Announcement</h3>
            <span class="announcement-delete-modal-close" onclick="document.getElementById('reject-modal-announcement').style.display='none'">&times;</span>
        </div>
        <div class="announcement-delete-modal-body">
            <p>Please provide a reason for rejection:</p>
            <textarea id="reject-reason-announcement" class="mt-2 w-full border border-gray-300 rounded-md p-2" rows="4"></textarea>
        </div>
        <div class="announcement-delete-modal-footer">
            <button type="button" onclick="document.getElementById('reject-modal-announcement').style.display='none'"
                class="px-4 py-2 border border-gray-300 text-gray-500 hover:bg-gray-500 hover:text-white font-medium rounded-lg shadow-sm transition duration-200 transform hover:scale-110">
                Cancel
            </button>
            <button type="button" onclick="window.submitDITAnnouncementRejection(event)"
                class="px-4 py-2 border border-red-500 text-red-500 hover:bg-red-500 hover:text-white font-medium rounded-lg shadow-sm transition duration-200 transform hover:scale-110">
                <i class="fas fa-times mr-2"></i> Reject
            </button>
        </div>
    </div>
</div>

<!-- Reject Reason Modal for Memos -->
<div id="reject-modal-memo" class="announcement-delete-modal">
    <div class="announcement-delete-modal-content">
        <div class="announcement-delete-modal-header">
            <h3 class="announcement-delete-modal-title">Reject Memo</h3>
            <span class="announcement-delete-modal-close" onclick="document.getElementById('reject-modal-memo').style.display='none'">&times;</span>
        </div>
        <div class="announcement-delete-modal-body">
            <p>Please provide a reason for rejection:</p>
            <textarea id="reject-reason-memo" class="mt-2 w-full border border-gray-300 rounded-md p-2" rows="4"></textarea>
        </div>
        <div class="announcement-delete-modal-footer">
            <button type="button" onclick="document.getElementById('reject-modal-memo').style.display='none'"
                class="px-4 py-2 border border-gray-300 text-gray-500 hover:bg-gray-500 hover:text-white font-medium rounded-lg shadow-sm transition duration-200 transform hover:scale-110">
                Cancel
            </button>
            <button type="button" onclick="window.submitDITMemoRejection(event)"
                class="px-4 py-2 border border-red-500 text-red-500 hover:bg-red-500 hover:text-white font-medium rounded-lg shadow-sm transition duration-200 transform hover:scale-110">
                <i class="fas fa-times mr-2"></i> Reject
            </button>
        </div>
    </div>
</div>

<!-- Reject Reason Modal for Graphs -->
<div id="reject-modal-graph" class="announcement-delete-modal">
    <div class="announcement-delete-modal-content">
        <div class="announcement-delete-modal-header">
            <h3 class="announcement-delete-modal-title">Reject Graph</h3>
            <span class="announcement-delete-modal-close" onclick="document.getElementById('reject-modal-graph').style.display='none'">&times;</span>
        </div>
        <div class="announcement-delete-modal-body">
            <p>Please provide a reason for rejection:</p>
            <textarea id="reject-reason-graph" class="mt-2 w-full border border-gray-300 rounded-md p-2" rows="4"></textarea>
        </div>
        <div class="announcement-delete-modal-footer">
            <button type="button" onclick="document.getElementById('reject-modal-graph').style.display='none'"
                class="px-4 py-2 border border-gray-300 text-gray-500 hover:bg-gray-500 hover:text-white font-medium rounded-lg shadow-sm transition duration-200 transform hover:scale-110">
                Cancel
            </button>
            <button type="button" onclick="window.submitDITGraphRejection(event)"
                class="px-4 py-2 border border-red-500 text-red-500 hover:bg-red-500 hover:text-white font-medium rounded-lg shadow-sm transition duration-200 transform hover:scale-110">
                <i class="fas fa-times mr-2"></i> Reject
            </button>
        </div>
    </div>
</div>