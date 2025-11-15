<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DCEA POSTS</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-800">

<div class="w-full">
  <nav class="flex justify-center space-x-10 border-b-2 border-gray-200 mb-6">
    <button class="dcea-tab-btn text-orange-600 border-orange-600 hover:border-b-2 hover:border-orange-400 hover:text-orange-400 border-b-2 font-bold hover:font-bold pb-2 transition duration-200 transform hover:scale-110" data-subtab="dcea-announcement">Announcement</button>
    <button class="dcea-tab-btn text-gray-700 hover:border-b-2 hover:border-orange-400 hover:text-orange-400 hover:font-bold pb-2 transition duration-200 transform hover:scale-110" data-subtab="dcea-memo">Memo Updates</button>
    <button class="dcea-tab-btn text-gray-700 hover:border-b-2 hover:border-orange-400 hover:text-orange-400 hover:font-bold pb-2 transition duration-200 transform hover:scale-110" data-subtab="dcea-graphs">Graphs</button>
    <button class="dcea-tab-btn text-gray-700 hover:border-b-2 hover:border-orange-400 hover:text-orange-400 hover:font-bold pb-2 transition duration-200 transform hover:scale-110" data-subtab="dcea-about">About DCEA</button>
  </nav>

  <div id="dcea-announcement" class="dcea-subtab">
    <h2 class="text-xl font-semibold text-orange-600 text-center mb-4">DCEA Announcements</h2>
  </div>

  <div id="dcea-memo" class="dcea-subtab hidden">
    <h2 class="text-xl font-semibold text-orange-600 text-center mb-4">DCEA Memo Updates</h2>
  </div>

  <div id="dcea-graphs" class="dcea-subtab hidden">
    <h2 class="text-xl font-semibold text-orange-600 text-center mb-4">DCEA Graphs</h2>
  </div>

  <div id="dcea-about" class="dcea-subtab hidden">
    <h2 class="text-xl font-semibold text-orange-600 text-center mb-4">About DCEA</h2>
  </div>
</div>

<script>
  document.querySelectorAll('.dcea-tab-btn').forEach(button => {
    button.addEventListener('click', () => {
      const target = button.getAttribute('data-subtab');

      document.querySelectorAll('.dcea-tab-btn').forEach(btn => {
        btn.classList.remove('text-orange-600', 'border-b-2', 'border-orange-600', 'font-bold');
        btn.classList.add('text-gray-700');
      });

      document.querySelectorAll('.dcea-subtab').forEach(tab => {
        tab.classList.add('hidden');
      });

      document.getElementById(target).classList.remove('hidden');
      button.classList.add('text-orange-600', 'border-b-2', 'border-orange-600','font-bold');
      button.classList.remove('text-gray-700');
    });
  });
</script>

</body>
</html>
