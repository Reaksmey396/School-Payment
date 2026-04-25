<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    const userRole = <?php echo isset($_SESSION['is_admin']) ? (int) $_SESSION['is_admin'] : 0; ?>;
    const paymentPageUrl = userRole === 2 ? '../Pages/Stu_dashoard.php' : '../Pages/Dashboards.php';
</script>

<div class="font-sans overflow-x-hidden bg-gray-100 text-gray-900">

    <!-- HERO SECTION -->
    <section class="relative z-10 max-w-7xl mx-auto mt-15 px-8 py-20 flex items-center justify-between gap-12">
        <div class="max-w-lg">
            <h1 class="md:text-5xl text-3xl font-extrabold leading-tight text-gray-900 mb-5">
                School Fees Payment <span class="text-brand">Made Simple.</span>
            </h1>
            <p class="text-gray-500 text-base leading-relaxed mb-8">
                Pay school fees securely from the comfort of your home. Track history, download receipts, and never miss a deadline again with our automated reminders.
            </p>
    <div class="flex items-center gap-4 mb-10">
                <button id="payFeesBtn" type="button" onclick="handlePayFeesClick(event)"
                    class="relative z-[999] flex no-underline items-center gap-2 bg-brand text-white text-sm font-semibold px-6 py-3 rounded-md hover:bg-brandDark cursor-pointer pointer-events-auto">
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

    <!-- Parent lookup modal -->
    <div id="paymentModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50">
    
    <!-- Modal Box -->
    <div class="bg-white w-full max-w-sm rounded-xl shadow-lg">

        <!-- Header -->
        <div class="flex justify-between items-center border-b px-4 py-3">
            <h2 class="text-3xl font-semibold text-gray-700">Student Information</h2>
            <button onclick="toggleModal(false)" class="text-gray-400 hover:text-red-500">
                ✕
            </button>
        </div>

        <!-- Body -->
        <div class="p-4">

            <p class="text-sm text-gray-500 mb-4">
                Enter student details to continue payment.
            </p>

            <form id="parentLookupForm" class="space-y-3">

                <div>
                    <label class="text-sm text-gray-600">Student ID</label>
                    <input 
                        id="lookupStuId" 
                        name="stu_id" 
                        type="text" 
                        required
                        placeholder="Enter ID"
                        class="w-full border rounded-md px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Student Name</label>
                    <input 
                        id="lookupName" 
                        name="name" 
                        type="text" 
                        required
                        placeholder="Enter name"
                        class="w-full border rounded-md px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Student Email</label>
                    <input 
                        id="lookupEmail" 
                        name="email" 
                        type="email" 
                        required
                        placeholder="example@gmail.com"
                        class="w-full border rounded-md px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <p id="parentLookupError" class="hidden text-sm text-red-500"></p>

                <!-- Buttons -->
                <div class="flex gap-2 mt-3">
                    <button 
                        type="button"
                        onclick="toggleModal(false)"
                        class="w-1/2 border py-2 rounded-md text-gray-600 hover:bg-gray-100">
                        Cancel
                    </button>

                    <button 
                        type="submit"
                        class="w-1/2 bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700">
                        Continue
                    </button>
                </div>

            </form>
        </div>

    </div>

    </div>

    <div id="guestModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/55 px-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-[1.75rem] bg-white shadow-2xl border border-slate-200 overflow-hidden">
            <div class="relative  py-6 text-black ">
                <button type="button" onclick="toggleGuestModal(false)" class="absolute right-4 top-4 text-black">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
                <h2 class="text-2xl ml-7 mt-2 font-bold">Welcome, Guest!</h2>
            </div>

            <div class="px-6 mb-5 py-6">
                <div class="rounded-2xl border border-blue-100 bg-blue-50/70 px-4 py-4">
                    <p class="text-sm font-semibold text-slate-800">Please go to login as an account to pay fee.</p>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        Once you sign in, you can continue to pay fees normally and access the payment modal.
                    </p>
                </div>

                <div class="mt-6 grid gap-3">
                    <div class="flex items-center gap-3 rounded-2xl bg-slate-200 px-4 py-3">
                        <div class="h-10 w-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Secure access</p>
                            <p class="text-xs text-slate-500">Only logged-in users can pay fees.</p>
                        </div>
                    </div>
                </div>

                <div class="relative top-8 flex items-center justify-end gap-3">
                    <button type="button" onclick="toggleGuestModal(false)" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50">
                        Close
                    </button>
                    <a href="../Components/Login.php" class="px-5 py-2.5 rounded-xl bg-brand text-white font-semibold hover:bg-brandDark no-underline inline-flex items-center gap-2">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- QR modal -->
    <div id="qrModal" class="hidden fixed inset-0 z-[160] items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeQR()"></div>

        <div class="relative bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden">
            <div class="p-5 border-b border-gray-100 text-center">
                <h2 class="text-xl font-bold text-gray-800">Scan QR to Pay</h2>
            </div>

            <div class="p-6 text-center">
                <img id="bakongQR" src="" alt="Payment QR Code" class="mx-auto w-[240px] h-[240px] object-contain" />
                <p class="mt-3 text-gray-500 text-sm">Scan using Bakong / ABA</p>

                <div id="qrStatus" class="mt-4 rounded-lg bg-blue-50 text-blue-700 px-4 py-3 text-sm">
                    Waiting for payment confirmation...
                </div>

                <button onclick="closeQR()" class="mt-4 text-red-500">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Receipt -->
    <div id="receipt" class="hidden fixed inset-0 z-[170] items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeReceipt(false)"></div>
        <div class="relative bg-white rounded-2xl w-full max-w-md shadow-2xl p-5 text-left">
            <h2 class="text-xl font-bold mb-3">Receipt</h2>
            <p>Payer Name: <span id="payerName">-</span></p>
            <p>Payer Account: <span id="payerAccount">-</span></p>
            <p>Amount: $<span id="receiptAmount">0.00</span></p>
            <p>Method: <span id="receiptMethod">-</span></p>
            <p>Bill No: <span id="receiptBillNo">-</span></p>
            <p>Receipt No: <span id="receiptCode">-</span></p>
            <p>Paid To: <span id="receiptPaidTo">-</span></p>
            <p>Date: <span id="payDate"></span></p>
            <div class="text-green-600 font-bold mt-4">✔ Payment Successful</div>

            <button type="button" onclick="closeReceipt(true)" class="mt-4 w-full bg-brand text-white font-semibold py-2 rounded-lg hover:bg-brandDark">
                Done
            </button>
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
        const form = document.getElementById("parentLookupForm");
        const error = document.getElementById("parentLookupError");
        if (show) {
            modal.style.display = 'flex';
            modal.classList.remove("hidden");
            modal.classList.add("flex");
            document.body.style.overflow = 'hidden'; // បិទ scroll កុំឱ្យរញ៉េរញ៉ៃ
            if (error) {
                error.classList.add("hidden");
                error.textContent = "";
            }
        } else {
            modal.classList.add("hidden");
            modal.classList.remove("flex");
            modal.style.display = 'none';
            document.body.style.overflow = 'auto'; // បើក scroll វិញ
            if (form) {
                form.reset();
            }
            if (error) {
                error.classList.add("hidden");
                error.textContent = "";
            }
        }
    }

    function toggleGuestModal(show) {
        const modal = document.getElementById("guestModal");
        if (!modal) return;

        modal.style.display = show ? 'flex' : 'none';
        modal.classList.toggle('hidden', !show);
        modal.classList.toggle('flex', show);
    }

    function handlePayFeesClick(event) {
        if (event) {
            event.preventDefault();
        }

        if (!isLoggedIn) {
            toggleGuestModal(true);
            return;
        }

        if (userRole === 1 || userRole === 2) {
            window.location.href = paymentPageUrl;
            return;
        }

        toggleModal(true);
    }

    function setParentLookupError(message) {
        const error = document.getElementById("parentLookupError");
        if (!error) {
            return;
        }

        if (message) {
            error.textContent = message;
            error.classList.remove("hidden");
        } else {
            error.textContent = "";
            error.classList.add("hidden");
        }
    }

    async function handleParentLookupSubmit(event) {
        event.preventDefault();

        const stuId = document.getElementById('lookupStuId')?.value.trim();
        const name = document.getElementById('lookupName')?.value.trim();
        const email = document.getElementById('lookupEmail')?.value.trim();

        if (!stuId || !name || !email) {
            setParentLookupError('Please fill in student ID, name, and email.');
            return;
        }

        const submitBtn = event.currentTarget.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.textContent : '';

        try {
            setParentLookupError('');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Checking...';
            }

            const response = await fetch(`../Components/lookup_student.php?${new URLSearchParams({
                stu_id: stuId,
                name,
                email
            }).toString()}`, {
                cache: 'no-store'
            });

            const data = await response.json();

            if (!response.ok || !data.success || !data.student) {
                throw new Error(data.message || 'Student lookup failed.');
            }

            const student = data.student;

            toggleModal(false);

            currentReceipt.payerName = student.name || '-';
            currentReceipt.payerAccount = student.email || '-';
            currentReceipt.amount = Number(student.total_fee || 0);
            updateReceipt(currentReceipt);

            showQR(student.id, student.total_fee || 0);
        } catch (error) {
            console.error('Student lookup error:', error);
            setParentLookupError(error.message || 'Unable to verify student information.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText || 'Continue to QR';
            }
        }
    }

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

    document.addEventListener('DOMContentLoaded', function() {
        const lookupForm = document.getElementById('parentLookupForm');
        if (lookupForm) {
            lookupForm.addEventListener('submit', handleParentLookupSubmit);
        }

    });

    // Payment Logic
    
    let currentStudent = null;
    let currentAmount = null;
    let currentBillNo = null;
    let currentMd5 = null;
    let autoQrOpened = false;
    let qrReady = false;
    let qrRefreshInterval = null; // Timer for QR refresh
    let paymentCheckInterval = null;
    let currentReceipt = {
        amount: 0,
        method: 'Bakong QR',
        billNo: '',
        receiptCode: '',
        paidAt: '',
        bankName: '<?php echo htmlspecialchars(trim(getenv('BAKONG_MERCHANT_NAME') ?: 'RUPP Pay'), ENT_QUOTES); ?>',
        bankAccount: '<?php echo htmlspecialchars(trim(getenv('BAKONG_ACCOUNT_ID') ?: 'khim_reaksmey@bkrt'), ENT_QUOTES); ?>',
        bankCity: '<?php echo htmlspecialchars(trim(getenv('BAKONG_MERCHANT_CITY') ?: 'PHNOM PENH'), ENT_QUOTES); ?>'
    };

    function buildQrPlaceholder(label) {
        const safeLabel = String(label || 'Loading').replace(/[<>&"]/g, '');
        const svg = `
            <svg xmlns="http://www.w3.org/2000/svg" width="220" height="220" viewBox="0 0 220 220">
                <rect width="220" height="220" fill="#f8fafc"/>
                <rect x="10" y="10" width="200" height="200" rx="18" fill="#ffffff" stroke="#cbd5e1" stroke-width="2"/>
                <text x="110" y="110" text-anchor="middle" dominant-baseline="middle" font-family="Arial, sans-serif" font-size="18" fill="#475569">${safeLabel}</text>
            </svg>
        `;

        return "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(svg);
    }

    function buildQrRequestUrl(studentId, amount) {
        const params = new URLSearchParams({
            student_id: String(studentId),
            amount: String(amount),
            qr_mode: "static",
            _: String(Date.now())
        });

        return "../Components/generate_qr.php?" + params.toString();
    }

    function setConfirmButtonState(disabled, label) {
        const confirmBtn = document.getElementById('confirmPaymentBtn');

        if (!confirmBtn) {
            return;
        }

        confirmBtn.disabled = disabled;
        confirmBtn.textContent = label;
        confirmBtn.classList.toggle('opacity-60', disabled);
        confirmBtn.classList.toggle('cursor-not-allowed', disabled);
    }

    function updateReceipt(details) {
        const receipt = details || currentReceipt;

        const amountEl = document.getElementById('receiptAmount');
        const methodEl = document.getElementById('receiptMethod');
        const bankNameEl = document.getElementById('receiptBankName');
        const bankAccountEl = document.getElementById('receiptBankAccount');
        const bankCityEl = document.getElementById('receiptBankCity');
        const billNoEl = document.getElementById('receiptBillNo');
        const receiptCodeEl = document.getElementById('receiptCode');
        const payDateEl = document.getElementById('payDate');
        const paidToEl = document.getElementById('receiptPaidTo');
        const payerNameEl = document.getElementById('payerName');
        const payerAccountEl = document.getElementById('payerAccount');

        if (payerNameEl) payerNameEl.textContent = receipt.payerName || '-';
        if (payerAccountEl) payerAccountEl.textContent = receipt.payerAccount || '-';
        if (amountEl) amountEl.textContent = Number(receipt.amount || currentAmount || 0).toFixed(2);
        if (methodEl) methodEl.textContent = receipt.method || 'Bakong QR';
        if (bankNameEl) bankNameEl.textContent = receipt.bankName || '';
        if (bankAccountEl) bankAccountEl.textContent = receipt.bankAccount || '';
        if (bankCityEl) bankCityEl.textContent = receipt.bankCity || '';
        if (billNoEl) billNoEl.textContent = receipt.billNo || '-';
        if (receiptCodeEl) receiptCodeEl.textContent = receipt.receiptCode || '-';
        if (payDateEl) payDateEl.textContent = receipt.paidAt || '';
        if (paidToEl) paidToEl.textContent = receipt.bankName || '';
    }

    // Make button pay runs
    // Print reciept
    function printReceipt() {
        var content = document.getElementById('receipt').innerHTML;
        var myWindow = window.open('', '', 'width=800,height=600');
        myWindow.document.write(content);
        myWindow.document.close();
        myWindow.print();
    }

    function closeReceipt(refreshPage) {
        const receipt = document.getElementById('receipt');
        if (receipt) {
            receipt.classList.add('hidden');
            receipt.classList.remove('flex');
            receipt.style.display = 'none';
        }
        document.body.style.overflow = 'auto';

        if (refreshPage) {
            window.location.reload();
        }
    }

    function closeQR() {
        document.getElementById('qrModal').classList.add('hidden');
        document.getElementById('qrModal').classList.remove('flex');
        document.body.style.overflow = 'auto';
        setConfirmButtonState(false, 'I Have Paid');

        // Stop QR refresh when user cancels
        if (qrRefreshInterval) {
            clearInterval(qrRefreshInterval);
            qrRefreshInterval = null;
        }
        if (paymentCheckInterval) {
            clearInterval(paymentCheckInterval);
            paymentCheckInterval = null;
        }
    }

    function confirmPayment() {
        if (!qrReady || !currentStudent || !currentAmount) {
            alert("Please wait for the QR code to finish loading first.");
            return;
        }

        setConfirmButtonState(true, 'Saving...');

        fetch("", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                credentials: "same-origin",
                body: new URLSearchParams({
                    pay: "1",
                    student_id: String(currentStudent),
                    amount: String(currentAmount),
                    method: "Bakong QR",
                    bill_no: String(currentBillNo || "")
                }).toString()
            })
            .then(async (res) => {
                const contentType = res.headers.get("content-type") || "";
                const raw = await res.text();

                if (!contentType.includes("application/json")) {
                    throw new Error("Your session may have expired. Please reload the page and try again.");
                }

                let data = null;

                try {
                    data = JSON.parse(raw);
                } catch (error) {
                    throw new Error("The server returned an invalid payment response.");
                }

                if (!res.ok || !data.success) {
                    throw new Error(data.message || "Failed to save payment.");
                }

                return data;
            })
            .then(data => {
                alert("Payment Successful!");

                closeQR();

                document.getElementById('receipt').classList.remove('hidden');
                document.getElementById('receipt').style.display = 'flex';

                document.getElementById('payDate').innerText = data.paid_at || new Date().toLocaleString();

                // Stop QR refresh on successful payment
                if (qrRefreshInterval) {
                    clearInterval(qrRefreshInterval);
                    qrRefreshInterval = null;
                }
            })
            .catch(err => {
                console.error("Payment save error:", err);
                alert(err.message || "Payment could not be completed.");
            })
            .finally(() => {
                setConfirmButtonState(false, 'I Have Paid');
            });
    }

    function showReceipt() {
        document.getElementById('receipt').classList.remove('hidden');
    }

    function closeQRAuto() {
        document.getElementById('qrModal').classList.add('hidden');
        document.getElementById('qrModal').style.display = 'none';
        document.body.style.overflow = 'auto';

        if (paymentCheckInterval) {
            clearInterval(paymentCheckInterval);
            paymentCheckInterval = null;
        }
    }

    function checkPaymentAuto(billNo, studentId, amount) {
        if (paymentCheckInterval) {
            clearInterval(paymentCheckInterval);
            paymentCheckInterval = null;
        }

        paymentCheckInterval = setInterval(() => {
            fetch(`../Components/check_payment.php?bill_no=${billNo}&md5=${encodeURIComponent(currentMd5 || billNo || '')}&hash=${encodeURIComponent(currentMd5 || billNo || '')}&student_id=${studentId}&amount=${amount}`)
                .then(res => res.json())
                .then(data => {
                    if (!data || data.status !== "PAID") {
                        console.log("CHECK PAYMENT:", data);
                        return;
                    }
                    currentReceipt.payerName = data.account_name || data.payer_name || '-';
                    currentReceipt.payerAccount = data.payer_account || '-';

                    if (paymentCheckInterval) {
                        clearInterval(paymentCheckInterval);
                        paymentCheckInterval = null;
                    }

                    const paidAt = new Date().toLocaleString();
                    currentReceipt.amount = Number(data.money_paid || amount || currentReceipt.amount || 0);
                    currentReceipt.billNo = billNo || currentReceipt.billNo;
                    currentReceipt.receiptCode = data.receipt_code || currentReceipt.receiptCode;
                    currentReceipt.paidAt = paidAt;
                    currentReceipt.method = data.method || currentReceipt.method || 'Bakong QR';
                    currentReceipt.bankName = data.bank_name || currentReceipt.bankName;
                    currentReceipt.bankAccount = data.bank_account || currentReceipt.bankAccount;
                    currentReceipt.bankCity = data.bank_city || currentReceipt.bankCity;
                    updateReceipt(currentReceipt);

                    // STOP INTERVALS
                    if (paymentCheckInterval) {
                        clearInterval(paymentCheckInterval);
                        paymentCheckInterval = null;
                    }

                    if (qrRefreshInterval) {
                        clearInterval(qrRefreshInterval);
                        qrRefreshInterval = null;
                    }

                    // HIDE QR MODAL (IMPORTANT FIX)
                    const qrModal = document.getElementById('qrModal');
                    qrModal.classList.add('hidden');
                    qrModal.classList.remove('flex');
                    qrModal.style.display = 'none';

                    // SHOW RECEIPT
                    const receipt = document.getElementById('receipt');
                    receipt.classList.remove('hidden');
                    receipt.classList.add('flex');
                    receipt.style.display = 'flex';

                    document.getElementById('payDate').innerText = paidAt;

                    alert(
                        "Account Name: " + (currentReceipt.payerName || '-') + "\n" +
                        "Money Paid: $" + Number(currentReceipt.amount || 0).toFixed(2) + "\n" +
                        "Receipt: " + (currentReceipt.receiptCode || '-') + "\n" +
                        "Bank / Account: " + (currentReceipt.bankName || "") + "\n" +
                        "Account No: " + (currentReceipt.bankAccount || "") + "\n" +
                        "Time: " + paidAt
                    );
                })
                .catch(err => {
                    console.error("Payment check error:", err);
                });
        }, 5000);
    }

    function checkPayment(billNo, studentId, amount) {
        let interval = setInterval(() => {
            fetch(`../Components/check_payment.php?bill_no=${billNo}&md5=${encodeURIComponent(currentMd5 || billNo || '')}&hash=${encodeURIComponent(currentMd5 || billNo || '')}&student_id=${studentId}&amount=${amount}`)
                .then(res => res.text())
                .then(data => {
                    if (data.trim() === "PAID") {
                        clearInterval(interval); // បញ្ឈប់ការឆែក
                        closeQR(); // បិទ Modal QR
                        alert(`ការបង់ប្រាក់ចំនួន $${amount} បានជោគជ័យ!`);

                        // បង្ហាញវិក្កយបត្រ
                        document.getElementById('receipt').classList.remove('hidden');
                        document.getElementById('receipt').style.display = 'flex';
                        document.getElementById('payDate').innerText = new Date().toLocaleString();

                        // Stop QR refresh on successful payment
                        if (qrRefreshInterval) {
                            clearInterval(qrRefreshInterval);
                            qrRefreshInterval = null;
                        }
                    }
                });
        }, 5000); // ឆែករៀងរាល់ ៥ វិនាទីម្តង
    }

    function showQR(studentId, amount) {
        document.getElementById('receipt').classList.add('hidden');
        document.getElementById('receipt').classList.remove('flex');
        currentStudent = studentId;
        currentAmount = amount;
        currentBillNo = null;
        currentMd5 = null;
        qrReady = false;

        // Clear any existing refresh timer
        if (qrRefreshInterval) {
            clearInterval(qrRefreshInterval);
            qrRefreshInterval = null;
        }

        document.getElementById('qrModal').classList.remove('hidden');
        document.getElementById('qrModal').classList.add('flex');

        const qrImg = document.getElementById('bakongQR');
        qrImg.src = buildQrPlaceholder("Loading QR");
        setConfirmButtonState(true, 'Loading QR...');

        fetch(buildQrRequestUrl(studentId, amount), {
                cache: "no-store"
            })
            .then(async (res) => {
                const raw = await res.text();
                let data = null;

                try {
                    data = JSON.parse(raw);
                } catch (error) {
                    throw new Error("The QR service returned an invalid response.");
                }

                if (!res.ok || !data.success) {
                    throw new Error(data.message || "Failed to generate Bakong QR.");
                }

                return data;
            })
            .then(data => {
                currentBillNo = data.bill_no || null;
                currentMd5 = data.md5 || null;
                currentReceipt.billNo = data.bill_no || currentReceipt.billNo;
                currentReceipt.receiptCode = data.receipt_code || currentReceipt.receiptCode;
                currentReceipt.amount = Number(data.amount || amount || currentReceipt.amount || 0);
                currentReceipt.bankName = data.merchant_name || currentReceipt.bankName;
                currentReceipt.bankAccount = data.merchant_account || currentReceipt.bankAccount;
                currentReceipt.bankCity = data.merchant_city || currentReceipt.bankCity;
                updateReceipt(currentReceipt);
                qrImg.src = data.qr_image || buildQrPlaceholder("QR Ready");
                qrReady = true;
                setConfirmButtonState(false, 'I Have Paid');

                checkPaymentAuto(currentBillNo, studentId, amount);
            })
            .catch(err => {
                console.error("Error fetching QR:", err);
                qrImg.src = buildQrPlaceholder("QR Failed");
                qrReady = false;
                setConfirmButtonState(true, 'QR Failed');
                alert(err.message || "Unable to connect to Bakong server.");
            });
    }

    function startQrRefresh(studentId, amount) {
        // Clear any existing refresh timer
        if (qrRefreshInterval) {
            clearInterval(qrRefreshInterval);
        }

        // Refresh QR every 4 minutes (240000 ms) to prevent expiration
        qrRefreshInterval = setInterval(() => {
            console.log("Refreshing QR code to prevent expiration...");

            const qrImg = document.getElementById('bakongQR');
            qrImg.src = buildQrPlaceholder("Refreshing QR...");
            setConfirmButtonState(true, 'Refreshing QR...');

            fetch(buildQrRequestUrl(studentId, amount), {
                    cache: "no-store"
                })
                .then(async (res) => {
                    const raw = await res.text();
                    let data = null;

                    try {
                        data = JSON.parse(raw);
                    } catch (error) {
                        throw new Error("The QR service returned an invalid response.");
                    }

                    if (!res.ok || !data.success) {
                        throw new Error(data.message || "Failed to refresh Bakong QR.");
                    }

                    return data;
                })
                .then(data => {
                    currentBillNo = data.bill_no || null;
                    currentMd5 = data.md5 || null;
                    qrImg.src = data.qr_image || buildQrPlaceholder("QR Ready");
                    setConfirmButtonState(false, 'I Have Paid');
                    console.log("QR code refreshed successfully");
                })
                .catch(err => {
                    console.error("Error refreshing QR:", err);
                    qrImg.src = buildQrPlaceholder("Refresh Failed");
                    setConfirmButtonState(true, 'Refresh Failed');
                });
        }, 240000); // 4 minutes
    }
</script>
<?php
include '../Components/Footer.php';
include '../Categories/footer.php';
?>
