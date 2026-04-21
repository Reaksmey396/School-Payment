<?php
    include '../Components/Navbar.php';
    include '../Categories/header.php';
?>

<div class="bg-slate-50 p-4 relative top-19 font-sans">

    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-start mb-8">
            <div>
                <h2 class="text-3xl font-bold text-[#001f3f]">Software Engineering - Year 2, Class A</h2>
                <p class="text-slate-500 mt-1">Manage student fee payments, invoices, and payment status updates.</p>
            </div>
            <button class="bg-white border border-slate-200 px-4 py-2 rounded-lg text-sm font-bold text-slate-700 flex items-center hover:bg-slate-50">
                <i class="fa-solid fa-download mr-2 text-slate-400"></i> Export List
            </button>
        </div>

        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative flex-1 max-w-lg">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="searchInput" onkeyup="filterStudents()" placeholder="Find student name, ID or email..."
                    class="w-full bg-slate-50 border-none rounded-xl py-3 pl-12 pr-4 text-sm focus:ring-2 focus:ring-blue-100 outline-none">
            </div>

            <div class="flex items-center space-x-2 overflow-x-auto pb-2 md:pb-0">
                <button onclick="setStatusFilter(this, 'all')" class="status-btn bg-[#0047ab] text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center">
                    <i class="fa-solid fa-list-ul mr-2"></i> All Students
                </button>
                <button onclick="setStatusFilter(this, 'paid')" class="status-btn bg-slate-50 text-slate-600 px-5 py-2.5 rounded-xl text-sm font-bold border border-transparent hover:bg-slate-100 transition-all flex items-center">
                    <i class="fa-solid fa-circle-check mr-2 text-green-500"></i> Paid
                </button>
                <button onclick="setStatusFilter(this, 'rejected')" class="status-btn bg-slate-50 text text-slate-600 px-5 py-2.5 rounded-xl text-sm font-bold border border-transparent hover:bg-slate-100 transition-all flex items-center">
                    <i class="fa-solid fa-circle-xmark mr-2 text-red-500"></i> Rejected
                </button>
                <button onclick="setStatusFilter(this, 'not paid')" class="status-btn bg-slate-50 text text-slate-600 px-5 py-2.5 rounded-xl text-sm font-bold border border-transparent hover:bg-slate-100 transition-all flex items-center">
                    <i class="fa-solid fa-circle-exclamation mr-2 text-orange-500"></i> Not Paid
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-left text-center border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 text-slate-400 text-[11px] font-bold uppercase tracking-wider">
                        <th class="px-6 py-4">No.</th>
                        <th class="px-6 py-4">Student Name</th>
                        <th class="px-6 py-4">Gender</th>
                        <th class="px-6 py-4">Student ID</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="studentTableBody" class="divide-y divide-slate-50">
                    <tr class="student-row hover:bg-slate-50/50 transition-colors" data-status="paid">
                        <td class="px-6 py-4 text-slate-400 text-sm">01</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs border border-blue-50">JD</div>
                                <span class="font-bold text-slate-700 text-sm">John Doe</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-500 text-sm font-medium">Male</td>
                        <td class="px-6 py-4 text-slate-500 text-sm font-medium">SE2024-001</td>
                        <td class="px-6 py-4 text-slate-500 text-sm font-normal italic">john.doe@university.edu</td>
                        <td class="px-6 py-4"><span class="bg-green-100 text-green-600 px-3 py-1 rounded-lg text-[10px] font-bold border border-green-200">● Paid</span></td>
                        <td class="px-6 py-4 text-center text-slate-400 text-xs italic">None</td>
                    </tr>
                    <tr class="student-row hover:bg-slate-50/50 transition-colors" data-status="not paid">
                        <td class="px-6 py-4 text-slate-400 text-sm">02</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-xs border border-purple-50">SM</div>
                                <span class="font-bold text-slate-700 text-sm">Sarah Miller</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-500 text-sm font-medium">Female</td>
                        <td class="px-6 py-4 text-slate-500 text-sm font-medium">SE2024-042</td>
                        <td class="px-6 py-4 text-slate-500 text-sm font-normal italic">s.miller@university.edu</td>
                        <td class="px-6 py-4"><span class="bg-orange-100 text-orange-600 px-3 py-1 rounded-lg text-[10px] font-bold border border-orange-200">● Not Paid</span></td>
                        <td class="px-6 py-4 text-center">
                            <button onclick="toggleModal(true)" class="bg-[#0047ab] text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-blue-800">Pay Now</button>
                        </td>
                    </tr>
                    <tr class="student-row hover:bg-slate-50/50 transition-colors" data-status="rejected">
                        <td class="px-6 py-4 text-slate-400 text-sm">03</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-bold text-xs border border-red-50">RK</div>
                                <span class="font-bold text-slate-700 text-sm">Robert Kim</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-500 text-sm font-medium">Male</td>
                        <td class="px-6 py-4 text-slate-500 text-sm font-medium">SE2024-015</td>
                        <td class="px-6 py-4 text-slate-500 text-sm font-normal italic">r.kim@university.edu</td>
                        <td class="px-6 py-4"><span class="bg-red-100 text-red-600 px-3 py-1 rounded-lg text-[10px] font-bold border border-red-200">● Rejected</span></td>
                        <td class="px-6 py-4 text-center">
                            <button class="bg-white border border-slate-200 text-slate-600 px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-slate-50">Review</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

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
                        <input type="text" placeholder="Enter Student ID..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
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
                    <input type="text" placeholder="Enter your full name..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500  mb-1">Student Email</label>
                    <input type="email" placeholder="Enter Student Email..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
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

                <button type="submit" class="w-full mt-4 bg-brand hover:bg-brandDark text-white font-bold py-3 rounded-xl shadow-lg transition duration-300 active:scale-95 text-sm ">
                    Confirm & Pay Now
                </button>
            </form>
        </div>
    </div>
</div>
<script>
    let currentStatus = 'all';

    function setStatusFilter(element, status) {
        currentStatus = status;

        // Update button styles
        const buttons = document.querySelectorAll('.status-btn');
        buttons.forEach(btn => {
            btn.classList.remove('bg-[#0047ab]', 'text-white');
            btn.classList.add('bg-slate-50', 'text-slate-600');
        });

        element.classList.remove('bg-slate-50', 'text-slate-600');
        element.classList.add('bg-[#0047ab]', 'text-white');

        filterStudents();
    }

    function filterStudents() {
        const searchText = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.student-row');

        rows.forEach(row => {
            const name = row.querySelector('.font-bold.text-slate-700').textContent.toLowerCase();
            const studentId = row.children[3].textContent.toLowerCase();
            const status = row.getAttribute('data-status');

            const matchesSearch = name.includes(searchText) || studentId.includes(searchText);
            const matchesStatus = currentStatus === 'all' || status === currentStatus;

            if (matchesSearch && matchesStatus) {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        });
    }

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

    });
</script>

<?php
    include '../Components/Footer.php';
    include '../Categories/footer.php';
?>