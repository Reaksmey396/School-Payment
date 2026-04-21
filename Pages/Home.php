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
        color: #1e293b;
        /* ពណ៌ Slate-800 ជួយឱ្យអក្សរមើលទៅទន់ភ្នែកជាងពណ៌ខ្មៅសុទ្ធ */
    }

    /* ៣. ការកំណត់ចំណងជើង (Headings) ឱ្យមើលទៅ "Premium" */
    h1,
    h2,
    h3,
    .font-bold {
        font-family: 'Inter', sans-serif;
        font-weight: 700 !important;
        letter-spacing: -0.04em !important;
        /* គាបអក្សរឱ្យជិតគ្នា បង្កើតអារម្មណ៍ទំនើប */
        line-height: 1.1;
    }

    /* ៤. ការកំណត់អត្ថបទធម្មតា (Paragraph) */
    p {
        line-height: 1.6;
        letter-spacing: -0.01em;
        color: #475569;
        /* ពណ៌ Slate-600 */
    }

    /* ៥. កែសម្រួល Font ក្នុង Form និង Button ឱ្យត្រូវគ្នា */
    input,
    button,
    select,
    textarea {
        font-family: 'Inter', sans-serif !important;
        letter-spacing: -0.01em;
    }
</style>
<script>
    // បង្កើត Variable ក្នុង JS ដើម្បីស្គាល់ស្ថានភាព Login ពី PHP
    const isLoggedIn = <?php echo isset($_SESSION['is_admin']) ? 'true' : 'false'; ?>;
</script>

