<?php
include '../Categories/header.php';
?>

<!-- REGISTER SECTION -->
<section class="min-h-screen  flex items-center justify-center bg-gray-100 px-4">

  <div class="bg-white shadow-lg rounded-xl w-full max-w-md p-8">

    <!-- Title -->
    <h2 class="text-4xl font-bold text-center mb-2">
      Create Account
    </h2>

    <p class="text-gray-500 text-center mb-2 text-sm">
      Register Account
    </p>

    <!-- Form -->
    <form id="registerForm" action="insert.php" method="post" class="space-y-5">
      <p id="errorMessage" class="text-red-500 py-2 px-5 bg-red-200 text-center text-xs mt-1 hidden">Password is not the same!</p>
      <p id="errorMessages" class="text-red-500 py-2 px-5 bg-red-200 text-center text-xs mt-1 hidden">please put password as letter with number !</p>

      <!-- Full Username -->
      <div>
        <label class="block text-sm font-medium mb-1">
          Full Username
        </label>

        <input
          type="text" required
          id="name"
          name="name"
          placeholder="Enter your full username"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- Email -->
      <div>
        <label class="block text-sm font-medium mb-1">
          Email
        </label>

        <input
          type="email"
          id="email"
          name="email"
          required
          placeholder="Enter your email"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- final Password -->
      <input type="hidden" id="final_password" name="password">

      <!-- Password -->
      <div>
        <div class="relative">
          <label class="block text-slate-700 font-semibold mb-2 text-sm">Password</label>
          <div class="relative">
            <input type="password" id="pass1" placeholder="••••••••"
              class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 transition-all">

            <div id="togglePassword1" class="absolute right-4 top-9 -translate-y-1/2 cursor-pointer text-slate-400 hover:text-blue-600">
              <i class="fa-solid fa-eye" id="eyeIcon1"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Confirm Password -->
      <div>
        <div class="relative">
          <label class="block text-slate-700 font-semibold mb-2 text-sm">ConfirmPassword</label>
          <div class="relative">
            <input type="password" id="pass2" placeholder="••••••••"
              class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 transition-all">

            <div id="togglePassword2" class="absolute right-4 top-9 -translate-y-1/2 cursor-pointer text-slate-400 hover:text-blue-600">
              <i class="fa-solid fa-eye" id="eyeIcon2"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Register Button -->
      <button
        type="submit"
        name="register"
        id="clickRegister"
        class="bg-blue-600 mt-5 text-white block mx-auto px-5 py-2 rounded-lg hover:bg-blue-700 transition">
        Register
      </button>
    </form>

    <!-- Login Link -->
    <p class="text-center text-sm text-gray-500 mt-3">
      Already have an account?
      <a href="../Components/Login.php" class="text-blue-600 font-medium hover:underline">
        Login
      </a>
    </p>
  </div>
</section>

<script>

  // compare 2 passoword
  // ១. ចាប់យក Element ឱ្យបានត្រឹមត្រូវ
  const p1 = document.getElementById('pass1');
  const p2 = document.getElementById('pass2');
  const finalPass = document.getElementById('final_password');
  const toggle1 = document.getElementById('togglePassword1');
  const toggle2 = document.getElementById('togglePassword2');
  const icon1 = document.getElementById('eyeIcon1');
  const icon2 = document.getElementById('eyeIcon2');
  const form = document.getElementById('registerForm');
  const errorMsg = document.getElementById('errorMessage');
  const errorMsgs = document.getElementById('errorMessages');

  // ២. មុខងារបញ្ជាភ្នែកទី ១
  toggle1.addEventListener('click', function() {
    const type = p1.getAttribute('type') === 'password' ? 'text' : 'password';
    p1.setAttribute('type', type);
    icon1.classList.toggle('fa-eye');
    icon1.classList.toggle('fa-eye-slash');
  });

  // ៣. មុខងារបញ្ជាភ្នែកទី ២
  toggle2.addEventListener('click', function() {
    const type = p2.getAttribute('type') === 'password' ? 'text' : 'password';
    p2.setAttribute('type', type);
    icon2.classList.toggle('fa-eye');
    icon2.classList.toggle('fa-eye-slash');
  });

  // ៤. មុខងារឆែក Password ពេលចុច Register
  form.addEventListener('submit', function(e) {
        // ១. លាងសម្អាត Error និង Border ចាស់ៗ
        errorMsg.classList.add('hidden');
        errorMsgs.classList.add('hidden');
        p1.style.borderColor = "";
        p2.style.borderColor = "";

        // ២. បង្កើតលក្ខខណ្ឌ (Validation Logic)
        const hasLetters = /[a-zA-Z]/.test(p1.value);       // មានអក្សរយ៉ាងតិច ១
        const hasNumbers = /\d/.test(p1.value);             // មានលេខយ៉ាងតិច ១
        const isAlphaNumeric = /^[a-zA-Z0-0]+$/.test(p1.value); // គ្មាននិមិត្តសញ្ញា (Symbols)
        const isLongEnough = p1.value.length >= 5;          // យ៉ាងតិច ៥ ខ្ទង់

        // --- លក្ខខណ្ឌទី ១: ឆែកមើលថា Password ទាំងពីរដូចគ្នា ---
        if (p1.value !== p2.value || p1.value === "") {
            e.preventDefault(); 
            errorMsg.innerText = "Password មិនដូចគ្នាទេ!";
            errorMsg.classList.remove('hidden');
            p2.style.borderColor = "red";
            p2.focus();
            return false;
        }

        // --- លក្ខខណ្ឌទី ២: ឆែកប្រវែង Password (យ៉ាងតិច ៥ ខ្ទង់) ---
        if (!isLongEnough) {
            e.preventDefault();
            errorMsgs.innerText = "Password ត្រូវតែមានយ៉ាងតិច ៥ ខ្ទង់!";
            errorMsgs.classList.remove('hidden');
            p1.style.borderColor = "orange";
            p1.focus();
            return false;
        }

        // --- លក្ខខណ្ឌទី ៣: ឆែកមើលថាមាននិមិត្តសញ្ញា (Symbols) ឬអត់ ---
        if (isAlphaNumeric) {
            e.preventDefault(); 
            errorMsgs.innerText = "មិនអនុញ្ញាតឱ្យប្រើនិមិត្តសញ្ញា (Symbols) ឡើយ!";
            errorMsgs.classList.remove('hidden');
            p1.style.borderColor = "orange";
            p1.focus();
            return false;
        }

        // --- លក្ខខណ្ឌទី ៤: ត្រូវតែមានទាំងអក្សរ និងលេខ (បង្ខំឱ្យលាយគ្នា) ---
        if (!hasLetters || !hasNumbers) {
            e.preventDefault(); 
            errorMsgs.innerText = "Password ត្រូវតែមានការលាយគ្នារវាងអក្សរ និងលេខ!";
            errorMsgs.classList.remove('hidden');
            p1.style.borderColor = "orange";
            p1.focus();
            return false;
        }

        // ៣. បើត្រឹមត្រូវទាំងអស់ រក្សាទុកតម្លៃក្នុង Hidden Input
        finalPass.value = p1.value;
        
        // ទុកឱ្យ Form ផ្ញើទៅ insert.php តាមធម្មជាតិ
        return true; 
    });
</script>

<?php
include '../Categories/footer.php';
?>