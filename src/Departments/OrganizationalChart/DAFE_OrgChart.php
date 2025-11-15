<?php
include '../../db.php';

function getInitialWithoutPrefix($name) {
    if (empty($name)) return '';
    
    $prefixes = ['Mr.', 'Mrs.', 'Ms.', 'Miss', 'Dr.', 'Prof.', 'Sir', 'Engr'];
    
    $cleanName = trim($name);
    
    foreach ($prefixes as $prefix) {
        if (strpos($cleanName, $prefix) === 0) {
            $cleanName = trim(substr($cleanName, strlen($prefix)));
            break;
        }
    }
    
    $nameParts = explode(' ', $cleanName);
    $initials = '';
    
    if (!empty($nameParts[0])) {
        $initials .= strtoupper(substr($nameParts[0], 0, 1));
    }
    
    if (count($nameParts) > 1) {
        $lastName = end($nameParts);
        if ($lastName != $nameParts[0]) {
            $initials .= strtoupper(substr($lastName, 0, 1));
        }
    }
    
    return $initials;
}

function getColorShade($section) {
    if ($section === 'top') {
        return [
            'bg' => 'bg-[#065F46]', 
            'text' => 'text-white',
            'border' => 'border-[#065F46]'
        ];
    }
    elseif ($section === 'faculty' || $section === 'unithead') {
        return [
            'bg' => 'bg-[#047857]', 
            'text' => 'text-white',
            'border' => 'border-[#047857]'
        ];
    }
    elseif ($section === 'adviser') {
        return [
            'bg' => 'bg-[#10B981]', 
            'text' => 'text-white',
            'border' => 'border-[#10B981]'
        ];
    }
    else {
        return [
            'bg' => 'bg-[#10B981]', 
            'text' => 'text-white',
            'border' => 'border-[#10B981]'
        ];
    }
}

function showBox($member, $position_code, $section, $defaultRole = '')
{
    $colorShade = getColorShade($section);
    
    $profileHtml = '';
    
    if (!$member) {
        $profileHtml = "<div class='h-12 w-12 rounded-full border {$colorShade['border']} {$colorShade['bg']} {$colorShade['text']} flex items-center justify-center font-md text-sm shadow-lg font-bold'>NA</div>";
    } else {
        if (!empty($member['photo'])) {
            $profileHtml = "<img src='../OrganizationalChart/uploadDAFE/" . htmlspecialchars($member['photo']) . "' class='h-12 w-12 rounded-full border {$colorShade['border']} object-cover shadow-lg'>";
        } else {
            $initial = getInitialWithoutPrefix($member['name']);
            $profileHtml = "<div class='h-12 w-12 rounded-full border {$colorShade['border']} {$colorShade['bg']} {$colorShade['text']} flex items-center justify-center shadow-lg font-bold'>" . htmlspecialchars($initial) . "</div>";
        }
    }
    
    $editButtonClass = "px-1.5 py-0.5 border text-green-800 border-green-800 hover:text-white hover:bg-green-800 rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 edit-btn";
    
    if (!$member) {
        return "<div class='{$colorShade['border']} border p-1 rounded-md bg-white shadow-md text-left h-[70px] w-[220px] ml-0.5 flex items-center space-x-2 mb-[-2px]'>
            $profileHtml
            <div class='text-[11px] leading-tight flex-1 min-w-0'>
                <strong class='block truncate font-medium'>Full Name</strong>
                <p class='text-gray-600 truncate'>" . htmlspecialchars($defaultRole) . "</p>
                <div class='mt-1 flex space-x-1 text-[11px]'>
                    <button class='$editButtonClass' 
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
    
    $deleteButton = '';
    if (!empty($member['id']) && $member['id'] > 0) {
        $deleteButton = "<button class='px-1.5 py-0.5 border border-red-600 text-red-600 hover:bg-red-600 hover:text-white rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 delete-btn' 
                        data-id='" . $member['id'] . "' 
                        title='Delete'>
                    <i class=\"fas fa-trash\"></i> Delete
                </button>";
    }
     
    return "<div class='{$colorShade['border']} border p-1 rounded-md bg-white shadow-md text-left h-[70px] w-[220px] ml-0.5 flex items-center space-x-2 mb-[-2px]'>
        $profileHtml
        <div class='text-[11px] leading-tight flex-1 min-w-0'>
            <strong class='block truncate font-medium'>" . htmlspecialchars($member['name']) . "</strong>
            <p class='text-gray-600 truncate'>" . htmlspecialchars($member['role']) . "</p>
            <div class='mt-1 flex space-x-1 text-[11px]'>
                <button class='$editButtonClass' 
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

function splitIntoColumns($members, $maxPerColumn = 6) {
    if (count($members) <= $maxPerColumn) {
        return [$members];
    }
    
    $columns = [];
    $total = count($members);
    $columnsCount = ceil($total / $maxPerColumn);
    
    for ($i = 0; $i < $columnsCount; $i++) {
        $start = $i * $maxPerColumn;
        $columns[] = array_slice($members, $start, $maxPerColumn);
    }
    
    return $columns;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DAFE Organizational Chart</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'top-dark-green': '#065F46',
            'faculty-moderate-green': '#047857',
            'adviser-light-green': '#10B981',
            'modal-green': '#059669',
          },
          spacing: {
            'compact': '150px',
          },
        },
      },
    }
  </script>
