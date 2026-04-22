<?php
require 'connection.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$bill_no = $_GET['bill_no'] ?? '';
$md5 = $_GET['md5'] ?? '';
$hash = $_GET['hash'] ?? '';
$student_id = (int) ($_GET['student_id'] ?? 0);
$amount = (float) ($_GET['amount'] ?? 0);

if ($md5 === '' && $hash === '') {
    echo json_encode([
        'status' => 'ERROR',
        'message' => 'Missing QR hash.'
    ]);
    exit();
}

$token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJkYXRhIjp7ImlkIjoiMmQyOWY1MjBlYTQ5NDY4YSJ9LCJpYXQiOjE3NzY2MDc2OTIsImV4cCI6MTc4NDM4MzY5Mn0.8RE0AfQT4311YXzXbwNgNoLHhNFnBAyysJ2KemDbFFo";

function bakongCheckTransaction(string $endpoint, array $payload, string $token): array
{
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $curlError) {
        return [
            'ok' => false,
            'error' => $curlError ?: 'Unknown Bakong API error'
        ];
    }

    $result = json_decode($response, true);
    if (!is_array($result)) {
        return [
            'ok' => false,
            'error' => 'Bakong returned an invalid response.',
            'raw' => $response
        ];
    }

    return [
        'ok' => true,
        'result' => $result
    ];
}

$candidates = [];

if ($md5 !== '') {
    $candidates[] = [
        'endpoint' => 'https://api-bakong.nbc.gov.kh/v1/check_transaction_by_md5',
        'payload' => ['md5' => $md5]
    ];
}

if ($hash !== '' && $hash !== $md5) {
    $candidates[] = [
        'endpoint' => 'https://api-bakong.nbc.gov.kh/v1/check_transaction_by_hash',
        'payload' => ['hash' => $hash]
    ];
}

if ($hash === '' && $md5 !== '') {
    $candidates[] = [
        'endpoint' => 'https://api-bakong.nbc.gov.kh/v1/check_transaction_by_hash',
        'payload' => ['hash' => $md5]
    ];
}

$result = null;
$apiError = null;
$paymentFound = false;

foreach ($candidates as $candidate) {
    $check = bakongCheckTransaction($candidate['endpoint'], $candidate['payload'], $token);

    if (!$check['ok']) {
        $apiError = $check['error'] ?? 'Failed to contact Bakong API.';
        continue;
    }

    $result = $check['result'];
    $responseCode = (int) ($result['responseCode'] ?? ($result['status']['code'] ?? 1));
    if ($responseCode !== 0) {
        continue;
    }

    $paymentFound = true;
    break;
}

if (!$paymentFound || !is_array($result)) {
    echo json_encode([
        'status' => 'PENDING',
        'message' => 'Transaction not confirmed yet.',
        'detail' => $apiError
    ]);
    exit();
}

$payerName = $result['data']['fromAccountId'] ?? ($result['data']['payerName'] ?? 'Unknown');
$payerAccount = $result['data']['fromAccountId'] ?? ($result['data']['payerAccount'] ?? 'Unknown');

$fee_id = 0;
$paid_at = date('Y-m-d H:i:s');
$bankName = trim(getenv('BAKONG_MERCHANT_NAME') ?: 'RUPP Pay');
$bankAccount = trim(getenv('BAKONG_ACCOUNT_ID') ?: 'khim_reaksmey@bkrt');
$bankCity = trim(getenv('BAKONG_MERCHANT_CITY') ?: 'PHNOM PENH');

$feeStmt = $conn->prepare("
    SELECT f.id AS fee_id
    FROM tbl_student s
    LEFT JOIN tbl_class c ON s.class_id = c.id
    LEFT JOIN tbl_fee f ON c.id = f.class_id
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

$payment_id = 0;
$paymentStmt = $conn->prepare("
    SELECT id
    FROM tbl_payment
    WHERE student_id = ? AND fee_id = ? AND amount = ?
    ORDER BY id DESC
    LIMIT 1
");

if ($paymentStmt) {
    $paymentStmt->bind_param("iid", $student_id, $fee_id, $amount);
    $paymentStmt->execute();
    $paymentRes = $paymentStmt->get_result();
    if ($paymentRes && ($paymentRow = $paymentRes->fetch_assoc())) {
        $payment_id = (int) ($paymentRow['id'] ?? 0);
    }
    $paymentStmt->close();
}

if ($payment_id <= 0) {
    $method = 'Bakong QR';
    $insertPayment = $conn->prepare("
        INSERT INTO tbl_payment (student_id, fee_id, amount, payment_date, method)
        VALUES (?, ?, ?, CURDATE(), ?)
    ");

    if ($insertPayment) {
        $insertPayment->bind_param("iids", $student_id, $fee_id, $amount, $method);
        $insertPayment->execute();
        $payment_id = (int) $insertPayment->insert_id;
        $insertPayment->close();
    }
}

$receipt_code = '';
$receiptStmt = $conn->prepare("SELECT receipt_code FROM tbl_receipt WHERE payment_id = ? LIMIT 1");
if ($receiptStmt) {
    $receiptStmt->bind_param("i", $payment_id);
    $receiptStmt->execute();
    $receiptRes = $receiptStmt->get_result();
    if ($receiptRes && ($receiptRow = $receiptRes->fetch_assoc())) {
        $receipt_code = (string) ($receiptRow['receipt_code'] ?? '');
    }
    $receiptStmt->close();
}

if ($receipt_code === '') {
    $receipt_code = 'RCPT-' . date('YmdHis') . '-' . $payment_id;
    $insertReceipt = $conn->prepare("INSERT INTO tbl_receipt (payment_id, receipt_code) VALUES (?, ?)");
    if ($insertReceipt) {
        $insertReceipt->bind_param("is", $payment_id, $receipt_code);
        $insertReceipt->execute();
        $insertReceipt->close();
    }
}

echo json_encode([
    'status' => 'PAID',
    'message' => 'Payment confirmed.',
    'payment_id' => $payment_id,
    'bill_no' => $bill_no,
    'md5' => $md5,
    'hash' => $hash,
    'receipt_code' => $receipt_code,
    'amount' => $amount,
    'method' => 'Bakong QR',

    // ✅ ADD HERE
    'payer_name' => $payerName,
    'payer_account' => $payerAccount,

    'bank_name' => $bankName,
    'bank_account' => $bankAccount,
    'bank_city' => $bankCity,
    'paid_at' => $paid_at
]);
