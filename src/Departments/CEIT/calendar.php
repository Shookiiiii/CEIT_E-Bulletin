<?php
include "../../db.php";

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
    $date = $conn->real_escape_string($entry['date']);
    $desc = $conn->real_escape_string($entry['description']);

    if ($action === "delete" && $id) {
        // Clear any previous output
        ob_clean();
        
        // Set JSON header
        header('Content-Type: application/json');
        
        // Initialize response array
        $response = ['success' => false];
        
        try {
            $result = $conn->query("DELETE FROM calendar WHERE id = $id");
            
            if ($result) {
                $response['success'] = true;
            } else {
                throw new Exception($conn->error);
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }
        
        // Ensure only JSON is output
        echo json_encode($response);
        exit;
    }

    if ($action === "save") {
        if ($id) {
            $conn->query("UPDATE calendar SET date='$date', description='$desc', semester=$semester WHERE id=$id");
            echo json_encode(["success" => true, "updated" => true]);
        } else {
            $conn->query("INSERT INTO calendar (date, description, semester) VALUES ('$date', '$desc', $semester)");
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

    $query = "SELECT * FROM calendar WHERE semester = $semester ORDER BY date ASC";
    $result = $conn->query($query);

    echo <<<HTML
    <div>
        <h2 class="text-3xl font-bold text-$color-600 mb-5">$title</h2>
        <div class="overflow-x-auto">
            <table class="calendar-table w-full text-sm mb-4" data-semester="$semester">
                <thead class="bg-$color-200 text-gray-900">
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
    HTML;

    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $date = htmlspecialchars($row['date']);
        $desc = htmlspecialchars($row['description']);
        echo <<<HTML
        <tr data-id="$id">
            <td><input type="date" value="$date" disabled /></td>
            <td><input type="text" value="$desc" disabled /></td>
            <td class="text-center space-x-2">
                <button class="edit-row p-2 border border-blue-500 text-blue-500 rounded-lg hover:bg-blue-500 hover:text-white transition duration-200 transform hover:scale-110" title="Edit">
                    <i class="fas fa-pen fa-sm mr-1"></i>Edit
                </button>
                <button class="delete-row p-2 border border-red-500 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition duration-200 transform hover:scale-110" title="Delete">
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
                <i class="fas fa-plus fa-sm"></i>
                Add Row
            </button>
        </div>
    </div>
    HTML;
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
        .calendar-table th, .calendar-table td {
            padding: 8px;
            border: 1px solid #ccc;
            vertical-align: middle;
        }
    </style>
</head>
<body class="bg-gray-100 p-6">
<div class="max-w-5xl mx-auto bg-white p-6 rounded shadow space-y-10">
    <div class="text-orange-500 font-bold text-center text-4xl">
    Cavite State Univeristy
    <div class="text-orange-500 font-bold text-center text-3xl mt-3">
    University Calendar
    </div>
    </div>
    <?php renderSemesterTable($conn, 1); ?>
    <?php renderSemesterTable($conn, 2); ?>
</div>

<script>
    // Add new row
    document.querySelectorAll(".add-row").forEach(button => {
        button.addEventListener("click", () => {
            const semester = button.dataset.semester;
            const tbody = document.querySelector(`table[data-semester='${semester}'] tbody`);
            const row = document.createElement("tr");
            row.innerHTML = `
                <td><input type="date" /></td>
                <td><input type="text" /></td>
                <td class="text-center space-x-2">
                    <button class="save-row p-2 border border-green-500 text-green-500 rounded hover:bg-green-500 hover:text-white transition duration-200 transform hover:scale-110" title="Save">
                        <i class="fas fa-save fa-sm mr-1"></i>Save
                    </button>
                    <button class="delete-row p-2 border border-red-500 text-red-500 rounded hover:bg-red-500 hover:text-white transition duration-200 transform hover:scale-110" title="Delete">
                        <i class="fas fa-trash fa-sm mr-1"></i>Delete
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    });

    // Delegate click events
    document.addEventListener("click", e => {
        const target = e.target.closest("button");
        if (!target) return;

        const row = target.closest("tr");
        const id = row.dataset.id || null;
        const semester = row.closest("table").dataset.semester;
        const dateInput = row.querySelector("input[type='date']");
        const descInput = row.querySelector("input[type='text']");

        // Save row
        if (target.classList.contains("save-row")) {
            const date = dateInput.value;
            const description = descInput.value;

            if (!date || !description) {
                alert("Please fill in both fields.");
                return;
            }

            fetch('calendar.php', {
                method: "POST",
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: "save",
                    entry: { id, semester, date, description }
                })
            })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    if (response.id) row.dataset.id = response.id;
                    dateInput.disabled = true;
                    descInput.disabled = true;
                    target.outerHTML = `<button class="edit-row p-2 border border-blue-500 text-blue-500 rounded hover:bg-blue-500 hover:text-white transition duration-200 transform hover:scale-110" title="Edit"><i class=\"fas fa-pen fa-sm mr-1\"></i>Edit</button>`;
                    alert("Row saved successfully.");
                } else {
                    alert("Save failed.");
                }
            })
            .catch(() => alert("Error saving row."));
        }

        // Edit row
        if (target.classList.contains("edit-row")) {
            dateInput.disabled = false;
            descInput.disabled = false;
            target.outerHTML = `<button class="save-row p-2 border border-green-500 text-green-500 rounded hover:bg-green-500 hover:text-white transition duration-200 transform hover:scale-110" title="Save"><i class=\"fas fa-save fa-sm mr-1\"></i>Save</button>`;
        }

        // Delete row
        if (target.classList.contains("delete-row")) {
            if (!confirm("Are you sure you want to delete this entry?")) return;
            if (!id) {
                row.remove();
                return;
            }

            fetch('calendar.php', {
                method: "POST",
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    action: "delete", 
                    entry: { id: parseInt(id) } 
                })
            })
            .then(async response => {
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`Server error: ${errorText}`);
                }
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || "Delete operation failed");
                }
                row.remove();
            })
            .catch(error => {
                console.error('Delete error:', error);
                alert(`Delete failed: ${error.message}`);
            });
        }
    });
</script>
</body>
</html>