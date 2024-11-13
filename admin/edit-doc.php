
    <?php
    
    

    //import database
    include("../connection.php");



    if($_POST){
        //print_r($_POST);
        $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
        $nic = htmlspecialchars(trim($_POST['nic']), ENT_QUOTES, 'UTF-8');
        $oldemail = filter_input(INPUT_POST, 'oldemail', FILTER_SANITIZE_EMAIL);
        $spec = filter_input(INPUT_POST, 'spec', FILTER_VALIDATE_INT); // Assuming spec is an integer ID
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $tele = htmlspecialchars(trim($_POST['Tele']), ENT_QUOTES, 'UTF-8');
        $password = $_POST['password'];
        $cpassword = $_POST['cpassword'];
        $id = filter_input(INPUT_POST, 'id00', FILTER_VALIDATE_INT); // Validate ID as integer
        
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

                //$sql1="insert into doctor(docemail,docname,docpassword,docnic,doctel,specialties) values('$email','$name','$password','$nic','$tele',$spec);";
                $sql1="update doctor set docemail='$email',docname='$name',docpassword='$password',docnic='$nic',doctel='$tele',specialties=$spec where docid=$id ;";
                $database->query($sql1);
                
                $sql1="update webuser set email='$email' where email='$oldemail' ;";
                $database->query($sql1);
                //echo $sql1;
                //echo $sql2;
                $error= '4';
                
            }
            
        }else{
            $error='2';
        }
    
    
        
        
    }else{
        //header('location: signup.php');
        $error='3';
    }
    

    header("location: doctors.php?action=edit&error=". urlencode($error) ."&id=".urlencode($id));
    ?>
    
   

</body>
</html>