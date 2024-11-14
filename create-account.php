<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/signup.css">
        
    <title>Create Account</title>
    <style>
        .container{
            animation: transitionIn-X 0.5s;
        }
    </style>
</head>
<body>
<?php

//learn from w3schools.com
//Unset all the server side variables
session_start();

$_SESSION["user"]="";
$_SESSION["usertype"]="";

// Set the new timezone
date_default_timezone_set('Asia/Kolkata');
$date = date('Y-m-d');

$_SESSION["date"]=$date;


//import database
include("connection.php");
include("csrf_helper.php");
// import EncryptionUtil
require "utils/encryption-util.php";
use function Utils\encrypt;


if($_POST){

    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        header('Location: ../login.php?csrf=true');
        exit();
    }

    $result= $database->query("select * from webuser");

    $fname=$_SESSION['personal']['fname'];
    $lname=$_SESSION['personal']['lname'];
    $name=$fname." ".$lname;
    $address=$_SESSION['personal']['address'];
    $nic=$_SESSION['personal']['nic'];
    $dob=$_SESSION['personal']['dob'];
    $email = filter_var(trim($_POST['newemail']), FILTER_SANITIZE_EMAIL);
    $tele = filter_var(trim($_POST['tele']), FILTER_SANITIZE_STRING);
    $newpassword=$_POST['newpassword'];
    $cpassword=$_POST['cpassword'];

    //ReGex Policy (1 digit,lowercase,uppercase and 8-64 length, any character non spaces.)
    $passwordPolicy = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{8,64}$/";

    if (!preg_match($passwordPolicy, $newpassword)) {
        // Error Message for failed policy
        $error = '<label for="password" style="color:rgb(255, 62, 62);text-align:center;" class="form-label">Password must be at least 8 characters and less than 64 characters, include uppercase, lowercase, a number, and a special character.</label>';
    } elseif ($newpassword !== $cpassword) {
        // Error Messge for confirm mismatch
        $error = '<label for="password" style="color:rgb(255, 62, 62);text-align:center;" class="form-label">Password confirmation does not match. Please re-enter the passwords.</label>';
    } else { 
        $sqlmain= "select * from webuser where email=?;";
        $stmt = $database->prepare($sqlmain);
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Encrypt sensitive data
        $encrypted_nic = encrypt($nic);

        if ($result->num_rows==1) {
            $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Already have an account for this Email address.</label>';
        }else{
            //TODO
            //Password Hashing
            $hashedpassword = password_hash($newpassword, PASSWORD_ARGON2ID, ['memory_cost' => 19456, 'time_cost' => 2, 'threads' => 1]);
            // Insert data into the patient table
            $stmt = $database->prepare("INSERT INTO patient (pemail, pname, ppassword, paddress, pnic, pdob, ptel) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $email, $name, $hashedpassword, $address, $encrypted_nic, $dob, $tele);
            $stmt->execute();

            // Insert data into the webuser table
            $stmt = $database->prepare("INSERT INTO webuser (email, usertype, attempts, last_recorded_attempt, end_lockout) VALUES (?, 'p', 0, NULL, NULL)");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            //print_r("insert into patient values($pid,'$email','$fname','$lname','$newpassword','$address','$nic','$dob','$tele');");
            $_SESSION["user"]=$email;
            $_SESSION["usertype"]="p";
            $_SESSION["username"]=$fname;

            header('Location: patient/index.php');
            $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;"></label>';
        }
    }
    
}else{
    //header('location: signup.php');
    $error='<label for="promter" class="form-label"></label>';
}

?>

    <center>
        <div class="container">
            <form action="" method="POST" >
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <table border="0" style="width: 69%;">
                    <!-- Header -->
                    <tr>
                        <td colspan="2">
                            <p class="header-text">Let's Get Started</p>
                            <p class="sub-text">Create Your User Account.</p>
                        </td>
                    </tr>

                    <!-- Input Fields -->
                    <tr>
                        
                <td class="label-td" colspan="2">
                            <label for="newemail" class="form-label">Email:</label>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-td" colspan="2">
                            <input type="email" name="newemail" class="input-text" placeholder="Email Address" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-td" colspan="2">
                            <label for="tele" class="form-label">Mobile Number:</label>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-td" colspan="2">
                            <input type="tel" name="tele" class="input-text"  placeholder="ex: 0712345678" pattern="[0]{1}[0-9]{9}" >
                        </td>
                    </tr>
                    <tr>
                        <td class="label-td" colspan="2">
                            <label for="newpassword" class="form-label">Password:</label>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-td" colspan="2">
                            <input type="password" name="newpassword" class="input-text" placeholder="New Password" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-td" colspan="2">
                            <label for="cpassword" class="form-label">Confirm Password:</label>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-td" colspan="2">
                            <input type="password" name="cpassword" class="input-text" placeholder="Confirm Password" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="label-td" colspan="2">
                            <input type="checkbox" id="cbxPrivacyPolicy" name="privacy-policy">
                            <label for="privacy-policy">
                                <small>
                                    I have read and accept the 
                                    <a href="privacy-policy.html" class="hover-link2" target="_blank" rel="noopener noreferrer">Privacy Policy</a>
                                </small>
                            </label>
                        </td>
                    </tr>
            
                    <!-- Error Message -->
                    <tr>
                        <td colspan="2">
                            <?php echo $error ?>
                        </td>
                    </tr>
                    
                    <!-- Action Buttons -->
                    <tr>
                        <td>
                            <input type="reset" value="Reset" class="action-btn btn-primary-soft btn" >
                        </td>
                        <td>
                            <input type="submit" value="Sign Up" id="btnSignUp" class="action-btn btn-primary btn">
                        </td>
                    </tr>

                    <!-- Redirect to Login -->
                    <tr>
                        <td colspan="2">
                            <br>
                            <label for="" class="sub-text" style="font-weight: 280; margin-right: 1rem;">Already have an account&#63; </label>
                            <a href="login.php" class="hover-link1">Login</a>
                            <br><br><br>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </center>
    
    <script type="text/javascript">
        var cbxPrivacyPolicy = document.getElementById("cbxPrivacyPolicy");
        var btnSignUp = document.getElementById("btnSignUp");
        
        cbxPrivacyPolicy.checked = false;
        btnSignUp.disabled = true;
        btnSignUp.style.opacity = 0.4;
        btnSignUp.style.cursor = "default";

        cbxPrivacyPolicy.addEventListener("change", function(event) {
            // Toggle Sign Up button based on privacy policy checkbox input
            if (event.currentTarget.checked) {
                btnSignUp.disabled = false;
                btnSignUp.style.opacity = 1;
                btnSignUp.style.cursor = "pointer";
            } else {
                btnSignUp.disabled = true;
                btnSignUp.style.opacity = 0.4;
                btnSignUp.style.cursor = "default";
            }
        })
    </script>
</body>
</html>