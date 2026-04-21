<?php
  include '../Categories/header.php';
?>

<!-- LOGIN SECTION -->
<section class="min-h-screen flex items-center justify-center bg-gray-100 px-4">

  <div class="bg-white shadow-lg rounded-xl w-full max-w-md p-8">

    <!-- Title -->
    <h1 class="text-4xl font-bold text-center mb-2">
      Login Form
    </h1>
    <p class="text-gray-500 text-center mb-6 text-sm">
      Please enter your account details
    </p>

    <!-- Form -->
    <form action="check_login.php" method="post" class="space-y-5">

      <!-- Username -->
      <div>
        <label class="block text-sm font-medium mb-1">
          Username
        </label>

        <input
          type="text"
          id="name"
          name="name"
          required
          placeholder="Enter your username"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
      </div>

      <!-- Email -->
      <div>
        <label class="block text-sm font-medium mb-1">
          Email
        </label>

        <input
          type="email"
          required
          id="email"
          name="email"
          placeholder="Enter your email"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
      </div>

      <!-- Password -->
      <div>
        <div class="relative">
          <label class="block text-slate-700 font-semibold mb-2 text-sm">Comfirm Password</label>
          <div class="relative">
            <input type="password" id="password" name="password" placeholder="••••••••"
              required
              class="w-full px-4 py-2 bg-white border border-slate-200 rounded-xl focus:outline-none focus:border-blue-500 transition-all">
            <div id="togglePassword" class="absolute right-4 top-9 -translate-y-1/2 cursor-pointer text-slate-400 hover:text-blue-600">
              <i class="fa-solid fa-eye" id="eyeIcon"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Options -->
      <div class="flex items-center justify-between text-sm">
        <label class="flex items-center gap-2">
          <input type="checkbox" class="accent-blue-600">
          Remember me
        </label>

        <a href="../Components/Register.php" class="text-blue-600 hover:underline">
          Forgot password?
        </a>
      </div>

      <!-- Login Button -->
      <button
        type="submit"
        id="login"
        name="login"
        class="bg-blue-600 mt-5 text-white px-5 block mx-auto py-2 rounded-lg hover:bg-blue-700 transition"
      >
        Login
      </button>
    </form>

    <!-- Register -->
    <p class="text-center text-sm text-gray-500 mt-6">
      Don't have an account?
      <a href="../Components/Register.php" class="text-blue-600 font-medium hover:underline">
        Register
      </a>
    </p>
  </div>
</section>

<script>
  const password = document.getElementById('password');
  const togglePassword = document.getElementById('togglePassword');
  const eyeIcon = document.getElementById('eyeIcon');

  togglePassword.addEventListener('click', function() {
    // ប្តូរប្រភេទ Type រវាង password និង text
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);

    // ប្តូររូបរាង Icon (ប្តូរ Class)
    if (type === 'text') {
      eyeIcon.classList.remove('fa-eye');
      eyeIcon.classList.add('fa-eye-slash');
    } else {
      eyeIcon.classList.remove('fa-eye-slash');
      eyeIcon.classList.add('fa-eye');
    }
  });
</script>

<?php
    include '../Categories/footer.php';
?>