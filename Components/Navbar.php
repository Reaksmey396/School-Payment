<?php
include '../Categories/header.php';
?>

<!-- Navbar -->
<nav class="fixed top-0 left-0 right-0 z-50">
  <div class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-6 py-3 flex justify-between items-center">
      <div class="flex items-center cursor-pointer gap-2">
        <img src="https://upload.wikimedia.org/wikipedia/en/a/a2/RUPP_logo.PNG" width="40px" height="40px" alt="">
        <span class="font-semibold fw-bold text-3xl uppercase text-red-500">RUPP<span class="text-blue-500">Pay</span></span>
      </div>
      <ul class="hidden md:flex relative top-2 gap-10">
        <li>
          <a href="../Pages/Home.php" class="hover:text-blue-600 no-underline  text-gray-900">Home</a>
        </li>
        <li>
          <a href="../Pages/Categories.php" class="hover:text-blue-600 no-underline  text-gray-900">Categories</a>
        </li>
        <li>
          <a href="../Pages/About.php" class="hover:text-blue-600 no-underline  text-gray-900">About</a>
        </li>
        <li>
          <a href="../Pages/Contact.php" class="hover:text-blue-600 no-underline  text-gray-900">Contact Us</a>
        </li>
      </ul>
      <div class="hidden md:flex items-center gap-3">
        <?php
          // For Session code
          if (session_status() === PHP_SESSION_NONE) {
              session_start();
          }
          if (isset($_SESSION['is_admin'])) {
              echo '
                  <a href="../Components/logout.php" class="text-gray-100 hover:bg-red-400 bg-red-600 float-end no-underline px-4 py-2 rounded-lg font-medium cursor-pointer">Logout</a>
              ';
          } else {
              echo '
                  <a href="../Components/Login.php" class="text-blue-500 hover:bg-blue-400 hover:text-gray-50 bg-gray-50 px-4 no-underline float-end py-2 rounded-lg font-medium cursor-pointer">Login</a>
                  <a href="../Components/Register.php" class="text-gray-100 hover:bg-blue-500 bg-blue-600 no-underline px-4 float-end py-2 rounded-lg font-medium cursor-pointer">Register</a>
              ';
          }
          ?>
      </div>

      <div class="block md:hidden text-slate-600 cursor-pointer">
        <i id="openSidebar" class="fa-solid fa-bars text-4xl "></i>
      </div>
    </div>
  </div>
  <div id="sidebarWrapper" class="fixed inset-0 z-[100] hidden">

    <div id="overlay" class="absolute inset-0 bg-black/40 backdrop-blur-[2px] transition-opacity duration-300"></div>
    <div id="sidebarPanel" class="absolute left-0 top-0 h-full w-[85%] max-w-[320px] bg-white shadow-2xl transition-transform duration-300 transform -translate-x-full flex flex-col">
      <div class="p-6">
        <div id="closeSidebar" class="text-2xl text-gray-400 cursor-pointer hover:text-red-500 w-fit mb-6">
          <i class="fa-solid fa-xmark"></i>
        </div>

        <div class="flex items-center gap-2 mb-8">
          <div class="font-bold text-2xl tracking-tighter text-red-500 uppercase">
            RUPP<span class="text-blue-500">Pay <span class="text-white">System</span></span>
          </div>
        </div>
      </div>

      <div class="flex-1 overflow-y-auto px-4">
        <div class="space-y-1">
          <div class="flex items-center gap-4 px-4 py-3 text-slate-700 hover:bg-slate-50 rounded-lg cursor-pointer transition group">
            <i class="fa-regular fa-comment text-xl w-6"></i>
            <a href="../Pages/Home.php" class="font-medium text-slate-700 no-underline text-[15px]">Home</a>
          </div>

          <div class="flex items-center gap-4 px-4 py-3 text-slate-700 hover:bg-slate-50 rounded-lg cursor-pointer transition">
            <i class="fa-regular fa-chart-bar text-xl w-6"></i>
            <a href="../Pages/" class="font-medium text-slate-700 no-underline text-[15px]">Categories</a>
          </div>

          <div class="flex items-center gap-4 px-4 py-3 text-slate-700 hover:bg-slate-50 rounded-lg cursor-pointer transition">
            <i class="fa-regular fa-rectangle-list text-xl w-6"></i>
            <a href="../Pages/About.php" class="font-medium text-slate-700 no-underline text-[15px]">About</a>
          </div>

          <div class="flex items-center gap-4 px-4 py-3 text-slate-700 hover:bg-slate-50 rounded-lg cursor-pointer transition border-b border-gray-100 pb-4">
            <i class="fa-regular fa-bookmark text-xl w-6"></i>
            <a href="../Pages/Contact.php" class="font-medium text-slate-700 no-underline text-[15px]">Contact Us</a>
          </div>

          <div class="pt-4 flex items-center gap-4 px-4 py-3 text-slate-700 hover:bg-slate-50 rounded-lg cursor-pointer transition">
            <i class="fa-regular fa-circle-question text-xl w-6"></i>
            <a href="../Pages/Contact.php" class="font-medium text-slate-700 no-underline text-[15px]">Help</a>
          </div>

          <div class="flex items-center gap-4 px-4 py-3 text-orange-500 hover:bg-orange-50 rounded-lg cursor-pointer transition">
            <i class="fa-solid fa-gift text-xl w-6"></i>
            <div class="font-medium text-slate-700 text-[15px]">Get $20</div>
          </div>
        </div>

        <div class="mt-4 ml-5 grid-cols-1">
          <button id="login" class="bg-gray-300 py-2 hover:bg-gray-400 px-3 rounded-lg">Login</button>
        </div>
      </div>
    </div>
  </div>
</nav>

<script>
  const sidebarWrapper = document.getElementById('sidebarWrapper');
  const sidebarPanel = document.getElementById('sidebarPanel');
  const openBtn = document.getElementById('openSidebar');
  const closeBtn = document.getElementById('closeSidebar');
  const overlay = document.getElementById('overlay');

  // Open Sidebar
  function toggleSidebar(isOpen) {
    if (isOpen) {
      sidebarWrapper.classList.remove('hidden');
      // Add delay a little bit to make transition animation process
      setTimeout(() => {
        sidebarPanel.classList.remove('-translate-x-full');
      }, 10);
    } else {
      sidebarPanel.classList.add('-translate-x-full');
      // hide Wrapper after animation end (300ms)
      setTimeout(() => {
        sidebarWrapper.classList.add('hidden');
      }, 300);
    }
  }

  openBtn.addEventListener('click', () => toggleSidebar(true));
  closeBtn.addEventListener('click', () => toggleSidebar(false));
  overlay.addEventListener('click', () => toggleSidebar(false));

  // button Login
  const btn = document.getElementById('login');

  btn.addEventListener('click', () => {
      window.location.href = "../Components/Login.php"; 
  });

  // button Register
  const btns = document.getElementById('register');

  btns.addEventListener('click', () => {
      window.location.href = "../Components/register.php"; 
  });
</script>
<?php
include '../Categories/footer.php';
?>
