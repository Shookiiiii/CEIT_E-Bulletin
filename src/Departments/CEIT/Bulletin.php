<?php
include 'Bulletin_Config.php';
include "../../db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CEIT Bulletin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --base-font-size: 16px;
    }
    html,
    body {
      height: 100%;
      margin: 0;
      overflow: hidden;
      font-size: var(--base-font-size);
    }
    /* Responsive font sizing */
    @media (max-width: 1280px) {
      :root {
        --base-font-size: 15px;
      }
    }
    @media (max-width: 1024px) {
      :root {
        --base-font-size: 14px;
      }
    }
    @media (max-width: 768px) {
      :root {
        --base-font-size: 13px;
      }
    }
    @media (max-width: 640px) {
      :root {
        --base-font-size: 12px;
      }
    }
    .carousel {
      position: relative;
      width: 100%;
      height: 100%;
      overflow: hidden;
    }
    .carousel-item {
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      transition: all 0.5s ease-in-out;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    .carousel-item:not(.active) {
      transform: translateY(20px) scale(0.95);
      opacity: 0;
      z-index: 0;
    }
    .carousel-item.active {
      transform: translateY(0) scale(1);
      opacity: 1;
      z-index: 1;
    }
    .content-area {
      height: calc(100% - 2rem);
      overflow: auto;
    }
    .page {
      display: none;
      height: 100%;
      flex-direction: column;
    }
    .page.active {
      display: flex;
    }
    /* Enhanced Marquee */
    .marquee-wrapper {
      display: inline-block;
      white-space: nowrap;
      animation: marquee 30s linear infinite;
    }
    .marquee-content {
      display: inline-block;
      background: linear-gradient(90deg, #ea580c, #f97316, #ea580c);
      -webkit-background-clip: text;
      background-clip: text;
      -webkit-text-fill-color: transparent;
      font-weight: 800;
      letter-spacing: 1px;
    }
    @keyframes marquee {
      0% {
        transform: translateX(0);
      }
      100% {
        transform: translateX(-50%);
      }
    }
    /* Responsive adjustments for different aspect ratios */
    @media (max-aspect-ratio: 4/3) {
      .main-grid {
        grid-template-columns: 1fr !important;
      }
      .page2-grid {
        grid-template-columns: 1fr !important;
      }
    }
    /* Make background images responsive */
    .responsive-bg {
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }
    /* Responsive text sizes */
    .header-title {
      font-size: clamp(1rem, 2.5vw, 1.5rem);
    }
    .header-subtitle {
      font-size: clamp(0.8rem, 2vw, 1.2rem);
    }
    .date-time {
      font-size: clamp(0.9rem, 2.2vw, 1.3rem);
    }
    .tenets-text {
      font-size: clamp(0.8rem, 1.8vw, 1.2rem);
    }
    .card-header {
      font-size: clamp(0.9rem, 2vw, 1.1rem);
    }
    .carousel-text {
      font-size: clamp(0.6rem, 1.5vw, 0.8rem);
    }
    /* Announcement Modal Styles */
    .announcement-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.8);
      z-index: 1000;
      backdrop-filter: blur(5px);
    }
    .modal-content {
      position: relative;
      background-color: white;
      margin: 2% auto;
      padding: 0;
      width: 90%;
      max-width: 1200px;
      max-height: 90vh;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }
    .modal-header {
      padding: 15px 20px;
      background-color: #f97316;
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .modal-title {
      font-size: 1.5rem;
      font-weight: 600;
    }
    .modal-close {
      font-size: 2rem;
      cursor: pointer;
      transition: transform 0.2s;
    }
    .modal-close:hover {
      transform: scale(1.2);
    }
    .modal-body {
      padding: 20px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }
    .pdf-container {
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      position: relative;
    }
    .pdf-page {
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      max-width: 100%;
      max-height: 100%;
    }
    .modal-footer {
      padding: 15px 20px;
      background-color: #f3f4f6;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .modal-meta {
      font-size: 0.9rem;
      color: #6b7280;
    }
    .page-navigation {
      display: flex;
      align-items: center;
      gap: 15px;
    }
    .page-nav-btn {
      background-color: #f97316;
      color: white;
      border: none;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: background-color 0.2s, transform 0.2s;
    }
    .page-nav-btn:hover {
      background-color: #ea580c;
      transform: scale(1.1);
    }
    .page-nav-btn:disabled {
      background-color: #d1d5db;
      cursor: not-allowed;
      transform: scale(1);
    }
    .page-indicator {
      font-weight: 600;
      color: #4b5563;
      min-width: 80px;
      text-align: center;
    }
    .loading-spinner {
      border: 4px solid rgba(0, 0, 0, 0.1);
      border-radius: 50%;
      border-top: 4px solid #f97316;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
      margin: 20px auto;
    }
    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }
      100% {
        transform: rotate(360deg);
      }
    }
    /* Announcement section styling */
    .announcement-section {
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    .announcement-carousel {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }
    /* Modal styles for different file types */
    .image-container {
      display: flex;
      justify-content: center;
      align-items: center;
      width: 100%;
    }
    .office-viewer {
      border: 1px solid #e5e7eb;
      border-radius: 0.375rem;
      overflow: hidden;
      width: 100%;
      height: 80vh;
    }
    .org-chart-content {
      position: relative;
      z-index: 2;
      padding: 1.5rem;
      height: 100%;
      min-height: 500px;
      display: flex;
      flex-direction: column;
    }
    
    /* Mobile-specific responsive improvements */
    @media (max-width: 768px) {
      /* Adjust modal for mobile */
      .modal-content {
        width: 95%;
        margin: 5% auto;
        max-height: 90vh;
      }
      
      .modal-header {
        padding: 12px 15px;
      }
      
      .modal-title {
        font-size: 1.2rem;
      }
      
      .modal-body {
        padding: 15px;
      }
      
      .modal-footer {
        padding: 12px 15px;
        flex-direction: column;
        gap: 10px;
      }
      
      .page-nav-btn {
        width: 36px;
        height: 36px;
      }
      
      /* Make buttons larger for touch */
      .btn {
        min-height: 44px; /* Minimum touch target size */
        padding: 10px 15px;
      }
      
      /* Adjust carousels for mobile */
      .carousel-text {
        font-size: 0.7rem;
        padding: 8px !important;
      }
      
      /* Make sure all content is visible */
      .content-area {
        height: calc(100% - 1.5rem);
      }
    }
    
    @media (max-width: 480px) {
      /* Further adjustments for very small screens */
      .modal-title {
        font-size: 1rem;
      }
      
      .modal-meta {
        font-size: 0.8rem;
      }
      
      .page-indicator {
        font-size: 0.9rem;
        min-width: 70px;
      }
      
      /* Adjust header for small screens */
      .header-title {
        font-size: 1rem;
      }
      
      .header-subtitle {
        font-size: 0.8rem;
      }
      
      .date-time {
        font-size: 0.8rem;
      }
      
      /* Make navigation buttons larger for easier touch */
      .page-nav-btn {
        width: 44px;
        height: 44px;
      }
    }
    
    /* Landscape orientation adjustments */
    @media (max-height: 600px) and (orientation: landscape) {
      /* Adjust for short screens in landscape */
      .modal-content {
        max-height: 95vh;
      }
      
      .modal-body {
        max-height: 70vh;
      }
      
      .office-viewer {
        height: 60vh;
      }
    }
  </style>
</head>
<body class="bg-orange-500 p-1 sm:p-2 font-sans h-full flex flex-col">
  <!-- Header -->
  <div class="flex justify-between items-center bg-gradient-to-r from-orange-700 to-orange-800 p-2 sm:p-3 rounded-xl mb-1 sm:mb-2 text-white shadow-xl fade-in">
    <div class="text-left flex items-center">
      <div class="bg-white/20 p-1 sm:p-2 md:p-3 rounded-full mr-1 sm:mr-2 md:mr-4 pulse">
        <i class="fas fa-university text-sm sm:text-lg md:text-2xl"></i>
      </div>
      <div>
        <div class="header-title font-bold">Cavite State University</div>
        <div class="header-subtitle text-orange-200">College of Engineering and Information Technology</div>
      </div>
    </div>
    <div class="text-right">
      <div id="date" class="date-time font-semibold"></div>
      <div id="time" class="date-time font-semibold"></div>
    </div>
  </div>
  
  <!-- Tenets -->
  <div class="bg-white/90 backdrop-blur-sm text-center font-bold text-black py-1 sm:py-2 md:py-3 mb-1 sm:mb-2 rounded-xl overflow-hidden relative h-8 sm:h-12 md:h-16 flex items-center shadow-lg slide-up">
    <div class="marquee-wrapper whitespace-nowrap">
      <div class="marquee-content inline-block tenets-text">
        TRUTH • EXCELLENCE • SERVICE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        ENGINEERING INNOVATION • DIGITAL TRANSFORMATION • TECH EXCELLENCE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      </div>
      <div class="marquee-content inline-block tenets-text">
        TRUTH • EXCELLENCE • SERVICE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        ENGINEERING INNOVATION • DIGITAL TRANSFORMATION • TECH EXCELLENCE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      </div>
    </div>
  </div>
  
  <!-- Page 1 -->
  <div id="page1" class="page active">
    <main class="grid grid-cols-1 lg:grid-cols-3 gap-1 sm:gap-2 p-1 font-bold text-center flex-grow main-grid">
      <!-- Mandates-->
      <div class="flex flex-col space-y-1 sm:space-y-2 h-full">
        <div class="bg-white rounded-lg shadow-md p-1 sm:p-2 flex-1 overflow-auto transition duration-500 transform hover:scale-[1.02]">
          <div class="h-full flex flex-col">
            <div class="card-header mb-1 sm:mb-2 text-orange-600 flex items-center p-1 sm:p-2 border-b">
              <i class="fas fa-landmark mr-1 sm:mr-2"></i> <span class="hidden sm:inline">About</span> CvSU
            </div>
            <div class="carousel mandates-carousel flex-grow relative">
              <div class="carousel-item active h-full">
                <div class="h-full flex items-center justify-center bg-gray-100 responsive-bg" style="background-image: url('images/bg-small.png');">
                  <div class="carousel-text p-1 sm:p-2 md:p-3" style="margin: 5px 10px; background-color: rgba(240, 240, 240, 0.8); padding: 8px; border-radius: 6px; line-height: 1.4;">
                    MISSION <br><br>
                    <?php echo mission($conn)['content']; ?>
                  </div>
                </div>
              </div>
              <div class="carousel-item h-full">
                <div class="h-full flex items-center justify-center bg-gray-100 responsive-bg" style="background-image: url('images/bg-small.png');">
                  <div class="carousel-text p-1 sm:p-2 md:p-3" style="margin: 5px 10px; background-color: rgba(240, 240, 240, 0.8); padding: 8px; border-radius: 6px; line-height: 1.4;">
                    VISION <br><br>
                    <?php echo vision($conn)['content']; ?>
                  </div>
                </div>
              </div>
              <div class="carousel-item h-full">
                <div class="h-full flex items-center justify-center bg-gray-100 responsive-bg" style="background-image: url('images/bg-small.png');">
                  <div class="carousel-text p-1 sm:p-2 md:p-3" style="margin: 5px 10px; background-color: rgba(240, 240, 240, 0.8); padding: 8px; border-radius: 6px; line-height: 1.4;">
                    QUALITY POLICY <br><br>
                    <?php echo quality_policy($conn)['content']; ?>
                  </div>
                </div>
              </div>
              <div class="carousel-item h-full">
                <div class="h-full flex items-center justify-center bg-gray-100 responsive-bg" style="background-image: url('images/bg-small.png');">
                  <div class="carousel-text p-1 sm:p-2 md:p-3" style="margin: 5px 10px; background-color: rgba(240, 240, 240, 0.8); padding: 8px; border-radius: 6px; line-height: 1.4;">
                    COLLEGE GOALS <br><br>
                    <div style="text-align: left; font-size: 8px">
                      <?php echo nl2br(college_goals($conn)['content']); ?>
                    </div>
                  </div>
                </div>
              </div>
              <div class="carousel-item h-full">
                <div class="h-full flex items-center justify-center bg-gray-100 responsive-bg" style="background-image: url('images/bg-small.png');">
                  <div class="carousel-text p-1 sm:p-2 md:p-3" style="margin: 5px 10px; background-color: rgba(240, 240, 240, 0.8); padding: 8px; border-radius: 6px; line-height: 1.4;">
                    PROGRAM OFFERINGS <br><br>
                    <div style="text-align: left; font-size: 10px">
                      <?php echo nl2br(program_offerings($conn)['content']); ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="flex justify-center space-x-1 sm:space-x-2 mt-1 sm:mt-2">
              <button onclick="prevCarousel('mandates-carousel')" class="bg-orange-500 text-white p-1 sm:p-2 rounded-full hover:bg-orange-600 transition duration-200 transform hover:scale-110">
                <svg class="w-3 h-3 sm:w-4 sm:h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="6" d="M15 19l-7-7 7-7" />
                </svg>
              </button>
              <button onclick="nextCarousel('mandates-carousel')" class="bg-orange-500 text-white p-1 sm:p-2 rounded-full hover:bg-orange-600 transition duration-200 transform hover:scale-110">
                <svg class="w-3 h-3 sm:w-4 sm:h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="6" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>
          </div>
        </div>
        <!-- Accreditation -->
        <div class="bg-white rounded-lg shadow-md p-1 sm:p-2 flex-1 overflow-auto transition duration-200 transform hover:scale-[1.02]">
          <div class="h-full flex flex-col">
            <div class="card-header mb-1 sm:mb-2 text-orange-600 flex items-center p-1 sm:p-2 md:p-3 border-b">
              <i class="fas fa-award mr-1 sm:mr-2"></i> <span class="hidden sm:inline">Accreditation</span> Status
            </div>
            <div class="carousel accreditation-carousel flex-grow relative">
              <div class="carousel-item active h-full">
                <div class="h-full flex items-center justify-center bg-gray-100 responsive-bg" style="background-image: url('images/bg-small.png');">
                  <div class="carousel-text p-1 sm:p-2 md:p-3" style="margin: 5px 10px; background-color: rgba(240, 240, 240, 0.8); padding: 8px; border-radius: 6px;">
                    <?php include 'ViewStatus.php' ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Calendar -->
      <div class="bg-white rounded-lg shadow-md p-1 sm:p-2 md:p-4 overflow-auto transition duration-500 transform hover:scale-[1.02]">
        <div class="card-header mb-1 sm:mb-2 text-orange-600 flex items-center border-b pb-1 sm:pb-2">
          <i class="fas fa-calendar-alt mr-1 sm:mr-2"></i> <span class="hidden sm:inline">Academic</span> Calendar
        </div>
        <?php include 'CEIT_CalendarView.php' ?>
      </div>
      
      <!-- Announcement -->
      <div class="bg-white rounded-lg shadow-md p-1 sm:p-2 md:p-4 overflow-auto transition duration-500 transform hover:scale-[1.02] announcement-section">
        <div class="card-header mb-1 sm:mb-2 text-orange-600 flex items-center border-b pb-1 sm:pb-2">
          <i class="fas fa-bullhorn mr-1 sm:mr-2"></i> <span class="hidden sm:inline">Announcements</span>
        </div>
        <div class="announcement-carousel">
          <script>
            // Initialize pdfData array if it doesn't exist
            if (typeof pdfData === 'undefined') {
              var pdfData = [];
            }
          </script>
          <?php include 'AnnouncementBulletin.php' ?>
        </div>
      </div>
    </main>
  </div>
  
  <!-- Page 2  -->
  <div id="page2" class="page">
    <main class="grid grid-cols-1 lg:grid-cols-2 gap-1 sm:gap-2 p-1 font-bold text-center flex-grow page2-grid">
      <!-- Graphs -->
      <div class="bg-white rounded-lg shadow-md p-1 sm:p-2 md:p-4 transition duration-500 transform hover:scale-[1.02]">
        <div class="card-header mb-1 sm:mb-2 text-orange-600 flex items-center border-b pb-1 sm:pb-2">
          <i class="fas fa-chart-pie mr-1 sm:mr-2"></i> Performance Metrics
        </div>
        <div class="content-area">
          <?php include 'graphs_carousel.php' ?>
        </div>
      </div>
      
      <!-- Organizational Structure -->
            <!-- Organizational Structure -->
      <div class="rounded-lg shadow-md p-2 transition duration-500 transform hover:scale-[1.02] relative overflow-hidden" style="background-image: url('images/ceitorgbg.jpg'); background-size: cover; background-position: center;">
        <div class="absolute inset-0 bg-white bg-opacity-20 z-0"></div>
        <div class="relative z-10">
          <div class="card-header mb-2 text-orange-600 flex items-center border-b pb-2">
            <i class="fas fa-sitemap mr-2"></i> CEIT Organizational Structure
          </div>
          <div class="content-area">
              <div class="org-chart-content">
                <?php include '../OrganizationalChart/CEIT_ViewChart.php' ?>
              </div>
          </div>
        </div>
      </div>
    </main>
  </div>
  
  <!-- Page Navigation -->
  <div class="flex justify-between items-center mt-1 sm:mt-2 md:mt-4">
    <div class="flex space-x-2 sm:space-x-3">
      <button id="refreshBtn" class="btn btn-secondary px-2 py-1 sm:px-3 sm:py-1 md:px-4 md:py-2 rounded-xl bg-white hover:bg-orange-800 hover:text-white transition-colors duration-200 flex items-center shadow-md">
        <i class="fas fa-sync-alt mr-1 sm:mr-2"></i> <span class="text-xs sm:text-sm md:text-base">Refresh</span>
      </button>
    </div>
    <button id="togglePageBtn" class="btn btn-primary px-2 py-1 sm:px-3 sm:py-1 md:px-4 md:py-2 rounded-xl bg-white hover:bg-orange-800 hover:text-white transition-colors duration-200 flex items-center shadow-md">
      <i class="fas fa-arrow-right mr-1 sm:mr-2"></i> <span class="text-xs sm:text-sm md:text-base">Go to Page 2</span>
    </button>
  </div>
  
  <!-- Announcement Modal -->
  <div id="announcementModal" class="announcement-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="modalTitle">Announcement</h3>
        <span class="modal-close" onclick="closeAnnouncementModal()">&times;</span>
      </div>
      <div class="modal-body">
        <div id="pdfContainer" class="pdf-container">
          <div class="loading-spinner"></div>
          <p class="text-center text-gray-600">Loading announcement...</p>
        </div>
      </div>
      <div class="modal-footer">
        <div class="modal-meta" id="modalMeta"></div>
        <div class="page-navigation">
          <button id="prevPageBtn" class="page-nav-btn" disabled>
            <i class="fas fa-chevron-left"></i>
          </button>
          <div id="pageIndicator" class="page-indicator">Page 1 of 1</div>
          <button id="nextPageBtn" class="page-nav-btn" disabled>
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Loading Overlay -->
  <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-3 sm:p-4 md:p-6 rounded-lg shadow-lg flex flex-col items-center">
      <i class="fas fa-spinner fa-spin text-xl sm:text-2xl md:text-3xl text-orange-600 mb-2 sm:mb-3"></i>
      <p class="text-sm sm:text-base md:text-lg font-semibold">Refreshing...</p>
    </div>
  </div>
  
  <script>
    // Initialize PDF.js worker
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';
    
    function updateDateTime() {
      const now = new Date();
      const dateOptions = {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
      };
      const timeOptions = {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
      };
      document.getElementById("date").textContent = now.toLocaleDateString(
        "en-PH",
        dateOptions
      );
      document.getElementById("time").textContent = now.toLocaleTimeString(
        "en-PH",
        timeOptions
      );
    }
    setInterval(updateDateTime, 1000);
    updateDateTime();
    
    // Carousel functionality
    function prevCarousel(carouselClass) {
      const carousel = document.querySelector(`.${carouselClass}`);
      const items = carousel.querySelectorAll(".carousel-item");
      let activeIndex = Array.from(items).findIndex((item) =>
        item.classList.contains("active")
      );
      items[activeIndex].classList.remove("active");
      activeIndex = (activeIndex - 1 + items.length) % items.length;
      items[activeIndex].classList.add("active");
    }
    
    function nextCarousel(carouselClass) {
      const carousel = document.querySelector(`.${carouselClass}`);
      const items = carousel.querySelectorAll(".carousel-item");
      let activeIndex = Array.from(items).findIndex((item) =>
        item.classList.contains("active")
      );
      items[activeIndex].classList.remove("active");
      activeIndex = (activeIndex + 1) % items.length;
      items[activeIndex].classList.add("active");
    }
    
    // Page toggle functionality
    const togglePageBtn = document.getElementById("togglePageBtn");
    let currentPage = 1;
    togglePageBtn.addEventListener("click", () => {
      if (currentPage === 1) {
        document.getElementById("page1").classList.remove("active");
        document.getElementById("page2").classList.add("active");
        togglePageBtn.innerHTML = `<i class="fas fa-arrow-left mr-1 sm:mr-2"></i> <span class="text-xs sm:text-sm md:text-base">Go to Page 1</span>`;
        currentPage = 2;
      } else {
        document.getElementById("page2").classList.remove("active");
        document.getElementById("page1").classList.add("active");
        togglePageBtn.innerHTML = `<i class="fas fa-arrow-right mr-1 sm:mr-2"></i> <span class="text-xs sm:text-sm md:text-base">Go to Page 2</span>`;
        currentPage = 1;
      }
    });
    
    // Refresh button
    document.getElementById('refreshBtn').addEventListener('click', function() {
      const loadingOverlay = document.getElementById('loadingOverlay');
      loadingOverlay.classList.remove('hidden'); // show overlay
      setTimeout(() => {
        location.reload();
      }, 1500); // 1.5s delay before reload
    });
    
    // Announcement Modal Functions
    let currentPdfDoc = null;
    let currentPageNum = 1;
    let totalPages = 0;
    
    function openAnnouncementModal(index) {
      const pdf = pdfData[index];
      document.getElementById('modalTitle').textContent = pdf.description;
      document.getElementById('modalMeta').textContent = `Posted on: ${pdf.posted_on} | File: ${pdf.file_path.split('/').pop()}`;
      const modal = document.getElementById('announcementModal');
      const container = document.getElementById('pdfContainer');
      
      // Reset container
      container.innerHTML = `
        <div class="loading-spinner"></div>
        <p class="text-center text-gray-600">Loading announcement...</p>
      `;
      
      // Reset page navigation
      currentPageNum = 1;
      document.getElementById('prevPageBtn').disabled = true;
      document.getElementById('nextPageBtn').disabled = true;
      document.getElementById('pageIndicator').textContent = 'Page 1 of 1';
      
      modal.style.display = 'block';
      
      // Get file extension
      const fileExtension = pdf.file_path.split('.').pop().toLowerCase();
      
      if (fileExtension === 'pdf') {
        // Load PDF
        pdfjsLib.getDocument(pdf.file_path).promise.then(pdfDoc => {
          currentPdfDoc = pdfDoc;
          totalPages = pdfDoc.numPages;
          
          // Update page indicator
          document.getElementById('pageIndicator').textContent = `Page 1 of ${totalPages}`;
          
          // Enable/disable navigation buttons
          document.getElementById('prevPageBtn').disabled = true;
          document.getElementById('nextPageBtn').disabled = totalPages <= 1;
          
          // Render first page
          renderPage(1);
        }).catch(error => {
          console.error('Error loading PDF:', error);
          container.innerHTML = `
            <div class="text-center py-8">
              <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
              <p class="text-lg text-gray-700 mb-2">Failed to load announcement</p>
              <p class="text-gray-600 mb-4">${error.message}</p>
              <button onclick="window.open('${pdf.file_path}', '_blank')" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-external-link-alt mr-2"></i> Open in New Tab
              </button>
            </div>
          `;
        });
      } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
        // Display image
        container.innerHTML = `
          <div class="image-container" style="max-width: 100%; max-height: 80vh; overflow: auto;">
            <img src="${pdf.file_path}" alt="Full view" style="max-width: 100%; height: auto; display: block; margin: 0 auto;">
          </div>
        `;
      } else if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(fileExtension)) {
        // Use Microsoft Office Online viewer
        const fileUrl = encodeURIComponent(pdf.file_path);
        container.innerHTML = `
          <div class="office-viewer">
            <iframe 
              src="https://view.officeapps.live.com/op/view.aspx?src=${fileUrl}" 
              style="width: 100%; height: 100%; border: none;"
              frameborder="0">
            </iframe>
          </div>
          <div class="text-center mt-4">
            <a href="${pdf.file_path}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg inline-block">
              <i class="fas fa-download mr-2"></i> Download File
            </a>
          </div>
        `;
      } else {
        // For other file types
        container.innerHTML = `
          <div class="text-center p-8">
            <i class="fas fa-file text-gray-400 text-6xl mb-4"></i>
            <p class="text-lg text-gray-700 mb-2">Preview not available</p>
            <p class="text-gray-600 mb-4">This file type cannot be previewed in the browser.</p>
            <p class="text-gray-600 mb-4">File: ${pdf.file_path.split('/').pop()}</p>
            <a href="${pdf.file_path}" download class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg">
              <i class="fas fa-download mr-2"></i> Download File
            </a>
          </div>
        `;
      }
    }
    
    function renderPage(pageNum) {
      if (!currentPdfDoc) return;
      
      const container = document.getElementById('pdfContainer');
      const modalBody = document.querySelector('.modal-body');
      
      // Show loading indicator
      container.innerHTML = `
        <div class="loading-spinner"></div>
        <p class="text-center text-gray-600">Loading page ${pageNum}...</p>
      `;
      
      currentPdfDoc.getPage(pageNum).then(page => {
        // Get the dimensions of the modal body
        const modalRect = modalBody.getBoundingClientRect();
        const availableWidth = modalRect.width - 40; // Subtract padding
        const availableHeight = modalRect.height - 40; // Subtract padding
        
        // Get the viewport at a readable scale (1.5)
        const viewport = page.getViewport({
          scale: 1.5
        });
        
        // Calculate the scale needed to fit the width
        const widthScale = availableWidth / viewport.width;
        
        // Calculate the scale needed to fit the height
        const heightScale = availableHeight / viewport.height;
        
        // Use the smaller scale to ensure the entire page fits
        let scale = Math.min(widthScale, heightScale);
        
        // Ensure the scale is not too small (minimum 0.8)
        scale = Math.max(scale, 0.8);
        
        // Get the scaled viewport
        const scaledViewport = page.getViewport({
          scale
        });
        
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        canvas.height = scaledViewport.height;
        canvas.width = scaledViewport.width;
        canvas.className = 'pdf-page';
        
        // Clear container and add canvas
        container.innerHTML = '';
        container.appendChild(canvas);
        
        return page.render({
          canvasContext: context,
          viewport: scaledViewport
        }).promise;
      }).catch(error => {
        console.error('Error rendering page:', error);
        container.innerHTML = `
          <div class="text-center py-8">
            <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
            <p class="text-lg text-gray-700 mb-2">Failed to load page ${pageNum}</p>
            <p class="text-gray-600">${error.message}</p>
          </div>
        `;
      });
    }
    
    function goToPrevPage() {
      if (currentPageNum > 1) {
        currentPageNum--;
        renderPage(currentPageNum);
        
        // Update navigation
        document.getElementById('prevPageBtn').disabled = currentPageNum === 1;
        document.getElementById('nextPageBtn').disabled = false;
        document.getElementById('pageIndicator').textContent = `Page ${currentPageNum} of ${totalPages}`;
      }
    }
    
    function goToNextPage() {
      if (currentPageNum < totalPages) {
        currentPageNum++;
        renderPage(currentPageNum);
        
        // Update navigation
        document.getElementById('prevPageBtn').disabled = false;
        document.getElementById('nextPageBtn').disabled = currentPageNum === totalPages;
        document.getElementById('pageIndicator').textContent = `Page ${currentPageNum} of ${totalPages}`;
      }
    }
    
    function closeAnnouncementModal() {
      document.getElementById('announcementModal').style.display = 'none';
      currentPdfDoc = null;
      currentPageNum = 1;
      totalPages = 0;
    }
    
    // Add event listeners for page navigation
    document.getElementById('prevPageBtn').addEventListener('click', goToPrevPage);
    document.getElementById('nextPageBtn').addEventListener('click', goToNextPage);
    
    // Close modal when clicking outside content
    window.onclick = function(event) {
      const modal = document.getElementById('announcementModal');
      if (event.target === modal) {
        closeAnnouncementModal();
      }
    }
    
    // Close modal with ESC key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeAnnouncementModal();
      }
    });
    
    // Add touch support for carousels on mobile
    document.addEventListener('DOMContentLoaded', function() {
      // Add touch swipe support for carousels
      const carousels = document.querySelectorAll('.carousel');
      
      carousels.forEach(carousel => {
        let touchStartX = 0;
        let touchEndX = 0;
        
        carousel.addEventListener('touchstart', function(e) {
          touchStartX = e.changedTouches[0].screenX;
        }, false);
        
        carousel.addEventListener('touchend', function(e) {
          touchEndX = e.changedTouches[0].screenX;
          handleSwipe(carousel);
        }, false);
        
        function handleSwipe(carousel) {
          const swipeThreshold = 50; // Minimum distance for swipe
          const diff = touchStartX - touchEndX;
          
          if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
              // Swipe left - go to next
              const carouselClass = carousel.classList[1]; // Get the second class which is the specific carousel class
              nextCarousel(carouselClass);
            } else {
              // Swipe right - go to previous
              const carouselClass = carousel.classList[1]; // Get the second class which is the specific carousel class
              prevCarousel(carouselClass);
            }
          }
        }
      });
      
      // Auto-rotate carousels on desktop, but not on mobile to save resources
      if (window.innerWidth > 768) {
        setInterval(() => {
          nextCarousel('mandates-carousel');
        }, 10000); // Change every 10 seconds
        
        setInterval(() => {
          nextCarousel('accreditation-carousel');
        }, 15000); // Change every 15 seconds
      }
    });
  </script>
</body>
</html>