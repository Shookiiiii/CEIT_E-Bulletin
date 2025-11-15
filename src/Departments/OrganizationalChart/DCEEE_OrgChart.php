<?php
include '../../db.php';

function getInitials($name) {
    if (empty(trim($name))) {
        return 'NA';
    }
    
    $prefixes = ['Mr. ', 'Mrs. ', 'Ms. ', 'Dr. ', 'Prof. ', 'Sir ', 'Engr. '];
    $cleanName = str_replace($prefixes, '', $name);
    
    $words = explode(' ', trim($cleanName));
    
    $initials = '';
    $count = min(2, count($words));
    for ($i = 0; $i < $count; $i++) {
        if (!empty($words[$i])) {
            $initials .= strtoupper(substr($words[$i], 0, 1));
        }
    }
    
    if (empty($initials)) {
        return 'NA';
    }
    
    return $initials;
}

function getColorShade($position_code) {
    if (in_array($position_code, ['president', 'vice_president', 'dean', 'chairperson'])) {
        return [
            'bg' => 'bg-[#800000]', 
            'text' => 'text-white',
            'border' => 'border-[#800000]'
        ];
    }
    else if (strpos($position_code, 'coordinator_') === 0) {
        return [
            'bg' => 'bg-[#990000]', 
            'text' => 'text-white',
            'border' => 'border-[#990000]'
        ];
    }
    else if (strpos($position_code, 'faculty_') === 0) {
        return [
            'bg' => 'bg-[#cc0000]', 
            'text' => 'text-white',
            'border' => 'border-[#cc0000]'
        ];
    }
    else {
        return [
            'bg' => 'bg-[#cc0000]', 
            'text' => 'text-white',
            'border' => 'border-[#cc0000]'
        ];
    }
}

