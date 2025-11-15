document.addEventListener('DOMContentLoaded', function () {
    // Set Announcements as active by default
    showTab('upload-announcements');
    
    // Sub-tab switching
    document.querySelectorAll('.upload-tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const tabId = this.getAttribute('data-tab');
            showTab(tabId);
        });
    });

    function showTab(tabId) {
        // Hide all content areas
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });

        // Show selected content
        const selectedTab = document.getElementById(tabId);
        if (selectedTab) {
            selectedTab.classList.add('active');
        }

        // Remove all active states from buttons
        document.querySelectorAll('.upload-tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // Add active to the matching button
        const activeBtn = document.querySelector(`[data-tab="${tabId}"]`);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }
    }
});