<?php
session_start();
require '../Components/connection.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 2) {
    header('location:../Components/Login.php');
    exit();
}

$email = $_SESSION['email'];

$select = "SELECT 
    s.id,
    s.stu_id,
    s.name,
    s.gender,
    s.email,
    s.created_at,
    c.class_name,
    c.department,
    c.year,
    f.total_fee
FROM tbl_student s
LEFT JOIN tbl_class c ON s.class_id = c.id
LEFT JOIN tbl_fee f ON c.id = f.class_id
WHERE s.email = '$email'
";


$ex = $conn->query($select);
$data = mysqli_fetch_assoc($ex);

include '../Categories/header.php';
?>
<div class="flex min-h-screen bg-gray-200">

    <!-- Sidebar -->
    <div class="w-[270px] bg-gray-50 text-blue-800 flex flex-col justify-between fixed h-full">

        <div>
            <div class="flex items-center gap-3 px-4 mb-8 mt-4">
            <div class="flex items-center cursor-pointer gap-2">
                <img src="https://upload.wikimedia.org/wikipedia/en/a/a2/RUPP_logo.PNG" width="45px" height="45px" alt="">
                <span class="font-semibold fw-bold text-3xl uppercase text-red-500">RUPP<span class="text-blue-500">Pay</span></span>
            </div>
        </div>
        <hr class="text-red-700 border-t-2 py-3">

            <nav class="flex flex-col gap-2 px-4">

                <button onclick="showSection('home_button', this)" class="flex items-center gap-3 p-2 text-[17px] rounded-lg bg-gray-300 fw-medium">
                    <i class="fa-solid fa-house"></i> Home
                </button>

                <button onclick="showSection('history_button', this)" class="flex items-center gap-3 p-2 text-[17px] rounded-lg hover:bg-gray-300 fw-medium">
                    <i class="fa-solid fa-clock-rotate-left"></i> history
                </button>

                <button onclick="showSection('student_button', this)" class="flex items-center gap-3 p-2 text-[17px] rounded-lg hover:bg-gray-300 fw-medium">
                    <i class="fa-solid fa-user-graduate"></i> Student
                </button>

                <button onclick="showSection('support_button', this)" class="flex items-center gap-3 p-2 text-[17px] rounded-lg hover:bg-gray-300 fw-medium">
                    <i class="fa-regular fa-comments"></i> Support
                </button>

                <button onclick="showSection('setting_button', this)" class="flex items-center gap-3 p-2 text-[17px] rounded-lg hover:bg-gray-300 fw-medium">
                    <i class="fa-solid fa-gear"></i> Settings
                </button>

            </nav>
        </div>

        <!-- Logout -->
        <div class="p-4">
            <a href="../Components/logout.php"
                class="block text-center bg-red-700 text-white hover:bg-red-500 py-2 rounded-lg">
                Logout
            </a>
        </div>

    </div>

    <!-- Main Content -->
    <div class="ml-[270px] flex-1 p-8">
    
    <!-- Home Page -->
    <div id="home_button" class="show_hide">
        <!-- Welcome -->
    <div class=" text-black bg-white rounded-2xl p-6 shadow mb-6">
        <h1 class="text-2xl font-bold">Hi, <?php echo $data['name'] ?> 👋</h1>
        <p class="text-gray-500 mt-1">Welcome to your student payment system</p>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-3 gap-6">

        <!-- Student Info -->
        <div class="bg-white rounded-xl shadow p-5 col-span-1">
            <h3 class="font-semibold text-gray-700 mb-4">Student Info</h3>

            <div class="space-y-2 text-sm text-gray-600">
                <p><strong>ID:</strong> <?php echo $data['stu_id'] ?></p>
                <p><strong>Name:</strong> <?php echo $data['name'] ?></p>
                <p><strong>Email:</strong> <?php echo $data['email'] ?></p>
                <p><strong>Department:</strong> <?php echo $data['department'] ?></p>
                <p><strong>Class:</strong> <?php echo 'Year '.$data['year'] ?></p>
                <p><strong>Total Fee:</strong> <?php echo $data['total_fee'] ?>$</p>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="bg-white rounded-xl shadow p-6 col-span-2">

            <h3 class="font-semibold text-gray-700 mb-4">Payment Overview</h3>

            <!-- Numbers -->
            <div class="flex justify-between mb-4">
                <div>
                    <p class="text-sm text-gray-500">Total</p>
                    <h2 class="text-xl font-bold">$500</h2>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Paid</p>
                    <h2 class="text-xl font-bold text-green-500">$300</h2>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Remaining</p>
                    <h2 class="text-xl font-bold text-red-500">$200</h2>
                </div>
            </div>

            <!-- Progress -->
            <div class="w-full bg-gray-200 rounded-full h-3 mb-3">
                <div class="bg-blue-600 h-3 rounded-full" style="width: 60%"></div>
            </div>

            <p class="text-sm text-gray-500 mb-4">60% completed</p>

            <!-- Button -->
            <button onclick="openPayModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow transition">
                Pay Now
            </button>

        </div>

    </div>

    <!-- Payment Modal -->
    <div id="payModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white w-[400px] rounded-xl p-6 shadow-lg">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Pay with Bakong QR</h2>
                <i class="fa-solid fa-xmark cursor-pointer text-2xl" onclick="closePayModal()"></i>
            </div>
            
            <div id="qrLoading" class="text-center py-4">
                <p class="text-gray-500">Generating QR Code...</p>
            </div>
            
            <div id="qrContainer" class="hidden text-center">
                <img id="bakongQR" src="" alt="QR Code" class="w-[200px] h-[200px] mx-auto mb-4">
                <p class="text-sm text-gray-500 mb-2">Scan with Bakong App</p>
                <p class="font-bold text-lg mb-4">Amount: $<span id="payAmount"></span></p>
                
                <div class="flex gap-2">
                    <button onclick="closePayModal()" class="flex-1 bg-gray-400 text-white px-4 py-2 rounded-lg">
                        Cancel
                    </button>
                    <button onclick="confirmPayment()" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg">
                        Confirm
                    </button>
                </div>
            </div>
            
            <div id="qrError" class="hidden text-center py-4">
                <p class="text-red-500">Failed to generate QR</p>
                <p id="errorMsg" class="text-sm text-gray-500"></p>
                <button onclick="closePayModal()" class="mt-2 bg-gray-400 text-white px-4 py-2 rounded-lg">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    <div class="bg-white rounded-xl shadow p-6 mt-6">
        <h3 class="font-semibold text-gray-700 mb-4">Recent Payments</h3>

        <table class="w-full text-sm">
            <thead>
                <tr class="border-b text-gray-500">
                    <th class="py-2 text-left">Date</th>
                    <th class="text-left">Amount</th>
                    <th class="text-left">Status</th>
                </tr>
            </thead>

            <tbody>
                <tr class="border-b">
                    <td class="py-2">2026-04-01</td>
                    <td>$100</td>
                    <td class="text-green-500">Paid</td>
                </tr>

                <tr>
                    <td class="py-2">2026-04-10</td>
                    <td>$200</td>
                    <td class="text-green-500">Paid</td>
                </tr>
            </tbody>
        </table>
    </div>
    </div>

    <!-- history Page -->
    <div id="history_button" class="show_hide" style="display: none;">
        <h1 class="fw-bold text-2xl">history Page</h1>
        <div class="bg-white">
            <p class="py-5">HI! This is history to show the graph or chart....</p>
        </div>
    </div>
    <!-- Student page -->
    <div id="student_button" class="show_hide" style="display: none;">
        <div class="flex justify-between items-center mb-5">
            <h1 class="text-2xl font-bold">Student Management</h1>
        </div>
        <p class="bg-white p-2 py-5 rounded-lg">This is Student Management</p>
    </div>

    <div id="support_button" class="show_hide" style="display: none;">
        <h1 class="text-2xl font-bold">Support</h1>
        <div class="bg-white p-5 rounded-lg shadow mt-4">
            <p class="">Seeing your payment in the class....</p>
        </div>
    </div>

    <div id="setting_button" class="show_hide" style="display: none;">
        <h1 class="text-2xl font-bold">Settings</h1>
        <div class="bg-white p-5 rounded-lg shadow mt-4">
            <p>Please set your favorite in my UI Website....</p>
        </div>
    </div>

