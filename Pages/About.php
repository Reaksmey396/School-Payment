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
<script>
  // បង្កើត Variable ក្នុង JS ដើម្បីស្គាល់ស្ថានភាព Login ពី PHP
  const isLoggedIn = <?php echo isset($_SESSION['is_admin']) ? 'true' : 'false'; ?>;
  const userRole = <?php echo isset($_SESSION['is_admin']) ? (int) $_SESSION['is_admin'] : 0; ?>;
  const paymentPageUrl = userRole === 2 ? '../Pages/Stu_dashoard.php' : '../Pages/Dashboards.php';
</script>

<!-- ================= ABOUT HERO ================= -->
<!-- HERO SECTION -->
<section class="bg-blue-800 relative top-15 text-white py-16">
  <div class="max-w-6xl mx-auto px-6 text-center">

    <h1 class="text-2xl md:text-4xl font-bold mb-4">
      Empowering Education through Modern Payments
    </h1>

    <p class="text-blue-100 max-w-2xl mx-auto">
      At RUPP-PAY, we believe that administrative tasks should never stand in the way
      of a student's learning journey.
    </p>

  </div>
</section>

<!-- ABOUT / MISSION SECTION -->
<section class="py-10 bg-gray-50">
  <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-center">

    <!-- Image -->
    <div>
      <img
        src="https://i.pinimg.com/736x/4f/fd/3c/4ffd3cb2376522b88092a85349673375.jpg"
        class="rounded-xl mt-5 h-[400px] shadow-lg w-full">
    </div>

    <!-- Text -->
    <div>
      <h2 class="text-2xl font-semibold mb-3">
        Our Mission
      </h2>
      <p class="text-gray-600 mb-6">
        Our school's mission is to provide a world-class education that fosters
        intellectual growth, creativity, and integrity. We are committed to creating
        an environment where every student can excel.
      </p>
      <h2 class="text-2xl font-semibold mb-3">
        The Purpose of RUPP-PAY
      </h2>

      <p class="text-gray-600">
        We developed this fee portal to streamline the financial interactions
        between our institution and parents. By digitizing fee payments, we aim
        to provide transparency, reduce manual errors, and offer a convenient,
        24/7 payment solution that fits into your busy lives.
      </p>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="py-16 bg-gray-100">
  <div class="max-w-7xl mx-auto px-6 text-center">
    <h2 class="text-2xl md:text-3xl font-bold mb-2">
      Why Use Our Portal?
    </h2>
    <p class="text-gray-500 mb-10">
      Designed with security and user experience at its core.
    </p>
    <div class="grid md:grid-cols-3 gap-8">

      <!-- Card 1 -->
      <div class="bg-white p-6 rounded-xl shadow-sm">
        <div class="text-blue-600 text-3xl mb-3">
          <i class="fa-solid fa-shield"></i>
        </div>
        <h3 class="font-semibold mb-2">
          Secure Transactions
        </h3>
        <p class="text-gray-500 text-sm">
          Your financial data is encrypted with bank-grade security protocols
          to ensure every payment is safe.
        </p>
      </div>

      <!-- Card 2 -->
      <div class="bg-white p-6 rounded-xl shadow-sm">
        <div class="text-green-600 text-3xl mb-3">
          <i class="fa-solid fa-receipt"></i>
        </div>

        <h3 class="font-semibold mb-2">
          Instant Receipts
        </h3>
        <p class="text-gray-500 text-sm">
          No more waiting. Download your digital receipt immediately after
          a successful transaction.
        </p>
      </div>

      <!-- Card 3 -->
      <div class="bg-white p-6 rounded-xl shadow-sm">
        <div class="text-purple-600 text-3xl mb-3">
          <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <h3 class="font-semibold mb-2">
          History Tracking
        </h3>
        <p class="text-gray-500 text-sm">
          Pay school fees securely from home or on the go without visiting office.
        </p>
      </div>
      <!-- Card 4 -->
      <div class="bg-white p-6 rounded-xl shadow-sm">
        <div class="text-orange-600 text-3xl mb-3">
          <i class="fa-regular fa-file"></i>
        </div>
        <h3 class="font-semibold mb-2">
          Total Convenience
        </h3>
        <p class="text-gray-500 text-sm">
          Receive automated notifications before due dates to avoid any late payment school penalties.
        </p>
      </div>
      <!-- Card 5 -->
      <div class="bg-white p-6 rounded-xl shadow-sm">
        <div class="text-yellow-600 text-3xl mb-3">
          <i class="fa-solid fa-trophy"></i>
        </div>
        <h3 class="font-semibold mb-2">
          Smart Reminders
        </h3>
        <p class="text-gray-500 text-sm">
          Choose from multiple trusted banking methods like ABA ACLEDA and Bakong for flexibility
        </p>
      </div>
      <!-- Card 6 -->
      <div class="bg-white p-6 rounded-xl shadow-sm">
        <div class="text-gray-600 text-3xl mb-3">
          <i class="fa-solid fa-key"></i>
        </div>
        <h3 class="font-semibold mb-2">
          Flexible Options
        </h3>
        <p class="text-gray-500 text-sm">
          Enjoy the flexibility of choosing from various payment methods, including ABA, ACLEDA, and Bakong.
        </p>
      </div>
    </div>
  </div>
