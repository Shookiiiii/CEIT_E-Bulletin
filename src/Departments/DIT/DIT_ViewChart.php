<?php include '../../db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DIT Organizational Chart - View Only</title>
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
    .org-chart-container {
      height: 100%;
      width: 100%;
      position: relative;
      background: transparent;
      display: flex;
      flex-direction: column;
      overflow: hidden; /* Ensure content never exceeds container */
    }
    .personnel-box {
      height: 50px;
      width: 150px;
    }
    .personnel-box img {
      height: 28px;
      width: 28px;
    }
    .personnel-box h3 {
      font-size: 0.65rem;
    }
    .personnel-box p {
      font-size: 0.55rem;
    }
    .personnel-columns {
      display: flex;
      justify-content: center;
      gap: 0.75rem;
      padding: 0.25rem;
      flex: 1;
      overflow: hidden;
      min-height: 0; /* Allow flex container to shrink */
    }
    .personnel-column {
      flex: 0 0 auto;
      width: 160px;
      /* Height calculated for exactly 6 items: 6*50px + 5*4px gaps + 8px padding = 328px */
      max-height: 328px;
      padding: 0.25rem;
      background-color: rgba(255, 255, 255, 0.85);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      overflow-y: hidden; /* Initially hidden */
      scrollbar-width: none;
      -ms-overflow-style: none;
    }
    .personnel-column::-webkit-scrollbar {
      display: none;
    }
    .personnel-column.scrollable {
      overflow-y: auto;
    }
    .chart-header {
      text-align: center;
      margin-bottom: 0.25rem;
      color: #fff;
      font-weight: bold;
      text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.8);
      font-size: 0.9rem;
    }
    
    /* Pyramid Structure Styles */
    .pyramid-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 0.25rem 0;
      /* Height for exactly 3 rows: 3*60px + 2*20px connectors + padding = 220px */
      height: 220px;
      overflow-y: hidden; /* Initially hidden */
      scrollbar-width: none;
      -ms-overflow-style: none;
    }
    .pyramid-container.scrollable {
      overflow-y: auto;
    }
    .pyramid-container::-webkit-scrollbar {
      display: none;
    }
    .pyramid-row {
      display: flex;
      justify-content: center;
      margin-bottom: 0.5rem;
      position: relative;
    }
    .pyramid-row::after {
      content: '';
      position: absolute;
      bottom: -0.5rem;
      left: 50%;
      width: 2px;
      height: 0.5rem;
      background-color: #cbd5e1;
      transform: translateX(-50%);
    }
    .pyramid-row:last-child::after {
      display: none;
    }
    .pyramid-node {
      margin: 0 0.25rem;
    }
    
    /* Fixed size for pyramid boxes to ensure consistent layout */
    .pyramid-box {
      width: 140px;
      padding: 0.6rem !important;
    }
    .pyramid-box img {
      height: 28px !important;
      width: 28px !important;
    }
    .pyramid-box h3 {
      font-size: 0.65rem !important;
    }
    .pyramid-box p {
      font-size: 0.55rem !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .personnel-columns {
        gap: 0.5rem;
      }
      .personnel-column {
        width: 140px;
        max-height: 328px;
      }
      .pyramid-container {
        height: 200px;
      }
      .pyramid-box {
        width: 120px !important;
        padding: 0.5rem !important;
      }
      .pyramid-box img {
        height: 24px !important;
        width: 24px !important;
      }
      .pyramid-box h3 {
        font-size: 0.6rem !important;
      }
      .pyramid-box p {
        font-size: 0.5rem !important;
      }
    }
  </style>
