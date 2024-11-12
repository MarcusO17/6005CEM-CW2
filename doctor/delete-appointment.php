<?php

    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
            header("location: ../login.php");
        }

    }else{
        header("location: ../login.php");
    }
    
    
    if($_GET){
        //import database
        include("../connection.php");
         // Sanitize and validate the id parameter from GET
        $id = isset($_GET["id"]) ? filter_var($_GET["id"], FILTER_VALIDATE_INT) : null;
        
        $sql= $database->query("delete from appointment where appoid='$id';");

        header("location: appointment.php");
    }


?>