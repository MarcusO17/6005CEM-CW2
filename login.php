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
    define("OTP_EXPIRY",30);
    //learn from w3schools.com
    //Unset all the server side variables

    session_start();

    $_SESSION["user"]="";
    $_SESSION["usertype"]="";
    
    // Set the new timezone
    date_default_timezone_set('Asia/Kolkata');
    $date = date('Y-m-d');

    $_SESSION["date"]=$date;
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

        $email=$_POST['useremail'];
        $password=$_POST['userpassword'];
        
        $error='<label for="promter" class="form-label"></label>';

        $result= $database->query("select * from webuser where email='$email'");
        if($result->num_rows==1){
            $utype=$result->fetch_assoc()['usertype'];
            if ($utype=='p'){
                //TODO
                $checker = $database->query("select * from patient where pemail='$email' and ppassword='$password'");
                if ($checker->num_rows==1){

                    $OTPSettings = getOTP();
                    
                    $_SESSION['otp'] = $OTPSettings['otp']; 
                    $_SESSION['expiryTime'] = $OTPSettings['expiryTime'];
                    $_SESSION['user'] = $email;

                    sendOTP($email);
                }else{
                    $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
                }

            }elseif($utype=='a'){
                //TODO
                $checker = $database->query("select * from admin where aemail='$email' and apassword='$password'");
                if ($checker->num_rows==1){


                    //   Admin dashbord
                    $_SESSION['user']=$email;
                    $_SESSION['usertype']='a';
                    
                    header('location: admin/index.php');

                }else{
                    $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
                }


            }elseif($utype=='d'){
                //TODO
                $checker = $database->query("select * from doctor where docemail='$email' and docpassword='$password'");
                if ($checker->num_rows==1){

                    $OTPSettings = getOTP();
                    
                    $_SESSION['otp'] = $OTPSettings['otp']; 
                    $_SESSION['expiryTime'] = $OTPSettings['expiryTime'];
                    $_SESSION['user'] = $email;
                    
                    sendOTP($email);

                }else{
                    $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
                }

            }
            
        }else{
            $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">We cant found any acount for this email.</label>';
        }






        
    }else{
        $error='<label for="promter" class="form-label">&nbsp;</label>';
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
</body>
</html>