<div class="font-sans overflow-x-hidden bg-gray-100 text-gray-900">

    <!-- HERO SECTION -->
    <section class="max-w-7xl mx-auto mt-15 px-8 py-20 flex items-center justify-between gap-12">
        <div class="max-w-lg">
            <h1 class="md:text-5xl text-3xl font-extrabold leading-tight text-gray-900 mb-5">
                School Fees Payment <span class="text-brand">Made Simple.</span>
            </h1>
            <p class="text-gray-500 text-base leading-relaxed mb-8">
                Pay school fees securely from the comfort of your home. Track history, download receipts, and never miss a deadline again with our automated reminders.
            </p>
            <div class="flex items-center gap-4 mb-10">
                <button id="payFeesBtn" class=" flex no-underline items-center gap-2 bg-brand hover:bg-brandDark text-white text-sm font-semibold px-6 py-3 rounded-md">
                    Pay Fees Now
                    <span class="text-base">→</span>
                </button>
                <a href="../Pages/About.php" class="text-sm no-underline font-semibold text-gray-700 border hover:bg-gray-300 border-gray-200 hover:border-brand hover:text-brand px-6 py-3 rounded-md">
                    Learn More
                </a>
            </div>
            <div class="flex items-center gap-6">
                <div>
                    <span class="font-bold text-gray-900">500+</span>
                    <span class="text-gray-400 text-sm ml-1">Schools</span>
                </div>
                <div class="w-px h-4 bg-gray-200"></div>
                <div>
                    <span class="font-bold text-gray-900">100k+</span>
                    <span class="text-gray-400 text-sm ml-1">Parents</span>
                </div>
            </div>
        </div>

        <!-- Dashboard Mockup -->
        <div class="py-5 hidden md:block">
            <img class="rounded-2xl" src="https://i.ytimg.com/vi/1RokbTCSbpQ/maxresdefault.jpg" alt="">
        </div>
    </section>

    <!-- KEY FEATURES -->
    <section class="bg-gray-50 py-20">
        <div class="max-w-7xl mx-auto px-8">
            <div class="text-center mb-14">
                <p class="text-brand text-xs font-bold tracking-widest  mb-3">KEY FEATURES</p>
                <h2 class="md:text-4xl font-extrabold text-3xl text-gray-900 mb-3">Why choose EduPay for your school?</h2>
                <p class="text-gray-400 text-base">The most secure and convenient way to handle educational finances.</p>
            </div>
            <div class="grid text-center md:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <div class="bg-white border border-gray-100 rounded-2xl p-7 shadow-sm">
                    <div class="w-11 h-11 bg-blue-50 ml-28 rounded-xl flex items-center justify-center mb-5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg mb-2">Secure Payments</h3>
                    <p class="text-gray-400 text-sm leading-relaxed">Industry-standard encryption ensures your transaction data and banking details are always protected.</p>
                </div>
                <!-- Card 2 -->
                <div class="bg-white border border-gray-100 rounded-2xl p-7 shadow-sm">
                    <div class="w-11 h-11 bg-blue-50 rounded-xl ml-28 flex items-center justify-center mb-5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg mb-2">Real-time Tracking</h3>
                    <p class="text-gray-400 text-sm leading-relaxed">Instant confirmation and digital receipts. View your entire payment history in a single dashboard.</p>
                </div>
                <!-- Card 3 -->
                <div class="bg-white border border-gray-100 rounded-2xl p-7 shadow-sm">
                    <div class="w-11 h-11 bg-blue-50 ml-28 rounded-xl flex items-center justify-center mb-5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg mb-2">Smart Reminders</h3>
                    <p class="text-gray-400 text-sm leading-relaxed">Receive automated SMS and email notifications before due dates to avoid late payment penalties.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="bg-gray-100 py-16 px-4 sm:px-10 border-t border-gray-100">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-10">
                <span class="text-blue-500 text-xs font-bold tracking-widest  bg-gray-200 hover:bg-gray-100 px-4 py-1.5 rounded-full">FAQs</span>
                <h2 class="font-nunito font-black text-3xl text-slate-800 mt-3">Frequently Asked Questions</h2>
            </div>
            <div class="flex flex-col gap-3">
                <details class="group bg-slate-50 hover:bg-blue-50 rounded-2xl px-6 py-5 cursor-pointer transition-colors border border-transparent hover:border-blue-100">
                    <summary class="flex justify-between items-center font-semibold text-slate-700 text-sm list-none gap-4">
                        How do I access the payment portal?
                        <span class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 group-open:bg-blue-600 transition-colors">
                            <svg class="w-4 h-4 text-blue-600 group-open:text-white group-open:rotate-180 transition-all" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </summary>
                    <p class="text-slate-500 text-sm mt-4 leading-relaxed">Log in using your student ID number and registered password. Forgot your password? Click "Forgot Password" on the login page to reset it via your school email.</p>
                </details>
                <details class="group bg-slate-50 hover:bg-blue-50 rounded-2xl px-6 py-5 cursor-pointer transition-colors border border-transparent hover:border-blue-100">
                    <summary class="flex justify-between items-center font-semibold text-slate-700 text-sm list-none gap-4">
                        What fees can I pay online?
                        <span class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 group-open:bg-blue-600 transition-colors">
                            <svg class="w-4 h-4 text-blue-600 group-open:text-white group-open:rotate-180 transition-all" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </summary>
                    <p class="text-slate-500 text-sm mt-4 leading-relaxed">You can pay tuition, miscellaneous fees, ID fees, library fees, and all other school-related charges through this portal.</p>
                </details>
                <details class="group bg-slate-50 hover:bg-blue-50 rounded-2xl px-6 py-5 cursor-pointer transition-colors border border-transparent hover:border-blue-100">
                    <summary class="flex justify-between items-center font-semibold text-slate-700 text-sm list-none gap-4">
                        Will I receive a receipt after payment?
                        <span class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 group-open:bg-blue-600 transition-colors">
                            <svg class="w-4 h-4 text-blue-600 group-open:text-white group-open:rotate-180 transition-all" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </summary>
                    <p class="text-slate-500 text-sm mt-4 leading-relaxed">Yes! An official e-receipt is sent instantly to your registered email and is also available for download in your student profile dashboard.</p>
                </details>
                <details class="group bg-slate-50 hover:bg-blue-50 rounded-2xl px-6 py-5 cursor-pointer transition-colors border border-transparent hover:border-blue-100">
                    <summary class="flex justify-between items-center font-semibold text-slate-700 text-sm list-none gap-4">
                        Who do I contact if my payment fails?
                        <span class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 group-open:bg-blue-600 transition-colors">
                            <svg class="w-4 h-4 text-blue-600 group-open:text-white group-open:rotate-180 transition-all" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </summary>
                    <p class="text-slate-500 text-sm mt-4 leading-relaxed">Contact the Cashier's Office at 8936-7321 or email cashier@sanbeda-alabang.edu.ph with your transaction reference number for immediate assistance.</p>
                </details>
            </div>
        </div>
    </section>

    <!-- CTA SECTION -->
    <section class="bg-brand py-20">
        <div id="modalContent" class="max-w-8xl mx-auto px-8 text-center">
            <!-- Decorative wave shapes -->
            <div class="relative">
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-48 h-48 bg-white opacity-5 rounded-full -translate-x-24"></div>
                <div class="absolute right-0 top-1/2 -translate-y-1/2 w-64 h-64 bg-white opacity-5 rounded-full translate-x-32"></div>
                <div class="absolute left-1/4 bottom-0 w-32 h-32 bg-white opacity-5 rounded-full translate-y-12"></div>
            </div>
            <h2 class="text-4xl font-extrabold text-white mb-4 relative z-10">Ready to simplify your school payments?</h2>
            <p class="text-blue-200 text-base mb-10 relative z-10">Join thousands of parents and schools using EduPay for stress-free fee management.</p>
            <div class="flex items-center justify-center gap-4 relative z-10">
                <a href="" class=" no-underline text-brand font-semibold text-sm px-7 py-3 rounded-md bg-blue-50 hover:bg-blue-400">Get Started</a>
                <a href="../Pages/Contact.php" class="border no-underline border-white text-gray-100 hover:text-gray-800 font-semibold text-sm px-7 py-3 rounded-md hover:bg-gray-400 hover:text-brand">Contact</a>
            </div>
        </div>
    </section>

    <!-- Code Paying -->
    <div id="payment" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-4">

        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeModal()"></div>

        <div class="relative bg-white rounded-2xl w-full max-w-lg shadow-2xl animate-fadeUp overflow-hidden">

            <div class="flex mx-3 items-center justify-between p-3 border-b border-gray-100">
                <h2 class="text-2xl md:text-4xl font-bold text-gray-800 tracking-tight">Payment Form</h2>
                <button onclick="isLoggedIn ? toggleModal(true) : null" class="text-gray-400 hover:text-red-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- payment modal -->
            <div id="paymentModal" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="toggleModal(false)"></div>

    <div class="relative bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b border-gray-100">
            <h2 class="text-xl font-bold text-gray-800">Payment Form</h2>
            <button onclick="toggleModal(false)" class="text-gray-400 hover:text-red-500 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="p-6">
            <form action="#" method="POST">
                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-1">Student ID</label>
                    <input class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand outline-none" type="text" name="stu_id" placeholder="Enter ID...">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-1">Full Name</label>
                    <input class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand outline-none" type="text" name="name" placeholder="Enter Name...">
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Gender</label>
                        <select name="gender" class="w-full px-4 py-2 border rounded-lg outline-none">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Faculty</label>
                        <select name="faculty" class="w-full px-4 py-2 border rounded-lg outline-none">
                            <option value="Engineering">Engineering</option>
                            <option value="Science">Science</option>
                            <option value="Education">Education</option>
                        </select>
                    </div>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-semibold mb-1">Email</label>
                    <input class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand outline-none" type="email" name="email" placeholder="student@example.com">
                </div>
                <button type="submit" class="w-full bg-brand text-white font-bold py-3 rounded-lg hover:bg-brandDark transition-all">
                    Submit Payment
                </button>
            </form>
        </div>
    </div>
