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
            header('Location: ../login.php?csrf=true');
            exit();
        }

        //import database
        include("../connection.php");
         // Sanitize and validate the id parameter from GET
        $id = isset($_POST["id"]) ? filter_var($_POST["id"], FILTER_VALIDATE_INT) : null;
        
        $sql= $database->query("delete from appointment where appoid='$id';");

        header("location: appointment.php");
    }


?>