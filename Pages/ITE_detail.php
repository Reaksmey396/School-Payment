<?php
include '../Components/Navbar.php';
include '../Categories/header.php';
?>

<section class="py-25 bg-gray-50">
  <div class="max-w-7xl mx-auto px-6">

    <!-- TITLE -->
    <div class="text-center mb-12">
      <h2 class="text-3xl md:text-4xl font-bold text-gray-900">
        Information Technology Engineering (ITE)
      </h2>
      <p class="text-gray-500 mt-3 max-w-2xl mx-auto">
        Master IT systems, programming, cybersecurity, and network technologies for the digital era.
      </p>
    </div>
    <div class="absolute top-10 left-10 z-20">
    <button onclick="back()" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2.5 rounded-xl top-15 relative right-5 fw-bold shadow-lg group">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
      </svg>
      Back
    </button>
  </div>

    <!-- MAIN GRID -->
    <div class="grid md:grid-cols-2 gap-10 items-center">

      <!-- IMAGE -->
      <div class="relative">
        <img src="https://i.pinimg.com/1200x/6e/7c/12/6e7c12ec3d4bafa4b65199609078f476.jpg"
             class="rounded-2xl shadow-xl w-full h-[400px] object-cover">

        <!-- FLOATING CARD -->
        <div class="absolute bottom-4 left-4 bg-white px-3 rounded-xl shadow-lg">
          <p class="text-sm relative top-3 text-blue-500">Tuition Fee</p>
          <p class="text-lg font-bold text-red-600">$600 / Semester</p>
        </div>
      </div>

      <!-- CONTENT -->
      <div>
        <h3 class="text-2xl font-bold text-gray-900 mb-4">
          Why Study Information Technology Engineering?
        </h3>

        <p class="text-gray-600 mb-6 leading-relaxed">
          The ITE Department equips students with knowledge in software development, networking, cybersecurity,
          and system design. Students gain practical skills for IT careers in software, hardware, and digital solutions.
        </p>

        <!-- FEATURES -->
        <div class="space-y-4">

          <div class="flex items-start gap-3">
            <div class="bg-blue-100 text-blue-600 p-2 rounded-lg">💻</div>
            <p class="text-gray-700 relative top-3 text-sm">
              Learn programming, software development, and web/mobile applications
            </p>
          </div>

          <div class="flex items-start gap-3">
            <div class="bg-green-100 text-green-600 p-2 rounded-lg">🔐</div>
            <p class="text-gray-700 relative top-3 text-sm">
              Explore cybersecurity, networks, and secure system design
            </p>
          </div>

          <div class="flex items-start gap-3">
            <div class="bg-purple-100 text-purple-600 p-2 rounded-lg">🖥️</div>
            <p class="text-gray-700 relative top-3 text-sm">
              Develop problem-solving, teamwork, and IT project management skills
            </p>
          </div>

          <div class="flex items-start gap-3">
            <div class="bg-orange-100 text-orange-600 p-2 rounded-lg">🚀</div>
            <p class="text-gray-700 relative top-3 text-sm">
              Careers: Software Engineer, Network Administrator, Cybersecurity Analyst
            </p>
          </div>

        </div>
      </div>

    </div>

    <!-- EXTRA CARDS -->
    <div class="grid md:grid-cols-3 gap-6 mt-16">

      <!-- CARD 1 -->
      <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition text-center">
        <img src="https://cdn-icons-png.flaticon.com/512/1051/1051277.png"
             class="w-14 mx-auto mb-4">
        <h4 class="font-bold mb-2">Software Expertise</h4>
        <p class="text-gray-500 text-sm">
          Gain hands-on experience in coding, development, and software design.
        </p>
      </div>

      <!-- CARD 2 -->
      <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition text-center">
        <img src="https://cdn-icons-png.flaticon.com/512/1041/1041886.png"
             class="w-14 mx-auto mb-4">
        <h4 class="font-bold mb-2">Cybersecurity & Networks</h4>
        <p class="text-gray-500 text-sm">
          Learn to protect systems, manage networks, and ensure IT security.
        </p>
      </div>

      <!-- CARD 3 -->
      <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition text-center">
        <img src="https://cdn-icons-png.flaticon.com/512/4149/4149313.png"
             class="w-14 mx-auto mb-4">
        <h4 class="font-bold mb-2">IT Career Path</h4>
        <p class="text-gray-500 text-sm">
          Prepare for in-demand roles in IT, software, and digital industries.
        </p>
      </div>

    </div>

    <!-- BUSINESS HIGHLIGHT -->
    <div class="mt-20 text-center">
      <h3 class="text-2xl font-bold mb-6 text-gray-900">
        Practical IT Projects
      </h3>

      <p class="text-gray-500 max-w-2xl mx-auto">
        Students engage in software development projects, cybersecurity labs, and networking simulations 
        to gain hands-on experience for real-world IT roles.
      </p>
    </div>

  </div>
</section>

<script>
  function back(){
    window.history.back()
  }
</script>

<?php
include '../Components/Footer.php';
?>