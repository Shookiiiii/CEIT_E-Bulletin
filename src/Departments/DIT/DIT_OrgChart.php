<?php
include '../../db.php';
function getMember($code, $conn)
{
    $stmt = $conn->prepare("SELECT * FROM DIT_Organization WHERE position_code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
function showBox($member, $position_code)
{
    if (!$member) {
        return "<div class='border border-orange-500 p-1 rounded-md bg-white shadow-md text-left h-[70px] w-[180px] ml-0.5 flex items-center space-x-2 mb-[-2px]'>
            <div class='h-12 w-12 rounded-full border border-orange-500 bg-gray-200 flex items-center justify-center text-gray-400 text-xs shadow-lg'>No Photo</div>
            <div class='text-[11px] leading-tight'>
                <strong>Full Name</strong>
                <p class='text-gray-600'>&mdash;</p>
                <div class='mt-1 flex space-x-1 text-[11px]'>
                    <button class='px-1.5 py-0.5 border text-blue-600 border-blue-600 hover:text-white hover:bg-blue-600 rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 edit-btn' 
                            data-id='0' 
                            data-name='' 
                            data-role='' 
                            data-photo='' 
                            data-position='$position_code' 
                            title='Edit'>
                        <i class=\"fas fa-pen\"></i> Edit
                    </button>
                </div>
            </div>
        </div>";
    }
    // Show delete button for all positions (including top positions)
    $deleteButton = "<button class='px-1.5 py-0.5 border text-red-600 border-red-600 hover:text-white hover:bg-red-600 rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 delete-btn' 
                    data-id='" . $member['id'] . "' 
                    title='Delete'>
                <i class=\"fas fa-trash\"></i> Delete
            </button>";
    return "<div class='border border-orange-500 p-1 rounded-md bg-white shadow-md text-left h-[70px] w-[180px] ml-0.5 flex items-center space-x-2 mb-[-2px]'>
        <img src='../OrganizationalChart/uploadDIT/" . htmlspecialchars($member['photo']) . "' class='h-12 w-12 rounded-full border border-orange-500 object-cover shadow-lg'>
        <div class='text-[11px] leading-tight'>
            <strong>" . htmlspecialchars($member['name']) . "</strong>
            <p class='text-gray-600'>" . htmlspecialchars($member['role']) . "</p>
            <div class='mt-1 flex space-x-1 text-[11px]'>
                <button class='px-1.5 py-0.5 border text-blue-600 border-blue-600 hover:text-white hover:bg-blue-600 rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 edit-btn' 
                        data-id='" . $member['id'] . "' 
                        data-name='" . htmlspecialchars($member['name']) . "' 
                        data-role='" . htmlspecialchars($member['role']) . "' 
                        data-photo='" . htmlspecialchars($member['photo']) . "' 
                        data-position='" . $member['position_code'] . "' 
                        title='Edit'>
                    <i class=\"fas fa-pen\"></i> Edit
                </button>
                $deleteButton
            </div>
        </div>
    </div>";
}
?>
<div class="container w-full max-w-7xl mx-auto px-4 py-6 text-center" id="orgChartContainer">
    <h4 class="text-xl md:text-2xl font-bold mb-5">DIT ORGANIZATIONAL STRUCTURE</h4>
    <div class="aspect-w-16 aspect-h-9">
        <div class="scale-wrapper">
            <div class="border-2 border-blue-300 rounded-xl p-4 md:p-6 bg-gradient-to-br from-blue-50 to-white shadow-lg">
                <!-- Top Management Section -->
                <div class="mb-8">
                    <div class="flex justify-end mb-4">
                        <button class="border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white text-sm px-3 py-1 rounded transition duration-200 transform hover:scale-110 add-top-position-btn">
                            <i class="fas fa-user-plus mr-1"></i> Add Position
                        </button>
                    </div>
                    <div class="border-t border-blue-200 pt-4">
                        <div class="flex flex-wrap justify-center gap-4 md:gap-6" id="topManagement">
                            <?php
                            // Get all top positions (those with position_code starting with 'top_')
                            $result = $conn->query("SELECT * FROM DIT_Organization WHERE position_code LIKE 'top_%' ORDER BY id ASC");
                            $hasTopPositions = $result->num_rows > 0;
                            
                            while ($row = $result->fetch_assoc()) {
                                echo "<div class='flex justify-center'>" . showBox($row, $row['position_code']) . "</div>";
                            }
                            
                            // If no top positions exist, show an empty slot
                            if (!$hasTopPositions) {
                                echo "<div class='flex justify-center'>" . showBox(null, 'top_1') . "</div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <!-- Divider Line -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                </div>
                
                <!-- Personnel Section with 4 Columns -->
                <div class="mt-8">
                    <div class="border-t border-orange-200 pt-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 justify-items-center" id="personnelGrid">
                            <?php
                            // Get all personnel excluding the top positions
                            $result = $conn->query("SELECT * FROM DIT_Organization WHERE position_code NOT LIKE 'top_%' ORDER BY id ASC");
                            
                            // Initialize 4 empty columns
                            $columns = [[], [], [], []];
                            $columnIndex = 0;
                            
                            // Distribute personnel across 4 columns
                            while ($row = $result->fetch_assoc()) {
                                // Check if position_code matches column pattern
                                if (preg_match('/^column_(\d+)$/', $row['position_code'], $matches)) {
                                    $colNum = intval($matches[1]) - 1; // Convert to 0-based index
                                    if ($colNum >= 0 && $colNum < 4) {
                                        $columns[$colNum][] = $row;
                                        continue;
                                    }
                                }
                                // If not assigned to a specific column, distribute evenly
                                $columns[$columnIndex][] = $row;
                                $columnIndex = ($columnIndex + 1) % 4;
                            }
                            
                            // Display each column
                            for ($i = 0; $i < 4; $i++) {
                                echo "<div class='flex flex-col items-center space-y-3 w-full border border-gray-200 rounded-lg p-4 bg-white shadow-sm' id='column-" . ($i + 1) . "'>";
                                
                                // Display personnel in this column
                                foreach ($columns[$i] as $member) {
                                    echo showBox($member, $member['position_code']);
                                }
                                
                                // Add personnel button for this column
                                echo "<button class='border border-green-600 text-green-600 hover:bg-green-600 hover:text-white text-sm px-3 py-1 rounded transition duration-200 transform hover:scale-110 add-personnel-btn mt-2' data-position='column_" . ($i + 1) . "'>
                                        <i class='fas fa-user-plus mr-1'></i> Add Personnel
                                      </button>";
                                echo "</div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal (unchanged) -->
<div id="memberModal" class="fixed inset-0 hidden z-50 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg w-full max-w-md shadow-2xl">
        <form id="memberForm" method="post" enctype="multipart/form-data">
            <h5 class="text-lg font-bold mb-4" id="modalTitle">Edit Member</h5>
            <input type="hidden" name="member_id" id="member_id">
            <div class="mb-3">
                <label class="block text-sm font-medium">Name</label>
                <input type="text" name="name" id="name" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium">Role</label>
                <input type="text" name="role" id="role" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <input type="hidden" name="position_code" id="position_code">
            <div class="mb-3">
                <label class="block text-sm font-medium">Photo</label>
                <input type="file" name="photo" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" id="photoInput">
                <div id="currentPhoto" class="mt-2 text-sm text-gray-500 hidden"></div>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-700 hover:bg-gray-700 hover:text-white rounded text-gray-700 transition duration-200 transform hover:scale-110">Cancel</button>
                <button type="submit" id="submitBtn" class="px-4 py-2 border border-green-600 bg-white text-green-600 rounded hover:bg-green-600 hover:text-white transition duration-200 transform hover:scale-110">
                    <i class="fas fa-save fa-sm"></i> Save
                </button>
            </div>
        </form>
    </div>
</div>
<!-- Notification Toast (unchanged) -->
<div id="notificationToast" class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg transform transition-transform duration-300 translate-y-20 opacity-0 flex items-center">
    <i class="fas fa-check-circle mr-2"></i>
    <span id="notificationMessage"></span>
</div>
<script>
    // Flag to check if form is already being submitted
    let isSubmitting = false;
    // Function to reload just the organizational chart
    function reloadOrgChart() {
        fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                // Create a temporary div to parse the HTML
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                // Get the updated org chart container
                const newOrgChartContainer = tempDiv.querySelector('#orgChartContainer');
                // Replace the current org chart with the updated one
                document.getElementById('orgChartContainer').innerHTML = newOrgChartContainer.innerHTML;
                // Re-attach event listeners
                attachEventListeners();
                // Show success notification
                showNotification('Changes saved successfully!');
            })
            .catch(error => {
                console.error('Error reloading organizational chart:', error);
                showNotification('Error saving changes', 'error');
            });
    }
    // Function to show notification
    function showNotification(message, type = 'success') {
        const toast = document.getElementById("notificationToast");
        const messageElement = document.getElementById("notificationMessage");
        // Set message
        messageElement.textContent = message;
        // Set background color based on type
        toast.className = "fixed bottom-4 right-4 px-4 py-2 rounded shadow-lg transform transition-transform duration-300 flex items-center";
        if (type === 'success') {
            toast.classList.add("bg-green-500", "text-white");
            toast.innerHTML = '<i class="fas fa-check-circle mr-2"></i><span id="notificationMessage">' + message + '</span>';
        } else if (type === 'error') {
            toast.classList.add("bg-red-500", "text-white");
            toast.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i><span id="notificationMessage">' + message + '</span>';
        }
        // Show toast
        toast.classList.remove("translate-y-20", "opacity-0");
        // Hide after 3 seconds
        setTimeout(() => {
            toast.classList.add("translate-y-20", "opacity-0");
        }, 3000);
    }
    // Open modal functions
    function openEditModal(id, name, role, photo, position_code) {
        document.getElementById("modalTitle").innerText = id === 0 ? "Add Member" : "Edit Member";
        document.getElementById("member_id").value = id;
        document.getElementById("name").value = name;
        document.getElementById("role").value = role;
        document.getElementById("position_code").value = position_code;
        const currentPhoto = document.getElementById("currentPhoto");
        if (photo) {
            currentPhoto.classList.remove("hidden");
            currentPhoto.innerHTML = `Current: ${photo}`;
        } else {
            currentPhoto.classList.add("hidden");
        }
        document.getElementById("memberModal").classList.remove("hidden");
    }
    function closeModal() {
        document.getElementById("memberModal").classList.add("hidden");
        // Reset submission flag when closing modal
        isSubmitting = false;
        // Reset submit button
        const submitBtn = document.getElementById("submitBtn");
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save fa-sm"></i> Save';
    }
    // Function to attach event listeners
    function attachEventListeners() {
        // Remove existing event listeners to prevent duplicates
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.removeEventListener('click', handleEditClick);
        });
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.removeEventListener('click', handleDeleteClick);
        });
        document.querySelectorAll('.add-personnel-btn').forEach(button => {
            button.removeEventListener('click', handleAddPersonnelClick);
        });
        const addTopPositionBtn = document.querySelector('.add-top-position-btn');
        if (addTopPositionBtn) {
            addTopPositionBtn.removeEventListener('click', handleAddTopPositionClick);
        }
        const memberForm = document.getElementById('memberForm');
        if (memberForm) {
            memberForm.removeEventListener('submit', handleFormSubmit);
        }
        
        // Add event listeners
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', handleEditClick);
        });
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', handleDeleteClick);
        });
        document.querySelectorAll('.add-personnel-btn').forEach(button => {
            button.addEventListener('click', handleAddPersonnelClick);
        });
        if (addTopPositionBtn) {
            addTopPositionBtn.addEventListener('click', handleAddTopPositionClick);
        }
        if (memberForm) {
            memberForm.addEventListener('submit', handleFormSubmit);
        }
    }
    
    // Event handler functions
    function handleEditClick() {
        const id = this.dataset.id;
        const name = this.dataset.name;
        const role = this.dataset.role;
        const photo = this.dataset.photo;
        const position = this.dataset.position;
        openEditModal(id, name, role, photo, position);
    }
    function handleDeleteClick() {
        const id = this.dataset.id;
        if (confirm("Delete this member?")) {
            fetch('../OrganizationalChart/DIT_OrgChart_process.php?delete=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        reloadOrgChart(); // Reload just the organizational chart
                    } else {
                        showNotification('Error deleting member', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred', 'error');
                });
        }
    }
    function handleAddPersonnelClick() {
        const position = this.dataset.position;
        openEditModal(0, '', '', '', position);
    }
    function handleAddTopPositionClick() {
        // Generate a unique position code for new top positions
        const position = 'top_' + Date.now();
        openEditModal(0, '', '', '', position);
    }
    function handleFormSubmit(e) {
        e.preventDefault();
        // Check if form is already being submitted
        if (isSubmitting) {
            return;
        }
        // Set submission flag
        isSubmitting = true;
        // Disable the submit button to prevent multiple submissions
        const submitBtn = document.getElementById("submitBtn");
        const originalButtonText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        const formData = new FormData(this);
        fetch('../OrganizationalChart/DIT_OrgChart_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal();
                    reloadOrgChart(); // Reload just the organizational chart
                } else {
                    showNotification('Error saving data: ' + (data.error || 'Unknown error'), 'error');
                    // Re-enable the button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalButtonText;
                    // Reset submission flag
                    isSubmitting = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred', 'error');
                // Re-enable the button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalButtonText;
                // Reset submission flag
                isSubmitting = false;
            });
    }
    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Initial attachment of event listeners
        attachEventListeners();
    });
</script>