function showFixedPositionBox($member, $position_code, $default_role)
{
    $colorShade = getColorShade($position_code);
    
    if (!$member) {
        $initials = 'NA';
        $circleContent = "<div class='h-12 w-12 rounded-full border {$colorShade['border']} {$colorShade['bg']} {$colorShade['text']} flex items-center justify-center text-sm shadow-lg font-bold'>$initials</div>";
    } else {
        if (!empty($member['photo'])) {
            $circleContent = "<img src='../OrganizationalChart/uploadDCEEE/" . htmlspecialchars($member['photo']) . "' class='h-12 w-12 rounded-full border {$colorShade['border']} object-cover shadow-lg'>";
        } else {
            $initials = getInitials($member['name']);
            $circleContent = "<div class='h-12 w-12 rounded-full border {$colorShade['border']} {$colorShade['bg']} {$colorShade['text']} flex items-center justify-center text-sm shadow-lg font-bold'>$initials</div>";
        }
    }
    
    if (!$member) {
        return "<div class='border {$colorShade['border']} p-1 rounded-md bg-white shadow-md text-left h-[70px] w-[200px] flex items-center space-x-2 mb-[-2px]'>
            $circleContent
            <div class='text-[11px] leading-tight flex-1 min-w-0'>
                <strong class='block truncate font-medium'>Full Name</strong>
                <p class='text-gray-600 truncate'>" . htmlspecialchars($default_role) . "</p>
                <div class='mt-1 flex space-x-1 text-[11px]'>
                    <button class='px-1.5 py-0.5 border text-[#800000] border-[#800000] hover:text-white hover:bg-[#800000] rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 edit-btn' 
                            data-id='0' 
                            data-name='' 
                            data-role='" . htmlspecialchars($default_role) . "' 
                            data-photo='' 
                            data-position='$position_code' 
                            title='Edit Personnel'>
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
    
    return "<div class='border {$colorShade['border']} p-1 rounded-md bg-white shadow-md text-left h-[70px] w-[200px] flex items-center space-x-2 mb-[-2px]'>
        $circleContent
        <div class='text-[11px] leading-tight flex-1 min-w-0'>
            <strong class='block truncate font-medium'>" . htmlspecialchars($member['name']) . "</strong>
            <p class='text-gray-600 truncate'>" . htmlspecialchars($member['role']) . "</p>
            <div class='mt-1 flex space-x-1 text-[11px]'>
                <button class='px-1.5 py-0.5 border text-[#800000] border-[#800000] hover:text-white hover:bg-[#800000] rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 edit-btn' 
                        data-id='" . $member['id'] . "' 
                        data-name='" . htmlspecialchars($member['name']) . "' 
                        data-role='" . htmlspecialchars($member['role']) . "' 
                        data-photo='" . htmlspecialchars($member['photo']) . "' 
                        data-position='" . $member['position_code'] . "' 
                        title='Edit Person'>
                    <i class=\"fas fa-pen\"></i> Edit
                </button>
                $deleteButton
            </div>
        </div>
    </div>";
}

function showBox($member, $position_code)
{
    $colorShade = getColorShade($position_code);
    
    if (!$member) {
        $initials = 'NA';
        $circleContent = "<div class='h-12 w-12 rounded-full border {$colorShade['border']} {$colorShade['bg']} {$colorShade['text']} flex items-center justify-center text-sm shadow-lg font-bold'>$initials</div>";
    } else {
        if (!empty($member['photo'])) {
            $circleContent = "<img src='../OrganizationalChart/uploadDCEEE/" . htmlspecialchars($member['photo']) . "' class='h-12 w-12 rounded-full border {$colorShade['border']} object-cover shadow-lg'>";
        } else {
            $initials = getInitials($member['name']);
            $circleContent = "<div class='h-12 w-12 rounded-full border {$colorShade['border']} {$colorShade['bg']} {$colorShade['text']} flex items-center justify-center text-sm shadow-lg font-bold'>$initials</div>";
        }
    }
    
    if (!$member) {
        return "<div class='border {$colorShade['border']} p-1 rounded-md bg-white shadow-md text-left h-[70px] w-[200px] flex items-center space-x-2 mb-[-2px]'>
            $circleContent
            <div class='text-[11px] leading-tight flex-1 min-w-0'>
                <strong class='block truncate font-medium'>Full Name</strong>
                <p class='text-gray-600 truncate'>&mdash;</p>
                <div class='mt-1 flex space-x-1 text-[11px]'>
                    <button class='px-1.5 py-0.5 border text-[#800000] border-[#800000] hover:text-white hover:bg-[#800000] rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 edit-btn' 
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
    $deleteButton = "<button class='px-1.5 py-0.5 border text-red-600 border-red-600 hover:text-white hover:bg-red-600 rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 delete-btn' 
                    data-id='" . $member['id'] . "' 
                    title='Delete'>
                <i class=\"fas fa-trash\"></i> Delete
            </button>";
    return "<div class='border {$colorShade['border']} p-1 rounded-md bg-white shadow-md text-left h-[85px] w-[150px] flex items-center space-x-2 mb-[-2px]'>
        $circleContent
        <div class='text-[11px] leading-tight flex-1 min-w-0'>
            <strong class='block truncate font-medium'>" . htmlspecialchars($member['name']) . "</strong>
            <p class='text-gray-600 truncate'>" . htmlspecialchars($member['role']) . "</p>
            <div class='mt-1 flex space-x-1 text-[11px] mb-1'>
                <button class='px-1.5 py-0.5 border text-[#800000] border-[#800000] hover:text-white hover:bg-[#800000] rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 edit-btn' 
                        data-id='" . $member['id'] . "' 
                        data-name='" . htmlspecialchars($member['name']) . "' 
                        data-role='" . htmlspecialchars($member['role']) . "' 
                        data-photo='" . htmlspecialchars($member['photo']) . "' 
                        data-position='" . $member['position_code'] . "' 
                        title='Edit'>
                    <i class=\"fas fa-pen\"></i> Edit
                </button>
            </div>
            $deleteButton
        </div>
    </div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DCEEE Organizational Chart</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'dcee-dark': '#800000',
            'dcee-medium': '#990000',
            'dcee-light': '#cc0000',
          },
        },
      },
    }
  </script>
