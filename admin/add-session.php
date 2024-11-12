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
        //import database
        include("../connection.php");
        $title=htmlspecialchars($_POST["title"]);
        $docid=htmlspecialchars($_POST["docid"]);
        $nop=htmlspecialchars($_POST["nop"]);
        $date=htmlspecialchars($_POST["date"]);
        $time=htmlspecialchars($_POST["time"]);
        $sql="insert into schedule (docid,title,scheduledate,scheduletime,nop) values ($docid,'$title','$date','$time',$nop);";
        $result= $database->query($sql);
        header("location: schedule.php?action=session-added&title=$title");
        
    }


?>