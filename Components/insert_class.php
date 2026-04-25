<?php
require 'connection.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_name = trim($_POST['class_name'] ?? '');
    $faculty_id = (int) ($_POST['faculty_id'] ?? 0);
    $department = trim($_POST['department'] ?? '');
    $year = (int) ($_POST['year'] ?? 0);

    if ($class_name === '' || $faculty_id <= 0 || $department === '' || $year <= 0) {
        echo "<script>
                alert('Please fill all required information!');
                window.location.href='../Pages/Dashboards.php';
              </script>";
        exit();
    }

    try {
        $check_sql = $conn->prepare("SELECT id FROM tbl_class WHERE class_name = ?");
        $check_sql->bind_param("s", $class_name);
        $check_sql->execute();
        $result = $check_sql->get_result();

        if ($result->num_rows > 0) {
            echo "<script>
                    alert('Class name already exists!');
                    window.history.back();
                  </script>";
            exit();
        }

        $faculty_lookup = $conn->prepare("SELECT faculty_name FROM tbl_faculty WHERE id = ? LIMIT 1");
        $faculty_lookup->bind_param("i", $faculty_id);
        $faculty_lookup->execute();
        $faculty_row = $faculty_lookup->get_result()->fetch_assoc();

        if (!$faculty_row) {
            echo "<script>
                    alert('Faculty not found!');
                    window.history.back();
                  </script>";
            exit();
        }

        $faculty = $faculty_row['faculty_name'];

        $department_lookup = $conn->prepare("
            SELECT id
            FROM tbl_fee
            WHERE faculty_id = ? AND department = ? AND total_fee > 0
            LIMIT 1
        ");
        $department_lookup->bind_param("is", $faculty_id, $department);
        $department_lookup->execute();
        $department_row = $department_lookup->get_result()->fetch_assoc();

        if (!$department_row) {
            echo "<script>
                    alert('This department does not belong to the selected faculty or has no fee!');
                    window.history.back();
                  </script>";
            exit();
        }

        $fee_lookup = $conn->prepare("
            SELECT id
            FROM tbl_fee
            WHERE faculty_id = ? AND department = ? AND total_fee > 0
            ORDER BY id DESC
            LIMIT 1
        ");
        $fee_lookup->bind_param("is", $faculty_id, $department);
        $fee_lookup->execute();
        $fee_row = $fee_lookup->get_result()->fetch_assoc();

        if (!$fee_row) {
            echo "<script>
                    alert('No fee found for this department!');
                    window.history.back();
                  </script>";
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO tbl_class (faculty, department, year, class_name, faculty_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisi", $faculty, $department, $year, $class_name, $faculty_id);

        if ($stmt->execute()) {
            echo "<script>
                    alert('Class inserted successfully!');
                    window.location.href='../Pages/Dashboards.php';
                  </script>";
            exit();
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
