<?php
require 'connection.php';
session_start();

if (isset($_POST['login'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $select = "SELECT * FROM tbl_account WHERE email='$email'";
    $ex = mysqli_query($conn, $select);

    if (mysqli_num_rows($ex) > 0) {

        $row = mysqli_fetch_assoc($ex);
        if ($password == $row['password'] && $name == $row['name']) {
            // save session
            $_SESSION['name'] = $row['name'];
            $_SESSION['id'] = $row['id'];
            $_SESSION['stu_id'] = $row['stu_id'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['gender'] = $row['gender'];
            $_SESSION['is_admin'] = $row['is_admin'];
            $_SESSION['class_id'] = $row['class_id'];

            if ($row['is_admin'] == 1) {
                header('Location: ../Pages/dashboards.php');
                exit();
            } elseif ($row['is_admin'] == 2) {

                // បង្ខំឱ្យវាទៅកាន់ទំព័រ Student ដោយប្រើ JS
                echo "  <script>
                            alert('Login ជោគជ័យក្នុងនាមជា Student!');
                            window.location.href='../Pages/Stu_dashoard.php';
                        </script>
                    ";
                exit();
            } elseif ($row['is_admin'] == 0) {
                header('Location:../Pages/Home.php');
                exit();
            } else {
                header('Location:Login.php');
                exit();
            }
        } else {
            echo "<script>
                alert('Your input was not correctly!');
                window.location.href='Login.php';
            </script>";
            exit();
        }
    } else {
        echo "<script>
            alert('User not found!');
            window.location.href='Login.php';
        </script>";
        exit();
    }
}
