<?php
// CEIT_ManageGraphs.php
include "../../db.php";
?>
<h2 class="text-xl font-semibold text-orange-600 text-center mb-4">CEIT Graphs</h2>
<?php
// Get pending graphs from all departments
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
            ];
        }
        $groups[$groupTitle]['graphs'][] = [
            'id' => $id,
            'title' => $title,
            'graphType' => $graphType,
            'data' => $data,
            'created_at' => $created_at,
        ];
        $groups[$groupTitle]['ids'][] = $id;
    } else {
        $individualGraphs[] = [
            'id' => $id,
            'title' => $title,
            'graphType' => $graphType,
            'data' => $data,
            'created_at' => $created_at,
        ];
    }
}

// Get approved graphs from all departments
$approved_graphs = [];
$query = "SELECT * FROM DIT_post WHERE title='graph' AND status='approved' ORDER BY created_at DESC";
$result = $conn->query($query);

// Group graphs by group_title
$approvedGroups = [];
$approvedIndividualGraphs = [];

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
        if (!isset($approvedGroups[$groupTitle])) {
            $approvedGroups[$groupTitle] = [
                'title' => $groupTitle,
                'graphs' => [],
                'ids' => [],
            ];
        }
        $approvedGroups[$groupTitle]['graphs'][] = [
            'id' => $id,
            'title' => $title,
            'graphType' => $graphType,
            'data' => $data,
            'created_at' => $created_at,
        ];
        $approvedGroups[$groupTitle]['ids'][] = $id;
    } else {
        $approvedIndividualGraphs[] = [
            'id' => $id,
            'title' => $title,
            'graphType' => $graphType,
            'data' => $data,
            'created_at' => $created_at,
        ];
    }
}

// Get not approved graphs from all departments
$not_approved_graphs = [];
$query = "SELECT * FROM DIT_post WHERE title='graph' AND status='not approved' ORDER BY created_at DESC";
$result = $conn->query($query);

