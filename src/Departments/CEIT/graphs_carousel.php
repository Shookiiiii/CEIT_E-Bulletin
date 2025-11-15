<?php
include "../../db.php";
require_once '../../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// Function to load all graphs from the graphs table for CEIT (all departments)
function loadGraphs($conn) {
    $graphs = [];
    // Fixed query to get all graphs for CEIT (no department filter)
    $query = "SELECT * FROM graphs ORDER BY IFNULL(group_title, title), created_at ASC";
    $result = $conn->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $graphData = json_decode($row['data'], true);
            if ($graphData && isset($row['title']) && isset($graphData)) {
                $graphs[] = [
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'type' => $row['type'],
                    'data' => $graphData,
                    'file_path' => $row['file_path'],
                    'group_title' => $row['group_title'],
                    'created_at' => $row['created_at']
                ];
            }
        }
    }

    return $graphs;
}

$graphs = loadGraphs($conn);

// Group graphs by group_title
$graphGroups = [];
$individualGraphs = [];
foreach ($graphs as $graph) {
    if (!empty($graph['group_title'])) {
        if (!isset($graphGroups[$graph['group_title']])) {
            $graphGroups[$graph['group_title']] = [];
        }
        $graphGroups[$graph['group_title']][] = $graph;
    } else {
        $individualGraphs[] = $graph;
    }
}

