<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('location:../Components/Login.php');
    exit();
}

include '../Components/connection.php';

$student_id = isset($_GET['student_id']) ? (int) $_GET['student_id'] : 0;
$paymentStudent = null;

if ($student_id > 0) {
    $stmt = $conn->prepare("
        SELECT
            s.*,
            c.class_name,
            c.department,
            f.id AS fee_id,
            f.total_fee
        FROM tbl_student s
        LEFT JOIN tbl_class c ON s.class_id = c.id
        LEFT JOIN tbl_fee f ON c.id = f.class_id
        WHERE s.id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $paymentStudent = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_POST['pay'])) {
    header('Content-Type: application/json');

    $student_id = isset($_POST['student_id']) ? (int) $_POST['student_id'] : 0;
    $amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
    $method = isset($_POST['method']) ? trim($_POST['method']) : 'Bakong QR';

    if ($student_id <= 0 || $amount <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid payment data.'
        ]);
        exit();
    }

    $stmt = $conn->prepare("
        INSERT INTO tbl_payment (student_id, amount, payment_date, method, bill_no)
        VALUES (?, ?, CURDATE(), ?, ?)
    ");

    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to prepare payment query.',
            'detail' => mysqli_error($conn)
        ]);
        exit();
    }

    $billNo = isset($_POST['bill_no']) ? trim($_POST['bill_no']) : null;
    $stmt->bind_param("idsss", $student_id, $amount, $method, $billNo);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Payment saved successfully.',
            'paid_at' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save payment.',
            'detail' => $stmt->error
        ]);
    }

    $stmt->close();
    exit();
}

include '../Categories/header.php';
?>
<style>
    html {
        scrollbar-gutter: stable;
    }
</style>

