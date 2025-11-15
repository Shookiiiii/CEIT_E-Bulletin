<?php
include "CEIT_config.php";
include "../../db.php";
session_start();

if (!isset($_SESSION['user_info']['name']) || !isset($_SESSION['user_info']['department_id'])) {
  header("Location: ../../login.php");
  exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>College MIS Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
      animation: fadeIn 0.3s ease-in-out;
    }
    .nav-btn.active,
    .upload-tab-btn.active {
      background-color: #ea580c;
      font-weight: bold;
    }

    textarea {
      resize: vertical;
      min-height: 250px;
      transition: all 0.3s ease;
    }

    textarea:disabled {
      background-color: #f3f4f6;
      cursor: not-allowed;
      opacity: 0.7;
    }

    textarea:focus:not(:disabled) {
      border-color: #ea580c;
      box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.2);
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .sidebar-item {
      transition: all 0.2s ease;
    }

    .sidebar-item:hover {
      transform: translateX(5px);
    }

    .content-card {
      transition: all 0.3s ease;
    }

    .content-card:hover {
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .edit-btn {
      transition: all 0.2s ease;
    }

    .edit-btn:hover {
      transform: scale(1.05);
    }

    /* Responsive styles */
    @media (max-width: 1024px) {
      .sidebar {
        position: fixed;
        z-index: 50;
        height: 100vh;
        transform: translateX(-100%);
        transition: transform 0.3s ease-in-out;
      }

      .sidebar.open {
        transform: translateX(0);
      }

      .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 40;
        display: none;
      }

      .sidebar-overlay.show {
        display: block;
      }
    }

    @media (max-width: 768px) {
      .main-tab-btn {
        flex-direction: column;
        padding: 12px 8px;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
      }

      .main-tab-btn i {
        margin-right: 0;
        margin-bottom: 4px;
      }

      .sidebar-item {
        padding: 12px 16px;
      }

      .sidebar-item i {
        margin-right: 12px;
      }

      .content-card {
        margin-bottom: 16px;
      }

      textarea {
        min-height: 80px;
      }
    }

    @media (max-width: 480px) {
      h1 {
        font-size: 1.5rem;
      }

      .edit-btn {
        padding: 8px 12px;
        font-size: 0.875rem;
      }

      .sidebar-item span {
        display: none;
      }

      .sidebar-item i {
        margin-right: 0;
      }

      .sidebar-item {
        justify-content: center;
      }
    }
  </style>
</head>

