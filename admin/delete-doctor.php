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

    include('../csrf_helper.php');
    
    if($_POST){
        
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {

            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time()-86400, '/');
            }
    
            session_unset();
            session_destroy();
            
            header('Location: ../login.php?csrf=true');
            exit();
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