<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Sessions</title>
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
    session_start();

    include('../session_handler.php');
    // Verify user session and authorization
    if(isset($_SESSION["user"])) {
        if(empty($_SESSION["user"]) || $_SESSION['usertype'] != 'p') {
            header("location: ../login.php");
            exit();
        } else {
            $useremail = $_SESSION["user"];
        }
    } else {
        header("location: ../login.php");
        exit();
    }

    include('../csrf_helper.php');
    
    //import database
    include("../connection.php");

    // Fetch user details securely
    $sqlmain = "SELECT * FROM patient WHERE pemail = ?";
    $stmt = $database->prepare($sqlmain);
    $stmt->bind_param("s", $useremail);
    $stmt->execute();
    $result = $stmt->get_result();
    $userfetch = $result->fetch_assoc();
    $userid = htmlspecialchars($userfetch["pid"], ENT_QUOTES, 'UTF-8');
    $username = htmlspecialchars($userfetch["pname"], ENT_QUOTES, 'UTF-8');


    date_default_timezone_set('Asia/Kolkata');
    $today = date('Y-m-d');
    ?>

    <div class="container">
       <div class="menu">
        <table class="menu-container" border="0">
                <tr>
                    <td style="padding:10px" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="30%" style="padding-left:20px" >
                                    <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title"><?php echo substr($username,0,13)  ?>..</p>
                                    <p class="profile-subtitle"><?php echo substr($useremail,0,22)  ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="../logout.php" ><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                                </td>
                            </tr>
                    </table>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-home" >
                        <a href="index.php" class="non-style-link-menu "><div><p class="menu-text">Home</p></a></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-doctor">
                        <a href="doctors.php" class="non-style-link-menu"><div><p class="menu-text">All Doctors</p></a></div>
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
                    <td class="menu-btn menu-icon-appoinment  menu-active menu-icon-appoinment-active">
                        <a href="appointment.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">My Bookings</p></a></div>
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
                        <a href="schedule.php"><button class="login-btn btn-primary-soft btn btn-icon-back" style="padding:11px 0;margin-left:20px;width:125px;">Back</button></a>
                    </td>
                    <td>
                        <form action="schedule.php" method="post" class="header-search">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <input type="search" name="search" class="input-text header-searchbar" placeholder="Search Doctor name or Email or Date (YYYY-MM-DD)" list="doctors">
                            &nbsp;&nbsp;
                            <?php
                                echo '<datalist id="doctors">';
                                $list11 = $database->query("SELECT DISTINCT docname FROM doctor");
                                $list12 = $database->query("SELECT DISTINCT title FROM schedule");

                                while ($row = $list11->fetch_assoc()) {
                                    echo "<option value='".htmlspecialchars($row["docname"], ENT_QUOTES, 'UTF-8')."'><br/>";
                                }

                                while ($row = $list12->fetch_assoc()) {
                                    echo "<option value='".htmlspecialchars($row["title"], ENT_QUOTES, 'UTF-8')."'><br/>";
                                }

                                echo '</datalist>';
                            ?>
                            <input type="Submit" value="Search" class="login-btn btn-primary btn" style="padding:10px 25px;">
                        </form>
                    </td>
                    <td width="15%">
                        <p style="font-size:14px;color:rgb(119, 119, 119);text-align:right;">Today's Date</p>
                        <p class="heading-sub12" style="text-align:right;"><?php echo $today; ?></p>
                    </td>
                    <td width="10%">
                        <button class="btn-label" style="display: flex; justify-content: center; align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>
                </tr>
                
                <tr>
                   <td colspan="4">
                       <center>
                        <div class="abc scroll">
                        <table width="100%" class="sub-table scrolldown" border="0" style="padding: 50px;border:none">
                            <tbody>
                            <?php
                            if($_GET && isset($_GET["id"])) {
                                $id = filter_var($_GET["id"], FILTER_VALIDATE_INT);

                                $sqlmain = "SELECT * FROM schedule INNER JOIN doctor ON schedule.docid=doctor.docid WHERE schedule.scheduleid=? ORDER BY schedule.scheduledate DESC";
                                $stmt = $database->prepare($sqlmain);
                                $stmt->bind_param("i", $id);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows > 0) {
                                    $row = $result->fetch_assoc();
                                    $scheduleid = htmlspecialchars($row["scheduleid"], ENT_QUOTES, 'UTF-8');
                                    $title = htmlspecialchars($row["title"], ENT_QUOTES, 'UTF-8');
                                    $docname = htmlspecialchars($row["docname"], ENT_QUOTES, 'UTF-8');
                                    $docemail = htmlspecialchars($row["docemail"], ENT_QUOTES, 'UTF-8');
                                    $scheduledate = htmlspecialchars($row["scheduledate"], ENT_QUOTES, 'UTF-8');
                                    $scheduletime = htmlspecialchars($row["scheduletime"], ENT_QUOTES, 'UTF-8');
                                    
                                    // Appointment count
                                    $sql2 = "SELECT * FROM appointment WHERE scheduleid=?";
                                    $stmt2 = $database->prepare($sql2);
                                    $stmt2->bind_param("i", $id);
                                    $stmt2->execute();
                                    $result12 = $stmt2->get_result();
                                    $apponum = ($result12->num_rows) + 1;

                                    echo '
                                        <form action="booking-complete.php" method="post">
                                            <input type="hidden" name="scheduleid" value="'.$scheduleid.'" >
                                            <input type="hidden" name="apponum" value="'.$apponum.'" >
                                            <input type="hidden" name="date" value="'.$today.'" >
                                            <input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">

                                        
                                    
                                    ';
                                     

                                    echo '
                                    <td style="width: 50%;" rowspan="2">
                                            <div  class="dashboard-items search-items"  >
                                            
                                                <div style="width:100%">
                                                    <div class="h1-search" style="font-size:25px;">Session Details</div><br><br>
                                                    <div class="h3-search" style="font-size:18px;line-height:30px">
                                                        Doctor name: &nbsp;&nbsp;<b>'.$docname.'</b><br>
                                                        Doctor Email: &nbsp;&nbsp;<b>'.$docemail.'</b>
                                                    </div>
                                                    <div class="h3-search" style="font-size:18px;">Session Title: '.$title.'<br>
                                                        Session Scheduled Date: '.$scheduledate.'<br>
                                                        Session Starts: '.$scheduletime.'<br>
                                                        Channeling fee: <b>LKR. 2000.00</b>
                                                    </div><br>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td style="width: 25%;">
                                            <div class="dashboard-items search-items">
                                                <div style="width:100%;padding:15px;">
                                                    <div class="h1-search" style="font-size:20px;line-height:35px;margin-left:8px;text-align:center;">
                                                        Your Appointment Number
                                                    </div>
                                                    <center>
                                                        <div class="dashboard-icons" style="width:90%;font-size:70px;font-weight:800;text-align:center;color:var(--btnnictext);background-color: var(--btnice)">'.$apponum.'</div>
                                                    </center>
                                                </div>
                                            </div>
                                        </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <input type="Submit" class="login-btn btn-primary btn btn-book" style="width:95%;text-align:center;" value="Book now" name="booknow">
                                            </form>
                                            </td>
                                        </tr>';
                                } else {
                                    echo "<p>No session found for this ID.</p>";
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
</body>
</html>
