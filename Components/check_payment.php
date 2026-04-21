<?php
require 'connection.php';

$bill_no = $_GET['bill_no'] ?? '';
$student_id = $_GET['student_id'] ?? 0;
$amount = $_GET['amount'] ?? 0;

$token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJkYXRhIjp7ImlkIjoiMmQyOWY1MjBlYTQ5NDY4YSJ9LCJpYXQiOjE3NzY2MDc2OTIsImV4cCI6MTc4NDM4MzY5Mn0.8RE0AfQT4311YXzXbwNgNoLHhNFnBAyysJ2KemDbFFo"; // ប្រើ Token របស់អ្នក

$data = ["bill_no" => $bill_no];

$ch = curl_init("https://api-bakong.nbc.gov.kh/v1/checkTransaction");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

// ប្រសិនបើបង់ប្រាក់ជោគជ័យ (responseCode == 0)
if (isset($result['responseCode']) && $result['responseCode'] == 0) {

    // ប្រើ Prepared Statement ដើម្បីសុវត្ថិភាព
    $stmt = $conn->prepare("INSERT INTO tbl_payment (student_id, amount, payment_date) VALUES (?, ?, NOW())");
    $stmt->bind_param("id", $student_id, $amount);
    
    if ($stmt->execute()) {
        echo "PAID";
    } else {
        echo "DB_ERROR";
    }
    $stmt->close();
} else {
    echo "PENDING";
}
?>