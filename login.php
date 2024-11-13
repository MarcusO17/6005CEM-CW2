<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/otp.css">
        
    <title>Login</title>

    
    
</head>
<body>
    <?php


    //Constants
    define('MAX_ATTEMPTS',3);
    define('LOCKOUT_DURATION','+60 seconds');
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    define("OTP_EXPIRY",30);
    //learn from w3schools.com
    //Unset all the server side variables

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);


    session_start();

    $_SESSION["user"]="";
    $_SESSION["usertype"]="";

    
    // Set the new timezone
    date_default_timezone_set('Asia/Kolkata');
    $date = date('Y-m-d');

    $_SESSION["date"]=$date;
    include("csrf_helper.php");
    
    if(isset($_SESSION["otp_error_message"])){
       echo" <input type='checkbox' id='popup-toggle'>
        <div class='overlay'>
            <div class='popup'>
                <a href='login.php' class='close'>&times;</a>
                <div class='error-message'>";
                        echo $_SESSION['otp_error_message'];
                        unset($_SESSION['otp_error_message']);
                echo'
                </div>
            </div>
        </div>';
    }

    //import database
    include("connection.php");


    if($_POST){
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            header('Location: ../login.php?csrf=true');
            exit();
        }

        $email=$_POST['useremail'];
        $password=$_POST['userpassword'];
        
        $error='<label for="promter" class="form-label"></label>';

        $result= $database->query("select * from webuser where email='$email'");
        if($result->num_rows==1){
            $row = $result->fetch_assoc();
            $locktime =$row["end_lockout"];
            if(!testAccountLock($locktime,$database,$email)){
                $utype = $row['usertype'];
                if ($utype=='p'){
                    //TODO
                    $checker = $database->query("select * from patient where pemail='$email'");
                    if ($checker->num_rows==1){
                        $hashedpassword = $checker->fetch_assoc()['ppassword'];
                        if(password_verify($password,$hashedpassword)){
                            $OTPSettings = getOTP();
                            
                            $_SESSION['otp'] = $OTPSettings['otp']; 
                            $_SESSION['expiryTime'] = $OTPSettings['expiryTime'];
                            $_SESSION['user'] = $email;

                            sendOTP($email);
                        }else{
                            recordFailedLogin($database,$email);
                            $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
                        }
                    }else{
                        recordFailedLogin($database,$email);
                        $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials111: Invalid email or password, You have ' . (3 - $row["attempts"]) . ' attempt(s) left. </label>';
                    }

                }elseif($utype=='a'){
                    //TODO
                    $checker = $database->query("select * from admin where aemail='$email' and apassword='$password'");
                    if ($checker->num_rows==1){


                    session_regenerate_id(true);

                    //   Admin dashbord
                    $_SESSION['user']=$email;
                    $_SESSION['usertype']='a';
                    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
                    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

                    include('session_handler.php');
                    
                    header('location: admin/index.php');

                        //   Admin dashbord
                        $_SESSION['user']=$email;
                        $_SESSION['usertype']='a';
                        
                        header('location: admin/index.php');

                    }else{
                        $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
                    }

                }elseif($utype=='d'){
                    //TODO
                    $checker = $database->query("select * from doctor where docemail='$email'");
                    if ($checker->num_rows==1){
                          $hashedpassword = $checker->fetch_assoc()['docpassword'];
                          if(password_verify($password,$hashedpassword)){

                            $OTPSettings = getOTP();
                    
                            $_SESSION['otp'] = $OTPSettings['otp']; 
                            $_SESSION['expiryTime'] = $OTPSettings['expiryTime'];
                            $_SESSION['user'] = $email;
                            
                            sendOTP($email);

                        }else{
                            recordFailedLogin($database,$email);
                            $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password, You have ' . (3 - $row["attempts"]) . '  attempt(s) left.</label>';
                          }
                    }else{
                        recordFailedLogin($database,$email);
                        $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password, You have ' . (3 - $row["attempts"]) . '  attempt(s) left. </label>';
                    }
                }
            }else{
                $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Your account is locked till '. $row['end_lockout'].'</label>';
            }
            
        }else{
            $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">We cant found any account for this email.</label>';
        }

    }else{
        $error='<label for="promter" class="form-label">&nbsp;</label>';
    }



    function recordFailedLogin($database,$email){

        $result= $database->query("select * from webuser where email='$email'");
        if ($result->num_rows== 1){
            $row = $result->fetch_assoc();
            if($row != NULL){
                $attempt = $row["attempts"];
                $lastfailedattempt = $row["last_recorded_attempt"];
                if ($lastfailedattempt != NULL){
                    $lastfailedattempt = strtotime($lastfailedattempt);
                }
                $failedAttempts = $attempt+1;
            }
          
            if($failedAttempts >= MAX_ATTEMPTS){
                $endDate= date('Y-m-d H:i:s', strtotime(LOCKOUT_DURATION, $lastfailedattempt));
                $sql1="UPDATE webuser SET end_lockout='$endDate' where email='$email';";
                $database->query($sql1);
            }else{
                $now = date('Y-m-d H:i:s');
                $sql1=  " UPDATE webuser SET attempts='$failedAttempts', last_recorded_attempt='$now' where email='$email';";
                $database->query($sql1);
            }
        } 
    
    }
    
    function testAccountLock($lockout,$database,$email) {
        if ($lockout == NULL) {
            return false;  
        }
        $now = time();
        $lockoutTime = strtotime($lockout);
    
        if ($lockoutTime <= $now) {
            resetAccountLock($database, $email);  // Reset attempts and lockout data
            return false;  
        }
        return true;  
    }
    function resetAccountLock($database,$email){
        $sql1=  " UPDATE webuser SET attempts=NULL, last_recorded_attempt=NULL, end_lockout=NULL where email='$email';";
        $database->query($sql1);
    }


    function sendMail($email,$otp){
        $data = [
            'Messages' => [
                [
                'From' => [
                    'Email' => getenv("SenderEmail"),
                    'Name' => "EDoc Services"
                ],
                'To' => [
                    [
                        'Email' => $email,
                        'Name' => ""
                    ]
                ],
                'Subject' => "Your OTP for Edoc Services",
                'HTMLPart' => "<h3>Dear User, Here is your OTP <b>$otp</b>, Your OTP expires in 30 seconds.</h3><br />"
                ]
            ]
        ];
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, "https://api.mailjet.com/v3.1/send");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_USERPWD, getenv("MJPublicKey") . ":" . getenv("MJSecretKey"));
        curl_exec($ch);
        curl_close ($ch);
        
    }

    function getOTP(){
        $otp = rand(100000,999999);
        $expiryTime = time() + OTP_EXPIRY;
        return ['otp' => $otp, 'expiryTime' => $expiryTime];
    }

    function sendOTP($email){
        sendMail($email,$_SESSION['otp']);

                        echo '<div id="popup1" class="overlay">
                                <div class="popup">
                                    <div class="popup-content">
                                        <div class="content-wrapper">
                                            <div class="abc">
                                                <h3 style="font-size: 18px; font-weight: 500; margin-bottom: 5px;">Enter OTP</h3>
                                                <p style="color: grey; font-size: 14px; margin-bottom: 20px;">Please enter the verification code sent to your email in <b>30 seconds.</b></p>
                                                <form action="verify_otp.php" method="POST" id="otpForm">
                                                    <div class="otp-input-group">
                                                        <input type="text" maxlength="1" class="input-text otp-input" name="otp[]" required />
                                                        <input type="text" maxlength="1" class="input-text otp-input" name="otp[]" required />
                                                        <input type="text" maxlength="1" class="input-text otp-input" name="otp[]" required />
                                                        <input type="text" maxlength="1" class="input-text otp-input" name="otp[]" required />
                                                        <input type="text" maxlength="1" class="input-text otp-input" name="otp[]" required />
                                                        <input type="text" maxlength="1" class="input-text otp-input" name="otp[]" required />
                                                        <input type="hidden" name="user_id" value="p">
                                                    </div>
                                                    <button type="submit" class="btn btn-primary" style="margin-top: 20px;margin-left: 120px">Verify OTP</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>';
}
    ?>





    <center>
    <div class="container">
        <table border="0" style="margin: 0;padding: 0;width: 60%;">
            <tr>
                <td>
                    <p class="header-text">Welcome Back!</p>
                </td>
            </tr>
        <div class="form-body">
            <tr>
                <td>
                    <p class="sub-text">Login with your details to continue</p>
                </td>
            </tr>
            <tr>
                <form action="" method="POST" >
                <td class="label-td">
                    <label for="useremail" class="form-label">Email: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td">
                    <input type="email" name="useremail" class="input-text" placeholder="Email Address" required>
                </td>
            </tr>
            <tr>
                <td class="label-td">
                    <label for="userpassword" class="form-label">Password: </label>
                </td>
            </tr>

            <tr>
                <td class="label-td">
                    <input type="Password" name="userpassword" class="input-text" placeholder="Password" required>
                </td>
            </tr>


            <tr>
                <td><br>
                <?php echo $error ?>
                </td>
            </tr>

            <tr>
                <td>
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">
                    <input type="submit" value="Login" class="login-btn btn-primary btn">
                </td>
            </tr>
        </div>
            <tr>
                <td>
                    <br>
                    <label for="" class="sub-text" style="font-weight: 280;">Don't have an account&#63; </label>
                    <a href="signup.php" class="hover-link1 non-style-link">Sign Up</a>
                    <br><br><br>
                </td>
            </tr>
                        
                        
    
                        
                    </form>
        </table>

    </div>
</center>

<script>
        <?php if (isset($_GET['expired']) && $_GET['expired'] == 'true'): ?>
            alert('Your session has expired. Please log in again.');
        <?php endif; ?>

        <?php if (isset($_GET['timeout']) && $_GET['timeout'] == 'true'): ?>
            alert('Your session has timed out due to inactivity. Please log in again.');
        <?php endif; ?>

        <?php if (isset($_GET['csrf']) && $_GET['csrf'] == 'true'): ?>
            alert('Your session terminated due to csrf. Please log in again.');
        <?php endif; ?>

        <?php
        if (isset($_GET['error'])) {
            if ($_GET['error'] == 'session_hijacked') {
                echo "alert('Potential session hijack detected. Please log in again.');";
            } 
        }
        ?>
    </script>

</body>
</html>