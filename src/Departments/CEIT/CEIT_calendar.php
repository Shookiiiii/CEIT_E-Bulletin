<?php
include "../../db.php";
date_default_timezone_set('Asia/Manila');

// Handle AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['action']) || !isset($data['entry'])) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid request"]);
        exit;
    }

    $entry = $data['entry'];
    $action = $data['action'];
    $id = isset($entry['id']) ? intval($entry['id']) : null;
    $semester = intval($entry['semester']);
    $start_date = $conn->real_escape_string($entry['start_date']);
    $end_date = $conn->real_escape_string($entry['end_date']);
    $desc = $conn->real_escape_string($entry['description']);

    if ($action === "delete" && $id) {
        ob_clean();
        header('Content-Type: application/json');
        $response = ['success' => false];

        try {
            $result = $conn->query("DELETE FROM ceit_calendar WHERE id = $id");
            if ($result) {
                $response['success'] = true;
            } else {
                throw new Exception($conn->error);
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        echo json_encode($response);
        exit;
    }

    if ($action === "save") {
        if ($id) {
            $conn->query("UPDATE ceit_calendar SET start_date='$start_date', end_date='$end_date', description='$desc', semester=$semester WHERE id=$id");
            echo json_encode(["success" => true, "updated" => true]);
        } else {
            $conn->query("INSERT INTO ceit_calendar (start_date, end_date, description, semester) VALUES ('$start_date', '$end_date', '$desc', $semester)");
            $newId = $conn->insert_id;
            echo json_encode(["success" => true, "inserted" => true, "id" => $newId]);
        }
        exit;
    }

    echo json_encode(["error" => "Unknown action"]);
    exit;
}

// Render tables
function renderSemesterTable($conn, $semester)
{
    $color = 'orange';
    $title = $semester === 1 ? 'First Semester' : 'Second Semester';

    $query = "SELECT * FROM ceit_calendar WHERE semester = $semester ORDER BY start_date ASC";
    $result = $conn->query($query);

    echo <<<HTML
    <div>
        <h2 class="text-3xl font-bold text-$color-600 mb-5">$title</h2>
        <div class="overflow-x-auto">
            <table class="calendar-table w-full text-sm mb-4" data-semester="$semester">
                <thead class="bg-$color-200 text-gray-900">
                    <tr>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Description</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
    HTML;

    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $start_date = htmlspecialchars($row['start_date']);
        $end_date = htmlspecialchars($row['end_date']);
        $desc = htmlspecialchars($row['description']);
        
        // Format dates to words
        $start_date_formatted = formatDateToWords($start_date);
        $end_date_formatted = formatDateToWords($end_date);
        
        echo <<<HTML
        <tr data-id="$id">
            <td>
                <input type="hidden" class="date-value" value="$start_date" />
                <input type="text" class="date-display" value="$start_date_formatted" disabled />
            </td>
            <td>
                <input type="hidden" class="date-value" value="$end_date" />
                <input type="text" class="date-display" value="$end_date_formatted" disabled />
            </td>
            <td><input type="text" value="$desc" disabled /></td>
            <td class="text-center space-x-2">
                <button class="edit-row px-3 py-1 border border-blue-500 text-blue-500 rounded-lg hover:bg-blue-500 hover:text-white transition duration-200 transform hover:scale-110" title="Edit">
                    <i class="fas fa-pen fa-sm mr-1"></i>Edit
                </button>
                <button class="delete-row px-3 py-1 border border-red-500 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition duration-200 transform hover:scale-110" title="Delete">
                    <i class="fas fa-trash fa-sm mr-1"></i>Delete
                </button>
            </td>
        </tr>
        HTML;
    }

    echo <<<HTML
                </tbody>
            </table>
            <button class="add-row mb-5 ml-5 mt-2 px-3 py-1 text-orange-500 border border-orange-500 rounded-lg hover:bg-orange-500 hover:text-white flex items-center gap-2 transition duration-200 transform hover:scale-110" data-semester="$semester" title="Add">
                <i class="fas fa-plus fa-sm"></i> Add Row
            </button>
        </div>
    </div>
    HTML;
}

