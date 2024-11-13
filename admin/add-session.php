<?php

    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
            header("location: ../login.php");
        }

    }else{
        header("location: ../login.php");
    }
    
    
    if($_POST){
        //import database
        include("../connection.php");
        // Sanitize inputs
        $title = htmlspecialchars(trim($_POST["title"]), ENT_QUOTES, 'UTF-8');
        $docid = filter_input(INPUT_POST, 'docid', FILTER_VALIDATE_INT);
        $nop = filter_input(INPUT_POST, 'nop', FILTER_VALIDATE_INT);
        $date = htmlspecialchars(trim($_POST["date"]), ENT_QUOTES, 'UTF-8');
        $time = htmlspecialchars(trim($_POST["time"]), ENT_QUOTES, 'UTF-8');
        $sql="insert into schedule (docid,title,scheduledate,scheduletime,nop) values ($docid,'$title','$date','$time',$nop);";
        $result= $database->query($sql);
        header("location: schedule.php?action=session-added&title=" . urlencode($title));
        
    }


?>