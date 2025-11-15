<?php
include "../../db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['action'])) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid request"]);
        exit;
    }

    $action = $data['action'];
    $entry = $data['entry'] ?? null;

    header('Content-Type: application/json');

    if ($action === "add") {
        $program = $conn->real_escape_string($entry['program']);
        $status  = $conn->real_escape_string($entry['status']);
        $conn->query("INSERT INTO accreditation (program, status) VALUES ('$program', '$status')");
        echo json_encode(["success" => true, "id" => $conn->insert_id]);
        exit;
    }

    if ($action === "update" && isset($entry['id'])) {
        $id      = intval($entry['id']);
        $program = $conn->real_escape_string($entry['program']);
        $status  = $conn->real_escape_string($entry['status']);
        $conn->query("UPDATE accreditation SET program='$program', status='$status' WHERE id=$id");
        echo json_encode(["success" => true]);
        exit;
    }

    if ($action === "delete" && isset($entry['id'])) {
        $id = intval($entry['id']);
        $conn->query("DELETE FROM accreditation WHERE id=$id");
        echo json_encode(["success" => true]);
        exit;
    }

    echo json_encode(["error" => "Unknown action"]);
    exit;
}

// Fetch data sorted by level (Level IV highest)
$result = $conn->query("
    SELECT * FROM accreditation
    ORDER BY 
        CASE status
            WHEN 'Level IV Re-accredited' THEN 4
            WHEN 'Level III Re-accredited' THEN 3
            WHEN 'Level II Re-accredited' THEN 2
            WHEN 'Level I Re-accredited' THEN 1
        END DESC,
        program ASC
");
$rows = $result->fetch_all(MYSQLI_ASSOC);

$programOptions = [
    "BS Architecture",
    "BS Agriculture and Biosystems Engineering",
    "BS Civil Engineering",
    "BS Computer Engineering",
    "BS Computer Science",
    "BS Electrical Engineering",
    "BS Electronics Engineering",
    "BS Information Technology",
    "BS Industrial Engineering",
    "BS Industrial Technology"
];
$statusOptions = [
    "Level I Re-accredited",
    "Level II Re-accredited",
    "Level III Re-accredited",
    "Level IV Re-accredited"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Accreditation Status</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" />
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-4xl mx-auto bg-white p-6 rounded-xl shadow space-y-6">
    <h1 class="text-2xl font-bold text-center">Accreditation Status</h1>

    <div class="flex flex-wrap gap-2">
        <select id="program" class="border rounded px-3 py-2">
            <option value="">Select Program</option>
            <?php foreach ($programOptions as $opt): ?>
                <option value="<?= htmlspecialchars($opt) ?>"><?= $opt ?></option>
            <?php endforeach; ?>
        </select>

        <select id="status" class="border rounded px-3 py-2">
            <option value="">Select Status</option>
            <?php foreach ($statusOptions as $opt): ?>
                <option value="<?= htmlspecialchars($opt) ?>"><?= $opt ?></option>
            <?php endforeach; ?>
        </select>

        <button id="addBtn" class="p-2 border border-blue-500 text-blue-500 rounded-lg hover:bg-blue-500 hover:text-white transition duration-200 transform hover:scale-110">
            <i class="fas fa-plus"></i> Add
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 rounded" id="accreditationTable">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-2 text-left">Program</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr data-id="<?= $row['id'] ?>" class="hover:bg-gray-50">
                        <td class="px-4 py-2"><?= htmlspecialchars($row['program']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['status']) ?></td>
                        <td class="px-4 py-2 text-center space-x-2">
                            <button 
                                class="edit p-1 text-sm border border-blue-500 text-blue-500 rounded-lg hover:bg-blue-500 hover:text-white transition duration-200 transform hover:scale-110"
                                data-id="<?= $row['id'] ?>"
                                data-program="<?= htmlspecialchars($row['program']) ?>"
                                data-status="<?= htmlspecialchars($row['status']) ?>"
                            ><i class="fas fa-pen"></i> Edit</button>
                            <button class="delete text-sm p-1 border border-red-500 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition duration-200 transform hover:scale-110">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-96 p-6 relative">
        <button id="closeModal" class="absolute top-2 right-2 text-gray-600 hover:text-gray-800"><i class="fas fa-times"></i></button>
        <h2 class="text-xl font-bold mb-4">Edit Accreditation</h2>

        <input type="hidden" id="editId">

        <div class="mb-3">
            <label class="block mb-1 font-medium text-gray-700">Program</label>
            <select id="editProgram" class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select Program</option>
                <?php foreach ($programOptions as $opt): ?>
                    <option value="<?= htmlspecialchars($opt) ?>"><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="block mb-1 font-medium text-gray-700">Status</label>
            <select id="editStatus" class="border border-gray-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select Status</option>
                <?php foreach ($statusOptions as $opt): ?>
                    <option value="<?= htmlspecialchars($opt) ?>"><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex justify-end gap-2 mt-4 text-sm">
            <button id="cancelEdit" class="p-1 border border-gray-500 text-gray-500 rounded-lg hover:bg-gray-500 hover:text-white transition duration-200 transform hover:scale-110">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button id="saveEdit" class="p-1 border border-green-500 text-green-500 rounded-lg hover:bg-green-500 hover:text-white transition duration-200 transform hover:scale-110">
                <i class="fas fa-check"></i> Save
            </button>
        </div>
    </div>
</div>

<script>
const table = document.querySelector("#accreditationTable tbody");
const addBtn = document.getElementById("addBtn");
const programSelect = document.getElementById("program");
const statusSelect = document.getElementById("status");

const editModal = document.getElementById("editModal");
const closeModalBtn = document.getElementById("closeModal");
const cancelEditBtn = document.getElementById("cancelEdit");

// Sort table by accreditation level
function sortTableByLevel() {
    const levelOrder = {
        "Level IV Re-accredited": 4,
        "Level III Re-accredited": 3,
        "Level II Re-accredited": 2,
        "Level I Re-accredited": 1
    };

    const rowsArray = Array.from(table.querySelectorAll("tr"));
    rowsArray.sort((a, b) => {
        const levelA = levelOrder[a.children[1].textContent] || 0;
        const levelB = levelOrder[b.children[1].textContent] || 0;
        return levelB - levelA || a.children[0].textContent.localeCompare(b.children[0].textContent);
    });
    rowsArray.forEach(row => table.appendChild(row));
}

addBtn.addEventListener("click", () => {
    const program = programSelect.value;
    const status = statusSelect.value;
    if (!program || !status) { alert("Select program and status"); return; }

    fetch("AccreditationStatus.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "add", entry: { program, status } })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const row = document.createElement("tr");
            row.dataset.id = data.id;
            row.classList.add("hover:bg-gray-50");
            row.innerHTML = `
                <td class="px-4 py-2">${program}</td>
                <td class="px-4 py-2">${status}</td>
                <td class="px-4 py-2 text-center space-x-2">
                    <button 
                        class="edit p-1 border border-blue-500 text-blue-500 rounded hover:bg-blue-500 hover:text-white transition duration-200 transform hover:scale-110"
                        data-id="${data.id}"
                        data-program="${program}"
                        data-status="${status}"
                    ><i class="fas fa-pen"></i> Edit</button>
                    <button class="delete p-1 border border-red-500 text-red-500 rounded hover:bg-red-500 hover:text-white transition duration-200 transform hover:scale-110">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>`;
            table.appendChild(row);
            sortTableByLevel();
            programSelect.value = "";
            statusSelect.value = "";
        } else alert("Add failed.");
    });
});

