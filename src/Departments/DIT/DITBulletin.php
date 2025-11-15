<?php
include 'DITBulletin_Config.php';
include "../../db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>DIT Bulletin</title>
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
    /* Enhanced Responsive font sizing */
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
    @media (max-width: 480px) {
      :root {
        --base-font-size: 11px;
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
      animation: marquee 25s linear infinite;
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
      font-size: clamp(0.9rem, 2.5vw, 1.5rem);
    }
    .header-subtitle {
      font-size: clamp(0.7rem, 2vw, 1.2rem);
    }
    .date-time {
      font-size: clamp(0.8rem, 2.2vw, 1.3rem);
    }
    .tenets-text {
      font-size: clamp(0.7rem, 1.8vw, 1.2rem);
    }
    .card-header {
      font-size: clamp(0.8rem, 2vw, 1.1rem);
    }
    .carousel-text {
      font-size: clamp(0.5rem, 1.5vw, 0.8rem);
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
      width: 95%;
      max-width: 1200px;
      max-height: 90vh;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }
    .modal-header {
      padding: 12px 15px;
      background-color: #f97316;
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .modal-title {
      font-size: 1.2rem;
      font-weight: 600;
    }
    .modal-close {
      font-size: 1.8rem;
      cursor: pointer;
      transition: transform 0.2s;
    }
    .modal-close:hover {
      transform: scale(1.2);
    }
    .modal-body {
      padding: 15px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      overflow: auto;
    }
    .pdf-container {
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      position: relative;
      max-height: 70vh;
    }
    .pdf-page {
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      max-width: 100%;
      max-height: 100%;
    }
    .modal-footer {
      padding: 12px 15px;
      background-color: #f3f4f6;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
    }
    .modal-meta {
      font-size: 0.8rem;
      color: #6b7280;
      text-align: center;
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
      width: 36px;
      height: 36px;
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
      font-size: 0.9rem;
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
      height: 70vh;
    }
    .carousel-container {
      width: 100%;
      height: 100%;
      max-height: 480px;
      overflow: hidden;
    }
    .pdf-preview-area {
      width: 100%;
      height: 320px;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
      cursor: pointer;
    }
    canvas {
      max-width: 100%;
      max-height: 100%;
    }
    .view-hint {
      position: absolute;
      bottom: 10px;
      right: 10px;
      background-color: rgba(249, 115, 22, 0.9);
      color: white;
      padding: 5px 10px;
      border-radius: 4px;
      font-size: 0.8rem;
      font-weight: 500;
      opacity: 0;
      transition: opacity 0.2s;
      pointer-events: none;
    }
    .pdf-preview-area:hover .view-hint {
      opacity: 1;
    }
    .loading-indicator {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
    }
    .spinner {
      border: 3px solid rgba(0, 0, 0, 0.1);
      border-radius: 50%;
      border-top: 3px solid #f97316;
      width: 30px;
      height: 30px;
      animation: spin 1s linear infinite;
      margin: 0 auto 10px;
    }
    /* Page Carousel Styles */
    .page-carousel {
      position: relative;
      width: 100%;
      height: 100%;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }
    .page-carousel-item {
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      transition: all 0.6s ease-in-out;
      display: flex;
      flex-direction: column;
    }
    .page-carousel-item:not(.active) {
      transform: translateX(100%);
      opacity: 0;
      z-index: 0;
    }
    .page-carousel-item.active {
      transform: translateX(0);
      opacity: 1;
      z-index: 1;
    }
    .page-content {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    .page-nav {
      display: flex;
      justify-content: center;
      margin-top: 8px;
      padding: 5px 0;
      gap: 10px;
    }
    .page-nav-btn {
      background-color: #f97316;
      color: white;
      border: none;
      border-radius: 30px;
      width: 100px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(249, 115, 22, 0.3);
      font-size: 0.9rem;
      font-weight: 600;
      position: relative;
      overflow: hidden;
    }
    .page-nav-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
      transition: left 0.5s;
    }
    .page-nav-btn:hover::before {
      left: 100%;
    }
    .page-nav-btn:hover {
      background-color: #ea580c;
      transform: translateY(-2px);
      box-shadow: 0 6px 8px rgba(249, 115, 22, 0.4);
    }
    .page-nav-btn:active {
      transform: translateY(0);
      box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);
    }
    .page-nav-btn i {
      margin-right: 5px;
    }
    .page-indicator {
      display: flex;
      justify-content: center;
      margin-top: 5px;
      padding: 3px 0;
    }
    .page-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background-color: #d1d5db;
      margin: 0 5px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .page-dot.active {
      background-color: #f97316;
      transform: scale(1.3);
      box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.3);
    }
    .page-dot:hover {
      background-color: #fdba74;
    }
    
    /* Mobile-specific adjustments */
    @media (max-width: 768px) {
      .pdf-preview-area {
        height: 250px;
      }
      
      .content-area {
        height: calc(100% - 1rem);
      }
      
      .page-nav-btn {
        width: 80px;
        font-size: 0.8rem;
      }
      
      .page-dot {
        width: 12px;
        height: 12px;
      }
    }
    
    @media (max-width: 640px) {
      .modal-content {
        width: 98%;
        margin: 5% auto;
      }
      
      .modal-header {
        padding: 10px;
      }
      
      .modal-title {
        font-size: 1rem;
      }
      
      .modal-close {
        font-size: 1.5rem;
      }
      
      .modal-body {
        padding: 10px;
      }
      
      .modal-footer {
        padding: 10px;
      }
      
      .office-viewer {
        height: 60vh;
      }
      
      .pdf-container {
        max-height: 60vh;
      }
    }
    
    @media (max-width: 480px) {
      .header-title {
        font-size: 0.9rem;
      }
      
      .header-subtitle {
        font-size: 0.7rem;
      }
      
      .date-time {
        font-size: 0.8rem;
      }
      
      .tenets-text {
        font-size: 0.7rem;
      }
      
      .card-header {
        font-size: 0.8rem;
      }
      
      .carousel-text {
        font-size: 0.6rem;
      }
      
      .page-nav-btn {
        width: 70px;
        height: 32px;
        font-size: 0.7rem;
      }
      
      .page-dot {
        width: 10px;
        height: 10px;
        margin: 0 3px;
      }
    }
  </style>
