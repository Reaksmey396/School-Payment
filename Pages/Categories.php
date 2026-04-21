<?php
include '../Components/Navbar.php';
include '../Categories/header.php';
?>

<style>
  /* ១. ទាញយក Font "Inter" ដែលជា Font លេខ ១ សម្រាប់ UI/UX Dashboard */
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

  /* ២. កំណត់ទៅលើ Body ឱ្យមានសោភ័ណភាពខ្ពស់ */
  body {
    font-family: 'Inter', sans-serif !important;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-rendering: optimizeLegibility;
    color: #1e293b; /* ពណ៌ Slate-800 ជួយឱ្យអក្សរមើលទៅទន់ភ្នែកជាងពណ៌ខ្មៅសុទ្ធ */
  }

  /* ៣. ការកំណត់ចំណងជើង (Headings) ឱ្យមើលទៅ "Premium" */
  h1, h2, h3, .font-bold {
    font-family: 'Inter', sans-serif;
    font-weight: 700 !important;
    letter-spacing: -0.04em !important; /* គាបអក្សរឱ្យជិតគ្នា បង្កើតអារម្មណ៍ទំនើប */
    line-height: 1.1;
  }

  /* ៤. ការកំណត់អត្ថបទធម្មតា (Paragraph) */
  p {
    line-height: 1.6;
    letter-spacing: -0.01em;
    color: #475569; /* ពណ៌ Slate-600 */
  }

  /* ៥. កែសម្រួល Font ក្នុង Form និង Button ឱ្យត្រូវគ្នា */
  input, button, select, textarea {
    font-family: 'Inter', sans-serif !important;
    letter-spacing: -0.01em;
  }
</style>