</section>


<!-- ================= MISSION & VISION ================= -->
<section class="py-24 bg-gray-100">

  <!-- Title -->
  <div class="text-center mb-16">
    <h2 class="text-4xl font-bold text-gray-800">
      Online Activities
    </h2>
    <p class="text-gray-600 mt-4">
      Our purpose and future direction for improving the school payment system.
    </p>
  </div>

  <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-12">

    <div class="bg-gray-200 p-10 rounded-2xl">
      <h3 class="text-2xl font-bold text-blue-600 mb-4">
        Our Mission
      </h3>

      <p class="text-gray-700">
        Our mission is to provide educational institutions with a
        secure and efficient payment platform that simplifies
        financial transactions for students and administrators.
      </p>

    </div>

    <div class="bg-gray-200 p-10 rounded-2xl">
      <h3 class="text-2xl font-bold text-purple-600 mb-4">
        Our Vision
      </h3>

      <p class="text-gray-700">
        We aim to create a fully digital campus ecosystem where
        payments, records, and services are integrated into
        one smart platform.
      </p>
    </div>
  </div>
</section>

<!-- HOW TO PAY -->
<section class="bg-gray-100 py-16 px-4 sm:px-10 border-t border-gray-100">
  <div class="max-w-5xl mx-auto">
    <div class="text-center mb-12">
      <span class="text-blue-500 text-xs font-bold tracking-widest uppercase bg-blue-50 px-4 py-1.5 rounded-full">Simple Process</span>
      <h2 class="font-nunito font-black text-3xl sm:text-4xl text-slate-800 mt-3">How to Pay in 3 Easy Steps</h2>
      <p class="text-slate-500 text-sm mt-2">Follow these steps to complete your payment quickly and securely.</p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 relative">
      <div class="hidden sm:block absolute top-10 left-[calc(16.5%+2rem)] right-[calc(16.5%+2rem)] h-0.5 bg-blue-100 z-0"></div>
      <div class="flex flex-col items-center text-center relative z-10">
        <div class="w-20 h-20 rounded-3xl bg-blue-600 flex items-center justify-center shadow-xl shadow-blue-200 mb-5">
          <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        </div>
        <span class="text-xs font-black text-blue-400 tracking-widest">STEP 01</span>
        <h3 class="font-bold text-slate-800 text-lg mt-1 mb-2">Log In</h3>
        <p class="text-slate-500 text-sm">Use your student ID and password to access the payment portal securely.</p>
      </div>
      <div class="flex flex-col items-center text-center relative z-10">
        <div class="w-20 h-20 rounded-3xl bg-yellow-400 flex items-center justify-center shadow-xl shadow-yellow-200 mb-5">
          <svg class="w-9 h-9 text-blue-900" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
          </svg>
        </div>
        <span class="text-xs font-black text-yellow-500 tracking-widest">STEP 02</span>
        <h3 class="font-bold text-slate-800 text-lg mt-1 mb-2">Select Fees</h3>
        <p class="text-slate-500 text-sm">Choose from tuition, miscellaneous, ID, or other school-related fees to pay.</p>
      </div>
      <div class="flex flex-col items-center text-center relative z-10">
        <div class="w-20 h-20 rounded-3xl bg-green-500 flex items-center justify-center shadow-xl shadow-green-200 mb-5">
          <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <span class="text-xs font-black text-green-500 tracking-widest">STEP 03</span>
        <h3 class="font-bold text-slate-800 text-lg mt-1 mb-2">Confirm & Pay</h3>
        <p class="text-slate-500 text-sm">Complete your payment and receive an instant e-receipt to your email address.</p>
      </div>
    </div>
  </div>