<body class="bg-gray-50 min-h-screen font-sans">
  <!-- Mobile Menu Button -->
  <button id="mobileMenuBtn" class="lg:hidden fixed top-4 left-4 z-50 w-12 h-12 rounded-full bg-orange-600 text-white flex items-center justify-center shadow-lg">
    <i class="fas fa-bars text-xl"></i>
  </button>

  <!-- Sidebar Overlay for Mobile -->
  <div id="sidebarOverlay" class="sidebar-overlay"></div>

  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar w-64 bg-gradient-to-b from-orange-500 to-orange-600 text-white p-4 flex flex-col justify-between shadow-xl">
      <div>
        <div class="flex items-center justify-center mb-4">
          <img src="images/cvsulogo.png" alt="School Logo" class="w-16 h-16 object-contain drop-shadow-[0_10px_10px_rgba(0,0,0,0.3)]" />
          <div class="ml-3">
            <h2 class="text-lg font-bold">CVSU CEIT</h2>
            <p class="text-xs opacity-80">Management System</p>
          </div>
        </div>
        <nav class="flex space-x-2 mb-2">
          <button id="manageBtn" class="main-tab-btn flex-1 text-center py-3 px-2 hover:bg-orange-700 rounded-lg font-bold transition duration-200 flex items-center justify-center active">
            <i class="fas fa-edit mr-2"></i> <span class="hidden sm:inline">Manage Posts</span>
          </button>
          <button id="uploadBtn" class="main-tab-btn flex-1 text-center py-3 px-2 hover:bg-orange-700 rounded-lg transition duration-200 flex items-center justify-center">
            <i class="fas fa-upload mr-2"></i> <span class="hidden sm:inline">Upload</span>
          </button>
        </nav>
        <div id="manageSubmenu" class="mt-2 space-y-1">
          <button data-tab="DAFE" class="nav-btn sidebar-item w-full text-left p-2 text-sm hover:bg-orange-700 rounded-lg transition duration-200 flex items-center active">
            <i class="fas fa-seedling mr-3 w-5"></i> <span>Agriculture & Food Engineering</span>
          </button>
          <button data-tab="DCEA" class="nav-btn sidebar-item w-full text-left p-2 text-sm hover:bg-orange-700 rounded-lg transition duration-200 flex items-center">
            <i class="fas fa-building mr-3 w-5"></i> <span>Civil Engineering & Architecture</span>
          </button>
          <button data-tab="DCEEE" class="nav-btn sidebar-item w-full text-left p-2 text-sm hover:bg-orange-700 rounded-lg transition duration-200 flex items-center">
            <i class="fas fa-microchip mr-3 w-5"></i> <span>Computer & Electronics Engineering</span>
          </button>
          <button data-tab="DIET" class="nav-btn sidebar-item w-full text-left p-2 text-sm hover:bg-orange-700 rounded-lg transition duration-200 flex items-center">
            <i class="fas fa-industry mr-3 w-5"></i> <span>Industrial Engineering & Technology</span>
          </button>
          <button data-tab="DIT" class="nav-btn sidebar-item w-full text-left p-2 text-sm hover:bg-orange-700 rounded-lg transition duration-200 flex items-center">
            <i class="fas fa-laptop-code mr-3 w-5"></i> <span>Information Technology</span>
          </button>
          <button data-tab="archive" class="nav-btn sidebar-item w-full text-left p-2 text-sm hover:bg-orange-700 rounded-lg transition duration-200 flex items-center">
            <i class="fas fa-archive mr-3 w-5"></i> <span>Archive Section</span>
          </button>
          <button data-tab="accounts" class="nav-btn sidebar-item w-full text-left p-2 text-sm hover:bg-orange-700 rounded-lg transition duration-200 flex items-center">
            <i class="fas fa-user-cog mr-3 w-5"></i> <span>MIS Officer Accounts</span>
          </button>
        </div>
        <div id="uploadSubmenu" class="mt-4 space-y-1 hidden">
          <button data-tab="upload-announcements" class="upload-tab-btn sidebar-item w-full text-left p-3 text-sm hover:bg-orange-700 rounded-lg transition duration-200 flex items-center">
            <i class="fas fa-bullhorn mr-3 w-5"></i> <span>Announcements</span>
          </button>
          <button data-tab="upload-graphs" class="upload-tab-btn sidebar-item w-full text-left p-3 text-sm hover:bg-orange-700 rounded-lg transition duration-200 flex items-center">
            <i class="fas fa-chart-pie mr-3 w-5"></i> <span>Graphs</span>
          </button>
          <button data-tab="upload-calendar" class="upload-tab-btn sidebar-item w-full text-left p-3 text-sm hover:bg-orange-700 rounded-lg transition duration-200 flex items-center">
            <i class="fas fa-calendar-alt mr-3 w-5"></i> <span>University Calendar</span>
          </button>
          <button data-tab="upload-chart" class="upload-tab-btn sidebar-item w-full text-left p-3 text-sm hover:bg-orange-700 rounded-lg transition duration-200 flex items-center">
            <i class="fas fa-sitemap mr-3 w-5"></i> <span>Organizational Chart</span>
          </button>
          <button data-tab="upload-status" class="upload-tab-btn sidebar-item w-full text-left p-3 text-sm hover:bg-orange-700 rounded-lg transition duration-200 flex items-center">
            <i class="fas fa-info-circle mr-3 w-5"></i> <span>Accreditation Status</span>
          </button>
          <button data-tab="upload-about" class="upload-tab-btn sidebar-item w-full text-left p-3 text-sm hover:bg-orange-700 rounded-lg transition duration-200 flex items-center">
            <i class="fas fa-info-circle mr-3 w-5"></i> <span>About CEIT</span>
          </button>
        </div>
      </div>
      <div class="mt-6 p-3 bg-orange-700 rounded-lg">
        <div class="flex items-center">
          <div class="w-10 h-10 rounded-full bg-orange-800 flex items-center justify-center">
            <i class="fas fa-user-tie text-white"></i>
          </div>
          <div class="ml-3">
            <p class="font-medium text-sm"><?php echo $_SESSION['user_info']['name']; ?></p>
            <p class="text-xs opacity-80">CEIT MIS Officer</p>
          </div>
        </div>
        
        <!-- Logout Button -->
        <form action="../../logout.php" method="POST" class="mt-3">
          <button type="submit" class="w-full py-2 px-4 bg-orange-800 hover:bg-orange-900 text-white rounded-lg transition duration-200 flex items-center justify-center">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
          </button>
        </form>
      </div>
    </aside>
    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto p-4 lg:p-8 bg-gradient-to-br from-gray-50 to-gray-100">
      <div class="max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 lg:mb-8">
          <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-800" id="pageTitle">
              Manage Department Posts
            </h1>
            <p class="text-gray-600 mt-1 text-sm lg:text-base">College of Engineering and Information Technology</p>
          </div>
          <div class="text-center md:text-right">
            <div class="text-sm text-gray-500"><?php echo date('l, F d, Y'); ?></div>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-4 lg:p-6 mb-6 lg:mb-8">
          <div id="manageContent">
            <div id="DAFE" class="tab-content active"><?php include '../DAFE/DAFEposts.php' ?></div>
            <div id="DCEA" class="tab-content"><?php include '../DCEA/DCEAposts.php' ?></div>
            <div id="DCEEE" class="tab-content"><?php include '../DCEEE/DCEEEposts.php' ?></div>
            <div id="DIET" class="tab-content">Wala muna to</div>
            <div id="DIT" class="tab-content"><?php include '../DIT/DITposts.php' ?></div>
            <div id="archive" class="tab-content"><?php include 'archive.php'; ?></div>
            <div id="accounts" class="tab-content"><?php include 'MIS_Accounts.php'; ?></div>
          </div>
          <div id="uploadContent" class="hidden">
            <div id="upload-announcements" class="tab-content"><?php include 'announcement.php'; ?></div>
            <div id="upload-graphs" class="tab-content"><?php include 'Graph.php'; ?></div>
            <div id="upload-enrollment" class="tab-content"><?php include 'Graph2.php'; ?></div>
            <div id="upload-licensure" class="tab-content"><?php include 'Graph3.php'; ?></div>
            <div id="upload-memos" class="tab-content"><?php include 'memo.php'; ?></div>
            <div id="upload-calendar" class="tab-content"><?php include 'CEIT_calendar.php'; ?></div>
            <div id="upload-chart" class="tab-content"><?php include '../OrganizationalChart/CEIT_OrgChart.php'; ?></div>
            <div id="upload-status" class="tab-content"><?php include 'AccreditationStatus.php'; ?></div>
            <div id="upload-about" class="tab-content">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                <!-- About College Section -->
                <div class="content-card bg-gray-50 p-4 lg:p-6 rounded-xl shadow">
                  <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center mr-3">
                      <i class="fas fa-university text-orange-600"></i>
                    </div>
                    <h3 class="text-lg lg:text-xl font-semibold text-gray-800">About College</h3>
                  </div>
                  <div id="ac-container">
                    <textarea
                      id="ac-textarea"
                      class="w-full border border-gray-300 rounded-lg p-4 focus:outline-none"
                      rows="6"
                      disabled><?php echo htmlspecialchars(about_college($conn)['content'] ?? ''); ?></textarea>
                    <div class="flex justify-end mt-3">
                      <button
                        onclick="enableACEdit()"
                        id="ac-edit-btn"
                        class="edit-btn px-3 py-1 lg:px-4 lg:py-2 border border-orange-500 text-orange-500 rounded-lg hover:bg-orange-500 hover:text-white flex items-center gap-2 text-sm">
                        <i class="fas fa-pen"></i> <span class="hidden sm:inline">Edit</span>
                      </button>
                      <button
                        onclick="saveAboutCollege()"
                        id="ac-save-btn"
                        class="edit-btn px-3 py-1 lg:px-4 lg:py-2 border border-green-500 text-green-500 rounded-lg hover:bg-green-500 hover:text-white hidden flex items-center gap-2 ml-2 text-sm">
                        <i class="fas fa-save"></i> <span class="hidden sm:inline">Save</span>
                      </button>
                    </div>
                    <div id="ac-message" class="mt-3 hidden"></div>
                  </div>
                </div>
                <!-- Mission Section -->
                <div class="content-card bg-gray-50 p-4 lg:p-6 rounded-xl shadow">
                  <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                      <i class="fas fa-bullseye text-blue-600"></i>
                    </div>
                    <h3 class="text-lg lg:text-xl font-semibold text-gray-800">Mission</h3>
                  </div>
                  <div id="mission-container">
                    <textarea
                      id="mission-textarea"
                      class="w-full border border-gray-300 rounded-lg p-4 focus:outline-none"
                      rows="4"
                      disabled><?php echo htmlspecialchars(mission($conn)['content'] ?? ''); ?></textarea>
                    <div class="flex justify-end mt-3">
                      <button
                        onclick="enableMissionEdit()"
                        id="mission-edit-btn"
                        class="edit-btn px-3 py-1 lg:px-4 lg:py-2 border border-orange-500 text-orange-500 rounded-lg hover:bg-orange-500 hover:text-white flex items-center gap-2 text-sm">
                        <i class="fas fa-pen"></i> <span class="hidden sm:inline">Edit</span>
                      </button>
                      <button
                        onclick="saveMission()"
                        id="mission-save-btn"
                        class="edit-btn px-3 py-1 lg:px-4 lg:py-2 border border-green-500 text-green-500 rounded-lg hover:bg-green-500 hover:text-white hidden flex items-center gap-2 ml-2 text-sm">
                        <i class="fas fa-save"></i> <span class="hidden sm:inline">Save</span>
                      </button>
                    </div>
                    <div id="mission-message" class="mt-3 hidden"></div>
                  </div>
                </div>
                <!-- Vision Section -->
                <div class="content-card bg-gray-50 p-4 lg:p-6 rounded-xl shadow">
                  <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center mr-3">
                      <i class="fas fa-eye text-purple-600"></i>
                    </div>
                    <h3 class="text-lg lg:text-xl font-semibold text-gray-800">Vision</h3>
                  </div>
                  <div id="vision-container">
                    <textarea
                      id="vision-textarea"
                      class="w-full border border-gray-300 rounded-lg p-4 focus:outline-none"
                      rows="4"
                      disabled><?php echo htmlspecialchars(vision($conn)['content'] ?? ''); ?></textarea>
                    <div class="flex justify-end mt-3">
                      <button
                        onclick="enableVisionEdit()"
                        id="vision-edit-btn"
                        class="edit-btn px-3 py-1 lg:px-4 lg:py-2 border border-orange-500 text-orange-500 rounded-lg hover:bg-orange-500 hover:text-white flex items-center gap-2 text-sm">
                        <i class="fas fa-pen"></i> <span class="hidden sm:inline">Edit</span>
                      </button>
                      <button
                        onclick="saveVision()"
                        id="vision-save-btn"
                        class="edit-btn px-3 py-1 lg:px-4 lg:py-2 border border-green-500 text-green-500 rounded-lg hover:bg-green-500 hover:text-white hidden flex items-center gap-2 ml-2 text-sm">
                        <i class="fas fa-save"></i> <span class="hidden sm:inline">Save</span>
                      </button>
                    </div>
                    <div id="vision-message" class="mt-3 hidden"></div>
                  </div>
                </div>
                <!-- Quality Policy Section -->
                <div class="content-card bg-gray-50 p-4 lg:p-6 rounded-xl shadow">
                  <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                      <i class="fas fa-certificate text-green-600"></i>
                    </div>
                    <h3 class="text-lg lg:text-xl font-semibold text-gray-800">Quality Policy</h3>
                  </div>
                  <div id="qp-container">
                    <textarea
                      id="qp-textarea"
                      class="w-full border border-gray-300 rounded-lg p-4 focus:outline-none"
                      rows="4"
                      disabled><?php echo htmlspecialchars(quality_policy($conn)['content'] ?? ''); ?></textarea>
                    <div class="flex justify-end mt-3">
                      <button
                        onclick="enableQPEdit()"
                        id="qp-edit-btn"
                        class="edit-btn px-3 py-1 lg:px-4 lg:py-2 border border-orange-500 text-orange-500 rounded-lg hover:bg-orange-500 hover:text-white flex items-center gap-2 text-sm">
                        <i class="fas fa-pen"></i> <span class="hidden sm:inline">Edit</span>
                      </button>
                      <button
                        onclick="saveQualityPolicy()"
                        id="qp-save-btn"
                        class="edit-btn px-3 py-1 lg:px-4 lg:py-2 border border-green-500 text-green-500 rounded-lg hover:bg-green-500 hover:text-white hidden flex items-center gap-2 ml-2 text-sm">
                        <i class="fas fa-save"></i> <span class="hidden sm:inline">Save</span>
                      </button>
                    </div>
                    <div id="qp-message" class="mt-3 hidden"></div>
                  </div>
                </div>
                <!-- College Goals -->
                <div class="content-card bg-gray-50 p-4 lg:p-6 rounded-xl shadow">
                  <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center mr-3">
                      <i class="fas fa-trophy text-yellow-600"></i>
                    </div>
                    <h3 class="text-lg lg:text-xl font-semibold text-gray-800">College Goals</h3>
                  </div>
                  <div id="cg-container">
                    <textarea
                      id="cg-textarea"
                      class="w-full border border-gray-300 rounded-lg p-4 focus:outline-none"
                      rows="6"
                      disabled><?php echo htmlspecialchars(college_goals($conn)['content'] ?? ''); ?></textarea>
                    <div class="flex justify-end mt-3">
                      <button
                        onclick="enableCGEdit()"
                        id="cg-edit-btn"
                        class="edit-btn px-3 py-1 lg:px-4 lg:py-2 border border-orange-500 text-orange-500 rounded-lg hover:bg-orange-500 hover:text-white flex items-center gap-2 text-sm">
                        <i class="fas fa-pen"></i> <span class="hidden sm:inline">Edit</span>
                      </button>
                      <button
                        onclick="saveCollegeGoals()"
                        id="cg-save-btn"
                        class="edit-btn px-3 py-1 lg:px-4 lg:py-2 border border-green-500 text-green-500 rounded-lg hover:bg-green-500 hover:text-white hidden flex items-center gap-2 ml-2 text-sm">
                        <i class="fas fa-save"></i> <span class="hidden sm:inline">Save</span>
                      </button>
                    </div>
                    <div id="cg-message" class="mt-3 hidden"></div>
                  </div>
                </div>
                <!-- Program Offerings -->
                <div class="content-card bg-gray-50 p-4 lg:p-6 rounded-xl shadow">
                  <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-3">
                      <i class="fas fa-graduation-cap text-red-600"></i>
                    </div>
                    <h3 class="text-lg lg:text-xl font-semibold text-gray-800">Program Offerings</h3>
                  </div>
                  <div id="po-container">
                    <textarea
                      id="po-textarea"
                      class="w-full border border-gray-300 rounded-lg p-4 focus:outline-none"
                      rows="8"
                      disabled><?php echo htmlspecialchars(program_offerings($conn)['content'] ?? ''); ?></textarea>
                    <div class="flex justify-end mt-3">
                      <button
                        onclick="enablePOEdit()"
                        id="po-edit-btn"
                        class="edit-btn px-3 py-1 lg:px-4 lg:py-2 border border-orange-500 text-orange-500 rounded-lg hover:bg-orange-500 hover:text-white flex items-center gap-2 text-sm">
                        <i class="fas fa-pen"></i> <span class="hidden sm:inline">Edit</span>
                      </button>
                      <button
                        onclick="saveProgramOfferings()"
                        id="po-save-btn"
                        class="edit-btn px-3 py-1 lg:px-4 lg:py-2 border border-green-500 text-green-500 rounded-lg hover:bg-green-500 hover:text-white hidden flex items-center gap-2 ml-2 text-sm">
                        <i class="fas fa-save"></i> <span class="hidden sm:inline">Save</span>
                      </button>
                    </div>
                    <div id="po-message" class="mt-3 hidden"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
  <script src="CEIT.js"></script>
  <script>
    // Mobile menu toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
      console.log('DOM fully loaded');

      const mobileMenuBtn = document.getElementById('mobileMenuBtn');
      const sidebar = document.getElementById('sidebar');
      const sidebarOverlay = document.getElementById('sidebarOverlay');

      // Toggle sidebar on mobile
      mobileMenuBtn.addEventListener('click', function() {
        sidebar.classList.toggle('open');
        sidebarOverlay.classList.toggle('show');
      });

      // Close sidebar when clicking on overlay
      sidebarOverlay.addEventListener('click', function() {
        sidebar.classList.remove('open');
        sidebarOverlay.classList.remove('show');
      });

      // Close sidebar when a menu item is clicked (on mobile)
      const sidebarItems = document.querySelectorAll('.nav-btn, .upload-tab-btn');
      sidebarItems.forEach(item => {
        item.addEventListener('click', function() {
          if (window.innerWidth <= 1024) {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('show');
          }
        });
      });

      // Add a small delay to ensure all included scripts are ready
      setTimeout(function() {
        console.log('Initializing tabs');
        // Trigger a click on the default tab if none is active
        if (!document.querySelector('.nav-btn.active') && !document.querySelector('.upload-tab-btn.active')) {
          document.getElementById('manageBtn').click();
        }
      }, 100);
    });
  </script>
</body>

</html>