<div class="flex min-h-screen bg-gray-100">

    <!-- Sidebar -->
    <div class="w-[270px] bg-gray-50 text-blue-700 flex flex-col justify-between fixed h-full">

        <div>
            <div class="flex items-center gap-3 px-4 mb-8 mt-4">
                <div class="flex items-center cursor-pointer gap-2">
                    <img src="https://upload.wikimedia.org/wikipedia/en/a/a2/RUPP_logo.PNG" width="45px" height="45px" alt="">
                    <span class="font-semibold fw-bold text-3xl uppercase text-red-500">RUPP<span class="text-blue-500">Pay</span></span>
                </div>
            </div>
            <hr class="text-red-700 py-3">

            <nav class="flex flex-col gap-2 px-4">

                <button onclick="showSection('home_button', this)" class="flex items-center gap-3 p-2 text-[17px] rounded-lg bg-gray-300 fw-medium">
                    <i class="fa-solid fa-house"></i> Home
                </button>

                <button onclick="showSection('dashboard_button', this)" class="flex items-center gap-3 p-2 text-[17px] rounded-lg hover:bg-gray-300 fw-medium">
                    <i class="fa-solid fa-chart-simple"></i> Dashboard
                </button>

                <button onclick="showSection('student_button', this)" class="flex items-center gap-3 p-2 text-[17px] rounded-lg hover:bg-gray-300 fw-medium">
                    <i class="fa-solid fa-user-graduate"></i> Student
                </button>

                <button onclick="showSection('class_button', this)" class="flex items-center gap-3 p-2 text-[17px] rounded-lg hover:bg-gray-300 fw-medium">
                    <i class="fa-solid fa-building-columns"></i> Class
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
    <div class="ml-[270px] flex-1 p-8 min-w-0 overflow-hidden">

        <div id="home_button" class="show_hide">
            <h1 class="fw-bold text-2xl">Home Page</h1>
            <div class="bg-white">
                <p class="py-5">HI! This is Admin Dashboard....</p>
            </div>
        </div>
        <div id="dashboard_button" class="show_hide" style="display: none;">
            <h1 class="fw-bold text-2xl">Dashboard Page</h1>
            <div class="bg-white">
                <p class="py-5">HI! This is Dashboard to show the graph or chart....</p>
            </div>
        </div>

        <div id="student_button" class="show_hide" style="display: none;">

            <div class="justify-between hidden md:flex items-center mb-4">
                <h1 class="font-bold text-3xl md:text-4xl">Student List Overview</h1>
                <div class="relative w-1/2">
                    <i class="fa-solid fa-magnifying-glass absolute right-3 top-4 text-gray-400"></i>
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
                    <button onclick="openModal()" class="btn btn-outline-primary float-end">+ Add Student</button>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl shadow overflow-hidden">
                <div class="w-full h-[500px] overflow-x-auto border border-gray-100 rounded-lg">
                    <table id="studentTable" class="table table-hover w-max min-w-full">
                        <thead class="table-primary text-center">
                            <tr>
                                <th class="px-4 py-2 whitespace-nowrap">ID</th>
                                <th class="px-4 py-2 whitespace-nowrap">Student ID</th>
                                <th class="px-4 py-2 whitespace-nowrap">Name</th>
                                <th class="px-4 py-2 whitespace-nowrap">Gender</th>
                                <th class="px-4 py-2 whitespace-nowrap">Email</th>
                                <th class="px-4 py-2 whitespace-nowrap">Faculty</th>
                                <th class="px-4 py-2 whitespace-nowrap">Department</th>
                                <th class="px-4 py-2 whitespace-nowrap">Year</th>
                                <th class="px-4 py-2 whitespace-nowrap">Class</th>
                                <th class="px-4 py-2 whitespace-nowrap">Created</th>
                                <th class="px-4 py-2 whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $select = "
                                SELECT 
                                    s.id,
                                    s.stu_id,
                                    s.name,
                                    s.gender,
                                    s.email,
                                    s.password,
                                    s.created_at,
                                    c.class_name,
                                    c.faculty,
                                    c.department,
                                    c.year
                                FROM tbl_student s
                                LEFT JOIN tbl_class c ON s.class_id = c.id
                            ";

                            $ex = $conn->query($select);

                            while ($row = mysqli_fetch_assoc($ex)) {
                                echo "
                                    <tr class='text-center border-b hover:bg-gray-50'>
                                        <td class='px-4 py-2 whitespace-nowrap'>{$row['id']}</td>
                                        <td class='px-4 py-2 whitespace-nowrap'>{$row['stu_id']}</td>
                                        <td class='px-4 py-2 whitespace-nowrap font-medium'>{$row['name']}</td>
                                        <td class='px-4 py-2 whitespace-nowrap'>{$row['gender']}</td>
                                        <td class='px-4 py-2 whitespace-nowrap'>{$row['email']}</td>
                                        <td class='px-4 py-2 whitespace-nowrap'>{$row['faculty']}</td>
                                        <td class='px-4 py-2 whitespace-nowrap'>{$row['department']}</td>
                                        <td class='px-4 py-2 whitespace-nowrap'>
                                            <span class='px-2 py-1 bg-blue-100 text-blue-600 rounded text-xs'>
                                                Year {$row['year']}
                                            </span>
                                        </td>
                                        <td class='px-4 py-2 whitespace-nowrap'>{$row['class_name']}</td>
                                        <td class='px-4 py-2 whitespace-nowrap'>{$row['created_at']}</td>
                                        <td class='px-4 py-2 whitespace-nowrap'>
                                            <div class='flex gap-2 justify-center'>
                                                <button onclick=\"viewStudent(" . $row['id'] . ")\" class='px-2 py-1 text-blue-600 border border-blue-600 rounded hover:bg-blue-600 hover:text-white'>
                                                    View
                                                </button>
                                                <button class='px-2 py-1 text-red-600 border border-red-600 rounded hover:bg-red-600 hover:text-white'>
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="class_button" class="show_hide" style="display: none;">
            <div class="flex justify-between items-center mb-5">
                <h1 class="text-2xl font-bold">Class Management</h1>
                <button onclick="openModalClass()" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg">
                    <i class="fa-solid fa-plus mr-2"></i> Add Class
                </button>
            </div>

            <div class="bg-white p-4 rounded-xl shadow">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Class List</h2>
                <div class="overflow-x-auto">
                    <table class="table table-hover w-full">
                        <thead class="table-primary text-center">
                            <tr>
                                <th class="p-3">ID</th>
                                <th class="p-3">Class Name</th>
                                <th class="p-3">Faculty</th>
                                <th class="p-3">Department</th>
                                <th class="p-3">Year</th>
                                <th class="p-3">Total Fee</th>
                                <th class="p-3">Created At</th>
                                <th class="p-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // តភ្ជាប់ Database
                            include '../Components/connection.php';
                            $class_id = isset($_GET['class_id']) ? $_GET['class_id'] : 0;

                            // Select ទិន្នន័យពី tbl_class (តម្រៀបតាម ID ធំមកតូច)
                            $select_class = "
                                SELECT 
                                    c.*, 
                                    f.total_fee 
                                FROM tbl_class c
                                LEFT JOIN tbl_fee f ON c.department = f.department 
                                ORDER BY c.id
                            ";
                            $ex_class = $conn->query($select_class);

                            if ($ex_class->num_rows > 0) {
                                while ($row = mysqli_fetch_assoc($ex_class)) {
                                    // កំណត់ពណ៌តាមឆ្នាំ (Option)
                                    $yearBadge = "";
                                    switch ($row['year']) {
                                        case 1:
                                            $yearBadge = "bg-blue-100 text-blue-700";
                                            break;
                                        case 2:
                                            $yearBadge = "bg-green-100 text-green-700";
                                            break;
                                        case 3:
                                            $yearBadge = "bg-yellow-100 text-yellow-700";
                                            break;
                                        case 4:
                                            $yearBadge = "bg-purple-100 text-purple-700";
                                            break;
                                        default:
                                            $yearBadge = "bg-gray-100";
                                    }

                                    echo "<tr class='text-center align-middle border-b'>
                                    <td class='p-3'>{$row['id']}</td>
                                    <td class='p-3 font-bold text-blue-600'>{$row['class_name']}</td>
                                    <td class='p-3'>{$row['faculty']}</td>
                                    <td class='p-3'>{$row['department']}</td>
                                    <td class='p-3'>
                                        <span class='px-3 py-1 rounded-full text-xs font-semibold {$yearBadge}'>
                                            Year {$row['year']}
                                        </span>
                                    </td>
                                    <td class='p-3'>{$row['total_fee']}</td>
                                    <td class='p-3 text-gray-500 text-sm'>" . date('d-M-Y', strtotime($row['created_at'])) . "</td>
                                    <td class='p-3'>
                                        <div class='flex gap-4 justify-center'>
                                            <button onclick=\"viewClass(" . $row['id'] . ")\" class=' text-blue-600 hover:text-blue-800' title='View students in class'>
                                                <i class='fa-solid fa-pen-to-square'></i>
                                            </button>
                                            <button class='text-red-600 hover:text-red-800' title='Delete'>
                                                <i class='fa-solid fa-trash'></i>
                                            </button>
                                        </div>
                                    </td>
                                  </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center p-5 text-gray-500'>No classes found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>



        <div id="setting_button" class="show_hide" style="display: none;">
            <h1 class="text-2xl font-bold">Settings</h1>
            <div class="bg-white p-5 rounded-lg shadow mt-4">
                <p>Please set your favorite in my UI Website....</p>
            </div>
        </div>
        <?php
        $select_class = "
                SELECT COUNT(*) as total_students
                FROM tbl_student 
                WHERE class_id = '$class_id'
            ";

        $execute = $conn->query($select_class);
        $datas = mysqli_fetch_assoc($execute);

        $select_total_fee = "SELECT total_fee FROM tbl_fee WHERE class_id = '$class_id'";
        $run = $conn->query($select_total_fee);
        $datass = mysqli_fetch_assoc($run);
        ?>

        <div id="page_class" class="show_hide" style="display:none;">
            <h1 class="text-4xl fw-bold">Student List</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">

                <div class="relative overflow-hidden bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Students</p>
                            <h3 class="text-3xl font-black text-slate-800 mt-1">
                                <?php echo number_format($datas['total_students']); ?>
                            </h3>
                        </div>
                        <div class="h-12 w-12 bg-blue-50 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-users text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 to-cyan-400"></div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Academic Status</p>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="flex h-3 w-3 rounded-full bg-green-500 animate-pulse"></span>
                                <h3 class="text-xl font-bold text-slate-800">Active Now</h3>
                            </div>
                        </div>
                        <div class="h-12 w-12 bg-green-50 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-graduation-cap text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Fee in Class</p>
                            <div class="flex items-center gap-2 mt-2">
                                <h1 class="text-3xl font-black text-slate-800 mt-1">$<?php echo number_format($datas['total_students'] * $datass['total_fee']); ?></h1>
                            </div>
                        </div>
                        <div class="h-12 w-12 bg-green-50 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-coins text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>

            </div>
            <div class="grid mt-5 mb-10">
                <div class="">
                    <button onclick="openModal()" class="btn btn-outline-primary float-end">+ Add Student</button>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl shadow overflow-hidden">
                <div class="w-full h-[500px] overflow-x-auto border border-gray-100 rounded-lg">
                    <table id="studentTable" class="table table-hover w-max min-w-full">
                        <thead class="table-primary text-center">
                            <tr>
                                <th class="px-4 py-2 whitespace-nowrap">ID</th>
                                <th class="px-4 py-2 whitespace-nowrap">Student ID</th>
                                <th class="px-4 py-2 whitespace-nowrap">Name</th>
                                <th class="px-4 py-2 whitespace-nowrap">Gender</th>
                                <th class="px-4 py-2 whitespace-nowrap">Email</th>
                                <th class="px-4 py-2 whitespace-nowrap">Faculty</th>
                                <th class="px-4 py-2 whitespace-nowrap">Department</th>
                                <th class="px-4 py-2 whitespace-nowrap">Year</th>
                                <th class="px-4 py-2 whitespace-nowrap">Class</th>
                                <th class="px-4 py-2 whitespace-nowrap">Created</th>
                                <th class="px-4 py-2 whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include '../Components/connection.php';

                            $class_id = isset($_GET['class_id']) ? $_GET['class_id'] : 0;
                            $student_id = isset($_GET['student_id']) ? $_GET['student_id'] : 0;

                            $selects = "
                                SELECT 
                                    s.id,
                                    s.stu_id,
                                    s.name,
                                    s.gender,
                                    s.email,
                                    s.created_at,
                                    c.class_name,
                                    c.faculty,
                                    c.department,
                                    c.year
                                FROM tbl_student s
                                LEFT JOIN tbl_class c ON s.class_id = c.id
                                WHERE s.class_id = '$class_id'
                            ";

                            $exe = $conn->query($selects);

                            while ($row = mysqli_fetch_assoc($exe)) {
                                echo "
                                    <tr class='text-center border-b hover:bg-gray-50'>
                                        <td class='px-4 py-2 whitespace-nowrap'>{$row['id']}</td>
                                        <td class='px-4 py-2 whitespace-nowrap'>{$row['stu_id']}</td>
                                        <td class='px-4 py-2 whitespace-nowrap font-medium'>{$row['name']}</td>
                                        <td class='px-4 py-2 whitespace-nowrap'>{$row['gender']}</td>
                                        <td class='px-4 py-2 whitespace-nowrap'>{$row['email']}</td>
                                        <td class='px-4 py-2 whitespace-nowrap'>{$row['faculty']}</td>
                                        <td class='px-4 py-2 whitespace-nowrap'>{$row['department']}</td>
                                        <td class='px-4 py-2 whitespace-nowrap'>
                                            <span class='px-2 py-1 bg-blue-100 text-blue-600 rounded text-xs'>
                                                Year {$row['year']}
                                            </span>
                                        </td>
                                        <td class='px-4 py-2 whitespace-nowrap'>{$row['class_name']}</td>
                                        <td class='px-4 py-2 whitespace-nowrap'>{$row['created_at']}</td>
                                        <td class='px-4 py-2 whitespace-nowrap'>
                                            <div class='flex gap-2 justify-center'>
                                                <button onclick=\"viewStudent(" . $row['id'] . ")\" class='px-2 py-1 text-blue-600 border border-blue-600 rounded hover:bg-blue-600 hover:text-white'>
                                                    View
                                                </button>
                                                <button class='px-2 py-1 text-red-600 border border-red-600 rounded hover:bg-red-600 hover:text-white'>
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <div id="payment_page" class="show_hide relative mt-1 right-12" style="display:none;">
        <div class="w-full space-y-6 mx-3">
            <?php if ($paymentStudent): ?>

                <!-- HEADER -->
                <div class=" text-black p-6 rounded-2xl shadow-lg flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold">Student Payment</h1>
                        <p class="text-sm text-gray-500 mt-2 opacity-90">Secure & fast payment system</p>
                    </div>
                    <div class="text-right">
                        <i class="fa-solid fa-qrcode text-5xl mr-3"></i>
                    </div>
                </div>

                <!-- MAIN GRID -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- LEFT: STUDENT PROFILE -->
                    <div class="bg-white rounded-2xl shadow p-6 col-span-2">

                        <h2 class="text-xl font-bold mb-4 border-b pb-2"><i class="fa-solid fa-user-graduate mr-1 text-gray-600"></i> Student Info</h2>

                        <div class="flex items-center gap-5 mb-6">
                            <div class="w-16 h-16 bg-blue-500 text-white flex items-center justify-center rounded-full text-xl font-bold">
                                <?php echo strtoupper(substr($paymentStudent['name'], 0, 1)); ?>
                            </div>
                            <div>
                                <p class="text-xl font-semibold"><?php echo htmlspecialchars($paymentStudent['name']); ?></p>
                                <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($paymentStudent['email']); ?></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-gray-700 text-sm">
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-500">Student ID</p>
                                <p class="font-semibold text-blue-600"><?php echo htmlspecialchars($paymentStudent['stu_id']); ?></p>
                            </div>

                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-500">Class</p>
                                <p class="font-semibold text-amber-600"><?php echo htmlspecialchars($paymentStudent['class_name'] ?? 'N/A'); ?></p>
                            </div>

                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-500">Department</p>
                                <p class="font-semibold text-red-600"><?php echo htmlspecialchars($paymentStudent['department'] ?? 'N/A'); ?></p>
                            </div>

                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-500">Payment Type</p>
                                <p class="font-semibold text-green-600">School Fee</p>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: PAYMENT BOX -->
                    <div class="bg-white rounded-2xl shadow p-6 flex flex-col justify-between">

                        <div>
                            <h2 class="text-xl font-bold mb-4 border-b pb-2">💰 Payment</h2>

                            <div class="bg-blue-50 p-4 rounded-xl text-center mb-4">
                                <p class="text-gray-500 text-sm">Total Fee</p>
                                <p class="text-4xl font-bold text-blue-600 mt-1">
                                    $<?php echo number_format((float) ($paymentStudent['total_fee'] ?? 0), 2); ?>
                                </p>
                            </div>

                            <div class="text-sm text-gray-500 mb-4">
                                ✔ Includes all semester fees<br>
                                ✔ One-time payment
                            </div>
                        </div>

                        <!-- BUTTONS -->
                        <div class="space-y-3">
                            <button onclick="showQR(<?php echo (int) $paymentStudent['id']; ?>, <?php echo (float) ($paymentStudent['total_fee'] ?? 0); ?>)"
                                class="w-full bg-green-600 hover:bg-green-500 text-white py-3 rounded-xl font-semibold shadow">
                                Confirm Payment
                            </button>

                            <button onclick="showReceipt()"
                                class="w-full bg-gray-800 hover:bg-gray-700 text-white py-3 rounded-xl">
                                🧾 Print Receipt
                            </button>
                        </div>

                    </div>

                </div>

                <!-- QR MODAL -->
                <div id="qrModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
                    <div class="bg-white p-6 rounded-xl text-center w-[350px]">

                        <h2 class="text-xl font-bold mb-4">Scan QR to Pay</h2>

                        <!-- QR -->
                        <img id="bakongQR" src="" alt="Payment QR Code" class="mx-auto w-[220px] h-[220px] object-contain" />

                        <p class="mt-3 text-gray-500 text-sm">Scan using Bakong / ABA</p>

                        <div class="mt-4 rounded-lg bg-blue-50 text-blue-700 px-4 py-3 text-sm">
                            Waiting for payment confirmation...
                        </div>

                        <button onclick="closeQRAuto()"
                            class="mt-2 text-red-500">
                            Cancel
                        </button>
                    </div>
                </div>

                <!-- RECEIPT -->
                <div id="receipt" class="hidden bg-white mt-6 p-6 rounded-xl shadow text-center">
                    <h2 class="text-2xl font-bold mb-4">🧾 Receipt</h2>
                    <p>Payer Name: <span id="payerName">-</span></p>
                    <p>Payer Account: <span id="payerAccount">-</span></p>
                    <p>Name: <?php echo htmlspecialchars($paymentStudent['name']); ?></p>
                    <p>ID: <?php echo htmlspecialchars($paymentStudent['stu_id']); ?></p>
                    <p>Amount: $<?php echo number_format((float) ($paymentStudent['total_fee'] ?? 0), 2); ?></p>
                    <p>Receipt Code: <span id="receiptCode">-</span></p>
                    <p>Date: <span id="payDate"></span></p>

                    <div class="text-green-600 font-bold mt-4">
                        ✔ Payment Successful
                    </div>
                </div>
            <?php elseif ($student_id > 0): ?>
                <div class="bg-white rounded-2xl shadow p-6">
                    <h2 class="text-xl font-bold mb-2">Student not found</h2>
                    <p class="text-gray-500">The selected student could not be loaded for payment.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Modal add student -->
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

<!--Add Class Modal -->
<div id="classModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">

    <div class="bg-white w-[500px] rounded-xl p-6 shadow-lg">

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold">Add Class</h2>
            <i class="fa-solid fa-xmark cursor-pointer" onclick="closeModalClass()"></i>
        </div>

        <form action="../Components/insert_class.php" method="POST">
            <div class="b-2">
                <label for="" class="form-label fw-bold">Class Name</label>
                <input type="text" name="class_name" placeholder="Class Name..."
                    class="w-full mb-3 px-3 py-2 border rounded-lg">
            </div>

            <div class="mb-2">
                <label for="" class="form-label fw-bold">Faculty</label>
                <select name="faculty" class="w-full mb-3 px-3 py-2 border rounded-lg">
                    <option value="">Select Faculty</option>
                    <option value="Engineering">Engineering</option>
                    <option value="Science">Science</option>
                    <option value="Education">Education</option>
                </select>
            </div>
            <div class="mb-2">
                <label for="" class="form-label fw-bold">Department</label>
                <select name="department" class="w-full mb-3 px-3 py-2 border rounded-lg">
                    <option value="">Select Department</option>
                    <option value="ITE">ITE</option>
                    <option value="IT">IT</option>
                    <option value="English">English</option>
                    <option value="IBM">IBM</option>
                    <option value="Math">Math</option>
                </select>
            </div>
            <div class="mb-2">
                <label for="" class="form-label fw-bold">Year</label>
                <select name="year" class="w-full mb-3 px-3 py-2 border rounded-lg">
                    <option value="">Select Year</option>
                    <option value="1">Year 1</option>
                    <option value="2">Year 2</option>
                    <option value="3">Year 3</option>
                    <option value="4">Year 4</option>
                </select>
            </div>

            <div class="mb-2">
                <label for="" class="form-label fw-bold">Price Fee</label>
                <input type="number" name="price" class="form-control" placeholder="Price fee..." required>
            </div>

            <div class="flex justify-end gap-2 mt-3">
                <button type="button" onclick="closeModalClass()"
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

    // View class
    function viewClass(classId) {
        // Redirect with class_id
        window.location.href = "?class_id=" + classId;
    }
    window.onload = function() {
        const params = new URLSearchParams(window.location.search);

        const classId = params.get('class_id');
        const studentId = params.get('student_id');

        if (classId) {
            showSection('page_class');
        }

        if (studentId) {
            showSection('payment_page');
            <?php if ($paymentStudent): ?>
            <?php endif; ?>
        }
    };
    //View student
    function viewStudent(id) {
        window.location.href = "?student_id=" + id;
    };


    // student modal
    function openModal() {
        document.getElementById('studentModal').classList.remove('hidden');
        document.getElementById('studentModal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('studentModal').classList.add('hidden');
    }

    // class modal
    function openModalClass() {
        document.getElementById('classModal').classList.remove('hidden');
        document.getElementById('classModal').classList.add('flex');
    }

    function closeModalClass() {
        document.getElementById('classModal').classList.add('hidden');
    }
    // for change page with button in sidear
    // Function សម្រាប់ប្តូរ Page និងដូរពណ៌ប៊ូតុង
    function showSection(id, btnElement) {
        // លាក់រាល់ Section ដែលមាន class 'show_hide'
        var sections = document.querySelectorAll('.show_hide');
        sections.forEach(section => {
            section.style.display = 'none';
        });

        // បង្ហាញ Section ដែលយើងចង់បាន
        const targetSection = document.getElementById(id);
        if (targetSection) {
            targetSection.style.display = 'block';
        }

        // ប្តូរពណ៌ប៊ូតុងនៅ Sidebar ឱ្យដឹងថា Page ណាមួយកំពុង Active
        var buttons = document.querySelectorAll('nav button');
        buttons.forEach(btn => {
            btn.classList.remove('bg-gray-300');
            btn.classList.add('hover:bg-gray-300');
        });

        if (btnElement) {
            btnElement.classList.add('bg-gray-300');
            btnElement.classList.remove('hover:bg-gray-300');
        }
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
    // Make button pay runs
    // Print reciept
    function printReceipt() {
        var content = document.getElementById('receipt').innerHTML;
        var myWindow = window.open('', '', 'width=800,height=600');
        myWindow.document.write(content);
        myWindow.document.close();
        myWindow.print();
    }

    function closeQR() {
        document.getElementById('qrModal').classList.add('hidden');
        setConfirmButtonState(false, 'I Have Paid');

        // Stop QR refresh when user cancels
        if (qrRefreshInterval) {
            clearInterval(qrRefreshInterval);
            qrRefreshInterval = null;
        }
        if (paymentCheckInterval) { clearInterval(paymentCheckInterval); paymentCheckInterval = null; }
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
                    currentReceipt.payerName = data.payer_name || '-';
                    currentReceipt.payerAccount = data.payer_account || '-';

                    if (paymentCheckInterval) {
                        clearInterval(paymentCheckInterval);
                        paymentCheckInterval = null;
                    }

                    const paidAt = new Date().toLocaleString();
                    currentReceipt.amount = Number(amount || currentReceipt.amount || 0);
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
qrModal.style.display = 'none';

// SHOW RECEIPT
const receipt = document.getElementById('receipt');
receipt.classList.remove('hidden');
receipt.style.display = 'block';

document.getElementById('payDate').innerText = paidAt;

                    alert(
                        "Receipt: " + (currentReceipt.receiptCode || '-') + "\n" +
                        "Amount: $" + Number(currentReceipt.amount || 0).toFixed(2) + "\n" +
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

<?php include '../Categories/footer.php'; ?>
