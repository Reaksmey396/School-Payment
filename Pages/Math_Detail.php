<?php
include '../Components/Navbar.php';
include '../Categories/header.php';
?>

<section class="py-25 bg-gray-50">
  <div class="max-w-7xl mx-auto px-6">

    <!-- TITLE -->
    <div class="text-center mb-12">
      <h2 class="text-3xl md:text-4xl font-bold text-gray-900">
        Mathematics Department
      </h2>
      <p class="text-gray-500 mt-3 max-w-2xl mx-auto">
        Build strong analytical thinking, logical reasoning, and problem-solving skills for real-world success.
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
        <img src="https://images.unsplash.com/photo-1635070041078-e363dbe005cb"
             class="rounded-2xl shadow-xl w-full h-[400px] object-cover">

        <!-- FLOATING CARD -->
        <div class="absolute bottom-4 left-4 bg-white px-3 rounded-xl shadow-lg">
          <p class="text-sm relative top-3 text-blue-500">Tuition Fee</p>
          <p class="text-lg font-bold text-red-600">$400 / Semester</p>
        </div>
      </div>

      <!-- CONTENT -->
      <div>

        <h3 class="text-2xl font-bold text-gray-900 mb-4">
          Why Study Mathematics?
        </h3>

        <p class="text-gray-600 mb-6 leading-relaxed">
          The Mathematics Department develops students' abilities in logical thinking,
          analytical reasoning, and problem-solving. Mathematics is the foundation of science,
          technology, engineering, and many real-world applications.
        </p>

        <!-- FEATURES -->
        <div class="space-y-4">

          <div class="flex items-start gap-3">
            <div class="bg-indigo-100 text-indigo-600 p-2 rounded-lg">📐</div>
            <p class="text-gray-700 relative top-3 text-sm">
              Learn Algebra, Calculus, Statistics, and Geometry
            </p>
          </div>

          <div class="flex items-start gap-3">
            <div class="bg-blue-100 text-blue-600 p-2 rounded-lg">🧠</div>
            <p class="text-gray-700 relative top-3 text-sm">
              Improve logical thinking and analytical skills
            </p>
          </div>

          <div class="flex items-start gap-3">
            <div class="bg-green-100 text-green-600 p-2 rounded-lg">📊</div>
            <p class="text-gray-700 relative top-3 text-sm">
              Apply mathematics in data analysis and real-world problems
            </p>
          </div>

          <div class="flex items-start gap-3">
            <div class="bg-orange-100 text-orange-600 p-2 rounded-lg">💼</div>
            <p class="text-gray-700 relative top-3 text-sm">
              Careers: Data Analyst, Teacher, Statistician, Researcher
            </p>
          </div>

        </div>

      </div>

    </div>

    <!-- EXTRA CARDS -->
    <div class="grid md:grid-cols-3 gap-6 mt-16">

      <!-- CARD 1 -->
      <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition text-center">
        <img src="https://cdn-icons-png.flaticon.com/512/1048/1048953.png"
             class="w-14 mx-auto mb-4">
        <h4 class="font-bold mb-2">Strong Foundation</h4>
        <p class="text-gray-500 text-sm">
          Build essential knowledge for science and technology fields.
        </p>
      </div>

      <!-- CARD 2 -->
      <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition text-center">
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135768.png"
             class="w-14 mx-auto mb-4">
        <h4 class="font-bold mb-2">Critical Thinking</h4>
        <p class="text-gray-500 text-sm">
          Develop deep thinking and analytical problem-solving skills.
        </p>
      </div>

      <!-- CARD 3 -->
      <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition text-center">
        <img src="https://cdn-icons-png.flaticon.com/512/190/190411.png"
             class="w-14 mx-auto mb-4">
        <h4 class="font-bold mb-2">Career Opportunities</h4>
        <p class="text-gray-500 text-sm">
          Wide range of jobs in education, finance, and data science.
        </p>
      </div>

    </div>

    <!-- SAMPLE MATH VISUAL -->
    <div class="mt-20 text-center">
      <h3 class="text-2xl font-bold mb-6 text-gray-900">
        Example Concept in Mathematics
      </h3>

      <p class="text-gray-500 mb-6">
        Students will learn important formulas like quadratic equations:
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