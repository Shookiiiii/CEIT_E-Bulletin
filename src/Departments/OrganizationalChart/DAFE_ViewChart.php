<?php include '../../db.php'; ?>
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
  <style>
    .faculty-column::-webkit-scrollbar,
    .unithead-column::-webkit-scrollbar,
    .adviser-column::-webkit-scrollbar {
      width: 4px;
    }
    
    .faculty-column::-webkit-scrollbar-track,
    .unithead-column::-webkit-scrollbar-track,
    .adviser-column::-webkit-scrollbar-track {
      background: rgba(241, 241, 241, 0.5);
      border-radius: 10px;
    }
    
    .faculty-column::-webkit-scrollbar-thumb,
    .unithead-column::-webkit-scrollbar-thumb,
    .adviser-column::-webkit-scrollbar-thumb {
      background: rgba(136, 136, 136, 0.7);
      border-radius: 10px;
    }
    
    .faculty-column::-webkit-scrollbar-thumb:hover,
    .unithead-column::-webkit-scrollbar-thumb:hover,
    .adviser-column::-webkit-scrollbar-thumb:hover {
      background: rgba(85, 85, 85, 0.7);
    }
    
    .modal-backdrop {
      backdrop-filter: blur(4px);
    }
    
    .org-chart-container {
      width: 720px;
      height: 550px;
      overflow: hidden;
      background-color: rgba(255, 255, 255, 0.8);
    }
    
    .semi-transparent-bg {
      background-color: rgba(255, 255, 255, 0.8);
    }
    
    .profile-box {
      height: 25px;
      width: 150px;
    }
    
    .profile-image {
      height: 20px;
      width: 20px;
    }
    
    .profile-initials {
      height: 20px;
      width: 20px;
      font-size: 8px;
    }
  </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
  <div class="org-chart-container border-2 border-[#065F46] rounded-xl p-2 shadow-lg">
    
    <div class="mb-4">
        <div class="flex flex-col items-center gap-1" id="topManagement">
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
    
    <div class="relative my-1">
      <div class="absolute inset-0 flex items-center">
        <div class="w-full border-t border-[#065F46]"></div>
      </div>
    </div>
    
    <div class="mt-1">
      <div class="border-t border-[#065F46] pt-1">
        <div class="grid grid-cols-2 gap-1 justify-items-center mb-1" id="centerManagement">
          <div class="w-full">
            <h2 class="text-center font-bold bg-[#047857] text-white rounded-lg text-sm uppercase tracking-wide mb-1">
              Faculty
            </h2>
            <div class="w-full border border-[#047857] rounded-lg p-2 semi-transparent-bg shadow-sm faculty-column max-h-[200px] overflow-y-auto" id="faculty-column">
              <?php
              $result = $conn->query("SELECT * FROM DAFE_Organization WHERE position_code LIKE 'faculty\_%' ORDER BY id ASC");
              $facultyMembers = [];
              
              if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  $facultyMembers[] = $row;
                }
              }
              
              echo "<div class='flex flex-col items-center space-y-1'>";
              if (!empty($facultyMembers)) {
                foreach ($facultyMembers as $member) {
                  echo showBox($member, $member['position_code'], 'faculty');
                }
              } else {
                echo showBox(null, 'faculty_1', 'faculty', 'Faculty Member');
              }
              echo "</div>";
              ?>
            </div>
          </div>
          
          <div class="w-full">
            <h2 class="text-center font-bold bg-[#047857] text-white rounded-lg text-sm uppercase tracking-wide mb-1">
              Unit Heads
            </h2>
            <div class="w-full border border-[#047857] rounded-lg p-2 semi-transparent-bg shadow-sm unithead-column max-h-[200px] overflow-y-auto" id="unithead-column">
              <?php
              $result = $conn->query("SELECT * FROM DAFE_Organization WHERE position_code LIKE 'unithead\_%' ORDER BY id ASC");
              $unitheadMembers = [];
              
              if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  $unitheadMembers[] = $row;
                }
              }
              
              echo "<div class='flex flex-col items-center space-y-1'>";
              if (!empty($unitheadMembers)) {
                foreach ($unitheadMembers as $member) {
                  echo showBox($member, $member['position_code'], 'unithead');
                }
              } else {
                echo showBox(null, 'unithead_1', 'unithead', 'Unit Head');
              }
              echo "</div>";
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="relative my-1">
      <div class="absolute inset-0 flex items-center">
        <div class="w-full border-t border-[#065F46] mt-2"></div>
      </div>
    </div>
    
    <div class="mt-3">
        <h2 class="text-center bg-[#10B981] font-bold text-white rounded-lg text-sm uppercase tracking-wide mb-2">
          Advisers
        </h2>
        <div class="flex flex-col items-center space-y-1 adviser-column max-h-[180px] overflow-y-auto" id="adviserSection">
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
            
            echo "<div class='grid grid-cols-2 gap-4 w-full'>";
            
            echo "<div class='flex flex-col items-center space-y-2'>";
            foreach ($firstHalf as $member) {
              echo "<div class='flex justify-center'>" . showBox($member, $member['position_code'], 'adviser') . "</div>";
            }
            echo "</div>";
            
            echo "<div class='flex flex-col items-center space-y-2'>";
            foreach ($secondHalf as $member) {
              echo "<div class='flex justify-center'>" . showBox($member, $member['position_code'], 'adviser') . "</div>";
            }
            echo "</div>";
            
            echo "</div>";
          } elseif (count($advisers) > 0) {
            foreach ($advisers as $member) {
              echo "<div class='flex justify-center'>" . showBox($member, $member['position_code'], 'adviser') . "</div>";
            }
          } else {
            echo "<div class='flex justify-center'>" . showBox(null, 'adviser_1', 'adviser', 'Adviser') . "</div>";
          }
          ?>
        </div>
      </div>
    </div>
  </div>

