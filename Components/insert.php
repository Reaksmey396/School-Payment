<?php
    require 'connection.php';
    if(isset($_POST['register'])){
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        // ២. ឆែកមើលថា តើមាន Email នេះក្នុង Database ហើយឬនៅ?
    $check_email = "SELECT * FROM tbl_account WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $check_email);

    if (mysqli_num_rows($result) > 0) {
        // បើរកឃើញមាន Email នេះហើយ បង្ហាញ Alert ហើយត្រឡប់ទៅទំព័រ Register វិញ
        echo "<script>
                alert('Email នេះត្រូវបានប្រើប្រាស់រួចហើយ! សូមប្រើ Email ផ្សេង។');
                window.history.back();
              </script>";
        exit();
    } else {
        // ៣. បើមិនទាន់មាន Email នេះទេ ទើបអនុញ្ញាតឱ្យចុះឈ្មោះ
        $insert_query = "INSERT INTO tbl_account (name, email, password, is_admin) 
                         VALUES ('$name', '$email', '$password', 0)";
        
        if (mysqli_query($conn, $insert_query)) {
            echo "<script>
                    alert('ចុះឈ្មោះជោគជ័យ!');
                    window.location.href = '../Components/Login.php';
                  </script>";
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
    }