<?php include '../../db.php'; ?>
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
  <style>
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
    
    .org-chart-container {
      width: 720px;
      height: 550px;
      overflow: hidden;
      background-color: rgba(255, 255, 255, 0.8);
    }
    
    h5 {
      -webkit-text-stroke: 0.15px black;
    }
  </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
  <div class="org-chart-container border-2 border-dcee-dark rounded-xl p-2 shadow-lg">
    
    <!-- Top Management Section -->
    <div class="mb-3">
        <div class="flex flex-col items-center gap-1" id="topManagement">
          <?php
          function getMember($code, $conn) {
            $stmt = $conn->prepare("SELECT * FROM DCEEE_Organization WHERE position_code = ?");
            $stmt->bind_param("s", $code);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
          }

          function getInitials($name) {
            // Common prefixes to exclude
            $prefixes = ['Mr.', 'Ms.', 'Mrs.', 'Miss', 'Dr.', 'Prof.', 'Sir', 'Madam', 'Engr.', 'Atty.'];
            
            // Split name into words
            $words = explode(' ', trim($name));
            
            // Filter out prefixes from the beginning
            $filteredWords = [];
            $skipPrefix = true;
            foreach ($words as $word) {
              if ($skipPrefix && in_array($word, $prefixes)) {
                continue; // Skip this prefix
              }
              $skipPrefix = false; // After first non-prefix, include all words
              $filteredWords[] = $word;
            }
            
            // Get first name and last name
            $initials = '';
            if (count($filteredWords) >= 2) {
              // First name is first word, last name is last word
              $firstName = $filteredWords[0];
              $lastName = end($filteredWords);
              $initials = strtoupper(substr($firstName, 0, 1)) . strtoupper(substr($lastName, 0, 1));
            } elseif (count($filteredWords) == 1) {
              // Only one name (after removing prefixes)
              $initials = strtoupper(substr($filteredWords[0], 0, 2));
            } else {
              // No valid name parts (unlikely)
              $initials = 'NA';
            }
            
            return $initials;
          }

          function getCleanName($name) {
            // Common prefixes to exclude
            $prefixes = ['Mr.', 'Ms.', 'Mrs.', 'Miss', 'Dr.', 'Prof.', 'Sir', 'Madam', 'Engr.', 'Atty.'];
            
            // Split name into words
            $words = explode(' ', trim($name));
            
            // Filter out prefixes from the beginning
            $filteredWords = [];
            $skipPrefix = true;
            foreach ($words as $word) {
              if ($skipPrefix && in_array($word, $prefixes)) {
                continue; // Skip this prefix
              }
              $skipPrefix = false; // After first non-prefix, include all words
              $filteredWords[] = $word;
            }
            
            return implode(' ', $filteredWords);
          }

          $positions = [
            'president' => 'President, CvSU',
            'vice_president' => 'Vice President, OVPAA',
            'dean' => 'Dean, CEIT',
            'chairperson' => 'Chairperson, DCEEE'
          ];

          foreach ($positions as $pos => $title) {
            $member = getMember($pos, $conn);
            echo "<div class='flex justify-center'>";
            if ($member) {
              $cleanName = getCleanName($member['name']);
              echo "<div class='org-box rounded-md shadow-md flex items-center space-x-2 h-[30px] w-[180px] border border-dcee-dark p-1 bg-white hover:shadow-md transition-all duration-200 cursor-pointer'
                    onclick='openModal(\"" . htmlspecialchars($member['name']) . "\", \"" . htmlspecialchars($member['role']) . "\", \"" . htmlspecialchars($member['photo']) . "\", \"" . htmlspecialchars($cleanName) . "\")'>
                    <div class='flex-shrink-0'>";
              if (!empty($member['photo'])) {
                echo "<img src='../OrganizationalChart/uploadDCEEE/" . htmlspecialchars($member['photo']) . "'
                          class='h-6 w-6 rounded-full border border-dcee-dark object-cover shadow-lg'
                          onerror=\"this.src='https://ui-avatars.com/api/?name=" . urlencode($cleanName) . "&background=800000&color=fff&size=36'\">
                      </div>";
              } else {
                $initials = getInitials($member['name']);
                echo "<div class='h-6 w-6 rounded-full border border-dcee-dark bg-dcee-dark text-white flex items-center justify-center text-sm font-bold shadow'>
                        $initials
                      </div></div>";
              }
              echo "<div class='text-[11px] leading-tight flex-1 min-w-0 text-left'>
                      <strong class='block truncate font-medium'>" . htmlspecialchars($member['name']) . "</strong>
                      <p class='text-gray-600 truncate'>" . htmlspecialchars($member['role']) . "</p>
                    </div>
                  </div>";
            } else {
              echo "<div class='org-box rounded-md shadow-md flex items-center space-x-2 h-[30px] w-[180px] border border-dcee-dark p-1 bg-white'>
                      <div class='h-6 w-6 rounded-full border border-dcee-dark bg-gray-200 flex items-center justify-center text-gray-400 text-sm font-bold'>
                        <i class=\"fas fa-user\"></i>
                      </div>
                      <div class='text-[11px] leading-tight flex-1 min-w-0 text-left'>
                        <strong class='block truncate font-medium'>-unfilled-</strong>
                        <p class='text-gray-600 truncate'>$title</p>
                      </div>
                    </div>";
            }
            echo "</div>";
          }
          ?>
        </div>
    </div>

    <!-- Divider -->
    <div class="border-t border-dcee-medium my-1"></div>

    <!-- Coordinators Section -->
    <div class="mb-2">
      <h5 class="text-center font-bold bg-dcee-medium text-white rounded-lg text-lg mb-1">Program Coordinators</h5>
      <div class="grid grid-cols-3 gap-2 justify-items-center max-h-[90px] overflow-y-auto px-2">
        <?php
        $coordinatorTitles = [
          1 => 'CpE Program Coordinator',
          2 => 'EcE Program Coordinator',
          3 => 'EE Program Coordinator'
        ];
        for ($i = 1; $i <= 3; $i++) {
          $position_code = 'coordinator_' . $i;
          $result = $conn->query("SELECT * FROM DCEEE_Organization WHERE position_code = '$position_code' ORDER BY id ASC");
          echo "<div class='flex flex-col items-center space-y-1 w-full'>";
          if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              $cleanName = getCleanName($row['name']);
              echo "<div class='org-box rounded-md shadow-md flex items-center space-x-2 h-[30px] w-[150px] border border-dcee-medium p-1 bg-white hover:shadow-md transition-all duration-200 cursor-pointer'
                      onclick='openModal(\"" . htmlspecialchars($row['name']) . "\", \"" . htmlspecialchars($row['role']) . "\", \"" . htmlspecialchars($row['photo']) . "\", \"" . htmlspecialchars($cleanName) . "\")'>
                      <div class='flex-shrink-0'>";
              if (!empty($row['photo'])) {
                echo "<img src='../OrganizationalChart/uploadDCEEE/" . htmlspecialchars($row['photo']) . "'
                          class='h-6 w-6 rounded-full border border-dcee-medium object-cover shadow-lg'
                          onerror=\"this.src='https://ui-avatars.com/api/?name=" . urlencode($cleanName) . "&background=990000&color=fff&size=36'\">
                      </div>";
              } else {
                $initials = getInitials($row['name']);
                echo "<div class='h-6 w-6 rounded-full border border-dcee-medium bg-dcee-medium text-white flex items-center justify-center text-sm font-bold shadow'>
                        $initials
                      </div></div>";
              }
              echo "<div class='text-[11px] leading-tight flex-1 min-w-0 text-left'>
                      <strong class='block truncate font-medium'>" . htmlspecialchars($row['name']) . "</strong>
                      <p class='text-gray-600 truncate'>" . htmlspecialchars($row['role']) . "</p>
                    </div>
                  </div>";
            }
          } else {
            echo "<div class='org-box rounded-md shadow-md flex items-center space-x-2 h-[30px] w-[150px] border border-dcee-medium p-1 bg-white'>
                    <div class='h-6 w-6 rounded-full border border-dcee-medium bg-gray-200 flex items-center justify-center text-gray-400 text-sm font-bold'>
                      <i class=\"fas fa-user\"></i>
                    </div>
                    <div class='text-[11px] leading-tight flex-1 min-w-0 text-left'>
                      <strong class='block truncate font-medium'>-unfilled-</strong>
                      <p class='text-gray-600 truncate'>" . $coordinatorTitles[$i] . "</p>
                    </div>
                  </div>";
          }
          echo "</div>";
        }
        ?>
      </div>
    </div>
    <div class="border-t border-dcee-light my-1"></div>
    <!-- Faculty Section -->
    <div class="mt-1">
      <h5 class="text-center font-bold bg-dcee-light text-white rounded-lg text-lg mb-1">Faculty Members</h5>
      <div class="grid grid-cols-3 gap-3 px-2">
        <?php
        $facultyTitles = [
          1 => 'CpE Faculty',
          2 => 'EcE Faculty',
          3 => 'EE Faculty'
        ];
        for ($i = 1; $i <= 3; $i++) {
          
          echo "<div class='border border-dcee-light rounded-lg p-2 bg-white/80 shadow-sm'>
                  <div class='grid grid-cols-2 gap-2 max-h-[240px] overflow-y-auto personnel-column'>";
          $position_pattern = 'faculty_' . $i . '_%';
          $result = $conn->query("SELECT * FROM DCEEE_Organization WHERE position_code LIKE '$position_pattern' ORDER BY id ASC");

          if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              $cleanName = getCleanName($row['name']);
              echo "<div class='org-box rounded-md shadow-md flex items-center space-x-2 h-[30px] w-full border border-dcee-light p-1 bg-white hover:shadow-md transition-all duration-200 cursor-pointer'
                      onclick='openModal(\"" . htmlspecialchars($row['name']) . "\", \"" . htmlspecialchars($row['role']) . "\", \"" . htmlspecialchars($row['photo']) . "\", \"" . htmlspecialchars($cleanName) . "\")'>
                      <div class='flex-shrink-0'>";
              if (!empty($row['photo'])) {
                echo "<img src='../OrganizationalChart/uploadDCEEE/" . htmlspecialchars($row['photo']) . "'
                          class='h-6 w-6 rounded-full border border-dcee-light object-cover shadow-lg'
                          onerror=\"this.src='https://ui-avatars.com/api/?name=" . urlencode($cleanName) . "&background=cc0000&color=fff&size=36'\">
                      </div>";
              } else {
                $initials = getInitials($row['name']);
                echo "<div class='h-6 w-6 rounded-full border border-dcee-light bg-dcee-light text-white flex items-center justify-center text-sm font-bold shadow'>
                        $initials
                      </div></div>";
              }
              echo "<div class='text-[11px] leading-tight flex-1 min-w-0 text-left'>
                      <strong class='block truncate font-medium'>" . htmlspecialchars($row['name']) . "</strong>
                      <p class='text-gray-600 truncate'>" . htmlspecialchars($row['role']) . "</p>
                    </div>
                  </div>";
            }
          } else {
            // Display placeholder in both columns when no faculty members
            echo "<div class='org-box rounded-md shadow-md flex items-center space-x-2 h-[30px] w-full border border-dcee-light p-1 bg-white col-span-2 justify-center'>
                    <div class='h-6 w-6 rounded-full border border-dcee-light bg-gray-200 flex items-center justify-center text-gray-400 text-sm font-bold'>
                      <i class=\"fas fa-user\"></i>
                    </div>
                    <div class='text-[11px] leading-tight flex-1 min-w-0 text-left'>
                      <strong class='block truncate font-medium'>-unfilled-</strong>
                      <p class='text-gray-600 truncate'>" . $facultyTitles[$i] . "</p>
                    </div>
                  </div>";
          }
          echo "</div></div>";
        }
        ?>
      </div>
    </div>
  </div>
  
  <!-- Profile Modal -->
  <div id="profileModal" class="modal fixed inset-0 z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black/50" onclick="closeModal()"></div>
    <div class="modal-container fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-[280px] z-10">
      <div class="bg-white rounded-xl shadow-2xl transform transition-all">
        <div class="bg-gradient-to-r from-dcee-dark to-dcee-medium text-white p-3 rounded-t-xl">
          <div class="flex justify-between items-center">
            <h3 class="text-md font-bold">Profile Details</h3>
            <button onclick="closeModal()" class="text-white hover:text-dcee-light transition-colors">
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
          <button onclick="closeModal()" class="w-full py-1 px-3 bg-dcee-dark hover:bg-dcee-medium text-white font-medium rounded-lg transition-colors duration-200 text-xs">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
  
  <script>
    function openModal(name, role, photo, cleanName) {
      document.getElementById("modalName").textContent = name;
      document.getElementById("modalRole").textContent = role;
      
      const photoElement = document.getElementById("modalPhoto");
      photoElement.src = '../OrganizationalChart/uploadDCEEE/' + photo;
      photoElement.onerror = function() {
        this.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(cleanName) + '&background=800000&color=fff&size=80';
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