</section>

<!-- ================= TEAM ================= -->
<section class="bg-gray-100 py-24">

  <div class="max-w-5xl mx-auto px-6 text-center">

    <h2 class="text-4xl font-bold text-gray-900 mb-6">
      Our Development Team
    </h2>

    <p class="text-gray-700 mb-16">
      Meet the team behind the School Payment System.
    </p>

    <!-- GRID -->
    <div class="grid md:grid-cols-2 gap-10">

      <!-- PROJECT MANAGER (TOP CENTER) -->
      <div class="md:col-span-2 flex justify-center">

        <div class="bg-gray-100 shadow-lg p-8 rounded-2xl w-80 ">

          <img src="https://randomuser.me/api/portraits/men/32.jpg"
            class="w-24 h-24 rounded-full mx-auto mb-4 object-cover">

          <h3 class="text-xl text-gray-900 font-bold">
            Hun Sovansoreya
          </h3>

          <p class="text-blue-600 text-sm">
            Project Manager
          </p>

          <p class="text-gray-700 text-sm mt-3">
            Leads the project planning and system development process.
          </p>

        </div>

      </div>


      <!-- FULL STACK -->
      <div class="flex justify-center">

        <div class="bg-gray-100 shadow-lg p-8 rounded-2xl w-80 ">

          <img src="https://i.pinimg.com/736x/7a/62/4b/7a624bc5e54b724307556617e6af5bfd.jpg"
            class="w-24 h-24 rounded-full mx-auto mb-4 object-cover">

          <h3 class="text-xl text-gray-900 font-bold">
            Khim Reaksmey
          </h3>

          <p class="text-blue-600 text-sm">
            Full Stack Developer
          </p>
          <p class="text-gray-700 text-sm mt-3">
            Responsible for building the payment system and database.
          </p>
        </div>
      </div>

      <!-- UI UX -->
      <div class="flex justify-center">

        <div class="bg-gray-100 shadow-lg p-8 rounded-2xl w-80">

          <img src="https://i.pinimg.com/736x/e6/f9/20/e6f920396ac6c32ffb58e8ac99ecfff0.jpg"
            class="w-24 h-24 rounded-full mx-auto mb-4 object-cover">

          <h3 class="text-xl text-gray-900 font-bold">
            Khan Odom
          </h3>

          <p class="text-blue-600 text-sm">
            UI / UX Designer
          </p>

          <p class="text-gray-700 text-sm mt-3">
            Designs modern interfaces and improves user experience.
          </p>

        </div>

      </div>

    </div>

  </div>

</section>

<!-- HELP SECTION -->
<section class="py-16 bg-gray-50">
  <div class="max-w-7xl mx-auto px-6">
    <div class="bg-gray-200 rounded-xl p-8 md:flex items-center justify-between">
      <div>
        <h3 class="text-xl font-semibold mb-2">
          Need Assistance?
        </h3>
        <p class="text-gray-600 mb-4">
          Our dedicated support team is here to help you with any payment-related
          queries or technical difficulties you might encounter.
        </p>
        <p>
          <i class="fa-regular fa-envelope text-xl"></i>
          <span class="text-sm relative -top-1 left-1 text-gray-700">
            support@rupppayportal.edu
          </span>
        </p>
        <p>
          <i class="fa-solid fa-phone"></i>
          <span class="text-sm relative left-1 text-gray-700">
            +885 389 329
          </span>
        </p>
      </div>

      <div class="mt-6 md:mt-0">
        <button class="bg-blue-600 text-white px-7 py-3 rounded-lg shadow hover:bg-blue-700">
          Visit Help Center
        </button>
      </div>

    </div>

  </div>

