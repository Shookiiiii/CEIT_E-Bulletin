<?php include '../../db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CEIT Organizational Chart - View Only</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    .org-box {
      transition: all 0.2s ease;
      cursor: pointer;
    }
    .org-box:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .modal {
      transition: opacity 0.25s ease;
    }
    .modal-backdrop {
      background-color: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(4px);
    }
    body.modal-open {
      overflow: hidden;
    }
    .modal-container {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }
    ::-webkit-scrollbar {
      display: none;
    }
    html {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }
    .org-chart-wrapper {
      width: 680px !important;
      height: 500px !important;
    }
    .chart-container {
      height: 100% !important;
      overflow: hidden;
    }
    .personnel-grid {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 6px;
      height: 100%;
      overflow: hidden;
    }
    .personnel-name {
      font-size: 9px;
      font-weight: 600;
    }
    .personnel-role {
      font-size: 8px;
    }
    .leadership-section {
      height: 130px;
    }
    .personnel-section {
      height: 300px;
    }
  </style>
</head>
<body class="bg-gray-50">
  <div class="org-chart-wrapper mx-auto p-3 overflow-hidden bg-transparent">
    <div class="chart-container border-2 border-orange-500 rounded-lg p-1 shadow-lg flex flex-col bg-white/60">
      
      <?php
      function getInitials($name) {
        $prefixes = ['Mr.', 'Mrs.', 'Ms.', 'Miss', 'Dr.', 'Prof.', 'Sir', 'Madam', 'Engr.', 'Atty.'];
        
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
        
        return 'NA';
      }
      
      function getMember($code, $conn) {
        $stmt = $conn->prepare("SELECT * FROM CEIT_Organization WHERE position_code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
      }
      
      $president = getMember('president', $conn);
      $vicePresident = getMember('vice_president', $conn);
      $collegeDean = getMember('college_dean', $conn);
      
      $additionalTopResult = $conn->query("SELECT * FROM CEIT_Organization WHERE position_code LIKE 'top_%' ORDER BY id ASC");
      $additionalTopPositions = [];
      while ($row = $additionalTopResult->fetch_assoc()) {
        $additionalTopPositions[] = $row;
      }
      ?>
      <div class="leadership-section mb-1">
          <div class="flex flex-col items-center gap-1" id="topManagement">
            <?php 
            $mainPositions = [
                'president' => ['title' => 'President, CvSU', 'member' => $president],
                'vice_president' => ['title' => 'Vice President, OVPAA', 'member' => $vicePresident],
                'college_dean' => ['title' => 'Dean, CEIT', 'member' => $collegeDean]
            ];
            
            foreach ($mainPositions as $positionCode => $positionData): 
                $member = $positionData['member'];
                $defaultRole = $positionData['title'];
            ?>
            <div class="flex justify-center">
              <?php if ($member): ?>
              <div class='org-box rounded-md shadow-md text-left flex items-center space-x-1 h-[40px] w-[180px] border border-orange-500 p-1 mb-[-2px] bg-white hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 cursor-pointer' onclick='openModal("<?php echo htmlspecialchars($member['name']); ?>", "<?php echo htmlspecialchars($member['role']); ?>", "<?php echo htmlspecialchars($member['photo']); ?>")'>
                <div class="flex-shrink-0">
                  <?php if (!empty($member['photo'])): ?>
                    <img src='../OrganizationalChart/uploadCEIT/<?php echo htmlspecialchars($member['photo']); ?>' 
                         class='h-6 w-6 rounded-full border border-orange-500 object-cover shadow-lg'
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class='hidden h-7 w-7 rounded-full border border-orange-500 bg-orange-500 text-white flex items-center justify-center text-sm shadow-lg font-bold'>
                      <?php echo getInitials($member['name']); ?>
                    </div>
                  <?php else: ?>
                    <div class='h-7 w-7 rounded-full border border-orange-500 bg-orange-500 text-white flex items-center justify-center text-sm shadow-lg font-bold'>
                      <?php echo getInitials($member['name']); ?>
                    </div>
                  <?php endif; ?>
                </div>
                <div class='text-[11px] leading-tight flex-1 min-w-0'>
                  <strong class='block truncate font-medium'><?php echo htmlspecialchars($member['name']); ?></strong>
                  <p class='text-gray-600 truncate'><?php echo htmlspecialchars($member['role']); ?></p>
                </div>
              </div>
              <?php else: ?>
              <div class='org-box rounded-md shadow-md text-left flex items-center space-x-1 h-[40px] w-[180px] border border-orange-500 p-1 mb-[-2px] bg-white/90'>
                <div class='h-7 w-7 rounded-full border border-orange-500 bg-gray-200 flex items-center justify-center text-gray-400 text-xs shadow-lg'>
                  <i class="fas fa-user"></i>
                </div>
                <div class='text-[11px] leading-tight flex-1 min-w-0'>
                  <strong class='block truncate font-medium'>-unfilled-</strong>
                  <p class='text-gray-600 truncate'><?php echo htmlspecialchars($defaultRole); ?></p>
                </div>
              </div>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
            
            <?php foreach ($additionalTopPositions as $member): ?>
            <div class="flex justify-center">
              <div class='org-box rounded-md shadow-md text-left flex items-center space-x-1 h-[40px] w-[180px] border border-orange-500 p-1 mb-[-2px] bg-white hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 cursor-pointer' onclick='openModal("<?php echo htmlspecialchars($member['name']); ?>", "<?php echo htmlspecialchars($member['role']); ?>", "<?php echo htmlspecialchars($member['photo']); ?>")'>
                <div class="flex-shrink-0">
                  <?php if (!empty($member['photo'])): ?>
                    <img src='../OrganizationalChart/uploadCEIT/<?php echo htmlspecialchars($member['photo']); ?>' 
                         class='h-7 w-7 *:rounded-full border border-orange-500 object-cover shadow-lg'
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class='hidden h-7 w-7 rounded-full border border-orange-500 bg-orange-500 text-white flex items-center justify-center text-sm shadow-lg font-bold'>
                      <?php echo getInitials($member['name']); ?>
                    </div>
                  <?php else: ?>
                    <div class='h-7 w-7 rounded-full border border-orange-500 bg-orange-500 text-white flex items-center justify-center text-sm shadow-lg font-bold'>
                      <?php echo getInitials($member['name']); ?>
                    </div>
                  <?php endif; ?>
                </div>
                <div class='text-[11px] leading-tight flex-1 min-w-0'>
                  <strong class='block truncate font-medium'><?php echo htmlspecialchars($member['name']); ?></strong>
                  <p class='text-gray-600 truncate'><?php echo htmlspecialchars($member['role']); ?></p>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
      </div>   
      <?php
      $result = $conn->query("SELECT * FROM CEIT_Organization WHERE position_code NOT IN ('president', 'vice_president', 'college_dean') AND position_code NOT LIKE 'top_%' ORDER BY id ASC");
      
      if ($result->num_rows > 0):
      ?>
      <div class="personnel-section flex flex-col">
        <h5 class="text-center font-bold bg-orange-500/80 text-white rounded-lg text-lg mb-2">CEIT Personnel</h5>
        <div class="personnel-grid">
          <?php
          $result->data_seek(0);
          while ($row = $result->fetch_assoc()) {
            echo "<div class='org-box rounded-md shadow-md text-left flex items-center space-x-2 h-[38px] w-[100px] border border-orange-500 p-1 mb-[-2px] bg-white hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 cursor-pointer' onclick='openModal(\"" . htmlspecialchars($row['name']) . "\", \"" . htmlspecialchars($row['role']) . "\", \"" . htmlspecialchars($row['photo']) . "\")'>
              <div class=\"flex-shrink-0\">";
            
            if (!empty($row['photo'])) {
              echo "<img src='../OrganizationalChart/uploadCEIT/" . htmlspecialchars($row['photo']) . "' 
                   class='h-7 w-7 rounded-full border border-orange-500 object-cover shadow-lg'
                   onerror=\"this.style.display='none'; this.nextElementSibling.style.display='flex';\">
                <div class='hidden h-7 w-7 rounded-full border border-orange-500 bg-orange-500 text-white flex items-center justify-center text-sm shadow-lg font-bold'>
                  " . getInitials($row['name']) . "
                </div>
              </div>";
            } else {
              echo "<div class='h-7 w-7 rounded-full border border-orange-500 bg-orange-500 text-white flex items-center justify-center text-sm shadow-lg font-bold'>
                      " . getInitials($row['name']) . "
                    </div>
                  </div>";
            }
            
            echo "<div class='text-[11px] leading-tight flex-1 min-w-0'>
                    <strong class='block truncate font-medium personnel-name'>" . htmlspecialchars($row['name']) . "</strong>
                    <p class='text-gray-600 truncate personnel-role'>" . htmlspecialchars($row['role']) . "</p>
                  </div>
                </div>";
          }
          ?>
        </div>
      </div>
      <?php endif; ?>
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
      document.getElementById("modalName").textContent = name;
      document.getElementById("modalRole").textContent = role;
      
      const photoElement = document.getElementById("modalPhoto");
      const firstLastName = getFirstAndLastName(name);
      
      photoElement.src = '../OrganizationalChart/uploadCEIT/' + photo;
      photoElement.onerror = function() {
        this.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(firstLastName) + '&background=f97316&color=fff&size=80';
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