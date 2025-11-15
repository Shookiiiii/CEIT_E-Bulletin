<?php
include "../../db.php";
?>
<div class="bg-white rounded-xl shadow-lg p-6 mb-6">
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
      <i class="fas fa-chart-pie mr-3 text-orange-500"></i>
      Graph Management
    </h2>
    <button onclick="showAddGraphForm()" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition duration-200 flex items-center shadow-md hover:shadow-lg">
      <i class="fas fa-plus mr-2"></i> Add New Graph
    </button>
  </div>
  
  <!-- Add Graph Form (Initially Hidden) -->
  <div id="addGraphForm" class="hidden mb-8 p-6 bg-gray-50 rounded-lg border border-gray-200 shadow-sm">
    <h3 class="text-lg font-semibold mb-4 text-gray-700">Create New Graph</h3>
    
    <!-- Graph Group Option -->
    <div class="mb-4">
      <label class="flex items-center cursor-pointer">
        <input type="checkbox" id="createGroup" onchange="toggleGroupOptions()" class="form-checkbox h-5 w-5 text-orange-600 rounded focus:ring-orange-500">
        <span class="ml-2 text-gray-700 font-medium">Create as a group of graphs</span>
      </label>
    </div>
    
    <!-- Group Options (Initially Hidden) -->
    <div id="groupOptions" class="hidden mb-4 p-4 bg-orange-50 rounded-lg border border-orange-200 shadow-sm">
      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="groupTitle">
          Group Title
        </label>
        <input class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
               id="groupTitle" name="groupTitle" type="text" placeholder="Enter group title">
      </div>
      
      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="graphCount">
          Number of Graphs
        </label>
        <select id="graphCount" onchange="updateGraphForms()" class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
          <option value="1">1</option>
          <option value="2">2</option>
          <option value="3">3</option>
          <option value="4">4</option>
          <option value="5">5</option>
        </select>
      </div>
      
      <div id="graphFormsContainer">
        <!-- Graph forms will be dynamically added here -->
      </div>
      
      <div class="flex justify-end space-x-3 mt-6">
        <button type="button" onclick="hideAddGraphForm()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200 shadow">
          Cancel
        </button>
        <button type="button" onclick="submitGroupForm()" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition duration-200 shadow-md hover:shadow-lg">
          <i class="fas fa-save mr-2"></i> Save Graph Group
        </button>
      </div>
    </div>
    
    <!-- Single Graph Form (Default) -->
    <div id="singleGraphForm">
      <!-- Pie Chart Form -->
      <form id="pieForm" action="DIT_Add_Graph.php" method="post" style="display: block;">
        <input type="hidden" name="graphType" value="pie">
        <input type="hidden" name="isGroup" value="0">
        <input type="hidden" name="mainTab" value="upload">
        <input type="hidden" name="currentTab" value="upload-graphs">
        
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="pieTitle">
            Graph Title
          </label>
          <input class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
                 id="pieTitle" name="graphTitle" type="text" placeholder="Enter graph title" required>
        </div>
        
        <div class="mb-4">
          <div class="flex justify-between items-center mb-2">
            <label class="block text-gray-700 text-sm font-bold">
              Data Points
            </label>
            <button type="button" onclick="addPieRow()" class="px-3 py-1 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600 transition duration-200 shadow">
              <i class="fas fa-plus mr-1"></i> Add Row
            </button>
          </div>
          
          <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
            <table class="min-w-full bg-white">
              <thead class="bg-gray-100">
                <tr>
                  <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Label</th>
                  <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Value</th>
                  <th class="py-3 px-4 text-center text-sm font-semibold text-gray-700">Actions</th>
                </tr>
              </thead>
              <tbody id="pieTableBody">
                <tr class="data-row border-b border-gray-200 hover:bg-gray-50">
                  <td class="py-3 px-4">
                    <input type="text" name="label[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Label" required>
                  </td>
                  <td class="py-3 px-4">
                    <input type="text" name="value[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Value" required>
                  </td>
                  <td class="py-3 px-4 text-center">
                    <button type="button" onclick="removePieRow(this)" class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition duration-200">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        
        <div class="flex justify-end space-x-3">
          <button type="button" onclick="hideAddGraphForm()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200 shadow">
            Cancel
          </button>
          <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition duration-200 shadow-md hover:shadow-lg">
            <i class="fas fa-save mr-2"></i> Save Pie Chart
          </button>
        </div>
      </form>
      
      <!-- Bar Chart Form -->
      <form id="barForm" action="DIT_Add_Graph.php" method="post" style="display: none;">
        <input type="hidden" name="graphType" value="bar">
        <input type="hidden" name="isGroup" value="0">
        <input type="hidden" name="mainTab" value="upload">
        <input type="hidden" name="currentTab" value="upload-graphs">
        
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2" for="barTitle">
            Graph Title
          </label>
          <input class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
                 id="barTitle" name="graphTitle" type="text" placeholder="Enter graph title" required>
        </div>
        
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">
            Series Labels
          </label>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <input type="text" name="series1Label" class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
                       placeholder="First Series Label">
            </div>
            <div>
              <input type="text" name="series2Label" class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
                       placeholder="Second Series Label">
            </div>
          </div>
        </div>
        
        <div class="mb-4">
          <div class="flex justify-between items-center mb-2">
            <label class="block text-gray-700 text-sm font-bold">
              Data Points
            </label>
            <button type="button" onclick="addBarRow()" class="px-3 py-1 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600 transition duration-200 shadow">
              <i class="fas fa-plus mr-1"></i> Add Row
            </button>
          </div>
          
          <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
            <table class="min-w-full bg-white">
              <thead class="bg-gray-100">
                <tr>
                  <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Category</th>
                  <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Series 1</th>
                  <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Series 2</th>
                  <th class="py-3 px-4 text-center text-sm font-semibold text-gray-700">Actions</th>
                </tr>
              </thead>
              <tbody id="barTableBody">
                <tr class="bar-data-row border-b border-gray-200 hover:bg-gray-50">
                  <td class="py-3 px-4">
                    <input type="text" name="bar_category[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Category" required>
                  </td>
                  <td class="py-3 px-4">
                    <input type="text" name="bar_series1[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Series 1 Value" required>
                  </td>
                  <td class="py-3 px-4">
                    <input type="text" name="bar_series2[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Series 2 Value" required>
                  </td>
                  <td class="py-3 px-4 text-center">
                    <button type="button" onclick="removeBarRow(this)" class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition duration-200">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        
        <div class="flex justify-end space-x-3">
          <button type="button" onclick="hideAddGraphForm()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200 shadow">
            Cancel
          </button>
          <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition duration-200 shadow-md hover:shadow-lg">
            <i class="fas fa-save mr-2"></i> Save Bar Chart
          </button>
        </div>
      </form>
    </div>
    
    <!-- Graph Type Selection (for single graph) -->
    <div id="graphTypeSelection" class="mb-4 border-t pt-4">
      <label class="block text-gray-700 text-sm font-bold mb-2">
        Graph Type
      </label>
      <div class="flex space-x-4">
        <label class="inline-flex items-center cursor-pointer">
          <input type="radio" name="graphTypeSelector" value="pie" class="form-radio h-4 w-4 text-orange-600 focus:ring-orange-500" checked onchange="switchGraphType('pie')">
          <span class="ml-2 text-gray-700">Pie Chart</span>
        </label>
        <label class="inline-flex items-center cursor-pointer">
          <input type="radio" name="graphTypeSelector" value="bar" class="form-radio h-4 w-4 text-orange-600 focus:ring-orange-500" onchange="switchGraphType('bar')">
          <span class="ml-2 text-gray-700">Bar Chart</span>
        </label>
      </div>
    </div>
  </div>
  
  <!-- Pending Graphs Section -->
  <div class="status-section pending mb-8">
    <h2 class="status-title text-xl font-semibold text-gray-800 mb-6">Pending Graphs</h2>
    <div class="graphs-container">
      <?php
      // Get pending graphs - ordered by creation date (newest first)
      $pending_graphs = [];
      $query = "SELECT * FROM DIT_post WHERE title='graph' AND status='pending' ORDER BY created_at DESC";
      $result = $conn->query($query);
      
      // Group graphs by group_title
      $groups = [];
      $individualGraphs = [];
      
      while ($row = $result->fetch_assoc()) {
          $graphData = json_decode($row['content'], true);
          $title = $graphData['title'] ?? 'Untitled Graph';
          $graphType = isset($graphData['data']) && is_array($graphData['data']) ? 
                      (isset($graphData['data'][0]['category']) ? 'bar' : 'pie') : 'pie';
          $data = $graphData['data'] ?? [];
          $id = $row['id'];
          $created_at = $row['created_at'];
          $groupTitle = $graphData['group_title'] ?? null;
          
          // Group graphs by group_title
          if ($groupTitle) {
              if (!isset($groups[$groupTitle])) {
                  $groups[$groupTitle] = [
                      'title' => $groupTitle,
                      'graphs' => [],
                      'ids' => [],
                      'created_at' => $created_at
                  ];
              }
              $groups[$groupTitle]['graphs'][] = [
                  'id' => $id,
                  'title' => $title,
                  'graphType' => $graphType,
                  'data' => $data,
                  'created_at' => $created_at
              ];
              $groups[$groupTitle]['ids'][] = $id;
          } else {
              $individualGraphs[] = [
                  'id' => $id,
                  'title' => $title,
                  'graphType' => $graphType,
                  'data' => $data,
                  'created_at' => $created_at
              ];
          }
      }
      
      // Sort groups by creation date (newest first)
      uasort($groups, function($a, $b) {
          return strtotime($b['created_at']) - strtotime($a['created_at']);
      });
      
      // Display group graphs
      foreach ($groups as $groupTitle => $group) {
          echo '<div class="graph-item-wrapper">';
          displayGroupCard($group['ids'], $groupTitle, $group['graphs'], 'pending');
          echo '</div>';
      }
      
      // Display individual graphs
      foreach ($individualGraphs as $graph) {
          echo '<div class="graph-item-wrapper">';
          displayGraphCard($graph['id'], $graph['title'], $graph['graphType'], $graph['data'], $graph['created_at'], 'pending');
          echo '</div>';
      }
      
      if (empty($groups) && empty($individualGraphs)) {
          echo '<div class="text-center py-8 text-gray-500">';
          echo '<i class="fas fa-inbox fa-3x mb-4"></i>';
          echo '<p class="text-lg">No pending graphs</p>';
          echo '</div>';
      }
      ?>
    </div>
  </div>
  
  <!-- Approved Graphs Section -->
  <div class="status-section approved mb-8">
    <h2 class="status-title text-xl font-semibold text-gray-800 mb-6">Approved Graphs</h2>
    <div class="graphs-container">
      <?php
      // Get approved graphs - ordered by creation date (newest first)
      $approved_graphs = [];
      $query = "SELECT * FROM DIT_post WHERE title='graph' AND status='approved' ORDER BY created_at DESC";
      $result = $conn->query($query);
      
      // Group graphs by group_title
      $groups = [];
      $individualGraphs = [];
      
      while ($row = $result->fetch_assoc()) {
          $graphData = json_decode($row['content'], true);
          $title = $graphData['title'] ?? 'Untitled Graph';
          $graphType = isset($graphData['data']) && is_array($graphData['data']) ? 
                      (isset($graphData['data'][0]['category']) ? 'bar' : 'pie') : 'pie';
          $data = $graphData['data'] ?? [];
          $id = $row['id'];
          $created_at = $row['created_at'];
          $groupTitle = $graphData['group_title'] ?? null;
          
          // Group graphs by group_title
          if ($groupTitle) {
              if (!isset($groups[$groupTitle])) {
                  $groups[$groupTitle] = [
                      'title' => $groupTitle,
                      'graphs' => [],
                      'ids' => [],
                      'created_at' => $created_at
                  ];
              }
              $groups[$groupTitle]['graphs'][] = [
                  'id' => $id,
                  'title' => $title,
                  'graphType' => $graphType,
                  'data' => $data,
                  'created_at' => $created_at
              ];
              $groups[$groupTitle]['ids'][] = $id;
          } else {
              $individualGraphs[] = [
                  'id' => $id,
                  'title' => $title,
                  'graphType' => $graphType,
                  'data' => $data,
                  'created_at' => $created_at
              ];
          }
      }
      
      // Sort groups by creation date (newest first)
      uasort($groups, function($a, $b) {
          return strtotime($b['created_at']) - strtotime($a['created_at']);
      });
      
      // Display group graphs
      foreach ($groups as $groupTitle => $group) {
          echo '<div class="graph-item-wrapper">';
          displayGroupCard($group['ids'], $groupTitle, $group['graphs'], 'approved');
          echo '</div>';
      }
      
      // Display individual graphs
      foreach ($individualGraphs as $graph) {
          echo '<div class="graph-item-wrapper">';
          displayGraphCard($graph['id'], $graph['title'], $graph['graphType'], $graph['data'], $graph['created_at'], 'approved');
          echo '</div>';
      }
      
      if (empty($groups) && empty($individualGraphs)) {
          echo '<div class="text-center py-8 text-gray-500">';
          echo '<i class="fas fa-inbox fa-3x mb-4"></i>';
          echo '<p class="text-lg">No approved graphs yet</p>';
          echo '</div>';
      }
      ?>
    </div>
  </div>
  
  <!-- Not Approved Graphs Section -->
  <div class="status-section not-approved">
    <h2 class="status-title text-xl font-semibold text-gray-800 mb-6">Not Approved Graphs</h2>
    <div class="graphs-container">
      <?php
      // Get not approved graphs - ordered by creation date (newest first)
      $not_approved_graphs = [];
      $query = "SELECT * FROM DIT_post WHERE title='graph' AND status='not approved' ORDER BY created_at DESC";
      $result = $conn->query($query);
      
      // Group graphs by group_title
      $groups = [];
      $individualGraphs = [];
      
      while ($row = $result->fetch_assoc()) {
          $graphData = json_decode($row['description'], true); // Using description field for original content
          $title = $graphData['title'] ?? 'Untitled Graph';
          $graphType = isset($graphData['data']) && is_array($graphData['data']) ? 
                      (isset($graphData['data'][0]['category']) ? 'bar' : 'pie') : 'pie';
          $data = $graphData['data'] ?? [];
          $id = $row['id'];
          $created_at = $row['created_at'];
          $reason = $row['content']; // Rejection reason
          $groupTitle = $graphData['group_title'] ?? null;
          
          // Group graphs by group_title
          if ($groupTitle) {
              if (!isset($groups[$groupTitle])) {
                  $groups[$groupTitle] = [
                      'title' => $groupTitle,
                      'graphs' => [],
                      'ids' => [],
                      'reason' => $reason,
                      'created_at' => $created_at
                  ];
              }
              $groups[$groupTitle]['graphs'][] = [
                  'id' => $id,
                  'title' => $title,
                  'graphType' => $graphType,
                  'data' => $data,
                  'created_at' => $created_at
              ];
              $groups[$groupTitle]['ids'][] = $id;
          } else {
              $individualGraphs[] = [
                  'id' => $id,
                  'title' => $title,
                  'graphType' => $graphType,
                  'data' => $data,
                  'created_at' => $created_at,
                  'reason' => $reason
              ];
          }
      }
      
      // Sort groups by creation date (newest first)
      uasort($groups, function($a, $b) {
          return strtotime($b['created_at']) - strtotime($a['created_at']);
      });
      
      // Display group graphs
      foreach ($groups as $groupTitle => $group) {
          echo '<div class="graph-item-wrapper">';
          displayGroupCard($group['ids'], $groupTitle, $group['graphs'], 'not-approved', $group['reason']);
          echo '</div>';
      }
      
      // Display individual graphs
      foreach ($individualGraphs as $graph) {
          echo '<div class="graph-item-wrapper">';
          displayGraphCard($graph['id'], $graph['title'], $graph['graphType'], $graph['data'], $graph['created_at'], 'not-approved', $graph['reason']);
          echo '</div>';
      }
      
      if (empty($groups) && empty($individualGraphs)) {
          echo '<div class="text-center py-8 text-gray-500">';
          echo '<i class="fas fa-inbox fa-3x mb-4"></i>';
          echo '<p class="text-lg">No not approved graphs</p>';
          echo '</div>';
      }
      ?>
    </div>
  </div>
