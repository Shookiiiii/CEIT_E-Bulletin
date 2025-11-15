<?php
include "DAFE_config.php";
include "../../db.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Department of Agriculture and Food Engineering</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    .upload-tab-btn.active {
      background-color: #ea580c;
      font-weight: bold;
    }
  </style>
</head>

<body class="bg-gray-100 min-h-screen font-sans">
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="w-64 bg-orange-400 text-white p-4 flex flex-col justify-between">
      <div>
        <img src="images/cvsulogo.png" alt="School Logo" class="w-24 mx-auto mb-6 drop-shadow-[0_10px_10px_rgba(0,0,0,0.5)]" />
        <hr class="my-4 border-t-2 border-orange-700" />
        <div class="mt-6 space-y-2">
          <button data-tab="upload-announcements" class="upload-tab-btn w-full text-left p-2 hover:bg-orange-500 rounded transition duration-200 transform hover:scale-110 active">
            Announcements
          </button>
          <button data-tab="upload-graphs" class="upload-tab-btn w-full text-left p-2 hover:bg-orange-500 rounded transition duration-200 transform hover:scale-110">
            Graphs
          </button>
          <button data-tab="upload-memos" class="upload-tab-btn w-full text-left p-2 hover:bg-orange-500 rounded transition duration-200 transform hover:scale-110">
            Memo Updates
          </button>
          <button data-tab="upload-calendar" class="upload-tab-btn w-full text-left p-2 hover:bg-orange-500 rounded transition duration-200 transform hover:scale-110">
            University Calendar
          </button>
          <button data-tab="upload-chart" class="upload-tab-btn w-full text-left p-2 hover:bg-orange-500 rounded transition duration-200 transform hover:scale-110">
            Organizational Chart
          </button>
          <button data-tab="upload-about" class="upload-tab-btn w-full text-left p-2 hover:bg-orange-500 rounded transition duration-200 transform hover:scale-110">
            About DAFE
          </button>
        </div>
      </div>
      <div class="mt-6 text-right text-xl">
        <p>Welcome,<br />DAFE MIS Officer!</p>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto p-8 bg-white shadow-inner h-screen">
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-3xl font-bold text-orange-600" id="pageTitle">
          Upload to DAFE Bulletin
        </h1>
      </div>
      <hr class="my-8 border-t-2 border-gray-300" />

      <div id="uploadContent">
        <div id="upload-announcements" class="tab-content active">
          <p>Announcements upload form will be here</p>
        </div>
        <div id="upload-graphs" class="tab-content">
          <p>Graphs upload form will be here</p>
        </div>
        <div id="upload-memos" class="tab-content">
          <p>Memo upload form will be here</p>
        </div>
        <div id="upload-calendar" class="tab-content">
          <p>Calendar upload form will be here</p>
        </div>
        <div id="upload-chart" class="tab-content">
          <p>Organizational chart upload form will be here</p>
        </div>
        <div id="upload-about" class="tab-content">
          <p>About DAFE content will be here</p>
        </div>
      </div>
    </main>
  </div>

  <script src="DAFE.js"></script>
</body>

</html>