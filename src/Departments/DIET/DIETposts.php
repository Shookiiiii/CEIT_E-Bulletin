<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DIET POSTS</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-800">

<div class="w-full">
  <nav class="flex justify-center space-x-10 border-b-2 border-gray-200 mb-6">
    <button class="diet-tab-btn text-orange-600 border-orange-600 hover:border-b-2  hover:border-orange-400 hover:text-orange-400 border-b-2 font-bold hover:font-bold pb-2" data-subtab="diet-announcement">Announcement</button>
    <button class="diet-tab-btn text-gray-700 hover:border-b-2 hover:border-orange-400 hover:text-orange-400 hover:font-bold pb-2" data-subtab="diet-memo">Memo Updates</button>
    <button class="diet-tab-btn text-gray-700 hover:border-b-2 hover:border-orange-400 hover:text-orange-400 hover:font-bold pb-2" data-subtab="diet-graphs">Graphs</button>
    <button class="diet-tab-btn text-gray-700 hover:border-b-2 hover:border-orange-400 hover:text-orange-400 hover:font-bold pb-2" data-subtab="diet-about">About DIET</button>
  </nav>

  <div id="diet-announcement" class="diet-subtab">
    <h2 class="text-xl font-semibold text-orange-600 text-center mb-4">DIET Announcements</h2>
  </div>

  <div id="diet-memo" class="diet-subtab hidden">
    <h2 class="text-xl font-semibold text-orange-600 text-center mb-4">DIET Memo Updates</h2>
  </div>

  <div id="diet-graphs" class="diet-subtab hidden">
    <h2 class="text-xl font-semibold text-orange-600 text-center mb-4">DIET Graphs</h2>
  </div>

  <div id="diet-about" class="diet-subtab hidden">
    <h2 class="text-xl font-semibold text-orange-600 text-center mb-4">About DIET</h2>
  </div>
</div>

<script>
  document.querySelectorAll('.diet-tab-btn').forEach(button => {
    button.addEventListener('click', () => {
      const target = button.getAttribute('data-subtab');

      document.querySelectorAll('.diet-tab-btn').forEach(btn => {
        btn.classList.remove('text-orange-600', 'border-b-2', 'border-orange-600', 'font-bold');
        btn.classList.add('text-gray-700');
      });

      document.querySelectorAll('.diet-subtab').forEach(tab => {
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
