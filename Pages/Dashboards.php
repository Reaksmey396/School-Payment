<?php
session_start();
if (!isset($_SESSION['is_admin']) || !in_array((int) $_SESSION['is_admin'], [1, 3], true)) {
    header('location:../Components/Login.php');
    exit();
}

include '../Components/connection.php';

$currentClassId = isset($_GET['class_id']) ? (int) $_GET['class_id'] : 0;
$student_id = isset($_GET['student_id']) ? (int) $_GET['student_id'] : 0;
$paymentStudent = null;
$studentReceipts = [];
$chartMonths = [];
$monthlyStudents = [];
$monthlyPayments = [];
$deptGenderStats = [];
$paymentCounts = [];
$paidStudentCounts = [];

$currentYear = (int) date('Y');
for ($m = 1; $m <= 12; $m++) {
    $chartMonths[] = date('M', mktime(0, 0, 0, $m, 1, $currentYear));

    $studentMonthStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM tbl_student WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?");
    if ($studentMonthStmt) {
        $studentMonthStmt->bind_param("ii", $currentYear, $m);
        $studentMonthStmt->execute();
        $studentMonthRes = $studentMonthStmt->get_result();
        $studentMonthRow = $studentMonthRes ? $studentMonthRes->fetch_assoc() : null;
        $monthlyStudents[] = (int) ($studentMonthRow['cnt'] ?? 0);
        $studentMonthStmt->close();
    } else {
        $monthlyStudents[] = 0;
    }

    $paymentMonthStmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) AS total_amount, COUNT(*) AS payment_cnt, COUNT(DISTINCT student_id) AS paid_students FROM tbl_payment WHERE YEAR(payment_date) = ? AND MONTH(payment_date) = ?");
    if ($paymentMonthStmt) {
        $paymentMonthStmt->bind_param("ii", $currentYear, $m);
        $paymentMonthStmt->execute();
        $paymentMonthRes = $paymentMonthStmt->get_result();
        $paymentMonthRow = $paymentMonthRes ? $paymentMonthRes->fetch_assoc() : null;
        $monthlyPayments[] = (float) ($paymentMonthRow['total_amount'] ?? 0);
        $paymentCounts[] = (int) ($paymentMonthRow['payment_cnt'] ?? 0);
        $paidStudentCounts[] = (int) ($paymentMonthRow['paid_students'] ?? 0);
        $paymentMonthStmt->close();
    } else {
        $monthlyPayments[] = 0;
        $paymentCounts[] = 0;
        $paidStudentCounts[] = 0;
    }
}

