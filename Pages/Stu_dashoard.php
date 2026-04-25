<?php
session_start();
require '../Components/connection.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 2) {
    header('location:../Components/Login.php');
    exit();
}

$email = $_SESSION['email'];

$studentSql = "
    SELECT 
        s.id,
        s.stu_id,
        s.name,
        s.gender,
        s.email,
        s.created_at,
        c.class_name,
        c.department,
        c.year,
        COALESCE(f.total_fee, 0) AS total_fee
    FROM tbl_student s
    LEFT JOIN tbl_class c ON s.class_id = c.id
    LEFT JOIN (
        SELECT faculty_id, department, MAX(id) AS fee_id
        FROM tbl_fee
        GROUP BY faculty_id, department
    ) latest_fee ON latest_fee.faculty_id = c.faculty_id AND latest_fee.department = c.department
    LEFT JOIN tbl_fee f ON f.id = latest_fee.fee_id
    WHERE s.email = ?
    LIMIT 1
";

$stmt = $conn->prepare($studentSql);
$stmt->bind_param("s", $email);
$stmt->execute();
$ex = $stmt->get_result();
$data = $ex ? $ex->fetch_assoc() : null;
$stmt->close();

$student_id = isset($_GET['student_id']) ? (int) $_GET['student_id'] : 0;
$paymentStudent = $data;
$totalPaid = 0.0;
$recentPayments = [];
$latestPayment = null;

