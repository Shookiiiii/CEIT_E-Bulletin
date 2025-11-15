<?php include '../../db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DIT Organizational View Chart</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'cs-orange': '#FF9500',
            'it-orange': '#FF9500',
            'top-orange': '#FF6B00',
          },
          spacing: {
            'compact': '150px',
          },
        },
      },
    }
  </script>
  <style>
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
    
    .modal-backdrop {
      backdrop-filter: blur(4px);
    }
    
    .org-chart-container {
      width: 720px;
      height: 550px;
      overflow: hidden;
      background-color: rgba(255, 255, 255, 0.8);
    }
  </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
  <div class="org-chart-container border-2 border-orange-500 rounded-xl p-2 shadow-lg">
    <div class="mb-3">
        <div class="flex flex-col items-center gap-1" id="topManagement">
          <?php
          function getMember($code, $conn) {
            $stmt = $conn->prepare("SELECT * FROM DIT_Organization WHERE position_code = ?");
            $stmt->bind_param("s", $code);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
          }
          
          function getFacultyMembers($unit, $conn) {
            $stmt = $conn->prepare("SELECT * FROM DIT_Organization WHERE position_code LIKE ? ORDER BY 
                          CASE 
                              WHEN role LIKE '%Associate Professor%' THEN
                                  CASE 
                                      WHEN role LIKE '%IV%' THEN 1
                                      WHEN role LIKE '%III%' THEN 2
                                      WHEN role LIKE '%II%' THEN 3
                                      WHEN role LIKE '%I%' THEN 4
                                      ELSE 5
                                  END
                              WHEN role LIKE '%Assistant Professor%' THEN
                                  CASE 
                                      WHEN role LIKE '%IV%' THEN 6
                                      WHEN role LIKE '%III%' THEN 7
                                      WHEN role LIKE '%II%' THEN 8
                                      WHEN role LIKE '%I%' THEN 9
                                      ELSE 10
                                  END
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
          
          function formatCoordinatorRole($role, $position) {
            $defaultRoles = [
              'cs_coordinator' => 'CS Coordinator',
              'it_coordinator' => 'IT Coordinator'
            ];
            
            $positionTitle = isset($defaultRoles[$position]) ? $defaultRoles[$position] : 'Coordinator';
            
            if (strpos($role, $positionTitle) !== false) {
              return htmlspecialchars($role);
            }
            
            return $positionTitle . '<br><span class="text-[7px] font-normal">' . htmlspecialchars($role) . '</span>';
          }
          
          function getInitials($name) {
            // Common prefixes to exclude
            $prefixes = ['Mr.', 'Mrs.', 'Ms.', 'Miss', 'Dr.', 'Prof.', 'Sir', 'Madam', 'Engr.', 'Atty.'];
            
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
          
          $positions = ['president', 'vice_president', 'dean', 'chairperson'];
          foreach ($positions as $position) {
            $member = getMember($position, $conn);
            if ($member):
              $initials = getInitials($member['name']);
              echo "<div class='flex justify-center'>
                      <div class='org-box rounded-md shadow-md text-left flex items-center space-x-1 h-[30px] w-[150px] border border-top-orange p-[2px] ml-[1px] mb-[-1px] bg-white/90 hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 cursor-pointer' onclick='openModal(\"" . htmlspecialchars($member['name']) . "\", \"" . htmlspecialchars($member['role']) . "\", \"" . htmlspecialchars($member['photo']) . "\")'>
                        <div class='flex-shrink-0'>";
              if (!empty($member['photo'])) {
                echo "<img src='../OrganizationalChart/uploadDIT/" . htmlspecialchars($member['photo']) . "' 
                             class='h-6 w-6 rounded-full border border-top-orange object-cover shadow-lg'
                             onerror=\"this.style.display='none'; this.nextElementSibling.style.display='flex';\">
                      <div class='hidden absolute inset-0 flex items-center justify-center h-6 w-6 rounded-full border border-top-orange bg-top-orange text-white text-xs font-bold shadow'>
                        $initials
                      </div>";
              } else {
                echo "<div class='h-6 w-6 rounded-full border border-top-orange bg-top-orange text-white flex items-center justify-center text-xs font-bold shadow'>
                        $initials
                      </div>";
              }
              echo "</div>
                        <div class='text-[9px] leading-tight flex-1 min-w-0'>
                          <strong class='block truncate font-medium'>" . htmlspecialchars($member['name']) . "</strong>
                          <p class='text-gray-600 truncate'>" . htmlspecialchars($member['role']) . "</p>
                        </div>
                      </div>
                    </div>";
            else:
              $defaultRoles = [
                'president' => 'President',
                'vice_president' => 'Vice President',
                'dean' => 'Dean',
                'chairperson' => 'Chairperson'
              ];
              $defaultRole = isset($defaultRoles[$position]) ? $defaultRoles[$position] : 'Position';
              
              echo "<div class='flex justify-center'>
                      <div class='org-box rounded-md shadow-md text-left flex items-center space-x-1 h-[30px] w-[150px] border border-top-orange p-[2px] ml-[1px] mb-[-1px] bg-white/90'>
                        <div class='h-6 w-6 rounded-full border border-top-orange bg-gray-200 flex items-center justify-center text-gray-400 text-xs shadow-lg'>
                          <i class=\"fas fa-user\"></i>
                        </div>
                        <div class='text-[9px] leading-tight flex-1 min-w-0'>
                          <strong class='block truncate font-medium'>-unfilled-</strong>
                          <p class='text-gray-600 truncate'>" . htmlspecialchars($defaultRole) . "</p>
                        </div>
                      </div>
                    </div>";
            endif;
          }
          ?>
        </div>
    </div>
    
    <div class="relative my-1">
      <div class="absolute inset-0 flex items-center">
        <div class="w-full border-t border-orange-500"></div>
      </div>
    </div>
    
    <div class="mt-1">
      <div class="border-t border-orange-500 pt-1">
        <div class="grid grid-cols-2 gap-4 justify-items-center mb-1">
          <div id="csCoordinator">
            <?php
            $csCoord = getMember('cs_coordinator', $conn);
            if ($csCoord):
              $initials = getInitials($csCoord['name']);
            ?>
            <div class='org-box rounded-md shadow-md text-left flex items-center space-x-1 h-[35px] w-[130px] border border-cs-orange p-[2px] ml-[1px] mb-[-1px] bg-white/90 hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 cursor-pointer' onclick='openModal("<?php echo htmlspecialchars($csCoord['name']); ?>", "<?php echo htmlspecialchars($csCoord['role']); ?>", "<?php echo htmlspecialchars($csCoord['photo']); ?>")'>
              <div class='flex-shrink-0'>
                <?php if (!empty($csCoord['photo'])): ?>
                  <img src='../OrganizationalChart/uploadDIT/<?php echo htmlspecialchars($csCoord['photo']); ?>' 
                       class='h-6 w-6 rounded-full border border-cs-orange object-cover shadow-lg'
                       onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                  <div class='hidden absolute inset-0 flex items-center justify-center h-6 w-6 rounded-full border border-cs-orange bg-cs-orange text-white text-xs font-bold shadow'>
                    <?php echo $initials; ?>
                  </div>
                <?php else: ?>
                  <div class='h-6 w-6 rounded-full border border-cs-orange bg-cs-orange text-white flex items-center justify-center text-xs font-bold shadow'>
                    <?php echo $initials; ?>
                  </div>
                <?php endif; ?>
              </div>
              <div class='text-[9px] leading-tight flex-1 min-w-0'>
                <strong class='block truncate font-medium'><?php echo htmlspecialchars($csCoord['name']); ?></strong>
                <p class='text-gray-600 leading-tight'><?php echo formatCoordinatorRole($csCoord['role'], 'cs_coordinator'); ?></p>
              </div>
            </div>
            <?php else: ?>
            <div class='org-box rounded-md shadow-md text-left flex items-center space-x-1 h-[35px] w-[130px] border border-cs-orange p-[2px] ml-[1px] mb-[-1px] bg-white/90'>
              <div class='h-6 w-6 rounded-full border border-cs-orange bg-gray-200 flex items-center justify-center text-gray-400 text-xs shadow-lg'>
                <i class="fas fa-user"></i>
              </div>
              <div class='text-[9px] leading-tight flex-1 min-w-0'>
                <strong class='block truncate font-medium'>No Coordinator</strong>
                <p class='text-gray-600 truncate'>Computer Science</p>
              </div>
            </div>
            <?php endif; ?>
          </div>
          
          <div id="itCoordinator">
            <?php
            $itCoord = getMember('it_coordinator', $conn);
            if ($itCoord):
              $initials = getInitials($itCoord['name']);
            ?>
            <div class='org-box rounded-md shadow-md text-left flex items-center space-x-1 h-[35px] w-[130px] border border-it-orange p-[2px] ml-[1px] mb-[-1px] bg-white/90 hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 cursor-pointer' onclick='openModal("<?php echo htmlspecialchars($itCoord['name']); ?>", "<?php echo htmlspecialchars($itCoord['role']); ?>", "<?php echo htmlspecialchars($itCoord['photo']); ?>")'>
              <div class='flex-shrink-0'>
                <?php if (!empty($itCoord['photo'])): ?>
                  <img src='../OrganizationalChart/uploadDIT/<?php echo htmlspecialchars($itCoord['photo']); ?>' 
                       class='h-6 w-6 rounded-full border border-it-orange object-cover shadow-lg'
                       onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                  <div class='hidden absolute inset-0 flex items-center justify-center h-6 w-6 rounded-full border border-it-orange bg-it-orange text-white text-xs font-bold shadow'>
                    <?php echo $initials; ?>
                  </div>
                <?php else: ?>
                  <div class='h-6 w-6 rounded-full border border-it-orange bg-it-orange text-white flex items-center justify-center text-xs font-bold shadow'>
                    <?php echo $initials; ?>
                  </div>
                <?php endif; ?>
              </div>
              <div class='text-[9px] leading-tight flex-1 min-w-0'>
                <strong class='block truncate font-medium'><?php echo htmlspecialchars($itCoord['name']); ?></strong>
                <p class='text-gray-600 leading-tight'><?php echo formatCoordinatorRole($itCoord['role'], 'it_coordinator'); ?></p>
              </div>
            </div>
            <?php else: ?>
            <div class='org-box rounded-md shadow-md text-left flex items-center space-x-1 h-[35px] w-[130px] border border-it-orange p-[2px] ml-[1px] mb-[-1px] bg-white/90'>
              <div class='h-6 w-6 rounded-full border border-it-orange bg-gray-200 flex items-center justify-center text-gray-400 text-xs shadow-lg'>
                <i class="fas fa-user"></i>
              </div>
              <div class='text-[9px] leading-tight flex-1 min-w-0'>
                <strong class='block truncate font-medium'>No Coordinator</strong>
                <p class='text-gray-600 truncate'>Information Technology</p>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4" id="personnelGrid">
          <?php
          $csFaculty = getFacultyMembers('cs', $conn);
          $itFaculty = getFacultyMembers('it', $conn);
          
          echo "<div class='unit-container border-2 border-cs-orange rounded-lg p-2 bg-white/70 shadow-sm'>";
          echo "<div class='unit-personnel-container grid grid-cols-2 gap-2 max-h-[300px] overflow-y-auto'>";
          
          echo "<div class='personnel-column flex flex-col items-center space-y-1' id='column-1'>";
          for ($i = 0; $i < count($csFaculty); $i += 2) {
              if (isset($csFaculty[$i])) {
                  $personnel = $csFaculty[$i];
                  $initials = getInitials($personnel['name']);
                  
                  echo "<div class='org-box rounded-md shadow-md text-left flex items-center space-x-1 h-[29px] w-[145px] border border-cs-orange p-[2px] ml-[1px] mb-[-1px] bg-white/90 hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 cursor-pointer' onclick='openModal(\"" . htmlspecialchars($personnel['name']) . "\", \"" . htmlspecialchars($personnel['role']) . "\", \"" . htmlspecialchars($personnel['photo']) . "\")'>
                          <div class='flex-shrink-0'>";
                  if (!empty($personnel['photo'])) {
                      echo "<img src='../OrganizationalChart/uploadDIT/" . htmlspecialchars($personnel['photo']) . "' 
                                   class='h-5 w-5 rounded-full border border-cs-orange object-cover shadow-lg'
                                   onerror=\"this.style.display='none'; this.nextElementSibling.style.display='flex';\">
                                <div class='hidden absolute inset-0 flex items-center justify-center h-5 w-5 rounded-full border border-cs-orange bg-cs-orange text-white text-xs font-bold shadow'>
                                  $initials
                                </div>";
                  } else {
                      echo "<div class='h-5 w-5 rounded-full border border-cs-orange bg-cs-orange text-white flex items-center justify-center text-xs font-bold shadow'>
                                  $initials
                                </div>";
                  }
                  echo "</div>
                          <div class='text-[9px] leading-tight flex-1 min-w-0'>
                            <strong class='block truncate font-medium'>" . htmlspecialchars($personnel['name']) . "</strong>
                            <p class='text-gray-600 truncate'>" . htmlspecialchars($personnel['role']) . "</p>
                          </div>
                        </div>";
              }
          }
          echo "</div>";
          
          echo "<div class='personnel-column flex flex-col items-center space-y-1' id='column-2'>";
          for ($i = 1; $i < count($csFaculty); $i += 2) {
              if (isset($csFaculty[$i])) {
                  $personnel = $csFaculty[$i];
                  $initials = getInitials($personnel['name']);
                  
                  echo "<div class='org-box rounded-md shadow-md text-left flex items-center space-x-1 h-[29px] w-[145px] border border-cs-orange p-[2px] ml-[1px] mb-[-1px] bg-white/90 hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 cursor-pointer' onclick='openModal(\"" . htmlspecialchars($personnel['name']) . "\", \"" . htmlspecialchars($personnel['role']) . "\", \"" . htmlspecialchars($personnel['photo']) . "\")'>
                          <div class='flex-shrink-0'>";
                  if (!empty($personnel['photo'])) {
                      echo "<img src='../OrganizationalChart/uploadDIT/" . htmlspecialchars($personnel['photo']) . "' 
                                   class='h-5 w-5 rounded-full border border-cs-orange object-cover shadow-lg'
                                   onerror=\"this.style.display='none'; this.nextElementSibling.style.display='flex';\">
                                <div class='hidden absolute inset-0 flex items-center justify-center h-5 w-5 rounded-full border border-cs-orange bg-cs-orange text-white text-xs font-bold shadow'>
                                  $initials
                                </div>";
                  } else {
                      echo "<div class='h-5 w-5 rounded-full border border-cs-orange bg-cs-orange text-white flex items-center justify-center text-xs font-bold shadow'>
                                  $initials
                                </div>";
                  }
                  echo "</div>
                          <div class='text-[9px] leading-tight flex-1 min-w-0'>
                            <strong class='block truncate font-medium'>" . htmlspecialchars($personnel['name']) . "</strong>
                            <p class='text-gray-600 truncate'>" . htmlspecialchars($personnel['role']) . "</p>
                          </div>
                        </div>";
              }
          }
          echo "</div>";
          
          echo "</div>";
          echo "</div>";
          
          echo "<div class='unit-container border-2 border-it-orange rounded-lg p-2 bg-white/70 shadow-sm'>";
          echo "<div class='unit-personnel-container grid grid-cols-2 gap-2 max-h-[300px] overflow-y-auto'>";
          
          echo "<div class='personnel-column flex flex-col items-center space-y-1' id='column-3'>";
          for ($i = 0; $i < count($itFaculty); $i += 2) {
              if (isset($itFaculty[$i])) {
                  $personnel = $itFaculty[$i];
                  $initials = getInitials($personnel['name']);
                  
                  echo "<div class='org-box rounded-md shadow-md text-left flex items-center space-x-1 h-[29px] w-[145px] border border-it-orange p-[2px] ml-[1px] mb-[-1px] bg-white/90 hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 cursor-pointer' onclick='openModal(\"" . htmlspecialchars($personnel['name']) . "\", \"" . htmlspecialchars($personnel['role']) . "\", \"" . htmlspecialchars($personnel['photo']) . "\")'>
                          <div class='flex-shrink-0'>";
                  if (!empty($personnel['photo'])) {
                      echo "<img src='../OrganizationalChart/uploadDIT/" . htmlspecialchars($personnel['photo']) . "' 
                                   class='h-5 w-5 rounded-full border border-it-orange object-cover shadow-lg'
                                   onerror=\"this.style.display='none'; this.nextElementSibling.style.display='flex';\">
                                <div class='hidden absolute inset-0 flex items-center justify-center h-5 w-5 rounded-full border border-it-orange bg-it-orange text-white text-xs font-bold shadow'>
                                  $initials
                                </div>";
                  } else {
                      echo "<div class='h-5 w-5 rounded-full border border-it-orange bg-it-orange text-white flex items-center justify-center text-xs font-bold shadow'>
                                  $initials
                                </div>";
                  }
                  echo "</div>
                          <div class='text-[9px] leading-tight flex-1 min-w-0'>
                            <strong class='block truncate font-medium'>" . htmlspecialchars($personnel['name']) . "</strong>
                            <p class='text-gray-600 truncate'>" . htmlspecialchars($personnel['role']) . "</p>
                          </div>
                        </div>";
              }
          }
          echo "</div>";
          
          echo "<div class='personnel-column flex flex-col items-center space-y-1' id='column-4'>";
          for ($i = 1; $i < count($itFaculty); $i += 2) {
              if (isset($itFaculty[$i])) {
                  $personnel = $itFaculty[$i];
                  $initials = getInitials($personnel['name']);
                  
                  echo "<div class='org-box rounded-md shadow-md text-left flex items-center space-x-1 h-[29px] w-[145px] border border-it-orange p-[2px] ml-[1px] mb-[-1px] bg-white/90 hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 cursor-pointer' onclick='openModal(\"" . htmlspecialchars($personnel['name']) . "\", \"" . htmlspecialchars($personnel['role']) . "\", \"" . htmlspecialchars($personnel['photo']) . "\")'>
                          <div class='flex-shrink-0'>";
                  if (!empty($personnel['photo'])) {
                      echo "<img src='../OrganizationalChart/uploadDIT/" . htmlspecialchars($personnel['photo']) . "' 
                                   class='h-5 w-5 rounded-full border border-it-orange object-cover shadow-lg'
                                   onerror=\"this.style.display='none'; this.nextElementSibling.style.display='flex';\">
                                <div class='hidden absolute inset-0 flex items-center justify-center h-5 w-5 rounded-full border border-it-orange bg-it-orange text-white text-xs font-bold shadow'>
                                  $initials
                                </div>";
                  } else {
                      echo "<div class='h-5 w-5 rounded-full border border-it-orange bg-it-orange text-white flex items-center justify-center text-xs font-bold shadow'>
                                  $initials
                                </div>";
                  }
                  echo "</div>
                          <div class='text-[9px] leading-tight flex-1 min-w-0'>
                            <strong class='block truncate font-medium'>" . htmlspecialchars($personnel['name']) . "</strong>
                            <p class='text-gray-600 truncate'>" . htmlspecialchars($personnel['role']) . "</p>
                          </div>
                        </div>";
              }
          }
          echo "</div>";
          
          echo "</div>";
          echo "</div>";
          ?>
        </div>
      </div>
    </div>
  </div>
  
  <div id="profileModal" class="modal fixed inset-0 z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black/50" onclick="closeModal()"></div>
    <div class="modal-container fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-[280px] z-10">
      <div class="bg-white rounded-xl shadow-2xl transform transition-all">
        <div class="bg-gradient-to-r from-orange-500 to-amber-500 text-white p-3 rounded-t-xl">
          <div class="flex justify-between items-center">
            <h3 class="text-md font-bold">Profile Details</h3>
            <button onclick="closeModal()" class="text-white hover:text-orange-200 transition-colors">
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
          <button onclick="closeModal()" class="w-full py-1 px-3 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg transition-colors duration-200 text-xs">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
  
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
      
      photoElement.src = '../OrganizationalChart/uploadDIT/' + photo;
      photoElement.onerror = function() {
        this.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(cleanName) + '&background=f97316&color=fff&size=80';
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