// Group graphs by group_title
$notApprovedGroups = [];
$notApprovedIndividualGraphs = [];

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
        if (!isset($notApprovedGroups[$groupTitle])) {
            $notApprovedGroups[$groupTitle] = [
                'title' => $groupTitle,
                'graphs' => [],
                'ids' => [],
                'reason' => $reason,
            ];
        }
        $notApprovedGroups[$groupTitle]['graphs'][] = [
            'id' => $id,
            'title' => $title,
            'graphType' => $graphType,
            'data' => $data,
            'created_at' => $created_at,
        ];
        $notApprovedGroups[$groupTitle]['ids'][] = $id;
    } else {
        $notApprovedIndividualGraphs[] = [
            'id' => $id,
            'title' => $title,
            'graphType' => $graphType,
            'data' => $data,
            'created_at' => $created_at,
            'reason' => $reason,
        ];
    }
}
?>
<!-- Pending Graphs (for approval/rejection) -->
<div class="status-section pending">
    <h2 class="status-title">Pending Graphs</h2>
    <?php if (count($groups) > 0 || count($individualGraphs) > 0): ?>
        <div class="grid grid-cols-1 gap-6">
            <!-- Display group graphs -->
            <?php foreach ($groups as $groupTitle => $group): ?>
                <div class="bg-white border border-gray-200 rounded-lg shadow-md p-5 hover:shadow-lg transition-shadow duration-200 group-graph-card flex flex-col">
                    <div class="flex justify-between items-start mb-4 group-graph-header">
                        <div>
                            <h5 class="font-semibold text-gray-800 text-lg group-graph-title"><?= htmlspecialchars($groupTitle) ?></h5>
                        </div>
                        <div class="flex space-x-2 text-xs">
                            <button class="p-2 border rounded-lg border-green-500 text-green-500 hover:bg-green-500 hover:text-white transition duration-200 transform hover:scale-110 group-approve-btn" 
                                    data-ids="<?= implode(',', $group['ids']) ?>" data-is-group="1" title="Approve Group">
                                <i class="fas fa-check fa-sm"></i>
                                Approve Group
                            </button>
                            <button class="p-2 border rounded-lg border-red-500 text-red-500 hover:bg-red-500 hover:text-white transition duration-200 transform hover:scale-110 group-reject-btn" 
                                    data-ids="<?= implode(',', $group['ids']) ?>" data-is-group="1" title="Reject Group">
                                <i class="fas fa-times fa-sm"></i>
                                Reject Group
                            </button>
                        </div>
                    </div>
                    <div class="flex-grow group-graph-content">
                        <!-- Display each graph in the group -->
                        <?php foreach ($group['graphs'] as $index => $graph): ?>
                            <div class="nested-graph">
                                <div class="nested-graph-header">
                                    <h6 class="nested-graph-title"><?= htmlspecialchars($graph['title']) ?></h6>
                                    
                                </div>
                                <div class="nested-graph-chart">
                                    <?php if ($graph['graphType'] === 'pie'): ?>
                                        <!-- Pie chart display with table -->
                                        <div class="flex flex-col md:flex-row gap-4">
                                            <!-- Table Section -->
                                            <div class="w-full md:w-1/2">
                                                <div class="overflow-hidden">
                                                    <div class="table-responsive">
                                                        <table class="min-w-full divide-y divide-gray-200">
                                                            <thead class="bg-gray-50">
                                                                <tr>
                                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                        Category
                                                                    </th>
                                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                        Count
                                                                    </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="bg-white divide-y divide-gray-200">
                                                                <?php
                                                                $total = 0;
                                                                foreach ($graph['data'] as $i => $item):
                                                                    $total += $item['value'];
                                                                    $label = $item['label'];
                                                                    $value = $item['value'];
                                                                ?>
                                                                    <tr class="hover:bg-gray-50">
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                                            <?= htmlspecialchars($label) ?>
                                                                        </td>
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                            <?= $value ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                                <tr class="bg-gray-50 font-semibold">
                                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                                        Total
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                        <?= $total ?>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Chart Section -->
                                            <div class="w-full md:w-1/2">
                                                <div class="h-64 w-full flex items-center justify-center">
                                                    <div class="chart-container w-full">
                                                        <canvas id="pending-group-chart-<?= $groupTitle ?>-<?= $index ?>"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Bar chart display -->
                                        <div class="h-64 w-full flex items-center justify-center">
                                            <div class="chart-container w-full">
                                                <canvas id="pending-group-chart-<?= $groupTitle ?>-<?= $index ?>"></canvas>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Display individual graphs -->
            <?php foreach ($individualGraphs as $index => $graph): ?>
                <div class="bg-white shadow-md rounded-lg p-4 w-full border border-yellow-500 transition duration-200 transform hover:scale-105">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($graph['title']) ?></h2>
                            
                        </div>
                        <div class="flex space-x-2 text-xs">
                            <button class="p-2 border rounded-lg border-green-500 text-green-500 hover:bg-green-500 hover:text-white transition duration-200 transform hover:scale-110 graph-approve-btn" data-id="<?= $graph['id'] ?>" title="Approve">
                                <i class="fas fa-check fa-sm"></i>
                                Approve
                            </button>
                            <button class="p-2 border rounded-lg border-red-500 text-red-500 hover:bg-red-500 hover:text-white transition duration-200 transform hover:scale-110 graph-reject-btn" data-id="<?= $graph['id'] ?>" title="Reject">
                                <i class="fas fa-times fa-sm"></i>
                                Reject
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Table Section -->
                            <div class="overflow-hidden">
                                <div class="table-responsive">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Category
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Count
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php
                                            $total = 0;
                                            // Check if this is a pie chart or bar chart
                                            $isPieChart = isset($graph['data'][0]['label']);
                                            
                                            foreach ($graph['data'] as $i => $item):
                                                if ($isPieChart) {
                                                    $total += $item['value'];
                                                    $label = $item['label'];
                                                    $value = $item['value'];
                                                } else {
                                                    // For bar charts, we'll just show the first series
                                                    $total += $item['series1'];
                                                    $label = $item['category'];
                                                    $value = $item['series1'];
                                                }
                                            ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        <?= htmlspecialchars($label) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <?= $value ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr class="bg-gray-50 font-semibold">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    Total
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?= $total ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Chart Section -->
                            <div class="flex flex-col">
                                <h3 class="text-lg font-medium text-gray-800 mb-4 text-center">Distribution</h3>
                                <div class="chart-container">
                                    <canvas id="chart-pending-<?= $index ?>"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-inbox fa-3x mb-4"></i>
            <p class="text-lg">No pending graphs</p>
        </div>
    <?php endif; ?>
</div>

