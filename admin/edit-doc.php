
    <?php
    
    
    include('../session_handler.php');
    //import database
    include("../connection.php");

    include('../csrf_helper.php');    // import EncryptionUtil
    require "../utils/encryption-util.php";
    use function Utils\encrypt;

    if($_POST){
        
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            header('Location: ../login.php?csrf=true');
            exit();
        }

        //print_r($_POST);
        $result= $database->query("select * from webuser");
        $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
        $nic = filter_var(trim($_POST['nic']), FILTER_SANITIZE_STRING);
        $oldemail = filter_var(trim($_POST["oldemail"]), FILTER_SANITIZE_EMAIL);
        $spec = filter_var(trim($_POST['spec']), FILTER_SANITIZE_NUMBER_INT);
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $tele = filter_var(trim($_POST['Tele']), FILTER_SANITIZE_NUMBER_INT);
        $password=$_POST['password'];
        $cpassword=$_POST['cpassword'];
        $id=$_POST['id00'];

        // Validation policies
    $namePolicy = "/^[a-zA-Z\s'-]+$/"; // Letters, spaces, hyphens, apostrophes
    $passwordPolicy = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{8,64}$/"; // 1 digit, lowercase, uppercase, special character, 8-64 length

    // Validate name
    if (!preg_match($namePolicy, $name)) {
        $error = '6'; // Error code for invalid name
    }
    // Validate password policy
    elseif (!preg_match($passwordPolicy, $password)) {
        $error = '5'; // Error code for invalid password policy
    }
    // Check if passwords match
    elseif ($password !== $cpassword) {
        $error = '2'; // Error code for passwords not matching
    } else {
        // Check if the new email already exists for another doctor
        $sqlmain = "SELECT docid FROM doctor WHERE docemail = ? AND docid != ?";
        $stmt = $database->prepare($sqlmain);
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = '1'; // Email already exists for another doctor
        } else {
            // Encrypt sensitive data
            $encrypted_nic = encrypt($nic);

            // Hash the password with Argon2ID
            $hashedpassword = password_hash($password, PASSWORD_ARGON2ID, ['memory_cost' => 19456, 'time_cost' => 2, 'threads' => 1]);

            // Update doctor data
            $sql = "UPDATE doctor SET docname = ?, docemail = ?, docnic = ?, doctel = ?, specialties = ?, docpassword = ? WHERE docid = ?";
            $stmt = $database->prepare($sql);
            $stmt->bind_param("ssssssi", $name, $email, $encrypted_nic, $tele, $spec, $hashedpassword, $id);
            $stmt->execute();

            // Update email in the webuser table
            $sql = "UPDATE webuser SET email = ? WHERE email = ?";
            $stmt = $database->prepare($sql);
            $stmt->bind_param("ss", $email, $oldemail);
            $stmt->execute();

            $error = '4'; // Success code
        }
    }
} else {
    $error = '3'; // General error code if no POST data is received
}
    

    header("location: doctors.php?action=edit&error=".urlencode($error)."&id=".urlencode($id));
    ?>
    
   

</body>
</html>