</head>
<body class="bg-gray-50">
<div class="container w-[1150px] mx-auto px-4 py-6 text-center" id="orgChartContainer">
    <h4 class="text-xl font-bold mb-5 text-[#065F46]">DAFE ORGANIZATIONAL STRUCTURE</h4>
    <div class="aspect-w-16 aspect-h-9">
        <div class="scale-wrapper">
            <div class="border-2 border-[#065F46] rounded-xl p-6 bg-[#065F46]/10">
                
                <div class="mb-8">
                    <div class="border-t border-[#065F46] pt-4">
                        <div class="flex flex-col items-center gap-4" id="topManagement">
                            <?php
                            $defaultTopPositions = [
                                ['position_code' => 'president', 'name' => 'President', 'role' => 'President, CvSU'],
                                ['position_code' => 'vice_president', 'name' => 'Vice President', 'role' => 'Vice President, OVPAA'],
                                ['position_code' => 'dean', 'name' => 'Dean', 'role' => 'Dean, DAFE'],
                                ['position_code' => 'chairperson', 'name' => 'Chairperson', 'role' => 'Chairperson, DAFE']
                            ];
                            
                            $result = $conn->query("SELECT * FROM DAFE_Organization WHERE position_code LIKE 'top_%' OR position_code IN ('president', 'vice_president', 'dean', 'chairperson') ORDER BY id ASC");
                            $existingPositions = [];
                            while ($row = $result->fetch_assoc()) {
                                $existingPositions[$row['position_code']] = $row;
                            }
                            
                            foreach ($defaultTopPositions as $defaultPosition) {
                                $positionCode = $defaultPosition['position_code'];
                                if (isset($existingPositions[$positionCode])) {
                                    echo "<div class='flex justify-center'>" . showBox($existingPositions[$positionCode], $positionCode, 'top') . "</div>";
                                } else {
                                    echo "<div class='flex justify-center'>" . showBox(null, $positionCode, 'top', $defaultPosition['role']) . "</div>";
                                }
                            }
                            
                            $result = $conn->query("SELECT * FROM DAFE_Organization WHERE position_code LIKE 'top_%' AND position_code NOT IN ('president', 'vice_president', 'dean', 'chairperson') ORDER BY id ASC");
                            while ($row = $result->fetch_assoc()) {
                                echo "<div class='flex justify-center'>" . showBox($row, $row['position_code'], 'top') . "</div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-[#065F46]"></div>
                    </div>
                </div>
                
                <div class="mt-8">
                    <div class="border-t border-[#065F46] pt-4">
                        <div class="grid grid-cols-2 gap-6 justify-items-center mb-6" id="centerManagement">
                            <!-- Faculty Column -->
                            <div class="w-full">
                                <h2 class="text-center font-bold text-[#047857] text-lg uppercase tracking-wide mb-4">
                                    Faculty
                                </h2>
                                <div class="w-full border bg-white border-[#047857] rounded-lg p-4 shadow-sm faculty-column" id="faculty-column">
                                    <?php
                                    $result = $conn->query("SELECT * FROM DAFE_Organization WHERE position_code LIKE 'faculty\_%' ORDER BY id ASC");
                                    $facultyMembers = [];
                                    
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $facultyMembers[] = $row;
                                        }
                                    }
                                    
                                    if (!empty($facultyMembers)) {
                                        $subColumns = splitIntoColumns($facultyMembers, 6);
                                        
                                        if (count($subColumns) > 1) {
                                            echo "<div class='grid grid-cols-" . count($subColumns) . " gap-4'>";
                                            foreach ($subColumns as $subColumn) {
                                                echo "<div class='flex flex-col items-center space-y-3'>";
                                                foreach ($subColumn as $member) {
                                                    echo showBox($member, $member['position_code'], 'faculty');
                                                }
                                                echo "</div>";
                                            }
                                            echo "</div>";
                                        } else {
                                            echo "<div class='flex flex-col items-center space-y-3'>";
                                            foreach ($facultyMembers as $member) {
                                                echo showBox($member, $member['position_code'], 'faculty');
                                            }
                                            echo "</div>";
                                        }
                                    } else {
                                        echo "<div class='text-gray-500 italic py-4 text-sm'>No faculty members yet.</div>";
                                    }
                                    
                                    echo "<div class='flex justify-center mt-2'>";
                                    echo "<button class='px-1.5 py-0.5 border text-green-800 border-green-800 hover:text-white hover:bg-green-800 rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 add-faculty-btn'>
                                            <i class='fas fa-user-plus mr-1'></i> Add Faculty
                                        </button>";
                                    echo "</div>";
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Unit Heads Column -->
                            <div class="w-full">
                                <h2 class="text-center font-bold text-[#047857] text-lg uppercase tracking-wide mb-4">
                                    Unit Heads
                                </h2>
                                <div class="w-full border bg-white border-[#047857] rounded-lg p-4 shadow-sm unithead-column" id="unithead-column">
                                    <?php
                                    $result = $conn->query("SELECT * FROM DAFE_Organization WHERE position_code LIKE 'unithead\_%' ORDER BY id ASC");
                                    $unitheadMembers = [];
                                    
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $unitheadMembers[] = $row;
                                        }
                                    }
                                    
                                    if (!empty($unitheadMembers)) {
                                        $subColumns = splitIntoColumns($unitheadMembers, 6);
                                        
                                        if (count($subColumns) > 1) {
                                            echo "<div class='grid grid-cols-" . count($subColumns) . " gap-4'>";
                                            foreach ($subColumns as $subColumn) {
                                                echo "<div class='flex flex-col items-center space-y-3'>";
                                                foreach ($subColumn as $member) {
                                                    echo showBox($member, $member['position_code'], 'unithead');
                                                }
                                                echo "</div>";
                                            }
                                            echo "</div>";
                                        } else {
                                            echo "<div class='flex flex-col items-center space-y-3'>";
                                            foreach ($unitheadMembers as $member) {
                                                echo showBox($member, $member['position_code'], 'unithead');
                                            }
                                            echo "</div>";
                                        }
                                    } else {
                                        echo "<div class='text-gray-500 italic py-4 text-sm'>No unit heads yet.</div>";
                                    }
                                    
                                    echo "<div class='flex justify-center mt-2'>";
                                    echo "<button class='px-1.5 py-0.5 border text-green-800 border-green-800 hover:text-white hover:bg-green-800 rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 add-unithead-btn'>
                                            <i class='fas fa-user-plus mr-1'></i> Add Unit Head
                                        </button>";
                                    echo "</div>";
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-[#065F46]"></div>
                    </div>
                </div>
                
                <div class="mt-8">
                    <div class="border-t border-[#065F46] pt-4">
                        <h2 class="text-center font-bold text-[#10B981] text-lg uppercase tracking-wide mb-4">
                            Advisers
                        </h2>
                        <div class="w-[50%] mx-auto border bg-white border-[#047857] rounded-lg p-4 shadow-sm unithead-column" id="unithead-column">
                        <div class="flex flex-col items-center space-y-4" id="adviserSection">
                            <?php
                            $result = $conn->query("SELECT * FROM DAFE_Organization WHERE position_code LIKE 'adviser\_%' ORDER BY id ASC");
                            $advisers = [];
                            
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $advisers[] = $row;
                                }
                            }
                            
                            if (count($advisers) > 5) {
                                $half = ceil(count($advisers) / 2);
                                $firstHalf = array_slice($advisers, 0, $half);
                                $secondHalf = array_slice($advisers, $half);
                                
                                echo "<div class='grid grid-cols-2 gap-8 w-full'>";
                                
                                echo "<div class='flex flex-col items-center space-y-3'>";
                                foreach ($firstHalf as $member) {
                                    echo "<div class='flex justify-center'>" . showBox($member, $member['position_code'], 'adviser') . "</div>";
                                }
                                echo "</div>";
                                
                                echo "<div class='flex flex-col items-center space-y-3'>";
                                foreach ($secondHalf as $member) {
                                    echo "<div class='flex justify-center'>" . showBox($member, $member['position_code'], 'adviser') . "</div>";
                                }
                                echo "</div>";
                                
                                echo "</div>";
                            } else {
                                foreach ($advisers as $member) {
                                    echo "<div class='flex justify-center'>" . showBox($member, $member['position_code'], 'adviser') . "</div>";
                                }
                            }
                            
                            echo "<button class='px-1.5 py-0.5 border text-green-800 border-green-800 hover:text-white hover:bg-green-800 rounded transition duration-200 transform hover:scale-110 flex items-center gap-1 add-adviser-btn mt-2'>
                                <i class='fas fa-user-plus mr-1'></i> Add Advisers
                            </button>";
                            ?>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="memberModal" class="fixed inset-0 hidden z-50 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg w-full max-w-md shadow-2xl">
        <form id="memberForm" method="post" enctype="multipart/form-data">
            <div class="bg-gradient-to-r from-[#059669] to-[#10B981] text-white p-3 rounded-t-lg -m-6 mb-4">
                <h5 class="text-lg font-bold" id="modalTitle">Edit Member</h5>
            </div>
            <input type="hidden" name="member_id" id="member_id">
            <div class="mb-3">
                <label class="block text-sm font-medium">Name</label>
                <input type="text" name="name" id="name" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#059669]" required>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium">Role</label>
                <input type="text" name="role" id="role" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#059669]" required>
            </div>
            <input type="hidden" name="position_code" id="position_code">
            <div class="mb-3">
                <label class="block text-sm font-medium">Photo</label>
                <input type="file" name="photo" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#059669]" id="photoInput">
                <div id="currentPhoto" class="mt-2 text-sm text-gray-500 hidden"></div>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-700 hover:bg-gray-700 hover:text-white rounded text-gray-700 transition duration-200 transform hover:scale-110">Cancel</button>
                <button type="submit" id="submitBtn" class="px-4 py-2 border border-[#059669] bg-[#059669] text-white rounded hover:bg-opacity-90 transition duration-200 transform hover:scale-110">
                    <i class="fas fa-save fa-sm"></i> Save
                </button>
            </div>
        </form>
    </div>
