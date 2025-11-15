<?php 
include '../../db.php';
function getMember($code, $conn) {
    $stmt = $conn->prepare("SELECT * FROM CEIT_Organization WHERE position_code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getInitials($name) {
    $prefixes = array('Dr.', 'Prof.', 'Mr.', 'Mrs.', 'Ms.', 'Engr.');
    $name = str_replace($prefixes, '', $name);
    
    $name = trim(preg_replace('/\s+/', ' ', $name));
    
    $nameParts = explode(' ', $name);
    
    $firstName = isset($nameParts[0]) ? $nameParts[0] : '';
    $lastName = isset($nameParts[count($nameParts)-1]) ? $nameParts[count($nameParts)-1] : '';
    
    $initials = '';
    if ($firstName) $initials .= strtoupper(substr($firstName, 0, 1));
    if ($lastName && $lastName != $firstName) $initials .= strtoupper(substr($lastName, 0, 1));
    
    return $initials;
}

function showBox($member, $position_code) {
    if (!$member) {
        $defaultRole = '';
        if ($position_code === 'president') {
            $defaultRole = 'President, CvSU';
        } elseif ($position_code === 'vice_president') {
            $defaultRole = 'Vice President, OVPAA';
        } elseif ($position_code === 'college_dean') {
            $defaultRole = 'Dean, CEIT';
        }
        
        return "<div class='border border-orange-400 p-1 rounded-md bg-white shadow-md text-left h-[70px] w-[180px] ml-0.5 flex items-center space-x-2 mb-[-2px]'>
            <div class='h-12 w-12 rounded-full border border-orange-400 bg-orange-400 flex items-center justify-center text-white font-bold text-xs shadow-lg flex-shrink-0'>NA</div>
            <div class='flex flex-col flex-1 min-w-0'>
                <div class='text-[11px] font-medium truncate'>Full Name</div>
                <div class='text-[11px] text-gray-600 truncate'>" . htmlspecialchars($defaultRole) . "</div>
                <div class='mt-1 flex space-x-1 text-[11px]'>
                    <button class='px-1.5 py-0.5 border text-orange-500 border-orange-500 hover:text-white hover:bg-orange-500 rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 edit-btn flex-shrink-0 whitespace-nowrap' 
                            data-id='0' 
                            data-name='' 
                            data-role='" . htmlspecialchars($defaultRole) . "' 
                            data-photo='' 
                            data-position='$position_code' 
                            title='Edit'>
                        <i class=\"fas fa-pen\"></i> Edit
                    </button>
                </div>
            </div>
        </div>";
    }
    
    $deleteButton = "<button class='px-1.5 py-0.5 border text-red-600 border-red-600 hover:text-white hover:bg-red-600 rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 delete-btn flex-shrink-0 whitespace-nowrap' 
                    data-id='" . $member['id'] . "' 
                    title='Delete'>
                <i class=\"fas fa-trash\"></i> Delete
            </button>";
    
    $photoDisplay = '';
    if (!empty($member['photo'])) {
        $photoDisplay = "<img src='../OrganizationalChart/uploadCEIT/" . htmlspecialchars($member['photo']) . "' class='h-12 w-12 rounded-full border border-orange-500 object-cover shadow-lg flex-shrink-0'>";
    } else {
        $initials = getInitials($member['name']);
        $photoDisplay = "<div class='h-12 w-12 rounded-full border border-orange-500 bg-orange-400 flex items-center justify-center text-white text-sm font-bold shadow-lg flex-shrink-0'>" . $initials . "</div>";
    }
    
    return "<div class='border border-orange-400 p-1 rounded-md bg-white shadow-md text-left h-[70px] w-[180px] ml-0.5 flex items-center space-x-2 mb-[-2px]'>
        " . $photoDisplay . "
        <div class='flex flex-col flex-1 min-w-0'>
            <div class='text-[11px] font-medium truncate'>" . htmlspecialchars($member['name']) . "</div>
            <div class='text-[11px] text-gray-600 truncate'>" . htmlspecialchars($member['role']) . "</div>
            <div class='mt-1 flex space-x-1 text-[11px]'>
                <button class='px-1.5 py-0.5 border text-orange-500 border-orange-500 hover:text-white hover:bg-orange-500 rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 edit-btn flex-shrink-0 whitespace-nowrap' 
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
  <title>CEIT Organizational Chart</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<div class="container w-[1150px] mx-auto px-4 py-6 text-center" id="orgChartContainer">
    <div class="aspect-w-16 aspect-h-9">
        <div class="scale-wrapper">
            <div class="space-y-4">
                <div class=" rounded-xl p-4 mb-6">
                    <div class="flex flex-col space-y-4" id="topManagement">
                        <?php
                        $fixedTopPositions = ['president', 'vice_president', 'college_dean'];
                        foreach ($fixedTopPositions as $position) {
                            $member = getMember($position, $conn);
                            echo "<div class='flex justify-center'>" . showBox($member, $position) . "</div>";
                        }
                        ?>
                    </div>
                </div>
                <p class="text-center font-bold bg-orange-500/80 text-white rounded-lg text-2xl mb-2">CEIT Personnel</p>
                <div class="border bg-orange-50 border-orange-400 rounded-xl mt-6 p-3">
                    <div class="grid grid-cols-6 gap-2 justify-items-center ml-1" id="personnelGrid">
                        <?php
                        $result = $conn->query("SELECT * FROM CEIT_Organization WHERE position_code NOT IN ('president', 'vice_president', 'college_dean') ORDER BY id ASC");
                        $personnel_count = 0;
                        while ($row = $result->fetch_assoc()) {
                            if ($personnel_count < 36) {
                                echo showBox($row, $row['position_code']);
                                $personnel_count++;
                            }
                        }
                        ?>
                    </div>
                    <div class="mt-3 text-center">
                        <button class="border text-orange-500 border-orange-500 hover:text-white hover:bg-orange-500 text-sm px-3 py-1 rounded transition duration-200 transform hover:scale-110 add-personnel-btn">
                            <i class="fas fa-user-plus"></i> Add Personnel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="memberModal" class="fixed inset-0 hidden z-50 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg w-full max-w-md">
        <form id="memberForm" method="post" enctype="multipart/form-data">
            <h5 class="text-lg font-bold mb-4" id="modalTitle">Edit Member</h5>
            <input type="hidden" name="member_id" id="member_id">
            <div class="mb-3">
                <label class="block text-sm font-medium">Name</label>
                <input type="text" name="name" id="name" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium">Role</label>
                <input type="text" name="role" id="role" class="w-full border rounded px-3 py-2" required>
            </div>
            <input type="hidden" name="position_code" id="position_code">
            <div class="mb-3">
                <label class="block text-sm font-medium">Photo</label>
                <input type="file" name="photo" class="w-full" id="photoInput">
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
<div id="notificationToast" class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg transform transition-transform duration-300 translate-y-20 opacity-0 flex items-center">
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
        toast.classList.add("bg-green-500", "text-white");
        toast.innerHTML = '<i class="fas fa-check-circle mr-2"></i><span id="notificationMessage">' + message + '</span>';
    } else if (type === 'error') {
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
    
    // Always make role field editable
    const roleField = document.getElementById("role");
    roleField.readOnly = false;
    roleField.classList.remove("bg-gray-100");
    
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
}
function attachEventListeners() {
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.removeEventListener('click', handleEditClick);
    });
    
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.removeEventListener('click', handleDeleteClick);
    });
    
    const addPersonnelBtn = document.querySelector('.add-personnel-btn');
    if (addPersonnelBtn) {
        addPersonnelBtn.removeEventListener('click', handleAddPersonnelClick);
    }
    
    const memberForm = document.getElementById('memberForm');
    if (memberForm) {
        memberForm.removeEventListener('submit', handleFormSubmit);
    }
    
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', handleEditClick);
    });
    
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', handleDeleteClick);
    });
    
    if (addPersonnelBtn) {
        addPersonnelBtn.addEventListener('click', handleAddPersonnelClick);
    }
    
    if (memberForm) {
        memberForm.addEventListener('submit', handleFormSubmit);
    }
}
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
        fetch('../OrganizationalChart/CEIT_OrgChart_process.php?delete=' + id)
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
function handleAddPersonnelClick() {
    // Generate a timestamp-based position code
    const timestamp = Date.now();
    const position = 'ceit_personnel_' + timestamp;
    openEditModal(0, '', '', '', position);
}
function handleFormSubmit(e) {
    e.preventDefault();
    if (isSubmitting) {
        return;
    }
    isSubmitting = true;
    const submitBtn = document.getElementById("submitBtn");
    const originalButtonText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    const formData = new FormData(this);
    fetch('../OrganizationalChart/CEIT_OrgChart_process.php', {
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
document.addEventListener('DOMContentLoaded', function() {
    attachEventListeners();
});
</script>

</body>
</html>