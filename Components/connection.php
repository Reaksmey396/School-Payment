<?php
    try{
        $conn = mysqli_connect('localhost', 'root', '', 'db_pp_s1');
    }
    catch(Exception $e){
        echo $e->getMessage();
    }