</div>

<!-- Delete/Archive Modal -->
<div id="deleteArchiveModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
  <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md transform transition-all">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-semibold text-gray-800">Confirm Action</h3>
      <button onclick="closeDeleteArchiveModal()" class="text-gray-500 hover:text-gray-700 p-2 rounded-full hover:bg-gray-100 transition duration-200">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>
    <div class="mb-6">
      <p class="text-gray-600 mb-4">What would you like to do with this graph?</p>
      <div class="flex flex-col space-y-3">
        <button onclick="archiveGraph()" class="px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 flex items-center justify-center shadow-md hover:shadow-lg">
          <i class="fas fa-archive mr-2"></i> Archive Graph
        </button>
        <button onclick="deleteGraph()" class="px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 flex items-center justify-center shadow-md hover:shadow-lg">
          <i class="fas fa-trash mr-2"></i> Delete Permanently
        </button>
      </div>
    </div>
    <div class="flex justify-end">
      <button onclick="closeDeleteArchiveModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200 shadow">
        Cancel
      </button>
    </div>
  </div>
</div>

<!-- Edit Graph Modal -->
<div id="editGraphModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
  <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-2xl max-h-screen overflow-y-auto transform transition-all">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-semibold text-gray-800">Edit Graph</h3>
      <button onclick="closeEditGraphModal()" class="text-gray-500 hover:text-gray-700 p-2 rounded-full hover:bg-gray-100 transition duration-200">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>
    
    <div id="editGraphContent">
      <!-- Content will be loaded dynamically -->
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Color palette for charts
const colorPalettes = [
  ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'],
  ['#8AC926', '#1982C4', '#6A4C93', '#F15BB5', '#00BBF9', '#00F5D4'],
  ['#FB5607', '#FF006E', '#8338EC', '#3A86FF', '#06FFA5', '#FFBE0B'],
  ['#E63946', '#F1FAEE', '#A8DADC', '#457B9D', '#1D3557', '#F77F00'],
  ['#2A9D8F', '#E9C46A', '#F4A261', '#E76F51', '#264653', '#E9D8A6']
];

