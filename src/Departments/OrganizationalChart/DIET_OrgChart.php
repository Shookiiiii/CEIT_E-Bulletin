<?php
include '../../db.php';

function getInitials($name) {
    $prefixes = ['Mr.', 'Mrs.', 'Ms.', 'Dr.', 'Prof.'];
    
    $words = explode(' ', trim($name));
    $filteredWords = [];
    
    foreach ($words as $word) {
        if (!in_array($word, $prefixes)) {
            $filteredWords[] = $word;
        }
    }
    
    if (count($filteredWords) >= 2) {
        $firstName = $filteredWords[0];
        $lastName = end($filteredWords);
        return strtoupper(substr($firstName, 0, 1)) . strtoupper(substr($lastName, 0, 1));
    }
    else if (count($filteredWords) == 1) {
        $word = $filteredWords[0];
        return strtoupper(substr($word, 0, min(2, strlen($word))));
    }
    
    return '';
}

function showBox($member, $position_code, $role = '')
{
    if (!$member) {
        $displayRole = !empty($role) ? $role : $position_code;
        
        return "<div class='border border-maroon-500 p-1 rounded-md bg-white shadow-md text-left h-[70px] w-[220px] flex items-center space-x-2 mb-2'>
            <div class='h-12 w-12 rounded-full flex items-center justify-center text-white font-bold text-xs shadow-lg' style='background-color: #700000;'>NA</div>
            <div class='text-[11px] leading-tight'>
                <strong>Full Name</strong>
                <p class='text-gray-600'>" . htmlspecialchars($displayRole) . "</p>
                <div class='mt-1 flex space-x-1 text-[11px]'>
                    <button class='px-1.5 py-0.5 border text-maroon-600 border-maroon-500 hover:text-white hover:bg-maroon-600 rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 edit-btn' 
                            data-id='0' 
                            data-name='' 
                            data-role='" . htmlspecialchars($displayRole) . "' 
                            data-photo='' 
                            data-position='$position_code' 
                            title='Edit'>
                        <i class=\"fas fa-pen\"></i> Edit
                    </button>
                </div>
            </div>
        </div>";
    }
    
    $deleteButton = "<button class='px-1.5 py-0.5 border text-red-600 border-red-600 hover:text-white hover:bg-red-600 rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 delete-btn' 
                    data-id='" . $member['id'] . "' 
                    title='Delete'>
                <i class=\"fas fa-trash\"></i> Delete
            </button>";
    
    $photoElement = '';
    if (!empty($member['photo'])) {
        $photoElement = "<img src='../OrganizationalChart/uploadDIET/" . htmlspecialchars($member['photo']) . "' class='h-12 w-12 rounded-full border border-maroon-500 object-cover shadow-lg'>";
    } else {
        $initials = getInitials($member['name']);
        $photoElement = "<div class='h-12 w-12 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-lg' style='background-color: #700000;'>$initials</div>";
    }
    
    return "<div class='border border-maroon-500 p-1 rounded-md bg-white shadow-md text-left h-[70px] w-[220px] flex items-center space-x-2 mb-2'>
        $photoElement
        <div class='text-[11px] leading-tight'>
            <strong>" . htmlspecialchars($member['name']) . "</strong>
            <p class='text-gray-600'>" . htmlspecialchars($member['role']) . "</p>
            <div class='mt-1 flex space-x-1 text-[11px]'>
                <button class='px-1.5 py-0.5 border border-maroon-500 text-maroon-600  hover:text-white hover:bg-maroon-600 rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 edit-btn' 
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
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DIET Organizational Chart</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
    :root {
        --maroon-500: #800000;
        --maroon-600: #700000;
        --maroon-700: #600000;
    }
    .border-maroon-500 {
        border-color: var(--maroon-500);
    }
    .text-maroon-600 {
        color: var(--maroon-600);
    }
    .hover\:bg-maroon-600:hover {
        background-color: var(--maroon-600);
    }
    .bg-maroon-50 {
        background-color: rgba(128, 0, 0, 0.05);
    }
    .border-maroon-300 {
        border-color: rgba(128, 0, 0, 0.3);
    }
    .bg-maroon-600 {
        background-color: var(--maroon-600);
    }
</style>
<div class="container w-[1150px] mx-auto px-4 py-6 text-center" id="orgChartContainer">
    <div class="aspect-w-16 aspect-h-9">
        <div class="scale-wrapper">
            <div class="border-2 border-maroon-300 rounded-xl p-6 bg-red-50 shadow-lg space-y-8">
                <div>
                    <div class="flex flex-col items-center gap-4" id="topManagementContainer">
                        <?php
                        $positions = [
                            ['code' => 'president', 'title' => 'President, CVSU'],
                            ['code' => 'vice_president', 'title' => 'Vice President, OVPAA'],
                            ['code' => 'dean', 'title' => 'Dean, CEIT'],
                            ['code' => 'chairperson', 'title' => 'Chairperson, DIET']
                        ];
                        
                        foreach ($positions as $pos) {
                            $stmt = $conn->prepare("SELECT * FROM DIET_Organization WHERE position_code = ? LIMIT 1");
                            $stmt->bind_param("s", $pos['code']);
                            $stmt->execute();
                            $member = $stmt->get_result()->fetch_assoc();
                            
                            echo "<div class='flex flex-col items-center'>";
                            if ($member) {
                                echo showBox($member, $pos['code']);
                            } else {
                                echo showBox(null, $pos['code'], $pos['title']);
                            }
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
                
                <div>
                    <h5 class="text-lg font-semibold mb-4 text-maroon-600">Coordinators</h5>
                    <div class="grid grid-cols-4 gap-6 justify-items-center">
                        <?php
                        // Define coordinator positions with editable codes
                        $coordinatorPositions = [
                            ['code' => 'coordinator_cs', 'title' => 'BSIE Coordinator'],
                            ['code' => 'coordinator_it', 'title' => 'BSIndt-AT Coordinator'],
                            ['code' => 'coordinator_is', 'title' => 'BSIndt-ET Coordinator'],
                            ['code' => 'coordinator_em', 'title' => 'BSIndt-EX Coordinator']
                        ];
                        
                        foreach ($coordinatorPositions as $position) {
                            $position_code = $position['code'];
                            $title = $position['title'];
                            $stmt = $conn->prepare("SELECT * FROM DIET_Organization WHERE position_code = ? LIMIT 1");
                            $stmt->bind_param("s", $position_code);
                            $stmt->execute();
                            $member = $stmt->get_result()->fetch_assoc();
                            
                            echo "<div class='flex flex-col items-center w-full'>";
                            if ($member) {
                                echo showBox($member, $position_code);
                            } else {
                                echo showBox(null, $position_code, $title);
                            }
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
                
                <div>
                    <h5 class="text-lg font-semibold mb-4 text-maroon-600">Faculty</h5>
                    <div class="grid grid-cols-4 gap-6 justify-items-center">
                        <?php
                        for ($i = 1; $i <= 4; $i++) {
                            echo "<div class='flex flex-col items-center w-full border border-maroon-500 rounded-lg p-4 bg-white shadow-sm'>";
                            
                            $result = $conn->query("SELECT * FROM DIET_Organization WHERE position_code LIKE 'faculty_" . $i . "_%' ORDER BY id ASC");
                            
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo showBox($row, $row['position_code']);
                                }
                            } else {
                                echo "<div class='text-gray-500 italic text-sm mb-2'>No faculty added yet</div>";
                            }
                            
                            // Modified: Changed data-position to only include faculty group number, not timestamp
                            echo "<button class='border border-maroon-500 text-maroon-600 hover:bg-maroon-600 hover:text-white text-sm px-3 py-1 rounded transition duration-200 transform hover:scale-110 add-faculty-btn mt-2' data-position='faculty_" . $i . "'>
                                    <i class='fas fa-user-plus mr-1'></i> Add Faculty
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
<div id="memberModal" class="fixed inset-0 hidden z-50 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg w-full max-w-md shadow-2xl">
        <form id="memberForm" method="post" enctype="multipart/form-data">
            <h5 class="text-lg font-bold mb-4 text-maroon-600" id="modalTitle">Edit Member</h5>
            <input type="hidden" name="member_id" id="member_id">
            <input type="hidden" name="position_code" id="position_code">
            <div class="mb-3">
                <label class="block text-sm font-medium text-maroon-600">Name</label>
                <input type="text" name="name" id="name" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-maroon-500" required>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-maroon-600">Role</label>
                <input type="text" name="role" id="role" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-maroon-500" required>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-maroon-600">Photo</label>
                <input type="file" name="photo" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-maroon-500" id="photoInput">
                <div id="currentPhoto" class="mt-2 text-sm text-gray-500 hidden"></div>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-700 hover:bg-gray-700 hover:text-white rounded text-gray-700 transition duration-200 transform hover:scale-110">Cancel</button>
                <button type="submit" id="submitBtn" class="px-4 py-2 border border-maroon-600 bg-white text-maroon-600 rounded hover:bg-maroon-600 hover:text-white transition duration-200 transform hover:scale-110">
                    <i class="fas fa-save fa-sm"></i> Save
                </button>
            </div>
        </form>
    </div>
</div>
<div id="notificationToast" class="fixed bottom-4 right-4 bg-maroon-600 text-white px-4 py-2 rounded shadow-lg transform transition-transform duration-300 translate-y-20 opacity-0 flex items-center">
    <i class="fas fa-check-circle mr-2"></i>
    <span id="notificationMessage"></span>
</div>
<script>
    let isSubmitting = false;
    
    function reloadOrgChart() {
        fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                const newOrgChartContainer = tempDiv.querySelector('#orgChartContainer');
                document.getElementById('orgChartContainer').innerHTML = newOrgChartContainer.innerHTML;
                attachEventListeners();
                showNotification('Changes saved successfully!');
            })
            .catch(error => {
                console.error('Error reloading organizational chart:', error);
                showNotification('Error saving changes', 'error');
            });
    }
    function showNotification(message, type = 'success') {
        const toast = document.getElementById("notificationToast");
        const messageElement = document.getElementById("notificationMessage");
        messageElement.textContent = message;
        toast.className = "fixed bottom-4 right-4 px-4 py-2 rounded shadow-lg transform transition-transform duration-300 flex items-center";
        if (type === 'success') {
            toast.classList.add("bg-maroon-600", "text-white");
            toast.innerHTML = '<i class="fas fa-check-circle mr-2"></i><span id="notificationMessage">' + message + '</span>';
        } else {
            toast.classList.add("bg-red-500", "text-white");
            toast.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i><span id="notificationMessage">' + message + '</span>';
        }
        toast.classList.remove("translate-y-20", "opacity-0");
        setTimeout(() => {
            toast.classList.add("translate-y-20", "opacity-0");
        }, 3000);
    }
    function openEditModal(id, name, role, photo, position_code) {
        document.getElementById("modalTitle").innerText = id === 0 ? "Add Member" : "Edit Member";
        document.getElementById("member_id").value = id;
        document.getElementById("name").value = name;
        document.getElementById("role").value = role;
        document.getElementById("position_code").value = position_code;
        
        const roleInput = document.getElementById("role");
        
        // Always make the role field editable for all positions
        roleInput.readOnly = false;
        roleInput.classList.remove('bg-gray-100');
        
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
        isSubmitting = false;
        const submitBtn = document.getElementById("submitBtn");
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save fa-sm"></i> Save';
        
        const roleInput = document.getElementById("role");
        roleInput.readOnly = false;
        roleInput.classList.remove('bg-gray-100');
    }
    function attachEventListeners() {
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.removeEventListener('click', handleEditClick);
            button.addEventListener('click', handleEditClick);
        });
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.removeEventListener('click', handleDeleteClick);
            button.addEventListener('click', handleDeleteClick);
        });
        document.querySelectorAll('.add-faculty-btn').forEach(button => {
            button.removeEventListener('click', handleAddFacultyClick);
            button.addEventListener('click', handleAddFacultyClick);
        });
        
        const memberForm = document.getElementById('memberForm');
        if (memberForm) {
            memberForm.removeEventListener('submit', handleFormSubmit);
            memberForm.addEventListener('submit', handleFormSubmit);
        }
    }
    function handleEditClick() {
        openEditModal(this.dataset.id, this.dataset.name, this.dataset.role, this.dataset.photo, this.dataset.position);
    }
    function handleDeleteClick() {
        const id = this.dataset.id;
        if (confirm("Delete this member?")) {
            fetch('../OrganizationalChart/DIET_OrgChart_process.php?delete=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        reloadOrgChart();
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
    function handleAddFacultyClick() {
        const position = this.dataset.position + '_' + Date.now();
        openEditModal(0, '', '', '', position);
    }
    function handleFormSubmit(e) {
        e.preventDefault();
        if (isSubmitting) return;
        isSubmitting = true;
        const submitBtn = document.getElementById("submitBtn");
        const originalButtonText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        
        const formData = new FormData(this);
        fetch('../OrganizationalChart/DIET_OrgChart_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal();
                    reloadOrgChart();
                } else {
                    showNotification('Error saving data: ' + (data.error || 'Unknown error'), 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalButtonText;
                    isSubmitting = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalButtonText;
                isSubmitting = false;
            });
    }
    document.addEventListener('DOMContentLoaded', attachEventListeners);
</script>
</body>
</html>