<?php
require 'connection.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$stu_id = $_POST['stu_id'];
$name = $_POST['name'];
$gender = $_POST['gender'];
$email = $_POST['email'];
$password = $_POST['password'];
$class_id = $_POST['class_id'];

// validation
if (empty($stu_id) || empty($name) || empty($email) || empty($password)) {
    die("Please fill all required fields");
}

$conn->begin_transaction();

try {

    // check email
    $check = $conn->prepare("SELECT id FROM tbl_account WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        throw new Exception("Email already exists!");
    }

    // insert student
    $stmt1 = $conn->prepare("INSERT INTO tbl_student (stu_id, name, gender, email, password, class_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt1->bind_param("sssssi", $stu_id, $name, $gender, $email, $password, $class_id);
    $stmt1->execute();

    // insert account
    $stmt2 = $conn->prepare("INSERT INTO tbl_account (name, email, password, is_admin) VALUES (?, ?, ?, 2)");
    $stmt2->bind_param("sss", $name, $email, $password);
    $stmt2->execute();

    $conn->commit();

    header("Location: ../Pages/Dashboards.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}
?>