</head>
<body>
<div class="container w-[1150px] mx-auto px-4 py-6 text-center" id="orgChartContainer">
    <h4 class="text-xl font-bold mb-5">DCEEE ORGANIZATIONAL STRUCTURE</h4>
    <div class="aspect-w-16 aspect-h-9">
        <div class="scale-wrapper">
            <div class="border-2 border-[#990000] rounded-xl p-6 bg-[#990000]/10 shadow-lg space-y-8">
                
                <!-- Top Management - Now vertically aligned -->
                <div>
                    <h5 class="text-lg font-semibold mb-4">Top Management</h5>
                    <div class="flex flex-col items-center space-y-4">
                        <?php
                        $topManagementRoles = [
                            'president' => 'President, CvSU',
                            'vice_president' => 'Vice President, OVPAA',
                            'dean' => 'Dean, CEIT',
                            'chairperson' => 'Chairperson, DCEEE'
                        ];
                        
                        $positions = ['president', 'vice_president', 'dean', 'chairperson'];
                        foreach ($positions as $pos) {
                            $stmt = $conn->prepare("SELECT * FROM DCEEE_Organization WHERE position_code = ? LIMIT 1");
                            $stmt->bind_param("s", $pos);
                            $stmt->execute();
                            $member = $stmt->get_result()->fetch_assoc();
                            echo showFixedPositionBox($member, $pos, $topManagementRoles[$pos]);
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Program Coordinators -->
                <div>
                    <h5 class="text-lg font-semibold mb-4">Program Coordinators</h5>
                    <div class="grid grid-cols-3 gap-6 justify-items-center">
                        <?php
                        $coordinatorRoles = [
                            'coordinator_1' => 'CpE Prgram Coordinator',
                            'coordinator_2' => 'EcE Program Coordinator',
                            'coordinator_3' => 'EE Program Coordinator'
                        ];
                        
                        foreach ($coordinatorRoles as $position_code => $role) {
                            $stmt = $conn->prepare("SELECT * FROM DCEEE_Organization WHERE position_code = ? LIMIT 1");
                            $stmt->bind_param("s", $position_code);
                            $stmt->execute();
                            $member = $stmt->get_result()->fetch_assoc();
                            echo showFixedPositionBox($member, $position_code, $role);
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Faculty Columns under each Coordinator -->
                <div>
                    <h5 class="text-lg font-semibold mb-4">Faculty Members</h5>
                    <div class="grid grid-cols-3 gap-6">
                        <?php
                        // Create 3 faculty columns, one under each coordinator
                        for ($i = 1; $i <= 3; $i++) {
                            $coordinator_code = "coordinator_$i";
                            
                            echo "<div class='border border-[#990000] rounded-lg p-4 bg-white shadow-sm'>";
                            
                            // Get faculty members for this coordinator
                            $stmt = $conn->prepare("SELECT * FROM DCEEE_Organization WHERE position_code LIKE ? ORDER BY id ASC");
                            $like_param = "faculty_" . $i . "_%";
                            $stmt->bind_param("s", $like_param);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            // Create a 2-column grid inside each faculty section
                            echo "<div class='grid grid-cols-2 gap-3'>";
                            
                            // Display faculty members
                            while ($row = $result->fetch_assoc()) {
                                echo showBox($row, $row['position_code']);
                            }
                            
                            echo "</div>"; // Close inner grid
                            
                            // Add button for new faculty members - centered across both columns
                            echo "<div class='flex justify-center mt-3'>";
                            echo "<button class='border border-[#990000] text-[#990000] hover:bg-[#990000] hover:text-white text-sm px-3 py-1 rounded transition duration-200 transform hover:scale-110 add-personnel-btn' 
                                    data-coordinator='$coordinator_code' 
                                    data-faculty-column='$i'
                                    title='Add Faculty'>
                                    <i class='fas fa-user-plus mr-1'></i> Add Faculty
                                  </button>";
                            echo "</div>";
                            
                            echo "</div>"; // Close outer faculty column
                        }
                        ?>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<!-- Member Modal -->
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

<!-- Notification Toast -->
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
        document.getElementById("modalTitle").innerText = id === 0 ? "Add Person" : "Edit Person";
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
        document.querySelectorAll('.add-personnel-btn').forEach(button => {
            button.removeEventListener('click', handleAddPersonnelClick);
        });
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
        document.querySelectorAll('.add-personnel-btn').forEach(button => {
            button.addEventListener('click', handleAddPersonnelClick);
        });
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
            fetch('../OrganizationalChart/DCEEE_OrgChart_process.php?delete=' + id)
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
        const facultyColumn = this.dataset.facultyColumn;
        // Generate unique position code with current timestamp
        const position = "faculty_" + facultyColumn + "_" + Date.now();
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
        fetch('../OrganizationalChart/DCEEE_OrgChart_process.php', {
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