<!-- Approved Graphs -->
<div class="status-section approved">
    <h2 class="status-title">Approved Graphs</h2>
    <?php if (count($approvedGroups) > 0 || count($approvedIndividualGraphs) > 0): ?>
        <div class="grid grid-cols-1 gap-6">
            <!-- Display approved group graphs -->
            <?php foreach ($approvedGroups as $groupTitle => $group): ?>
                <div class="bg-white border border-gray-200 rounded-lg shadow-md p-5 hover:shadow-lg transition-shadow duration-200 group-graph-card flex flex-col">
                    <div class="flex justify-between items-start mb-4 group-graph-header">
                        <div>
                            <h5 class="font-semibold text-gray-800 text-lg group-graph-title"><?= htmlspecialchars($groupTitle) ?></h5>
                        </div>
                        <span class="px-3 py-1 bg-green-100 text-green-800 text-xs rounded-full font-medium">Approved</span>
                    </div>
                    <div class="flex-grow group-graph-content">
                        <!-- Display each graph in the group -->
                        <?php foreach ($group['graphs'] as $index => $graph): ?>
                            <div class="nested-graph">
                                <div class="nested-graph-header">
                                    <h6 class="nested-graph-title"><?= htmlspecialchars($graph['title']) ?></h6>
                                    
                                </div>
                                <div class="nested-graph-chart">
                                    <?php if ($graph['graphType'] === 'pie'): ?>
                                        <!-- Pie chart display with table -->
                                        <div class="flex flex-col md:flex-row gap-4">
                                            <!-- Table Section -->
                                            <div class="w-full md:w-1/2">
                                                <div class="overflow-hidden">
                                                    <div class="table-responsive">
                                                        <table class="min-w-full divide-y divide-gray-200">
                                                            <thead class="bg-gray-50">
                                                                <tr>
                                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                        Category
                                                                    </th>
                                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                        Count
                                                                    </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="bg-white divide-y divide-gray-200">
                                                                <?php
                                                                $total = 0;
                                                                foreach ($graph['data'] as $i => $item):
                                                                    $total += $item['value'];
                                                                    $label = $item['label'];
                                                                    $value = $item['value'];
                                                                ?>
                                                                    <tr class="hover:bg-gray-50">
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                                            <?= htmlspecialchars($label) ?>
                                                                        </td>
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                            <?= $value ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                                <tr class="bg-gray-50 font-semibold">
                                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                                        Total
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                        <?= $total ?>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Chart Section -->
                                            <div class="w-full md:w-1/2">
                                                <div class="h-64 w-full flex items-center justify-center">
                                                    <div class="chart-container w-full">
                                                        <canvas id="approved-group-chart-<?= $groupTitle ?>-<?= $index ?>"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Bar chart display -->
                                        <div class="h-64 w-full flex items-center justify-center">
                                            <div class="chart-container w-full">
                                                <canvas id="approved-group-chart-<?= $groupTitle ?>-<?= $index ?>"></canvas>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Display approved individual graphs -->
            <?php foreach ($approvedIndividualGraphs as $index => $graph): ?>
                <div class="bg-white shadow-md rounded-lg p-4 w-full border border-green-500 transition duration-200 transform hover:scale-105">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($graph['title']) ?></h2>
                            
                        </div>
                        <span class="px-3 py-1 bg-green-100 text-green-800 text-xs rounded-full font-medium">Approved</span>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Table Section -->
                            <div class="overflow-hidden">
                                <div class="table-responsive">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Category
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Count
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php
                                            $total = 0;
                                            // Check if this is a pie chart or bar chart
                                            $isPieChart = isset($graph['data'][0]['label']);
                                            
                                            foreach ($graph['data'] as $i => $item):
                                                if ($isPieChart) {
                                                    $total += $item['value'];
                                                    $label = $item['label'];
                                                    $value = $item['value'];
                                                } else {
                                                    // For bar charts, we'll just show the first series
                                                    $total += $item['series1'];
                                                    $label = $item['category'];
                                                    $value = $item['series1'];
                                                }
                                            ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        <?= htmlspecialchars($label) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <?= $value ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr class="bg-gray-50 font-semibold">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    Total
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?= $total ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Chart Section -->
                            <div class="flex flex-col">
                                <h3 class="text-lg font-medium text-gray-800 mb-4 text-center">Distribution</h3>
                                <div class="chart-container">
                                    <canvas id="chart-approved-<?= $index ?>"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-inbox fa-3x mb-4"></i>
            <p class="text-lg">No approved graphs yet</p>
        </div>
    <?php endif; ?>
