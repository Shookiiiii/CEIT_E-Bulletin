<?php include '../../db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DIET Organizational Chart - View Only</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    :root {
        --maroon-500: #800000;
        --maroon-600: #700000;
        --maroon-700: #600000;
        --maroon-800: #500000;
    }
    .border-maroon-500 {
        border-color: var(--maroon-500);
    }
    .text-maroon-600 {
        color: var(--maroon-600);
    }
    .bg-maroon-600 {
        background-color: var(--maroon-600);
    }
    .bg-maroon-800 {
        background-color: var(--maroon-800);
    }
    .hover\:bg-maroon-600:hover {
        background-color: var(--maroon-600);
    }
    .bg-maroon-50 {
        background-color: rgba(128, 0, 0, 0.05);
    }
    
    /* Custom scrollbar styles */
    .personnel-column::-webkit-scrollbar {
      width: 4px;
    }
    
    .personnel-column::-webkit-scrollbar-track {
      background: rgba(241, 241, 241, 0.5);
      border-radius: 10px;
    }
    
    .personnel-column::-webkit-scrollbar-thumb {
      background: rgba(136, 136, 136, 0.7);
      border-radius: 10px;
    }
    
    .personnel-column::-webkit-scrollbar-thumb:hover {
      background: rgba(85, 85, 85, 0.7);
    }
    
    /* Modal backdrop blur */
    .modal-backdrop {
      backdrop-filter: blur(4px);
    }
    
    /* Text truncation styles */
    .truncate-name {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 120px;
      display: block;
    }
    
    .truncate-role {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 90px;
      display: block;
    }
  </style>