// Function to format date from YYYY-MM-DD to Month Day, Year
function formatDateToWords($dateString) {
    if (empty($dateString)) return "";
    $date = new DateTime($dateString);
    return $date->format('F j, Y'); // e.g., "April 4, 2023"
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>University Calendar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        .calendar-table input {
            width: 100%;
            padding: 4px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .calendar-table th,
        .calendar-table td {
            padding: 8px;
            border: 1px solid #ccc;
            vertical-align: middle;
        }
    </style>
</head>

<body class="bg-gray-100 p-6">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow space-y-10">
        <div class="text-orange-500 font-bold text-center text-4xl">
            Cavite State University
            <div class="text-orange-500 font-bold text-center text-3xl mt-3">
                University Calendar
            </div>
        </div>
        <?php renderSemesterTable($conn, 1); ?>
        <?php renderSemesterTable($conn, 2); ?>
    </div>

    <script>
        // Helper: format "YYYY-MM-DD" -> "Month DD, YYYY"
        function formatDateToWords(dateString) {
            if (!dateString) return "";
            
            // Split the date string to avoid timezone issues
            const parts = dateString.split('-');
            if (parts.length !== 3) return dateString;
            
            const year = parseInt(parts[0]);
            const month = parseInt(parts[1]) - 1; // Months are 0-indexed in JS
            const day = parseInt(parts[2]);
            
            const date = new Date(year, month, day);
            
            // Check if date is valid
            if (isNaN(date)) return dateString;
            
            return date.toLocaleDateString("en-US", { 
                year: "numeric", 
                month: "long", 
                day: "numeric" 
            });
        }

        // Helper: convert "Month DD, YYYY" -> "YYYY-MM-DD"
        function parseDateFromWords(dateString) {
            if (!dateString) return "";
            
            // Try to parse the date string
            const date = new Date(dateString);
            
            // Check if date is valid
            if (isNaN(date)) return "";
            
            // Extract date components in local timezone
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            
            return `${year}-${month}-${day}`;
        }

        // Add new row
        document.querySelectorAll(".add-row").forEach(button => {
            button.addEventListener("click", () => {
                const semester = button.dataset.semester;
                const tbody = document.querySelector(`table[data-semester='${semester}'] tbody`);
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>
                        <input type="hidden" class="date-value" />
                        <input type="text" class="date-display" placeholder="Start Date (e.g., April 5, 2025)" />
                    </td>
                    <td>
                        <input type="hidden" class="date-value" />
                        <input type="text" class="date-display" placeholder="End Date (e.g., April 10, 2025)" />
                    </td>
                    <td><input type="text" /></td>
                    <td class="text-center space-x-2">
                        <button class="save-row px-3 py-1 border border-green-500 text-green-500 rounded-lg hover:bg-green-500 hover:text-white transition duration-200 transform hover:scale-110" title="Save">
                            <i class="fas fa-save fa-sm mr-1"></i>Save
                        </button>
                        <button class="delete-row px-3 py-1 border border-red-500 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition duration-200 transform hover:scale-110" title="Delete">
                            <i class="fas fa-trash fa-sm mr-1"></i>Delete
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        });

        // Handle row actions
        document.addEventListener("click", e => {
            const target = e.target.closest("button");
            if (!target) return;

            const row = target.closest("tr");
            const id = row.dataset.id || null;
            const semester = row.closest("table").dataset.semester;
            
            // Get input fields
            const startValueInput = row.querySelectorAll("input.date-value")[0];
            const endValueInput = row.querySelectorAll("input.date-value")[1];
            const startDisplayInput = row.querySelectorAll("input.date-display")[0];
            const endDisplayInput = row.querySelectorAll("input.date-display")[1];
            const descInput = row.querySelector("input:not(.date-value):not(.date-display)");
            
            // Get values
            const start_date = parseDateFromWords(startDisplayInput.value);
            const end_date = parseDateFromWords(endDisplayInput.value);
            const description = descInput.value;

            // Save
            if (target.classList.contains("save-row")) {
                if (!start_date || !end_date || !description) {
                    alert("Please fill in all fields using valid dates.");
                    return;
                }

                if (new Date(end_date) < new Date(start_date)) {
                    alert("End date must be after start date.");
                    return;
                }

                fetch('CEIT_calendar.php', {
                    method: "POST",
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: "save",
                        entry: { id, semester, start_date, end_date, description }
                    })
                })
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        if (response.id) row.dataset.id = response.id;
                        
                        // Update hidden inputs with the actual date values
                        startValueInput.value = start_date;
                        endValueInput.value = end_date;
                        
                        // Format and update display inputs
                        startDisplayInput.value = formatDateToWords(start_date);
                        endDisplayInput.value = formatDateToWords(end_date);
                        
                        // Disable all inputs
                        startDisplayInput.disabled = true;
                        endDisplayInput.disabled = true;
                        descInput.disabled = true;
                        
                        // Change button to Edit
                        target.outerHTML = `<button class="edit-row px-3 py-1 border border-blue-500 text-blue-500 rounded-lg hover:bg-blue-500 hover:text-white transition duration-200 transform hover:scale-110" title="Edit"><i class="fas fa-pen fa-sm mr-1"></i>Edit</button>`;
                        alert("Row saved successfully.");
                    } else {
                        alert("Save failed.");
                    }
                })
                .catch(() => alert("Error saving row."));
            }

            // Edit
            if (target.classList.contains("edit-row")) {
                startDisplayInput.disabled = false;
                endDisplayInput.disabled = false;
                descInput.disabled = false;
                target.outerHTML = `<button class="save-row px-3 py-1 border border-green-500 text-green-500 rounded-lg hover:bg-green-500 hover:text-white transition duration-200 transform hover:scale-110" title="Save"><i class="fas fa-save fa-sm mr-1"></i>Save</button>`;
            }

            // Delete
            if (target.classList.contains("delete-row")) {
                if (!confirm("Are you sure you want to delete this entry?")) return;
                if (!id) {
                    row.remove();
                    return;
                }

                fetch('CEIT_calendar.php', {
                    method: "POST",
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: "delete",
                        entry: { id: parseInt(id) }
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) row.remove();
                    else alert("Delete failed.");
                })
                .catch(err => alert("Error deleting row: " + err.message));
            }
        });
    </script>
</body>
</html>