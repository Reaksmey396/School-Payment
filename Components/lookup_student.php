<?php
require 'connection.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$stu_id = trim($_GET['stu_id'] ?? '');
$name = trim($_GET['name'] ?? '');
$email = trim($_GET['email'] ?? '');

if ($stu_id === '' || $name === '' || $email === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in student ID, name, and email.'
    ]);
    exit();
}

$stmt = $conn->prepare("
    SELECT
        s.id,
        s.stu_id,
        s.name,
        s.email,
        c.class_name,
        c.department,
        COALESCE(f.total_fee, 0) AS total_fee
    FROM tbl_student s
    LEFT JOIN tbl_class c ON s.class_id = c.id
    LEFT JOIN (
        SELECT faculty_id, department, MAX(id) AS fee_id
        FROM tbl_fee
        GROUP BY faculty_id, department
    ) latest_fee ON latest_fee.faculty_id = c.faculty_id AND latest_fee.department = c.department
    LEFT JOIN tbl_fee f ON f.id = latest_fee.fee_id
    WHERE s.stu_id = ? AND s.name = ? AND s.email = ?
    LIMIT 1
");

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to prepare student lookup.',
        'detail' => mysqli_error($conn)
    ]);
    exit();
}

$stmt->bind_param("sss", $stu_id, $name, $email);
$stmt->execute();
$result = $stmt->get_result();
$student = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$student) {
    echo json_encode([
        'success' => false,
        'message' => 'Student not found. Please check the information and try again.'
    ]);
    exit();
}

echo json_encode([
    'success' => true,
    'student' => [
        'id' => (int) $student['id'],
        'stu_id' => $student['stu_id'],
        'name' => $student['name'],
        'email' => $student['email'],
        'class_name' => $student['class_name'] ?? '',
        'department' => $student['department'] ?? '',
        'total_fee' => (float) ($student['total_fee'] ?? 0)
    ]
]);