</div>

</div>

<!-- Modal -->
<div id="studentModal"
    class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">

    <div class="bg-white w-[500px] rounded-xl p-6 shadow-lg">

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Add Student</h2>
            <i class="fa-solid fa-xmark cursor-pointer" onclick="closeModal()"></i>
        </div>

        <form action="../Components/insert_student.php" method="POST">

            <input type="text" name="stu_id" placeholder="Student ID"
                class="w-full mb-3 px-3 py-2 border rounded-lg" required>

            <input type="text" name="name" placeholder="Name"
                class="w-full mb-3 px-3 py-2 border rounded-lg" required>

            <select name="gender" class="w-full mb-3 px-3 py-2 border rounded-lg">
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>

            <input type="email" name="email" placeholder="Email"
                class="w-full mb-3 px-3 py-2 border rounded-lg" required>

            <input type="text" name="password" placeholder="Password"
                class="w-full mb-3 px-3 py-2 border rounded-lg" required>

            <input type="number" name="class_id" placeholder="Class ID"
                class="w-full mb-3 px-3 py-2 border rounded-lg">

            <div class="flex justify-end gap-2 mt-3">
                <button type="button" onclick="closeModal()"
                    class="bg-gray-400 text-white px-4 py-2 rounded-lg">
                    Cancel
                </button>

                <button type="submit"
                    class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded-lg">
                    Save
                </button>
            </div>

        </form>

    </div>
</div>

<script>
    function openModal() {
        document.getElementById('studentModal').classList.remove('hidden');
        document.getElementById('studentModal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('studentModal').classList.add('hidden');
    }
// for change page with button in sidear
    // Function សម្រាប់ប្តូរ Page និងដូរពណ៌ប៊ូតុង
    function showSection(id, btnElement) {
        // ១. លាក់រាល់ Section មាតិកាទាំងអស់
        var sections = document.getElementsByClassName('show_hide');
        for (var i = 0; i < sections.length; i++) {
            sections[i].style.display = 'none';
        }
        
        // ២. បង្ហាញ Section ដែលបានរើស
        document.getElementById(id).style.display = 'block';

        // ៣. ដកពណ៌ដិត (Active) ចេញពីប៊ូតុងទាំងអស់ក្នុង nav
        var buttons = document.querySelectorAll('nav button');
        buttons.forEach(btn => {
            btn.classList.remove('bg-gray-300');
            btn.classList.add('hover:bg-gray-300');
        });

        // ៤. បន្ថែមពណ៌ដិតទៅឱ្យប៊ូតុងដែលយើងកំពុងចុច
        btnElement.classList.add('bg-gray-300');
        btnElement.classList.remove('hover:bg-gray-300');
    }
</script>

<?php include '../Categories/footer.php'; ?>