</div>

<!-- Not Approved Graphs -->
<div class="status-section not-approved">
    <h2 class="status-title">Not Approved Graphs</h2>
    <?php if (count($notApprovedGroups) > 0 || count($notApprovedIndividualGraphs) > 0): ?>
        <div class="grid grid-cols-1 gap-6">
            <!-- Display not approved group graphs -->
            <?php foreach ($notApprovedGroups as $groupTitle => $group): ?>
                <div class="bg-white border border-gray-200 rounded-lg shadow-md p-5 hover:shadow-lg transition-shadow duration-200 group-graph-card flex flex-col">
                    <div class="flex justify-between items-start mb-4 group-graph-header">
                        <div>
                            <h5 class="font-semibold text-gray-800 text-lg group-graph-title"><?= htmlspecialchars($groupTitle) ?></h5>
                          
                        </div>
                        <span class="px-3 py-1 bg-red-100 text-red-800 text-xs rounded-full font-medium">Rejected</span>
                    </div>
                    <div class="mb-4 p-3 bg-red-50 rounded-lg">
                        <p class="text-red-700 font-medium">Rejection Reason:</p>
                        <p class="text-red-600"><?= htmlspecialchars($group['reason']) ?></p>
                    </div>
                    <div class="flex-grow group-graph-content">
                        <!-- Display each graph in the group -->
                        <?php foreach ($group['graphs'] as $index => $graph): ?>
                            <div class="nested-graph">
                                <div class="nested-graph-header">
                                    <h6 class="nested-graph-title"><?= htmlspecialchars($graph['title']) ?></h6>
                                    
                                </div>
                                <div class="nested-graph-chart">
                                    <?php if ($graph['graphType'] === 'pie'): ?>
                                        <!-- Pie chart display with table -->
                                        <div class="flex flex-col md:flex-row gap-4">
                                            <!-- Table Section -->
                                            <div class="w-full md:w-1/2">
                                                <div class="overflow-hidden">
                                                    <div class="table-responsive">
                                                        <table class="min-w-full divide-y divide-gray-200">
                                                            <thead class="bg-gray-50">
                                                                <tr>
                                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                        Category
                                                                    </th>
                                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                        Count
                                                                    </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="bg-white divide-y divide-gray-200">
                                                                <?php
                                                                $total = 0;
                                                                foreach ($graph['data'] as $i => $item):
                                                                    $total += $item['value'];
                                                                    $label = $item['label'];
                                                                    $value = $item['value'];
                                                                ?>
                                                                    <tr class="hover:bg-gray-50">
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                                            <?= htmlspecialchars($label) ?>
                                                                        </td>
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                            <?= $value ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                                <tr class="bg-gray-50 font-semibold">
                                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                                        Total
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                        <?= $total ?>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Chart Section -->
                                            <div class="w-full md:w-1/2">
                                                <div class="h-64 w-full flex items-center justify-center">
                                                    <div class="chart-container w-full">
                                                        <canvas id="rejected-group-chart-<?= $groupTitle ?>-<?= $index ?>"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Bar chart display -->
                                        <div class="h-64 w-full flex items-center justify-center">
                                            <div class="chart-container w-full">
                                                <canvas id="rejected-group-chart-<?= $groupTitle ?>-<?= $index ?>"></canvas>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Display not approved individual graphs -->
            <?php foreach ($notApprovedIndividualGraphs as $index => $graph): ?>
                <div class="bg-white shadow-md rounded-lg p-4 w-full border border-red-500 transition duration-200 transform hover:scale-105">
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($graph['title']) ?></h2>
                            
                        </div>
                        <span class="px-3 py-1 bg-red-100 text-red-800 text-xs rounded-full font-medium">Rejected</span>
                    </div>
                    <div class="p-6">
                        <div class="mb-4 p-3 bg-red-50 rounded-lg">
                            <p class="text-red-700 font-medium">Rejection Reason:</p>
                            <p class="text-red-600"><?= htmlspecialchars($graph['reason']) ?></p>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Table Section -->
                            <div class="overflow-hidden">
                                <div class="table-responsive">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Category
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Count
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php
                                            $total = 0;
                                            // Check if this is a pie chart or bar chart
                                            $isPieChart = isset($graph['data'][0]['label']);
                                            
                                            foreach ($graph['data'] as $i => $item):
                                                if ($isPieChart) {
                                                    $total += $item['value'];
                                                    $label = $item['label'];
                                                    $value = $item['value'];
                                                } else {
                                                    // For bar charts, we'll just show the first series
                                                    $total += $item['series1'];
                                                    $label = $item['category'];
                                                    $value = $item['series1'];
                                                }
                                            ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        <?= htmlspecialchars($label) ?>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <?= $value ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr class="bg-gray-50 font-semibold">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    Total
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?= $total ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Chart Section -->
                            <div class="flex flex-col">
                                <h3 class="text-lg font-medium text-gray-800 mb-4 text-center">Distribution</h3>
                                <div class="chart-container">
                                    <canvas id="chart-not-approved-<?= $index ?>"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-inbox fa-3x mb-4"></i>
            <p class="text-lg">No not approved graphs</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize charts when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Initialize pending individual graphs
    <?php foreach ($individualGraphs as $index => $graph): ?>
        initChart('chart-pending-<?= $index ?>', <?= json_encode($graph['data']) ?>);
    <?php endforeach; ?>
    
    // Initialize pending group graphs
    <?php foreach ($groups as $groupTitle => $group): ?>
        <?php foreach ($group['graphs'] as $index => $graph): ?>
            initChart('pending-group-chart-<?= $groupTitle ?>-<?= $index ?>', <?= json_encode($graph['data']) ?>);
        <?php endforeach; ?>
    <?php endforeach; ?>
    
    // Initialize approved individual graphs
    <?php foreach ($approvedIndividualGraphs as $index => $graph): ?>
        initChart('chart-approved-<?= $index ?>', <?= json_encode($graph['data']) ?>);
    <?php endforeach; ?>
    
    // Initialize approved group graphs
    <?php foreach ($approvedGroups as $groupTitle => $group): ?>
        <?php foreach ($group['graphs'] as $index => $graph): ?>
            initChart('approved-group-chart-<?= $groupTitle ?>-<?= $index ?>', <?= json_encode($graph['data']) ?>);
        <?php endforeach; ?>
    <?php endforeach; ?>
    
    // Initialize not approved individual graphs
    <?php foreach ($notApprovedIndividualGraphs as $index => $graph): ?>
        initChart('chart-not-approved-<?= $index ?>', <?= json_encode($graph['data']) ?>);
    <?php endforeach; ?>
    
    // Initialize not approved group graphs
    <?php foreach ($notApprovedGroups as $groupTitle => $group): ?>
        <?php foreach ($group['graphs'] as $index => $graph): ?>
            initChart('rejected-group-chart-<?= $groupTitle ?>-<?= $index ?>', <?= json_encode($graph['data']) ?>);
        <?php endforeach; ?>
    <?php endforeach; ?>
    
    // Setup approve/reject buttons for individual graphs
    document.querySelectorAll('.graph-approve-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            approveGraph(id, this);
        });
    });
    
    document.querySelectorAll('.graph-reject-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            rejectGraph(id);
        });
    });
    
    // Setup approve/reject buttons for group graphs
    document.querySelectorAll('.group-approve-btn').forEach(button => {
        button.addEventListener('click', function() {
            const ids = this.getAttribute('data-ids').split(',');
            const isGroup = this.getAttribute('data-is-group') === '1';
            approveGraphGroup(ids, isGroup, this);
        });
    });
    
    document.querySelectorAll('.group-reject-btn').forEach(button => {
        button.addEventListener('click', function() {
            const ids = this.getAttribute('data-ids').split(',');
            const isGroup = this.getAttribute('data-is-group') === '1';
            rejectGraphGroup(ids, isGroup, this);
        });
    });
});

