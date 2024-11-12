<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Doctors</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
</style>
</head>
<body>
    <?php

    //learn from w3schools.com

    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='p'){
            header("location: ../login.php");
        }else{
            $useremail=$_SESSION["user"];
        }

    }else{
        header("location: ../login.php");
    }
    

    //import database
    include("../connection.php");
    $userrow = $database->query("select * from patient where pemail='$useremail'");
    $userfetch=$userrow->fetch_assoc();
    $userid= $userfetch["pid"];
    $username=$userfetch["pname"];

    ?>
    <div class="container">
        <div class="menu">
            <table class="menu-container" border="0">
                <!-- User Profile Section -->
                <tr>
                    <td style="padding:10px" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="30%" style="padding-left:20px">
                                    <img src="../img/user.png" alt="Profile Picture" width="100%" style="border-radius:50%">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title"><?= substr($username, 0, 13) ?>..</p>
                                    <p class="profile-subtitle"><?= substr($useremail, 0, 22) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="../logout.php"><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- Menu Navigation -->
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-home">
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Home</p></a></div>
                    </td>
                </tr>

                <tr class="menu-row">
                    <td class="menu-btn menu-icon-doctor menu-active menu-icon-doctor-active">
                        <a href="doctors.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">All Doctors</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-session">
                        <a href="prescriptions.php" class="non-style-link-menu"><div><p class="menu-text">My Prescriptions</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-session">
                        <a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">Scheduled Sessions</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">My Bookings</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></a></div>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="dash-body">
            <table border="0" width="100%" style="margin-top:25px;">
                <tr>
                    <td width="13%">
                        <a href="doctors.php">
                            <button class="login-btn btn-primary-soft btn btn-icon-back" style="padding:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button>
                        </a>
                    </td>
                    <td>
                        <form action="" method="post" class="header-search">
                            <input type="search" name="search" class="input-text header-searchbar" placeholder="Search Doctor name or Email" list="doctors" required>
                            <datalist id="doctors">
                                <?php
                                $list11 = $database->query("SELECT docname, docemail FROM doctor");
                                while ($row00 = $list11->fetch_assoc()) {
                                    echo "<option value='" . htmlspecialchars($row00['docname'], ENT_QUOTES, 'UTF-8') . "'></option>";
                                    echo "<option value='" . htmlspecialchars($row00['docemail'], ENT_QUOTES, 'UTF-8') . "'></option>";
                                }
                                ?>
                            </datalist>
                            <input type="submit" value="Search" class="login-btn btn-primary btn" style="padding:10px 25px;">
                        </form>
                    </td>
                    <td width="15%">
                        <p style="font-size:14px;color:rgb(119,119,119);text-align:right;">Today's Date</p>
                        <p class="heading-sub12"><?= date('Y-m-d') ?></p>
                    </td>
                    <td width="10%">
                        <button class="btn-label" style="display:flex;justify-content:center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>
                </tr>

                <tr>
                    <td colspan="4" style="padding-top:10px;">
                        <p class="heading-main12" style="margin-left:45px;font-size:18px;color:rgb(49,49,49)">All Doctors (<?= $list11->num_rows ?>)</p>
                    </td>
                </tr>

                <?php
                $sqlmain = "SELECT * FROM doctor ORDER BY docid DESC";
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['search'])) {
                    $keyword = '%' . $database->real_escape_string($_POST['search']) . '%';
                    $sqlmain = "SELECT * FROM doctor WHERE docemail LIKE ? OR docname LIKE ?";
                    $stmt = $database->prepare($sqlmain);
                    $stmt->bind_param("ss", $keyword, $keyword);
                    $stmt->execute();
                    $result = $stmt->get_result();
                } else {
                    $result = $database->query($sqlmain);
                }
                ?>
                
                <tr>
                   <td colspan="4">
                       <center>
                        <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown" border="0">
                        <thead>
                        <tr>
                            <th class="table-headin">Doctor Name</th>
                            <th class="table-headin">Email</th>
                            <th class="table-headin">Specialties</th>
                            <th class="table-headin">Events</th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows == 0) {
                                echo '<tr>
                                        <td colspan="4">
                                            <center>
                                            <img src="../img/notfound.svg" width="25%">
                                            <p class="heading-main12" style="font-size:20px;color:rgb(49,49,49)">No doctors found!</p>
                                            <a class="non-style-link" href="doctors.php">
                                                <button class="login-btn btn-primary-soft btn">Show all Doctors</button>
                                            </a>
                                            </center>
                                        </td>
                                    </tr>';
                            } else {
                                while ($row = $result->fetch_assoc()) {
                                    $docid = htmlspecialchars($row["docid"], ENT_QUOTES, 'UTF-8');
                                    $name = htmlspecialchars($row["docname"], ENT_QUOTES, 'UTF-8');
                                    $email = htmlspecialchars($row["docemail"], ENT_QUOTES, 'UTF-8');
                                    $spe = htmlspecialchars($row["specialties"], ENT_QUOTES, 'UTF-8');

                                    $stmt = $database->prepare("SELECT sname FROM specialties WHERE id = ?");
                                    $stmt->bind_param("s", $spe);
                                    $stmt->execute();
                                    $spcil_name = htmlspecialchars($stmt->get_result()->fetch_assoc()["sname"], ENT_QUOTES, 'UTF-8');

                                    echo "<tr>
                                        <td>$name</td>
                                        <td>$email</td>
                                        <td>$spcil_name</td>
                                        <td>
                                            <div style='display:flex;justify-content:center;'>
                                                <a href='?action=view&id=" . urlencode($docid) . "' class='non-style-link'><button class='btn-primary-soft btn button-icon btn-view'>View</button></a>
                                                &nbsp;
                                                <a href='?action=session&id=" . urlencode($docid) . "&name=" . urlencode($name) . "' class='non-style-link'><button class='btn-primary-soft btn button-icon menu-icon-session-active '>Sessions</button></a>
                                            </div>
                                        </td>
                                    </tr>";
                                }
                            }
                            ?>
                        </tbody>
                        </table>
                        </div>
                        </center>
                   </td> 
                </tr>
            </table>
        </div>
    </div>
    <?php 
    if($_GET){
        
        $id = filter_var($_GET["id"], FILTER_VALIDATE_INT);
        $action = htmlspecialchars($_GET["action"], ENT_QUOTES, 'UTF-8');


        if ($action === 'drop' && isset($_GET["name"])) {
            $nameget = htmlspecialchars($_GET["name"], ENT_QUOTES, 'UTF-8');
            echo "
            <div id='popup1' class='overlay'>
                <div class='popup'>
                    <center>
                        <h2>Are you sure?</h2>
                        <a class='close' href='doctors.php'>&times;</a>
                        <div class='content'>
                            You want to delete this record for $nameget.
                        </div>
                        <div style='display: flex;justify-content: center;'>
                            <a href='delete-doctor.php?id=$id' class='non-style-link'><button class='btn-primary btn'>Yes</button></a>
                            &nbsp;
                            <a href='doctors.php' class='non-style-link'><button class='btn-primary btn'>No</button></a>
                        </div>
                    </center>
                </div>
            </div>";
        }

        elseif ($action === 'view' && $id !== null) {
            $sqlmain = "SELECT * FROM doctor WHERE docid = ?";
            $stmt = $database->prepare($sqlmain);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if ($row = $result->fetch_assoc()) {
                $name = htmlspecialchars($row["docname"], ENT_QUOTES, 'UTF-8');
                $email = htmlspecialchars($row["docemail"], ENT_QUOTES, 'UTF-8');
                $spe = htmlspecialchars($row["specialties"], ENT_QUOTES, 'UTF-8');
    
                // Get the specialty name
                $stmt = $database->prepare("SELECT sname FROM specialties WHERE id = ?");
                $stmt->bind_param("s", $spe);
                $stmt->execute();
                $spcil_res = $stmt->get_result();
                $spcil_array = $spcil_res->fetch_assoc();
                $spcil_name = htmlspecialchars($spcil_array["sname"], ENT_QUOTES, 'UTF-8');
                $nic = htmlspecialchars($row['docnic'], ENT_QUOTES, 'UTF-8');
                $tele = htmlspecialchars($row['doctel'], ENT_QUOTES, 'UTF-8');
    
                echo "
                <div id='popup1' class='overlay'>
                    <div class='popup'>
                        <center>
                            <h2>Doctor Details</h2>
                            <a class='close' href='doctors.php'>&times;</a>
                            <div class='content'>eDoc Web App<br></div>
                            <div style='display: flex; justify-content: center;'>
                                <table width='80%' class='sub-table scrolldown add-doc-form-container' border='0'>
                                    <tr><td><p style='padding: 0; margin: 0; text-align: left; font-size: 25px; font-weight: 500;'>View Details</p><br><br></td></tr>
                                    <tr><td class='label-td' colspan='2'><label class='form-label'>Name:</label></td></tr>
                                    <tr><td class='label-td' colspan='2'>$name<br><br></td></tr>
                                    <tr><td class='label-td' colspan='2'><label class='form-label'>Email:</label></td></tr>
                                    <tr><td class='label-td' colspan='2'>$email<br><br></td></tr>
                                    <tr><td class='label-td' colspan='2'><label class='form-label'>NIC:</label></td></tr>
                                    <tr><td class='label-td' colspan='2'>$nic<br><br></td></tr>
                                    <tr><td class='label-td' colspan='2'><label class='form-label'>Telephone:</label></td></tr>
                                    <tr><td class='label-td' colspan='2'>$tele<br><br></td></tr>
                                    <tr><td class='label-td' colspan='2'><label class='form-label'>Specialties:</label></td></tr>
                                    <tr><td class='label-td' colspan='2'>$spcil_name<br><br></td></tr>
                                    <tr><td colspan='2'><a href='doctors.php'><input type='button' value='OK' class='login-btn btn-primary-soft btn'></a></td></tr>
                                </table>
                            </div>
                        </center>
                        <br><br>
                    </div>
                </div>";
            } else {
                echo "<p>Error: Doctor not found.</p>";
            }
    
        } elseif ($action === 'session' && isset($_GET["name"])) {
            $name = htmlspecialchars($_GET["name"], ENT_QUOTES, 'UTF-8');
            echo "
            <div id='popup1' class='overlay'>
                <div class='popup'>
                    <center>
                        <h2>Redirect to Doctors Sessions?</h2>
                        <a class='close' href='doctors.php'>&times;</a>
                        <div class='content'>You want to view all sessions by <br>(" . htmlspecialchars(substr($name, 0, 40)) . ")</div>
                        <form action='schedule.php' method='post' style='display: flex; justify-content: center; width: 100%; margin: 6% 0;'>
                            <input type='hidden' name='search' value='" . htmlspecialchars($name) . "'>
                            <input type='submit' value='Yes' class='btn-primary btn' style='margin: 0 auto;'>
                        </form>
                    </center>
                </div>
            </div>";
            
    
        } elseif ($action === 'edit' && $id !== null) {
            $sqlmain = "SELECT * FROM doctor WHERE docid = ?";
            $stmt = $database->prepare($sqlmain);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if ($row = $result->fetch_assoc()) {
                $name = htmlspecialchars($row["docname"], ENT_QUOTES, 'UTF-8');
                $email = htmlspecialchars($row["docemail"], ENT_QUOTES, 'UTF-8');
                $spe = htmlspecialchars($row["specialties"], ENT_QUOTES, 'UTF-8');
    
                $stmt = $database->prepare("SELECT sname FROM specialties WHERE id = ?");
                $stmt->bind_param("s", $spe);
                $stmt->execute();
                $spcil_res = $stmt->get_result();
                $spcil_name = htmlspecialchars($spcil_res->fetch_assoc()["sname"], ENT_QUOTES, 'UTF-8');
                $nic = htmlspecialchars($row['docnic'], ENT_QUOTES, 'UTF-8');
                $tele = htmlspecialchars($row['doctel'], ENT_QUOTES, 'UTF-8');
    
                $error_1 = isset($_GET["error"]) ? $_GET["error"] : '';
                $errorlist = array(
                    '1' => "<label for='promter' class='form-label' style='color:rgb(255,62,62);text-align:center;'>Already have an account for this Email address.</label>",
                    '2' => "<label for='promter' class='form-label' style='color:rgb(255,62,62);text-align:center;'>Password Confirmation Error! Please re-confirm Password.</label>",
                    '3' => '',
                    '4' => '',
                    '0' => '',
                );
                $error_msg = isset($errorlist[$error_1]) ? $errorlist[$error_1] : '';
    
                echo "
                <div id='popup1' class='overlay'>
                    <div class='popup'>
                        <center>
                            <a class='close' href='doctors.php'>&times;</a>
                            <div style='display: flex; justify-content: center;'>
                                <div class='abc'>
                                    <table width='80%' class='sub-table scrolldown add-doc-form-container' border='0'>
                                        <tr><td class='label-td' colspan='2'>$error_msg</td></tr>
                                        <tr><td><p style='text-align: left; font-size: 25px; font-weight: 500;'>Edit Doctor Details</p>Doctor ID : $id (Auto Generated)<br><br></td></tr>
                                        <tr><td class='label-td' colspan='2'>
                                            <form action='edit-doc.php' method='POST' class='add-new-form'>
                                                <label for='Email' class='form-label'>Email:</label>
                                                <input type='hidden' value='$id' name='id00'>
                                            </td></tr>
                                        <tr><td class='label-td' colspan='2'>
                                            <input type='email' name='email' class='input-text' placeholder='Email Address' value='$email' required><br>
                                        </td></tr>
                                        <tr><td class='label-td' colspan='2'><label for='name' class='form-label'>Name:</label></td></tr>
                                        <tr><td class='label-td' colspan='2'><input type='text' name='name' class='input-text' placeholder='Doctor Name' value='$name' required><br></td></tr>
                                        <tr><td class='label-td' colspan='2'><label for='nic' class='form-label'>NIC:</label></td></tr>
                                        <tr><td class='label-td' colspan='2'><input type='text' name='nic' class='input-text' placeholder='NIC Number' value='$nic' required><br></td></tr>
                                        <tr><td class='label-td' colspan='2'><label for='Tele' class='form-label'>Telephone:</label></td></tr>
                                        <tr><td class='label-td' colspan='2'><input type='tel' name='Tele' class='input-text' placeholder='Telephone Number' value='$tele' required><br></td></tr>
                                        <tr><td class='label-td' colspan='2'><label for='spec' class='form-label'>Choose specialties: (Current: $spcil_name)</label></td></tr>
                                        <tr><td class='label-td' colspan='2'><select name='spec' id='' class='box'>";
    
                $list11 = $database->query("SELECT * FROM specialties;");
                while ($row00 = $list11->fetch_assoc()) {
                    $sn = htmlspecialchars($row00["sname"], ENT_QUOTES, 'UTF-8');
                    $id00 = htmlspecialchars($row00["id"], ENT_QUOTES, 'UTF-8');
                    echo "<option value='$id00'>$sn</option>";
                }
                echo "</select><br><br></td></tr>
                                        <tr><td class='label-td' colspan='2'><label for='password' class='form-label'>Password:</label></td></tr>
                                        <tr><td class='label-td' colspan='2'><input type='password' name='password' class='input-text' placeholder='Define a Password' required><br></td></tr>
                                        <tr><td class='label-td' colspan='2'><label for='cpassword' class='form-label'>Confirm Password:</label></td></tr>
                                        <tr><td class='label-td' colspan='2'><input type='password' name='cpassword' class='input-text' placeholder='Confirm Password' required><br></td></tr>
                                        <tr><td colspan='2'><input type='reset' value='Reset' class='login-btn btn-primary-soft btn'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' value='Save' class='login-btn btn-primary btn'></td></tr>
                                    </form></table>
                                </div>
                            </div>
                        </center>
                        <br><br>
                    </div>
                </div>";
        }
        else{
            echo '
                <div id="popup1" class="overlay">
                        <div class="popup">
                        <center>
                        <br><br><br><br>
                            <h2>Edit Successfully!</h2>
                            <a class="close" href="doctors.php">&times;</a>
                            <div class="content">
                                
                                
                            </div>
                            <div style="display: flex;justify-content: center;">
                            
                            <a href="doctors.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>

                            </div>
                            <br><br>
                        </center>
                </div>
                </div>
    ';
        }; 
    };
};
?>
</div>

</body>
</html>