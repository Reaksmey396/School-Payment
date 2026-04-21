<?php
include '../Components/Navbar.php';
include '../Categories/header.php';
?>

<div class="bg-slate-50 p-4 relative top-15 font-sans">

    <div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-[#001f3f]">Department of Software Engineering</h2>
            <p class="text-slate-500 mt-2 max-w-3xl">Manage and view class-wise fee payments and student records. Select a class to drill down into detailed payment reports.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-start">
                <div>
                    <p class="text-slate-400 text-sm font-medium mb-2">Total Revenue</p>
                    <h3 class="text-3xl font-bold">$5,890.00</h3>
                </div>
                <div class="bg-green-100 relative py-3 top-4 px-4 rounded-lg text-green-600"><i class="fa-solid fa-wallet"></i></div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-start">
                <div>
                    <p class="text-slate-400 text-sm font-medium mb-2">Total Students</p>
                    <h3 class="text-3xl font-bold">204</h3>
                </div>
                <div class="bg-blue-100 relative py-3 top-4 px-4 rounded-lg text-blue-600"><i class="fa-solid fa-users"></i></div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-start">
                <div>
                    <p class="text-slate-400 text-sm font-medium mb-2">Pending Payments</p>
                    <h3 class="text-3xl font-bold">24</h3>
                </div>
                <div class="bg-orange-100 relative py-3 top-4 px-4 rounded-lg text-orange-600"><i class="fa-solid fa-hourglass-half"></i></div>
            </div>
        </div>

        <div class="flex justify-between items-center mb-6">
            <h4 class="font-bold text-xl text-[#001f3f]">Available Classes</h4>
            <button class="text-blue-500 text-sm font-bold flex items-center">
                <i class="fa-solid fa-filter mr-2"></i> Filter List
            </button>
        </div>

        <div class="flex space-x-3 mb-8 overflow-x-auto pb-2">
            <button onclick="filterClass('all')" class="filter-btn active bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-bold shadow-md transition-all">All</button>
            <button onclick="filterClass('year1')" class="filter-btn bg-white text-slate-600 px-6 py-2 rounded-lg text-sm font-bold border border-slate-200 hover:bg-slate-50 transition-all">Year 1</button>
            <button onclick="filterClass('year2')" class="filter-btn bg-white text-slate-600 px-6 py-2 rounded-lg text-sm font-bold border border-slate-200 hover:bg-slate-50 transition-all">Year 2</button>
            <button onclick="filterClass('year3')" class="filter-btn bg-white text-slate-600 px-6 py-2 rounded-lg text-sm font-bold border border-slate-200 hover:bg-slate-50 transition-all">Year 3</button>
            <button onclick="filterClass('year4')" class="filter-btn bg-white text-slate-600 px-6 py-2 rounded-lg text-sm font-bold border border-slate-200 hover:bg-slate-50 transition-all">Year 4</button>
        </div>

        <div id="classList" class="space-y-4">
            <div class="class-card bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex items-center justify-between" data-year="year1">
                <div class="flex items-center space-x-4">
                    <div class="bg-orange-50 text-blue-500 p-3 rounded-lg"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <h5 class="font-bold text-[#001f3f]">Software Engineering - Year 1, Class A</h5>
                        <div class="flex space-x-4 text-xs mt-1">
                            <span class="text-slate-400"><i class="fa-solid fa-user mr-1"></i> 120 Students</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="text-slate-300"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                    <a href="../Pages/List.php" class="no-underline bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-bold hover:bg-blue-400 transition-all">Explore</a>
                </div>
            </div>

            <div class="class-card bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex items-center justify-between" data-year="year2">
                <div class="flex items-center space-x-4">
                    <div class="bg-orange-50 text-blue-500 p-3 rounded-lg"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <h5 class="font-bold text-[#001f3f]">Software Engineering - Year 2, Class A</h5>
                        <div class="flex space-x-4 text-xs mt-1">
                            <span class="text-slate-400"><i class="fa-solid fa-user mr-1"></i> 108 Students</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="text-slate-300"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                    <a href="../Pages/List.php" class="no-underline bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-bold hover:bg-blue-400 transition-all">Explore</a>
                </div>
            </div>

            <div class="class-card bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex items-center justify-between" data-year="year3">
                <div class="flex items-center space-x-4">
                    <div class="bg-orange-50 text-blue-500 p-3 rounded-lg"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <h5 class="font-bold text-[#001f3f]">Software Engineering - Year 3, Class A</h5>
                        <div class="flex space-x-4 text-xs mt-1">
                            <span class="text-slate-400"><i class="fa-solid fa-user mr-1"></i> 98 Students</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="text-slate-300"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                    <a href="../Pages/List.php" class="no-underline bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-bold hover:bg-blue-400 transition-all">Explore</a>
                </div>
            </div>
            <div class="class-card bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex items-center justify-between" data-year="year3">
                <div class="flex items-center space-x-4">
                    <div class="bg-orange-50 text-blue-500 p-3 rounded-lg"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <h5 class="font-bold text-[#001f3f]">Software Engineering - Year 3, Class A</h5>
                        <div class="flex space-x-4 text-xs mt-1">
                            <span class="text-slate-400"><i class="fa-solid fa-user mr-1"></i> 98 Students</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="text-slate-300"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                    <a href="../Pages/List.php" class="no-underline bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-bold hover:bg-blue-400 transition-all">Explore</a>
                </div>
            </div>

            <div class="class-card bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex items-center justify-between" data-year="year4">
                <div class="flex items-center space-x-4">
                    <div class="bg-orange-50 text-blue-500 p-3 rounded-lg"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <h5 class="font-bold text-[#001f3f]">Software Engineering - Year 4, Class A</h5>
                        <div class="flex space-x-4 text-xs mt-1">
                            <span class="text-slate-400"><i class="fa-solid fa-user mr-1"></i> 82 Students</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="text-slate-300"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                    <a href="../Pages/List.php" class="no-underline bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-bold hover:bg-blue-400 transition-all">Explore</a>
                </div>
            </div>
        </div>
    </div>

</div>
    <script>
        function filterClass(year) {
            // 1. ប្តូរពណ៌ប៊ូតុងដែលកំពុងចុច (Active State)
            const buttons = document.querySelectorAll('.filter-btn');
            buttons.forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white', 'shadow-md');
                btn.classList.add('bg-white', 'text-slate-600');
            });

            // កំណត់ប៊ូតុងដែលទើបចុចឱ្យពណ៌ខៀវ
            const activeBtn = event.currentTarget;
            activeBtn.classList.remove('bg-white', 'text-slate-600');
            activeBtn.classList.add('bg-blue-600', 'text-white', 'shadow-md');

            // 2. បង្ហាញ/លាក់ Card តាមឆ្នាំ
            const cards = document.querySelectorAll('.class-card');
            cards.forEach(card => {
                if (year === 'all' || card.getAttribute('data-year') === year) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>

<?php
include '../Components/Footer.php';
include '../Categories/footer.php';
?>