table.addEventListener("click", e => {
    const row = e.target.closest("tr");
    if (e.target.closest(".delete")) {
        const id = row.dataset.id;
        if (!confirm("Delete this record?")) return;

        fetch("AccreditationStatus.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ action: "delete", entry: { id } })
        })
        .then(res => res.json())
        .then(data => { if (data.success) row.remove(); else alert("Delete failed."); });
    }

    if (e.target.closest(".edit")) {
        const id = e.target.closest(".edit").dataset.id;
        document.getElementById("editId").value = id;
        document.getElementById("editProgram").value = e.target.closest(".edit").dataset.program;
        document.getElementById("editStatus").value = e.target.closest(".edit").dataset.status;
        editModal.classList.remove("hidden");
        editModal.classList.add("flex");
    }
});

// Close modal
closeModalBtn.addEventListener("click", () => editModal.classList.add("hidden"));
cancelEditBtn.addEventListener("click", () => editModal.classList.add("hidden"));

// Save from modal
document.getElementById("saveEdit").addEventListener("click", () => {
    const id = document.getElementById("editId").value;
    const program = document.getElementById("editProgram").value;
    const status = document.getElementById("editStatus").value;

    fetch("AccreditationStatus.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "update", entry: { id, program, status } })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const row = table.querySelector(`tr[data-id="${id}"]`);
            row.children[0].textContent = program;
            row.children[1].textContent = status;
            const editBtn = row.querySelector(".edit");
            editBtn.dataset.program = program;
            editBtn.dataset.status = status;
            sortTableByLevel();
            editModal.classList.add("hidden");
        } else alert("Update failed.");
    });
});
</script>

</body>
</html>
