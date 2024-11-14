<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/admin.css">

    <title>Doctor</title>
    <style>
        .popup {
            animation: transitionIn-Y-bottom 0.5s;
        }
    </style>
</head>

<body>
    <?php

    //learn from w3schools.com

    session_start();

    include('../session_handler.php');

    if (isset($_SESSION["user"])) {
        if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'a') {
            header("location: ../login.php");
        }
    } else {
        header("location: ../login.php");
    }

    include('../csrf_helper.php');

    //import database
    include("../connection.php");

    // import EncryptionUtil
    require "../utils/encryption-util.php";

    use function Utils\encrypt;


    if ($_POST) {

        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            header('Location: ../login.php?csrf=true');
            exit();
        }

        //print_r($_POST);
        $result = $database->query("select * from webuser");
        $name = $_POST['name'];
        $nic = $_POST['nic'];
        $spec = $_POST['spec'];
        $email = $_POST['email'];
        $tele = $_POST['Tele'];
        $password = $_POST['password'];
        $cpassword = $_POST['cpassword'];

        //ReGex Policy (1 digit,lowercase,uppercase and 8-64 length, any character non spaces.)
        $passwordPolicy = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{8,64}$/";

        if (preg_match($passwordPolicy, $password)) {
            if ($password == $cpassword) {
                $error = '3';
                $result = $database->query("select * from webuser where email='$email';");
                if ($result->num_rows == 1) {
                    $error = '1';
                } else {
                    //Password Hashing
                    $hashedpassword = password_hash($password, PASSWORD_ARGON2ID, ['memory_cost' => 19456, 'time_cost' => 2, 'threads' => 1]);
                    // Encrypt sensitive data
                    $encrypted_nic = encrypt($nic);

                    $sql1 = "insert into doctor(docemail,docname,docpassword,docnic,doctel,specialties) values('$email','$name','$hashedpassword','$encrypted_nic','$tele',$spec);";
                    $sql2 = "insert into webuser values('$email','d',0,NULL,NULL);";
                    $database->query($sql1);
                    $database->query($sql2);

                    //echo $sql1;
                    //echo $sql2;
                    $error = '4';
                }
            } else {
                $error = '2';
            }
        } else {
            $error = '5';
        }
    } else {
        //header('location: signup.php');
        $error = '3';
    }


    header("location: doctors.php?action=add&error=" . $error);
    ?>



</body>

</html>