// Combine groups and individual graphs
$allItems = [];
foreach ($graphGroups as $groupTitle => $groupGraphs) {
    $allItems[] = [
        'type' => 'group',
        'title' => $groupTitle,
        'graphs' => $groupGraphs
    ];
}
foreach ($individualGraphs as $graph) {
    $allItems[] = [
        'type' => 'graph',
        'graph' => $graph
    ];
}
?>
<!-- graphs_carousel.php -->
<div class="card h-full w-full overflow-hidden card-shadow">
    <div class="h-full flex flex-col">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-600 to-orange-700 text-white p-3 rounded-t-lg flex-shrink-0">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-bold flex items-center">
                    <i class="fas fa-chart-line mr-2"></i>
                    CEIT Analytics Dashboard
                </h2>
            </div>
        </div>

        <!-- Main Carousel Area -->
        <div class="carousel graphs-carousel flex-grow relative overflow-hidden p-0 m-0">
            <?php if (empty($allItems)): ?>
            <!-- No graphs message -->
            <div class="h-full flex items-center justify-center p-4">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded flex items-center text-center">
                    <div>
                        <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl mb-2"></i>
                        <p class="text-sm text-yellow-700 font-medium">No graphs available</p>
                        <p class="text-xs text-yellow-600 mt-1">Please add graphs to display them here.</p>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Graphs Carousel -->
            <div class="h-full flex flex-col">
                <div class="graphs-container flex-grow relative overflow-hidden">
                    <?php foreach ($allItems as $index => $item):
                        if ($item['type'] === 'group'):
                            $groupTitle = $item['title'];
                            $groupGraphs = $item['graphs'];
                    ?>
                    <div class="graph-item <?= $index === 0 ? 'active' : '' ?> h-full w-full absolute inset-0 p-3" data-index="<?= $index ?>">
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md h-full w-full flex flex-col">
                            <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 flex-shrink-0">
                                <h2 class="text-sm font-semibold text-gray-800 flex items-center justify-center">
                                    <i class="fas fa-layer-group mr-2 text-purple-500"></i>
                                    <?= htmlspecialchars($groupTitle) ?>
                                </h2>
                            </div>
                            <div class="flex-grow flex flex-col p-0">
                                <!-- Nested Carousel for Group -->
                                <div class="flex-grow relative overflow-hidden">
                                    <?php foreach ($groupGraphs as $gIndex => $graph):
                                        $title = $graph['title'];
                                        $graphType = $graph['type'];
                                        $data = $graph['data'];
                                    ?>
                                    <div class="nested-graph-item <?= $gIndex === 0 ? 'active' : '' ?> absolute inset-0 p-3" data-group="<?= $index ?>" data-nested="<?= $gIndex ?>">
                                        <div class="h-full w-full flex flex-col">
                                            <div class="border-b border-gray-200 bg-white px-4 py-2 flex-shrink-0">
                                                <h3 class="text-xs font-medium text-gray-700 text-center">
                                                    <?= htmlspecialchars($title) ?>
                                                </h3>
                                            </div>
                                            <div class="flex-grow p-3">
                                                <?php if ($graphType === 'pie'): ?>
                                                <!-- For pie charts, show table and chart -->
                                                <div class="h-full w-full flex flex-col md:flex-row gap-4">
                                                    <div class="w-full md:w-1/2">
                                                        <div class="h-full w-full flex flex-col">
                                                            <div class="overflow-hidden flex-grow mb-3">
                                                                <?php
                                                                // Keep original data order for table
                                                                $originalData = $data;
                                                                $total = array_sum(array_column($originalData, 'value'));
                                                                ?>
                                                                <table class="w-full divide-y divide-gray-200 text-xs">
                                                                    <thead class="bg-gray-50">
                                                                        <tr>
                                                                            <th class="px-2 py-1 text-center font-medium text-gray-500 uppercase">Category</th>
                                                                            <th class="px-2 py-1 text-center font-medium text-gray-500 uppercase">Count</th>
                                                                            <th class="px-2 py-1 text-center font-medium text-gray-500 uppercase">%</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                                        <?php foreach ($originalData as $item): ?>
                                                                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                                            <!-- Allow text to wrap and remove truncation -->
                                                                            <td class="px-2 py-1 font-medium text-gray-900 text-center" title="<?= htmlspecialchars($item['label']) ?>">
                                                                                <?= htmlspecialchars($item['label']) ?>
                                                                            </td>
                                                                            <td class="px-2 py-1 text-gray-500 text-center"><?= formatNumber($item['value'], isset($item['format']) ? $item['format'] : null) ?></td>
                                                                            <td class="px-2 py-1 text-gray-500 text-center"><?= round(($item['value'] / $total) * 100, 1) ?>%</td>
                                                                        </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="w-full md:w-1/2 flex items-center justify-center">
                                                        <div class="chart-container w-full">
                                                            <canvas id="nested_graph_<?= $index ?>_<?= $gIndex ?>"></canvas>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php else: ?>
                                                <!-- For bar charts, show only the chart with full width -->
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <div class="chart-container w-full bar-chart-container">
                                                        <canvas id="nested_graph_<?= $index ?>_<?= $gIndex ?>"></canvas>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Nested Graph Navigation -->
                                <?php if (count($groupGraphs) > 1): ?>
                                <div class="flex justify-center items-center space-x-2 mt-3 flex-shrink-0 relative z-10">
                                    <button onclick="changeNestedGraph(<?= $index ?>, -1)"
                                        class="w-8 h-8 rounded-full bg-white border border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors duration-200 shadow-sm">
                                        <i class="fas fa-chevron-left text-gray-600 text-xs"></i>
                                    </button>
                                    <div class="text-xs text-gray-500">
                                        <span id="currentNestedGraph<?= $index ?>">1</span> of <span id="totalNestedGraphs<?= $index ?>"><?= count($groupGraphs) ?></span>
                                    </div>
                                    <button onclick="changeNestedGraph(<?= $index ?>, 1)"
                                        class="w-8 h-8 rounded-full bg-white border border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors duration-200 shadow-sm">
                                        <i class="fas fa-chevron-right text-gray-600 text-xs"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php else:
                        $graph = $item['graph'];
                        $title = $graph['title'];
                        $graphType = $graph['type'];
                        $data = $graph['data'];
                    ?>
                    <div class="graph-item <?= $index === 0 ? 'active' : '' ?> h-full w-full absolute inset-0 p-3" data-index="<?= $index ?>">
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md h-full w-full flex flex-col">
                            <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 flex-shrink-0">
                                <h2 class="text-sm font-semibold text-gray-800 flex items-center justify-center">
                                    <i class="fas fa-chart-<?= $graphType === 'pie' ? 'pie' : 'bar' ?> mr-2 text-orange-500"></i>
                                    <?= htmlspecialchars($title) ?>
                                </h2>
                            </div>
                            <div class="flex-grow flex p-0">
                                <?php if ($graphType === 'pie'): ?>
                                <!-- For pie charts, show table and chart -->
                                <div class="w-full md:w-1/2 pr-0 md:pr-3">
                                    <div class="h-full w-full flex flex-col">
                                        <div class="overflow-hidden flex-grow mb-3">
                                            <?php
                                            // Keep original data order for table
                                            $originalData = $data;
                                            $total = array_sum(array_column($originalData, 'value'));
                                            ?>
                                            <table class="w-full divide-y divide-gray-200 text-sm">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-2 py-1 text-center font-medium text-gray-500 uppercase">Category</th>
                                                        <th class="px-2 py-1 text-center font-medium text-gray-500 uppercase">Count</th>
                                                        <th class="px-2 py-1 text-center font-medium text-gray-500 uppercase">%</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    <?php foreach ($originalData as $item): ?>
                                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                        <!-- Allow text to wrap and remove truncation -->
                                                        <td class="px-2 py-1 font-medium text-gray-900 text-center" title="<?= htmlspecialchars($item['label']) ?>">
                                                            <?= htmlspecialchars($item['label']) ?>
                                                        </td>
                                                        <td class="px-2 py-1 text-gray-500 text-center"><?= formatNumber($item['value'], isset($item['format']) ? $item['format'] : null) ?></td>
                                                        <td class="px-2 py-1 text-gray-500 text-center"><?= round(($item['value'] / $total) * 100, 1) ?>%</td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="w-full md:w-1/2 pl-0 md:pl-3 flex items-center justify-center">
                                    <div class="chart-container w-full">
                                        <canvas id="individual_graph_<?= $index ?>"></canvas>
                                    </div>
                                </div>
                                <?php else: ?>
                                <!-- For bar charts, show only the chart with full width -->
                                <div class="w-full h-full flex items-center justify-center p-3">
                                    <div class="chart-container w-full bar-chart-container">
                                        <canvas id="individual_graph_<?= $index ?>"></canvas>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Graphs Navigation -->
                <?php if (count($allItems) > 1): ?>
                <div class="flex justify-center items-center space-x-2 mt-3 flex-shrink-0 relative z-10">
                    <button onclick="changeGraph(-1)"
                        class="w-8 h-8 rounded-full bg-white border border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors duration-200 shadow-sm">
                        <i class="fas fa-chevron-left text-gray-600 text-xs"></i>
                    </button>
                    <div class="text-xs text-gray-500">
                        <span id="currentGraph">1</span> of <span id="totalGraphs"><?= count($allItems) ?></span>
                    </div>
                    <button onclick="changeGraph(1)"
                        class="w-8 h-8 rounded-full bg-white border border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors duration-200 shadow-sm">
                        <i class="fas fa-chevron-right text-gray-600 text-xs"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}
