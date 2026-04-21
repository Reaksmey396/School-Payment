<?php
header('Content-Type: application/json');

$amount = $_GET['amount'] ?? 0;
$student_id = $_GET['student_id'] ?? 0;

// ១.  ប្រើ Token
$token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJkYXRhIjp7ImlkIjoiMGFkZTkxZTYtYmU1NC00OTY4LThhNzYtYjYxN2U1M2E0Y2M0In0sImlhdCI6MTY3OTIzOTIzOSwiZXhwIjoyMDk0NTk5MjM5fQ.8RE0AfQT4311YXzXbwNgNoLHhNFnBAyysJ2KemDbFFo";

$billNo = "RUPP_" . time() . "_" . $student_id;

// ២.  រៀបចំ Data ឱ្យគ្រប់លក្ខខណ្ឌដែល Sandbox ត្រូវការ
$data = [
    "merchant_id" => "khim_reaksmey@bkrt",
    "amount"      => (float)$amount,
    "currency"    => "USD",
    "bill_no"     => $billNo,
    "description" => "Student Fee Payment ID: $student_id",
    "mobile_number" => "85512345678", // Sandbox ត្រូវការលេខទូរស័ព្ទគំរូ
    "store_label"   => "RUPP Store",
    "terminal_label"=> "POS-001"
];

// ៣.  ប្រើ URL ឱ្យត្រូវតាមបច្ចេកទេស Sandbox
$ch = curl_init("https://api-bakong.nbc.gov.kh/v1/generate_khqr");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . trim($token),
    "Content-Type: application/json",
    "Accept: application/json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    echo json_encode(["error" => curl_error($ch)]);
} else {
    // បញ្ជូន Response ទៅឱ្យ UI របស់អ្នកវិញ
    echo $response;
}

curl_close($ch);
?>