</head>
<body class="bg-gray-50 p-1">
  <div class="org-chart-container">
    <!-- Leadership Section - Pyramid Structure -->
    <?php
    function getMember($code, $conn) {
      $stmt = $conn->prepare("SELECT * FROM DIT_Organization WHERE position_code = ?");
      $stmt->bind_param("s", $code);
      $stmt->execute();
      return $stmt->get_result()->fetch_assoc();
    }
    
    // Get all top positions
    $result = $conn->query("SELECT * FROM DIT_Organization WHERE position_code LIKE 'top_%' ORDER BY id ASC");
    
    if ($result->num_rows > 0):
      $topPositions = [];
      while ($member = $result->fetch_assoc()) {
        $topPositions[] = $member;
      }
      
      // Create pyramid structure: 1, 2, 3 pattern
      $pyramidRows = [];
      $pattern = [1, 2, 3];
      $patternIndex = 0;
      $currentRow = [];
      
      foreach ($topPositions as $position) {
        $currentRow[] = $position;
        if (count($currentRow) == $pattern[$patternIndex]) {
          $pyramidRows[] = $currentRow;
          $currentRow = [];
          $patternIndex = ($patternIndex + 1) % 3;
        }
      }
      
      // Add any remaining positions to the last row
      if (!empty($currentRow)) {
        $pyramidRows[] = $currentRow;
      }
      
      // Check if scrolling is needed (more than 3 rows)
      $needsScrolling = count($pyramidRows) > 3;
    ?>
    <div class="mb-2">
      <div class="chart-header">DIT Organizational Structure</div>
      <div class="border-t border-blue-200 pt-2">
        <div class="pyramid-container <?php echo $needsScrolling ? 'scrollable' : ''; ?>">
          <?php foreach ($pyramidRows as $rowIndex => $row): ?>
          <div class="pyramid-row">
            <?php foreach ($row as $member): ?>
            <div class="pyramid-node">
              <div class="bg-white rounded shadow p-1">
                <div class='org-box bg-indigo-50 border-l-2 border-indigo-500 rounded shadow-sm pyramid-box' onclick='openModal("<?php echo htmlspecialchars($member['name']); ?>", "<?php echo htmlspecialchars($member['role']); ?>", "<?php echo htmlspecialchars($member['photo']); ?>")'>
                  <div class='flex items-center'>
                    <img src='../OrganizationalChart/uploadDIT/<?php echo htmlspecialchars($member['photo']); ?>' 
                         class='rounded-full object-cover border border-white shadow'
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode(htmlspecialchars($member['name'])); ?>&background=6366f1&color=fff&size=36'">
                    <div class='ml-2 min-w-0'>
                      <h3 class='font-bold truncate'><?php echo htmlspecialchars($member['name']); ?></h3>
                      <p class='text-gray-600 truncate'><?php echo htmlspecialchars($member['role']); ?></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
    
    <!-- Divider Line -->
    <div class="relative my-2">
      <div class="absolute inset-0 flex items-center">
        <div class="w-full border-t border-gray-300"></div>
      </div>
    </div>
    
    <!-- Personnel Section -->
    <?php
    $result = $conn->query("SELECT * FROM DIT_Organization WHERE position_code NOT LIKE 'top_%' ORDER BY id ASC");
    
    if ($result->num_rows > 0):
    ?>
    <div class="mt-2 flex-1">
      <div class="border-t border-orange-200 pt-2 h-full">
        <div class="personnel-columns h-full">
          <?php
          $columns = [[], [], [], []];
          $columnIndex = 0;
          
          $result->data_seek(0);
          while ($row = $result->fetch_assoc()) {
              if (preg_match('/^column_(\d+)$/', $row['position_code'], $matches)) {
                  $colNum = intval($matches[1]) - 1;
                  if ($colNum >= 0 && $colNum < 4) {
                      $columns[$colNum][] = $row;
                      continue;
                  }
              }
              $columns[$columnIndex][] = $row;
              $columnIndex = ($columnIndex + 1) % 4;
          }
          
          for ($i = 0; $i < 4; $i++) {
              echo "<div class='personnel-column space-y-1' data-column='" . ($i + 1) . "'>";
              
              foreach ($columns[$i] as $personnel) {
                  echo "<div class='org-box bg-white rounded shadow-sm border-l border-orange-400 personnel-box' onclick='openModal(\"" . htmlspecialchars($personnel['name']) . "\", \"" . htmlspecialchars($personnel['role']) . "\", \"" . htmlspecialchars($personnel['photo']) . "\")'>
                        <div class='flex items-center'>
                          <img src='../OrganizationalChart/uploadDIT/" . htmlspecialchars($personnel['photo']) . "' 
                               class='rounded-full object-cover border border-white shadow'
                               onerror=\"this.src='https://ui-avatars.com/api/?name=" . urlencode(htmlspecialchars($personnel['name'])) . "&background=f97316&color=fff&size=32'\">
                          <div class='ml-1.5 min-w-0'>
                            <h3 class='font-bold truncate'>" . htmlspecialchars($personnel['name']) . "</h3>
                            <p class='text-gray-600 truncate'>" . htmlspecialchars($personnel['role']) . "</p>
                          </div>
                        </div>
                      </div>";
              }
              
              echo "</div>";
          }
          ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
  
  <!-- Profile Modal -->
  <div id="profileModal" class="modal fixed inset-0 z-50 hidden">
    <div class="modal-backdrop fixed inset-0" onclick="closeModal()"></div>
    <div class="modal-container max-w-sm w-full mx-auto z-10">
      <div class="bg-white rounded-xl shadow-2xl transform transition-all">
        <div class="bg-gradient-to-r from-orange-500 to-amber-500 text-white p-4 rounded-t-xl">
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-bold">Profile Details</h3>
            <button onclick="closeModal()" class="text-white hover:text-orange-200 transition-colors">
              <i class="fas fa-times text-lg"></i>
            </button>
          </div>
        </div>
        
        <div class="p-4">
          <div class="flex flex-col items-center mb-3">
            <div class="relative mb-3">
              <img id="modalPhoto" src="" alt="Profile Photo" class="h-32 w-32 rounded-full object-cover border-4 border-white shadow-lg">
              <div class="absolute inset-0 rounded-full bg-gradient-to-t from-black/30 to-transparent"></div>
            </div>
            <h4 id="modalName" class="text-xl font-bold text-gray-800 mb-1 text-center"></h4>
            <p id="modalRole" class="text-gray-600 text-center"></p>
          </div>
        </div>
        
        <div class="bg-gray-50 px-4 py-3 rounded-b-xl">
          <button onclick="closeModal()" class="w-full py-2 px-4 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg transition-colors duration-200 text-sm">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
  
  <script>
    function openModal(name, role, photo) {
      document.getElementById("modalName").textContent = name;
      document.getElementById("modalRole").textContent = role;
      
      const photoElement = document.getElementById("modalPhoto");
      photoElement.src = '../OrganizationalChart/uploadDIT/' + photo;
      photoElement.onerror = function() {
        this.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(name) + '&background=f97316&color=fff&size=128';
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
    
    // Smart scrolling for personnel columns
    document.addEventListener('DOMContentLoaded', function() {
      const columns = document.querySelectorAll('.personnel-column');
      
      columns.forEach(column => {
        // Check if column needs scrolling
        function checkScrollNeeded() {
          const items = column.querySelectorAll('.personnel-box');
          const maxItems = 6; // Maximum items before scrolling is needed
          
          // Only enable scrolling if there are more than maxItems
          if (items.length > maxItems) {
            column.classList.add('scrollable');
          } else {
            column.classList.remove('scrollable');
          }
        }
        
        // Initial check
        checkScrollNeeded();
        
        // Touch/mouse swipe functionality
        let isDown = false;
        let startY;
        let scrollTop;
        
        // Mouse events
        column.addEventListener('mousedown', (e) => {
          if (!column.classList.contains('scrollable')) return;
          isDown = true;
          startY = e.pageY - column.offsetTop;
          scrollTop = column.scrollTop;
        });
        
        column.addEventListener('mouseleave', () => {
          isDown = false;
        });
        
        column.addEventListener('mouseup', () => {
          isDown = false;
        });
        
        column.addEventListener('mousemove', (e) => {
          if (!isDown || !column.classList.contains('scrollable')) return;
          e.preventDefault();
          const y = e.pageY - column.offsetTop;
          const walk = (y - startY) * 2;
          column.scrollTop = scrollTop - walk;
        });
        
        // Touch events
        column.addEventListener('touchstart', (e) => {
          if (!column.classList.contains('scrollable')) return;
          startY = e.touches[0].pageY - column.offsetTop;
          scrollTop = column.scrollTop;
        });
        
        column.addEventListener('touchmove', (e) => {
          if (!column.classList.contains('scrollable') || e.touches.length !== 1) return;
          const y = e.touches[0].pageY - column.offsetTop;
          const walk = (y - startY) * 2;
          column.scrollTop = scrollTop - walk;
        });
      });
      
      // Touch/mouse swipe functionality for pyramid container
      const pyramidContainer = document.querySelector('.pyramid-container');
      if (pyramidContainer) {
        let isDown = false;
        let startY;
        let scrollTop;
        
        // Mouse events
        pyramidContainer.addEventListener('mousedown', (e) => {
          if (!pyramidContainer.classList.contains('scrollable')) return;
          isDown = true;
          startY = e.pageY - pyramidContainer.offsetTop;
          scrollTop = pyramidContainer.scrollTop;
        });
        
        pyramidContainer.addEventListener('mouseleave', () => {
          isDown = false;
        });
        
        pyramidContainer.addEventListener('mouseup', () => {
          isDown = false;
        });
        
        pyramidContainer.addEventListener('mousemove', (e) => {
          if (!isDown || !pyramidContainer.classList.contains('scrollable')) return;
          e.preventDefault();
          const y = e.pageY - pyramidContainer.offsetTop;
          const walk = (y - startY) * 2;
          pyramidContainer.scrollTop = scrollTop - walk;
        });
        
        // Touch events
        pyramidContainer.addEventListener('touchstart', (e) => {
          if (!pyramidContainer.classList.contains('scrollable')) return;
          startY = e.touches[0].pageY - pyramidContainer.offsetTop;
          scrollTop = pyramidContainer.scrollTop;
        });
        
        pyramidContainer.addEventListener('touchmove', (e) => {
          if (!pyramidContainer.classList.contains('scrollable') || e.touches.length !== 1) return;
          const y = e.touches[0].pageY - pyramidContainer.offsetTop;
          const walk = (y - startY) * 2;
          pyramidContainer.scrollTop = scrollTop - walk;
        });
      }
    });
  </script>
</body>
</html>