</head>
<body class="bg-orange-500 p-2 font-sans h-full flex flex-col">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row justify-between items-center bg-gradient-to-r from-orange-700 to-orange-800 p-3 rounded-xl mb-2 text-white shadow-xl fade-in">
    <div class="text-left flex items-center mb-2 sm:mb-0">
      <div class="bg-white/20 p-2 sm:p-3 rounded-full mr-2 sm:mr-4 pulse">
        <i class="fas fa-university text-lg sm:text-2xl"></i>
      </div>
      <div>
        <div class="header-title font-bold">Cavite State University</div>
        <div class="header-subtitle text-orange-200">Department of Information Technology</div>
      </div>
    </div>
    <div class="text-right">
      <div id="date" class="date-time font-semibold"></div>
      <div id="time" class="date-time font-semibold"></div>
    </div>
  </div>
  
  <!-- Tenets -->
  <div class="bg-white/90 backdrop-blur-sm text-center font-bold text-black py-2 sm:py-3 mb-2 rounded-xl overflow-hidden relative h-12 sm:h-16 flex items-center shadow-lg slide-up">
    <div class="marquee-wrapper whitespace-nowrap">
      <div class="marquee-content inline-block tenets-text">
        TRUTH • EXCELLENCE • SERVICE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        ENGINEERING INNOVATION • DIGITAL TRANSFORMATION • TECH EXCELLENCE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      </div>
      <div class="marquee-content inline-block tenets-text" aria-hidden="true">
        TRUTH • EXCELLENCE • SERVICE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        ENGINEERING INNOVATION • DIGITAL TRANSFORMATION • TECH EXCELLENCE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      </div>
    </div>
  </div>
  
  <!-- Page 1 -->
  <div id="page1" class="page active">
    <main class="grid grid-cols-1 lg:grid-cols-3 gap-2 p-1 font-bold text-center flex-grow main-grid">
      <!-- Mandates and Accreditation -->
      <div class="flex flex-col space-y-2 h-full">
        <!-- Mandates -->
        <div class="bg-white rounded-lg shadow-md p-2 flex-1 overflow-auto transition duration-500 transform hover:scale-[1.02]">
          <div class="h-full flex flex-col">
            <div class="card-header mb-2 text-orange-600 flex items-center p-2 border-b">
              <i class="fas fa-landmark mr-2"></i> About CvSU
            </div>
            <div class="carousel mandates-carousel flex-grow relative">
              <div class="carousel-item active h-full">
                <div class="h-full flex items-center justify-center bg-gray-100 responsive-bg" style="background-image: url('images/bg-small.png');">
                  <div class="carousel-text p-2 sm:p-3" style="margin: 5px 10px; background-color: rgba(240, 240, 240, 0.8); border-radius: 6px; line-height: 1.5;">
                    MISSION <br><br>
                    <?php echo mission($conn)['content']; ?>
                  </div>
                </div>
              </div>
              <div class="carousel-item h-full">
                <div class="h-full flex items-center justify-center bg-gray-100 responsive-bg" style="background-image: url('images/bg-small.png');">
                  <div class="carousel-text p-2 sm:p-3" style="margin: 5px 10px; background-color: rgba(240, 240, 240, 0.8); border-radius: 6px; line-height: 1.5;">
                    VISION <br><br>
                    <?php echo vision($conn)['content']; ?>
                  </div>
                </div>
              </div>
              <div class="carousel-item h-full">
                <div class="h-full flex items-center justify-center bg-gray-100 responsive-bg" style="background-image: url('images/bg-small.png');">
                  <div class="carousel-text p-2 sm:p-3" style="margin: 5px 10px; background-color: rgba(240, 240, 240, 0.8); border-radius: 6px; line-height: 1.5;">
                    CORE VALUES <br><br>
                    <?php echo core_values($conn)['content']; ?>
                  </div>
                </div>
              </div>
              <div class="carousel-item h-full">
                <div class="h-full flex items-center justify-center bg-gray-100 responsive-bg" style="background-image: url('images/bg-small.png');">
                  <div class="carousel-text p-2 sm:p-3" style="margin: 5px 10px; background-color: rgba(240, 240, 240, 0.8); border-radius: 6px; line-height: 1.5;">
                    QUALITY POLICY <br><br>
                    <?php echo quality_policy($conn)['content']; ?>
                  </div>
                </div>
              </div>
              <div class="carousel-item h-full">
                <div class="h-full flex items-center justify-center bg-gray-100 responsive-bg" style="background-image: url('images/bg-small.png');">
                  <div class="carousel-text p-2 sm:p-3" style="margin: 5px 10px; background-color: rgba(240, 240, 240, 0.8); border-radius: 6px; line-height: 1.5;">
                    COLLEGE GOALS<br><br>
                    <div style="text-align: left; font-size: 8px">
                      <?php echo nl2br(college_goals($conn)['content']); ?>
                    </div>
                  </div>
                </div>
              </div>
              <div class="carousel-item h-full">
                <div class="h-full flex items-center justify-center bg-gray-100 responsive-bg" style="background-image: url('images/bg-small.png');">
                  <div class="carousel-text p-2 sm:p-3" style="margin: 5px 10px; background-color: rgba(240, 240, 240, 0.8); border-radius: 6px; line-height: 1.5;">
                    ABOUT THE DEPARTMENT <br><br>
                    <div style="text-align: left;">
                      <?php echo nl2br(about_department($conn)['content']); ?>
                    </div>
                  </div>
                </div>
              </div>
              <div class="carousel-item h-full">
                <div class="h-full flex items-center justify-center bg-gray-100 responsive-bg" style="background-image: url('images/bg-small.png');">
                  <div class="carousel-text p-2 sm:p-3" style="margin: 5px 10px; background-color: rgba(240, 240, 240, 0.8); border-radius: 6px; line-height: 1.5;">
                    PROGRAM OFFERINGS <br><br>
                    <div style="text-align: left;">
                      <?php echo nl2br(program_offerings($conn)['content']); ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="flex justify-center space-x-2 mt-2">
              <button class="mandates-prev-btn bg-orange-500 text-white p-1 sm:p-2 rounded-full hover:bg-orange-600 transition duration-200 transform hover:scale-110">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="6" d="M15 19l-7-7 7-7" />
                </svg>
              </button>
              <button class="mandates-next-btn bg-orange-500 text-white p-1 sm:p-2 rounded-full hover:bg-orange-600 transition duration-200 transform hover:scale-110">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="6" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>
          </div>
        </div>
        
        <!-- Accreditation -->
        <div class="bg-white rounded-lg shadow-md p-2 flex-1 overflow-auto transition duration-200 transform hover:scale-[1.02]">
          <div class="h-full flex flex-col">
            <div class="card-header mb-2 text-orange-600 flex items-center p-2 sm:p-3 border-b">
              <i class="fas fa-award mr-2"></i> Accreditation Status
            </div>
            <div class="carousel accreditation-carousel flex-grow relative">
              <div class="carousel-item active h-full">
                <div class="h-full flex items-center justify-center bg-gray-100 responsive-bg" style="background-image: url('images/bg-small.png');">
                  <div class="carousel-text p-2 sm:p-3" style="margin: 5px 10px; background-color: rgba(240, 240, 240, 0.8); border-radius: 6px;">
                    Status 1 Content
                  </div>
                </div>
              </div>
              <div class="carousel-item h-full">
                <div class="h-full flex items-center justify-center bg-gray-100 responsive-bg" style="background-image: url('images/bg-small.png');">
                  <div class="carousel-text p-2 sm:p-3" style="margin: 5px 10px; background-color: rgba(240, 240, 240, 0.8); border-radius: 6px;">
                    Status 2 Content
                  </div>
                </div>
              </div>
              <div class="carousel-item h-full">
                <div class="h-full flex items-center justify-center bg-gray-100 responsive-bg" style="background-image: url('images/bg-small.png');">
                  <div class="carousel-text p-2 sm:p-3" style="margin: 5px 10px; background-color: rgba(240, 240, 240, 0.8); border-radius: 6px;">
                    Status 3 Content
                  </div>
                </div>
              </div>
            </div>
            <div class="flex justify-center space-x-2 mt-2">
              <button class="accreditation-prev-btn bg-orange-500 text-white p-1 sm:p-2 rounded-full hover:bg-orange-600 transition duration-200 transform hover:scale-110">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="6" d="M15 19l-7-7 7-7" />
                </svg>
              </button>
              <button class="accreditation-next-btn bg-orange-500 text-white p-1 sm:p-2 rounded-full hover:bg-orange-600 transition duration-200 transform hover:scale-110">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="6" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Calendar -->
      <div class="bg-white rounded-lg shadow-md p-2 sm:p-4 overflow-auto transition duration-500 transform hover:scale-[1.02]">
        <div class="card-header mb-2 text-orange-600 flex items-center border-b pb-2">
          <i class="fas fa-calendar-alt mr-2"></i> Academic Calendar
        </div>
        <div class="overflow-x-auto">
          <?php include 'DIT_CalendarView.php' ?>
        </div>
      </div>
      
      <!-- Announcement & Memos Page Carousel -->
      <div class="bg-white rounded-lg shadow-md p-2 sm:p-4 overflow-auto transition duration-500 transform hover:scale-[1.02] announcement-section">
        <div class="page-carousel">
          <!-- Announcements Page -->
          <div class="page-carousel-item active" id="announcements-page">
            <div class="card-header mb-2 text-orange-600 flex items-center border-b pb-2">
              <i class="fas fa-bullhorn mr-2"></i> Announcements
            </div>
            <div class="page-content">
              <?php 
              // Get announcement data directly
              $query = "SELECT * FROM DIT_Post WHERE title='announcement' and status='Approved'";
              $result = $conn->query($query);
              $announcements = [];
              while ($row = $result->fetch_assoc()) {
                  $announcements[] = [
                      'file_path' => 'uploads/' . $row['file_path'],
                      'description' => $row['content'],
                      'posted_on' => date("F j, Y", strtotime($row['created_at']))
                  ];
              }
              ?>
              <div class="carousel-container bg-white shadow-lg rounded-lg p-4 flex flex-col justify-between h-full">
                <div id="announcement-viewer" class="pdf-preview-area rounded mb-4 bg-gray-50">
                  <div class="loading-indicator">
                    <div class="spinner"></div>
                    <p class="text-gray-500 text-sm">Loading preview...</p>
                  </div>
                  <div class="view-hint">Click to view full announcement</div>
                </div>
                <div class="text-center mb-2">
                  <p id="announcement-description" class="text-base font-semibold text-gray-800 truncate px-2"></p>
                  <p id="announcement-filename" class="text-sm text-gray-600 italic"></p>
                </div>
                <div class="text-center text-sm text-gray-500">
                  <span id="announcement-posted-date"></span>
                </div>
                <div class="flex justify-center space-x-2 mt-2">
                  <button id="announcement-prev-btn" class="bg-orange-500 text-white p-2 rounded-full hover:bg-orange-600 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="6" d="M15 19l-7-7 7-7" />
                    </svg>
                  </button>
                  <button id="announcement-next-btn" class="bg-orange-500 text-white p-2 rounded-full hover:bg-orange-600 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="6" d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Memos Page -->
          <div class="page-carousel-item" id="memos-page">
            <div class="card-header mb-2 text-orange-600 flex items-center border-b pb-2">
              <i class="fas fa-file-alt mr-2"></i> Memos
            </div>
            <div class="page-content">
              <?php 
              // Get memo data directly
              $query = "SELECT * FROM DIT_Post WHERE (title='memo' OR title='Memo') AND (status='Approved' OR status='approved')";
              $result = $conn->query($query);
              $memos = [];
              if ($result) {
                  while ($row = $result->fetch_assoc()) {
                      $file_path = 'uploads/' . $row['file_path'];
                      $file_exists = file_exists($file_path);
                      $memos[] = [
                          'file_path' => $file_path,
                          'description' => $row['content'],
                          'posted_on' => date("F j, Y", strtotime($row['created_at'])),
                          'file_exists' => $file_exists,
                          'id' => $row['id']
                      ];
                  }
              }
              ?>
              <?php if (count($memos) > 0): ?>
                <div class="carousel-container bg-white shadow-lg rounded-lg p-4 flex flex-col justify-between h-full">
                  <div id="memo-viewer" class="pdf-preview-area rounded mb-4 bg-gray-50">
                    <div class="loading-indicator">
                      <div class="spinner"></div>
                      <p class="text-gray-500 text-sm">Loading preview...</p>
                    </div>
                    <div class="view-hint">Click to view full memo</div>
                  </div>
                  <div class="text-center mb-2">
                    <p id="memo-description" class="text-base font-semibold text-gray-800 truncate px-2"></p>
                    <p id="memo-filename" class="text-sm text-gray-600 italic"></p>
                  </div>
                  <div class="text-center text-sm text-gray-500">
                    <span id="memo-posted-date"></span>
                  </div>
                  <div class="flex justify-center space-x-2 mt-2">
                    <button id="memo-prev-btn" class="bg-orange-500 text-white p-2 rounded-full hover:bg-orange-600 transition-colors duration-200 shadow-md">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="6" d="M15 19l-7-7 7-7" />
                      </svg>
                    </button>
                    <button id="memo-next-btn" class="bg-orange-500 text-white p-2 rounded-full hover:bg-orange-600 transition-colors duration-200 shadow-md">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="6" d="M9 5l7 7-7 7" />
                      </svg>
                    </button>
                  </div>
                </div>
              <?php else: ?>
                <div class="no-memos-message flex flex-col justify-center items-center h-full p-4 text-center">
                  <i class="fas fa-file-alt text-gray-400 text-5xl mb-4"></i>
                  <p class="text-gray-600 text-lg">No approved memos available</p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        
        <!-- Page Navigation -->
        <div class="page-nav">
          <button class="page-nav-btn page-prev-btn">
            <i class="fas fa-arrow-left"></i> Previous
          </button>
          <button class="page-nav-btn page-next-btn">
            Next <i class="fas fa-arrow-right"></i>
          </button>
        </div>
        
        <!-- Page Indicators -->
        <div class="page-indicator">
          <div class="page-dot active" data-page="0"></div>
          <div class="page-dot" data-page="1"></div>
        </div>
      </div>
    </main>
  </div>
  
  <!-- Page 2 -->
  <div id="page2" class="page">
    <main class="grid grid-cols-1 lg:grid-cols-2 gap-2 p-1 font-bold text-center flex-grow page2-grid">
      <!-- Graphs -->
      <div class="bg-white rounded-lg shadow-md p-2 sm:p-4 transition duration-500 transform hover:scale-[1.02]">
        <div class="card-header mb-2 text-orange-600 flex items-center border-b pb-2">
          <i class="fas fa-chart-pie mr-2"></i> Performance Metrics
        </div>
        <div class="content-area">
          <?php include 'graphs_carousel.php' ?>
        </div>
      </div>
      

      <!-- Organizational Structure -->
      <div class="rounded-lg shadow-md p-2 sm:p-4 transition duration-500 transform hover:scale-[1.02] relative overflow-hidden" style="background-image: url('images/ditorg.jpg'); background-size: cover; background-position: center;">
        <div class="absolute inset-0 bg-white bg-opacity-0 z-"></div>
        <div class="relative z-10">
          <div class="card-header mb-2 text-orange-600 flex items-center border-b pb-2">
            <i class="fas fa-sitemap mr-2"></i> DIT Organizational Structure
          </div>
          <div class="content-area">
            <?php include '../OrganizationalChart/DIT_ViewChart.php' ?>
          </div>
        </div>
      </div>
    </main>
  </div>
  
  <!-- Page Navigation -->
  <div class="flex flex-col sm:flex-row justify-between items-center mt-2 sm:mt-4 gap-2">
    <div class="flex space-x-3 w-full sm:w-auto">
      <button id="refreshBtn" class="btn btn-secondary px-3 py-1 sm:px-4 sm:py-2 rounded-xl bg-white hover:bg-orange-800 hover:text-white transition-colors duration-200 flex items-center shadow-md w-full sm:w-auto justify-center">
        <i class="fas fa-sync-alt mr-1 sm:mr-2"></i> <span class="text-sm sm:text-base">Refresh</span>
      </button>
    </div>
    <button id="togglePageBtn" class="btn btn-primary px-3 py-1 sm:px-4 sm:py-2 rounded-xl bg-white hover:bg-orange-800 hover:text-white transition-colors duration-200 flex items-center shadow-md w-full sm:w-auto justify-center">
      <i class="fas fa-arrow-right mr-1 sm:mr-2"></i> <span class="text-sm sm:text-base">Go to Page 2</span>
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
    <div class="bg-white p-4 sm:p-6 rounded-lg shadow-lg flex flex-col items-center">
      <i class="fas fa-spinner fa-spin text-2xl sm:text-3xl text-orange-600 mb-3"></i>
      <p class="text-base sm:text-lg font-semibold">Refreshing...</p>
    </div>
  </div>
  
  <script>
    // Initialize PDF.js worker
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';
    
    // Global variables for PDF data
    let announcementData = <?= json_encode($announcements); ?>;
    let memoData = <?= json_encode($memos); ?>;
    
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
    
    // Page Carousel functionality
    let currentPageIndex = 0;
    const pages = document.querySelectorAll('.page-carousel-item');
    const dots = document.querySelectorAll('.page-dot');
    
    function showPage(index) {
      // Hide all pages
      pages.forEach(page => {
        page.classList.remove('active');
      });
      
      // Remove active class from all dots
      dots.forEach(dot => {
        dot.classList.remove('active');
      });
      
      // Show selected page
      pages[index].classList.add('active');
      dots[index].classList.add('active');
      
      currentPageIndex = index;
    }
    
    function prevPage() {
      const newIndex = (currentPageIndex - 1 + pages.length) % pages.length;
      showPage(newIndex);
    }
    
    function nextPage() {
      const newIndex = (currentPageIndex + 1) % pages.length;
      showPage(newIndex);
    }
    
    function goToPage(index) {
      showPage(index);
    }
    
    // Page toggle functionality
    const togglePageBtn = document.getElementById("togglePageBtn");
    let currentPage = 1;
    togglePageBtn.addEventListener("click", () => {
      if (currentPage === 1) {
        document.getElementById("page1").classList.remove("active");
        document.getElementById("page2").classList.add("active");
        togglePageBtn.innerHTML = `<i class="fas fa-arrow-left mr-1 sm:mr-2"></i> <span class="text-sm sm:text-base">Go to Page 1</span>`;
        currentPage = 2;
      } else {
        document.getElementById("page2").classList.remove("active");
        document.getElementById("page1").classList.add("active");
        togglePageBtn.innerHTML = `<i class="fas fa-arrow-right mr-1 sm:mr-2"></i> <span class="text-sm sm:text-base">Go to Page 2</span>`;
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
      console.log("openAnnouncementModal called with index:", index); // Debug: Log function call
      console.log("announcementData:", announcementData); // Debug: Log announcement data
      
      if (!announcementData || announcementData.length === 0) {
        console.error("No announcement data available"); // Debug: Check if data is available
        return;
      }
      
      if (index >= announcementData.length) {
        console.error("Index out of bounds:", index, "announcementData length:", announcementData.length); // Debug: Check index bounds
        return;
      }
      
      const pdf = announcementData[index];
      console.log("Opening announcement modal for:", pdf); // Debug: Log PDF being opened
      
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
      console.log("File extension:", fileExtension); // Debug: Log file extension
      
      if (fileExtension === 'pdf') {
        // Load PDF
        pdfjsLib.getDocument(pdf.file_path).promise.then(pdfDoc => {
          console.log("PDF document loaded successfully"); // Debug: Confirm PDF loading
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
          console.error('Error loading PDF:', error); // Debug: Log any errors
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
    
    function openMemoModal(index) {
      console.log("openMemoModal called with index:", index); // Debug: Log function call
      console.log("memoData:", memoData); // Debug: Log memo data
      
      if (!memoData || memoData.length === 0) {
        console.error("No memo data available"); // Debug: Check if data is available
        return;
      }
      
      if (index >= memoData.length) {
        console.error("Index out of bounds:", index, "memoData length:", memoData.length); // Debug: Check index bounds
        return;
      }
      
      const pdf = memoData[index];
      console.log("Opening memo modal for:", pdf); // Debug: Log PDF being opened
      
      document.getElementById('modalTitle').innerHTML = `<i class="fas fa-file-alt mr-2"></i> Memo: ${pdf.description}`;
      document.getElementById('modalMeta').textContent = `Posted on: ${pdf.posted_on} | File: ${pdf.file_path.split('/').pop()}`;
      const modal = document.getElementById('announcementModal');
      const container = document.getElementById('pdfContainer');
      
      // Reset container
      container.innerHTML = `
        <div class="loading-spinner"></div>
        <p class="text-center text-gray-600">Loading memo...</p>
      `;
      
      // Reset page navigation
      currentPageNum = 1;
      document.getElementById('prevPageBtn').disabled = true;
      document.getElementById('nextPageBtn').disabled = true;
      document.getElementById('pageIndicator').textContent = 'Page 1 of 1';
      
      modal.style.display = 'block';
      
      // Get file extension
      const fileExtension = pdf.file_path.split('.').pop().toLowerCase();
      console.log("File extension:", fileExtension); // Debug: Log file extension
      
      if (fileExtension === 'pdf') {
        // Load PDF
        pdfjsLib.getDocument(pdf.file_path).promise.then(pdfDoc => {
          console.log("PDF document loaded successfully"); // Debug: Confirm PDF loading
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
          console.error('Error loading PDF:', error); // Debug: Log any errors
          container.innerHTML = `
            <div class="text-center py-8">
              <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
              <p class="text-lg text-gray-700 mb-2">Failed to load memo</p>
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
    
    // Add event listeners for carousel buttons
    document.addEventListener('DOMContentLoaded', function() {
      // Mandates carousel
      document.querySelector('.mandates-prev-btn').addEventListener('click', function() {
        prevCarousel('mandates-carousel');
      });
      
      document.querySelector('.mandates-next-btn').addEventListener('click', function() {
        nextCarousel('mandates-carousel');
      });
      
      // Accreditation carousel
      document.querySelector('.accreditation-prev-btn').addEventListener('click', function() {
        prevCarousel('accreditation-carousel');
      });
      
      document.querySelector('.accreditation-next-btn').addEventListener('click', function() {
        nextCarousel('accreditation-carousel');
      });
      
      // Page navigation
      document.querySelector('.page-prev-btn').addEventListener('click', prevPage);
      document.querySelector('.page-next-btn').addEventListener('click', nextPage);
      
      // Page dots
      document.querySelectorAll('.page-dot').forEach(function(dot, index) {
        dot.addEventListener('click', function() {
          goToPage(index);
        });
      });
      
      // Announcement carousel
      let announcementIndex = 0;
      const announcementViewer = document.getElementById('announcement-viewer');
      const announcementDesc = document.getElementById('announcement-description');
      const announcementFilename = document.getElementById('announcement-filename');
      const announcementPostedDate = document.getElementById('announcement-posted-date');
      
      function renderAnnouncement(index) {
        const pdf = announcementData[index];
        console.log("Rendering Announcement:", pdf); // Debug: Log current PDF
        
        // Show loading indicator
        announcementViewer.innerHTML = `
        <div class="loading-indicator">
            <div class="spinner"></div>
            <p class="text-gray-500 text-sm">Loading preview...</p>
        </div>
        <div class="view-hint">Click to view full announcement</div>
      `;
        
        announcementDesc.textContent = pdf.description;
        announcementFilename.textContent = pdf.file_path.split('/').pop();
        announcementPostedDate.textContent = "Posted on: " + pdf.posted_on;
        
        // Get file extension
        const fileExtension = pdf.file_path.split('.').pop().toLowerCase();
        console.log("File extension:", fileExtension); // Debug: Log file extension
        
        if (fileExtension === 'pdf') {
          pdfjsLib.getDocument(pdf.file_path).promise.then(pdfDoc => {
            return pdfDoc.getPage(1);
          }).then(page => {
            // Calculate scale to fit the container
            const containerWidth = announcementViewer.clientWidth;
            const containerHeight = announcementViewer.clientHeight;
            const viewport = page.getViewport({
              scale: 1.0
            });
            // Calculate scale to fit container while maintaining aspect ratio
            const scale = Math.min(
              containerWidth / viewport.width,
              containerHeight / viewport.height
            ) * 1.2; // Increased from 0.9 to 1.2 for larger display
            const scaledViewport = page.getViewport({
              scale
            });
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.height = scaledViewport.height;
            canvas.width = scaledViewport.width;
            // Clear viewer and add canvas
            announcementViewer.innerHTML = '';
            announcementViewer.appendChild(canvas);
            // Add view hint back
            const hint = document.createElement('div');
            hint.className = 'view-hint';
            hint.textContent = 'Click to view full announcement';
            announcementViewer.appendChild(hint);
            return page.render({
              canvasContext: context,
              viewport: scaledViewport
            }).promise;
          }).catch(error => {
            console.error('Preview error:', error);
            announcementViewer.innerHTML = `
            <div class="text-center p-4">
                <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                <p class="text-red-500">Failed to load preview</p>
            </div>
            <div class="view-hint">Click to view full announcement</div>
        `;
          });
        } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
          // Display image preview
          announcementViewer.innerHTML = `
          <img src="${pdf.file_path}" alt="Preview" style="max-width: 100%; max-height: 100%; object-fit: contain;">
          <div class="view-hint">Click to view full announcement</div>
      `;
        } else {
          // For other file types, show icon
          announcementViewer.innerHTML = `
          <div class="text-center">
              <i class="fas fa-file text-gray-400 text-6xl mb-2"></i>
              <p class="text-gray-600">No preview available</p>
          </div>
          <div class="view-hint">Click to view full announcement</div>
      `;
        }
      }
      
      // Add click event to open modal
      announcementViewer.addEventListener('click', () => {
        console.log("Announcement viewer clicked, opening modal for index:", announcementIndex); // Debug: Log click event
        openAnnouncementModal(announcementIndex);
      });
      
      document.getElementById('announcement-prev-btn').addEventListener('click', () => {
        announcementIndex = (announcementIndex - 1 + announcementData.length) % announcementData.length;
        console.log("Announcement Previous button clicked, new index:", announcementIndex); // Debug: Log navigation
        renderAnnouncement(announcementIndex);
      });
      
      document.getElementById('announcement-next-btn').addEventListener('click', () => {
        announcementIndex = (announcementIndex + 1) % announcementData.length;
        console.log("Announcement Next button clicked, new index:", announcementIndex); // Debug: Log navigation
        renderAnnouncement(announcementIndex);
      });
      
      // Initial render
      if (announcementData.length > 0) {
        renderAnnouncement(announcementIndex);
      }
      
      // Memo carousel
      let memoIndex = 0;
      const memoViewer = document.getElementById('memo-viewer');
      const memoDesc = document.getElementById('memo-description');
      const memoFilename = document.getElementById('memo-filename');
      const memoPostedDate = document.getElementById('memo-posted-date');
      
      function renderMemo(index) {
        const pdf = memoData[index];
        console.log("Rendering Memo:", pdf); // Debug: Log current PDF
        
        // Check if file exists
        if (!pdf.file_exists) {
          memoViewer.innerHTML = `
              <div class="text-center p-4">
                  <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                  <p class="text-red-500">File not found</p>
                  <p class="text-gray-600 text-sm">${pdf.file_path}</p>
              </div>
          `;
          memoDesc.textContent = pdf.description;
          memoFilename.textContent = pdf.file_path.split('/').pop();
          memoPostedDate.textContent = "Posted on: " + pdf.posted_on;
          return;
        }
        
        // Show loading indicator
        memoViewer.innerHTML = `
            <div class="loading-indicator">
                <div class="spinner"></div>
                <p class="text-gray-500 text-sm">Loading preview...</p>
            </div>
            <div class="view-hint">Click to view full memo</div>
        `;
        
        memoDesc.textContent = pdf.description;
        memoFilename.textContent = pdf.file_path.split('/').pop();
        memoPostedDate.textContent = "Posted on: " + pdf.posted_on;
        
        // Get file extension
        const fileExtension = pdf.file_path.split('.').pop().toLowerCase();
        console.log("File extension:", fileExtension); // Debug: Log file extension
        
        if (fileExtension === 'pdf') {
          try {
            pdfjsLib.getDocument(pdf.file_path).promise.then(pdfDoc => {
              console.log("PDF document loaded successfully"); // Debug: Confirm PDF loading
              return pdfDoc.getPage(1);
            }).then(page => {
              console.log("First page retrieved successfully"); // Debug: Confirm page retrieval
              
              // Calculate scale to fit the container
              const containerWidth = memoViewer.clientWidth;
              const containerHeight = memoViewer.clientHeight;
              const viewport = page.getViewport({ scale: 1.0 });
              
              // Calculate scale to fit container while maintaining aspect ratio
              const scale = Math.min(
                containerWidth / viewport.width,
                containerHeight / viewport.height
              ) * 1.2; // Increased from 0.9 to 1.2 for larger display
              
              const scaledViewport = page.getViewport({ scale });
              const canvas = document.createElement('canvas');
              const context = canvas.getContext('2d');
              canvas.height = scaledViewport.height;
              canvas.width = scaledViewport.width;
              
              // Clear viewer and add canvas
              memoViewer.innerHTML = '';
              memoViewer.appendChild(canvas);
              
              // Add view hint back
              const hint = document.createElement('div');
              hint.className = 'view-hint';
              hint.textContent = 'Click to view full memo';
              memoViewer.appendChild(hint);
              
              return page.render({
                canvasContext: context,
                viewport: scaledViewport
              }).promise;
            }).then(() => {
              console.log("PDF rendered successfully"); // Debug: Confirm rendering
            }).catch(error => {
              console.error('Preview error:', error); // Debug: Log any errors
              memoViewer.innerHTML = `
                  <div class="text-center p-4">
                      <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                      <p class="text-red-500">Failed to load preview</p>
                      <p class="text-gray-600 text-sm">${error.message}</p>
                  </div>
                  <div class="view-hint">Click to view full memo</div>
              `;
            });
          } catch (error) {
            console.error('PDF loading error:', error); // Debug: Log any errors
            memoViewer.innerHTML = `
                <div class="text-center p-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                    <p class="text-red-500">Failed to load PDF</p>
                    <p class="text-gray-600 text-sm">${error.message}</p>
                </div>
                <div class="view-hint">Click to view full memo</div>
            `;
          }
        } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
          // Display image preview
          memoViewer.innerHTML = `
              <img src="${pdf.file_path}" alt="Preview" style="max-width: 100%; max-height: 100%; object-fit: contain;">
              <div class="view-hint">Click to view full memo</div>
          `;
        } else {
          // For other file types, show icon
          memoViewer.innerHTML = `
              <div class="text-center">
                  <i class="fas fa-file text-gray-400 text-6xl mb-2"></i>
                  <p class="text-gray-600">No preview available</p>
                  <p class="text-gray-500 text-sm">${fileExtension.toUpperCase()} file</p>
              </div>
              <div class="view-hint">Click to view full memo</div>
          `;
        }
      }
      
      // Add click event to open modal
      memoViewer.addEventListener('click', () => {
        console.log("Memo viewer clicked, opening modal for index:", memoIndex); // Debug: Log click event
        openMemoModal(memoIndex);
      });
      
      document.getElementById('memo-prev-btn').addEventListener('click', () => {
        memoIndex = (memoIndex - 1 + memoData.length) % memoData.length;
        console.log("Memo Previous button clicked, new index:", memoIndex); // Debug: Log navigation
        renderMemo(memoIndex);
      });
      
      document.getElementById('memo-next-btn').addEventListener('click', () => {
        memoIndex = (memoIndex + 1) % memoData.length;
        console.log("Memo Next button clicked, new index:", memoIndex); // Debug: Log navigation
        renderMemo(memoIndex);
      });
      
      // Initial render
      if (memoData.length > 0) {
        renderMemo(memoIndex);
      }
    });
  </script>
</body>
</html>