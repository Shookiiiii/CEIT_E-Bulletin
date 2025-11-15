<?php
include '../../db.php';
function getMember($code, $conn)
{
    $stmt = $conn->prepare("SELECT * FROM DIT_Organization WHERE position_code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
function getFacultyMembers($unit, $conn)
{
    $stmt = $conn->prepare("SELECT * FROM DIT_Organization WHERE position_code LIKE ? ORDER BY 
                            CASE 
                                -- Associate Professor (highest rank)
                                WHEN role LIKE '%Associate Professor%' THEN
                                    CASE 
                                        WHEN role LIKE '%IV%' THEN 1
                                        WHEN role LIKE '%III%' THEN 2
                                        WHEN role LIKE '%II%' THEN 3
                                        WHEN role LIKE '%I%' THEN 4
                                        ELSE 5
                                    END
                                -- Assistant Professor (2nd rank)
                                WHEN role LIKE '%Assistant Professor%' THEN
                                    CASE 
                                        WHEN role LIKE '%IV%' THEN 6
                                        WHEN role LIKE '%III%' THEN 7
                                        WHEN role LIKE '%II%' THEN 8
                                        WHEN role LIKE '%I%' THEN 9
                                        ELSE 10
                                    END
                                -- Instructor (lowest rank)
                                WHEN role LIKE '%Instructor%' THEN
                                    CASE 
                                        WHEN role LIKE '%IV%' THEN 11
                                        WHEN role LIKE '%III%' THEN 12
                                        WHEN role LIKE '%II%' THEN 13
                                        WHEN role LIKE '%I%' THEN 14
                                        ELSE 15
                                    END
                                ELSE 16
                            END, id ASC");
    $pattern = $unit . '_faculty_%';
    $stmt->bind_param("s", $pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }
    return $members;
}
function getInitials($name) {
    if (empty($name)) return "N/A";
    
    // Remove any prefixes like Dr., Mr., Mrs., etc.
    $prefixes = array('Dr.', 'Mr.', 'Mrs.', 'Ms.', 'Prof.');
    $name = str_replace($prefixes, '', $name);
    
    // Trim and split the name into words
    $name = trim($name);
    $words = explode(' ', $name);
    
    // Filter out empty words
    $words = array_filter($words, function($word) {
        return !empty($word);
    });
    
    // Reset array keys
    $words = array_values($words);
    
    $initials = '';
    $count = count($words);
    
    if ($count >= 2) {
        // Use first and last word
        $initials = strtoupper(substr($words[0], 0, 1)) . strtoupper(substr($words[$count - 1], 0, 1));
    } else if ($count == 1) {
        // Only one word, use first two characters if available
        $word = $words[0];
        if (strlen($word) >= 2) {
            $initials = strtoupper(substr($word, 0, 2));
        } else {
            $initials = strtoupper($word);
        }
    } else {
        $initials = "N/A";
    }
    
    return $initials;
}
function getColorShade($position_code) {
    if (in_array($position_code, ['president', 'vice_president', 'dean', 'chairperson'])) {
        return [
            'bg' => 'bg-[#FF6B00]', 
            'text' => 'text-white',
            'border' => 'border-[#FF6B00]'
        ];
    }
    else if (strpos($position_code, 'coordinator_') === 0 || in_array($position_code, ['cs_coordinator', 'it_coordinator'])) {
        return [
            'bg' => 'bg-[#FF9500]', 
            'text' => 'text-white',
            'border' => 'border-[#FF9500]'
        ];
    }
    else if (strpos($position_code, 'faculty') !== false) {
        return [
            'bg' => 'bg-[#FF9500]', 
            'text' => 'text-white',
            'border' => 'border-[#FF9500]'
        ];
    }
    else {
        return [
            'bg' => 'bg-[#FF9500]', 
            'text' => 'text-white',
            'border' => 'border-[#FF9500]'
        ];
    }
}
function showBox($member, $position_code)
{
    $colorShade = getColorShade($position_code);
    
    $defaultRoles = [
        'president' => 'President, CvSU',
        'vice_president' => 'Vice President, OVPAA',
        'dean' => 'Dean, CEIT',
        'chairperson' => 'Chairperson, DIT',
        'cs_coordinator' => 'CS Coordinator',
        'it_coordinator' => 'IT Coordinator'
    ];
    
    $defaultRole = isset($defaultRoles[$position_code]) ? $defaultRoles[$position_code] : 'Faculty Member';
    
    // For coordinators, we want to show only their academic rank in the role field
    if ($member && in_array($position_code, ['cs_coordinator', 'it_coordinator'])) {
        // Extract just the academic rank part
        $academicRank = htmlspecialchars($member['role']);
        
        // Remove any coordinator titles that might be included
        $academicRank = str_replace(['CS Coordinator', 'IT Coordinator'], '', $academicRank);
        $academicRank = trim($academicRank, " ,");
        
        $roleDisplay = $academicRank;
    } else {
        $roleDisplay = htmlspecialchars($member['role'] ?? $defaultRole);
    }
    
    if (!$member) {
        $initials = 'NA';
        $circleContent = "<div class='h-12 w-12 rounded-full border {$colorShade['border']} {$colorShade['bg']} {$colorShade['text']} flex items-center justify-center text-sm shadow-lg font-bold'>$initials</div>";
    } else {
        if (!empty($member['photo'])) {
            $circleContent = "<img src='../OrganizationalChart/uploadDIT/" . htmlspecialchars($member['photo']) . "' class='h-12 w-12 rounded-full border {$colorShade['border']} object-cover shadow-lg'>";
        } else {
            $initials = getInitials($member['name']);
            $circleContent = "<div class='h-12 w-12 rounded-full border {$colorShade['border']} {$colorShade['bg']} {$colorShade['text']} flex items-center justify-center text-sm shadow-lg font-bold'>$initials</div>";
        }
    }
    
    if (!$member) {
        return "<div class='border {$colorShade['border']} p-1 rounded-md bg-white shadow-md text-left h-[70px] w-[235px] flex items-center space-x-2 mb-[-2px]'>
            $circleContent
            <div class='text-[11px] leading-tight flex-1 min-w-0'>
                <strong class='block truncate font-medium'>Full Name</strong>
                <p class='text-gray-600 truncate'>" . htmlspecialchars($defaultRole) . "</p>
                <div class='mt-1 flex space-x-1 text-[11px]'>
                    <button class='px-1.5 py-0.5 border text-dit-dark border-dit-dark hover:text-white hover:bg-dit-dark rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 edit-btn' 
                            data-id='0' 
                            data-name='' 
                            data-role='" . htmlspecialchars($defaultRole) . "' 
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
    
    // For coordinators, we need to show both the fixed title and the academic rank
    if (in_array($position_code, ['cs_coordinator', 'it_coordinator'])) {
        $roleDisplay = $defaultRole . '<br><span class="text-[10px] font-normal">' . $roleDisplay . '</span>';
    }
    
    return "<div class='border {$colorShade['border']} p-1 rounded-md bg-white shadow-md text-left h-[70px] w-[235px] flex items-center space-x-2 mb-[-2px]'>
        $circleContent
        <div class='text-[11px] leading-tight flex-1 min-w-0'>
            <strong class='block truncate font-medium'>" . htmlspecialchars($member['name']) . "</strong>
            <p class='text-gray-600 truncate'>" . $roleDisplay . "</p>
            <div class='mt-1 flex space-x-1 text-[11px]'>
                <button class='px-1.5 py-0.5 border text-dit-dark border-dit-dark hover:text-white hover:bg-dit-dark rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 edit-btn' 
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DIT Organizational Chart</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'dit-dark': '#FF6B00',
            'dit-medium': '#FF9500',
            'dit-light': '#FF9500',
          },
        },
      },
    }
  </script>
</head>
<body>
<div class="container w-[1150px] mx-auto px-4 py-6 text-center" id="orgChartContainer">
    <h4 class="text-xl font-bold mb-5">DIT ORGANIZATIONAL STRUCTURE</h4>
    <div class="aspect-w-16 aspect-h-9">
        <div class="scale-wrapper">
            <div class="border-2 border-dit-dark rounded-xl p-6 bg-dit-medium/15 shadow-lg space-y-8">
                
                <!-- Top Management - Vertically aligned -->
                <div>
                    <div class="flex flex-col items-center space-y-4">
                        <?php
                        $positions = ['president', 'vice_president', 'dean', 'chairperson'];
                        foreach ($positions as $pos) {
                            $member = getMember($pos, $conn);
                            echo showBox($member, $pos);
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Program Coordinators -->
                <div>
                    <h5 class="text-lg font-semibold mb-4">Program Coordinators</h5>
                    <div class="grid grid-cols-2 gap-6 justify-items-center">
                        <?php
                        $coordinatorPositions = ['cs_coordinator', 'it_coordinator'];
                        foreach ($coordinatorPositions as $pos) {
                            $member = getMember($pos, $conn);
                            echo showBox($member, $pos);
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Faculty Columns under each Coordinator -->
                <div>
                    <h5 class="text-lg font-semibold mb-4">Faculty Members</h5>
                    <div class="grid grid-cols-2 gap-6">
                        <?php
                        // Create 2 faculty columns, one under each coordinator
                        $facultyUnits = ['cs', 'it'];
                        foreach ($facultyUnits as $unit) {
                            $coordinator_code = $unit . "_coordinator";
                            
                            echo "<div class='border border-dit-medium rounded-lg p-4 bg-white shadow-sm'>";
                            
                            // Get faculty members for this unit
                            $facultyMembers = getFacultyMembers($unit, $conn);
                            
                            // Create a 2-column grid inside each faculty section
                            echo "<div class='grid grid-cols-2 gap-3'>";
                            
                            // Display faculty members
                            foreach ($facultyMembers as $member) {
                                echo showBox($member, $member['position_code']);
                            }
                            
                            echo "</div>"; // Close inner grid
                            
                            // Add button for new faculty members - centered across both columns
                            echo "<div class='flex justify-center mt-3'>";
                            echo "<button class='border text-dit-dark border-dit-dark hover:text-white hover:bg-dit-dark text-sm px-3 py-1 rounded transition duration-200 transform hover:scale-110 add-faculty-btn' 
                                    data-unit='$unit'
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
    // Define default roles for coordinators
    const defaultRoles = {
        'president': 'President, CvSU',
        'vice_president': 'Vice President, OVPAA',
        'dean': 'Dean, CEIT',
        'chairperson': 'Chairperson, DIT',
        'cs_coordinator': 'CS Coordinator',
        'it_coordinator': 'IT Coordinator'
    };
    
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
        
        // For coordinators, show the full role including the coordinator title
        if (position_code === 'cs_coordinator' || position_code === 'it_coordinator') {
            // Get the default coordinator title
            const coordinatorTitle = defaultRoles[position_code];
            
            // If we have an academic rank, combine it with the coordinator title
            if (role && role.trim() !== '') {
                // Remove any coordinator titles that might be included in the role
                let academicRank = role;
                const coordinatorTitles = ['CS Coordinator', 'IT Coordinator'];
                coordinatorTitles.forEach(title => {
                    academicRank = academicRank.replace(title, '');
                });
                academicRank = academicRank.replace(/^,\s*/, '').replace(/\s*,\s*$/, '').trim();
                
                // Set the role input to the full role: "Coordinator Title Academic Rank" (without comma)
                document.getElementById("role").value = coordinatorTitle + ' ' + academicRank;
            } else {
                // If no academic rank, just show the coordinator title
                document.getElementById("role").value = coordinatorTitle;
            }
        } else {
            document.getElementById("role").value = role;
        }
        
        document.getElementById("position_code").value = position_code;
        
        // Reset the file input and current photo display
        const photoInput = document.getElementById('photoInput');
        photoInput.value = ''; // Clear the file input
        const currentPhoto = document.getElementById("currentPhoto");
        currentPhoto.classList.add("hidden");
        currentPhoto.innerHTML = '';
        
        // If editing and there is a photo, show the current photo
        if (id !== 0 && photo) {
            currentPhoto.classList.remove("hidden");
            currentPhoto.innerHTML = `Current: ${photo}`;
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
        document.querySelectorAll('.add-faculty-btn').forEach(button => {
            button.removeEventListener('click', handleAddFacultyClick);
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
        document.querySelectorAll('.add-faculty-btn').forEach(button => {
            button.addEventListener('click', handleAddFacultyClick);
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
            fetch('../OrganizationalChart/DIT_OrgChart_process.php?delete=' + id)
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
        const unit = this.dataset.unit;
        // Generate unique position code with current timestamp
        const position = unit + "_faculty_" + Date.now();
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
        const position_code = document.getElementById("position_code").value;
        
        // For coordinators, we need to extract only the academic rank for saving
        if (position_code === 'cs_coordinator' || position_code === 'it_coordinator') {
            const roleInput = document.getElementById("role");
            const fullRole = roleInput.value;
            
            // Get the default coordinator title
            const coordinatorTitle = defaultRoles[position_code];
            
            // Extract academic rank by removing the coordinator title
            let academicRank = fullRole;
            
            // Check if the role starts with the coordinator title
            if (fullRole.startsWith(coordinatorTitle)) {
                // Remove the coordinator title and any space following it
                academicRank = fullRole.substring(coordinatorTitle.length).replace(/^\s+/, '').trim();
            } else {
                // If it doesn't start with the coordinator title, try to remove any occurrence of coordinator titles
                const coordinatorTitles = ['CS Coordinator', 'IT Coordinator'];
                coordinatorTitles.forEach(title => {
                    academicRank = academicRank.replace(title, '');
                });
                academicRank = academicRank.replace(/^,\s*/, '').replace(/\s*,\s*$/, '').trim();
            }
            
            // Update the form data with only the academic rank
            formData.set('role', academicRank);
        }
        
        fetch('../OrganizationalChart/DIT_OrgChart_process.php', {
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