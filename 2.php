<?php
session_start();
require '../Components/connection.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 2) {
    header('location:../Components/Login.php');
    exit();
}
$email = $_SESSION['email'];

// join account + student
$query = "SELECT * 
FROM tbl_account a
JOIN tbl_student s ON a.email = s.email
WHERE a.email = '$email'
";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

include '../Categories/header.php';
?>
<style>
    body {
        font-family: 'Inter', sans-serif;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    .nav-item.active {
        background-color: #f3f4f6;
        color: #002B7F !important;
        border-right: 3px solid #002B7F;
        opacity: 1;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
</style>

<body class="flex h-screen overflow-hidden">

    <aside class="w-64 bg-white border-r border-gray-100 flex-shrink-0 flex flex-col z-20">
        <div class="flex mt-5 ml-4 items-center cursor-pointer gap-2">
            <img src="https://upload.wikimedia.org/wikipedia/en/a/a2/RUPP_logo.PNG" width="40px" height="40px" alt="">
            <span class="font-semibold fw-bold text-3xl uppercase text-red-500">RUPP<span class="text-blue-500">Pay</span></span>
        </div>

        <nav class="flex-1 mt-5 space-y-5">
            <button onclick="switchTab(event, 'dashboard')" class="nav-item active w-full flex items-center space-x-4 px-6 py-3.5 text-gray-500 opacity-70 hover:opacity-100 transition-all">
                <i class="fa-solid fa-table-cells-large text-sm"></i>
                <span class="text-sm font-semibold">Home</span>
            </button>
            <button onclick="switchTab(event, 'payment-history')" class="nav-item w-full flex items-center space-x-4 px-6 py-3.5 text-gray-500 opacity-70 hover:opacity-100 transition-all">
                <i class="fa-solid fa-clock-rotate-left text-sm"></i>
                <span class="text-sm font-semibold">History</span>
            </button>
            <button onclick="switchTab(event, 'fees-paid')" class="nav-item w-full flex items-center space-x-4 px-6 py-3.5 text-gray-500 opacity-70 hover:opacity-100 transition-all">
                <i class="fa-solid fa-money-check-dollar text-sm"></i>
                <span class="text-sm font-semibold">Payment</span>
            </button>
            <button onclick="switchTab(event, 'reports')" class="nav-item w-full flex items-center space-x-4 px-6 py-3.5 text-gray-500 opacity-70 hover:opacity-100 transition-all">
                <i class="fa-solid fa-chart-simple text-sm"></i>
                <span class="text-sm font-semibold">About Stude</span>
            </button>
            <button onclick="switchTab(event, 'reports')" class="nav-item w-full flex items-center space-x-4 px-6 py-3.5 text-gray-500 opacity-70 hover:opacity-100 transition-all">
                <i class="fa-solid fa-chart-simple text-sm"></i>
                <span class="text-sm font-semibold">Settings</span>
            </button>
        </nav>

        <!-- Logout fixed at bottom of sidebar -->
        <div class="p-4 border-t border-gray-100">
            <?php
            if (isset($_SESSION['is_admin'])) {
                echo '
                    <a href="../Components/logout.php" class="flex items-center justify-center text-gray-100 hover:bg-red-400 bg-red-600 px-4 py-3 rounded-lg font-medium cursor-pointer w-full">
                        Logout <i class="fa-solid fa-arrow-right mx-1"></i>
                    </a>
                ';
            } else {
                echo '
                    <a href="../Components/Login.php" class="block text-center text-gray-100 hover:bg-blue-500 bg-blue-600 px-4 py-2 rounded-lg font-medium cursor-pointer mb-2">Login</a>
                    <a href="../Components/Register.php" class="block text-center text-gray-100 hover:bg-blue-500 bg-blue-600 px-4 py-2 rounded-lg font-medium cursor-pointer">Register</a>
                ';
            }
            ?>
        </div>
    </aside>

    <main class="flex-1 flex flex-col min-w-0">
        <header class="h-16 bg-white border-b border-gray-50 flex items-center justify-between px-10 flex-shrink-0">
            <h2 class="text-sm font-bold text-blue-500"><span class="text-red-500">RUPP</span>PAY Institutions</h2>
            <div class="flex items-center space-x-6 text-gray-400 text-sm">
                <i class="fa-solid fa-circle-question cursor-pointer hover:text-blue-600"></i>
                <i class="fa-solid fa-bell cursor-pointer hover:text-blue-600"></i>
                <i class="fa-solid fa-gear cursor-pointer hover:text-blue-600"></i>
                <span class="h-4 w-px bg-gray-200 mx-2"></span>
                <span class="text-xs font-bold text-gray-700 cursor-pointer">Support</span>
            </div>
        </header>

        <!-- Single scroll container — fixed the duplicate nested div -->
        <div class="flex-1 overflow-y-auto p-10">

            <!-- ===================== DASHBOARD ===================== -->
            <div id="dashboard" class="tab-content active">
                <div class="flex">
                    <div class="flex flex-col items-start justify-center mb-10">
                        <div class="bg-gray-300 rounded-full p-1 h-30 w-30">
                            <img src="https://i.pinimg.com/736x/64/c1/c3/64c1c3a7ee9b939305dbd30f92246631.jpg" class="rounded-full h-full w-full object-cover" alt="User Profile">
                        </div>
                        <div class="text-center ml-5 mt-2">
                            <p class="text-xl font-bold text-gray-800"><?php echo $data['name']; ?></p>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">ID: STU00002</p>
                        </div>
                    </div>
                    <div class="ml-7 mt-8">
                        <div>
                            <h3 class="text-2xl md:text-4xl font-bold text-gray-900">Hi! <?php echo $data['name']; ?></h3>
                            <p class="text-gray-400 text-xs mt-1">Manage your academic financial commitments and history.</p>
                        </div>
                        <div class="flex gap-2 mt-4">
                            <button class="bg-pink-600 text-white px-5 py-2 rounded-lg">Pay Fee</button>
                            <button onclick="window.print()" class="px-4 py-2 bg-gray-300 text-gray-600 text-[11px] font-bold rounded flex items-center">
                                <i class="fa-solid fa-print mr-2"></i> Print Statement
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-10">
                    <div class="xl:col-span-2 bg-[#0047AB] rounded-xl p-10 text-white relative shadow-lg">
                        <p class="text-[10px] font-bold opacity-70 mb-2 uppercase tracking-widest">Total Fees Paid</p>
                        <h4 class="text-5xl font-bold tracking-tight">$2,400.00</h4>
                        <div class="mt-10 flex items-center text-[10px] opacity-70">
                            <i class="fa-solid fa-circle-check mr-2 text-blue-300"></i> Updated 2 hours ago • Session 2023-2024
                        </div>
                        <div class="absolute top-1/2 -translate-y-1/2 right-10 bg-white/10 p-4 rounded-xl">
                            <i class="fa-solid fa-wallet text-3xl"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-10 border border-gray-100 shadow-sm">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Pending Balance</p>
                            <h4 class="text-3xl font-bold text-gray-800">$600</h4>
                        </div>
                        <div class="space-y-3">
                            <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-[#0047AB] h-full w-[85%]"></div>
                            </div>
                            <div class="flex justify-between text-[9px] font-bold text-gray-400">
                                <span class="text-blue-600">85% COMPLETED</span>
                                <span>$1,200 TOTAL</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-10">
                    <div class="flex justify-between items-center mt-5">
                        <h5 class="font-bold text-gray-800">Recent Transactions</h5>
                        <p class="text-[10px] text-gray-400 font-bold uppercase cursor-pointer">Filter by: <span class="text-gray-900 ml-1">All Transactions <i class="fa-solid fa-chevron-down ml-1"></i></span></p>
                    </div>
                    <div class="">
                        <table class="table">
                            <thead class="table-hover">
                                <tr class="bg-dark">
                                    <th>Transaction ID</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="py-2 text-sm">TXN-882910</td>
                                    <td class="py-2 text-sm">Semester 2 Tuition Fees</td>
                                    <td class="py-2 text-sm">Oct 24, 2023</td>
                                    <td class="py-2 text-sm">$500.00</td>
                                    <td class="py-2 text-sm text-center"><span class="px-3 py-1 bg-blue-100 text-blue-600 font-bold rounded-full text-[9px]">PAID</span></td>
                                </tr>
                                <tr>
                                    <td class="py-2 text-sm">TXN-882894</td>
                                    <td class="py-2 text-sm">Campus Housing Deposit</td>
                                    <td class="py-2 text-sm">Sep 28, 2023</td>
                                    <td class="py-2 text-sm">$400.00</td>
                                    <td class="py-2 text-sm text-center"><span class="px-3 py-1 bg-indigo-100 text-indigo-600 font-bold rounded-full text-[9px]">PENDING</span></td>
                                </tr>
                                <tr>
                                    <td class="py-2 text-sm">TXN-882881</td>
                                    <td class="py-2 text-sm">Laboratory Equipment Fee</td>
                                    <td class="py-2 text-sm">Sep 15, 2023</td>
                                    <td class="py-2 text-sm">$200.00</td>
                                    <td class="py-2 text-sm text-center"><span class="px-3 py-1 bg-red-100 text-red-500 font-bold rounded-full text-[9px]">FAILED</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-8 text-center">
                        <button class="text-[10px] font-black text-blue-700 uppercase tracking-widest hover:underline">View All Transactions <i class="fa-solid fa-arrow-right ml-2"></i></button>
                    </div>
                </div>

                <div class="bg-gray-100/50 rounded-xl p-8 flex items-center justify-between">
                    <div class="flex items-center space-x-6">
                        <div class="bg-[#0047AB] p-4 rounded-lg text-white">
                            <i class="fa-solid fa-graduation-cap text-2xl"></i>
                        </div>
                        <div>
                            <h6 class="font-bold text-gray-800">Apply for Alumni Grant 2024</h6>
                            <p class="text-xs text-gray-500 max-w-lg mt-1 leading-relaxed">Eligible students can receive up to 15% discount on the upcoming semester fees by applying before November 30th.</p>
                        </div>
                    </div>
                    <button class="px-6 py-2.5 bg-gray-900 text-white text-xs font-bold rounded hover:bg-black transition">Learn More</button>
                </div>
            </div>

            <!-- ===================== PAYMENT HISTORY ===================== -->
            <div id="payment-history" class="tab-content">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900">Payment History</h2>
                    <p class="text-gray-400 text-sm mt-1">All your historical payment records from previous semesters.</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
                        <h5 class="font-bold text-gray-800">All Transactions</h5>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Session 2023–2024</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table mb-0">
                            <thead>
                                <tr class="bg-dark">
                                    <th>Transaction ID</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-sm">TXN-882910</td>
                                    <td class="text-sm">Semester 2 Tuition Fees</td>
                                    <td class="text-sm">Oct 24, 2023</td>
                                    <td class="text-sm">$500.00</td>
                                    <td class="text-sm">Bank Transfer</td>
                                    <td class="text-sm text-center"><span class="px-3 py-1 bg-blue-100 text-blue-600 font-bold rounded-full text-[9px]">PAID</span></td>
                                </tr>
                                <tr>
                                    <td class="text-sm">TXN-882894</td>
                                    <td class="text-sm">Campus Housing Deposit</td>
                                    <td class="text-sm">Sep 28, 2023</td>
                                    <td class="text-sm">$400.00</td>
                                    <td class="text-sm">ABA Pay</td>
                                    <td class="text-sm text-center"><span class="px-3 py-1 bg-indigo-100 text-indigo-600 font-bold rounded-full text-[9px]">PENDING</span></td>
                                </tr>
                                <tr>
                                    <td class="text-sm">TXN-882881</td>
                                    <td class="text-sm">Laboratory Equipment Fee</td>
                                    <td class="text-sm">Sep 15, 2023</td>
                                    <td class="text-sm">$200.00</td>
                                    <td class="text-sm">Cash</td>
                                    <td class="text-sm text-center"><span class="px-3 py-1 bg-red-100 text-red-500 font-bold rounded-full text-[9px]">FAILED</span></td>
                                </tr>
                                <tr>
                                    <td class="text-sm">TXN-881204</td>
                                    <td class="text-sm">Semester 1 Tuition Fees</td>
                                    <td class="text-sm">Mar 10, 2023</td>
                                    <td class="text-sm">$600.00</td>
                                    <td class="text-sm">Bank Transfer</td>
                                    <td class="text-sm text-center"><span class="px-3 py-1 bg-blue-100 text-blue-600 font-bold rounded-full text-[9px]">PAID</span></td>
                                </tr>
                                <tr>
                                    <td class="text-sm">TXN-880091</td>
                                    <td class="text-sm">Library Annual Fee</td>
                                    <td class="text-sm">Feb 5, 2023</td>
                                    <td class="text-sm">$100.00</td>
                                    <td class="text-sm">ABA Pay</td>
                                    <td class="text-sm text-center"><span class="px-3 py-1 bg-blue-100 text-blue-600 font-bold rounded-full text-[9px]">PAID</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ===================== FEES PAID ===================== -->
            <div id="fees-paid" class="tab-content">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900">Fees Paid</h2>
                    <p class="text-gray-400 text-sm mt-1">Breakdown of all charges including tuition, labs, and insurance.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Tuition Fee</p>
                            <h4 class="text-2xl font-bold text-gray-800">$1,200.00</h4>
                            <p class="text-[10px] text-blue-600 font-bold mt-2">FULLY PAID</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-lg text-blue-600">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Laboratory Fee</p>
                            <h4 class="text-2xl font-bold text-gray-800">$200.00</h4>
                            <p class="text-[10px] text-red-500 font-bold mt-2">FAILED</p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-lg text-red-500">
                            <i class="fa-solid fa-flask"></i>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Housing Deposit</p>
                            <h4 class="text-2xl font-bold text-gray-800">$400.00</h4>
                            <p class="text-[10px] text-indigo-500 font-bold mt-2">PENDING</p>
                        </div>
                        <div class="bg-indigo-100 p-3 rounded-lg text-indigo-500">
                            <i class="fa-solid fa-building"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h5 class="font-bold text-gray-800">Fee Breakdown Detail</h5>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table mb-0">
                            <thead>
                                <tr class="bg-dark">
                                    <th>Fee Type</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Paid</th>
                                    <th>Remaining</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-sm">Semester 1 Tuition</td>
                                    <td class="text-sm">Mar 1, 2023</td>
                                    <td class="text-sm">$600.00</td>
                                    <td class="text-sm">$600.00</td>
                                    <td class="text-sm">$0.00</td>
                                    <td class="text-sm text-center"><span class="px-3 py-1 bg-blue-100 text-blue-600 font-bold rounded-full text-[9px]">PAID</span></td>
                                </tr>
                                <tr>
                                    <td class="text-sm">Semester 2 Tuition</td>
                                    <td class="text-sm">Oct 1, 2023</td>
                                    <td class="text-sm">$600.00</td>
                                    <td class="text-sm">$600.00</td>
                                    <td class="text-sm">$0.00</td>
                                    <td class="text-sm text-center"><span class="px-3 py-1 bg-blue-100 text-blue-600 font-bold rounded-full text-[9px]">PAID</span></td>
                                </tr>
                                <tr>
                                    <td class="text-sm">Laboratory Fee</td>
                                    <td class="text-sm">Sep 1, 2023</td>
                                    <td class="text-sm">$200.00</td>
                                    <td class="text-sm">$0.00</td>
                                    <td class="text-sm text-red-500 font-semibold">$200.00</td>
                                    <td class="text-sm text-center"><span class="px-3 py-1 bg-red-100 text-red-500 font-bold rounded-full text-[9px]">FAILED</span></td>
                                </tr>
                                <tr>
                                    <td class="text-sm">Housing Deposit</td>
                                    <td class="text-sm">Sep 28, 2023</td>
                                    <td class="text-sm">$400.00</td>
                                    <td class="text-sm">$0.00</td>
                                    <td class="text-sm text-indigo-500 font-semibold">$400.00</td>
                                    <td class="text-sm text-center"><span class="px-3 py-1 bg-indigo-100 text-indigo-600 font-bold rounded-full text-[9px]">PENDING</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ===================== REPORTS ===================== -->
            <div id="reports" class="tab-content">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900">Reports</h2>
                    <p class="text-gray-400 text-sm mt-1">Generate and download PDF summaries for tax or reimbursement purposes.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="bg-red-100 p-4 rounded-lg text-red-500">
                                <i class="fa-solid fa-file-pdf text-2xl"></i>
                            </div>
                            <div>
                                <h6 class="font-bold text-gray-800">Annual Fee Statement 2023</h6>
                                <p class="text-xs text-gray-400 mt-1">Full breakdown of all fees paid this academic year.</p>
                            </div>
                        </div>
                        <button onclick="window.print()" class="px-4 py-2 bg-gray-900 text-white text-xs font-bold rounded hover:bg-black transition flex items-center gap-2">
                            <i class="fa-solid fa-download"></i> Download
                        </button>
                    </div>
                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="bg-blue-100 p-4 rounded-lg text-blue-600">
                                <i class="fa-solid fa-file-invoice-dollar text-2xl"></i>
                            </div>
                            <div>
                                <h6 class="font-bold text-gray-800">Semester 1 Receipt</h6>
                                <p class="text-xs text-gray-400 mt-1">Official receipt for Semester 1 tuition payment.</p>
                            </div>
                        </div>
                        <button onclick="window.print()" class="px-4 py-2 bg-gray-900 text-white text-xs font-bold rounded hover:bg-black transition flex items-center gap-2">
                            <i class="fa-solid fa-download"></i> Download
                        </button>
                    </div>
                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="bg-blue-100 p-4 rounded-lg text-blue-600">
                                <i class="fa-solid fa-file-invoice-dollar text-2xl"></i>
                            </div>
                            <div>
                                <h6 class="font-bold text-gray-800">Semester 2 Receipt</h6>
                                <p class="text-xs text-gray-400 mt-1">Official receipt for Semester 2 tuition payment.</p>
                            </div>
                        </div>
                        <button onclick="window.print()" class="px-4 py-2 bg-gray-900 text-white text-xs font-bold rounded hover:bg-black transition flex items-center gap-2">
                            <i class="fa-solid fa-download"></i> Download
                        </button>
                    </div>
                    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="bg-green-100 p-4 rounded-lg text-green-600">
                                <i class="fa-solid fa-file-excel text-2xl"></i>
                            </div>
                            <div>
                                <h6 class="font-bold text-gray-800">Full Transaction Export</h6>
                                <p class="text-xs text-gray-400 mt-1">Export all transactions as a spreadsheet.</p>
                            </div>
                        </div>
                        <button class="px-4 py-2 bg-gray-900 text-white text-xs font-bold rounded hover:bg-black transition flex items-center gap-2">
                            <i class="fa-solid fa-download"></i> Export
                        </button>
                    </div>
                </div>
                <div class="bg-gray-100/50 rounded-xl p-8 flex items-center justify-between">
                    <div class="flex items-center space-x-6">
                        <div class="bg-[#0047AB] p-4 rounded-lg text-white">
                            <i class="fa-solid fa-graduation-cap text-2xl"></i>
                        </div>
                        <div>
                            <h6 class="font-bold text-gray-800">Apply for Alumni Grant 2024</h6>
                            <p class="text-xs text-gray-500 max-w-lg mt-1 leading-relaxed">Eligible students can receive up to 15% discount on the upcoming semester fees by applying before November 30th.</p>
                        </div>
                    </div>
                    <button class="px-6 py-2.5 bg-gray-900 text-white text-xs font-bold rounded hover:bg-black transition">Learn More</button>
                </div>
            </div>

        </div><!-- end single scroll container -->
    </main>

    <script>
        function switchTab(event, tabId) {
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => {
                tab.style.display = 'none';
                tab.classList.remove('active');
            });

            const buttons = document.querySelectorAll('.nav-item');
            buttons.forEach(btn => {
                btn.classList.remove('active');
            });

            const targetTab = document.getElementById(tabId);
            if (targetTab) {
                targetTab.style.display = 'block';
                setTimeout(() => {
                    targetTab.classList.add('active');
                }, 10);
            }

            event.currentTarget.classList.add('active');
        }
    </script>
</body>

<?php include '../Categories/footer.php'; ?>