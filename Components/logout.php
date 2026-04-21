<?php
        // For Session Code

    session_start();
    session_destroy();
    header('location:../Pages/Home.php');
?>