</head>
<body class="bg-gray-50">
  <?php
  function getInitials($name) {
    $prefixes = ['Mr.', 'Mrs.', 'Ms.', 'Dr.', 'Prof.', 'Engr.'];
    
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
  
  function getMember($code, $conn) {
    $stmt = $conn->prepare("SELECT * FROM DIET_Organization WHERE position_code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }
  
  function getFacultyMembers($group, $conn) {
    $stmt = $conn->prepare("SELECT * FROM DIET_Organization WHERE position_code LIKE ? ORDER BY id ASC");
    $pattern = 'faculty_' . $group . '_%';
    $stmt->bind_param("s", $pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $members = [];
    while ($row = $result->fetch_assoc()) {
      $members[] = $row;
    }
    return $members;
  }
  
  function showBox($member, $position_code, $role = '') {
    if (!$member) {
      $displayRole = !empty($role) ? $role : $position_code;
      
      return "<div class='border border-maroon-500 p-1 rounded-md bg-white shadow-md text-left h-[30px] w-[150px] flex items-center space-x-1 mb-1'>
          <div class='h-6 w-6 rounded-full flex items-center justify-center text-white font-bold text-xs shadow-lg' style='background-color: #700000;'>NA</div>
          <div class='text-[9px] leading-tight flex-1 min-w-0'>
            <strong class='truncate-name'>Full Name</strong>
            <p class='text-gray-600 truncate-role'>" . htmlspecialchars($displayRole) . "</p>
          </div>
        </div>";
    }
    
    $photoElement = '';
    if (!empty($member['photo'])) {
      $photoElement = "<img src='../OrganizationalChart/uploadDIET/" . htmlspecialchars($member['photo']) . "' class='h-6 w-6 rounded-full border border-maroon-500 object-cover shadow-lg'>";
    } else {
      $initials = getInitials($member['name']);
      $photoElement = "<div class='h-6 w-6 rounded-full flex items-center justify-center text-white font-bold text-xs shadow-lg' style='background-color: #700000;'>$initials</div>";
    }
    
    return "<div class='border border-maroon-500 p-1 rounded-md bg-white shadow-md text-left h-[30px] w-[150px] flex items-center space-x-1 mb-1 cursor-pointer hover:shadow-lg transition-all duration-200' onclick='openModal(\"" . htmlspecialchars($member['name']) . "\", \"" . htmlspecialchars($member['role']) . "\", \"" . htmlspecialchars($member['photo']) . "\")'>
        $photoElement
        <div class='text-[9px] leading-tight flex-1 min-w-0'>
          <strong class='truncate-name'>" . htmlspecialchars($member['name']) . "</strong>
          <p class='text-gray-600 truncate-role'>" . htmlspecialchars($member['role']) . "</p>
        </div>
      </div>";
  }
  ?>
  
  <div class="container w-[720px] h-[550px] mx-auto px-2 py-3 text-center overflow-hidden" id="orgChartContainer">
    <div class="border-2 border-maroon-500 rounded-xl p-2 bg-white/50 shadow-lg h-full flex flex-col">
      
      <!-- Top Management Section -->
      <div class="flex flex-col items-center gap-1 mb-2" id="topManagementContainer">
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
      
      <!-- Coordinators Section -->
      <div class="mb-2">
        <h5 class="text-xs font-semibold mb-1 text-maroon-600">Coordinators</h5>
        <div class="grid grid-cols-4 gap-2 justify-items-center">
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
      
      <!-- Faculty Section -->
      <div class="flex-1 h-full">
        <h5 class="text-xs font-semibold mb-1 text-maroon-600">Faculty</h5>
        <div class="grid grid-cols-4 gap-2 h-[270px]">
          <?php
          for ($i = 1; $i <= 4; $i++) {
            echo "<div class='flex flex-col items-center w-full border border-maroon-500 rounded-lg p-1 bg-white shadow-sm overflow-hidden'>";            
            $result = $conn->query("SELECT * FROM DIET_Organization WHERE position_code LIKE 'faculty_" . $i . "_%' ORDER BY id ASC");
            
            if ($result->num_rows > 0) {
              echo "<div class='flex-1 overflow-y-auto w-full personnel-column'>";
              while ($row = $result->fetch_assoc()) {
                echo showBox($row, $row['position_code']);
              }
              echo "</div>";
            } else {
              echo "<div class='text-gray-500 italic text-[8px] mb-1 h-8 flex items-center justify-center'>No faculty added yet</div>";
            }
            
            echo "</div>";
          }
          ?>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Profile Modal -->
  <div id="profileModal" class="modal fixed inset-0 z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black/50" onclick="closeModal()"></div>
    <div class="modal-container fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-[280px] z-10">
      <div class="bg-white rounded-xl shadow-2xl transform transition-all">
        <div class="bg-gradient-to-r from-maroon-600 to-maroon-800 text-white p-3 rounded-t-xl">
          <div class="flex justify-between items-center">
            <h3 class="text-md font-bold">Profile Details</h3>
            <button onclick="closeModal()" class="text-maroon-500 hover:text-maroon-500 transition-colors">
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
          <button onclick="closeModal()" class="w-full py-1 px-3 bg-maroon-600 hover:bg-maroon-700 text-white font-medium rounded-lg transition-colors duration-200 text-xs">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
  
  <script>
    function removePrefix(name) {
      if (!name) return '';
      
      const prefixes = ['Mr.', 'Mrs.', 'Ms.', 'Miss', 'Dr.', 'Prof.', 'Sir', 'Madam', 'Engr.', 'Atty.'];
      let cleanName = name.trim();
      
      for (const prefix of prefixes) {
        if (cleanName.startsWith(prefix)) {
          cleanName = cleanName.substring(prefix.length).trim();
          break;
        }
      }
      
      return cleanName;
    }
    
    function getFirstAndLastName(name) {
      if (!name) return '';
      
      const cleanName = removePrefix(name);
      const words = cleanName.split(' ');
      
      if (words.length >= 2) {
        return words[0] + ' ' + words[words.length - 1];
      }
      
      return cleanName;
    }
    
    function openModal(name, role, photo) {
      // Display the full name (with prefix) in the modal
      document.getElementById("modalName").textContent = name;
      document.getElementById("modalRole").textContent = role;
      
      const photoElement = document.getElementById("modalPhoto");
      photoElement.src = '../OrganizationalChart/uploadDIET/' + photo;
      
      // For the avatar fallback, use the name without prefix
      photoElement.onerror = function() {
        const cleanName = getFirstAndLastName(name);
        this.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(cleanName) + '&background=700000&color=fff&size=80';
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