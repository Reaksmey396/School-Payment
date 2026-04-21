<?php

// this code is process

require 'connection.php';
session_start();

if (isset($_POST['login'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $select = "SELECT * FROM tbl_account WHERE email='$email' AND password='$password'";
    $ex = mysqli_query($conn, $select);

    if (mysqli_num_rows($ex) > 0) {

        $row = mysqli_fetch_assoc($ex);
        $_SESSION['is_admin'] = $row['is_admin'];

        if($row['is_admin'] == 1){
            header('Location: ../Pages/dashboards.php');
            exit();
        }
        else if($row['is_admin'] == 0){
            header('Location:../Pages/Home.php');
            exit();
        }elseif ($row['is_admin'] == 2) {

                // បង្ខំឱ្យវាទៅកាន់ទំព័រ Student ដោយប្រើ JS
                echo "  <script>
                            alert('Login ជោគជ័យក្នុងនាមជា Student!');
                            window.location.href='../Pages/Stu_dashoard.php';
                        </script>
                    ";
                exit();
            }
            else{
                echo 'user not found';
            }
    } else {
        echo "<script>
            alert('User not found!');
            window.location.href='Login.php';
        </script>";
        exit();
    }
}


?>
<!-- ssss -->

<?php

// For Session code

session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('location:../Components/Login.php');
    exit();
}

include '../Categories/header.php';
?>

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="bg-slate-50 min-h-screen relative flex font-sans text-slate-900">
    <aside class="w-66 bg-white border-r border-gray-100 flex flex-col p-4 shadow-sm">
        <div class="flex items-center gap-3 px-2 mb-8 mt-2">
            <div class="flex items-center cursor-pointer gap-2">
                <img src="https://upload.wikimedia.org/wikipedia/en/a/a2/RUPP_logo.PNG" width="35px" height="35px" alt="">
                <span class="font-semibold fw-bold text-3xl uppercase text-red-500">RUPP<span class="text-blue-500">Pay</span></span>
            </div>
        </div>
        <nav class="flex-col space-y-1">

            <a id="nav-home" onclick="showSection('home', this)"
                class="menu-link flex items-center gap-4 px-4 py-3 bg-gray-100/80 rounded-2xl text-blue-600 transition-all duration-300 cursor-pointer">
                <i class="fa-solid fa-house-chimney"></i>
                <span class="font-semibold text-[15px]">Home</span>
            </a>

            <a id="nav-student" onclick="showSection('student', this)"
                class="menu-link flex items-center gap-4 px-4 py-3 text-slate-600 hover:bg-gray-50 rounded-2xl transition-all duration-300 cursor-pointer">
                <i class="fa-solid fa-user-graduate"></i>
                <span class="font-medium text-[15px]">Student</span>
            </a>

            <a id="nav-register" onclick="showSection('register', this)"
                class="menu-link flex items-center gap-4 px-4 py-3 text-slate-600 hover:bg-gray-50 rounded-2xl transition-all duration-300 cursor-pointer">
                <i class="fa-regular fa-address-card"></i>
                <span class="font-medium text-[15px]">Add Class</span>
            </a>

            <a id="nav-payment" onclick="showSection('payment', this)"
                class="menu-link flex items-center gap-4 px-4 py-3 text-slate-600 hover:bg-gray-50 rounded-2xl transition-all duration-300 cursor-pointer">
                <i class="fa-solid fa-sack-dollar"></i>
                <span class="font-medium text-[15px]">Payment</span>
            </a>

            <a id="nav-recent" onclick="showSection('recent', this)"
                class="menu-link flex items-center gap-4 px-4 py-3 text-slate-600 hover:bg-gray-50 rounded-2xl transition-all duration-300 cursor-pointer">
                <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-medium text-[15px]">Recent (Delete)</span>
            </a>

            <a id="nav-setting" onclick="showSection('setting', this)"
                class="menu-link flex items-center gap-4 px-4 py-3 text-slate-600 hover:bg-gray-50 rounded-2xl transition-all duration-300 cursor-pointer">
                <i class="fa-solid fa-gear"></i>
                <span class="font-medium text-[15px]">Setting</span>
            </a>
        </nav>

        <div class="mt-35">
            <?php
            if (isset($_SESSION['is_admin'])) {
                echo '
                    <a href="../Components/logout.php" class="text-gray-100 hover:bg-red-400 bg-red-600 relative px-5 py-3 rounded-lg font-medium cursor-pointer">Logout <i class="fa-solid fa-arrow-right mx-1"></i></a>
                ';
            } else {
                echo '
                    <a href="../Components/Login.php" class="text-gray-100 hover:bg-blue-500 bg-blue-600 px-4 float-end relative bottom-2 py-2 rounded-lg font-medium cursor-pointer">Login</a>
                    <a href="../Components/Register.php" class="text-gray-100 hover:bg-blue-500 bg-blue-600 px-4 float-end relative bottom-2 py-2 rounded-lg font-medium cursor-pointer">Register</a>
                ';
            }
            ?>
        </div>
    </aside>

    <main class="flex-1 overflow-x-hidden p-10">

        <!-- ===================== HOME SECTION ===================== -->
        <div id="home-section">
            <div class="mb-8">
                <h2 class="text-3xl font-bold">Hi, Welcome To Admin!</h2>
                <p class="text-slate-500">Real-time tracking of school fee collections and student account statuses.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <?php
                include '../Components/connection.php';

                $query_revenue = "SELECT SUM(fee) as total_paid FROM tbl_student";
                $res_revenue = mysqli_query($conn, $query_revenue);
                $row_revenue = mysqli_fetch_assoc($res_revenue);
                $total_revenue = $row_revenue['total_paid'] ?? 0;

                $query_students = "SELECT COUNT(id) as total_stu FROM tbl_student";
                $res_students = mysqli_query($conn, $query_students);
                $row_students = mysqli_fetch_assoc($res_students);
                $total_students = $row_students['total_stu'] ?? 0;

                $query_pending = "SELECT COUNT(id) as total_pen FROM tbl_student WHERE status = 'partial'";
                $res_pending = mysqli_query($conn, $query_pending);
                $row_pending = mysqli_fetch_assoc($res_pending);
                $total_pending = $row_pending['total_pen'] ?? 0;
                ?>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-start">
                    <div class="mb-2">
                        <p class="text-slate-400 text-sm font-medium mb-2">Total Revenue</p>
                        <h3 class="text-3xl font-bold">$<?php echo number_format($total_revenue, 2); ?></h3>
                        <p class="text-green-500 text-xs mt-2 font-semibold"> +12.5% <span class="text-slate-400 font-normal">vs last term</span></p>
                    </div>
                    <div class="bg-green-100 relative py-3 top-4 px-4 rounded-lg text-green-600"><i class="fa-solid fa-wallet"></i></div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-start">
                    <div class="mb-2">
                        <p class="text-slate-400 text-sm font-medium mb-2">Total Students</p>
                        <h3 class="text-3xl font-bold"><?php echo number_format($total_students); ?></h3>
                        <p class="text-blue-500 text-xs mt-2 font-semibold"> +3.2% <span class="text-slate-400 font-normal">new enrollments</span></p>
                    </div>
                    <div class="bg-blue-100 relative py-3 top-4 px-4 rounded-lg text-blue-600"><i class="fa-solid fa-users"></i></div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-start">
                    <div class="mb-2">
                        <p class="text-slate-400 text-sm font-medium mb-2">Pending Payments</p>
                        <h3 class="text-3xl font-bold"><?php echo $total_pending; ?></h3>
                        <p class="text-red-500 text-xs mt-2 font-semibold"> -5.4% <span class="text-slate-400 font-normal">collections effort required</span></p>
                    </div>
                    <div class="bg-orange-100 relative py-3 top-4 px-4 rounded-lg text-orange-600"><i class="fa-solid fa-hourglass-half"></i></div>
                </div>
            </div>

            <!-- Revenue Chart (only shown on home) -->
            <div id="revenue-overview" class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <div class="mb-2">
                        <h4 class="font-bold text-lg">Revenue Overview</h4>
                        <p class="text-slate-400 text-sm">Monthly fee collection trends for current academic year</p>
                    </div>
                    <div class="bg-slate-100 hidden md:block p-1 rounded-lg flex space-x-1 text-xs font-semibold text-slate-600">
                        <button class="px-3 py-1.5 rounded-md hover:bg-white hover:shadow-sm">6 Months</button>
                        <button class="px-3 py-1.5 rounded-md bg-blue-500 text-white shadow-sm">12 Months</button>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- ===================== STUDENT SECTION ===================== -->
        <div id="student-section" class="hidden">
            <div class="justify-between hidden md:flex items-center mb-4">
                <h1 class="font-bold text-3xl md:text-4xl">Student List Overview</h1>
                <div class="relative w-1/2">
                    <i class="fa-solid fa-magnifying-glass absolute right-3 top-3 text-gray-400"></i>
                    <input
                        id="searchDesktop"
                        type="text"
                        placeholder="Search student id or name..."
                        class="form-control w-full py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
            </div>

            <div class="block md:hidden mb-4 mt-5">
                <h1 class="font-bold text-3xl">Student List Overview</h1>
                <p class="text-sm text-slate-500 mt-1 mb-3">Check student list to see student payments.</p>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute right-3 top-3 text-gray-400"></i>
                    <input id="searchMobile" class="form-control w-full pr-10" type="text" placeholder="Search student id or name...">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 mb-10">
                <div class="bg-blue-200 gap-3 grid grid-cols-2 md:grid-cols-4 p-4 rounded-lg">
                    <div class="flex flex-col">
                        <select id="filterFaculty" class="p-2 border rounded bg-white">
                            <option value="Faculty">Faculty</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Science">Science</option>
                            <option value="Education">Education</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <select id="filterDepartment" class="p-2 border rounded bg-white">
                            <option value="Department">Department</option>
                            <option value="ITE">ITE</option>
                            <option value="CS">CS</option>
                            <option value="IBM">IBM</option>
                            <option value="IT">IT</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <select id="filterYear" class="p-2 border rounded bg-white">
                            <option value="Year">Years</option>
                            <option value="1">Year 1</option>
                            <option value="2">Year 2</option>
                            <option value="3">Year 3</option>
                            <option value="4">Year 4</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <select id="filterClass" class="p-2 border rounded bg-white">
                            <option value="Class">Class</option>
                            <option value="A1">A1</option>
                            <option value="A2">A2</option>
                            <option value="A3">A3</option>
                            <option value="A4">A4</option>
                            <option value="M1">M1</option>
                            <option value="M2">M2</option>
                            <option value="M3">M3</option>
                            <option value="M4">M4</option>
                            <option value="E1">E1</option>
                            <option value="E2">E2</option>
                            <option value="E3">E3</option>
                            <option value="E4">E4</option>
                        </select>
                    </div>
                </div>

                <div class="">
                    <button onclick="toggleModal()" class="btn btn-outline-primary float-end">+ Add Student</button>
                </div>

                <div class="overflow-x-auto overflow-y-auto h-[370px] border rounded-lg">
                    <table id="studentTable" class="table table-hover whitespace-nowrap mb-0">
                        <thead class="table-light">
                            <tr class="bg-primary text-white text-center sticky top-0 z-10">
                                <th class="bg-warning">No</th>
                                <th class="bg-warning">Stu-ID</th>
                                <th class="bg-warning">Name</th>
                                <th class="bg-warning">Gender</th>
                                <th class="bg-warning">Stu-Email</th>
                                <th class="bg-warning">Faculty</th>
                                <th class="bg-warning">Department</th>
                                <th class="bg-warning">Year</th>
                                <th class="bg-warning">Class</th>
                                <th class="bg-warning">Fee</th>
                                <th class="bg-warning">Paid</th>
                                <th class="bg-warning">Remaining</th>
                                <th class="bg-warning">Status</th>
                                <th class="text-center bg-warning">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include '../Components/connection.php';

                            $select = "SELECT * FROM tbl_student";
                            $ex = $conn->query($select);
                            while ($row = mysqli_fetch_assoc($ex)) {
                                echo '
                                    <tr class="text-center">
                                        <td>' . $row['id'] . '</td>
                                        <td>' . $row['stu_id'] . '</td>
                                        <td>' . $row['name'] . '</td>
                                        <td>' . $row['gender'] . '</td>
                                        <td>' . $row['email'] . '</td>
                                        <td>' . $row['faculty'] . '</td>
                                        <td>' . $row['department'] . '</td>
                                        <td>' . $row['year'] . '</td>
                                        <td>' . $row['class'] . '</td>
                                        <td>' . $row['fee'] . '</td>
                                        <td>' . $row['paid'] . '</td>
                                        <td>' . $row['remaining'] . '</td>
                                        <td>' . $row['status'] . '</td>
                                        <td class="text-center">
                                            <div class="flex gap-2 justify-center">
                                                <button class="btn btn-sm btn-outline-success">View</button>
                                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                ';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===================== PAYMENT SECTION ===================== -->
        <div id="payment-section" class="hidden">
            <div class="mb-8">
                <h2 class="text-3xl font-bold">Payment Management</h2>
                <p class="text-slate-500">Track and manage all student fee payments.</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                <div class="overflow-x-auto overflow-y-auto h-[430px] border rounded-lg">
                    <table class="table table-hover whitespace-nowrap mb-0">
                        <thead>
                            <tr class="sticky top-0 z-10 text-center">
                                <th class="bg-warning">No</th>
                                <th class="bg-warning">Stu-ID</th>
                                <th class="bg-warning">Name</th>
                                <th class="bg-warning">Fee</th>
                                <th class="bg-warning">Paid</th>
                                <th class="bg-warning">Remaining</th>
                                <th class="bg-warning">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include '../Components/connection.php';
                            $pay_query = "SELECT id, stu_id, name, fee, paid, remaining, status FROM tbl_student";
                            $pay_res = $conn->query($pay_query);
                            while ($p = mysqli_fetch_assoc($pay_res)) {
                                $badge = 'bg-green-100 text-green-700';
                                if ($p['status'] === 'partial') $badge = 'bg-yellow-100 text-yellow-700';
                                if ($p['status'] === 'unpaid')  $badge = 'bg-red-100 text-red-700';
                                echo '
                                    <tr class="text-center">
                                        <td>' . $p['id'] . '</td>
                                        <td>' . $p['stu_id'] . '</td>
                                        <td>' . $p['name'] . '</td>
                                        <td>$' . number_format($p['fee'], 2) . '</td>
                                        <td>$' . number_format($p['paid'], 2) . '</td>
                                        <td>$' . number_format($p['remaining'], 2) . '</td>
                                        <td><span class="px-3 py-1 rounded-full text-xs font-semibold ' . $badge . '">' . ucfirst($p['status']) . '</span></td>
                                    </tr>
                                ';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===================== REGISTER SECTION ===================== -->
        <div id="register-section" class="hidden">
            <div class=" min-h-screen">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold">Register Admin</h2>
                    <p class="text-slate-500">Create a new admin account for the system.</p>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100 max-w-lg">
                    <form action="../Components/register_admin.php" method="POST" class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Full Name</label>
                            <input type="text" name="name" required placeholder="Enter full name"
                                class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                            <input type="email" name="email" required placeholder="admin@rupp.edu.kh"
                                class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password</label>
                            <input type="password" name="password" required placeholder="Enter password"
                                class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-semibold shadow-lg shadow-blue-200 transition-all">
                                Register
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ===================== RECENT SECTION ===================== -->
        <div id="recent-section" class="hidden">
            <div class="mb-8">
                <h2 class="text-3xl font-bold">Recent Activity</h2>
                <p class="text-slate-500">Latest student registrations and payment updates.</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                <div class="overflow-x-auto overflow-y-auto h-[430px] border rounded-lg">
                    <table class="table table-hover whitespace-nowrap mb-0">
                        <thead>
                            <tr class="sticky top-0 z-10 text-center">
                                <th class="bg-warning">No</th>
                                <th class="bg-warning">Stu-ID</th>
                                <th class="bg-warning">Name</th>
                                <th class="bg-warning">Faculty</th>
                                <th class="bg-warning">Department</th>
                                <th class="bg-warning">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include '../Components/connection.php';
                            $recent_query = "SELECT id, stu_id, name, faculty, department, status FROM tbl_student ORDER BY id DESC LIMIT 20";
                            $recent_res = $conn->query($recent_query);
                            while ($r = mysqli_fetch_assoc($recent_res)) {
                                $badge = 'bg-green-100 text-green-700';
                                if ($r['status'] === 'partial') $badge = 'bg-yellow-100 text-yellow-700';
                                if ($r['status'] === 'unpaid')  $badge = 'bg-red-100 text-red-700';
                                echo '
                                    <tr class="text-center">
                                        <td>' . $r['id'] . '</td>
                                        <td>' . $r['stu_id'] . '</td>
                                        <td>' . $r['name'] . '</td>
                                        <td>' . $r['faculty'] . '</td>
                                        <td>' . $r['department'] . '</td>
                                        <td><span class="px-3 py-1 rounded-full text-xs font-semibold ' . $badge . '">' . ucfirst($r['status']) . '</span></td>
                                    </tr>
                                ';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===================== SETTING SECTION ===================== -->
        <div id="setting-section" class="hidden">
            <div class="mb-8">
                <h2 class="text-3xl font-bold">Settings</h2>
                <p class="text-slate-500">Manage your admin account and system preferences.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                    <h4 class="font-bold text-lg mb-5">Change Password</h4>
                    <form action="../Components/change_password.php" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Current Password</label>
                            <input type="password" name="current_password" required placeholder="Enter current password"
                                class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">New Password</label>
                            <input type="password" name="new_password" required placeholder="Enter new password"
                                class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Confirm New Password</label>
                            <input type="password" name="confirm_password" required placeholder="Confirm new password"
                                class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                        <div class="flex justify-end pt-2">
                            <button type="submit" class="px-6 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-semibold shadow-lg shadow-blue-200 transition-all">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                    <h4 class="font-bold text-lg mb-5">System Info</h4>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-3 border-b border-slate-100">
                            <span class="text-slate-500 text-sm">System Name</span>
                            <span class="font-semibold">RUPPPay</span>
                        </div>
                        <div class="flex justify-between items-center py-3 border-b border-slate-100">
                            <span class="text-slate-500 text-sm">University</span>
                            <span class="font-semibold">RUPP</span>
                        </div>
                        <div class="flex justify-between items-center py-3 border-b border-slate-100">
                            <span class="text-slate-500 text-sm">Version</span>
                            <span class="font-semibold">1.0.0</span>
                        </div>
                        <div class="flex justify-between items-center py-3">
                            <span class="text-slate-500 text-sm">Admin Session</span>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================== ADD STUDENT MODAL ===================== -->
        <div id="addStudentModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="md:text-4xl text-xl text-center font-bold text-slate-800">Add New Student</h3>
                    <button onclick="toggleModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <form action="../Components/insert_student.php" method="POST" class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div class="mb-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Student ID</label>
                            <input type="text" name="stu_id" required placeholder="stu0000"
                                class="w-full px-4 py-2 border border-slate-200 rounded-xl">
                        </div>
                        <div class="mb-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Full Name</label>
                            <input type="text" name="name" required placeholder="Enter full name"
                                class="w-full px-4 py-2 border border-slate-200 rounded-xl">
                        </div>
                        <div class="mb-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                            <input type="email" name="email" required placeholder="name@rupp.edu.kh"
                                class="w-full px-4 py-2 border border-slate-200 rounded-xl">
                        </div>
                        <div class="mb-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Gender</label>
                            <select name="gender" class="w-full px-4 py-2 border border-slate-200 rounded-xl">
                                <option disabled selected>Other</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Faculty</label>
                            <select name="faculty" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-400 outline-none">
                                <option value="Engineering">Engineering</option>
                                <option value="Science">Science</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Department</label>
                            <select name="department" class="w-full px-4 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-400 outline-none">
                                <option value="ITE">ITE</option>
                                <option value="IT">IT</option>
                                <option value="IBM">IBM</option>
                                <option value="English">English</option>
                            </select>
                        </div>
                    </div>

                    <div class="bg-blue-50 p-4 rounded-xl grid grid-cols-2 gap-4 border border-blue-100">
                        <div class="mb-2">
                            <label class="block text-xs font-bold text-blue-700 uppercase mb-1">Total Fee ($)</label>
                            <input type="number" step="0.01" name="fee" value="600.00" class="w-full px-3 py-2 border border-blue-200 rounded-lg outline-none">
                        </div>
                        <div class="mb-2">
                            <label class="block text-xs font-bold text-blue-700 uppercase mb-1">Paid Amount ($)</label>
                            <input type="number" step="0.01" name="paid" placeholder="0.00" class="w-full px-3 py-2 border border-blue-200 rounded-lg outline-none">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" onclick="toggleModal()" class="px-5 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow-lg shadow-blue-200 font-semibold transition-all">
                            Save Student
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </main>
</div>

<script>
    // All section IDs mapped to nav IDs
    const sectionMap = {
        'home': 'home-section',
        'student': 'student-section',
        'payment': 'payment-section',
        'register': 'register-section',
        'recent': 'recent-section',
        'setting': 'setting-section'
    };

    function showSection(sectionName, element) {
        // Hide all sections
        Object.values(sectionMap).forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        });

        // Show the selected section
        const target = document.getElementById(sectionMap[sectionName]);
        if (target) target.classList.remove('hidden');

        // Reset all menu links to inactive
        document.querySelectorAll('.menu-link').forEach(link => {
            link.classList.remove('bg-gray-100/80', 'text-blue-600', 'font-semibold');
            link.classList.add('text-slate-600', 'font-medium');
        });

        // Set clicked link to active
        element.classList.add('bg-gray-100/80', 'text-blue-600', 'font-semibold');
        element.classList.remove('text-slate-600', 'font-medium');
    }

    // Revenue Chart
    const dynamicMonths = [];
    const today = new Date();
    for (let i = 4; i >= 0; i--) {
        const d = new Date(today.getFullYear(), today.getMonth() - i, 1);
        dynamicMonths.push(d.toLocaleString('en-US', {
            month: 'short'
        }));
    }

    const canvas = document.getElementById('revenueChart');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dynamicMonths,
                datasets: [{
                    label: 'Revenue',
                    data: [<?php echo $total_students ?>],
                    borderColor: '#3b82f6',
                    borderWidth: 3,
                    fill: true,
                    backgroundColor: gradient,
                    tension: 0.4,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Payment Date',
                            color: '#64748b',
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Student Paid',
                            color: '#64748b',
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            color: '#f1f5f9'
                        }
                    }
                }
            }
        });
    }

    // Add Student Modal
    function toggleModal() {
        const modal = document.getElementById('addStudentModal');
        modal.classList.toggle('hidden');
    }

    window.onclick = function(event) {
        const modal = document.getElementById('addStudentModal');
        if (event.target == modal) toggleModal();
    }

    // Search filter (works for both desktop and mobile inputs)
    document.addEventListener('DOMContentLoaded', function() {
        const searchDesktop = document.getElementById('searchDesktop');
        const searchMobile = document.getElementById('searchMobile');
        const studentTable = document.getElementById('studentTable');

        if (studentTable) {
            const rows = studentTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            const filterTable = (event) => {
                const filter = event.target.value.toLowerCase();
                for (let i = 0; i < rows.length; i++) {
                    const stuId = rows[i].cells[1].textContent.toLowerCase();
                    const name = rows[i].cells[2].textContent.toLowerCase();
                    rows[i].style.display = (stuId.includes(filter) || name.includes(filter)) ? '' : 'none';
                }
            };

            if (searchDesktop) searchDesktop.addEventListener('input', filterTable);
            if (searchMobile) searchMobile.addEventListener('input', filterTable);
        }

        // Dropdown filters
        const fFaculty = document.getElementById('filterFaculty');
        const fDept = document.getElementById('filterDepartment');
        const fYear = document.getElementById('filterYear');
        const fClass = document.getElementById('filterClass');
        const table = document.getElementById('studentTable');

        function filterStudents() {
            if (!table) return;
            const rows = table.querySelectorAll('tbody tr');
            const facultyVal = fFaculty.value.toLowerCase().trim();
            const deptVal = fDept.value.toLowerCase().trim();
            const yearVal = fYear.value.toLowerCase().trim();
            const classVal = fClass.value.toLowerCase().trim();

            rows.forEach(row => {
                const rowFaculty = row.cells[5] ? row.cells[5].textContent.toLowerCase().trim() : '';
                const rowDept = row.cells[6] ? row.cells[6].textContent.toLowerCase().trim() : '';
                const rowYear = row.cells[7] ? row.cells[7].textContent.toLowerCase().trim() : '';
                const rowClass = row.cells[8] ? row.cells[8].textContent.toLowerCase().trim() : '';

                const matchFaculty = (facultyVal === 'faculty' || rowFaculty === facultyVal);
                const matchDept = (deptVal === 'department' || rowDept === deptVal);
                const matchYear = (yearVal === 'year' || yearVal === 'years' || rowYear === yearVal || rowYear.includes(yearVal));
                const matchClass = (classVal === 'class' || rowClass === classVal || rowClass.includes(classVal));

                row.style.display = (matchFaculty && matchDept && matchYear && matchClass) ? '' : 'none';
            });
        }

        if (fFaculty) fFaculty.addEventListener('change', filterStudents);
        if (fDept) fDept.addEventListener('change', filterStudents);
        if (fYear) fYear.addEventListener('change', filterStudents);
        if (fClass) fClass.addEventListener('change', filterStudents);
    });
</script>

<?php include '../Categories/footer.php'; ?>