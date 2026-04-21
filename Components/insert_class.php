<?php
require 'connection.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_name = $_POST['class_name'];
    $faculty    = $_POST['faculty'];
    $department = $_POST['department'];
    $year       = $_POST['year'];
    $price      = $_POST['price']; // ចាប់យកតម្លៃ Price fee ពី Form

    // ឆែកមើលព័ត៌មានចាំបាច់ (បន្ថែម price)
    if (empty($class_name) || empty($faculty) || empty($department) || empty($year) || empty($price)) {
        echo "<script>
                alert('សូមបំពេញព័ត៌មានឱ្យបានគ្រប់គ្រាន់!');
                window.location.href='../Pages/Dashboards.php';
              </script>";
        exit();
    }

    try {
        // ១. ឆែកមើលឈ្មោះថ្នាក់ដែលមានស្រាប់
        $check_sql = $conn->prepare("SELECT id FROM tbl_class WHERE class_name = ?");
        $check_sql->bind_param("s", $class_name);
        $check_sql->execute();
        $result = $check_sql->get_result();

        if ($result->num_rows > 0) {
            echo "<script>
                    alert('ឈ្មោះថ្នាក់ \"$class_name\" នេះមានរួចហើយ!');
                    window.history.back();
                  </script>";
            exit();
        }

        // ២. ចាប់ផ្តើមបញ្ចូលទៅក្នុង tbl_class
        $stmt = $conn->prepare("INSERT INTO tbl_class (faculty, department, year, class_name) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $faculty, $department, $year, $class_name);
        
        if ($stmt->execute()) {
            // ៣. ចាប់យក ID របស់ Class ដែលទើបបញ្ចូលមិញ
            $last_class_id = $conn->insert_id;

            // ៤. បញ្ចូលទៅក្នុង tbl_fee (ប្រើ class_id, total_fee, និង department)
            $stmt_fee = $conn->prepare("INSERT INTO tbl_fee (class_id, total_fee, department) VALUES (?, ?, ?)");
            $stmt_fee->bind_param("ids", $last_class_id, $price, $department);
            $stmt_fee->execute();

            echo "<script>
                    alert('បញ្ចូលថ្នាក់ និងតម្លៃសិក្សាជោគជ័យ!');
                    window.location.href='../Pages/Dashboards.php';
                  </script>";
            exit();
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>