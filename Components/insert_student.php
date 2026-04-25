<?php
require 'connection.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$stu_id = $_POST['stu_id'];
$name = $_POST['name'];
$gender = $_POST['gender'];
$email = $_POST['email'];
$password = $_POST['password'];
$class_id = isset($_POST['class_id']) ? (int) $_POST['class_id'] : 0;
$selected_class_id = isset($_POST['selected_class_id']) ? (int) $_POST['selected_class_id'] : 0;
$return_class_id = isset($_POST['return_class_id']) ? (int) $_POST['return_class_id'] : 0;

if ($class_id <= 0) {
    $class_id = $selected_class_id;
}

// validation
if (empty($stu_id) || empty($name) || empty($email) || empty($password)) {
    die("Please fill all required fields");
}

if ($class_id <= 0) {
    die("Please select a class");
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

    // make sure the current class exists before assigning the student
    $classStmt = $conn->prepare("SELECT id FROM tbl_class WHERE id = ? LIMIT 1");
    $classStmt->bind_param("i", $class_id);
    $classStmt->execute();
    $class = $classStmt->get_result()->fetch_assoc();

    if (!$class) {
        throw new Exception("Class not found!");
    }

    $class_id = (int) $class['id'];

    // insert student
    $stmt1 = $conn->prepare("INSERT INTO tbl_student (stu_id, name, gender, email, password, class_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt1->bind_param("sssssi", $stu_id, $name, $gender, $email, $password, $class_id);
    $stmt1->execute();

    // insert account
    $stmt2 = $conn->prepare("INSERT INTO tbl_account (name, email, password, is_admin) VALUES (?, ?, ?, 2)");
    $stmt2->bind_param("sss", $name, $email, $password);
    $stmt2->execute();

    $conn->commit();

    if ($return_class_id > 0) {
        header("Location: ../Pages/Dashboards.php?class_id=" . $return_class_id);
    } else {
        header("Location: ../Pages/Dashboards.php");
    }
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}
?>