<div class="max-w-7xl relative mx-auto px-6 py-30">

  <!-- TITLE -->
  <h1 class="text-3xl mt-4 font-bold">
    Explore <span class="text-blue-600">Departments</span>
  </h1>

  <!-- GRID -->
  <div class="grid md:grid-cols-2 mt-6 lg:grid-cols-3 gap-6">

    <!-- CARD 1 -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-lg transition">

      <!-- IMAGE -->
      <div class="h-40 overflow-hidden">
        <img src="https://i.pinimg.com/1200x/41/2e/a7/412ea792b6963690a4a9dce67b73f216.jpg"
             class="w-full h-full object-cover hover:scale-110 transition duration-300">
      </div>

      <!-- CONTENT -->
      <div class="p-5">

        <h2 class="text-lg font-bold text-gray-900 mb-1">
          Computer Science
        </h2>

        <p class="text-blue-600 text-sm font-semibold mb-3">
          $450 / Year
        </p>

        <p class="text-gray-500 text-sm mb-4">
          Learn programming, Cybersecurity, web development, and AI technologies.
        </p>

        <!-- FOOTER -->
        <div class="flex justify-end mt-auto">


          <a href="../Pages/CS_Detail.php" class="bg-blue-600 no-underline text-white text-xs px-4 py-2 rounded-lg hover:bg-blue-700">
            Explore
          </a>

        </div>

      </div>
    </div>
    <!-- CARD 2 -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-lg transition">

      <!-- IMAGE -->
      <div class="h-40 overflow-hidden">
        <img src="https://i.pinimg.com/736x/e2/31/4e/e2314e7700e94d5a14adeb759fb1b029.jpg"
             class="w-full h-full object-cover hover:scale-110 transition duration-300">
      </div>

      <!-- CONTENT -->
      <div class="p-5">

        <h2 class="text-lg font-bold text-gray-900 mb-1">
          English Literature
        </h2>

        <p class="text-blue-600 text-sm font-semibold mb-3">
          $350 / Year
        </p>

        <p class="text-gray-500 text-sm mb-4">
          Learn English, Speaking, Writing, Listening, and Practice in face to face.
        </p>

        <!-- FOOTER -->
        <div class="flex justify-end mt-auto">


          <a href="../Pages/English_Detail.php" class="bg-blue-600 no-underline text-white text-xs px-4 py-2 rounded-lg hover:bg-blue-700">
            Explore
          </a>

        </div>

      </div>
    </div>
    <!-- CARD 3 -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-lg transition">

      <!-- IMAGE -->
      <div class="h-40 overflow-hidden">
        <img src="https://i.pinimg.com/736x/18/1d/9c/181d9c6905a0a730588287c620e8cd1b.jpg"
             class="w-full h-full object-cover hover:scale-110 transition duration-300">
      </div>

      <!-- CONTENT -->
      <div class="p-5">

        <h2 class="text-lg font-bold text-gray-900 mb-1">
          IMB
        </h2>

        <p class="text-blue-600 text-sm font-semibold mb-3">
          $500 / Year
        </p>

        <p class="text-gray-500 text-sm mb-4">
          Learn for understanding about plant grow, food, learn to know how to grow the pant.
        </p>

        <!-- FOOTER -->
        <div class="flex justify-end mt-auto">


          <a href="../Pages/IBM_Detail.php" class="bg-blue-600 no-underline text-white text-xs px-4 py-2 rounded-lg hover:bg-blue-700">
            Explore
          </a>

        </div>

      </div>
    </div>

    <!-- CARD 4 -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-lg transition">

      <div class="h-40 overflow-hidden">
        <img src="https://i.pinimg.com/736x/ed/7a/50/ed7a50f18ec965ed6b5931ea543cf542.jpg"
             class="w-full h-full object-cover hover:scale-110 transition duration-300">
      </div>

      <div class="p-5">

        <h2 class="text-lg font-bold text-gray-900 mb-1">
          Physics 
        </h2>

        <p class="text-blue-600 text-sm font-semibold mb-3">
          $400 / Year
        </p>

        <p class="text-gray-500 text-sm mb-4">
          Improvement for know about processing of what physics do and practice lab.
        </p>

        <div class="flex justify-end mt-auto">

          <a href="../Pages/Physic_Detail.php" class="bg-blue-600 no-underline text-white text-xs px-4 py-2 rounded-lg hover:bg-blue-700">
            Explore
          </a>

        </div>

      </div>
    </div>

    <!-- CARD 5 -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-lg transition">

      <div class="h-40 overflow-hidden">
        <img src="https://i.pinimg.com/1200x/f9/01/bb/f901bb71e28c559d90257a4a3880e773.jpg"
             class="w-full h-full object-cover hover:scale-110 transition duration-300">
      </div>

      <div class="p-5">

        <h2 class="text-lg font-bold text-gray-900 mb-1">
          ITE
        </h2>

        <p class="text-blue-600 text-sm font-semibold mb-3">
          $600 / Year
        </p>

        <p class="text-gray-500 text-sm mb-4">
          Study about Programming, Network, Device, and design UI for user see.
        </p>

        <div class="flex justify-end mt-auto">

          <a href="../Pages/ITE_detail.php" class="bg-blue-600 no-underline text-white text-xs px-4 py-2 rounded-lg hover:bg-blue-700">
            Explore
          </a>

        </div>

      </div>
      
    </div>
    <!-- CARD 6 -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-lg transition">

      <div class="h-40 overflow-hidden">
        <img src="https://i.pinimg.com/1200x/40/3e/c0/403ec05565c57d800ac7d7747aa9d29a.jpg"
             class="w-full h-full object-cover hover:scale-110 transition duration-300">
      </div>

      <div class="p-5">

        <h2 class="text-lg font-bold text-gray-900 mb-1">
          Mathematics
        </h2>

        <p class="text-blue-600 text-sm font-semibold mb-3">
          $400 / Year
        </p>

        <p class="text-gray-500 text-sm mb-4">
          Learn Calculate and analyze exercise and problem for teaching or somethng.
        </p>

        <div class="flex justify-end mt-auto">

          <a href="../Pages/Math_Detail.php" class="bg-blue-600 no-underline text-white text-xs px-4 py-2 rounded-lg hover:bg-blue-700">
            Explore
          </a>

        </div>

      </div>
    </div>

</div>

<?php
include '../Components/Footer.php';
include '../Categories/footer.php';
?>