</section>

<div class="w-full">
  <!-- Code Paying -->
  <div id="paymentForm" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-4">

    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeModal()"></div>

    <div class="relative bg-white rounded-2xl w-full max-w-lg shadow-2xl animate-fadeUp overflow-hidden">

      <div class="flex mx-3 items-center justify-between p-3 border-b border-gray-100">
        <h2 class="text-2xl md:text-4xl font-bold text-gray-800 tracking-tight">Payment Form</h2>
        <button onclick="closeModal()" class="text-gray-400 hover:text-red-500 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <div class="p-6 mx-3">
        <form action="#" method="POST" class="space-y-4">

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-bold text-gray-500  mb-1">Student ID</label>
              <input type="text" required placeholder="Enter Student ID..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>
            <div>
              <label class="block text-[10px] font-bold text-gray-500  mb-1">Gender</label>
              <select class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                <option>Male</option>
                <option>Female</option>
                <option>Other</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-[10px] font-bold text-gray-500  mb-1">Full Name</label>
            <input type="text" required placeholder="Enter your full name..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
          </div>

          <div>
            <label class="block text-[10px] font-bold text-gray-500  mb-1">Student Email</label>
            <input type="email" required placeholder="Enter Student Email..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
          </div>

          <div class="bg-gray-50 h-[50px] w-full rounded-lg text-center border border-dashed border-gray-200">
            <p class="text-[10px] font-bold relative top-1 text-gray-400 ">Payment Date</p>
            <p class="text-sm relative -top-2 font-semibold text-gray-700">20 / 10 / 2026</p>
          </div>

          <div>
            <label class="block text-[10px] font-bold text-gray-500  mb-3">Choose Bank</label>
            <div class="grid grid-cols-3 gap-3">
              <label class="cursor-pointer group">
                <input type="radio" name="bank" value="bakong" class="hidden peer" required>
                <div class="rounded-xl p-2 border-2 border-gray-100 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all flex items-center justify-center h-14">
                  <img class="h-8 object-contain" src="https://media.licdn.com/dms/image/v2/C5612AQFP1BrbSZ0jAg/article-cover_image-shrink_600_2000/article-cover_image-shrink_600_2000/0/1607185380032?e=2147483647&v=beta&t=h5Ibwns6vQhWdQou1PI2aaFWz5_Wy2vs_Atp3xA-pUw" alt="Bakong">
                </div>
              </label>

              <label class="cursor-pointer group">
                <input type="radio" name="bank" value="aceleda" class="hidden peer">
                <div class="rounded-xl p-2 border-2 border-gray-100 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all flex items-center justify-center h-14">
                  <img class="h-8 object-contain" src="https://play-lh.googleusercontent.com/weU8O2dHEQffcEyHeK11qTUMS-AQvlHW1IolQDM1XLuZN0ggl6Zr9kUwBqHwXr7i5T0=w600-h300-pc0xffffff-pd" alt="ACLEDA">
                </div>
              </label>

              <label class="cursor-pointer group">
                <input type="radio" name="bank" value="aba" class="hidden peer">
                <div class="rounded-xl p-2 border-2 border-gray-100 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all flex items-center justify-center h-14">
                  <img class="h-8 rounded-md object-contain" src="https://play-lh.googleusercontent.com/WU6sZMD1UspzwqYnlACtmN60rckp8hoINSgsR21mKLJBbsHPwXtzwvOocpjC7FcO1g" alt="ABA">
                </div>
              </label>
            </div>
          </div>

          <button type="submit" class="w-full mt-4 bg-blue-700 hover:bg-blue-600 text-white font-bold py-3 rounded-xl shadow-lg transition duration-300 active:scale-95 text-sm ">
            Confirm & Pay Now
          </button>
        </form>
      </div>
    </div>
  </div>

  <script>
    // button back
    function back() {
      window.history.back()
    }
    // tilte animation
    document.addEventListener("DOMContentLoaded", function() {

      let text = "Smart & Secure School Fee Payment System";
      let i = 0;
      let isDeleting = false;

      function type() {

        let element = document.getElementById("typing");

        if (!isDeleting) {
          element.innerHTML = text.substring(0, i);
          i++;

          if (i > text.length) {
            isDeleting = true;
            setTimeout(type, 1500);
            return;
          }

        } else {

          element.innerHTML = text.substring(0, i);
          i--;

          if (i < 0) {
            isDeleting = false;
            i = 0;
          }
        }

        setTimeout(type, isDeleting ? 50 : 100);
      }

      type();

    });

    // ២. មុខងារសម្រាប់បើក និងបិទ Modal (សម្រួលឱ្យប្រើតែមួយ)
    function toggleModal(show) {
      const modal = document.getElementById('paymentForm');
      // ចំណាំ៖ ក្នុង HTML របស់អ្នកមិនមាន ID="modalContent" នៅក្នុង paymentForm ទេ 
      // ដូច្នេះយើងប្រើការលាក់/បង្ហាញធម្មតា ឬប្រើ animation fadeUp ដែលអ្នកមានស្រាប់

      if (show) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // បិទ scroll ក្រោយ
      } else {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto'; // បើក scroll វិញ
      }
    }

    // select Bank
    document.querySelector('#paymentForm form').addEventListener('submit', function(e) {
      const selectedBank = document.querySelector('input[name="bank"]:checked');

      if (!selectedBank) {
        e.preventDefault(); // Stop the form from submitting
        alert("Please select a bank before proceeding with the payment.");
      } else {
        // Optional: Show which bank was selected for confirmation
        alert("Proceeding with: " + selectedBank.value);
      }
    });

    // Command Modal
    function openModal() {
      const modal = document.getElementById("paymentForm");
      modal.classList.remove("hidden");
      // Prevent background scrolling
      document.body.style.overflow = 'hidden';
    }

    function closeModal() {
      const modal = document.getElementById("paymentForm");
      modal.classList.add("hidden");
      // Re-enable background scrolling
      document.body.style.overflow = 'auto';
    }

    // Optional: Close modal if user presses 'Escape' key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeModal();
    });


    // Animation Title
    document.addEventListener("DOMContentLoaded", function() {

      let text = "Pay Your RUPP School Fee Online";
      let i = 0;
      let isDeleting = false;

      function type() {

        let element = document.getElementById("typing");

        if (!isDeleting) {
          element.innerHTML = text.substring(0, i);
          i++;

          if (i > text.length) {
            isDeleting = true;
            setTimeout(type, 1500);
            return;
          }

        } else {

          element.innerHTML = text.substring(0, i);
          i--;

          if (i < 0) {
            isDeleting = false;
            i = 0;
          }
        }

        setTimeout(type, isDeleting ? 50 : 100);
      }

      type();

    }); // if user do not has to stay in account, cannot pay
    document.addEventListener('click', function(e) {
      return;
      const target = e.target.closest('button, a');
      if (!target) return;

      // ១. ករណី User មិនទាន់ Login
      if (!isLoggedIn) {
        // អនុញ្ញាតឱ្យចុចតែលើ Link ទៅកាន់ Login/Register ប៉ុណ្ណោះ
        const isAuthLink = target.href && (
          target.href.includes('Login.php') ||
          target.href.includes('Register.php')
        );

        if (!isAuthLink) {
          e.preventDefault();
          e.stopImmediatePropagation(); // បញ្ឈប់គ្រប់ Script ផ្សេងទៀតមិនឱ្យរត់
          alert("សូមចូលគណនីរបស់អ្នកជាមុនសិន ដើម្បីប្រើប្រាស់មុខងារនេះ!");
          return false;
        }
      }

      // ២. ករណី User បាន Login រួចហើយ និងចុចចំប៊ូតុង Pay Fees
      else if (isLoggedIn && target.id === 'payFeesBtn') {
        e.preventDefault();
        window.location.href = paymentPageUrl;
      }
    }, true); // ប្រើ true (Capturing phase) ដើម្បីស្ទាក់ចាប់មុនគេបង្អស់
  </script>

  <?php
  include '../Components/Footer.php';
  ?>