// Function to get colors for a chart
function getChartColors(count, paletteIndex) {
  const palette = colorPalettes[paletteIndex % colorPalettes.length];
  const colors = [];
  for (let i = 0; i < count; i++) {
    colors.push(palette[i % palette.length]);
  }
  return colors;
}

// Store the current graph ID for delete/archive operations
let currentGraphId = null;
let currentGraphIds = null; // For group operations

function showAddGraphForm() {
  document.getElementById('addGraphForm').classList.remove('hidden');
  document.getElementById('addGraphForm').scrollIntoView({ behavior: 'smooth' });
}

function hideAddGraphForm() {
  document.getElementById('addGraphForm').classList.add('hidden');
  document.getElementById('createGroup').checked = false;
  toggleGroupOptions();
}

function toggleGroupOptions() {
  const createGroup = document.getElementById('createGroup').checked;
  const groupOptions = document.getElementById('groupOptions');
  const singleGraphForm = document.getElementById('singleGraphForm');
  const graphTypeSelection = document.getElementById('graphTypeSelection');
  
  if (createGroup) {
    groupOptions.classList.remove('hidden');
    singleGraphForm.classList.add('hidden');
    graphTypeSelection.classList.add('hidden');
    updateGraphForms();
  } else {
    groupOptions.classList.add('hidden');
    singleGraphForm.classList.remove('hidden');
    graphTypeSelection.classList.remove('hidden');
  }
}

function updateGraphForms() {
  const graphCount = document.getElementById('graphCount').value;
  const container = document.getElementById('graphFormsContainer');
  
  container.innerHTML = '';
  
  for (let i = 0; i < graphCount; i++) {
    const formHtml = `
      <div class="mb-6 p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
        <h4 class="text-md font-semibold mb-3 text-gray-700">Graph ${i + 1}</h4>
        
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Graph Title</label>
          <input class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
                 name="graphTitle[]" type="text" placeholder="Enter graph title" required>
        </div>
        
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Graph Type</label>
          <div class="flex space-x-4">
            <label class="inline-flex items-center cursor-pointer">
              <input type="radio" name="graphType[${i}]" value="pie" class="form-radio h-4 w-4 text-orange-600 focus:ring-orange-500" checked onchange="switchGroupGraphType(${i}, 'pie')">
              <span class="ml-2 text-gray-700">Pie Chart</span>
            </label>
            <label class="inline-flex items-center cursor-pointer">
              <input type="radio" name="graphType[${i}]" value="bar" class="form-radio h-4 w-4 text-orange-600 focus:ring-orange-500" onchange="switchGroupGraphType(${i}, 'bar')">
              <span class="ml-2 text-gray-700">Bar Chart</span>
            </label>
          </div>
        </div>
        
        <div id="pieForm${i}" class="graph-form">
          <div class="mb-4">
            <div class="flex justify-between items-center mb-2">
              <label class="block text-gray-700 text-sm font-bold">
                Data Points
              </label>
              <button type="button" onclick="addGroupPieRow(${i})" class="px-3 py-1 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600 transition duration-200 shadow">
                <i class="fas fa-plus mr-1"></i> Add Row
              </button>
            </div>
            
            <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
              <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Label</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Value</th>
                    <th class="py-3 px-4 text-center text-sm font-semibold text-gray-700">Actions</th>
                  </tr>
                </thead>
                <tbody id="pieTableBody${i}">
                  <tr class="data-row border-b border-gray-200 hover:bg-gray-50">
                    <td class="py-3 px-4">
                      <input type="text" name="label[${i}][]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Label" required>
                    </td>
                    <td class="py-3 px-4">
                      <input type="text" name="value[${i}][]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Value" required>
                    </td>
                    <td class="py-3 px-4 text-center">
                      <button type="button" onclick="removeGroupPieRow(${i}, this)" class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition duration-200">
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        <div id="barForm${i}" class="graph-form hidden">
          <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
              Series Labels
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <input type="text" name="series1Label[${i}]" class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
                         placeholder="First Series Label">
              </div>
              <div>
                <input type="text" name="series2Label[${i}]" class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
                         placeholder="Second Series Label">
              </div>
            </div>
          </div>
          
          <div class="mb-4">
            <div class="flex justify-between items-center mb-2">
              <label class="block text-gray-700 text-sm font-bold">
                Data Points
              </label>
              <button type="button" onclick="addGroupBarRow(${i})" class="px-3 py-1 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600 transition duration-200 shadow">
                <i class="fas fa-plus mr-1"></i> Add Row
              </button>
            </div>
            
            <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
              <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Category</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Series 1</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Series 2</th>
                    <th class="py-3 px-4 text-center text-sm font-semibold text-gray-700">Actions</th>
                  </tr>
                </thead>
                <tbody id="barTableBody${i}">
                  <tr class="bar-data-row border-b border-gray-200 hover:bg-gray-50">
                    <td class="py-3 px-4">
                      <input type="text" name="bar_category[${i}][]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Category" required>
                    </td>
                    <td class="py-3 px-4">
                      <input type="text" name="bar_series1[${i}][]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Series 1 Value" required>
                    </td>
                    <td class="py-3 px-4">
                      <input type="text" name="bar_series2[${i}][]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Series 2 Value" required>
                    </td>
                    <td class="py-3 px-4 text-center">
                      <button type="button" onclick="removeGroupBarRow(${i}, this)" class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition duration-200">
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    `;
    
    container.innerHTML += formHtml;
  }
}

function switchGroupGraphType(index, type) {
  const pieForm = document.getElementById(`pieForm${index}`);
  const barForm = document.getElementById(`barForm${index}`);
  
  if (type === 'pie') {
    pieForm.classList.remove('hidden');
    barForm.classList.add('hidden');
  } else {
    pieForm.classList.add('hidden');
    barForm.classList.remove('hidden');
  }
}

function addGroupPieRow(index) {
  const tableBody = document.getElementById(`pieTableBody${index}`);
  const newRow = document.createElement('tr');
  newRow.className = 'data-row border-b border-gray-200 hover:bg-gray-50';
  newRow.innerHTML = `
    <td class="py-3 px-4">
      <input type="text" name="label[${index}][]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Label" required>
    </td>
    <td class="py-3 px-4">
      <input type="text" name="value[${index}][]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Value" required>
    </td>
    <td class="py-3 px-4 text-center">
      <button type="button" onclick="removeGroupPieRow(${index}, this)" class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition duration-200">
        <i class="fas fa-trash"></i>
      </button>
    </td>
  `;
  tableBody.appendChild(newRow);
}

function removeGroupPieRow(index, button) {
  const row = button.closest('tr');
  const tableBody = document.getElementById(`pieTableBody${index}`);
  
  // Don't remove if it's the only row
  if (tableBody.children.length > 1) {
    row.remove();
  } else {
    showNotification('You must have at least one data row', 'warning');
  }
}