if ($data && !empty($data['id'])) {
    $currentStudentId = (int) $data['id'];

    $paymentSummaryStmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) AS total_paid FROM tbl_payment WHERE student_id = ?");
    if ($paymentSummaryStmt) {
        $paymentSummaryStmt->bind_param("i", $currentStudentId);
        $paymentSummaryStmt->execute();
        $paymentSummaryResult = $paymentSummaryStmt->get_result();
        if ($paymentSummaryResult && ($paymentSummaryRow = $paymentSummaryResult->fetch_assoc())) {
            $totalPaid = (float) ($paymentSummaryRow['total_paid'] ?? 0);
        }
        $paymentSummaryStmt->close();
    }

    $recentPaymentsStmt = $conn->prepare("
        SELECT p.payment_date, p.amount, p.method, p.bill_no, r.receipt_code
        FROM tbl_payment p
        LEFT JOIN tbl_receipt r ON p.id = r.payment_id
        WHERE p.student_id = ?
        ORDER BY p.payment_date DESC, p.id DESC
        LIMIT 10
    ");
    if ($recentPaymentsStmt) {
        $recentPaymentsStmt->bind_param("i", $currentStudentId);
        $recentPaymentsStmt->execute();
        $recentPaymentsResult = $recentPaymentsStmt->get_result();
        while ($recentPaymentsResult && ($row = $recentPaymentsResult->fetch_assoc())) {
            $recentPayments[] = $row;
        }
        $recentPaymentsStmt->close();
    }

    $latestPayment = $recentPayments[0] ?? null;
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

                <button onclick="showSection('about_button', this)" class="flex items-center gap-3 p-2 text-[17px] rounded-lg hover:bg-gray-300 fw-medium">
                    <i class="fa-solid fa-info-circle"></i> About
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
        <div id="home_button" class="show_hide mb-9">
            <!-- Welcome -->
            <div class=" flex text-black bg-white rounded-2xl p-6 shadow mb-8">
                <div>
                <h1 class="text-4xl fw-bold">Dear! <?php echo $data['name'] ?> !</h1>
                <p class="text-gray-500 mt-2 ml-2">Welcome to your student payment system</p>
                </div>
                <div class="text-blue-500 text-[50px] ml-auto">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
            </div>

            <!-- Main Grid -->
            <div class="grid grid-cols-3 gap-6">

                <!-- Student Info -->
                <div class="bg-white rounded-xl shadow p-5 col-span-1">
                    <h3 class="font-semibold text-gray-700 mb-4">Student Info</h3>

                    <div class="space-y-2 text-sm text-gray-600">
                        <p><strong>ID:</strong> <?php echo htmlspecialchars($data['stu_id'] ?? 'N/A'); ?></p>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($data['name'] ?? 'N/A'); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($data['email'] ?? 'N/A'); ?></p>
                        <p><strong>Department:</strong> <?php echo htmlspecialchars($data['department'] ?? 'N/A'); ?></p>
                        <p><strong>Class:</strong> <?php echo 'Year ' . htmlspecialchars($data['year'] ?? 'N/A'); ?></p>
                        <p><strong>Total Fee:</strong> <?php echo number_format((float) ($data['total_fee'] ?? 0), 2); ?>$</p>
                    </div>
                </div>

                <!-- Payment Status -->
                <div class="bg-white rounded-xl shadow p-6 col-span-2">

                    <h3 class="font-semibold text-gray-700 mb-4">Payment Overview</h3>

                    <?php
                    $totalFee = (float) ($data['total_fee'] ?? 0);
                    $remainingFee = max($totalFee - $totalPaid, 0);
                    $progressPercent = $totalFee > 0 ? min(100, round(($totalPaid / $totalFee) * 100)) : 0;
                    ?>

                    <!-- Numbers -->
                    <div class="flex justify-between mb-4">
                        <div>
                            <p class="text-sm text-gray-500">Total</p>
                            <h2 id="overviewTotalFee" class="text-xl font-bold">$<?php echo number_format($totalFee, 2); ?></h2>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">Paid</p>
                            <h2 id="overviewTotalPaid" class="text-xl font-bold text-green-500">$<?php echo number_format($totalPaid, 2); ?></h2>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">Remaining</p>
                            <h2 id="overviewRemainingFee" class="text-xl font-bold text-red-500">$<?php echo number_format($remainingFee, 2); ?></h2>
                        </div>
                    </div>

                    <!-- Progress -->
                    <div class="w-full bg-gray-200 rounded-full h-3 mb-3">
                        <div id="overviewProgressBar" class="bg-blue-600 h-3 rounded-full" style="width: <?php echo (int) $progressPercent; ?>%"></div>
                    </div>

                    <div class="flex items-center justify-between mb-4">
                        <p id="overviewProgressText" class="text-sm text-gray-500"><?php echo (int) $progressPercent; ?>% completed</p>
                        <p class="text-sm text-gray-500">
                            Updated: <?php echo $latestPayment ? htmlspecialchars($latestPayment['payment_date'] ?? '') : 'No payment yet'; ?>
                        </p>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-gray-800">Latest Payment</h4>
                            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-blue-50 text-blue-700">Live snapshot</span>
                        </div>
                        <?php if ($latestPayment): ?>
                            <div class="grid gap-2 text-sm text-gray-600">
                                <p>Date: <span id="latestPaymentDate"><?php echo htmlspecialchars($latestPayment['payment_date'] ?? ''); ?></span></p>
                                <p>Amount: $<span id="latestPaymentAmount"><?php echo number_format((float) ($latestPayment['amount'] ?? 0), 2); ?></span></p>
                                <p>Bill No: <span id="latestPaymentBillNo"><?php echo htmlspecialchars($latestPayment['bill_no'] ?? '-'); ?></span></p>
                                <p>Receipt: <span id="latestPaymentReceiptCode"><?php echo htmlspecialchars($latestPayment['receipt_code'] ?? '-'); ?></span></p>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">No payments have been made yet. Your first payment will appear here automatically.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Button -->
                    <button onclick="showQR(<?php echo (int) ($data['id'] ?? 0); ?>, <?php echo (float) ($data['total_fee'] ?? 0); ?>)"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow transition">
                        Pay with QR
                    </button>

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
                            <th class="text-left">Bill No</th>
                            <th class="text-left">Receipt</th>
                            <th class="text-left">Status</th>
                        </tr>
                    </thead>

                    <tbody id="recentPaymentsBody">
                        <?php if (!empty($recentPayments)): ?>
                            <?php foreach ($recentPayments as $payment): ?>
                                <tr class="border-b">
                                    <td class="py-2"><?php echo htmlspecialchars($payment['payment_date'] ?? ''); ?></td>
                                    <td>$<?php echo number_format((float) ($payment['amount'] ?? 0), 2); ?></td>
                                    <td><?php echo htmlspecialchars($payment['bill_no'] ?? '-'); ?></td>
                                    <td class="text-xs text-gray-500"><?php echo htmlspecialchars($payment['receipt_code'] ?? '-'); ?></td>
                                    <td class="text-green-500"><?php echo htmlspecialchars($payment['method'] ?? 'Paid'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td class="py-2 text-gray-500" colspan="5">No payments found yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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

                    <button type="button" onclick="closeQR()"
                        class="mt-2 text-red-500">
                        Cancel
                    </button>
                </div>
            </div>

            <!-- RECEIPT -->
            <div id="receipt" class="hidden fixed inset-0 z-[170] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeReceipt(false)"></div>
                <div class="relative bg-white w-full max-w-md p-6 rounded-2xl shadow-2xl text-center">
                    <h2 class="text-2xl font-bold mb-4">🧾 Receipt</h2>
                    <p>Payer Name: <span id="payerName">-</span></p>
                    <p>Payer Account: <span id="payerAccount">-</span></p>
                    <p>Name: <?php echo htmlspecialchars($paymentStudent['name'] ?? ''); ?></p>
                    <p>ID: <?php echo htmlspecialchars($paymentStudent['stu_id'] ?? ''); ?></p>
                    <p>Amount: $<span id="receiptAmount">0.00</span></p>
                    <p>Method: <span id="receiptMethod">-</span></p>
                    <p>Bill No: <span id="receiptBillNo">-</span></p>
                    <p>Receipt No: <span id="receiptCode">-</span></p>
                    <p>Paid To: <span id="receiptPaidTo">-</span></p>
                    <p>Date: <span id="payDate"></span></p>

                    <div class="text-green-600 font-bold mt-4">
                        ✔ Payment Successful
                    </div>
                    <button type="button" onclick="closeReceipt(true)" class="mt-4 w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                        Done
                    </button>
                </div>
            </div>
            <?php if ($student_id > 0 && !$paymentStudent): ?>
                <div class="bg-white rounded-2xl shadow p-6">
                    <h2 class="text-xl font-bold mb-2">Student not found</h2>
                    <p class="text-gray-500">The selected student could not be loaded for payment.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- history Page -->
        <div id="history_button" class="show_hide" style="display: none;">
            <div class="flex items-center justify-between mb-5">
                <h1 class="text-2xl font-bold">Payment History</h1>
                <p class="text-sm text-gray-500"><?php echo count($recentPayments); ?> records</p>
            </div>

            <?php if (!empty($recentPayments)): ?>
                <div class="grid gap-4">
                    <?php foreach ($recentPayments as $payment): ?>
                        <?php
                        $historyPayment = [
                            'payment_date' => $payment['payment_date'] ?? '',
                            'amount' => (float) ($payment['amount'] ?? 0),
                            'bill_no' => $payment['bill_no'] ?? '-',
                            'receipt_code' => $payment['receipt_code'] ?? '-',
                            'method' => $payment['method'] ?? 'Bakong QR'
                        ];
                        ?>
                        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-5 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold">
                                        Paid
                                    </span>
                                    <span class="text-sm text-gray-500"><?php echo htmlspecialchars($payment['payment_date'] ?? ''); ?></span>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800">$<?php echo number_format((float) ($payment['amount'] ?? 0), 2); ?></h3>
                                <p class="text-sm text-gray-500 truncate">Bill No: <?php echo htmlspecialchars($payment['bill_no'] ?? '-'); ?></p>
                                <p class="text-sm text-gray-500 truncate">Receipt No: <?php echo htmlspecialchars($payment['receipt_code'] ?? '-'); ?></p>
                            </div>
                            <button
                                type="button"
                                class="shrink-0 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow transition"
                                data-payment="<?php echo htmlspecialchars(json_encode($historyPayment, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG), ENT_QUOTES, 'UTF-8'); ?>"
                                onclick="openHistoryReceipt(this)">
                                View
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-2xl shadow p-6">
                    <p class="text-gray-500">No payment history found yet.</p>
                </div>
            <?php endif; ?>
        </div>
        <!-- Student page -->
        <div id="about_button" class="show_hide" style="display: none;">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h1 class="text-2xl font-bold">Student Information</h1>
                    <p class="text-sm text-gray-500 mt-1">Your payment details, privacy, and how to pay from one place.</p>
                </div>
                <div class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-full bg-blue-50 text-blue-700 text-sm font-semibold">
                    <i class="fa-solid fa-shield-halved"></i>
                    Private & secure
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="bg-white rounded-2xl shadow p-6 border border-gray-100">
                    <h2 class="text-lg font-bold mb-4">Privacy Notice</h2>
                    <div class="space-y-4 text-sm text-gray-600 leading-6">
                        <p>
                            Your student profile is visible only after login. We use your ID, name, email, and class information to manage school fee payments.
                        </p>
                        <p>
                            Payment records and receipts are stored in your account history so you can review them later. Your data should not be shared with other users.
                        </p>
                        <div class="rounded-xl bg-blue-50 border border-blue-100 p-4 text-blue-900">
                            <p class="font-semibold mb-2">Keep your account safe</p>
                            <ul class="space-y-1 list-disc pl-5">
                                <li>Do not share your password with anyone.</li>
                                <li>Log out after using a public device.</li>
                                <li>Check the receipt before leaving the payment screen.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 border border-gray-100">
                    <h2 class="text-lg font-bold mb-4">How to Pay Fee</h2>
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center font-bold">1</div>
                            <div>
                                <h3 class="font-semibold text-gray-800">Open the QR payment</h3>
                                <p class="text-sm text-gray-500">Click <span class="font-medium">Pay with QR</span> to start the payment process.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center font-bold">2</div>
                            <div>
                                <h3 class="font-semibold text-gray-800">Scan and pay</h3>
                                <p class="text-sm text-gray-500">Use Bakong, ABA, or your supported banking app to scan the QR code and send the fee.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center font-bold">3</div>
                            <div>
                                <h3 class="font-semibold text-gray-800">Wait for confirmation</h3>
                                <p class="text-sm text-gray-500">The system will detect the payment, close the QR modal, and show your receipt automatically.</p>
                            </div>
                        </div>

                        <div class="mt-4 rounded-xl bg-gray-50 border border-gray-100 p-4">
                            <p class="text-sm text-gray-600">
                                Tip: You can always use the <span class="font-semibold">History</span> tab to view previous receipts and payment records.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="support_button" class="show_hide" style="display: none;">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h1 class="text-2xl font-bold">Support</h1>
                    <p class="text-sm text-gray-500 mt-1">Contact admin when you have a payment or account issue.</p>
                </div>
                <div class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-full bg-green-50 text-green-700 text-sm font-semibold">
                    <i class="fa-solid fa-headset"></i>
                    Help center
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white p-6 rounded-2xl shadow border border-gray-100 lg:col-span-2">
                    <h2 class="text-lg font-bold mb-4">Common Problems</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl border border-gray-100 p-4 bg-gray-50">
                            <h3 class="font-semibold text-gray-800 mb-1">Payment not confirmed</h3>
                            <p class="text-sm text-gray-500">Wait for the Bakong scan to finish. If it still does not update, contact admin with your bill number.</p>
                        </div>
                        <div class="rounded-xl border border-gray-100 p-4 bg-gray-50">
                            <h3 class="font-semibold text-gray-800 mb-1">Wrong student info</h3>
                            <p class="text-sm text-gray-500">If your name, email, or class is incorrect, ask admin to review your profile before paying again.</p>
                        </div>
                        <div class="rounded-xl border border-gray-100 p-4 bg-gray-50">
                            <h3 class="font-semibold text-gray-800 mb-1">Receipt missing</h3>
                            <p class="text-sm text-gray-500">Open the History tab and press <span class="font-medium">View</span> to check the receipt for that payment.</p>
                        </div>
                        <div class="rounded-xl border border-gray-100 p-4 bg-gray-50">
                            <h3 class="font-semibold text-gray-800 mb-1">Duplicate payment</h3>
                            <p class="text-sm text-gray-500">If you paid twice by mistake, send the bill number and receipt code to admin for review.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow border border-gray-100">
                    <h2 class="text-lg font-bold mb-4">Contact Admin</h2>
                    <div class="space-y-4 text-sm text-gray-600">
                        <div class="rounded-xl bg-blue-50 border border-blue-100 p-4">
                            <p class="font-semibold text-gray-800">Email</p>
                            <p class="break-all">admin@rupppay.com</p>
                        </div>
                        <div class="rounded-xl bg-blue-50 border border-blue-100 p-4">
                            <p class="font-semibold text-gray-800">Phone</p>
                            <p>+855 12 345 678</p>
                        </div>
                        <div class="rounded-xl bg-blue-50 border border-blue-100 p-4">
                            <p class="font-semibold text-gray-800">What to send</p>
                            <p>Student ID, bill number, receipt code, and a short message about the issue.</p>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col gap-3">
                        <a href="mailto:admin@rupppay.com" class="text-center no-underline bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition">
                            Email Admin
                        </a>
                        <a href="tel:+85512345678" class="text-center no-underline border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold py-2.5 rounded-lg transition">
                            Call Admin
                        </a>
                    </div>
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
                </div>
            </div>

            <div class="grid gap-5 mt-5 lg:grid-cols-1">


                <div class="bg-white rounded-2xl shadow border border-gray-100 p-6 lg:col-span-2">
                    <h2 class="text-lg font-bold mb-4">Preferences</h2>

                    <div class="grid gap-2 md:grid-cols-2">
                        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-semibold text-gray-800">Payment alerts</h3>
                                    <p class="text-sm text-gray-500 mt-1">Receive a notice when your QR payment is confirmed.</p>
                                </div>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" checked class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-2.5"></div>
                                </label>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-semibold text-gray-800">Receipt history</h3>
                                    <p class="text-sm text-gray-500 mt-1">Keep your paid receipts visible in the History tab.</p>
                                </div>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" checked class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-2.5"></div>
                                </label>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-semibold text-gray-800">Privacy mode</h3>
                                    <p class="text-sm text-gray-500 mt-1">Hide sensitive details when you are on a shared screen.</p>
                                </div>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-2.5"></div>
                                </label>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-semibold text-gray-800">Auto refresh QR</h3>
                                    <p class="text-sm text-gray-500 mt-1">Refresh the payment code automatically if it takes too long.</p>
                                </div>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" checked class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-2.5"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <div class="rounded-2xl bg-blue-50 border border-blue-100 p-4">
                            <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Theme</p>
                            <p class="text-sm font-semibold text-gray-800 mt-1">Clean Blue</p>
                        </div>
                        <div class="rounded-2xl bg-blue-50 border border-blue-100 p-4">
                            <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Language</p>
                            <p class="text-sm font-semibold text-gray-800 mt-1">English</p>
                        </div>
                        <div class="rounded-2xl bg-blue-50 border border-blue-100 p-4">
                            <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Security</p>
                            <p class="text-sm font-semibold text-gray-800 mt-1">2-step verification</p>
                        </div>
                    </div>

                    <div class="bg-white mt-3 rounded-2xl border border-slate-200 shadow-sm p-5">
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
                            <button type="button" class="px-5 py-3 rounded-xl bg-blue-500 text-white font-semibold hover:bg-blue-600 inline-flex items-center gap-2">
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
    </div>

    <!-- History Receipt Modal -->
    <div id="historyReceiptModal" class="hidden fixed inset-0 z-[170] flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeHistoryReceipt()"></div>
        <div class="relative bg-white rounded-2xl w-full max-w-md shadow-2xl p-6 text-left">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold">Receipt</h2>
                <button type="button" onclick="closeHistoryReceipt()" class="text-gray-400 hover:text-red-500 text-xl leading-none">&times;</button>
            </div>
            <p>Payer Name: <span id="historyReceiptPayerName"><?php echo htmlspecialchars($data['name'] ?? ''); ?></span></p>
            <p>Payer Account: <span id="historyReceiptPayerAccount"><?php echo htmlspecialchars($data['email'] ?? ''); ?></span></p>
            <p>Amount: $<span id="historyReceiptAmount">0.00</span></p>
            <p>Method: <span id="historyReceiptMethod">-</span></p>
            <p>Bill No: <span id="historyReceiptBillNo">-</span></p>
            <p>Receipt No: <span id="historyReceiptCode">-</span></p>
            <p>Paid To: <span id="historyReceiptPaidTo">-</span></p>
            <p>Date: <span id="historyReceiptDate"></span></p>
            <div class="text-green-600 font-bold mt-4">✔ Payment Successful</div>
            <button type="button" onclick="closeHistoryReceipt()" class="mt-4 w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                Done
            </button>
        </div>
    </div>

</div>

</div>
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
?>

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
        accountName: '',
        moneyPaid: 0,
        bankName: '<?php echo htmlspecialchars(trim(getenv('BAKONG_MERCHANT_NAME') ?: 'RUPP Pay'), ENT_QUOTES); ?>',
        bankAccount: '<?php echo htmlspecialchars(trim(getenv('BAKONG_ACCOUNT_ID') ?: 'khim_reaksmey@bkrt'), ENT_QUOTES); ?>',
        bankCity: '<?php echo htmlspecialchars(trim(getenv('BAKONG_MERCHANT_CITY') ?: 'PHNOM PENH'), ENT_QUOTES); ?>'
    };
    const overviewTotalFeeValue = <?php echo json_encode((float) ($data['total_fee'] ?? 0)); ?>;
    let overviewTotalPaidValue = <?php echo json_encode((float) $totalPaid); ?>;

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
        if (billNoEl) billNoEl.textContent = receipt.billNo || '-';
        if (receiptCodeEl) receiptCodeEl.textContent = receipt.receiptCode || '-';
        if (payDateEl) payDateEl.textContent = receipt.paidAt || '';
        if (paidToEl) paidToEl.textContent = receipt.accountName || receipt.bankName || '';
    }

    function updatePaymentOverview(deltaAmount, paymentDetails) {
        const amount = Number(deltaAmount || 0);
        overviewTotalPaidValue = Math.max(0, overviewTotalPaidValue + amount);
        const remaining = Math.max(overviewTotalFeeValue - overviewTotalPaidValue, 0);
        const progress = overviewTotalFeeValue > 0 ? Math.min(100, Math.round((overviewTotalPaidValue / overviewTotalFeeValue) * 100)) : 0;

        const totalPaidEl = document.getElementById('overviewTotalPaid');
        const remainingEl = document.getElementById('overviewRemainingFee');
        const progressBarEl = document.getElementById('overviewProgressBar');
        const progressTextEl = document.getElementById('overviewProgressText');
        const latestDateEl = document.getElementById('latestPaymentDate');
        const latestAmountEl = document.getElementById('latestPaymentAmount');
        const latestBillNoEl = document.getElementById('latestPaymentBillNo');
        const latestReceiptCodeEl = document.getElementById('latestPaymentReceiptCode');

        if (totalPaidEl) totalPaidEl.textContent = '$' + overviewTotalPaidValue.toFixed(2);
        if (remainingEl) remainingEl.textContent = '$' + remaining.toFixed(2);
        if (progressBarEl) progressBarEl.style.width = progress + '%';
        if (progressTextEl) progressTextEl.textContent = progress + '% completed';

        if (paymentDetails) {
            if (latestDateEl) latestDateEl.textContent = paymentDetails.paidAt || paymentDetails.payment_date || '';
            if (latestAmountEl) latestAmountEl.textContent = amount.toFixed(2);
            if (latestBillNoEl) latestBillNoEl.textContent = paymentDetails.billNo || paymentDetails.bill_no || '-';
            if (latestReceiptCodeEl) latestReceiptCodeEl.textContent = paymentDetails.receiptCode || paymentDetails.receipt_code || '-';
        }
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
            showSection('class_button');
        }

        if (studentId) {
            showSection('home_button');
        }
    };
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
        if (qrModal) {
            qrModal.classList.add('hidden');
            qrModal.style.display = 'none';
        }
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
                alertReceiptSummary(currentReceipt);

                closeQR();

                showReceipt();
                document.getElementById('receipt').style.display = 'flex';

                currentReceipt.amount = Number(currentAmount || 0);
                currentReceipt.method = data.method || currentReceipt.method || 'Bakong QR';
                currentReceipt.billNo = data.bill_no || currentBillNo || currentReceipt.billNo;
                currentReceipt.receiptCode = data.receipt_code || currentReceipt.receiptCode;
                currentReceipt.paidAt = data.paid_at || new Date().toLocaleString();
                updateReceipt(currentReceipt);
                prependRecentPaymentRow({
                    payment_date: currentReceipt.paidAt,
                    amount: currentReceipt.amount,
                    bill_no: currentReceipt.billNo || '-',
                    receipt_code: currentReceipt.receiptCode || '-',
                    method: currentReceipt.method || 'Bakong QR'
                });
                updatePaymentOverview(currentReceipt.amount, {
                    paidAt: currentReceipt.paidAt,
                    billNo: currentReceipt.billNo,
                    receiptCode: currentReceipt.receiptCode
                });

                // Stop QR refresh on successful payment
                if (qrRefreshInterval) {
                    clearInterval(qrRefreshInterval);
                    qrRefreshInterval = null;
                }
                if (paymentCheckInterval) {
                    clearInterval(paymentCheckInterval);
                    paymentCheckInterval = null;
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
        if (receipt) {
            receipt.classList.remove('hidden');
            receipt.style.display = 'flex';
        }
    }

    function closeReceipt(refreshPage) {
        const receipt = document.getElementById('receipt');
        if (receipt) {
            receipt.classList.add('hidden');
            receipt.style.display = 'none';
        }
        document.body.style.overflow = 'auto';

        if (refreshPage) {
            window.location.reload();
        }
    }

    function alertReceiptSummary(receipt) {
        alert(
            "Payment successful\n" +
            "Account Name: " + (receipt.accountName || receipt.bankName || '-') + "\n" +
            "Money Paid: $" + Number(receipt.moneyPaid || receipt.amount || 0).toFixed(2) + "\n" +
            "Receipt: " + (receipt.receiptCode || '-') + "\n" +
            "Bill No: " + (receipt.billNo || '-') + "\n" +
            "Account No: " + (receipt.bankAccount || '-') + "\n" +
            "Time: " + (receipt.paidAt || '-')
        );
    }

    function closeHistoryReceipt() {
        const modal = document.getElementById('historyReceiptModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
        document.body.style.overflow = 'auto';
    }

    function openHistoryReceipt(button) {
        const raw = button?.dataset?.payment || '';
        let payment = null;

        try {
            payment = JSON.parse(raw);
        } catch (error) {
            payment = null;
        }

        if (!payment) {
            return;
        }

        const modal = document.getElementById('historyReceiptModal');
        const payerNameEl = document.getElementById('historyReceiptPayerName');
        const payerAccountEl = document.getElementById('historyReceiptPayerAccount');
        const amountEl = document.getElementById('historyReceiptAmount');
        const methodEl = document.getElementById('historyReceiptMethod');
        const billNoEl = document.getElementById('historyReceiptBillNo');
        const receiptCodeEl = document.getElementById('historyReceiptCode');
        const paidToEl = document.getElementById('historyReceiptPaidTo');
        const dateEl = document.getElementById('historyReceiptDate');

        if (payerNameEl) payerNameEl.textContent = <?php echo json_encode($data['name'] ?? ''); ?> || '-';
        if (payerAccountEl) payerAccountEl.textContent = <?php echo json_encode($data['email'] ?? ''); ?> || '-';
        if (amountEl) amountEl.textContent = Number(payment.amount || 0).toFixed(2);
        if (methodEl) methodEl.textContent = payment.method || 'Bakong QR';
        if (billNoEl) billNoEl.textContent = payment.bill_no || '-';
        if (receiptCodeEl) receiptCodeEl.textContent = payment.receipt_code || '-';
        if (paidToEl) paidToEl.textContent = '<?php echo htmlspecialchars(trim(getenv("BAKONG_MERCHANT_NAME") ?: "RUPP Pay"), ENT_QUOTES); ?>';
        if (dateEl) dateEl.textContent = payment.payment_date || '';

        if (modal) {
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        }

        document.body.style.overflow = 'hidden';
    }

    function prependRecentPaymentRow(payment) {
        const tbody = document.getElementById('recentPaymentsBody');
        if (!tbody) {
            return;
        }

        const emptyRow = tbody.querySelector('tr td[colspan]');
        if (emptyRow && tbody.children.length === 1) {
            tbody.innerHTML = '';
        }

        const row = document.createElement('tr');
        row.className = 'border-b';

        row.innerHTML = `
            <td class="py-2">${payment.payment_date || ''}</td>
            <td>$${Number(payment.amount || 0).toFixed(2)}</td>
            <td>${payment.bill_no || '-'}</td>
            <td class="text-xs text-gray-500">${payment.receipt_code || '-'}</td>
            <td class="text-green-500">${payment.method || 'Paid'}</td>
        `;

        tbody.prepend(row);
    }



    function checkPayment(billNo, studentId, amount) {
        let interval = setInterval(() => {
            fetch(`../Components/check_payment.php?bill_no=${billNo}&student_id=${studentId}&amount=${amount}`)
                .then(res => res.text())
                .then(data => {
                    if (data.trim() === "PAID") {
                        clearInterval(interval); // បញ្ឈប់ការឆែក
                        closeQR(); // បិទ Modal QR
                        alertReceiptSummary(currentReceipt);

                        // បង្ហាញវិក្កយបត្រ
                        showReceipt();
                        document.getElementById('payDate').innerText = new Date().toLocaleString();

                        // Stop QR refresh on successful payment
                        if (qrRefreshInterval) {
                            clearInterval(qrRefreshInterval);
                            qrRefreshInterval = null;
                        }
                    }
                });
        }, 50000); // ឆែករៀងរាល់ ៥ វិនាទីម្តង
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
                    if (!data || data.status !== 'PAID') {
                        return;
                    }

                    currentReceipt.amount = Number(amount || currentReceipt.amount || 0);
                    currentReceipt.method = data.method || currentReceipt.method || 'Bakong QR';
                    currentReceipt.billNo = billNo || currentReceipt.billNo;
                    currentReceipt.receiptCode = data.receipt_code || currentReceipt.receiptCode;
                    currentReceipt.payerName = data.payer_name || currentReceipt.payerName || '-';
                    currentReceipt.payerAccount = data.payer_account || currentReceipt.payerAccount || '-';
                    currentReceipt.accountName = data.account_name || data.bank_name || currentReceipt.accountName;
                    currentReceipt.moneyPaid = Number(data.money_paid || amount || currentReceipt.moneyPaid || 0);
                    currentReceipt.bankName = data.bank_name || currentReceipt.bankName;
                    currentReceipt.bankAccount = data.bank_account || currentReceipt.bankAccount;
                    currentReceipt.bankCity = data.bank_city || currentReceipt.bankCity;
                    currentReceipt.paidAt = data.paid_at || new Date().toLocaleString();
                    updateReceipt(currentReceipt);
                    updatePaymentOverview(currentReceipt.moneyPaid || currentReceipt.amount || amount, {
                        paidAt: currentReceipt.paidAt,
                        billNo: currentReceipt.billNo,
                        receiptCode: currentReceipt.receiptCode
                    });

                    if (paymentCheckInterval) {
                        clearInterval(paymentCheckInterval);
                        paymentCheckInterval = null;
                    }

                    if (qrRefreshInterval) {
                        clearInterval(qrRefreshInterval);
                        qrRefreshInterval = null;
                    }

                    const qrModal = document.getElementById('qrModal');
                    qrModal.classList.add('hidden');
                    qrModal.style.display = 'none';

                    showReceipt();

                    document.getElementById('payDate').innerText = currentReceipt.paidAt;

                    alertReceiptSummary(currentReceipt);
                })
                .catch(err => {
                    console.error("Payment check error:", err);
                });
        }, 5000);
    }

    function showQR(studentId, amount) {
        document.getElementById('receipt').classList.add('hidden');
        currentStudent = studentId;
        currentAmount = amount;
        currentBillNo = null;
        currentMd5 = null;
        qrReady = false;
        currentReceipt = {
            amount: 0,
            method: 'Bakong QR',
            billNo: '',
            receiptCode: '',
            paidAt: '',
            bankName: '<?php echo htmlspecialchars(trim(getenv('BAKONG_MERCHANT_NAME') ?: 'RUPP Pay'), ENT_QUOTES); ?>',
            bankAccount: '<?php echo htmlspecialchars(trim(getenv('BAKONG_ACCOUNT_ID') ?: 'khim_reaksmey@bkrt'), ENT_QUOTES); ?>',
            bankCity: '<?php echo htmlspecialchars(trim(getenv('BAKONG_MERCHANT_CITY') ?: 'PHNOM PENH'), ENT_QUOTES); ?>'
        };
        updateReceipt(currentReceipt);

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
        qrModal.classList.remove('hidden');
        qrModal.style.display = 'flex';

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
                qrImg.src = data.qr_image || buildQrPlaceholder("QR Ready");
                qrReady = true;
                setConfirmButtonState(false, 'I Have Paid');

                //  ADD THIS LINE — start automatic payment detection
                checkPaymentAuto(currentBillNo, studentId, amount);

                // Start QR refresh timer
                startQrRefresh(studentId, amount);
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
