<?php
include '../Categories/header.php';
?>

<footer class="bg-slate-950 text-slate-400 py-20 px-6">
    <div class="max-w-7xl mx-5">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-12 lg:gap-16">
            <div class="col-span-2 lg:col-span-2 pr-10">
                <div class="flex items-center gap-3 mb-6">
                    <img src="https://upload.wikimedia.org/wikipedia/en/a/a2/RUPP_logo.PNG" width="40px" height="40px" alt="">
                    <span class="font-semibold fw-bold text-3xl uppercase text-red-500">RUPP<span class="text-blue-500">Pay</span></span>
                </div>
                <p class="text-sm leading-relaxed max-w-sm">
                    Simplifying education payments for schools and parents through secure, fast, and transparent digital solutions.
                </p>
            </div>

            <div>
                <h4 class="text-white font-bold mb-6">Quick Links</h4>
                <ul class="space-y-4 text-sm relative right-7">
                    <li><a href="#" class="hover:text-blue-500">Home</a></li>
                    <li><a href="#" class="hover:text-blue-500">About Us</a></li>
                    <li><a href="#" class="hover:text-blue-500">Pay Fees</a></li>
                    <li><a href="#" class="hover:text-blue-500">Student Login</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-bold mb-6">Support</h4>
                <ul class="space-y-4 text-sm relative right-8">
                    <li><a href="#" class="hover:text-blue-500">Help Center</a></li>
                    <li><a href="#" class="hover:text-blue-500">Payment Guide</a></li>
                    <li><a href="#" class="hover:text-blue-500">Terms of Service</a></li>
                    <li><a href="#" class="hover:text-blue-500">Privacy Policy</a></li>
                </ul>
            </div>

            <div class="col-span-2 md:col-span-2 lg:col-span-1">
                <h4 class="text-white font-bold mb-6">Stay Updated</h4>
                <p class="text-sm leading-relaxed mb-6">Subscribe to our newsletter for school updates.</p>

                <div class="grid grid-cols-1">
                    <input type="text" placeholder="Your name" class="bg-slate-900 text-slate-400 px-4 py-2 rounded-lg text-sm w-full border border-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none focus:border-blue-600 transition">
                    <button class="bg-blue-600 mt-2 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">Subscribe</button>
                </div>
            </div>
        </div>

        <div class="mt-16 pt-10 border-t border-slate-900 text-center text-xs">
            © 2023 EduPay Portal. All rights reserved. Built with excellence.
            <div class="flex justify-center gap-6 mt-6">
                <a href="#" class="hover:text-blue-500"><i class="fa-brands fa-facebook-f text-lg"></i></a>
                <a href="#" class="hover:text-blue-500"><i class="fa-brands fa-twitter text-lg"></i></a>
            </div>
        </div>
    </div>
</footer>

<script>
    document.addEventListener('click', function(e) {
    // ១. ឆែករកមើលថាតើអ្វីដែលគេចុចជា Button ឬ Link (<a>)
    const target = e.target.closest('button, a');

    // ២. ប្រសិនបើរកឃើញ Button/Link ហើយ User មិនទាន់ Login
    if (target && !isLoggedIn) {
        
        // លើកលែង៖ អនុញ្ញាតឱ្យចុចតែប៊ូតុង Login ឬ Register ប៉ុណ្ណោះ
        if (target.href && (target.href.includes('Login.php') || target.href.includes('Register.php'))) {
            return; // ឱ្យគេចុចចូលទៅ Login បាន
        }

        // ៣. បញ្ឈប់សកម្មភាពចុច និងបង្ហាញ Alert
        e.preventDefault();
        alert("សូមចូលគណនីរបស់អ្នកជាមុនសិន ដើម្បីប្រើប្រាស់មុខងារនេះ!");
        
        // អ្នកក៏អាចបញ្ជូនគេទៅទំព័រ Login ភ្លាមៗក៏បាន
        // window.location.href = "../Components/Login.php";
    }
}, true);
</script>

<?php
include '../Categories/footer.php';
?>