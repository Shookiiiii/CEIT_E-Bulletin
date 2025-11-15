<?php
include "../../db.php";

// Function to get all MIS accounts
function getMISAccounts($conn)
{
    $sql = "SELECT * FROM users WHERE role IN ('MIS', 'LEAD_MIS') ORDER BY role DESC, department_id";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Department mapping
 $departments = [
    1 => 'Computer Engineering and Information Technology',
    2 => 'Information Technology',
    3 => 'Civil Engineering & Architecture',
    4 => 'Computer & Electronics Engineering',
    5 => 'Industrial Engineering & Technology',
    6 => 'Agriculture & Food Engineering'
];

// Get the default user for CEIT (id=1)
 $default_ceit_user = null;
 $result = $conn->query("SELECT * FROM users WHERE id = 1");
if ($result && $result->num_rows > 0) {
    $default_ceit_user = $result->fetch_assoc();
}

// Handle form submission via AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Set content type header for JSON responses
    header('Content-Type: application/json');
    
    if (isset($_POST['add_mis_account'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $department_id = $_POST['department_id'];
        $role = 'MIS'; // Default role for new accounts

        $stmt = $conn->prepare("INSERT INTO users (name, email, role, department_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $email, $role, $department_id);

        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            echo json_encode(['status' => 'success', 'id' => $new_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        exit();
    } elseif (isset($_POST['update_mis_account'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $email = $_POST['email'];

        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        exit();
    }
}

 $accounts = getMISAccounts($conn);

// Function to find account by role and department
function findAccount($accounts, $role, $department_id = null)
{
    foreach ($accounts as $account) {
        if (
            $account['role'] === $role &&
            ($department_id === null || $account['department_id'] == $department_id)
        ) {
            return $account;
        }
    }
    return null;
}
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
    <!-- Default CEIT Card (using existing user with id=1) - This is also the Lead MIS -->
    <?php if ($default_ceit_user): ?>
        <div class="content-card bg-gray-50 p-4 lg:p-6 rounded-xl shadow">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center mr-3">
                    <i class="fas fa-user-tie text-purple-600"></i>
                </div>
                <h3 class="text-lg lg:text-xl font-semibold text-gray-800">Lead MIS Officer (CEIT)</h3>
            </div>

            <div id="dept-1-container">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                    <input type="text" id="dept-1-name" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none" disabled value="<?php echo htmlspecialchars($default_ceit_user['name']); ?>">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" id="dept-1-email" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none" disabled value="<?php echo htmlspecialchars($default_ceit_user['email']); ?>">
                </div>
                <div class="flex justify-end">
                    <button onclick="enableAccountEdit('dept-1')" id="dept-1-edit-btn" class="edit-btn px-3 py-1 lg:px-4 lg:py-2 border border-orange-500 text-orange-500 rounded-lg hover:bg-orange-500 hover:text-white flex items-center gap-2 text-sm">
                        <i class="fas fa-pen"></i> <span class="hidden sm:inline">Edit</span>
                    </button>
                    <button onclick="saveAccount('dept-1', <?php echo $default_ceit_user['id']; ?>)" id="dept-1-save-btn" class="edit-btn px-3 py-1 lg:px-4 lg:py-2 border border-green-500 text-green-500 rounded-lg hover:bg-green-500 hover:text-white hidden flex items-center gap-2 ml-2 text-sm">
                        <i class="fas fa-save"></i> <span class="hidden sm:inline">Save</span>
                    </button>
                </div>
                <div id="dept-1-message" class="mt-3 hidden"></div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Department MIS Officers Cards -->
    <?php foreach ($departments as $id => $name): ?>
        <?php if ($id != 1): // Skip CEIT as it's handled above ?>
            <?php
            $dept_mis = findAccount($accounts, 'MIS', $id);
            ?>
            <div class="content-card bg-gray-50 p-4 lg:p-6 rounded-xl shadow">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                        <i class="fas <?php echo $dept_mis ? 'fa-user' : 'fa-user-plus'; ?> text-blue-600"></i>
                    </div>
                    <h3 class="text-lg lg:text-xl font-semibold text-gray-800"><?php echo $name; ?></h3>
                </div>

                <div id="dept-<?php echo $id; ?>-container">
                    <?php if ($dept_mis): ?>
                        <!-- Existing Account Display -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                            <input type="text" id="dept-<?php echo $id; ?>-name" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none" disabled value="<?php echo htmlspecialchars($dept_mis['name']); ?>">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                            <input type="email" id="dept-<?php echo $id; ?>-email" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none" disabled value="<?php echo htmlspecialchars($dept_mis['email']); ?>">
                        </div>
                        <div class="flex justify-end">
                            <button onclick="enableAccountEdit('dept-<?php echo $id; ?>')" id="dept-<?php echo $id; ?>-edit-btn" class="edit-btn px-3 py-1 lg:px-4 lg:py-2 border border-orange-500 text-orange-500 rounded-lg hover:bg-orange-500 hover:text-white flex items-center gap-2 text-sm">
                                <i class="fas fa-pen"></i> <span class="hidden sm:inline">Edit</span>
                            </button>
                            <button onclick="saveAccount('dept-<?php echo $id; ?>', <?php echo $dept_mis['id']; ?>)" id="dept-<?php echo $id; ?>-save-btn" class="edit-btn px-3 py-1 lg:px-4 lg:py-2 border border-green-500 text-green-500 rounded-lg hover:bg-green-500 hover:text-white hidden flex items-center gap-2 ml-2 text-sm">
                                <i class="fas fa-save"></i> <span class="hidden sm:inline">Save</span>
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- New Account Form -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                            <input type="text" id="dept-<?php echo $id; ?>-name" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none" placeholder="Enter full name">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                            <input type="email" id="dept-<?php echo $id; ?>-email" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none" placeholder="Enter email address">
                        </div>
                        <div class="flex justify-end">
                            <button onclick="addAccount('dept-<?php echo $id; ?>', <?php echo $id; ?>)" id="dept-<?php echo $id; ?>-add-btn" class="edit-btn px-3 py-1 lg:px-4 lg:py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center gap-2 text-sm">
                                <i class="fas fa-plus"></i> <span class="hidden sm:inline">Add Account</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    <div id="dept-<?php echo $id; ?>-message" class="mt-3 hidden"></div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<script>
    // Function to enable editing for an account
    function enableAccountEdit(prefix) {
        const nameInput = document.getElementById(prefix + '-name');
        const emailInput = document.getElementById(prefix + '-email');
        const editBtn = document.getElementById(prefix + '-edit-btn');
        const saveBtn = document.getElementById(prefix + '-save-btn');

        if (nameInput) nameInput.disabled = false;
        if (emailInput) emailInput.disabled = false;
        if (nameInput) nameInput.focus();

        if (editBtn) editBtn.classList.add('hidden');
        if (saveBtn) saveBtn.classList.remove('hidden');
    }

    // Function to save account data (for existing accounts)
    function saveAccount(prefix, accountId) {
        const nameInput = document.getElementById(prefix + '-name');
        const emailInput = document.getElementById(prefix + '-email');
        const messageDiv = document.getElementById(prefix + '-message');
        const editBtn = document.getElementById(prefix + '-edit-btn');
        const saveBtn = document.getElementById(prefix + '-save-btn');

        if (!nameInput || !emailInput || !messageDiv) return;

        const name = nameInput.value;
        const email = emailInput.value;

        // Validate inputs
        if (!name.trim() || !email.trim()) {
            messageDiv.textContent = "Please fill in all fields.";
            messageDiv.classList.remove('hidden', 'text-green-600');
            messageDiv.classList.add('text-red-600');
            return;
        }

        // Show loading state
        messageDiv.textContent = "Saving...";
        messageDiv.classList.remove('hidden', 'text-green-600', 'text-red-600');

        // Send AJAX request
        fetch('MIS_Accounts.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `update_mis_account=1&id=${accountId}&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    messageDiv.textContent = "Account updated successfully!";
                    messageDiv.classList.add('text-green-600');

                    // Disable the input fields
                    nameInput.disabled = true;
                    emailInput.disabled = true;

                    // Show edit button again
                    if (editBtn) editBtn.classList.remove('hidden');
                    if (saveBtn) saveBtn.classList.add('hidden');

                    // Hide message after 3 seconds
                    setTimeout(() => {
                        messageDiv.classList.add('hidden');
                    }, 3000);
                } else {
                    throw new Error(data.message || 'Unknown error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.textContent = "Error saving. Please try again.";
                messageDiv.classList.add('text-red-600');
            });
    }

    // Function to create a new account
    function addAccount(prefix, departmentId) {
        const nameInput = document.getElementById(prefix + '-name');
        const emailInput = document.getElementById(prefix + '-email');
        const messageDiv = document.getElementById(prefix + '-message');
        const addBtn = document.getElementById(prefix + '-add-btn');

        if (!nameInput || !emailInput || !messageDiv) return;

        const name = nameInput.value.trim();
        const email = emailInput.value.trim();

        // Validate inputs
        if (!name || !email) {
            messageDiv.textContent = "Please fill in all fields.";
            messageDiv.classList.remove('hidden', 'text-green-600');
            messageDiv.classList.add('text-red-600');
            return;
        }

        // Show loading state
        messageDiv.textContent = "Creating account...";
        messageDiv.classList.remove('hidden', 'text-green-600', 'text-red-600');
        if (addBtn) addBtn.disabled = true;

        // Send AJAX request
        fetch('MIS_Accounts.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `add_mis_account=1&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&department_id=${departmentId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    messageDiv.textContent = "Account added successfully!";
                    messageDiv.classList.add('text-green-600');

                    // Instead of reloading, we'll transform the form into an account display
                    setTimeout(() => {
                        // Get the container
                        const container = document.getElementById(prefix + '-container');
                        
                        // Create the new HTML structure
                        container.innerHTML = `
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                                <input type="text" id="${prefix}-name" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none" disabled value="${name}">
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                                <input type="email" id="${prefix}-email" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none" disabled value="${email}">
                            </div>
                            <div class="flex justify-end">
                                <button onclick="enableAccountEdit('${prefix}')" id="${prefix}-edit-btn" class="edit-btn px-3 py-1 lg:px-4 lg:py-2 border border-orange-500 text-orange-500 rounded-lg hover:bg-orange-500 hover:text-white flex items-center gap-2 text-sm">
                                    <i class="fas fa-pen"></i> <span class="hidden sm:inline">Edit</span>
                                </button>
                                <button onclick="saveAccount('${prefix}', ${data.id})" id="${prefix}-save-btn" class="edit-btn px-3 py-1 lg:px-4 lg:py-2 border border-green-500 text-green-500 rounded-lg hover:bg-green-500 hover:text-white hidden flex items-center gap-2 ml-2 text-sm">
                                    <i class="fas fa-save"></i> <span class="hidden sm:inline">Save</span>
                                </button>
                            </div>
                            <div id="${prefix}-message" class="mt-3 hidden"></div>
                        `;
                        
                        // Change the icon from user-plus to user
                        const iconContainer = container.parentElement.querySelector('.rounded-full i');
                        if (iconContainer) {
                            iconContainer.classList.remove('fa-user-plus');
                            iconContainer.classList.add('fa-user');
                        }
                        
                        // Hide the success message
                        messageDiv.classList.add('hidden');
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Unknown error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.textContent = "Error creating account. Please try again.";
                messageDiv.classList.add('text-red-600');
                if (addBtn) addBtn.disabled = false;
            });
    }

    // Add CSS styles for inputs
    document.addEventListener('DOMContentLoaded', function() {
        const style = document.createElement('style');
        style.textContent = `
        input:disabled {
            background-color: #f3f4f6;
            cursor: not-allowed;
            opacity: 0.7;
        }
        input:focus:not(:disabled) {
            border-color: #ea580c;
            box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.2);
        }
        input::placeholder {
            color: #9ca3af;
        }
    `;
        document.head.appendChild(style);
    });
</script>