function addGroupBarRow(index) {
  const tableBody = document.getElementById(`barTableBody${index}`);
  const newRow = document.createElement('tr');
  newRow.className = 'bar-data-row border-b border-gray-200 hover:bg-gray-50';
  newRow.innerHTML = `
    <td class="py-3 px-4">
      <input type="text" name="bar_category[${index}][]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Category" required>
    </td>
    <td class="py-3 px-4">
      <input type="text" name="bar_series1[${index}][]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Series 1 Value" required>
    </td>
    <td class="py-3 px-4">
      <input type="text" name="bar_series2[${index}][]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Series 2 Value" required>
    </td>
    <td class="py-3 px-4 text-center">
      <button type="button" onclick="removeGroupBarRow(${index}, this)" class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition duration-200">
        <i class="fas fa-trash"></i>
      </button>
    </td>
  `;
  tableBody.appendChild(newRow);
}

function removeGroupBarRow(index, button) {
  const row = button.closest('tr');
  const tableBody = document.getElementById(`barTableBody${index}`);
  
  // Don't remove if it's the only row
  if (tableBody.children.length > 1) {
    row.remove();
  } else {
    showNotification('You must have at least one data row', 'warning');
  }
}

function submitGroupForm() {
  const groupTitle = document.getElementById('groupTitle').value;
  const graphCount = document.getElementById('graphCount').value;
  
  if (!groupTitle) {
    showNotification('Please enter a group title', 'warning');
    return;
  }
  
  // Create a form dynamically to submit the group data
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = 'DIT_Add_Graph.php';
  
  // Add group title
  const groupTitleInput = document.createElement('input');
  groupTitleInput.type = 'hidden';
  groupTitleInput.name = 'groupTitle';
  groupTitleInput.value = groupTitle;
  form.appendChild(groupTitleInput);
  
  // Add graph count
  const graphCountInput = document.createElement('input');
  graphCountInput.type = 'hidden';
  graphCountInput.name = 'graphCount';
  graphCountInput.value = graphCount;
  form.appendChild(graphCountInput);
  
  // Add isGroup flag
  const isGroupInput = document.createElement('input');
  isGroupInput.type = 'hidden';
  isGroupInput.name = 'isGroup';
  isGroupInput.value = '1';
  form.appendChild(isGroupInput);
  
  // Add tab state parameters
  const mainTabInput = document.createElement('input');
  mainTabInput.type = 'hidden';
  mainTabInput.name = 'mainTab';
  mainTabInput.value = 'upload';
  form.appendChild(mainTabInput);
  
  const currentTabInput = document.createElement('input');
  currentTabInput.type = 'hidden';
  currentTabInput.name = 'currentTab';
  currentTabInput.value = 'upload-graphs';
  form.appendChild(currentTabInput);
  
  // Add all graph data
  const container = document.getElementById('graphFormsContainer');
  const inputs = container.querySelectorAll('input, select');
  
  inputs.forEach(input => {
    const clonedInput = input.cloneNode(true);
    form.appendChild(clonedInput);
  });
  
  document.body.appendChild(form);
  form.submit();
}

function switchGraphType(type) {
  if (type === 'pie') {
    document.getElementById('pieForm').style.display = 'block';
    document.getElementById('barForm').style.display = 'none';
  } else {
    document.getElementById('pieForm').style.display = 'none';
    document.getElementById('barForm').style.display = 'block';
  }
}

function addPieRow() {
  const tableBody = document.getElementById('pieTableBody');
  const newRow = document.createElement('tr');
  newRow.className = 'data-row border-b border-gray-200 hover:bg-gray-50';
  newRow.innerHTML = `
    <td class="py-3 px-4">
      <input type="text" name="label[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Label" required>
    </td>
    <td class="py-3 px-4">
      <input type="text" name="value[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Value" required>
    </td>
    <td class="py-3 px-4 text-center">
      <button type="button" onclick="removePieRow(this)" class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition duration-200">
        <i class="fas fa-trash"></i>
      </button>
    </td>
  `;
  tableBody.appendChild(newRow);
}

function removePieRow(button) {
  const row = button.closest('tr');
  const tableBody = document.getElementById('pieTableBody');
  
  // Don't remove if it's the only row
  if (tableBody.children.length > 1) {
    row.remove();
  } else {
    showNotification('You must have at least one data row', 'warning');
  }
}

function addBarRow() {
  const tableBody = document.getElementById('barTableBody');
  const newRow = document.createElement('tr');
  newRow.className = 'bar-data-row border-b border-gray-200 hover:bg-gray-50';
  newRow.innerHTML = `
    <td class="py-3 px-4">
      <input type="text" name="bar_category[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Category" required>
    </td>
    <td class="py-3 px-4">
      <input type="text" name="bar_series1[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Series 1 Value" required>
    </td>
    <td class="py-3 px-4">
      <input type="text" name="bar_series2[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Series 2 Value" required>
    </td>
    <td class="py-3 px-4 text-center">
      <button type="button" onclick="removeBarRow(this)" class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition duration-200">
        <i class="fas fa-trash"></i>
      </button>
    </td>
  `;
  tableBody.appendChild(newRow);
}

function removeBarRow(button) {
  const row = button.closest('tr');
  const tableBody = document.getElementById('barTableBody');
  
  // Don't remove if it's the only row
  if (tableBody.children.length > 1) {
    row.remove();
  } else {
    showNotification('You must have at least one data row', 'warning');
  }
}

// Edit graph functions
function editGraph(graphId) {
  // Show loading indicator
  document.getElementById('editGraphContent').innerHTML = `
    <div class="text-center py-8">
      <i class="fas fa-spinner fa-spin text-2xl text-orange-500 mb-2"></i>
      <p>Loading graph data...</p>
    </div>
  `;
  
  // Show the modal
  document.getElementById('editGraphModal').classList.remove('hidden');
  
  // Fetch graph data
  fetch(`DIT_get_graph.php?id=${graphId}`)
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      // Build the edit form based on graph type
      let formHtml = `
        <form id="editGraphForm" onsubmit="updateGraph(event, ${graphId})">
          <input type="hidden" id="editGraphId" name="graph_id" value="${data.id}">
          <input type="hidden" name="graphType" value="${data.type}">
          <input type="hidden" name="mainTab" value="upload">
          <input type="hidden" name="currentTab" value="upload-graphs">
          
          <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="editGraphTitle">
              Graph Title
            </label>
            <input class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
                   id="editGraphTitle" name="graphTitle" type="text" value="${data.title}" required>
          </div>
      `;
      
      // Add group title field if it exists
      if (data.group_title) {
        formHtml += `
          <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="editGroupTitle">
              Group Title
            </label>
            <input class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
                   id="editGroupTitle" name="groupTitle" type="text" value="${data.group_title}" readonly>
            <p class="text-xs text-gray-500 mt-1">Group title cannot be changed here. Edit the entire group to change it.</p>
          </div>
        `;
      }
      
      if (data.type === 'pie') {
        formHtml += `
          <div class="mb-4">
            <div class="flex justify-between items-center mb-2">
              <label class="block text-gray-700 text-sm font-bold">
                Data Points
              </label>
              <button type="button" onclick="addEditPieRow()" class="px-3 py-1 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600 transition duration-200 shadow">
                <i class="fas fa-plus mr-1"></i> Add Row
              </button>
            </div>
            
            <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
              <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Label</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Value</th>
                    <th class="py-3 px-4 text-center text-sm font-semibold text-gray-700">Actions</th>
                  </tr>
                </thead>
                <tbody id="editPieTableBody">
        `;
        
        data.data.forEach(item => {
          formHtml += `
            <tr class="data-row border-b border-gray-200 hover:bg-gray-50">
              <td class="py-3 px-4">
                <input type="text" name="label[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" value="${item.label}" required>
              </td>
              <td class="py-3 px-4">
                <input type="text" name="value[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" value="${item.value}" required>
              </td>
              <td class="py-3 px-4 text-center">
                <button type="button" onclick="removeEditPieRow(this)" class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition duration-200">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          `;
        });
        
        formHtml += `
                </tbody>
              </table>
            </div>
          </div>
        `;
      } else {
        const series1Label = data.data[0]?.series1_label || '';
        const series2Label = data.data[0]?.series2_label || '';
        
        formHtml += `
          <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
              Series Labels
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <input type="text" name="series1Label" class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
                         placeholder="First Series Label" value="${series1Label}">
              </div>
              <div>
                <input type="text" name="series2Label" class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
                         placeholder="Second Series Label" value="${series2Label}">
              </div>
            </div>
          </div>
          
          <div class="mb-4">
            <div class="flex justify-between items-center mb-2">
              <label class="block text-gray-700 text-sm font-bold">
                Data Points
              </label>
              <button type="button" onclick="addEditBarRow()" class="px-3 py-1 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600 transition duration-200 shadow">
                <i class="fas fa-plus mr-1"></i> Add Row
              </button>
            </div>
            
            <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
              <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Category</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Series 1</th>
                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Series 2</th>
                    <th class="py-3 px-4 text-center text-sm font-semibold text-gray-700">Actions</th>
                  </tr>
                </thead>
                <tbody id="editBarTableBody">
        `;
        
        data.data.forEach(item => {
          formHtml += `
            <tr class="bar-data-row border-b border-gray-200 hover:bg-gray-50">
              <td class="py-3 px-4">
                <input type="text" name="bar_category[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" value="${item.category}" required>
              </td>
              <td class="py-3 px-4">
                <input type="text" name="bar_series1[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" value="${item.series1}" required>
              </td>
              <td class="py-3 px-4">
                <input type="text" name="bar_series2[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" value="${item.series2}" required>
              </td>
              <td class="py-3 px-4 text-center">
                <button type="button" onclick="removeEditBarRow(this)" class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition duration-200">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          `;
        });
        
        formHtml += `
                </tbody>
              </table>
            </div>
          </div>
        `;
      }
      
      formHtml += `
          <div class="flex justify-end space-x-3">
            <button type="button" onclick="closeEditGraphModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200 shadow">
              Cancel
            </button>
            <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition duration-200 shadow-md hover:shadow-lg">
              <i class="fas fa-save mr-2"></i> Update Graph
            </button>
          </div>
        </form>
      `;
      
      // Update the modal content
      document.getElementById('editGraphContent').innerHTML = formHtml;
    })
    .catch(error => {
      console.error('Error:', error);
      document.getElementById('editGraphContent').innerHTML = `
        <div class="text-center py-8">
          <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
          <p class="text-red-700">Failed to load graph data</p>
          <p class="text-gray-600">${error.message}</p>
          <button onclick="closeEditGraphModal()" class="mt-4 px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 shadow">
            Close
          </button>
        </div>
      `;
    });
}

