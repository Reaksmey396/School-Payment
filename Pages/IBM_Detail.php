<?php
include '../Components/Navbar.php';
include '../Categories/header.php';
?>

<section class="py-16 bg-gray-50">
  <div class="max-w-7xl mx-auto px-6">

    <!-- TITLE -->
    <div class="text-center mb-12">
      <h2 class="text-3xl md:text-4xl font-bold text-gray-900">
        International Business Management (IBM)
      </h2>
      <p class="text-gray-500 mt-3 max-w-2xl mx-auto">
        Gain global business expertise, leadership, and strategic thinking for success in the international market.
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
        <img src="https://images.unsplash.com/photo-1507679799987-c73779587ccf"
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
          Why od we Study IBM?
        </h3>

        <p class="text-gray-600 mb-6 leading-relaxed">
          The IBM Department prepares students to excel in global business environments, 
          international trade, and strategic management. Develop leadership, communication, 
          and analytical skills for high-demand international careers.
        </p>

        <!-- FEATURES -->
        <div class="space-y-4">

          <div class="flex items-start gap-3">
            <div class="bg-blue-100 text-blue-600 p-2 rounded-lg">💻</div>
            <p class="text-gray-700 relative top-3 text-sm">
              Master International Trade, Marketing, Finance, and Management
            </p>
          </div>

          <div class="flex items-start gap-3">
            <div class="bg-green-100 text-green-600 p-2 rounded-lg">🔐</div>
            <p class="text-gray-700 relative top-3 text-sm">
              Analyze business cases and make strategic decisions
            </p>
          </div>

          <div class="flex items-start gap-3">
            <div class="bg-purple-100 text-purple-600 p-2 rounded-lg">🖥️</div>
            <p class="text-gray-700 relative top-3 text-sm">
              Enhance communication, teamwork, and leadership abilities
            </p>
          </div>

          <div class="flex items-start gap-3">
            <div class="bg-orange-100 text-orange-600 p-2 rounded-lg">🚀</div>
            <p class="text-gray-700 relative top-3 text-sm">
              Careers: Manager, Business Analyst, Entrepreneur, Marketing Executive
            </p>
          </div>

        </div>
      </div>

    </div>

    <!-- EXTRA CARDS -->
    <div class="grid md:grid-cols-3 gap-6 mt-16">
      
      <!-- CARD 1 -->
      <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-lg transition flex flex-col h-full group">
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135679.png" 
             class="w-14 mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
        <h4 class="font-bold mb-2 text-gray-900">Global Perspective</h4>
        <p class="text-gray-500 text-sm mb-4 flex-1">
          Understand international markets and global business trends.
        </p>
        <div class="mt-auto flex justify-end">
          <button class="bg-emerald-600 text-white text-xs px-4 py-2 rounded-lg hover:bg-emerald-700">
            Explore
          </button>
        </div>
      </div>

      <!-- CARD 2 -->
      <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-lg transition flex flex-col h-full group">
        <img src="https://cdn-icons-png.flaticon.com/512/1055/1055687.png" 
             class="w-14 mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
        <h4 class="font-bold mb-2 text-gray-900">Leadership Skills</h4>
        <p class="text-gray-500 text-sm mb-4 flex-1">
          Build strong leadership and management capabilities.
        </p>
        <div class="mt-auto flex justify-end">
          <button class="bg-emerald-600 text-white text-xs px-4 py-2 rounded-lg hover:bg-emerald-700">
            Explore
          </button>
        </div>
      </div>

      <!-- CARD 3 -->
      <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-lg transition flex flex-col h-full group">
        <img src="https://cdn-icons-png.flaticon.com/512/942/942748.png" 
             class="w-14 mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
        <h4 class="font-bold mb-2 text-gray-900">Career Growth</h4>
        <p class="text-gray-500 text-sm mb-4 flex-1">
          High-demand careers in business, finance, and international trade.
        </p>
        <div class="mt-auto flex justify-end">
          <button class="bg-emerald-600 text-white text-xs px-4 py-2 rounded-lg hover:bg-emerald-700">
            Explore
          </button>
        </div>
      </div>

    </div>

    <!-- BUSINESS HIGHLIGHT -->
    <div class="mt-20 text-center">
      <h3 class="text-2xl font-bold mb-6 text-gray-900">
        Real-World Business Experience
      </h3>
      <p class="text-gray-500 max-w-2xl mx-auto">
        Engage in case studies, business simulations, and international projects to prepare for leadership roles 
        in global companies.
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