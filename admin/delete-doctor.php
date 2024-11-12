<?php

    session_start();

    include('../session_handler.php');

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
            header("location: ../login.php");
        }

    }else{
        header("location: ../login.php");
    }
    
    
    if($_POST){
        
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            die('CSRF token validation failed.');
        }

        //import database
        include("../connection.php");
        $id=$_POST["id"];
        $result001= $database->query("select * from doctor where docid=$id;");
        $email=($result001->fetch_assoc())["docemail"];
        $sql= $database->query("delete from webuser where email='$email';");
        $sql= $database->query("delete from doctor where docemail='$email';");
        //print_r($email);
        header("location: doctors.php");
    }


?>