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
      <form id="pieForm" action="add_graph_ceit.php" method="post" style="display: block;">
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
      <form id="barForm" action="add_graph_ceit.php" method="post" style="display: none;">
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
  
  <!-- All Graphs Section -->
  <div>
    <h3 class="text-xl font-semibold mb-4 text-gray-700 flex items-center">
      <i class="fas fa-chart-bar mr-2 text-blue-500"></i>
      All Graphs
    </h3>
    
    <?php
    // Get all graphs, ordered by created_at in descending order (newest first)
    $query = "SELECT * FROM graphs ORDER BY created_at DESC";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
      $currentGroup = null;
      $individualGraphs = []; // Collect individual graphs
      
      while ($row = $result->fetch_assoc()) {
        $title = $row['title'];
        $graphType = $row['type'];
        $data = json_decode($row['data'], true);
        $groupTitle = $row['group_title'];
        
        // If this is a new group, display the group header
        if ($groupTitle && $groupTitle !== $currentGroup) {
          // First, display any collected individual graphs
          if (!empty($individualGraphs)) {
            echo '<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">';
            foreach ($individualGraphs as $graph) {
              displayGraphCard($graph);
            }
            echo '</div>';
            $individualGraphs = []; // Reset individual graphs array
          }
          
          // Close previous group container if exists
          if ($currentGroup !== null) {
            echo '</div></div>';
          }
          
          // Start new group
          echo '<div class="mb-8">';
          echo '<h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center p-3 bg-purple-50 rounded-lg border border-purple-100 shadow-sm">';
          echo '<i class="fas fa-layer-group mr-2 text-purple-500"></i>';
          echo htmlspecialchars($groupTitle);
          echo '</h4>';
          echo '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">';
          
          $currentGroup = $groupTitle;
        }
        // If this is not part of a group and we were in a group, close the group container
        else if (!$groupTitle && $currentGroup !== null) {
          echo '</div></div>';
          $currentGroup = null;
        }
        
        // Collect individual graphs or display group graphs
        if (!$groupTitle) {
          $individualGraphs[] = [
            'id' => $row['id'],
            'title' => $title,
            'type' => $graphType,
            'data' => $data,
            'created_at' => $row['created_at']
          ];
        } else {
          // Display group graph immediately
          displayGraphCard([
            'id' => $row['id'],
            'title' => $title,
            'type' => $graphType,
            'data' => $data,
            'created_at' => $row['created_at']
          ]);
        }
      }
      
      // Display any remaining individual graphs
      if (!empty($individualGraphs)) {
        // Close previous group container if exists
        if ($currentGroup !== null) {
          echo '</div></div>';
        }
        
        echo '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">';
        foreach ($individualGraphs as $graph) {
          displayGraphCard($graph);
        }
        echo '</div>';
      }
      
      // Close the last group container if we're still in a group
      if ($currentGroup !== null) {
        echo '</div></div>';
      }
    } else {
      echo '<div class="bg-blue-50 border-l-4 border-blue-400 p-6 rounded-lg shadow-sm">';
      echo '<div class="flex items-start">';
      echo '<div class="flex-shrink-0">';
      echo '<i class="fas fa-info-circle text-blue-500 text-2xl"></i>';
      echo '</div>';
      echo '<div class="ml-3">';
      echo '<p class="text-base text-blue-700 font-medium">No graphs found</p>';
      echo '<p class="text-sm text-blue-600 mt-1">Add graphs to display them here.</p>';
      echo '</div>';
      echo '</div>';
      echo '</div>';
    }
    ?>
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
// Global variable to track if we're on the graphs tab
let isGraphsTabActive = false;
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
    // Use a more user-friendly notification instead of alert
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
    // Use a more user-friendly notification instead of alert
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
  form.action = 'add_graph_ceit.php';
  
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
    // Use a more user-friendly notification instead of alert
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
    // Use a more user-friendly notification instead of alert
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
  fetch(`get_graph.php?id=${graphId}`)
    .then(response => response.json())
    .then(data => {
      // Build the edit form based on graph type
      let formHtml = `
        <form id="editGraphForm" action="update_graph.php" method="post">
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
                <input type="text" name="value[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" value="${formatValueForInput(item.value, item.format)}" required>
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
                <input type="text" name="bar_series1[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" value="${formatValueForInput(item.series1, item.series1_format)}" required>
              </td>
              <td class="py-3 px-4">
                <input type="text" name="bar_series2[]" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" value="${formatValueForInput(item.series2, item.series2_format)}" required>
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
    // Use a more user-friendly notification instead of alert
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
    // Use a more user-friendly notification instead of alert
    showNotification('You must have at least one data row', 'warning');
  }
}
// Delete/Archive modal functions
function showDeleteArchiveModal(graphId) {
  currentGraphId = graphId;
  document.getElementById('deleteArchiveModal').classList.remove('hidden');
}
function closeDeleteArchiveModal() {
  document.getElementById('deleteArchiveModal').classList.add('hidden');
  currentGraphId = null;
}
function deleteGraph() {
  if (!currentGraphId) return;
  
  // Create a form to submit the delete request
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = 'delete_graph.php';
  
  // Add graph ID
  const graphIdInput = document.createElement('input');
  graphIdInput.type = 'hidden';
  graphIdInput.name = 'graph_id';
  graphIdInput.value = currentGraphId;
  form.appendChild(graphIdInput);
  
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
  
  // Submit the form
  document.body.appendChild(form);
  form.submit();
}
function archiveGraph() {
  if (!currentGraphId) return;
  
  // Create a form to submit the archive request
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = 'archive_graph.php';
  
  // Add graph ID
  const graphIdInput = document.createElement('input');
  graphIdInput.type = 'hidden';
  graphIdInput.name = 'graph_id';
  graphIdInput.value = currentGraphId;
  form.appendChild(graphIdInput);
  
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
  
  // Submit the form
  document.body.appendChild(form);
  form.submit();
}
// Format number for display - preserve original format
function formatNumber(value, originalValue, format) {
  // If format is percentage, return with % sign
  if (format === 'percentage') {
    if (value == Math.round(value)) {
      return Math.round(value) + '%';
    }
    return parseFloat(value).toFixed(2) + '%';
  }
  
  // If original value is provided and contains %, return it as is
  if (originalValue && typeof originalValue === 'string' && originalValue.includes('%')) {
    return originalValue;
  }
  
  // If it's a whole number, return without decimals
  if (value == Math.round(value)) {
    return Math.round(value);
  }
  
  // Otherwise, return with 2 decimal places
  return parseFloat(value).toFixed(2);
}
// Format value for input field - preserve original format if possible
function formatValueForInput(value, format) {
  // If format is percentage, return with % sign
  if (format === 'percentage') {
    return value + '%';
  }
  
  // If it's already a string with % sign, return as is
  if (typeof value === 'string' && value.includes('%')) {
    return value;
  }
  
  // If it's a number, format it appropriately
  if (typeof value === 'number') {
    if (value == Math.round(value)) {
      return Math.round(value).toString();
    }
    return value.toFixed(2);
  }
  
  // If it's a string without %, try to parse and format
  const numValue = parseFloat(value);
  if (!isNaN(numValue)) {
    if (numValue == Math.round(numValue)) {
      return Math.round(numValue).toString();
    }
    return numValue.toFixed(2);
  }
  
  // Return as is if we can't parse it
  return value;
}
// Format percentage for display - add % sign and remove .00 for whole numbers
function formatPercentage(value) {
  const numValue = parseFloat(value);
  if (numValue == Math.round(numValue)) {
    return Math.round(numValue) + '%';
  }
  return numValue.toFixed(2) + '%';
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
    isGraphsTabActive = true;
  }
  
  // Set up a MutationObserver to detect when the graphs tab becomes active
  const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
        const target = mutation.target;
        if (target.id === 'upload-graphs') {
          // Check if the tab is now active
          const isActive = target.classList.contains('active');
          
          if (isActive && !isGraphsTabActive) {
            // The graphs tab is now active, initialize charts
            console.log('Graphs tab activated, initializing charts');
            setTimeout(initializeAllCharts, 100);
            chartsInitialized = true;
            isGraphsTabActive = true;
          } else if (!isActive && isGraphsTabActive) {
            // The graphs tab is now inactive
            console.log('Graphs tab deactivated');
            isGraphsTabActive = false;
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
    if (isGraphsTabActive) {
      // Reinitialize all charts on resize only if graphs tab is active
      setTimeout(initializeAllCharts, 100);
    }
  });
  
  // Add event listener for visibility change to handle tab switching
  document.addEventListener('visibilitychange', function() {
    if (!document.hidden && isGraphsTabActive) {
      // Page became visible again and we're on graphs tab, reinitialize charts
      console.log('Page became visible, reinitializing charts');
      setTimeout(initializeAllCharts, 100);
    }
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
// Function to initialize charts when the tab is shown
function initializeChartsWhenTabIsShown() {
  // Check if we're on the graphs tab
  const graphsTab = document.getElementById('upload-graphs');
  if (graphsTab && graphsTab.classList.contains('active')) {
    // Initialize all graphs that haven't been initialized yet
    if (!chartsInitialized) {
      initializeAllCharts();
      chartsInitialized = true;
    } else {
      // If charts are already initialized, just resize them
      const chartCanvases = document.querySelectorAll('canvas[id^="graph"]');
      chartCanvases.forEach((canvas) => {
        const chart = Chart.getChart(canvas);
        if (chart) {
          chart.resize();
        }
      });
    }
  }
}
</script>
<style>
.graph-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    height: 500px; /* Fixed height for all cards */
    display: flex;
    flex-direction: column;
    overflow: hidden; /* Prevent any overflow */
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
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.graph-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Smooth transitions for all interactive elements */
button, input, select, .graph-card {
    transition: all 0.2s ease;
}
</style>
<?php
// Function to display a graph card
function displayGraphCard($graph) {
  $title = $graph['title'];
  $graphType = $graph['type'];
  $data = $graph['data'];
  $id = $graph['id'];
  $created_at = $graph['created_at'];
  
  // Add a class for hover effects and consistent height
  echo '<div class="bg-white border border-gray-200 rounded-lg shadow-md p-5 hover:shadow-lg transition-shadow duration-200 graph-card flex flex-col">';
  echo '<div class="flex justify-between items-start mb-4">';
  echo '<h5 class="font-semibold text-gray-800 text-lg">' . htmlspecialchars($title) . '</h5>';
  echo '<span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs rounded-full font-medium">' . ucfirst($graphType) . '</span>';
  echo '</div>';
  
  // Content area that will grow to fill available space
  echo '<div class="flex-grow">';
  
  // For pie charts, display table on left and chart on right
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
    echo '<div class="h-full flex flex-col">';
    echo '<div class="overflow-hidden flex-grow mb-3">';
    
    $total = array_sum(array_column($originalData, 'value'));
    echo '<table class="w-full divide-y divide-gray-200 text-sm">';
    echo '<thead class="bg-gray-50">';
    echo '<tr>';
    echo '<th class="px-2 py-1 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Label</th>';
    echo '<th class="px-2 py-1 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>';
    echo '<th class="px-2 py-1 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">%</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody class="bg-white divide-y divide-gray-200">';
    
    // Limit to 8 rows to prevent overflow
    $displayData = array_slice($originalData, 0, 8);
    foreach ($displayData as $item) {
      $percentage = round(($item['value'] / $total) * 100, 2);
      echo '<tr class="hover:bg-gray-50">';
      // Allow text to wrap and remove truncation
      echo '<td class="px-2 py-1 text-xs text-gray-900 text-center break-words max-w-[100px]" title="' . htmlspecialchars($item['label']) . '">' . htmlspecialchars($item['label']) . '</td>';
      echo '<td class="px-2 py-1 whitespace-nowrap text-xs text-gray-500 text-center">' . formatNumber($item['value'], $item['original_value'] ?? null, $item['format'] ?? null) . '</td>';
      echo '<td class="px-2 py-1 whitespace-nowrap text-xs text-gray-500 text-center">' . formatPercentage($percentage) . '</td>';
      echo '</tr>';
    }
    
    // If there are more than 8 items, show a summary row
    if (count($originalData) > 8) {
      echo '<tr class="bg-gray-50 font-semibold">';
      echo '<td class="px-2 py-1 text-xs text-gray-900 text-center">Others</td>';
      $othersTotal = array_sum(array_column(array_slice($originalData, 8), 'value'));
      $othersPercentage = round(($othersTotal / $total) * 100, 2);
      echo '<td class="px-2 py-1 whitespace-nowrap text-xs text-gray-500 text-center">' . formatNumber($othersTotal) . '</td>';
      echo '<td class="px-2 py-1 whitespace-nowrap text-xs text-gray-500 text-center">' . formatPercentage($othersPercentage) . '</td>';
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
    echo '<div class="chart-container w-full h-full">';
    // Use JSON_UNESCAPED_UNICODE to properly handle special characters
    echo '<canvas id="graph' . $id . '" data-type="bar" data-graph=\'' . json_encode($originalData, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS) . '\' style="width: 100%; height: 100%;"></canvas>';
    echo '</div>';
    echo '</div>';
  }
  
  // Close the content area
  echo '</div>';
  
  // Consistent button placement at the bottom for all graphs
  echo '<div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-100">';
  echo '<div class="text-xs text-gray-500">';
  echo 'Created: ' . date('M j, Y', strtotime($created_at));
  echo '</div>';
  
  echo '<div class="flex space-x-2">';
  echo '<button onclick="editGraph(' . $id . ')" class="px-3 py-2 bg-yellow-500 text-white text-xs rounded-lg hover:bg-yellow-600 transition duration-200 shadow hover:shadow-md">';
  echo '<i class="fas fa-edit mr-1"></i> Edit';
  echo '</button>';
  
  echo '<button onclick="showDeleteArchiveModal(' . $id . ')" class="px-3 py-2 bg-red-500 text-white text-xs rounded-lg hover:bg-red-600 transition duration-200 shadow hover:shadow-md">';
  echo '<i class="fas fa-trash mr-1"></i> Delete/Archive';
  echo '</button>';
  echo '</div>';
  echo '</div>';
  
  echo '</div>';
}
// Format number for display - preserve original format
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