.bar-chart-container {
    height: 320px !important;
}

/* Fix for carousel scrolling */
.graphs-carousel {
    overflow: hidden !important;
    position: relative;
    height: 100%;
}

.graphs-container {
    overflow: hidden !important;
    position: relative;
    height: 100%;
}

.graph-item {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    display: flex;
    opacity: 0;
    transition: opacity 0.5s ease-in-out;
}

.graph-item.active {
    opacity: 1;
    z-index: 1;
}

.nested-graph-item {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    display: flex;
    opacity: 0;
    transition: opacity 0.5s ease-in-out;
}

.nested-graph-item.active {
    opacity: 1;
    z-index: 1;
}

/* Ensure no scrolling in carousel */
.card {
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    transition: box-shadow 0.3s ease;
}

.card:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>

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

// Format number for display - use original format if available
function formatNumber(value, format) {
    if (format === 'percentage') {
        if (value == Math.round(value)) {
            return Math.round(value) + '%';
        }
        return parseFloat(value).toFixed(2) + '%';
    } else if (format === 'decimal') {
        return parseFloat(value).toFixed(2);
    } else {
        // Default to integer format
        if (value == Math.round(value)) {
            return Math.round(value);
        }
        return parseFloat(value).toFixed(2);
    }
}

// Cache DOM elements
let currentGraph = 0;
let graphs = {};
let nestedGraphs = {};
let nestedCurrentGraph = {};

