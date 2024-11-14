<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/signup.css">
        
    <title>Sign Up</title>
    
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

include("csrf_helper.php");

if($_POST){

    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        header('Location: ../login.php?csrf=true');
        exit();
    }

    // Sanitize and validate inputs
    $fname = filter_var(trim($_POST['fname']), FILTER_SANITIZE_STRING);
    $lname = filter_var(trim($_POST['lname']), FILTER_SANITIZE_STRING);
    $address = filter_var(trim($_POST['address']), FILTER_SANITIZE_STRING);
    $nic = filter_var(trim($_POST['nic']), FILTER_SANITIZE_STRING);
    $dob = filter_var(trim($_POST['dob']), filter: FILTER_SANITIZE_STRING);

    // allows uppercase, lowercase, hyphen, apostrophe, and space
    if (!preg_match("/^[a-zA-Z-' ]*$/", $fname) || !preg_match("/^[a-zA-Z-' ]*$/", $lname)) {
        $name_error = 'Invalid name format. Only letters, spaces, hyphens, and apostrophes are allowed.';
    }

    // Validate Date of Birth (DOB) - check if DOB is at least 18 years old
    $dob_date = new DateTime($dob);
    $today = new DateTime($date); // Today's date
    $age = $today->diff($dob_date)->y; // Get the age in years

    if ($age < 18) {
        $dob_error = 'You must be at least 18 years old to register.';
    }

    //validate address
    if (strlen($address) < 5 || strlen($address) > 255) {
        $address_error = 'Address must be between 5 and 255 characters.';
    } elseif (!preg_match("/^[a-zA-Z0-9\s,.-]*$/", $address)) {
        $address_error = 'Address contains invalid characters. Only letters, numbers, spaces, commas, periods, and hyphens are allowed.';
    }

    // Only proceed if there is no error
    if (!isset($name_error)&& !isset($dob_error)&& !isset($address_error)) {
        $_SESSION["personal"] = array(
            'fname' => htmlspecialchars($fname, ENT_QUOTES, 'UTF-8'),
            'lname' => htmlspecialchars($lname, ENT_QUOTES, 'UTF-8'),
            'address' => htmlspecialchars($address, ENT_QUOTES, 'UTF-8'),
            'nic' => htmlspecialchars($nic, ENT_QUOTES, 'UTF-8'),
            'dob' => htmlspecialchars($dob, ENT_QUOTES, 'UTF-8')
        );
    

    print_r($_SESSION["personal"]);
    header("location: create-account.php");
}}

?>


    <center>
    <div class="container">
        <table border="0">
            <tr>
                <td colspan="2">
                    <p class="header-text">Let's Get Started</p>
                    <p class="sub-text">Add Your Personal Details to Continue</p>
                </td>
            </tr>
            
            <tr>
                <form action="" method="POST" >
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">

                <td class="label-td" colspan="2">
                    <label for="name" class="form-label">Name: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td">
                    <input type="text" name="fname" class="input-text" placeholder="First Name" required>
                </td>
                <td class="label-td">
                    <input type="text" name="lname" class="input-text" placeholder="Last Name" required>
                </td>
            </tr>
              <!-- Name Error Message Display -->
              <tr>
                <td colspan="2" style="text-align: center; color: red; font-size: 12px;">
                    <?php echo isset($name_error) ? htmlspecialchars($name_error, ENT_QUOTES, 'UTF-8') : ''; ?>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="address" class="form-label">Address: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="text" name="address" class="input-text" placeholder="Address" required>
                </td>
            </tr>
            <!-- Address Error Message Display -->
            <tr>
                <td colspan="2" style="text-align: center; color: red; font-size: 12px;">
                    <?php echo isset($address_error) ? htmlspecialchars($address_error, ENT_QUOTES, 'UTF-8') : ''; ?>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="nic" class="form-label">NIC: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="text" name="nic" class="input-text" placeholder="NIC Number" maxlength="15" required>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="dob" class="form-label">Date of Birth: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="date" name="dob" class="input-text" required>
                </td>
            </tr>
            <!-- Date of Birth Error Message Display -->
            <tr>
                <td colspan="2" style="text-align: center; color: red; font-size: 12px;">
                    <?php echo isset($dob_error) ? htmlspecialchars($dob_error, ENT_QUOTES, 'UTF-8') : ''; ?>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                </td>
            </tr>

            <tr>
                <td>
                    <input type="reset" value="Reset" class="login-btn btn-primary-soft btn" >
                </td>
                <td>
                    <input type="submit" value="Next" class="login-btn btn-primary btn">
                </td>

            </tr>
            <tr>
                <td colspan="2">
                    <br>
                    <label for="" class="sub-text" style="font-weight: 280; margin-right: 1rem;">Already have an account&#63; </label>
                    <a href="login.php" class="hover-link1">Login</a>
                    <br><br><br>
                </td>
            </tr>

                    </form>
            </tr>
        </table>
    </div>
</center>
</body>
</html>