<?php
session_start();
require 'connection.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['is_admin']) || !in_array((int) $_SESSION['is_admin'], [1, 3], true)) {
    header('Location: Login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['delete_action'], $_POST['delete_id'])) {
    header('Location: ../Pages/Dashboards.php?delete_error=invalid');
    exit();
}

$deleteAction = $_POST['delete_action'];
$deleteId = (int) $_POST['delete_id'];

if ($deleteId <= 0 || !in_array($deleteAction, ['student', 'class'], true)) {
    header('Location: ../Pages/Dashboards.php?delete_error=invalid');
    exit();
}

function placeholders(array $values): string
{
    return implode(',', array_fill(0, count($values), '?'));
}

function bindIntList(mysqli_stmt $stmt, array $values): void
{
    $types = str_repeat('i', count($values));
    $params = [$types];

    foreach ($values as $index => $value) {
        $values[$index] = (int) $value;
        $params[] = &$values[$index];
    }

    call_user_func_array([$stmt, 'bind_param'], $params);
}

function fetchStudentIdsForClass(mysqli $conn, int $classId): array
{
    $stmt = $conn->prepare("SELECT id FROM tbl_student WHERE class_id = ?");
    $stmt->bind_param("i", $classId);
    $stmt->execute();
    $result = $stmt->get_result();
    $ids = [];

    while ($row = $result->fetch_assoc()) {
        $ids[] = (int) $row['id'];
    }

    $stmt->close();
    return $ids;
}

function fetchPaymentIdsForStudents(mysqli $conn, array $studentIds): array
{
    if (empty($studentIds)) {
        return [];
    }

    $stmt = $conn->prepare("SELECT id FROM tbl_payment WHERE student_id IN (" . placeholders($studentIds) . ")");
    bindIntList($stmt, $studentIds);
    $stmt->execute();
    $result = $stmt->get_result();
    $ids = [];

    while ($row = $result->fetch_assoc()) {
        $ids[] = (int) $row['id'];
    }

    $stmt->close();
    return $ids;
}

function deleteReceiptsForPayments(mysqli $conn, array $paymentIds): void
{
    if (empty($paymentIds)) {
        return;
    }

    $stmt = $conn->prepare("DELETE FROM tbl_receipt WHERE payment_id IN (" . placeholders($paymentIds) . ")");
    bindIntList($stmt, $paymentIds);
    $stmt->execute();
    $stmt->close();
}

function deletePaymentsByIds(mysqli $conn, array $paymentIds): void
{
    if (empty($paymentIds)) {
        return;
    }

    $stmt = $conn->prepare("DELETE FROM tbl_payment WHERE id IN (" . placeholders($paymentIds) . ")");
    bindIntList($stmt, $paymentIds);
    $stmt->execute();
    $stmt->close();
}

