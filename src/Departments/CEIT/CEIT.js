// CEIT.js
document.addEventListener('DOMContentLoaded', function () {
    // Check for tab parameter in URL
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    const mainTabParam = urlParams.get('main');
    const editParam = urlParams.get('edit');
    
    // Main tab switching
    document.querySelectorAll('.main-tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const isManage = this.id === 'manageBtn';
            // Toggle sections
            document.getElementById('manageContent').classList.toggle('hidden', !isManage);
            document.getElementById('uploadContent').classList.toggle('hidden', isManage);
            document.getElementById('manageSubmenu').classList.toggle('hidden', !isManage);
            document.getElementById('uploadSubmenu').classList.toggle('hidden', isManage);
            // Update page title
            document.getElementById('pageTitle').textContent = isManage
                ? 'Manage Department Posts'
                : 'Upload to CEIT Bulletin';
            
            // FIX: Remove active styles from all main buttons first
            document.querySelectorAll('.main-tab-btn').forEach(b => {
                b.classList.remove('bg-orange-600', 'font-bold', 'bg-orange-700');
            });
            
            // Add active style to the clicked button
            this.classList.add('bg-orange-600', 'font-bold');
            
            // Set the default active tab for each section
            const defaultTab = isManage ? 'DAFE' : 'upload-announcements';
            showTab(defaultTab);
            
            // Update URL with main tab state
            const url = new URL(window.location);
            url.searchParams.set('main', isManage ? 'manage' : 'upload');
            // Remove edit parameter when switching main tabs
            url.searchParams.delete('edit');
            window.history.pushState({}, '', url);
        });
    });
    
    // Sub-tab switching - FIXED to check for null elements
    document.querySelectorAll('.nav-btn, .upload-tab-btn').forEach(btn => {
        if (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const tabId = this.getAttribute('data-tab');
                if (tabId) { // Check if tabId exists
                    showTab(tabId);
                }
            });
        }
    });
    
    function showTab(tabId) {
        // Update URL without reloading the page
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        
        // Preserve main tab state in URL
        const isUploadActive = !document.getElementById('uploadContent').classList.contains('hidden');
        url.searchParams.set('main', isUploadActive ? 'upload' : 'manage');
        
        // Remove edit parameter when switching to a different tab
        // Only keep edit parameter if switching to the same tab type that was being edited
        if (editParam) {
            if ((editParam === 'enrollment' && tabId !== 'upload-enrollment') ||
                (editParam === 'licensure' && tabId !== 'upload-licensure')) {
                url.searchParams.delete('edit');
            }
        }
        
        window.history.pushState({}, '', url);
        
        // Hide all content areas
        document.querySelectorAll('.tab-content').forEach(tab => {
            if (tab) tab.classList.remove('active');
        });
        // Show selected content
        const selectedTab = document.getElementById(tabId);
        if (selectedTab) {
            selectedTab.classList.add('active');
            
            // Special handling for archive tab
            if (tabId === 'archive') {
                console.log('Archive tab activated');
            }
            // Initialize graphs when their tabs are shown
            else if (tabId === 'upload-graphs') {
                // Graph tab is now active
                console.log('Graph tab activated');
                // Resize charts after tab is shown
                setTimeout(() => {
                    if (typeof Chart !== 'undefined') {
                        // Check if we have the initializeChartsWhenTabIsShown function
                        if (typeof initializeChartsWhenTabIsShown === 'function') {
                            initializeChartsWhenTabIsShown();
                        } else {
                            // Fallback to just resizing existing charts
                            Chart.helpers.each(Chart.instances, function(instance) {
                                instance.resize();
                            });
                        }
                    }
                }, 100);
            } else if (tabId === 'upload-enrollment') {
                if (typeof initializeGraph2 === 'function') {
                    setTimeout(initializeGraph2, 100);
                }
            } else if (tabId === 'upload-licensure') {
                if (typeof initializeGraph3 === 'function') {
                    setTimeout(initializeGraph3, 100);
                }
            }
        }
        
        // Remove all active states
        document.querySelectorAll('.nav-btn, .upload-tab-btn').forEach(btn => {
            if (btn) btn.classList.remove('active');
        });
        // Add active to the matching button
        const activeBtn = document.querySelector(`[data-tab="${tabId}"]`);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }
    }
    
    // Initialize auto-resize for all textareas
    document.querySelectorAll('textarea').forEach(textarea => {
        autoResizeTextarea(textarea);
        textarea.addEventListener('input', function() {
            autoResizeTextarea(this);
        });
    });
    
    // Handle URL parameters after setting up event listeners
    if (mainTabParam === 'upload') {
        const uploadBtn = document.getElementById('uploadBtn');
        if (uploadBtn) uploadBtn.click();
    } else if (mainTabParam === 'manage') {
        const manageBtn = document.getElementById('manageBtn');
        if (manageBtn) manageBtn.click();
    } else {
        // Default to manage tab if no parameter
        const manageBtn = document.getElementById('manageBtn');
        if (manageBtn) manageBtn.click();
    }
    
    // Set active tab after main tab is set
    setTimeout(() => {
        if (tabParam) {
            showTab(tabParam);
        } else {
            showTab('DAFE');
        }
    }, 100);
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        const mainTab = urlParams.get('main');
        const edit = urlParams.get('edit');
        
        if (mainTab === 'upload') {
            const uploadBtn = document.getElementById('uploadBtn');
            if (uploadBtn) uploadBtn.click();
        } else if (mainTab === 'manage') {
            const manageBtn = document.getElementById('manageBtn');
            if (manageBtn) manageBtn.click();
        }
        
        if (tab) {
            // Only preserve edit parameter if it matches the current tab
            if ((edit === 'enrollment' && tab === 'upload-enrollment') ||
                (edit === 'licensure' && tab === 'upload-licensure')) {
                showTab(tab);
            } else {
                // Remove edit parameter from URL before showing tab
                const url = new URL(window.location);
                url.searchParams.delete('edit');
                window.history.replaceState({}, '', url);
                showTab(tab);
            }
        } else {
            showTab('DAFE');
        }
    });
});
// Auto-resize textarea function
function autoResizeTextarea(el) {
    if (el) {
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
    }
}
// Generic functions to enable editing and save content
function enableEdit(textareaId, editBtnId, saveBtnId) {
    const textarea = document.getElementById(textareaId);
    const editBtn = document.getElementById(editBtnId);
    const saveBtn = document.getElementById(saveBtnId);
    
    if (textarea) textarea.disabled = false;
    if (textarea) textarea.focus();
    if (editBtn) editBtn.classList.add('hidden');
    if (saveBtn) saveBtn.classList.remove('hidden');
}
function saveContent(textareaId, saveBtnId, messageDivId, paramName, actionName, successMessage) {
    const textarea = document.getElementById(textareaId);
    const messageDiv = document.getElementById(messageDivId);
    
    if (!textarea || !messageDiv) return;
    
    const content = textarea.value;
    
    // Show loading state
    messageDiv.textContent = "Saving...";
    messageDiv.classList.remove('hidden', 'text-green-600', 'text-red-600');
    
    fetch('CEIT_config.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `${paramName}=${encodeURIComponent(content)}&${actionName}=1`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(result => {
        if (result.trim() === 'success') {
            messageDiv.textContent = successMessage;
            messageDiv.classList.add('text-green-600');
            
            // Disable the textarea
            textarea.disabled = true;
            
            // Show edit button again
            const editBtnId = saveBtnId.replace('save', 'edit');
            const editBtn = document.getElementById(editBtnId);
            const saveBtn = document.getElementById(saveBtnId);
            
            if (editBtn) editBtn.classList.remove('hidden');
            if (saveBtn) saveBtn.classList.add('hidden');
            
            // Hide message after 3 seconds
            setTimeout(() => {
                messageDiv.classList.add('hidden');
            }, 3000);
        } else {
            throw new Error('Server returned: ' + result);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        messageDiv.textContent = "Error saving. Please try again.";
        messageDiv.classList.add('text-red-600');
    });
}
// Specific functions using the generic ones
function enableMissionEdit() {
    enableEdit('mission-textarea', 'mission-edit-btn', 'mission-save-btn');
}
function saveMission() {
    saveContent('mission-textarea', 'mission-save-btn', 'mission-message', 
               'mission_content', 'save_mission', 'Mission updated successfully!');
}
function enableVisionEdit() {
    enableEdit('vision-textarea', 'vision-edit-btn', 'vision-save-btn');
}
function saveVision() {
    saveContent('vision-textarea', 'vision-save-btn', 'vision-message', 
               'vision_content', 'save_vision', 'Vision updated successfully!');
}
function enableQPEdit() {
    enableEdit('qp-textarea', 'qp-edit-btn', 'qp-save-btn');
}
function saveQualityPolicy() {
    saveContent('qp-textarea', 'qp-save-btn', 'qp-message', 
               'quality_policy_content', 'save_quality_policy', 'Quality Policy updated successfully!');
}
function enableCGEdit() {
    enableEdit('cg-textarea', 'cg-edit-btn', 'cg-save-btn');
}
function saveCollegeGoals() {
    saveContent('cg-textarea', 'cg-save-btn', 'cg-message', 
               'college_goals_content', 'save_college_goals', 'College Goals updated successfully!');
}
function enableACEdit() {
    enableEdit('ac-textarea', 'ac-edit-btn', 'ac-save-btn');
}
function saveAboutCollege() {
    saveContent('ac-textarea', 'ac-save-btn', 'ac-message', 
               'about_college_content', 'save_about_college', 'About College updated successfully!');
}
function enablePOEdit() {
    enableEdit('po-textarea', 'po-edit-btn', 'po-save-btn');
}
function saveProgramOfferings() {
    saveContent('po-textarea', 'po-save-btn', 'po-message', 
               'program_offerings_content', 'save_program_offerings', 'Program Offerings updated successfully!');
}