<div id="profileModal" class="modal fixed inset-0 z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black/50" onclick="closeModal()"></div>
    <div class="modal-container fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-[280px] z-10">
        <div class="bg-white rounded-xl shadow-2xl transform transition-all">
            <div class="bg-gradient-to-r from-modal-green to-adviser-light-green text-white p-3 rounded-t-xl">
                <div class="flex justify-between items-center">
                    <h3 class="text-md font-bold">Profile Details</h3>
                    <button onclick="closeModal()" class="text-white hover:text-green-200 transition-colors">
                        <i class="fas fa-times text-md"></i>
                    </button>
                </div>
            </div>
            
            <div class="p-3">
                <div class="flex flex-col items-center mb-2">
                    <div class="relative mb-2">
                        <img id="modalPhoto" src="" alt="Profile Photo" class="h-20 w-20 rounded-full object-cover border-4 border-white shadow-lg">
                        <div class="absolute inset-0 rounded-full bg-gradient-to-t from-black/30 to-transparent"></div>
                    </div>
                    <h4 id="modalName" class="text-md font-bold text-gray-800 mb-1 text-center break-words max-w-[200px]"></h4>
                    <p id="modalRole" class="text-gray-600 text-center text-sm break-words max-w-[200px]"></p>
                </div>
            </div>
            
            <div class="bg-gray-50 px-3 py-2 rounded-b-xl">
                <button onclick="closeModal()" class="w-full py-1 px-3 bg-modal-green hover:bg-faculty-moderate-green text-white font-medium rounded-lg transition-colors duration-200 text-xs">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<?php
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

function getNameWithoutPrefix($name) {
    if (empty($name)) return '';
    
    $prefixes = ['Mr.', 'Mrs.', 'Ms.', 'Miss', 'Dr.', 'Prof.', 'Sir', 'Engr'];
    
    $cleanName = trim($name);
    
    foreach ($prefixes as $prefix) {
        if (strpos($cleanName, $prefix) === 0) {
            $cleanName = trim(substr($cleanName, strlen($prefix)));
            break;
        }
    }
    
    return $cleanName;
}