function updateGraph(event, graphId) {
  event.preventDefault();
  
  const form = event.target;
  const formData = new FormData(form);
  
  // Show loading state
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalText = submitBtn.innerHTML;
  submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Updating...';
  submitBtn.disabled = true;
  
  fetch('DIT_Update_Graph.php', {
    method: 'POST',
    body: formData
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      showNotification('Graph updated successfully!');
      closeEditGraphModal();
      // Reload the current tab to show updated graph
      setTimeout(() => {
        location.reload();
      }, 1000);
    } else {
      showNotification('Error updating graph: ' + data.message, 'error');
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('An error occurred while updating the graph', 'error');
    submitBtn.innerHTML = originalText;
    submitBtn.disabled = false;
  });
}

function closeEditGraphModal() {
  document.getElementById('editGraphModal').classList.add('hidden');
}

function addEditPieRow() {
  const tableBody = document.getElementById('editPieTableBody');
  const newRow = document.createElement('tr');
  newRow.className = 'data-row border-b border-gray-200 hover:bg-gray-50';
  newRow.innerHTML = `
    <td class="py-3 px-4">
      <input type="text" name="label[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Label" required>
    </td>
    <td class="py-3 px-4">
      <input type="text" name="value[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Value" required>
    </td>
    <td class="py-3 px-4 text-center">
      <button type="button" onclick="removeEditPieRow(this)" class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition duration-200">
        <i class="fas fa-trash"></i>
      </button>
    </td>
  `;
  tableBody.appendChild(newRow);
}

function removeEditPieRow(button) {
  const row = button.closest('tr');
  const tableBody = document.getElementById('editPieTableBody');
  
  // Don't remove if it's the only row
  if (tableBody.children.length > 1) {
    row.remove();
  } else {
    showNotification('You must have at least one data row', 'warning');
  }
}

function addEditBarRow() {
  const tableBody = document.getElementById('editBarTableBody');
  const newRow = document.createElement('tr');
  newRow.className = 'bar-data-row border-b border-gray-200 hover:bg-gray-50';
  newRow.innerHTML = `
    <td class="py-3 px-4">
      <input type="text" name="bar_category[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Category" required>
    </td>
    <td class="py-3 px-4">
      <input type="text" name="bar_series1[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Series 1 Value" required>
    </td>
    <td class="py-3 px-4">
      <input type="text" name="bar_series2[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Series 2 Value" required>
    </td>
    <td class="py-3 px-4 text-center">
      <button type="button" onclick="removeEditBarRow(this)" class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50 transition duration-200">
        <i class="fas fa-trash"></i>
      </button>
    </td>
  `;
  tableBody.appendChild(newRow);
}

function removeEditBarRow(button) {
  const row = button.closest('tr');
  const tableBody = document.getElementById('editBarTableBody');
  
  // Don't remove if it's the only row
  if (tableBody.children.length > 1) {
    row.remove();
  } else {
    showNotification('You must have at least one data row', 'warning');
  }
}

// Delete/Archive modal functions
function showDeleteArchiveModal(graphId, isGroup = false, graphIds = null) {
  if (isGroup && graphIds) {
    currentGraphIds = graphIds;
    currentGraphId = null;
  } else {
    currentGraphId = graphId;
    currentGraphIds = null;
  }
  document.getElementById('deleteArchiveModal').classList.remove('hidden');
}

function closeDeleteArchiveModal() {
  document.getElementById('deleteArchiveModal').classList.add('hidden');
  currentGraphId = null;
  currentGraphIds = null;
}

function deleteGraph() {
  if (currentGraphIds) {
    // Delete group
    deleteGraphGroup(currentGraphIds);
  } else if (currentGraphId) {
    // Create a FormData object to submit the delete request
    const formData = new FormData();
    formData.append('id', currentGraphId);
    
    // Submit the form using fetch API
    fetch('DIT_Delete_Graph.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showNotification('Graph deleted successfully!');
        // Reload the current tab instead of redirecting
        setTimeout(() => {
          location.reload();
        }, 1000);
      } else {
        showNotification('Error deleting graph: ' + data.message, 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showNotification('An error occurred while deleting the graph', 'error');
    });
  }
}

function deleteGraphGroup(graphIds) {
  // Create a FormData object to submit the delete request for group
  const formData = new FormData();
  formData.append('isGroup', '1');
  
  // Add graph IDs
  graphIds.forEach(id => {
    formData.append('graphIds[]', id);
  });
  
  // Submit the form using fetch API
  fetch('DIT_Delete_Graph.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification('Graphs deleted successfully!');
      // Reload the current tab instead of redirecting
      setTimeout(() => {
        location.reload();
      }, 1000);
    } else {
      showNotification('Error deleting graphs: ' + data.message, 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('An error occurred while deleting the graphs', 'error');
  });
}

function archiveGraph() {
  if (currentGraphIds) {
    // Archive group
    archiveGraphGroup(currentGraphIds);
  } else if (currentGraphId) {
    // Create a FormData object to submit the archive request
    const formData = new FormData();
    formData.append('id', currentGraphId);
    
    // Submit the form using fetch API
    fetch('DIT_Archive_Graph.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showNotification('Graph archived successfully!');
        // Reload the current tab instead of redirecting
        setTimeout(() => {
          location.reload();
        }, 1000);
      } else {
        showNotification('Error archiving graph: ' + data.message, 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showNotification('An error occurred while archiving the graph', 'error');
    });
  }
}

