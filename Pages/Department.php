<?php
include '../Components/Navbar.php';
include '../Categories/header.php';
?>

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="bg-slate-50 relative top-15 p-8 font-sans">

    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-[#001f3f]">Department For Views</h2>
            <p class="text-slate-400 text-sm">Browse departments in each faculty and see related academic programs.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Revenue</p>
                    <h3 class="text-3xl font-bold text-[#001f3f]">$5,890.00</h3>
                    <p class="text-green-500 text-[10px] mt-1 font-bold"> +12.5% <span class="text-slate-300 font-normal">vs last term</span></p>
                </div>
                <div class="bg-green-100 p-4 rounded-xl text-green-600">
                    <i class="fa-solid fa-credit-card text-xl"></i>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Students</p>
                    <h3 class="text-3xl font-bold text-[#001f3f]">254</h3>
                    <p class="text-blue-500 text-[10px] mt-1 font-bold"> +3.2% <span class="text-slate-300 font-normal">new enrollments</span></p>
                </div>
                <div class="bg-blue-100 p-4 rounded-xl text-blue-600">
                    <i class="fa-solid fa-users-rectangle text-xl"></i>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pending Payments</p>
                    <h3 class="text-3xl font-bold text-[#001f3f]">14</h3>
                    <p class="text-red-500 text-[10px] mt-1 font-bold"> -5.4% <span class="text-slate-300 font-normal">collections effort required</span></p>
                </div>
                <div class="bg-orange-100 p-4 rounded-xl text-orange-600">
                    <i class="fa-solid fa-clock text-xl"></i>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <!-- Department -->
            <div class="bg-white px-6 py-4 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between group hover:border-blue-200 transition-all cursor-pointer">
                <div class="flex items-center space-x-5">
                    <div class="bg-blue-50 text-blue-500 p-3 rounded-xl">
                        <i class="fa-solid fa-link text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-[#001f3f] text-lg">Information Technology and Engineering</h4>
                        <p class="text-slate-400 text-xs">Technology, engineering design, innovation, and practical problem solving.</p>
                    </div>
                </div>
                <a href="../Pages/Class.php" class=" font-bold px-4 py-2 rounded-lg no-underline border bg-blue-600 border-slate-200 text-xs flex items-cente text-gray-50 hover:bg-blue-400">
                    Explore <i class="fa-solid fa-arrow-right ml-2 mt-1"></i>
                </a>
            </div>
        </div>
    </div>

</div>

<?php
include '../Components/Footer.php';
include '../Categories/footer.php';
?>