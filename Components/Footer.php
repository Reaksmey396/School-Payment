<?php
include '../Categories/header.php';
?>

<footer class="bg-slate-950 text-slate-300">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="grid gap-10 md:grid-cols-2 xl:grid-cols-4">
            <div class="xl:col-span-2">
                <div class="flex items-center gap-3">
                    <img src="https://upload.wikimedia.org/wikipedia/en/a/a2/RUPP_logo.PNG" width="60px" height="60px" alt="RUPP logo" class="rounded-full bg-white/5 p-1">
                    <div>
                        <span class="block text-3xl font-black uppercase text-red-500 leading-none">RUPP<span class="text-blue-500">Pay</span></span>
                        <span class="text-xs tracking-[0.25em] uppercase text-slate-500">School fee portal</span>
                    </div>
                </div>

                <p class="mt-5 max-w-xl text-sm leading-7 text-slate-400">
                    A secure and simple payment portal for students, parents, and school staff.
                    Manage fees, view receipts, and stay on top of school payments from one place.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="../Pages/Home.php" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-white/15">
                        <i class="fa-solid fa-house"></i> Home
                    </a>
                    <a href="../Pages/About.php" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-white/15">
                        <i class="fa-solid fa-circle-info"></i> About
                    </a>
                    <a href="../Components/Login.php" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white no-underline hover:bg-blue-500">
                        <i class="fa-solid fa-right-to-bracket"></i> Login
                    </a>
                </div>
            </div>

            <div>
                <h4 class="text-sm font-bold uppercase relative top-4 text-white">Quick Links</h4>
                <ul class="mt-5 relative right-8 space-y-3 text-sm">
                    <li><a href="../Pages/Home.php" class="hover:text-white no-underline transition">Home</a></li>
                    <li><a href="../Pages/Categories.php" class="hover:text-white no-underline transition">Categories</a></li>
                    <li><a href="../Pages/About.php" class="hover:text-white no-underline transition">About Us</a></li>
                    <li><a href="../Pages/Contact.php" class="hover:text-white no-underline transition">Contact Us</a></li>
                </ul>
            </div>

            <div class="">
                <h4 class="text-sm font-bold uppercase relative top-4 text-white">Support</h4>
                <ul class="mt-5 relative right-8 space-y-3 text-sm">
                    <li><a href="../Pages/Contact.php" class="hover:text-white no-underline transition">Help Center</a></li>
                    <li><a href="../Pages/About.php" class="hover:text-white no-underline transition">Payment Guide</a></li>
                    <li><a href="#" class="hover:text-white no-underline transition">Terms of Service</a></li>
                    <li><a href="#" class="hover:text-white no-underline transition">Privacy Policy</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-14 border-t border-white/10 pt-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <p class="text-xs text-slate-500">
                &copy; 2026 EduPay Portal. All rights reserved.
            </p>

            <div class="flex items-center gap-4">
                <a href="#" class="text-slate-500 hover:text-white transition no-underline" aria-label="Facebook">
                    <i class="fa-brands fa-facebook-f text-lg"></i>
                </a>
                <a href="#" class="text-slate-500 hover:text-white transition no-underline" aria-label="Twitter">
                    <i class="fa-brands fa-twitter text-lg"></i>
                </a>
                <a href="#" class="text-slate-500 hover:text-white transition no-underline" aria-label="Instagram">
                    <i class="fa-brands fa-instagram text-lg"></i>
                </a>
            </div>
        </div>
    </div>
</footer>

<?php
include '../Categories/footer.php';
?>