function archiveGraphGroup(graphIds) {
  // Create a FormData object to submit the archive request for group
  const formData = new FormData();
  formData.append('isGroup', '1');
  
  // Add graph IDs
  graphIds.forEach(id => {
    formData.append('graphIds[]', id);
  });
  
  // Submit the form using fetch API
  fetch('DIT_Archive_Graph.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification('Graphs archived successfully!');
      // Reload the current tab instead of redirecting
      setTimeout(() => {
        location.reload();
      }, 1000);
    } else {
      showNotification('Error archiving graphs: ' + data.message, 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('An error occurred while archiving the graphs', 'error');
  });
}
// Show notification function (replaces alerts)
function showNotification(message, type = 'info') {
  // Create notification element
  const notification = document.createElement('div');
  notification.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-lg z-50 flex items-center transform transition-all duration-300 translate-x-full`;
  
  // Set color based on type
  if (type === 'success') {
    notification.classList.add('bg-green-500', 'text-white');
  } else if (type === 'warning') {
    notification.classList.add('bg-yellow-500', 'text-white');
  } else if (type === 'error') {
    notification.classList.add('bg-red-500', 'text-white');
  } else {
    notification.classList.add('bg-blue-500', 'text-white');
  }
  
  // Add icon based on type
  let icon = 'fa-info-circle';
  if (type === 'success') icon = 'fa-check-circle';
  else if (type === 'warning') icon = 'fa-exclamation-triangle';
  else if (type === 'error') icon = 'fa-times-circle';
  
  // Set content
  notification.innerHTML = `
    <i class="fas ${icon} mr-3 text-xl"></i>
    <span>${message}</span>
    <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
      <i class="fas fa-times"></i>
    </button>
  `;
  
  // Add to DOM
  document.body.appendChild(notification);
  
  // Animate in
  setTimeout(() => {
    notification.classList.remove('translate-x-full');
  }, 10);
  
  // Auto remove after 5 seconds
  setTimeout(() => {
    notification.classList.add('translate-x-full');
    setTimeout(() => {
      notification.remove();
    }, 300);
  }, 5000);
}

// Flag to track if charts have been initialized
let chartsInitialized = false;

// Initialize all charts on page load
document.addEventListener('DOMContentLoaded', function() {
  // Check if we're on the graphs tab
  const graphsTab = document.getElementById('upload-graphs');
  if (graphsTab && graphsTab.classList.contains('active')) {
    // Initialize all graphs
    initializeAllCharts();
    chartsInitialized = true;
  }
  
  // Set up a MutationObserver to detect when the graphs tab becomes active
  const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
        const target = mutation.target;
        if (target.id === 'upload-graphs') {
          // Check if the tab is now active
          const isActive = target.classList.contains('active');
          
          if (isActive && !chartsInitialized) {
            // The graphs tab is now active, initialize charts
            console.log('Graphs tab activated, initializing charts');
            setTimeout(initializeAllCharts, 100);
            chartsInitialized = true;
          }
        }
      }
    });
  });
  
  // Start observing the graphs tab for class changes
  if (graphsTab) {
    observer.observe(graphsTab, { attributes: true });
  }
  
  // Also handle window resize to ensure charts are properly sized
  window.addEventListener('resize', function() {
    // Reinitialize all charts on resize
    setTimeout(initializeAllCharts, 100);
  });
});

// Function to initialize all charts
function initializeAllCharts() {
  console.log('Initializing all charts...');
  
  // Get all canvas elements for charts
  const chartCanvases = document.querySelectorAll('canvas[id^="graph"]');
  
  if (chartCanvases.length === 0) {
    console.log('No chart canvases found');
    return;
  }
  
  chartCanvases.forEach((canvas, index) => {
    const graphId = canvas.id.replace('graph', '');
    const graphType = canvas.getAttribute('data-type');
    
    console.log(`Initializing chart ${index}: ${canvas.id}, type: ${graphType}`);
    
    // Get the graph data from the data attribute
    let graphData;
    try {
      // Try to parse the data directly from the data-graph attribute
      const graphDataAttr = canvas.getAttribute('data-graph');
      if (!graphDataAttr) {
        console.error(`No data-graph attribute found for canvas ${canvas.id}`);
        return;
      }
      graphData = JSON.parse(graphDataAttr);
    } catch (e) {
      console.error(`Error parsing graph data for canvas ${canvas.id}:`, e);
      return;
    }
    
    // Validate the data structure
    if (!Array.isArray(graphData) || graphData.length === 0) {
      console.error(`Invalid graph data structure for canvas ${canvas.id}`);
      return;
    }
    
    // Destroy existing chart if it exists
    const existingChart = Chart.getChart(canvas);
    if (existingChart) {
      existingChart.destroy();
    }
    
    // Create the chart with original data (no sorting)
    if (graphType === 'pie') {
      // For pie charts with many labels, adjust the legend position and chart size
      const labelCount = graphData.length;
      let legendPosition = 'bottom';
      let chartHeight = 250;
      
      // Adjust for many labels
      if (labelCount > 8) {
        legendPosition = 'right';
        chartHeight = 300;
      }
      
      // Set the container height
      const container = canvas.parentElement;
      container.style.height = chartHeight + 'px';
      
      new Chart(canvas, {
        type: 'pie',
        data: {
          labels: graphData.map(item => item.label || ''),
          datasets: [{
            data: graphData.map(item => item.value || 0),
            backgroundColor: getChartColors(graphData.length, index),
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: {
            duration: 500
          },
          plugins: {
            legend: {
              position: legendPosition,
              labels: {
                boxWidth: 15,
                font: {
                  size: labelCount > 8 ? 10 : 12
                },
                padding: labelCount > 8 ? 8 : 15
              }
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  let label = context.label || '';
                  if (label) {
                    label += ': ';
                  }
                  if (context.parsed !== null) {
                    // Get the original value and format
                    const originalItem = graphData[context.dataIndex];
                    label += formatNumber(context.parsed, originalItem.original_value, originalItem.format);
                  }
                  return label;
                }
              }
            }
          }
        }
      });
      console.log(`Successfully initialized pie chart ${index}`);
    } else {
      // For bar charts - ensure proper container dimensions
      const container = canvas.parentElement;
      container.style.height = '300px';
      container.style.width = '100%';
      
      // Ensure canvas has proper dimensions
      canvas.style.height = '100%';
      canvas.style.width = '100%';
      
      new Chart(canvas, {
        type: 'bar',
        data: {
          labels: graphData.map(item => item.category || ''),
          datasets: [
            {
              label: graphData[0]?.series1_label || 'Series 1',
              data: graphData.map(item => item.series1 || 0),
              backgroundColor: getChartColors(1, index)[0],
              borderWidth: 1
            },
            {
              label: graphData[0]?.series2_label || 'Series 2',
              data: graphData.map(item => item.series2 || 0),
              backgroundColor: getChartColors(1, index + 1)[0],
              borderWidth: 1
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                color: 'rgba(0, 0, 0, 0.05)'
              }
            },
            x: {
              ticks: {
                autoSkip: false,
                maxRotation: 90,
                minRotation: 0,
                font: {
                  size: 6
                }
              },
              grid: {
                display: false
              }
            }
          },
          plugins: {
            legend: {
              position: 'top',
              labels: {
                boxWidth: 15,
                font: {
                  size: 12
                }
              }
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  let label = context.dataset.label || '';
                  if (label) {
                    label += ': ';
                  }
                  if (context.parsed.y !== null) {
                    // Get the original value and format
                    const originalItem = graphData[context.dataIndex];
                    const format = context.datasetIndex === 0 ? 
                      originalItem.series1_format : 
                      originalItem.series2_format;
                    label += formatNumber(context.parsed.y, null, format);
                  }
                  return label;
                }
              }
            }
          },
          // Add layout padding to ensure labels are visible
          layout: {
            padding: {
              left: 10,
              right: 20,
              top: 10,
              bottom: 20
            }
          }
        }
      });
      console.log(`Successfully initialized bar chart ${index}`);
    }
  });
}
</script>
<style>
.graph-item-wrapper {
    margin-bottom: 40px; /* Increased space between graph items */
}

.graph-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    height: 500px; /* Fixed height for all cards */
    display: flex;
    flex-direction: column;
    overflow: hidden; /* Prevent any overflow */
    border-radius: 12px; /* More rounded corners */
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); /* Subtle shadow */
}

.graph-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.chart-container {
    position: relative;
    width: 100%;
    height: 100%;
}

/* Ensure the content area grows to fill available space */
.graph-card .flex-grow {
    flex-grow: 1;
    overflow: hidden; /* Prevent scrolling */
}

/* Add border-top to separate content from buttons */
.graph-card .border-t {
    border-top: 1px solid #f3f4f6;
}

/* Ensure text wraps properly */
.break-words {
    word-wrap: break-word;
    overflow-wrap: break-word;
}

/* Set max width for table cells to prevent overflow */
.max-w-[100px] {
    max-width: 100px;
}

/* Loading animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.fa-spinner.fa-spin {
    animation: spin 1s linear infinite;
}

/* Ensure canvas elements are visible */
canvas {
    display: block !important;
    visibility: visible !important;
}

/* Custom scrollbar for tables */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}
::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}
::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Graph card hover effect */
.graph-card {
    transition: all 0.2s ease;
}
.graph-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Status-specific styles */
.status-pending {
    border-left: 4px solid #f59e0b;
}

.status-approved {
    border-left: 4px solid #10b981;
}

.status-not-approved {
    border-left: 4px solid #ef4444;
}

/* Group graph card styles */
.group-graph-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-left: 4px solid #8b5cf6;
    min-height: 400px;
    display: flex;
    flex-direction: column;
    border-radius: 12px; /* More rounded corners */
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); /* Subtle shadow */
}

.group-graph-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.group-graph-header {
    background-color: #f5f3ff;
    border-bottom: 1px solid #e9d5ff;
    border-radius: 12px 12px 0 0; /* Rounded top corners */
}

.group-graph-title {
    color: #6d28d9;
}

.group-graph-content {
    max-height: 500px;
    overflow-y: auto;
    flex-grow: 1;
}

/* Nested graph styles */
.nested-graph {
    margin-bottom: 32px; /* Increased space between nested graphs */
    border: 1px solid #e5e7eb;
    border-radius: 12px; /* More rounded corners */
    padding: 1rem; /* More padding */
    background-color: #f9fafb;
    min-height: 300px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); /* Subtle shadow */
}

.nested-graph-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem; /* Increased margin */
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}

.nested-graph-title {
    font-weight: 600;
    color: #4b5563;
}

.nested-graph-actions {
    display: flex;
    gap: 0.5rem;
}

.nested-graph-chart {
    height: 250px;
}

/* Status section styling */
.status-section {
    margin-bottom: 3rem;
}

.status-title {
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #f3f4f6;
    font-size: 1.25rem;
    font-weight: 600;
}

/* Graphs container */
.graphs-container {
    display: flex;
    flex-direction: column;
}
</style>

<?php
// Function to display a group graph card
function displayGroupCard($graphIds, $groupTitle, $graphs, $status, $reason = '') {
    // Add a class for hover effects and consistent height
    echo '<div class="bg-white border border-gray-200 rounded-lg shadow-md p-5 hover:shadow-lg transition-shadow duration-200 group-graph-card flex flex-col">';
    echo '<div class="flex justify-between items-start mb-4 group-graph-header">';
    echo '<h5 class="font-semibold text-gray-800 text-lg group-graph-title">' . htmlspecialchars($groupTitle) . '</h5>';
    
    // Status badge
    $statusClass = '';
    $statusText = '';
    if ($status == 'pending') {
        $statusClass = 'bg-yellow-100 text-yellow-800';
        $statusText = 'Pending';
    } elseif ($status == 'approved') {
        $statusClass = 'bg-green-100 text-green-800';
        $statusText = 'Approved';
    } elseif ($status == 'not-approved') {
        $statusClass = 'bg-red-100 text-red-800';
        $statusText = 'Rejected';
    }
    
    echo '<span class="px-3 py-1 ' . $statusClass . ' text-xs rounded-full font-medium">' . $statusText . '</span>';
    echo '</div>';
    
    // Content area that will grow to fill available space
    echo '<div class="flex-grow group-graph-content">';
    
    // Display each graph in the group
    foreach ($graphs as $index => $graph) {
        echo '<div class="nested-graph">';
        echo '<div class="nested-graph-header">';
        echo '<h6 class="nested-graph-title">' . htmlspecialchars($graph['title']) . '</h6>';
        echo '<div class="nested-graph-actions">';
        
        // Show edit button only for pending graphs
        if ($status == 'pending') {
            echo '<button onclick="editGraph(' . $graph['id'] . ')" class="px-2 py-1 bg-yellow-500 text-white text-xs rounded hover:bg-yellow-600 transition duration-200 shadow hover:shadow-md">';
            echo '<i class="fas fa-edit mr-1"></i> Edit';
            echo '</button>';
        }
        
        echo '</div>';
        echo '</div>';
        
        // Display the graph
        echo '<div class="nested-graph-chart">';
        
        if ($graph['graphType'] === 'pie') {
            // Make sure we have the correct data structure
            $pieData = [];
            if (isset($graph['data'][0]['label']) && isset($graph['data'][0]['value'])) {
                $pieData = $graph['data'];
            } else {
                // Try to extract label and value from any structure
                foreach ($graph['data'] as $item) {
                    if (is_array($item) && isset($item['label']) && isset($item['value'])) {
                        $pieData[] = $item;
                    }
                }
            }
            
            // Keep original data order for table display
            $originalData = $pieData;
            
            // Use flex row layout for medium screens and above, column for small screens
            echo '<div class="flex flex-col md:flex-row gap-3 h-full">';
            // Table container (left on medium screens, top on small)
            echo '<div class="w-full md:w-1/2">';
            echo '<div class="h-full w-full flex flex-col">';
            echo '<div class="overflow-hidden flex-grow mb-3">';
            
            $total = array_sum(array_column($originalData, 'value'));
            echo '<table class="w-full divide-y divide-gray-200 text-sm">';
            echo '<thead class="bg-gray-50">';
            echo '<tr>';
            echo '<th class="py-3 px-4 text-center font-medium text-gray-500 uppercase">Category</th>';
            echo '<th class="py-3 px-4 text-center font-medium text-gray-500 uppercase">Count</th>';
            echo '<th class="py-3 px-4 text-center font-medium text-gray-500 uppercase">%</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody class="bg-white divide-y divide-gray-200">';
            
            // Limit to 5 rows to prevent overflow
            $displayData = array_slice($originalData, 0, 5);
            foreach ($displayData as $item) {
                $percentage = round(($item['value'] / $total) * 100, 2);
                echo '<tr class="hover:bg-gray-50">';
                // Allow text to wrap and remove truncation
                echo '<td class="py-3 px-4 font-medium text-gray-900 text-center break-words max-w-[100px]" title="' . htmlspecialchars($item['label']) . '">' . htmlspecialchars($item['label']) . '</td>';
                echo '<td class="py-3 px-4 text-gray-500 text-center">' . formatNumber($item['value'], $item['original_value'] ?? null, $item['format'] ?? null) . '</td>';
                echo '<td class="py-3 px-4 text-gray-500 text-center">' . formatPercentage($percentage) . '</td>';
                echo '</tr>';
            }
            
            // If there are more than 5 items, show a summary row
            if (count($originalData) > 5) {
                echo '<tr class="bg-gray-50 font-semibold">';
                echo '<td class="py-3 px-4 text-gray-900 text-center">Others</td>';
                $othersTotal = array_sum(array_column(array_slice($originalData, 5), 'value'));
                $othersPercentage = round(($othersTotal / $total) * 100, 2);
                echo '<td class="py-3 px-4 text-gray-500 text-center">' . formatNumber($othersTotal) . '</td>';
                echo '<td class="py-3 px-4 text-gray-500 text-center">' . formatPercentage($othersPercentage) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            
            // Chart container (right on medium screens, bottom on small)
            echo '<div class="w-full md:w-1/2 flex items-center justify-center">';
            echo '<div class="chart-container" style="height: 200px; width: 100%;">'; // Fixed height
            // Use JSON_UNESCAPED_UNICODE to properly handle special characters
            echo '<canvas id="graph' . $graph['id'] . '" data-type="pie" data-graph=\'' . json_encode($originalData, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS) . '\' style="width: 100%; height: 100%;"></canvas>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        } else {
            // For bar charts, make sure we have the correct data structure
            $barData = [];
            if (isset($graph['data'][0]['category']) && isset($graph['data'][0]['series1']) && isset($graph['data'][0]['series2'])) {
                $barData = $graph['data'];
            } else {
                // Try to extract category, series1, and series2 from any structure
                foreach ($graph['data'] as $item) {
                    if (is_array($item) && isset($item['category']) && isset($item['series1']) && isset($item['series2'])) {
                        $barData[] = $item;
                    }
                }
            }
            
            // Keep original data order for display
            $originalData = $barData;
            
            // Display the chart with reduced height
            echo '<div class="h-64 w-full flex items-center justify-center">'; // Reduced from h-96 to h-80
            echo '<div class="chart-container w-full">';
            // Use JSON_UNESCAPED_UNICODE to properly handle special characters
            echo '<canvas id="graph' . $graph['id'] . '" data-type="bar" data-graph=\'' . json_encode($originalData, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS) . '\' style="width: 100%; height: 100%;"></canvas>';
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    // Close the content area
    echo '</div>';
    
    // Show rejection reason if present
    if ($status == 'not-approved' && !empty($reason)) {
        echo '<div class="mb-4 p-3 bg-red-50 rounded-lg">';
        echo '<p class="text-red-700 font-medium">Rejection Reason:</p>';
        echo '<p class="text-red-600">' . htmlspecialchars($reason) . '</p>';
        echo '</div>';
    }
    
    // Consistent button placement at the bottom for all graphs
    echo '<div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-100">';
    echo '<div class="text-xs text-gray-500">';
    echo 'Group Graph';
    echo '</div>';
    
    echo '<div class="flex space-x-2">';
    
    // Show delete/archive buttons for all graphs
    echo '<button onclick="showDeleteArchiveModal(null, true, [' . implode(',', $graphIds) . '])" class="px-3 py-2 bg-red-500 text-white text-xs rounded-lg hover:bg-red-600 transition duration-200 shadow hover:shadow-lg">';
    echo '<i class="fas fa-trash mr-1"></i> Delete/Archive';
    echo '</button>';
    echo '</div>';
    echo '</div>';
    
    echo '</div>';
}

// Function to display a graph card
function displayGraphCard($id, $title, $graphType, $data, $created_at, $status, $reason = '') {
    // Add a class for hover effects and consistent height
    echo '<div class="bg-white border border-gray-200 rounded-lg shadow-md p-5 hover:shadow-lg transition-shadow duration-200 graph-card flex flex-col status-' . $status . '">';
    echo '<div class="flex justify-between items-start mb-4">';
    echo '<h5 class="font-semibold text-gray-800 text-lg">' . htmlspecialchars($title) . '</h5>';
    
    // Status badge
    $statusClass = '';
    $statusText = '';
    if ($status == 'pending') {
        $statusClass = 'bg-yellow-100 text-yellow-800';
        $statusText = 'Pending';
    } elseif ($status == 'approved') {
        $statusClass = 'bg-green-100 text-green-800';
        $statusText = 'Approved';
    } elseif ($status == 'not-approved') {
        $statusClass = 'bg-red-100 text-red-800';
        $statusText = 'Rejected';
    }
    
    echo '<span class="px-3 py-1 ' . $statusClass . ' text-xs rounded-full font-medium">' . $statusText . '</span>';
    echo '</div>';
    
    // Content area that will grow to fill available space
    echo '<div class="flex-grow">';
    
    // For pie charts, show table and chart
    if ($graphType === 'pie') {
        // Make sure we have the correct data structure
        $pieData = [];
        if (isset($data[0]['label']) && isset($data[0]['value'])) {
            $pieData = $data;
        } else {
            // Try to extract label and value from any structure
            foreach ($data as $item) {
                if (is_array($item) && isset($item['label']) && isset($item['value'])) {
                    $pieData[] = $item;
                }
            }
        }
        
        // Keep original data order for table display
        $originalData = $pieData;
        
        // Use flex row layout for medium screens and above, column for small screens
        echo '<div class="flex flex-col md:flex-row gap-5 h-full">';
        // Table container (left on medium screens, top on small)
        echo '<div class="w-full md:w-1/2">';
        echo '<div class="h-full w-full flex flex-col">';
        echo '<div class="overflow-hidden flex-grow mb-3">';
        
        $total = array_sum(array_column($originalData, 'value'));
        echo '<table class="w-full divide-y divide-gray-200 text-sm">';
        echo '<thead class="bg-gray-50">';
        echo '<tr>';
        echo '<th class="py-3 px-4 text-center font-medium text-gray-500 uppercase">Category</th>';
        echo '<th class="py-3 px-4 text-center font-medium text-gray-500 uppercase">Count</th>';
        echo '<th class="py-3 px-4 text-center font-medium text-gray-500 uppercase">%</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody class="bg-white divide-y divide-gray-200">';
        
        // Limit to 8 rows to prevent overflow
        $displayData = array_slice($originalData, 0, 8);
        foreach ($displayData as $item) {
            $percentage = round(($item['value'] / $total) * 100, 2);
            echo '<tr class="hover:bg-gray-50">';
            // Allow text to wrap and remove truncation
            echo '<td class="py-3 px-4 font-medium text-gray-900 text-center break-words max-w-[100px]" title="' . htmlspecialchars($item['label']) . '">' . htmlspecialchars($item['label']) . '</td>';
            echo '<td class="py-3 px-4 text-gray-500 text-center">' . formatNumber($item['value'], $item['original_value'] ?? null, $item['format'] ?? null) . '</td>';
            echo '<td class="py-3 px-4 text-gray-500 text-center">' . formatPercentage($percentage) . '</td>';
            echo '</tr>';
        }
        
        // If there are more than 8 items, show a summary row
        if (count($originalData) > 8) {
            echo '<tr class="bg-gray-50 font-semibold">';
            echo '<td class="py-3 px-4 text-gray-900 text-center">Others</td>';
            $othersTotal = array_sum(array_column(array_slice($originalData, 8), 'value'));
            $othersPercentage = round(($othersTotal / $total) * 100, 2);
            echo '<td class="py-3 px-4 text-gray-500 text-center">' . formatNumber($othersTotal) . '</td>';
            echo '<td class="py-3 px-4 text-gray-500 text-center">' . formatPercentage($othersPercentage) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        // Chart container (right on medium screens, bottom on small)
        echo '<div class="w-full md:w-1/2 flex items-center justify-center">';
        echo '<div class="chart-container" style="height: 250px; width: 100%;">'; // Fixed height
        // Use JSON_UNESCAPED_UNICODE to properly handle special characters
        echo '<canvas id="graph' . $id . '" data-type="pie" data-graph=\'' . json_encode($originalData, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS) . '\' style="width: 100%; height: 100%;"></canvas>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    } else {
        // For bar charts, make sure we have the correct data structure
        $barData = [];
        if (isset($data[0]['category']) && isset($data[0]['series1']) && isset($data[0]['series2'])) {
            $barData = $data;
        } else {
            // Try to extract category, series1, and series2 from any structure
            foreach ($data as $item) {
                if (is_array($item) && isset($item['category']) && isset($item['series1']) && isset($item['series2'])) {
                    $barData[] = $item;
                }
            }
        }
        
        // Keep original data order for display
        $originalData = $barData;
        
        // Display the chart with reduced height
        echo '<div class="h-80 w-full flex items-center justify-center">'; // Reduced from h-96 to h-80
        echo '<div class="chart-container w-full">';
        // Use JSON_UNESCAPED_UNICODE to properly handle special characters
        echo '<canvas id="graph' . $id . '" data-type="bar" data-graph=\'' . json_encode($originalData, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS) . '\' style="width: 100%; height: 100%;"></canvas>';
        echo '</div>';
        echo '</div>';
    }
    
    // Close the content area
    echo '</div>';
    
    // Show rejection reason if present
    if ($status == 'not-approved' && !empty($reason)) {
        echo '<div class="mb-4 p-3 bg-red-50 rounded-lg">';
        echo '<p class="text-red-700 font-medium">Rejection Reason:</p>';
        echo '<p class="text-red-600">' . htmlspecialchars($reason) . '</p>';
        echo '</div>';
    }
    
    // Consistent button placement at the bottom for all graphs
    echo '<div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-100">';
    echo '<div class="text-xs text-gray-500">';
    echo 'Created: ' . date('M j, Y', strtotime($created_at));
    echo '</div>';
    
    echo '<div class="flex space-x-2">';
    
    // Show edit button only for pending graphs
    if ($status == 'pending') {
        echo '<button onclick="editGraph(' . $id . ')" class="px-3 py-2 bg-yellow-500 text-white text-xs rounded-lg hover:bg-yellow-600 transition duration-200 shadow hover:shadow-md">';
        echo '<i class="fas fa-edit mr-1"></i> Edit';
        echo '</button>';
    }
    
    // Show delete/archive buttons for all graphs
    echo '<button onclick="showDeleteArchiveModal(' . $id . ')" class="px-3 py-2 bg-red-500 text-white text-xs rounded-lg hover:bg-red-600 transition duration-200 shadow hover:shadow-md">';
    echo '<i class="fas fa-trash mr-1"></i> Delete/Archive';
    echo '</button>';
    echo '</div>';
    echo '</div>';
    
    echo '</div>';
}

// Format number for display - use original format if available
function formatNumber($value, $originalValue = null, $format = null) {
    // If format is percentage, return with % sign
    if ($format === 'percentage') {
        if ($value == round($value)) {
            return round($value) . '%';
        }
        return number_format($value, 2) . '%';
    }
    
    // If original value is provided and contains %, return it as is
    if ($originalValue && is_string($originalValue) && strpos($originalValue, '%') !== false) {
        return $originalValue;
    }
    
    // If it's a whole number, return without decimals
    if ($value == round($value)) {
        return round($value);
    }
    
    // Otherwise, return with 2 decimal places
    return number_format($value, 2);
}

// Format percentage for display - add % sign and remove .00 for whole numbers
function formatPercentage($value) {
    if ($value == round($value)) {
        return round($value) . '%';
    }
    return number_format($value, 2) . '%';
}
?>