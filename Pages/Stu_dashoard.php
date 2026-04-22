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


include '../Components/connection.php';

$student_id = isset($_GET['student_id']) ? (int) $_GET['student_id'] : 0;
$paymentStudent = null;

if ($student_id > 0) {
    $stmt = $conn->prepare("SELECT 
    s.id, s.stu_id, s.name, s.email, 
    c.class_name, c.department, c.year, 
    f.total_fee
FROM tbl_student s
LEFT JOIN tbl_class c ON s.class_id = c.id
LEFT JOIN tbl_fee f ON c.id = f.class_id
WHERE s.email = '$email' LIMIT 1");
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
                        <p><strong>Class:</strong> <?php echo 'Year ' . $data['year'] ?></p>
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
                    <button onclick="showQR(<?php echo (int) $data['id']; ?>, <?php echo (float) ($data['total_fee'] ?? 0); ?>)"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg shadow transition">
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

            <!-- QR MODAL -->
            <div id="qrModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
                <div class="bg-white p-6 rounded-xl text-center w-[350px]">

                    <h2 class="text-xl font-bold mb-4">Scan QR to Pay</h2>

                    <!-- QR -->
                    <img id="bakongQR" src="" alt="Payment QR Code" class="mx-auto w-[220px] h-[220px] object-contain" />

                    <p class="mt-3 text-gray-500 text-sm">Scan using Bakong / ABA</p>

                    <button id="confirmPaymentBtn" onclick="confirmPayment()"
                        class="bg-green-600 text-white mt-4 px-4 py-2 rounded-lg w-full">
                        I Have Paid
                    </button>

                    <button onclick="closeQR()"
                        class="mt-2 text-red-500">
                        Cancel
                    </button>

                </div>
            </div>

            <!-- RECEIPT -->
            <div id="receipt" class="hidden bg-white mt-6 p-6 rounded-xl shadow text-center">
                <h2 class="text-2xl font-bold mb-4">🧾 Receipt</h2>

                <p>Name: <?php echo htmlspecialchars($paymentStudent['name']); ?></p>
                <p>ID: <?php echo htmlspecialchars($paymentStudent['stu_id']); ?></p>
                <p>Amount: $<?php echo number_format((float) ($paymentStudent['total_fee'] ?? 0), 2); ?></p>
                <p>Date: <span id="payDate"></span></p>

                <div class="text-green-600 font-bold mt-4">
                    ✔ Payment Successful
                </div>
            </div>
            <?php if ($student_id > 0): ?>
                <div class="bg-white rounded-2xl shadow p-6">
                    <h2 class="text-xl font-bold mb-2">Student not found</h2>
                    <p class="text-gray-500">The selected student could not be loaded for payment.</p>
                </div>
            <?php endif; ?>
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
            showSection('payment_page');
            <?php if ($paymentStudent): ?>
            <?php endif; ?>
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
        document.getElementById('qrModal').classList.add('hidden');
        setConfirmButtonState(false, 'I Have Paid');

        // Stop QR refresh when user cancels
        if (qrRefreshInterval) {
            clearInterval(qrRefreshInterval);
            qrRefreshInterval = null;
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



    function checkPayment(billNo, studentId, amount) {
        let interval = setInterval(() => {
            fetch(`../Components/check_payment.php?bill_no=${billNo}&student_id=${studentId}&amount=${amount}`)
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

        fetch("../Components/generate_qr.php?student_id=" + encodeURIComponent(studentId) + "&amount=" + encodeURIComponent(amount))
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

                // Start QR refresh timer (refresh every 4 minutes to prevent expiration)
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

            fetch("../Components/generate_qr.php?student_id=" + encodeURIComponent(studentId) + "&amount=" + encodeURIComponent(amount))
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