function initChart(canvasId, data) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    
    // Check if this is a pie chart or bar chart
    const isPieChart = data.length > 0 && data[0].label !== undefined;
    
    if (isPieChart) {
        // Pie chart
        new Chart(canvas, {
            type: 'pie',
            data: {
                labels: data.map(item => item.label),
                datasets: [{
                    data: data.map(item => item.value),
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    } else {
        // Bar chart
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: data.map(item => item.category),
                datasets: [
                    {
                        label: data[0]?.series1_label || 'Series 1',
                        data: data.map(item => item.series1),
                        backgroundColor: '#36A2EB',
                        borderWidth: 1
                    },
                    {
                        label: data[0]?.series2_label || 'Series 2',
                        data: data.map(item => item.series2),
                        backgroundColor: '#FF6384',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

function approveGraph(id, buttonElement) {
    if (confirm('Are you sure you want to approve this graph?')) {
        // Show loading state
        const originalText = buttonElement.innerHTML;
        buttonElement.innerHTML = '<div class="loading-spinner"></div>';
        buttonElement.disabled = true;

        // Create FormData object for proper encoding
        const formData = new FormData();
        formData.append('id', id);
        formData.append('action', 'approve');

        fetch('../DIT/DIT_Approve_Reject_Graph.php', {
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
                alert('Graph approved successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
                buttonElement.innerHTML = originalText;
                buttonElement.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while approving the graph.');
            buttonElement.innerHTML = originalText;
            buttonElement.disabled = false;
        });
    }
}

function rejectGraph(id) {
    const reason = prompt('Please enter a reason for rejection:');
    if (reason) {
        // Create FormData object for proper encoding
        const formData = new FormData();
        formData.append('id', id);
        formData.append('action', 'reject');
        formData.append('reason', reason);

        fetch('../DIT/DIT_Approve_Reject_Graph.php', {
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
                alert('Graph rejected successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while rejecting the graph.');
        });
    }
}

function approveGraphGroup(ids, isGroup, buttonElement) {
    if (confirm('Are you sure you want to approve this entire group of graphs?')) {
        // Show loading state
        const originalText = buttonElement.innerHTML;
        buttonElement.innerHTML = '<div class="loading-spinner"></div>';
        buttonElement.disabled = true;

        // Debug: Log the IDs
        console.log('Approving group with IDs:', ids);

        // Create FormData object for proper encoding
        const formData = new FormData();
        formData.append('action', 'approve');
        formData.append('isGroup', isGroup);
        
        // Add each ID separately
        ids.forEach((id, index) => {
            formData.append('graphIds[]', id);
        });

        fetch('../DIT/DIT_Approve_Reject_Graph.php', {
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
                alert('Graph group approved successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
                buttonElement.innerHTML = originalText;
                buttonElement.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while approving the graph group.');
            buttonElement.innerHTML = originalText;
            buttonElement.disabled = false;
        });
    }
}

function rejectGraphGroup(ids, isGroup, buttonElement) {
    const reason = prompt('Please enter a reason for rejecting this entire group:');
    if (reason) {
        // Show loading state
        const originalText = buttonElement.innerHTML;
        buttonElement.innerHTML = '<div class="loading-spinner"></div>';
        buttonElement.disabled = true;

        // Debug: Log the IDs
        console.log('Rejecting group with IDs:', ids);

        // Create FormData object for proper encoding
        const formData = new FormData();
        formData.append('action', 'reject');
        formData.append('isGroup', isGroup);
        formData.append('reason', reason);
        
        // Add each ID separately
        ids.forEach((id, index) => {
            formData.append('graphIds[]', id);
        });

        fetch('../DIT/DIT_Approve_Reject_Graph.php', {
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
                alert('Graph group rejected successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
                buttonElement.innerHTML = originalText;
                buttonElement.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while rejecting the graph group.');
            buttonElement.innerHTML = originalText;
            buttonElement.disabled = false;
        });
    }
}
</script>

<style>
.group-graph-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-left: 4px solid #8b5cf6;
    min-height: 400px;
    display: flex;
    flex-direction: column;
}

.group-graph-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.group-graph-header {
    background-color: #f5f3ff;
    border-bottom: 1px solid #e9d5ff;
}

.group-graph-title {
    color: #6d28d9;
}

.group-graph-content {
    max-height: 500px;
    overflow-y: auto;
    flex-grow: 1;
}

.nested-graph {
    margin-bottom: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 0.75rem;
    background-color: #f9fafb;
    min-height: 300px;
}

.nested-graph-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.nested-graph-title {
    font-weight: 600;
    color: #4b5563;
}

.nested-graph-chart {
    height: 250px;
}

.chart-container {
    position: relative;
    height: 100%;
    width: 100%;
}

.loading-spinner {
    border: 4px solid rgba(0, 0, 0, 0.1);
    border-radius: 50%;
    border-top: 4px solid #f97316;
    width: 20px;
    height: 20px;
    animation: spin 1s linear infinite;
    display: inline-block;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>