function deleteStudentAccountsByIds(mysqli $conn, array $studentIds): void
{
    if (empty($studentIds)) {
        return;
    }

    $stmt = $conn->prepare("
        DELETE a
        FROM tbl_account a
        INNER JOIN tbl_student s ON a.email = s.email
        WHERE s.id IN (" . placeholders($studentIds) . ") AND a.is_admin = 2
    ");
    bindIntList($stmt, $studentIds);
    $stmt->execute();
    $stmt->close();
}

function deleteStudentsByIds(mysqli $conn, array $studentIds): void
{
    if (empty($studentIds)) {
        return;
    }

    $stmt = $conn->prepare("DELETE FROM tbl_student WHERE id IN (" . placeholders($studentIds) . ")");
    bindIntList($stmt, $studentIds);
    $stmt->execute();
    $stmt->close();
}

function deleteReceiptsForClass(mysqli $conn, int $classId): void
{
    $stmt = $conn->prepare("
        DELETE r
        FROM tbl_receipt r
        INNER JOIN tbl_payment p ON r.payment_id = p.id
        INNER JOIN tbl_student s ON p.student_id = s.id
        WHERE s.class_id = ?
    ");
    $stmt->bind_param("i", $classId);
    $stmt->execute();
    $stmt->close();
}

function deletePaymentsForClass(mysqli $conn, int $classId): void
{
    $stmt = $conn->prepare("
        DELETE p
        FROM tbl_payment p
        INNER JOIN tbl_student s ON p.student_id = s.id
        WHERE s.class_id = ?
    ");
    $stmt->bind_param("i", $classId);
    $stmt->execute();
    $stmt->close();
}

function deleteStudentAccountsForClass(mysqli $conn, int $classId): void
{
    $stmt = $conn->prepare("
        DELETE a
        FROM tbl_account a
        INNER JOIN tbl_student s ON a.email = s.email
        WHERE s.class_id = ? AND a.is_admin = 2
    ");
    $stmt->bind_param("i", $classId);
    $stmt->execute();
    $stmt->close();
}

function deleteStudentsForClass(mysqli $conn, int $classId): void
{
    $stmt = $conn->prepare("DELETE FROM tbl_student WHERE class_id = ?");
    $stmt->bind_param("i", $classId);
    $stmt->execute();
    $stmt->close();
}

function deleteStudentById(mysqli $conn, int $studentId): void
{
    $studentIds = [$studentId];

    $existsStmt = $conn->prepare("SELECT id FROM tbl_student WHERE id = ? LIMIT 1");
    $existsStmt->bind_param("i", $studentId);
    $existsStmt->execute();
    $student = $existsStmt->get_result()->fetch_assoc();
    $existsStmt->close();

    if (!$student) {
        throw new RuntimeException('Student not found.');
    }

    $paymentIds = fetchPaymentIdsForStudents($conn, $studentIds);

    deleteReceiptsForPayments($conn, $paymentIds);
    deletePaymentsByIds($conn, $paymentIds);
    deleteStudentAccountsByIds($conn, $studentIds);
    $conn->query("SET FOREIGN_KEY_CHECKS=0");
    deleteStudentsByIds($conn, $studentIds);
    $conn->query("SET FOREIGN_KEY_CHECKS=1");
}

function tableRowCount(mysqli $conn, string $tableName): int
{
    $allowedTables = ['tbl_faculty', 'tbl_fee'];
    if (!in_array($tableName, $allowedTables, true)) {
        throw new InvalidArgumentException('Invalid table count requested.');
    }

    $result = $conn->query("SELECT COUNT(*) AS cnt FROM " . $tableName);
    $row = $result ? $result->fetch_assoc() : null;

    return (int) ($row['cnt'] ?? 0);
}

function deleteClassById(mysqli $conn, int $classId): void
{
    $facultyCountBefore = tableRowCount($conn, 'tbl_faculty');
    $feeCountBefore = tableRowCount($conn, 'tbl_fee');

    $classStmt = $conn->prepare("SELECT id FROM tbl_class WHERE id = ? LIMIT 1");
    $classStmt->bind_param("i", $classId);
    $classStmt->execute();
    $class = $classStmt->get_result()->fetch_assoc();
    $classStmt->close();

    if (!$class) {
        throw new RuntimeException('Class not found.');
    }

    deleteReceiptsForClass($conn, $classId);
    deletePaymentsForClass($conn, $classId);
    deleteStudentAccountsForClass($conn, $classId);

    $conn->query("SET FOREIGN_KEY_CHECKS=0");
    deleteStudentsForClass($conn, $classId);

    $deleteClassStmt = $conn->prepare("DELETE FROM tbl_class WHERE id = ?");
    $deleteClassStmt->bind_param("i", $classId);
    $deleteClassStmt->execute();
    $deleteClassStmt->close();
    $conn->query("SET FOREIGN_KEY_CHECKS=1");

    if (
        tableRowCount($conn, 'tbl_faculty') !== $facultyCountBefore ||
        tableRowCount($conn, 'tbl_fee') !== $feeCountBefore
    ) {
        throw new RuntimeException('Class delete attempted to change faculty or department fee data.');
    }
}

try {
    $conn->begin_transaction();

    if ($deleteAction === 'student') {
        deleteStudentById($conn, $deleteId);
    } else {
        deleteClassById($conn, $deleteId);
    }

    $conn->commit();
    header('Location: ../Pages/Dashboards.php?deleted=' . urlencode($deleteAction));
    exit();
} catch (Throwable $error) {
    $conn->query("SET FOREIGN_KEY_CHECKS=1");
    $conn->rollback();
    error_log('Delete failed: ' . $error->getMessage());
    header('Location: ../Pages/Dashboards.php?delete_error=' . urlencode($error->getMessage()));
    exit();
}
