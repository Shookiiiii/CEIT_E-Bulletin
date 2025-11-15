document.addEventListener('DOMContentLoaded', function () {
    // Check for tab parameter in URL
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    
    // Sub-tab switching
    document.querySelectorAll('.upload-tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const tabId = this.getAttribute('data-tab');
            showTab(tabId);
        });
    });
    
    function showTab(tabId) {
        // Update URL without reloading the page
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.pushState({}, '', url);
        
        // Hide all content areas
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        // Show selected content
        const selectedTab = document.getElementById(tabId);
        if (selectedTab) {
            selectedTab.classList.add('active');
        }
        
        // Remove all active states
        document.querySelectorAll('.upload-tab-btn').forEach(btn => {
            btn.classList.remove('active');
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
    
    // Set active tab after main tab is set
    setTimeout(() => {
        if (tabParam) {
            showTab(tabParam);
        } else {
            showTab('upload-announcements');
        }
    }, 100);
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        
        if (tab) {
            showTab(tab);
        } else {
            showTab('upload-announcements');
        }
    });
});

// Auto-resize textarea function
function autoResizeTextarea(el) {
    el.style.height = 'auto';
    el.style.height = el.scrollHeight + 'px';
}

// Show notification function
function showNotification(message, type = 'success') {
    // Create notification element if it doesn't exist
    let notification = document.getElementById('notification');
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'notification';
        notification.className = 'notification';
        document.body.appendChild(notification);
    }
    
    notification.textContent = message;
    notification.className = 'notification ' + type;
    notification.classList.add('show');
    
    setTimeout(() => {
        notification.classList.remove('show');
    }, 3000);
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
    
    fetch('DIT_config.php', {
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
            
            // Show notification
            showNotification(successMessage);
            
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
        
        // Show error notification
        showNotification("Error saving. Please try again.", 'error');
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

function enableADEdit() {
    enableEdit('ad-textarea', 'ad-edit-btn', 'ad-save-btn');
}

function saveAboutDepartment() {
    saveContent('ad-textarea', 'ad-save-btn', 'ad-message', 
               'about_department_content', 'save_about_department', 'About the Department updated successfully!');
}

function enablePOEdit() {
    enableEdit('po-textarea', 'po-edit-btn', 'po-save-btn');
}

function saveProgramOfferings() {
    saveContent('po-textarea', 'po-save-btn', 'po-message', 
               'program_offerings_content', 'save_program_offerings', 'Program Offerings updated successfully!');
}

function enableCVEdit() {
    enableEdit('cv-textarea', 'cv-edit-btn', 'cv-save-btn');
}

function saveCoreValues() {
    saveContent('cv-textarea', 'cv-save-btn', 'cv-message', 
               'core_values_content', 'save_core_values', 'Core Values updated successfully!');
}