// Store chart data for animations
const graphData = [
    <?php if (!empty($allItems)): ?>
    <?php foreach ($allItems as $index => $item): ?>
    <?php if ($item['type'] === 'group'): ?>
    {
        type: "group",
        title: "<?= addslashes($item['title']) ?>",
        graphs: [
            <?php foreach ($item['graphs'] as $gIndex => $graph): ?>
            {
                title: "<?= addslashes($graph['title']) ?>",
                type: "<?= $graph['type'] ?>",
                id: <?= $graph['id'] ?>,
                <?php if ($graph['type'] === 'pie'): ?>
                // Keep original data order
                labels: <?= json_encode(array_column($graph['data'], 'label')) ?>,
                values: <?= json_encode(array_column($graph['data'], 'value')) ?>,
                percentages: <?= json_encode(array_map(function($val) use ($graph) {
                    $total = array_sum(array_column($graph['data'], 'value'));
                    return round(($val / $total) * 100, 1);
                }, array_column($graph['data'], 'value'))) ?>,
                formats: <?= json_encode(array_column($graph['data'], 'format')) ?>
                <?php else: ?>
                // Keep original data order
                categories: <?= json_encode(array_column($graph['data'], 'category')) ?>,
                series1: <?= json_encode(array_column($graph['data'], 'series1')) ?>,
                series2: <?= json_encode(array_column($graph['data'], 'series2')) ?>,
                series1Label: "<?= isset($graph['data'][0]['series1_label']) ? addslashes($graph['data'][0]['series1_label']) : 'Series 1' ?>",
                series2Label: "<?= isset($graph['data'][0]['series2_label']) ? addslashes($graph['data'][0]['series2_label']) : 'Series 2' ?>",
                series1_formats: <?= json_encode(array_column($graph['data'], 'series1_format')) ?>,
                series2_formats: <?= json_encode(array_column($graph['data'], 'series2_format')) ?>
                <?php endif; ?>
            }<?= $gIndex < count($item['graphs']) - 1 ? ',' : '' ?>
            <?php endforeach; ?>
        ]
    }<?= $index < count($allItems) - 1 ? ',' : '' ?>
    <?php else: ?>
    {
        type: "graph",
        id: <?= $item['graph']['id'] ?>,
        title: "<?= addslashes($item['graph']['title']) ?>",
        graphType: "<?= $item['graph']['type'] ?>",
        <?php if ($item['graph']['type'] === 'pie'): ?>
        // Keep original data order
        labels: <?= json_encode(array_column($item['graph']['data'], 'label')) ?>,
        values: <?= json_encode(array_column($item['graph']['data'], 'value')) ?>,
        percentages: <?= json_encode(array_map(function($val) use ($item) {
            $total = array_sum(array_column($item['graph']['data'], 'value'));
            return round(($val / $total) * 100, 1);
        }, array_column($item['graph']['data'], 'value'))) ?>,
        formats: <?= json_encode(array_column($item['graph']['data'], 'format')) ?>
        <?php else: ?>
        // Keep original data order
        categories: <?= json_encode(array_column($item['graph']['data'], 'category')) ?>,
        series1: <?= json_encode(array_column($item['graph']['data'], 'series1')) ?>,
        series2: <?= json_encode(array_column($item['graph']['data'], 'series2')) ?>,
        series1Label: "<?= isset($item['graph']['data'][0]['series1_label']) ? addslashes($item['graph']['data'][0]['series1_label']) : 'Series 1' ?>",
        series2Label: "<?= isset($item['graph']['data'][0]['series2_label']) ? addslashes($item['graph']['data'][0]['series2_label']) : 'Series 2' ?>",
        series1_formats: <?= json_encode(array_column($item['graph']['data'], 'series1_format')) ?>,
        series2_formats: <?= json_encode(array_column($item['graph']['data'], 'series2_format')) ?>
        <?php endif; ?>
    }<?= $index < count($allItems) - 1 ? ',' : '' ?>
    <?php endif; ?>
    <?php endforeach; ?>
    <?php endif; ?>
];

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize the graphs
    initGraphs();

    // Initialize nested graphs
    initNestedGraphs();

    // Add custom styles
    addCustomStyles();

    // Handle window resize to ensure charts are properly sized
    window.addEventListener('resize', () => {
        // Reinitialize all charts on resize
        Object.keys(graphs).forEach(key => {
            if (graphs[key]) {
                graphs[key].destroy();
                graphs[key] = null;
            }
        });

        Object.keys(nestedGraphs).forEach(key => {
            if (nestedGraphs[key]) {
                nestedGraphs[key].destroy();
                nestedGraphs[key] = null;
            }
        });

        // Reinitialize after a short delay
        setTimeout(() => {
            initGraphs();
            initNestedGraphs();
        }, 100);
    });
});

