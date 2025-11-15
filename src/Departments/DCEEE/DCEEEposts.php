<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DCEEE POSTS</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-800">

<div class="w-full">
  <nav class="flex justify-center space-x-10 border-b-2 border-gray-200 mb-6">
    <button class="dceee-tab-btn text-orange-600  border-orange-600 hover:border-b-2 hover:border-orange-400 hover:text-orange-400 border-b-2 font-bold hover:font-bold pb-2" data-subtab="dceee-announcement">Announcement</button>
    <button class="dceee-tab-btn text-gray-700 hover:border-b-2 hover:border-orange-400 hover:text-orange-400 hover:font-bold pb-2" data-subtab="dceee-memo">Memo Updates</button>
    <button class="dceee-tab-btn text-gray-700 hover:border-b-2 hover:border-orange-400 hover:text-orange-400 hover:font-bold pb-2" data-subtab="dceee-graphs">Graphs</button>
    <button class="dceee-tab-btn text-gray-700 hover:border-b-2 hover:border-orange-400 hover:text-orange-400 hover:font-bold pb-2" data-subtab="dceee-about">About DCEEE</button>
  </nav>

  <div id="dceee-announcement" class="dceee-subtab">
    <h2 class="text-xl font-semibold text-orange-600 text-center mb-4">DCEEE Announcements</h2>
  </div>

  <div id="dceee-memo" class="dceee-subtab hidden">
    <h2 class="text-xl font-semibold text-orange-600 text-center mb-4">DCEEE Memo Updates</h2>
  </div>

  <div id="dceee-graphs" class="dceee-subtab hidden">
    <h2 class="text-xl font-semibold text-orange-600 text-center mb-4">DCEEE Graphs</h2>
  </div>

  <div id="dceee-about" class="dceee-subtab hidden">
    <h2 class="text-xl font-semibold text-orange-600 text-center mb-4">About DCEEE</h2>
  </div>
</div>

<script>
  document.querySelectorAll('.dceee-tab-btn').forEach(button => {
    button.addEventListener('click', () => {
      const target = button.getAttribute('data-subtab');

      document.querySelectorAll('.dceee-tab-btn').forEach(btn => {
        btn.classList.remove('text-orange-600', 'border-b-2', 'border-orange-600', 'font-bold');
        btn.classList.add('text-gray-700');
      });

      document.querySelectorAll('.dceee-subtab').forEach(tab => {
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