$deptGenderStmt = $conn->prepare("
    SELECT
        COALESCE(c.department, 'Unknown') AS department,
        SUM(CASE WHEN LOWER(COALESCE(s.gender, '')) = 'female' THEN 1 ELSE 0 END) AS girls,
        SUM(CASE WHEN LOWER(COALESCE(s.gender, '')) = 'male' THEN 1 ELSE 0 END) AS boys
    FROM tbl_student s
    LEFT JOIN tbl_class c ON s.class_id = c.id
    GROUP BY COALESCE(c.department, 'Unknown')
    ORDER BY department ASC
    LIMIT 8
");
if ($deptGenderStmt) {
    $deptGenderStmt->execute();
    $deptGenderRes = $deptGenderStmt->get_result();
    while ($deptGenderRes && ($deptGenderRow = $deptGenderRes->fetch_assoc())) {
        $deptGenderStats[] = $deptGenderRow;
    }
    $deptGenderStmt->close();
}

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
        LEFT JOIN (
            SELECT faculty_id, department, MAX(id) AS fee_id
            FROM tbl_fee
            GROUP BY faculty_id, department
        ) latest_fee ON latest_fee.faculty_id = c.faculty_id AND latest_fee.department = c.department
        LEFT JOIN tbl_fee f ON f.id = latest_fee.fee_id
        WHERE s.id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $paymentStudent = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $receiptStmt = $conn->prepare("
        SELECT p.payment_date, p.amount, p.method, p.bill_no, r.receipt_code
        FROM tbl_payment p
        LEFT JOIN tbl_receipt r ON p.id = r.payment_id
        WHERE p.student_id = ?
        ORDER BY p.id DESC
        LIMIT 20
    ");
    if ($receiptStmt) {
        $receiptStmt->bind_param("i", $student_id);
        $receiptStmt->execute();
        $receiptResult = $receiptStmt->get_result();
        while ($receiptResult && ($receiptRow = $receiptResult->fetch_assoc())) {
            $studentReceipts[] = $receiptRow;
        }
        $receiptStmt->close();
    }
}

if (isset($_POST['pay'])) {
    header('Content-Type: application/json');

    $student_id = isset($_POST['student_id']) ? (int) $_POST['student_id'] : 0;
    $amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
    $method = isset($_POST['method']) ? trim($_POST['method']) : 'Bakong QR';
    $billNo = isset($_POST['bill_no']) ? trim($_POST['bill_no']) : '';

    if ($student_id <= 0 || $amount <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid payment data.'
        ]);
        exit();
    }

    if ($billNo !== '') {
        $existingStmt = $conn->prepare("SELECT id FROM tbl_payment WHERE bill_no = ? LIMIT 1");
        if ($existingStmt) {
            $existingStmt->bind_param("s", $billNo);
            $existingStmt->execute();
            $existingRes = $existingStmt->get_result();
            if ($existingRes && ($existingRow = $existingRes->fetch_assoc())) {
                $existingPaymentId = (int) ($existingRow['id'] ?? 0);
                $existingReceipt = '';
                if ($existingPaymentId > 0) {
                    $receiptStmt = $conn->prepare("SELECT receipt_code FROM tbl_receipt WHERE payment_id = ? LIMIT 1");
                    if ($receiptStmt) {
                        $receiptStmt->bind_param("i", $existingPaymentId);
                        $receiptStmt->execute();
                        $receiptRes = $receiptStmt->get_result();
                        if ($receiptRes && ($receiptRow = $receiptRes->fetch_assoc())) {
                            $existingReceipt = (string) ($receiptRow['receipt_code'] ?? '');
                        }
                        $receiptStmt->close();
                    }
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Payment already saved.',
                    'payment_id' => $existingPaymentId,
                    'receipt_code' => $existingReceipt,
                    'bill_no' => $billNo,
                    'paid_at' => date('Y-m-d H:i:s')
                ]);
                $existingStmt->close();
                exit();
            }
            $existingStmt->close();
        }
    }

    $fee_id = 0;
    $feeStmt = $conn->prepare("
        SELECT f.id AS fee_id
        FROM tbl_student s
        LEFT JOIN tbl_class c ON s.class_id = c.id
        LEFT JOIN (
            SELECT faculty_id, department, MAX(id) AS fee_id
            FROM tbl_fee
            GROUP BY faculty_id, department
        ) latest_fee ON latest_fee.faculty_id = c.faculty_id AND latest_fee.department = c.department
        LEFT JOIN tbl_fee f ON f.id = latest_fee.fee_id
        WHERE s.id = ?
        LIMIT 1
    ");
    if ($feeStmt) {
        $feeStmt->bind_param("i", $student_id);
        $feeStmt->execute();
        $feeRes = $feeStmt->get_result();
        if ($feeRes && ($feeRow = $feeRes->fetch_assoc())) {
            $fee_id = (int) ($feeRow['fee_id'] ?? 0);
        }
        $feeStmt->close();
    }

    $stmt = $conn->prepare("
        INSERT INTO tbl_payment (student_id, fee_id, amount, payment_date, method, bill_no)
        VALUES (?, ?, ?, CURDATE(), ?, ?)
    ");

    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to prepare payment query.',
            'detail' => mysqli_error($conn)
        ]);
        exit();
    }

    $stmt->bind_param("iidss", $student_id, $fee_id, $amount, $method, $billNo);

    if ($stmt->execute()) {
        $payment_id = (int) $stmt->insert_id;
        $receipt_code = '';
        if ($payment_id > 0) {
            $receipt_code = 'RCPT-' . date('YmdHis') . '-' . $payment_id;
            $receiptStmt = $conn->prepare("INSERT INTO tbl_receipt (payment_id, receipt_code) VALUES (?, ?)");
            if ($receiptStmt) {
                $receiptStmt->bind_param("is", $payment_id, $receipt_code);
                $receiptStmt->execute();
                $receiptStmt->close();
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Payment saved successfully.',
            'payment_id' => $payment_id,
            'receipt_code' => $receipt_code,
            'bill_no' => $billNo,
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

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

                <button onclick="showSection('history_button', this)" class="flex items-center gap-3 p-2 text-[17px] rounded-lg hover:bg-gray-300 fw-medium">
                    <i class="fa-solid fa-chart-simple"></i> History
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
        <?php if (isset($_GET['deleted'])): ?>
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
                <?php echo htmlspecialchars(ucfirst($_GET['deleted']), ENT_QUOTES, 'UTF-8'); ?> deleted successfully.
            </div>
        <?php elseif (isset($_GET['delete_error'])): ?>
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                Delete failed: <?php echo htmlspecialchars(substr((string) $_GET['delete_error'], 0, 220), ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div id="home_button" class="show_hide">
            <h1 class="text-4xl fw-bold mb-2">Welcome To Admin !!</h1>
            <p class="text-gray-600 mb-5">Monitor student payments and manage your classes efficiently.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Students</p>
                            <h3 class="text-3xl font-black text-slate-800 mt-1">
                                <?php
                                $total_students = $conn->query("SELECT COUNT(*) as cnt FROM tbl_student")->fetch_assoc();
                                echo number_format($total_students['cnt']);
                                ?>
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
                            <p class="text-sm font-medium text-slate-500">Total Classes</p>
                            <h3 class="text-3xl font-black text-slate-800 mt-1">
                                <?php
                                $total_classes = $conn->query("SELECT COUNT(*) as cnt FROM tbl_class")->fetch_assoc();
                                echo number_format($total_classes['cnt']);
                                ?>
                            </h3>
                        </div>
                        <div class="h-12 w-12 bg-green-50 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-building-columns text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-green-500 to-emerald-400"></div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Revenue</p>
                            <h3 class="text-3xl font-black text-slate-800 mt-1">
                                $<?php
                                    $total_revenue = $conn->query("SELECT SUM(amount) as total FROM tbl_payment")->fetch_assoc();
                                    echo number_format($total_revenue['total'], 2);
                                    ?>
                            </h3>
                        </div>
                        <div class="h-12 w-12 bg-red-50 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-dollar-sign text-red-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-red-500 to-rose-400"></div>
                </div>
            </div>

            <!-- Charts Row 1: Overview Line + Students Bar -->
            <div class="grid grid-cols-2 gap-4 mb-5">

                <!-- Line Chart: Overview (New Students vs Payments) -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-gray-800">Overview</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Live comparison from the database</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-blue-600 inline-block"></span>
                                <span class="text-xs text-gray-500 font-medium">Students</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-teal-400 inline-block"></span>
                                <span class="text-xs text-gray-500 font-medium">Payments</span>
                            </div>
                            <button class="text-gray-400 hover:text-gray-600">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                        </div>
                    </div>
                    <div class="relative h-52">
                        <canvas id="overviewChart"></canvas>
                    </div>
                </div>

                <!-- Bar Chart: Number of Students (Girls vs Boys by Department) -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-gray-800">Number of Students</h3>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-indigo-500 inline-block"></span>
                                <span class="text-xs text-gray-500 font-medium">Girls</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-teal-400 inline-block"></span>
                                <span class="text-xs text-gray-500 font-medium">Boys</span>
                            </div>
                            <button class="text-gray-400 hover:text-gray-600">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                        </div>
                    </div>
                    <div class="relative h-52">
                        <canvas id="studentsChart"></canvas>
                    </div>
                </div>

            </div>

            <!-- Charts Row 2: Payment Collection + Recent Payments -->
            <div class="grid grid-cols-3 gap-4">

                <!-- Payment Collection Line Chart -->
                <div class="col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-gray-800">Payment Collection</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Monthly payments and paid student activity · <?php echo date('Y'); ?></p>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-blue-600 inline-block"></span>
                                <span class="text-xs text-gray-500 font-medium">Collected</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span>
                                <span class="text-xs text-gray-500 font-medium">Paid students</span>
                            </div>
                        </div>
                    </div>
                    <div class="relative h-44">
                        <canvas id="paymentChart"></canvas>
                    </div>
                </div>

                <!-- Recent Payments -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-gray-800">Recent Payments</h3>
                        <span onclick="showSection('student_button', document.querySelector('nav button:nth-child(3)'))"
                            class="text-xs text-blue-600 font-semibold cursor-pointer hover:underline">View all</span>
                    </div>
                    <div class="flex flex-col gap-3 flex-1">
                        <?php
                        $recent_payments = $conn->query("
                            SELECT p.amount, p.payment_date, p.method,
                                s.name, s.stu_id,
                                c.department
                            FROM tbl_payment p
                            LEFT JOIN tbl_student s ON p.student_id = s.id
                            LEFT JOIN tbl_class c ON s.class_id = c.id
                            ORDER BY p.payment_date DESC, p.id DESC
                            LIMIT 5
                        ");
                        $avatar_colors = ['bg-blue-100 text-blue-700', 'bg-teal-100 text-teal-700', 'bg-red-100 text-red-700', 'bg-purple-100 text-purple-700', 'bg-orange-100 text-orange-700'];
                        $i = 0;
                        while ($rp = $recent_payments->fetch_assoc()):
                            $initials = strtoupper(substr($rp['name'] ?? 'U', 0, 2));
                            $color = $avatar_colors[$i % 5];
                            $i++;
                        ?>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full <?php echo $color; ?> flex items-center justify-center font-bold text-xs flex-shrink-0">
                                        <?php echo htmlspecialchars($initials); ?>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-700 leading-tight"><?php echo htmlspecialchars($rp['name'] ?? 'Unknown'); ?></p>
                                        <p class="text-xs text-gray-400"><?php echo htmlspecialchars($rp['department'] ?? '-'); ?> · <?php echo htmlspecialchars($rp['stu_id'] ?? ''); ?></p>
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-green-600">$<?php echo number_format((float)$rp['amount'], 2); ?></span>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Collection rate progress -->
                    <?php
                    include '../Components/connection.php';

                    // Add these 4 lines here — before ANY HTML
                    $total_stu  = $conn->query("SELECT COUNT(*) as cnt FROM tbl_student")->fetch_assoc();
                    $paid_stu   = $conn->query("SELECT COUNT(DISTINCT student_id) as cnt FROM tbl_payment")->fetch_assoc();
                    $unpaid_stu = $conn->query("SELECT COUNT(*) as cnt FROM tbl_student WHERE id NOT IN (SELECT DISTINCT student_id FROM tbl_payment)")->fetch_assoc();
                    $total_cls  = $conn->query("SELECT COUNT(*) as cnt FROM tbl_class")->fetch_assoc();

                    $total_s = isset($total_stu['cnt']) ? (int)$total_stu['cnt'] : 0;
                    $paid_s  = isset($paid_stu['cnt'])  ? (int)$paid_stu['cnt']  : 0;
                    $rate    = $total_s > 0 ? round($paid_s / $total_s * 100, 1) : 0;
                    ?>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Collection rate</span>
                            <span class="font-bold text-gray-700"><?php echo $rate; ?>%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-blue-600 h-1.5 rounded-full" style="width:<?php echo $rate; ?>%"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div id="history_button" class="show_hide" style="display: none;">
            <h1 class="text-3xl font-bold">Payment History</h1>
            <p class="text-gray-600 mt-1 mb-4">View and manage all payment records. Moreover, you can filter and sort the records for easier access.</p>

            <!-- Search -->
            <div class="mb-4 flex justify-between">
                <input id="searchReceipt" type="text"
                    placeholder="Search by student name or ID..."
                    class="w-1/2 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400 outline-none">

                <span class="text-sm text-gray-500 mt-2">
                    Total Records:
                    <?php
                    $count = $conn->query("SELECT COUNT(*) as cnt FROM tbl_payment")->fetch_assoc();
                    echo $count['cnt'];
                    ?>
                </span>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow overflow-hidden">

                <div class="overflow-x-auto">
                    <table id="receiptTable" class="min-w-full text-sm border-separate border-spacing-y-2">

                        <thead class="text-gray-500 text-center">
                            <tr>
                                <th>NO.</th>
                                <th class="text-left relative left-5">Student</th>
                                <th>ID</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                                <th>Bill</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody id="receiptBody" class="text-center">

                            <?php
                            $history = $conn->query("
                                SELECT 
                                    p.*, 
                                    s.name, 
                                    s.stu_id,
                                    s.email
                                FROM tbl_payment p
                                LEFT JOIN tbl_student s ON p.student_id = s.id
                                ORDER BY p.id DESC
                            ");

                            $i = 1;
                            while ($row = $history->fetch_assoc()):
                            ?>

                                <tr class="bg-white shadow-sm rounded-xl hover:shadow-md transition">

                                    <td class="p-3"><?php echo $i++; ?></td>

                                    <td class="p-3 text-left font-semibold text-gray-700">
                                        <?php echo htmlspecialchars($row['name']); ?>
                                    </td>

                                    <td class="p-3 text-gray-600">
                                        <?php echo htmlspecialchars($row['stu_id']); ?>
                                    </td>

                                    <td class="p-3 text-green-600 font-bold">
                                        $<?php echo number_format($row['amount'], 2); ?>
                                    </td>

                                    <td class="p-3">
                                        <span class="bg-blue-100 text-blue-600 px-2 py-1 rounded text-xs">
                                            <?php echo $row['method']; ?>
                                        </span>
                                    </td>

                                    <td class="p-3 text-gray-500 text-xs">
                                        <?php echo date('d M Y', strtotime($row['payment_date'])); ?>
                                    </td>

                                    <td class="p-3 text-xs text-gray-400">
                                        <?php echo $row['bill_no'] ?? '-'; ?>
                                    </td>

                                    <td class="p-3">
                                        <button onclick='printReceipt(
                                            <?php echo json_encode($row["name"]); ?>,
                                            <?php echo json_encode($row["stu_id"]); ?>,
                                            <?php echo json_encode($row["email"] ?? "-"); ?>,
                                            <?php echo json_encode(number_format($row["amount"], 2)); ?>,
                                            <?php echo json_encode($row["method"]); ?>,
                                            <?php echo json_encode($row["bill_no"] ?? "-"); ?>,
                                            <?php echo json_encode($row["payment_date"]); ?>
                                        )' class="px-2 py-1 text-blue-600 border border-blue-600 rounded hover:bg-blue-600 hover:text-white">
                                            Print
                                        </button>
                                    </td>

                                </tr>

                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
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
                    <button onclick="openModal('student')" class="btn btn-outline-primary float-end">+ Add Student</button>
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
                                                <form method='POST' action='../Components/delete_record.php' data-delete-type='student' data-delete-label='" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "' onsubmit=\"return window.openDeleteConfirm ? openDeleteConfirm(this) : confirm('Are you sure?');\" class='inline'>
                                                    <input type='hidden' name='delete_action' value='student'>
                                                    <input type='hidden' name='delete_id' value='" . (int) $row['id'] . "'>
                                                    <button type='submit' class='px-2 py-1 text-red-600 border border-red-600 rounded hover:bg-red-600 hover:text-white'>
                                                        Delete
                                                    </button>
                                                </form>
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
            <div class="justify-between items-center mb-5">
                <h1 class="text-3xl font-bold">Class Management</h1>
                <p class="text-sm text-slate-500">Manage and organize classes to find students easily</p>
                <button onclick="openModalClass()" class="bg-blue-600 focus:outline-none relative bottom-11 float-end hover:bg-blue-500 text-white px-4 py-2 rounded-lg">
                    <i class="fa-solid fa-plus mr-2"></i> Add Class
                </button>
            </div>

            <?php
            $classStats = $conn->query("
            SELECT
                (SELECT COUNT(*) FROM tbl_class) AS total_class,
                (SELECT COUNT(DISTINCT department) FROM tbl_class) AS total_department,
                (SELECT COUNT(*) FROM tbl_faculty) AS total_faculty,
                (SELECT COUNT(*) FROM tbl_student) AS total_student
        ")->fetch_assoc();
            ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-4">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow px-4 py-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Class</p>
                            <h3 class="text-2xl font-black text-slate-900 mt-1 leading-none"><?php echo number_format((int)($classStats['total_class'] ?? 0)); ?></h3>
                        </div>
                        <div class="h-10 w-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow px-4 py-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Faculty</p>
                            <h3 class="text-2xl font-black text-slate-900 mt-1 leading-none"><?php echo number_format((int)($classStats['total_faculty'] ?? 0)); ?></h3>
                        </div>
                        <div class="h-10 w-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid fa-university"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow px-4 py-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Department</p>
                            <h3 class="text-2xl font-black text-slate-900 mt-1 leading-none"><?php echo number_format((int)($classStats['total_department'] ?? 0)); ?></h3>
                        </div>
                        <div class="h-10 w-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid fa-sitemap"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow px-4 py-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Student</p>
                            <h3 class="text-2xl font-black text-slate-900 mt-1 leading-none"><?php echo number_format((int)($classStats['total_student'] ?? 0)); ?></h3>
                        </div>
                        <div class="h-10 w-10 rounded-2xl bg-pink-50 text-pink-600 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                </div>
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
                                    COALESCE(tf.faculty_name, c.faculty) AS faculty_display,
                                    f.total_fee 
                                FROM tbl_class c
                                LEFT JOIN tbl_faculty tf ON c.faculty_id = tf.id
                                LEFT JOIN (
                                    SELECT faculty_id, department, MAX(id) AS fee_id
                                    FROM tbl_fee
                                    GROUP BY faculty_id, department
                                ) latest_fee ON latest_fee.faculty_id = c.faculty_id AND latest_fee.department = c.department
                                LEFT JOIN tbl_fee f ON f.id = latest_fee.fee_id
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
                                    <td class='p-3'>{$row['faculty_display']}</td>
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
                                            <form method='POST' action='../Components/delete_record.php' data-delete-type='class' data-delete-label='" . htmlspecialchars($row['class_name'], ENT_QUOTES, 'UTF-8') . "' onsubmit=\"return window.openDeleteConfirm ? openDeleteConfirm(this) : confirm('Are you sure?');\" class='inline'>
                                                <input type='hidden' name='delete_action' value='class'>
                                                <input type='hidden' name='delete_id' value='" . (int) $row['id'] . "'>
                                                <button type='submit' class='text-red-600 hover:text-red-800' title='Delete'>
                                                    <i class='fa-solid fa-trash'></i>
                                                </button>
                                            </form>
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
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-slate-900">Settings</h1>
                <p class="mt-2 text-sm text-slate-500 max-w-2xl">Manage dashboard preferences, contact details, and notification behavior from one place.</p>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-1 gap-5">

                <div class="xl:col-span-2 space-y-5">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm text-slate-500">Notifications</p>
                                <h2 class="text-xl font-bold text-slate-900">Notification Preferences</h2>
                            </div>
                            <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-bell"></i>
                            </div>
                        </div>
                        <p class="text-sm text-slate-500 mb-5">Choose how the dashboard alerts you about payments and system activity.</p>

                        <div class="space-y-3">
                            <label class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 px-4 py-4">
                                <div>
                                    <p class="font-semibold text-slate-900">Email notifications</p>
                                    <p class="text-xs text-slate-500 mt-1">Receive reports and account updates by email.</p>
                                </div>
                                <input type="checkbox" checked class="h-5 w-5 accent-blue-600">
                            </label>
                            <label class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 px-4 py-4">
                                <div>
                                    <p class="font-semibold text-slate-900">Push notifications</p>
                                    <p class="text-xs text-slate-500 mt-1">Get real-time alerts on your device.</p>
                                </div>
                                <input type="checkbox" class="h-5 w-5 accent-blue-600">
                            </label>
                            <label class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 px-4 py-4">
                                <div>
                                    <p class="font-semibold text-slate-900">SMS notifications</p>
                                    <p class="text-xs text-slate-500 mt-1">Receive critical security alerts via SMS.</p>
                                </div>
                                <input type="checkbox" class="h-5 w-5 accent-blue-600">
                            </label>
                            <label class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 px-4 py-4">
                                <div>
                                    <p class="font-semibold text-slate-900">In-app notifications</p>
                                    <p class="text-xs text-slate-500 mt-1">Show alerts inside the application.</p>
                                </div>
                                <input type="checkbox" checked class="h-5 w-5 accent-blue-600">
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-sm text-slate-500">Authentication</p>
                                    <h2 class="text-xl font-bold text-slate-900">Details</h2>
                                </div>
                                <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                                    <input type="email" value="admin@rupppay.com" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Mobile Number</label>
                                    <input type="text" value="+855 12 345 678" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-sm text-slate-500">Appearance</p>
                                    <h2 class="text-xl font-bold text-slate-900">Workspace</h2>
                                </div>
                                <div class="h-12 w-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                                    <i class="fa-solid fa-palette"></i>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Default language</label>
                                    <select class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                                        <option>English</option>
                                        <option>Khmer</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Receipt layout</label>
                                    <select class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                                        <option>Centered modal</option>
                                        <option>Right panel</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm text-slate-500 flex items-center gap-2">
                                    <i class="fa-solid fa-wand-magic-sparkles text-slate-400"></i>
                                    Action
                                </p>
                                <h2 class="text-xl font-bold text-slate-900">Save Settings</h2>
                            </div>
                            <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shadow-sm">
                                <i class="fa-solid fa-sliders"></i>
                            </div>
                        </div>

                        <p class="text-sm text-slate-500 mb-4 max-w-2xl">
                            Save your dashboard preferences or reset them back to the default layout.
                        </p>

                        <div class="flex flex-wrap gap-3">
                            <button type="button" class="px-5 py-3 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 inline-flex items-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i>
                                Save Changes
                            </button>
                            <button type="button" class="px-5 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 inline-flex items-center gap-2">
                                <i class="fa-solid fa-rotate-left"></i>
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
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

        $select_total_fee = "
            SELECT COALESCE(f.total_fee, 0) AS total_fee
            FROM tbl_class c
            LEFT JOIN (
                SELECT faculty_id, department, MAX(id) AS fee_id
                FROM tbl_fee
                GROUP BY faculty_id, department
            ) latest_fee ON latest_fee.faculty_id = c.faculty_id AND latest_fee.department = c.department
            LEFT JOIN tbl_fee f ON f.id = latest_fee.fee_id
            WHERE c.id = '$class_id'
            LIMIT 1
        ";
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
                                <h1 class="text-3xl font-black text-slate-800 mt-1">$<?php echo number_format((int) ($datas['total_students'] ?? 0) * (float) ($datass['total_fee'] ?? 0)); ?></h1>
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
                    <button onclick="openModal('class')" class="btn btn-outline-primary float-end">+ Add Student</button>
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
                                                <form method='POST' action='../Components/delete_record.php' data-delete-type='student' data-delete-label='" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "' onsubmit=\"return window.openDeleteConfirm ? openDeleteConfirm(this) : confirm('Are you sure?');\" class='inline'>
                                                    <input type='hidden' name='delete_action' value='student'>
                                                    <input type='hidden' name='delete_id' value='" . (int) $row['id'] . "'>
                                                    <button type='submit' class='px-2 py-1 text-red-600 border border-red-600 rounded hover:bg-red-600 hover:text-white'>
                                                        Delete
                                                    </button>
                                                </form>
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


    <div id="payment_page" class="show_hide mt-1 w-full max-w-full" style="display:none;">
        <div class="w-full max-w-full relative right-7 space-y-6">
            <?php if ($paymentStudent): ?>

                <!-- HEADER -->
                <div class="text-black p-6 rounded-2xl shadow-lg flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center">
                    <div class="min-w-0">
                        <h1 class="text-3xl font-bold break-words">Student Payment</h1>
                        <p class="text-sm text-gray-500 mt-2 opacity-90">Secure & fast payment system</p>
                    </div>
                    <div class="text-right shrink-0">
                        <i class="fa-solid fa-qrcode text-5xl mr-3"></i>
                    </div>
                </div>

                <!-- MAIN GRID -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- LEFT: STUDENT PROFILE -->
                    <div class="bg-white rounded-2xl shadow p-6 lg:col-span-2 min-w-0">

                        <h2 class="text-xl font-bold mb-4 border-b pb-2"><i class="fa-solid fa-user-graduate mr-1 text-gray-600"></i> Student Info</h2>

                        <div class="flex items-center gap-5 mb-6 min-w-0">
                            <div class="w-16 h-16 bg-blue-500 text-white flex items-center justify-center rounded-full text-xl font-bold shrink-0">
                                <?php echo strtoupper(substr($paymentStudent['name'], 0, 1)); ?>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xl font-semibold break-words"><?php echo htmlspecialchars($paymentStudent['name']); ?></p>
                                <p class="text-gray-500 text-sm break-all"><?php echo htmlspecialchars($paymentStudent['email']); ?></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-gray-700 text-sm">
                            <div class="bg-gray-50 p-3 rounded-lg min-w-0">
                                <p class="text-gray-500">Student ID</p>
                                <p class="font-semibold text-blue-600 break-words"><?php echo htmlspecialchars($paymentStudent['stu_id']); ?></p>
                            </div>

                            <div class="bg-gray-50 p-3 rounded-lg min-w-0">
                                <p class="text-gray-500">Class</p>
                                <p class="font-semibold text-amber-600 break-words"><?php echo htmlspecialchars($paymentStudent['class_name'] ?? 'N/A'); ?></p>
                            </div>

                            <div class="bg-gray-50 p-3 rounded-lg min-w-0">
                                <p class="text-gray-500">Department</p>
                                <p class="font-semibold text-red-600 break-words"><?php echo htmlspecialchars($paymentStudent['department'] ?? 'N/A'); ?></p>
                            </div>

                            <div class="bg-gray-50 p-3 rounded-lg min-w-0">
                                <p class="text-gray-500">Payment Type</p>
                                <p class="font-semibold text-green-600">School Fee</p>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: PAYMENT BOX -->
                    <div class="bg-white rounded-2xl shadow p-6 flex flex-col justify-between min-w-0">

                        <div>
                            <h2 class="text-xl font-bold mb-4 border-b pb-2">💰 Payment</h2>

                            <div class="bg-blue-50 p-4 rounded-xl text-center mb-4">
                                <p class="text-gray-500 text-sm">Total Fee</p>
                                <p class="text-3xl sm:text-4xl font-bold text-blue-600 mt-1 break-words">
                                    $<?php echo number_format((float) ($paymentStudent['total_fee'] ?? 0), 2); ?>
                                </p>
                            </div>

                            <div class="text-sm text-gray-500 mb-4">
                                ✔ Includes all semester fees<br>
                                ✔ Multiple payments allowed
                            </div>
                        </div>

                        <!-- BUTTONS -->
                        <div class="space-y-3">
                            <button onclick="showQR(<?php echo (int) $paymentStudent['id']; ?>, <?php echo (float) ($paymentStudent['total_fee'] ?? 0); ?>)"
                                class="w-full bg-green-600 hover:bg-green-500 text-white py-3 rounded-xl font-semibold shadow">
                                Confirm Payment
                            </button>

                            <!-- show receipt for old place -->
                        </div>

                    </div>

                </div>

                <!-- QR MODAL -->
                <div id="qrModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
                    <div class="bg-white p-6 rounded-xl text-center w-[350px] max-w-[calc(100vw-2rem)]">

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
                <div id="receipt" class="hidden fixed inset-0 z-[180] items-center justify-center bg-black/50 p-4">
                    <div class="w-full max-w-md rounded-xl bg-white p-6 text-center shadow-2xl">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-2xl font-bold">Receipt</h2>
                            <button type="button" onclick="closeReceipt()" class="text-gray-400 hover:text-gray-700">
                                <i class="fa-solid fa-xmark text-xl"></i>
                            </button>
                        </div>

                        <div class="space-y-2 text-left text-sm text-gray-700">
                            <p><strong>Payer Name:</strong> <span id="payerName">-</span></p>
                            <p><strong>Payer Account:</strong> <span id="payerAccount">-</span></p>
                            <p><strong>Student Name:</strong> <?php echo htmlspecialchars($paymentStudent['name']); ?></p>
                            <p><strong>Student Email:</strong> <?php echo htmlspecialchars($paymentStudent['email']); ?></p>
                            <p><strong>Student ID:</strong> <?php echo htmlspecialchars($paymentStudent['stu_id']); ?></p>
                            <p><strong>Amount:</strong> $<span id="receiptAmount"><?php echo number_format((float) ($paymentStudent['total_fee'] ?? 0), 2); ?></span></p>
                            <p><strong>Method:</strong> <span id="receiptMethod">Bakong QR</span></p>
                            <p><strong>Bill No:</strong> <span id="receiptBillNo">-</span></p>
                            <p><strong>Receipt Code:</strong> <span id="receiptCode">-</span></p>
                            <p><strong>Paid To:</strong> <span id="receiptPaidTo">-</span></p>
                            <p><strong>Date:</strong> <span id="payDate"></span></p>
                        </div>

                        <div class="mt-5 rounded-lg bg-green-50 px-4 py-3 font-bold text-green-600">
                            Payment Successful
                        </div>

                        <button type="button" onclick="closeReceipt()"
                            class="mt-5 w-full rounded-lg bg-blue-600 py-2 font-semibold text-white hover:bg-blue-700">
                            Close
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 mt-6 max-w-full overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-800">All Receipts for This Student</h3>
                        <span class="text-sm text-gray-500"><?php echo count($studentReceipts); ?> receipt(s)</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-[720px] w-full text-sm">
                            <thead>
                                <tr class="border-b text-gray-500 text-left">
                                    <th class="py-2">Date</th>
                                    <th>Amount</th>
                                    <th>Bill No</th>
                                    <th>Receipt No</th>
                                    <th>Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($studentReceipts)): ?>
                                    <?php foreach ($studentReceipts as $receipt): ?>
                                        <tr class="border-b">
                                            <td class="py-2"><?php echo htmlspecialchars($receipt['payment_date'] ?? ''); ?></td>
                                            <td>$<?php echo number_format((float) ($receipt['amount'] ?? 0), 2); ?></td>
                                            <td><?php echo htmlspecialchars($receipt['bill_no'] ?? '-'); ?></td>
                                            <td class="text-xs text-gray-500"><?php echo htmlspecialchars($receipt['receipt_code'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($receipt['method'] ?? 'Paid'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td class="py-4 text-gray-500" colspan="5">No receipts found for this student yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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

        <form id="studentForm" action="../Components/insert_student.php" method="POST">

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

            <input id="modalClassId" type="hidden" name="class_id" value="<?php echo (int) $currentClassId; ?>">
            <input id="modalReturnClass" type="hidden" name="return_class_id" value="">

            <select id="modalClassSelect" name="selected_class_id" class="w-full mb-3 px-3 py-2 border rounded-lg">
                <option value="">Select Class</option>
                <?php
                $classOptions = $conn->query("SELECT id, class_name FROM tbl_class ORDER BY class_name ASC");
                while ($classOptions && ($classOption = $classOptions->fetch_assoc())):
                ?>
                    <option value="<?php echo (int) $classOption['id']; ?>">
                        <?php echo htmlspecialchars($classOption['class_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

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

        <form id="classForm" action="../Components/insert_class.php" method="POST">
            <div class="b-2">
                <label for="" class="form-label fw-bold">Class Name</label>
                <input type="text" name="class_name" placeholder="Class Name..."
                    class="w-full mb-3 px-3 py-2 border rounded-lg">
            </div>

            <div class="mb-2">
                <label for="" class="form-label fw-bold">Faculty</label>
                <select id="classFaculty" name="faculty_id" class="w-full mb-3 px-3 py-2 border rounded-lg" onchange="syncFacultyDepartments()">
                    <option value="">Select Faculty</option>
                    <?php
                    $facultyOptions = $conn->query("SELECT id, faculty_name FROM tbl_faculty ORDER BY faculty_name ASC");
                    while ($facultyOptions && ($facultyOption = $facultyOptions->fetch_assoc())):
                    ?>
                        <option value="<?php echo (int) $facultyOption['id']; ?>">
                            <?php echo htmlspecialchars($facultyOption['faculty_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <?php
            $departmentFeeOptions = $conn->query("
                SELECT f.faculty_id, f.department, f.total_fee
                FROM tbl_fee f
                INNER JOIN (
                    SELECT faculty_id, department, MAX(id) AS fee_id
                    FROM tbl_fee
                    WHERE faculty_id IS NOT NULL
                        AND faculty_id > 0
                        AND department IS NOT NULL
                        AND department <> ''
                        AND total_fee > 0
                    GROUP BY faculty_id, department
                ) latest_fee ON latest_fee.fee_id = f.id
                ORDER BY f.faculty_id ASC, f.department ASC
            ");
            ?>
            <div class="mb-2">
                <label for="" class="form-label fw-bold">Department</label>
                <select id="classDepartment" name="department" class="w-full mb-3 px-3 py-2 border rounded-lg" onchange="syncDepartmentFee()">
                    <option value="">Select Department</option>
                    <?php if ($departmentFeeOptions && $departmentFeeOptions->num_rows > 0): ?>
                        <?php while ($departmentFee = $departmentFeeOptions->fetch_assoc()): ?>
                            <option
                                value="<?php echo htmlspecialchars($departmentFee['department']); ?>"
                                data-faculty-id="<?php echo (int) $departmentFee['faculty_id']; ?>"
                                data-price="<?php echo htmlspecialchars((string) $departmentFee['total_fee']); ?>">
                                <?php echo htmlspecialchars($departmentFee['department']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="" disabled>No department fee found</option>
                    <?php endif; ?>
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
                <input id="departmentFeeDisplay" type="text" class="form-control" placeholder="Choose department to load price" readonly>
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

<!-- Delete confirmation modal -->
<div id="deleteModal"
    class="fixed inset-0 z-[200] hidden items-center justify-center bg-slate-950/45 p-4 backdrop-blur-sm">

    <div class="w-full max-w-[470px] rounded-xl bg-white px-9 py-10 text-center shadow-2xl ring-1 ring-cyan-100">
        <div class="mx-auto mb-7 flex h-12 w-12 items-center justify-center rounded-full bg-rose-50 text-rose-500">
            <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
        </div>

        <h2 class="text-2xl font-black text-slate-950">
            Are you sure?
        </h2>

        <p id="deleteModalText" class="mx-auto mt-4 max-w-sm text-base leading-6 text-slate-400">
            This action cannot be undone. All values associated with this field will be lost.
        </p>

        <div class="mt-7 space-y-4">
            <button type="button" onclick="submitPendingDeleteForm()"
                class="w-full rounded-md bg-rose-600 px-5 py-3.5 text-base font-bold text-white shadow-lg shadow-rose-100 transition hover:bg-rose-700">
                Delete field
            </button>

            <button type="button" onclick="closeDeleteModal()"
                class="w-full rounded-md border border-slate-200 bg-white px-5 py-3.5 text-base font-bold text-slate-800 transition hover:bg-slate-50">
                Cancel
            </button>
        </div>
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
    let qrRequestToken = 0;
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
    function openModal(mode = 'student') {
        const currentClassId = <?php echo (int) $currentClassId; ?>;
        const classIdInput = document.getElementById('modalClassId');
        const returnClassInput = document.getElementById('modalReturnClass');
        const classSelect = document.getElementById('modalClassSelect');

        if (mode === 'class' && currentClassId <= 0) {
            alert('Please open a class first, then add the student.');
            return;
        }

        if (mode === 'class') {
            classIdInput.value = currentClassId;
            returnClassInput.value = currentClassId;
            classSelect.value = '';
            classSelect.required = false;
            classSelect.disabled = true;
            classSelect.classList.add('hidden');
        } else {
            classIdInput.value = '';
            returnClassInput.value = '';
            classSelect.disabled = false;
            classSelect.required = true;
            classSelect.classList.remove('hidden');
        }

        document.getElementById('studentModal').classList.remove('hidden');
        document.getElementById('studentModal').classList.add('flex');
    }

    function closeModal() {
        const form = document.getElementById('studentForm');
        if (form) {
            form.reset();
        }

        document.getElementById('studentModal').classList.add('hidden');
        document.getElementById('studentModal').classList.remove('flex');
    }

    // class modal
    function openModalClass() {
        document.getElementById('classModal').classList.remove('hidden');
        document.getElementById('classModal').classList.add('flex');
        syncFacultyDepartments();
    }

    function closeModalClass() {
        const form = document.getElementById('classForm');
        if (form) {
            form.reset();
        }

        syncFacultyDepartments();
        document.getElementById('classModal').classList.add('hidden');
        document.getElementById('classModal').classList.remove('flex');
    }

    function syncFacultyDepartments() {
        const facultySelect = document.getElementById('classFaculty');
        const departmentSelect = document.getElementById('classDepartment');

        if (!facultySelect || !departmentSelect) return;

        const selectedFacultyId = facultySelect.value;
        let hasVisibleDepartment = false;

        Array.from(departmentSelect.options).forEach((option) => {
            if (!option.value) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const matchesFaculty = selectedFacultyId !== '' && option.dataset.facultyId === selectedFacultyId;
            option.hidden = !matchesFaculty;
            option.disabled = !matchesFaculty;
            if (matchesFaculty) {
                hasVisibleDepartment = true;
            }
        });

        if (!selectedFacultyId || departmentSelect.selectedOptions[0]?.disabled) {
            departmentSelect.value = '';
        }

        departmentSelect.disabled = !selectedFacultyId || !hasVisibleDepartment;
        syncDepartmentFee();
    }

    function syncDepartmentFee() {
        const departmentSelect = document.getElementById('classDepartment');
        const feeDisplay = document.getElementById('departmentFeeDisplay');

        if (!departmentSelect || !feeDisplay) return;

        const selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
        const price = selectedOption ? selectedOption.dataset.price : '';
        feeDisplay.value = price ? `$${Number(price).toLocaleString()}` : '';
    }

    let pendingDeleteForm = null;

    function openDeleteConfirm(form) {
        pendingDeleteForm = form;
        const modal = document.getElementById('deleteModal');
        const text = document.getElementById('deleteModalText');
        const typeLabel = form.dataset.deleteType === 'class' ? 'class' : 'student';
        const label = form.dataset.deleteLabel || '';

        text.textContent = `This action cannot be undone. All values associated with this ${typeLabel}${label ? ` (${label})` : ''} will be lost.`;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        return false;
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        pendingDeleteForm = null;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function submitPendingDeleteForm() {
        if (!pendingDeleteForm) {
            closeDeleteModal();
            return;
        }

        pendingDeleteForm.removeAttribute('onsubmit');
        pendingDeleteForm.submit();
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeDeleteModal();
        }
    });

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
        const qrModal = document.getElementById('qrModal');
        qrModal.classList.add('hidden');
        qrModal.style.display = '';
        setConfirmButtonState(false, 'I Have Paid');
        qrReady = false;
        currentBillNo = null;
        currentMd5 = null;
        qrRequestToken++;

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
                closeQR();
                currentReceipt.amount = Number(currentAmount || currentReceipt.amount || 0);
                currentReceipt.method = data.method || currentReceipt.method || 'Bakong QR';
                currentReceipt.billNo = data.bill_no || currentBillNo || currentReceipt.billNo;
                currentReceipt.receiptCode = data.receipt_code || currentReceipt.receiptCode;
                currentReceipt.paidAt = data.paid_at || new Date().toLocaleString();
                updateReceipt(currentReceipt);
                showReceipt();

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
        const receipt = document.getElementById('receipt');
        if (!receipt) return;

        receipt.classList.remove('hidden');
        receipt.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeReceipt() {
        const receipt = document.getElementById('receipt');
        if (!receipt) return;

        receipt.classList.add('hidden');
        receipt.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    function closeQRAuto() {
        const qrModal = document.getElementById('qrModal');
        qrModal.classList.add('hidden');
        qrModal.style.display = '';
        setConfirmButtonState(false, 'I Have Paid');
        qrReady = false;
        currentBillNo = null;
        currentMd5 = null;
        qrRequestToken++;

        if (paymentCheckInterval) {
            clearInterval(paymentCheckInterval);
            paymentCheckInterval = null;
        }
        if (qrRefreshInterval) {
            clearInterval(qrRefreshInterval);
            qrRefreshInterval = null;
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

                    showReceipt();
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
        closeReceipt();
        const requestToken = ++qrRequestToken;
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
        if (paymentCheckInterval) {
            clearInterval(paymentCheckInterval);
            paymentCheckInterval = null;
        }

        const qrModal = document.getElementById('qrModal');
        qrModal.style.display = 'flex';
        qrModal.classList.remove('hidden');

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
                if (requestToken !== qrRequestToken) {
                    return;
                }

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
                if (requestToken !== qrRequestToken) {
                    return;
                }

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

    document.addEventListener('DOMContentLoaded', function() {

        // Only run charts if the canvas elements exist (i.e. home page is visible)
        const overviewEl = document.getElementById('overviewChart');
        const studentsEl = document.getElementById('studentsChart');
        const paymentEl = document.getElementById('paymentChart');
        if (!overviewEl || !studentsEl || !paymentEl) return;

        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
        Chart.defaults.font.size = 11;
        Chart.defaults.color = '#9ca3af';
        const gridColor = 'rgba(0,0,0,0.04)';
        const overviewLabels = <?php echo json_encode($chartMonths); ?>;
        const monthlyStudents = <?php echo json_encode($monthlyStudents); ?>;
        const monthlyPayments = <?php echo json_encode($monthlyPayments); ?>;
        const departmentLabels = <?php echo json_encode(array_map(static function ($row) {
                                        return $row['department'] ?? 'Unknown';
                                    }, $deptGenderStats)); ?>;
        const girlsByDept = <?php echo json_encode(array_map(static function ($row) {
                                return (int) ($row['girls'] ?? 0);
                            }, $deptGenderStats)); ?>;
        const boysByDept = <?php echo json_encode(array_map(static function ($row) {
                                return (int) ($row['boys'] ?? 0);
                            }, $deptGenderStats)); ?>;
        const paymentCounts = <?php echo json_encode($paymentCounts); ?>;
        const paidStudentCounts = <?php echo json_encode($paidStudentCounts); ?>;
        const overviewMax = Math.max(10, ...monthlyStudents, ...monthlyPayments);
        const genderMax = Math.max(10, ...girlsByDept, ...boysByDept);
        const paymentMax = Math.max(10, ...paymentCounts, ...paidStudentCounts);

        // 1. Overview Line Chart
        const overviewCtx = overviewEl.getContext('2d');
        const teacherGradient = overviewCtx.createLinearGradient(0, 0, 0, 200);
        teacherGradient.addColorStop(0, 'rgba(37,99,235,0.15)');
        teacherGradient.addColorStop(1, 'rgba(37,99,235,0)');
        const studentGradient = overviewCtx.createLinearGradient(0, 0, 0, 200);
        studentGradient.addColorStop(0, 'rgba(45,212,191,0.15)');
        studentGradient.addColorStop(1, 'rgba(45,212,191,0)');

        new Chart(overviewCtx, {
            type: 'line',
            data: {
                labels: overviewLabels,
                datasets: [{
                        label: 'New Students',
                        data: monthlyStudents,
                        borderColor: '#2563eb',
                        backgroundColor: teacherGradient,
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.45,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#2563eb',
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 2
                    },
                    {
                        label: 'Payments',
                        data: monthlyPayments,
                        borderColor: '#2dd4bf',
                        backgroundColor: studentGradient,
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.45,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#2dd4bf',
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f1f5f9',
                        bodyColor: '#94a3b8',
                        padding: 10,
                        cornerRadius: 10,
                        displayColors: true,
                        boxWidth: 8,
                        boxHeight: 8
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            color: '#9ca3af'
                        }
                    },
                    y: {
                        min: 0,
                        max: overviewMax,
                        grid: {
                            color: gridColor
                        },
                        border: {
                            display: false,
                            dash: [3, 3]
                        },
                        ticks: {
                            color: '#9ca3af',
                            stepSize: Math.max(1, Math.ceil(overviewMax / 6))
                        }
                    }
                }
            }
        });

        // 2. Bar Chart: Students
        const studentsCtx = studentsEl.getContext('2d');
        new Chart(studentsCtx, {
            type: 'bar',
            data: {
                labels: departmentLabels,
                datasets: [{
                        label: 'Girls',
                        data: girlsByDept,
                        backgroundColor: '#6366f1',
                        borderRadius: 5,
                        borderSkipped: false,
                        barPercentage: 0.6,
                        categoryPercentage: 0.7
                    },
                    {
                        label: 'Boys',
                        data: boysByDept,
                        backgroundColor: '#2dd4bf',
                        borderRadius: 5,
                        borderSkipped: false,
                        barPercentage: 0.6,
                        categoryPercentage: 0.7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f1f5f9',
                        bodyColor: '#94a3b8',
                        padding: 10,
                        cornerRadius: 10,
                        displayColors: true,
                        boxWidth: 8,
                        boxHeight: 8
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            color: '#9ca3af'
                        }
                    },
                    y: {
                        min: 0,
                        max: genderMax,
                        grid: {
                            color: gridColor
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            color: '#9ca3af',
                            stepSize: Math.max(1, Math.ceil(genderMax / 5))
                        }
                    }
                }
            }
        });

        // 3. Payment Collection Line Chart
        const paymentCtx = paymentEl.getContext('2d');
        const collectedGrad = paymentCtx.createLinearGradient(0, 0, 0, 180);
        collectedGrad.addColorStop(0, 'rgba(37,99,235,0.12)');
        collectedGrad.addColorStop(1, 'rgba(37,99,235,0)');
        const pendingGrad = paymentCtx.createLinearGradient(0, 0, 0, 180);
        pendingGrad.addColorStop(0, 'rgba(248,113,113,0.12)');
        pendingGrad.addColorStop(1, 'rgba(248,113,113,0)');

        new Chart(paymentCtx, {
            type: 'line',
            data: {
                labels: overviewLabels,
                datasets: [{
                        label: 'Collected',
                        data: paymentCounts,
                        borderColor: '#2563eb',
                        backgroundColor: collectedGrad,
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#2563eb',
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 2
                    },
                    {
                        label: 'Paid students',
                        data: paidStudentCounts,
                        borderColor: '#f87171',
                        backgroundColor: pendingGrad,
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#f87171',
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f1f5f9',
                        bodyColor: '#94a3b8',
                        padding: 10,
                        cornerRadius: 10,
                        displayColors: true,
                        boxWidth: 8,
                        boxHeight: 8,
                        callbacks: {
                            label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString()}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            color: '#9ca3af'
                        }
                    },
                    y: {
                        min: 0,
                        max: paymentMax,
                        grid: {
                            color: gridColor
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            color: '#9ca3af',
                            stepSize: Math.max(1, Math.ceil(paymentMax / 6)),
                            callback: v => v.toLocaleString()
                        }
                    }
                }
            }
        });

    }); // end DOMContentLoaded

    document.getElementById('searchReceipt').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll('#receiptBody tr');

        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(value) ? '' : 'none';
        });
    });

    function printReceipt(name, stu_id, email, amount, method, bill_no, date) {

        let content = `
    <html>
    <head>
        <title>Receipt</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f3f4f6;
                padding: 20px;
            }

            .receipt {
                max-width: 500px;
                margin: auto;
                background: white;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            }

            .header {
                text-align: center;
                border-bottom: 2px dashed #ddd;
                padding-bottom: 10px;
                margin-bottom: 15px;
            }

            .header h2 {
                margin: 0;
                color: #2563eb;
            }

            .info {
                margin: 10px 0;
                font-size: 14px;
            }

            .info p {
                margin: 5px 0;
            }

            .table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }

            .table th, .table td {
                border-bottom: 1px solid #eee;
                padding: 8px;
                text-align: left;
                font-size: 14px;
            }

            .total {
                text-align: right;
                font-size: 18px;
                font-weight: bold;
                margin-top: 10px;
                color: green;
            }

            .footer {
                text-align: center;
                margin-top: 20px;
                font-size: 12px;
                color: #666;
            }

            .success {
                text-align: center;
                margin-top: 15px;
                color: green;
                font-weight: bold;
            }

            @media print {
                body {
                    background: white;
                }
                .receipt {
                    box-shadow: none;
                }
            }
        </style>
    </head>

    <body>

        <div class="receipt">

            <!-- Header -->
            <div class="header">
                <h2>RUPP PAY</h2>
                <p>Official Payment Receipt</p>
            </div>

            <!-- Info -->
            <div class="info">
                <p><strong>Student Name:</strong> ${name}</p>
                <p><strong>Student Email:</strong> ${email || '-'}</p>
                <p><strong>Student ID:</strong> ${stu_id}</p>
                <p><strong>Date:</strong> ${new Date(date).toLocaleString()}</p>
                <p><strong>Bill No:</strong> ${bill_no || '-'}</p>
            </div>

            <!-- Table -->
            <table class="table">
                <tr>
                    <th>Description</th>
                    <th>Method</th>
                    <th>Amount</th>
                </tr>
                <tr>
                    <td>School Fee Payment</td>
                    <td>${method}</td>
                    <td>$${amount}</td>
                </tr>
            </table>

            <!-- Total -->
            <div class="total">
                Total: $${amount}
            </div>

            <!-- Success -->
            <div class="success">
                ✔ Payment Successful
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>Thank you for your payment</p>
                <p>RUPP Pay System</p>
            </div>

        </div>

    </body>
    </html>
    `;

        let win = window.open('', '', 'width=700,height=700');
        win.document.write(content);
        win.document.close();
        win.print();
    }

    document.getElementById('searchReceipt').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll('#receiptBody tr');

        rows.forEach(row => {
            let name = row.children[1].innerText.toLowerCase();
            let id = row.children[2].innerText.toLowerCase();

            if (name.includes(value) || id.includes(value)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>

<?php include '../Categories/footer.php'; ?>