</div>
        </div>
    </div>
</div>

<script>
    // បង្កើត Tailwind Config ឱ្យត្រឹមត្រូវ
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    sans: ['Plus Jakarta Sans', 'sans-serif'],
                },
                colors: {
                    brand: '#2563EB',
                    brandDark: '#1d4ed8',
                }
            }
        }
    }
    // Command Modal
    // Function សម្រាប់បើក និង បិទ Modal
function toggleModal(show) {
    const modal = document.getElementById("paymentModal");
    if (show) {
        modal.classList.remove("hidden");
        document.body.style.overflow = 'hidden'; // បិទ scroll កុំឱ្យរញ៉េរញ៉ៃ
    } else {
        modal.classList.add("hidden");
        document.body.style.overflow = 'auto'; // បើក scroll វិញ
    }
}

// ស្ទាក់ចាប់ព្រឹត្តិការណ៍ចុច (Event Listener)
document.addEventListener('click', function(e) {
    const target = e.target.closest('#payFeesBtn, a');
    if (!target) return;

    // ប្រសិនបើចុចលើប៊ូតុង Pay Fees
    if (target.id === 'payFeesBtn') {
        e.preventDefault();
        
        if (!isLoggedIn) {
            alert("សូមចូលគណនីរបស់អ្នកជាមុនសិន ដើម្បីប្រើប្រាស់មុខងារនេះ!");
            // បងអាច redirect ទៅកាន់ login page បន្ថែម
            // window.location.href = '../Components/Login.php';
        } else {
            toggleModal(true);
        }
    }
});

// ចុច 'Escape' ដើម្បីបិទ Modal
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') toggleModal(false);
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

    });
    // if user do not has to stay in account, cannot pay
    document.addEventListener('click', function(e) {
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
            toggleModal(true); // ហៅ function បើក Modal នៅទីនេះវិញ
        }
    }, true); // ប្រើ true (Capturing phase) ដើម្បីស្ទាក់ចាប់មុនគេបង្អស់
</script>
<?php
include '../Components/Footer.php';
include '../Categories/footer.php';
?>