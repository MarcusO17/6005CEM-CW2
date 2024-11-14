
    <?php
    
    include('../session_handler.php');

    //import database
    include("../connection.php");

    // import EncryptionUtil
    require "../utils/encryption-util.php";
    use function Utils\encrypt;
    include('../csrf_helper.php');

    if($_POST){

        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {

            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time()-86400, '/');
            }
    
            session_unset();
            session_destroy();
            
            header('Location: ../login.php?csrf=true');
            exit();
        }
        
        //print_r($_POST);
        $result= $database->query("select * from webuser");
        $name=$_POST['name'];
        $oldemail=$_POST["oldemail"];
        $nic=$_POST['nic'];
        $spec=$_POST['spec'];
        $email=$_POST['email'];
        $tele=$_POST['Tele'];
        $password=$_POST['password'];
        $cpassword=$_POST['cpassword'];
        $id=$_POST['id00'];
        
        //ReGex Policy (1 digit,lowercase,uppercase and 8-64 length, any character non spaces.)
        $passwordPolicy = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{8,64}$/";

        if (preg_match($passwordPolicy, $password)){
            if ($password==$cpassword){
                $error='3';
                $result= $database->query("select doctor.docid from doctor inner join webuser on doctor.docemail=webuser.email where webuser.email='$email';");
                //$resultqq= $database->query("select * from doctor where docid='$id';");
                if($result->num_rows==1){
                    $id2=$result->fetch_assoc()["docid"];
                }else{
                    $id2=$id;
                }
                
                echo $id2."jdfjdfdh";
                if($id2!=$id){
                    $error='1';
                    //$resultqq1= $database->query("select * from doctor where docemail='$email';");
                    //$did= $resultqq1->fetch_assoc()["docid"];
                    //if($resultqq1->num_rows==1){
                        
                }else{
                    //Password Hashing
                    $hashedpassword = password_hash($password, PASSWORD_ARGON2ID, ['memory_cost' => 19456, 'time_cost' => 2, 'threads' => 1]);
                    // Encrypt sensitive data
                    $encrypted_nic = encrypt($nic);

                    //$sql1="insert into doctor(docemail,docname,docpassword,docnic,doctel,specialties) values('$email','$name','$password','$nic','$tele',$spec);";
                    $sql1="update doctor set docemail='$email',docname='$name',docpassword='$hashedpassword',docnic='$encrypted_nic',doctel='$tele',specialties=$spec where docid=$id ;";
                    $database->query($sql1);

                    $sql1="update webuser set email='$email' where email='$oldemail' ;";
                    $database->query($sql1);

                    echo $sql1;
                    //echo $sql2;
                    $error= '4';
                    
                }
                
            }else{
                $error='2';
            }
        
        }else{
            $error='5';
        }
    
             
    }else{
        //header('location: signup.php');
        $error='3';
    }
    

    header("location: settings.php?action=edit&error=".$error."&id=".$id);
    ?>
    
   

</body>
</html>