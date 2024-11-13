<?php

    session_start();

    include('../session_handler.php');

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='p'){
            header("location: ../login.php");
        }else{
            $useremail=$_SESSION["user"];
        }

    }else{
        header("location: ../login.php");
    }

    include('../csrf_helper.php');
    
    //import database
    include("../connection.php");
    $sqlmain= "select * from patient where pemail=?";
    $stmt = $database->prepare($sqlmain);
    $stmt->bind_param("s",$useremail);
    $stmt->execute();
    $userrow = $stmt->get_result();
    $userfetch=$userrow->fetch_assoc();
    $userid = htmlspecialchars($userfetch["pid"], ENT_QUOTES, 'UTF-8');
    $username = htmlspecialchars($userfetch["pname"], ENT_QUOTES, 'UTF-8');
    
    if($_POST){

        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            header('Location: ../login.php?csrf=true');
            exit();
        }
        //import database
        include("../connection.php");
        $id=filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $sqlmain= "select * from patient where pid=?";
        $stmt = $database->prepare($sqlmain);
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $result001 = $stmt->get_result();
        $email=($result001->fetch_assoc())["pemail"];
        $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

        $sqlmain= "delete from webuser where email=?;";
        $stmt = $database->prepare($sqlmain);
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $result = $stmt->get_result();


        $sqlmain= "delete from patient where pemail=?";
        $stmt = $database->prepare($sqlmain);
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $result = $stmt->get_result();

        //print_r($email);
        header("location: ../logout.php");
    }


?>