function showBox($member, $position_code, $section, $defaultRole = '')
{
    $profileHtml = '';
    
    $circleBgColor = '';
    $boxBorderColor = '';
    
    if ($section === 'top') {
        $circleBgColor = 'bg-[#065F46]';
        $boxBorderColor = 'border-[#065F46]';
    } elseif ($section === 'faculty' || $section === 'unithead') {
        $circleBgColor = 'bg-[#047857]';
        $boxBorderColor = 'border-[#047857]';
    } elseif ($section === 'adviser') {
        $circleBgColor = 'bg-[#10B981]';
        $boxBorderColor = 'border-[#10B981]';
    }
    
    if (!$member) {
        $profileHtml = "<div class='h-5 w-5 rounded-full border {$boxBorderColor} bg-gray-200 flex items-center justify-center text-gray-400 text-xs shadow-lg'>
                            <i class=\"fas fa-user\"></i>
                        </div>";
    } else {
        if (!empty($member['photo'])) {
            $profileHtml = "<img src='../OrganizationalChart/uploadDAFE/" . htmlspecialchars($member['photo']) . "' class='profile-image rounded-full border object-cover shadow-lg'>";
        } else {
            $initial = getInitialWithoutPrefix($member['name']);
            $profileHtml = "<div class='profile-initials rounded-full border {$circleBgColor} text-white font-bold flex items-center justify-center shadow-lg'>" . htmlspecialchars($initial) . "</div>";
        }
    }
    
    if (!$member) {
        return "<div class='{$boxBorderColor} border p-1 rounded-md bg-white shadow-md text-left profile-box flex items-center space-x-2'>
            $profileHtml
            <div class='text-[9px] leading-tight flex-1 min-w-0'>
                <strong class='block truncate font-medium'>-unfilled-</strong>
                <p class='text-gray-600 truncate'>" . htmlspecialchars($defaultRole) . "</p>
            </div>
        </div>";
    }
     
    return "<div class='{$boxBorderColor} border p-1 rounded-md bg-white shadow-md text-left profile-box flex items-center space-x-2 cursor-pointer' onclick='openModal(\"" . htmlspecialchars($member['name']) . "\", \"" . htmlspecialchars($member['role']) . "\", \"" . htmlspecialchars($member['photo']) . "\")'>
        $profileHtml
        <div class='text-[9px] leading-tight flex-1 min-w-0'>
            <strong class='block truncate font-medium'>" . htmlspecialchars($member['name']) . "</strong>
            <p class='text-gray-600 truncate'>" . htmlspecialchars($member['role']) . "</p>
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

<script>
    function getNameWithoutPrefix(name) {
        if (!name) return '';
        
        const prefixes = ['Mr.', 'Mrs.', 'Ms.', 'Miss', 'Dr.', 'Prof.', 'Sir', 'Engr'];
        let cleanName = name.trim();
        
        for (const prefix of prefixes) {
            if (cleanName.startsWith(prefix)) {
                cleanName = cleanName.substring(prefix.length).trim();
                break;
            }
        }
        
        return cleanName;
    }
    
    function openModal(name, role, photo) {
      document.getElementById("modalName").textContent = name;
      document.getElementById("modalRole").textContent = role;
      
      const photoElement = document.getElementById("modalPhoto");
      const cleanName = getNameWithoutPrefix(name);
      
      photoElement.src = '../OrganizationalChart/uploadDAFE/' + photo;
      photoElement.onerror = function() {
        this.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(cleanName) + '&background=065F46&color=fff&size=80';
      };
      
      const modal = document.getElementById("profileModal");
      modal.classList.remove("hidden");
      document.body.classList.add("modal-open");
      setTimeout(() => {
        modal.classList.add("opacity-100");
      }, 10);
    }
    
    function closeModal() {
      const modal = document.getElementById("profileModal");
      modal.classList.remove("opacity-100");
      document.body.classList.remove("modal-open");
      setTimeout(() => {
        modal.classList.add("hidden");
      }, 250);
    }
    
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeModal();
      }
    });
</script>
</body>
</html>