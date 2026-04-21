<?php
include '../Components/Navbar.php';
include '../Categories/header.php';
?>

<div class="font-sans bg-gray-50 text-gray-900">

    <div class="max-w-7xl mx-auto mt-18 px-6 sm:px-8 py-10">

        <!-- ===== PAGE TITLE ROW ===== -->
        <div class="flex items-start justify-between gap-5 mb-8">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">Payment History</h1>
                <p class="text-gray-400 text-sm">View, manage, and download your past transaction records for the 2023-2024 academic year.</p>
            </div>
            <button class="flex items-center gap-2 bg-blue-900 hover:bg-blue-950 text-white text-sm font-semibold px-5 py-3 rounded-xl flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export Report
            </button>
        </div>

        <!-- ===== STAT CARDS ===== -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-8">

            <!-- Total Money -->
            <div class="bg-white border border-gray-100 rounded-2xl px-6 py-5 flex items-center gap-4 shadow-sm">
                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-1">Total Paid</p>
                    <p class="text-2xl font-bold text-gray-900">$12,450.00</p>
                </div>
            </div>
                <!-- Total Paid -->
            <div class="bg-white border border-gray-100 rounded-2xl px-6 py-5 flex items-center gap-4 shadow-sm">
                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-1">Total Paid</p>
                    <p class="text-2xl font-bold text-gray-900">10</p>
                </div>
            </div>

            <!-- Pending -->
            <div class="bg-white border border-gray-100 rounded-2xl px-6 py-5 flex items-center gap-4 shadow-sm">
                <div class="w-12 h-12 bg-orange-50 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-1">Pending</p>
                    <p class="text-2xl font-bold text-gray-900">$0.00</p>
                </div>
            </div>

            <!-- Transactions -->
            <div class="bg-white border border-gray-100 rounded-2xl px-6 py-5 flex items-center gap-4 shadow-sm">
                <div class="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-1">Transactions</p>
                    <p class="text-2xl font-bold text-gray-900">14 Records</p>
                </div>
            </div>

        </div>
        <!-- Table History -->
        <div class="">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr class="text-center">
                        <th>Date</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="">
                    <tr class="text-center">
                        <td>20.10.2025</td>
                        <td>Stu-0001</td>
                        <td>Khim Reaksmey</td>
                        <td>khimreaksmey123@gmail.com</td>
                        <td>Rejected</td>
                        <td>
                            <button class="px-3 py-1 text-sm bg-blue-500 hover:bg-blue-400 fw-bold rounded-lg">Pay Now</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include '../Components/Footer.php';
include '../Categories/footer.php';
?>