// Change graph with direction
function changeGraph(direction) {
    const items = document.querySelectorAll('.graph-item');
    const totalItems = items.length;

    currentGraph += direction;
    if (currentGraph < 0) currentGraph = totalItems - 1;
    if (currentGraph >= totalItems) currentGraph = 0;

    // Update graph visibility
    items.forEach((item, i) => {
        item.classList.toggle('active', i === currentGraph);
    });

    // Update graph counter
    document.getElementById('currentGraph').textContent = currentGraph + 1;
    document.getElementById('totalGraphs').textContent = totalItems;

    // Initialize the graph when it becomes active
    if (graphData[currentGraph]) {
        if (graphData[currentGraph].type === 'graph') {
            initSingleGraph(currentGraph);
        } else if (graphData[currentGraph].type === 'group') {
            initNestedGraphsForGroup(currentGraph);
        }
    }
}

// Change nested graph with direction
function changeNestedGraph(groupIndex, direction) {
    const key = `nested_${groupIndex}`;
    if (!nestedCurrentGraph[key]) nestedCurrentGraph[key] = 0;

    const items = document.querySelectorAll(`.graph-item:nth-child(${groupIndex + 1}) .nested-graph-item`);
    const totalItems = items.length;

    nestedCurrentGraph[key] += direction;
    if (nestedCurrentGraph[key] < 0) nestedCurrentGraph[key] = totalItems - 1;
    if (nestedCurrentGraph[key] >= totalItems) nestedCurrentGraph[key] = 0;

    // Update graph visibility
    items.forEach((item, i) => {
        item.classList.toggle('active', i === nestedCurrentGraph[key]);
    });

    // Update graph counter
    document.getElementById(`currentNestedGraph${groupIndex}`).textContent = nestedCurrentGraph[key] + 1;
    document.getElementById(`totalNestedGraphs${groupIndex}`).textContent = totalItems;

    // Initialize the nested graph when it becomes active
    initSingleNestedGraph(groupIndex, nestedCurrentGraph[key]);
}

// Initialize all graphs
function initGraphs() {
    // Initialize all visible graphs
    const activeGraphItem = document.querySelector('.graph-item.active');
    if (activeGraphItem) {
        const index = parseInt(activeGraphItem.getAttribute('data-index'));

        if (graphData[index]) {
            if (graphData[index].type === 'graph') {
                initSingleGraph(index);
            } else if (graphData[index].type === 'group') {
                initNestedGraphsForGroup(index);
            }
        }
    }

    // Also initialize all individual graphs that might be visible
    const individualCanvases = document.querySelectorAll('canvas[id^="individual_graph_"]');
    individualCanvases.forEach(canvas => {
        const index = parseInt(canvas.id.replace('individual_graph_', ''));
        if (graphData[index] && graphData[index].type === 'graph') {
            initSingleGraph(index);
        }
    });
}

// Initialize all nested graphs
function initNestedGraphs() {
    // Initialize all visible nested graphs
    const activeGraphItems = document.querySelectorAll('.graph-item.active .nested-graph-item');
    activeGraphItems.forEach(item => {
        const groupIndex = parseInt(item.getAttribute('data-group'));
        const nestedIndex = parseInt(item.getAttribute('data-nested'));

        if (item.classList.contains('active')) {
            initSingleNestedGraph(groupIndex, nestedIndex);
        }
    });
}