</div>
<div id="notificationToast" class="fixed bottom-4 right-4 bg-[#059669] text-white px-4 py-2 rounded shadow-lg transform transition-transform duration-300 translate-y-20 opacity-0 flex items-center">
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
            toast.classList.add("bg-[#059669]", "text-white");
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
        document.querySelectorAll('.add-adviser-btn').forEach(button => {
            button.removeEventListener('click', handleAddAdviserClick);
        });
        document.querySelectorAll('.add-faculty-btn').forEach(button => {
            button.removeEventListener('click', handleAddFacultyClick);
        });
        document.querySelectorAll('.add-unithead-btn').forEach(button => {
            button.removeEventListener('click', handleAddUnitheadClick);
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
        document.querySelectorAll('.add-adviser-btn').forEach(button => {
            button.addEventListener('click', handleAddAdviserClick);
        });
        document.querySelectorAll('.add-faculty-btn').forEach(button => {
            button.addEventListener('click', handleAddFacultyClick);
        });
        document.querySelectorAll('.add-unithead-btn').forEach(button => {
            button.addEventListener('click', handleAddUnitheadClick);
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
            fetch('../OrganizationalChart/DAFE_OrgChart_process.php?delete=' + id)
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
    function handleAddAdviserClick() {
        const position = 'adviser_' + Date.now();
        openEditModal(0, '', '', '', position);
    }
    function handleAddFacultyClick() {
        const position = 'faculty_' + Date.now();
        openEditModal(0, '', '', '', position);
    }
    function handleAddUnitheadClick() {
        const position = 'unithead_' + Date.now();
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
        fetch('../OrganizationalChart/DAFE_OrgChart_process.php', {
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