// Initialize nested graphs for a specific group
function initNestedGraphsForGroup(groupIndex) {
    const nestedItems = document.querySelectorAll(`.graph-item:nth-child(${groupIndex + 1}) .nested-graph-item`);

    nestedItems.forEach((item, index) => {
        if (item.classList.contains('active')) {
            initSingleNestedGraph(groupIndex, index);
        }
    });
}

// Initialize a single graph
function initSingleGraph(index) {
    const canvasId = `individual_graph_${index}`;
    const canvas = document.getElementById(canvasId);

    if (!canvas) {
        console.error(`Canvas with ID ${canvasId} not found`);
        return;
    }

    // Check if we already have a chart for this canvas
    if (graphs[canvasId]) {
        graphs[canvasId].destroy();
        graphs[canvasId] = null;
    }

    if (graphData[index] && graphData[index].type === 'graph') {
        const data = graphData[index];

        if (data.graphType === 'pie') {
            // Sort data for chart display only
            const sortedIndices = data.values.map((_, i) => i).sort((a, b) => data.values[b] - data.values[a]);
            const sortedLabels = sortedIndices.map(i => data.labels[i]);
            const sortedValues = sortedIndices.map(i => data.values[i]);
            const sortedFormats = sortedIndices.map(i => data.formats[i]);

            graphs[canvasId] = new Chart(canvas, {
                type: 'pie',
                data: {
                    labels: sortedLabels,
                    datasets: [{
                        data: sortedValues,
                        backgroundColor: getChartColors(sortedLabels.length, index),
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 500 // Faster animation for pie charts
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 10,
                                font: {
                                    size: 10
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            titleFont: {
                                size: 12
                            },
                            bodyFont: {
                                size: 12
                            },
                            padding: 10,
                            cornerRadius: 4,
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const percentage = data.percentages[context.dataIndex];

                                    // Get the format information if available
                                    let format = null;
                                    if (sortedFormats && sortedFormats[context.dataIndex]) {
                                        format = sortedFormats[context.dataIndex];
                                    }

                                    return `${label}: ${formatNumber(value, format)} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            // For bar charts - use original data order without sorting
            graphs[canvasId] = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: data.categories,
                    datasets: [
                        {
                            label: data.series1Label,
                            data: data.series1,
                            backgroundColor: getChartColors(1, index)[0],
                            borderWidth: 1
                        },
                        {
                            label: data.series2Label,
                            data: data.series2,
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
                                minRotation:0,
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
                                boxWidth: 12,
                                padding: 10,
                                font: {
                                    size: 10
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            titleFont: {
                                size: 12
                            },
                            bodyFont: {
                                size: 12
                            },
                            padding: 10,
                            cornerRadius: 4,
                            callbacks: {
                                title: function(context) {
                                    // Show full category name in tooltip
                                    return context[0].label;
                                },
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        // Get the format information if available
                                        let format = null;
                                        if (context.datasetIndex === 0 && data.series1_formats && data.series1_formats[context.dataIndex]) {
                                            format = data.series1_formats[context.dataIndex];
                                        } else if (context.datasetIndex === 1 && data.series2_formats && data.series2_formats[context.dataIndex]) {
                                            format = data.series2_formats[context.dataIndex];
                                        }

                                        label += formatNumber(context.parsed.y, format);
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
                            bottom: 15 // Reduced bottom padding
                        }
                    }
                }
            });
        }

        console.log(`Initialized individual graph at index ${index}`);
    }
}

// Initialize a single nested graph
function initSingleNestedGraph(groupIndex, nestedIndex) {
    const canvasId = `nested_graph_${groupIndex}_${nestedIndex}`;
    const canvas = document.getElementById(canvasId);

    if (!canvas) {
        console.error(`Canvas with ID ${canvasId} not found`);
        return;
    }

    // Check if we already have a chart for this canvas
    const key = `${groupIndex}_${nestedIndex}`;
    if (nestedGraphs[key]) {
        nestedGraphs[key].destroy();
        nestedGraphs[key] = null;
    }

    if (graphData[groupIndex] && graphData[groupIndex].type === 'group' && graphData[groupIndex].graphs[nestedIndex]) {
        const data = graphData[groupIndex].graphs[nestedIndex];

        if (data.type === 'pie') {
            // Sort data for chart display only
            const sortedIndices = data.values.map((_, i) => i).sort((a, b) => data.values[b] - data.values[a]);
            const sortedLabels = sortedIndices.map(i => data.labels[i]);
            const sortedValues = sortedIndices.map(i => data.values[i]);
            const sortedFormats = sortedIndices.map(i => data.formats[i]);

            nestedGraphs[key] = new Chart(canvas, {
                type: 'pie',
                data: {
                    labels: sortedLabels,
                    datasets: [{
                        data: sortedValues,
                        backgroundColor: getChartColors(sortedLabels.length, groupIndex + nestedIndex),
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 500 // Faster animation for pie charts
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 8,
                                padding: 5,
                                font: {
                                    size: 8
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            titleFont: {
                                size: 10
                            },
                            bodyFont: {
                                size: 10
                            },
                            padding: 8,
                            cornerRadius: 4,
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const percentage = data.percentages[context.dataIndex];

                                    // Get the format information if available
                                    let format = null;
                                    if (sortedFormats && sortedFormats[context.dataIndex]) {
                                        format = sortedFormats[context.dataIndex];
                                    }

                                    return `${label}: ${formatNumber(value, format)} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            // For bar charts - use original data order without sorting
            nestedGraphs[key] = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: data.categories,
                    datasets: [
                        {
                            label: data.series1Label,
                            data: data.series1,
                            backgroundColor: getChartColors(1, groupIndex + nestedIndex)[0],
                            borderWidth: 1
                        },
                        {
                            label: data.series2Label,
                            data: data.series2,
                            backgroundColor: getChartColors(1, groupIndex + nestedIndex + 1)[0],
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
                                minRotation: 45,
                                font: {
                                    size: 6 // Even smaller font size for category names
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
                                boxWidth: 8,
                                padding: 5,
                                font: {
                                    size: 8
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            titleFont: {
                                size: 10
                            },
                            bodyFont: {
                                size: 10
                            },
                            padding: 8,
                            cornerRadius: 4,
                            callbacks: {
                                title: function(context) {
                                    // Show full category name in tooltip
                                    return context[0].label;
                                },
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        // Get the format information if available
                                        let format = null;
                                        if (context.datasetIndex === 0 && data.series1_formats && data.series1_formats[context.dataIndex]) {
                                            format = data.series1_formats[context.dataIndex];
                                        } else if (context.datasetIndex === 1 && data.series2_formats && data.series2_formats[context.dataIndex]) {
                                            format = data.series2_formats[context.dataIndex];
                                        }

                                        label += formatNumber(context.parsed.y, format);
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
                            bottom: 15 // Reduced bottom padding
                        }
                    }
                }
            });
        }

        console.log(`Initialized nested graph at group ${groupIndex}, nested ${nestedIndex}`);
    }
}

// Add custom styles
function addCustomStyles() {
    const style = document.createElement('style');
    style.textContent = `
    .graph-item {
        display: flex;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
        width: 100%;
        height: 100%;
    }
    .graph-item.active {
        opacity: 1;
    }
    .nested-graph-item {
        display: flex;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
        width: 100%;
        height: 100%;
    }
    .nested-graph-item.active {
        opacity: 1;
    }
    .graphs-carousel {
        padding: 0;
        margin: 0;
        width: 100%;
        height: 100%;
        overflow: hidden !important;
    }
    .graphs-container {
        padding: 0;
        margin: 0;
        width: 100%;
        height: 100%;
        overflow: hidden !important;
    }
    .card {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }
    canvas {
        max-width: 100%;
        max-height: 100%;
    }
    `;
    document.head.appendChild(style);
}
</script>

<?php
// Format number for display - use original format if available
function formatNumber($value, $format = null) {
    if ($format === 'percentage') {
        if ($value == round($value)) {
            return round($value) . '%';
        }
        return number_format($value, 2) . '%';
    } elseif ($format === 'decimal') {
        return number_format($value, 2);
    } else {
        // Default to integer format
        if ($value == round($value)) {
            return round($value);
        }
        return number_format($value, 2);
    }
}
?>