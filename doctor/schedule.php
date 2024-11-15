<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/admin.css">

    <title>Schedule</title>
    <style>
        .popup {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            animation: none;
            max-height: 90vh;
            overflow-y: auto;
        }

        .sub-table {
            animation: none;
            background: white;
            border-radius: 4px;
            margin: 10px 0;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 10px;
        }

        .add-doc-form-container {
            background: white;
            padding: 10px;
            border-radius: 8px;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        .popup .content {
            margin: 10px 0;
            color: #333;
        }

        .popup .close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
            color: #666;
        }

        .popup .close:hover {
            color: #333;
        }

        .abc.scroll {
            scrollbar-width: thin;
            scrollbar-color: #ddd transparent;
            max-height: calc(90vh - 100px);
            overflow-y: auto;
        }

        .abc.scroll::-webkit-scrollbar {
            width: 6px;
        }

        .abc.scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .abc.scroll::-webkit-scrollbar-thumb {
            background-color: #ddd;
            border-radius: 3px;
        }

        .table-headin {
            background: white;
            position: sticky;
            top: 0;
            z-index: 1;
            padding: 8px;
        }

        br {
            display: block;
            margin: 5px 0;
        }

        .form-label {
            margin-bottom: 3px;
            display: inline-block;
        }

        p {
            margin: 5px 0;
        }

        .label-td {
            padding: 5px 10px;
        }

        .sub-table td {
            padding: 8px;
        }
    </style>
</head>

<body>
    <?php

    //learn from w3schools.com

    session_start();

    include('../session_handler.php');

    if (isset($_SESSION["user"])) {
        if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'd') {
            header("location: ../login.php");
        } else {
            $useremail = $_SESSION["user"];
        }
    } else {
        header("location: ../login.php");
    }

    include('../csrf_helper.php');

    //import database
    include("../connection.php");

    // Sanitize user email to prevent SQL injection
    $useremail = mysqli_real_escape_string($database, $useremail);

    // Query to fetch doctor details with error handling
    $userrow = $database->query("SELECT * FROM doctor WHERE docemail='$useremail'");
    if (!$userrow) {
        echo '<p class="error-message">An error occurred while retrieving data. Please try again later.</p>';
        error_log("Database error: " . $database->error);
        exit();
    }

    $userfetch = $userrow->fetch_assoc();
    $userid = htmlspecialchars($userfetch["docid"], ENT_QUOTES, 'UTF-8');
    $username = htmlspecialchars($userfetch["docname"], ENT_QUOTES, 'UTF-8');
    //echo $userid;
    ?>
    <div class="container">
        <div class="menu">
            <table class="menu-container" border="0">
                <tr>
                    <td style="padding:10px" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="30%" style="padding-left:20px">
                                    <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title"><?php echo substr($username, 0, 13)  ?>..</p>
                                    <p class="profile-subtitle"><?php echo substr($useremail, 0, 22)  ?></p>
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
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-dashbord ">
                        <a href="index.php" class="non-style-link-menu ">
                            <div>
                                <p class="menu-text">Dashboard</p>
                        </a>
        </div></a>
        </td>
        </tr>
        <tr class="menu-row">
            <td class="menu-btn menu-icon-appoinment  ">
                <a href="appointment.php" class="non-style-link-menu">
                    <div>
                        <p class="menu-text">My Appointments</p>
                </a>
    </div>
    </td>
    </tr>

    <tr class="menu-row">
        <td class="menu-btn menu-icon-session menu-active menu-icon-session-active">
            <a href="schedule.php" class="non-style-link-menu non-style-link-menu-active">
                <div>
                    <p class="menu-text">My Sessions</p>
                </div>
            </a>
        </td>
    </tr>
    <tr class="menu-row">
        <td class="menu-btn menu-icon-patient">
            <a href="patient.php" class="non-style-link-menu">
                <div>
                    <p class="menu-text">My Patients</p>
            </a></div>
        </td>
    </tr>
    <tr class="menu-row">
        <td class="menu-btn menu-icon-settings">
            <a href="settings.php" class="non-style-link-menu">
                <div>
                    <p class="menu-text">Settings</p>
            </a></div>
        </td>
    </tr>

    </table>
    </div>
    <div class="dash-body">
        <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
            <tr>
                <td width="13%">
                    <a href="schedule.php"><button class="login-btn btn-primary-soft btn btn-icon-back" style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px">
                            <font class="tn-in-text">Back</font>
                        </button></a>
                </td>
                <td>
                    <p style="font-size: 23px;padding-left:12px;font-weight: 600;">My Sessions</p>

                </td>
                <td width="15%">
                    <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                        Today's Date
                    </p>
                    <p class="heading-sub12" style="padding: 0;margin: 0;">
                        <?php

                        date_default_timezone_set('Asia/Kolkata');

                        $today = date('Y-m-d');
                        echo $today;

                        $sqlmain = "SELECT * FROM schedule WHERE docid=$userid";
                        $list110 = $database->query($sqlmain);
                        if (!$list110) {
                            echo '<p class="error-message">An error occurred while retrieving sessions. Please try again later.</p>';
                            error_log("Database error: " . $database->error);
                            exit();
                        }

                        ?>
                    </p>
                </td>
                <td width="10%">
                    <button class="btn-label" style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                </td>


            </tr>


            <tr>
                <td colspan="4" style="padding-top:10px;width: 100%;">

                    <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">My Sessions (<?php echo $list110->num_rows; ?>) </p>
                </td>

            </tr>

            <tr>
                <td colspan="4" style="padding-top:0px;width: 100%;">
                    <center>
                        <table class="filter-container" border="0">
                            <tr>
                                <td width="10%">

                                </td>
                                <td width="5%" style="text-align: center;">
                                    Date:
                                </td>
                                <td width="30%">
                                    <form action="" method="post">

                                        <input type="date" name="sheduledate" id="date" class="input-text filter-container-items" style="margin: 0;width: 95%;" min="1900-01-01" max="2099-12-31">

                                </td>

                                <<td width="20%">
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <a href="?action=<?php echo isset($_GET['action']) && $_GET['action'] == 'upcoming' ? 'all' : 'upcoming'; ?>" class="non-style-link">
                                            <input type="button" value="<?php echo isset($_GET['action']) && $_GET['action'] == 'upcoming' ? 'Show All' : 'View Upcoming'; ?>" class="btn-primary-soft btn button-icon btn-view" style="padding: 12px 40px;">
                                        </a>
                                        <input type="submit" name="filter" value="Filter" class="btn-primary-soft btn button-icon btn-filter" style="padding: 12px 40px;">
                                    </div>
                </td>
                </form>
            </tr>
        </table>

        </center>
        </td>

        </tr>

        <?php

        $sqlmain = "select schedule.scheduleid,schedule.title,doctor.docname,schedule.scheduledate,schedule.scheduletime,schedule.nop from schedule inner join doctor on schedule.docid=doctor.docid where doctor.docid=$userid ";
        if ($_POST) {
            //print_r($_POST);
            $sqlpt1 = "";
            if (!empty($_POST["sheduledate"])) {
                $sheduledate = mysqli_real_escape_string($database, $_POST["sheduledate"]);
                $sqlmain .= " and schedule.scheduledate='$sheduledate' ";
            }
        }
        if (isset($_GET['action']) && $_GET['action'] == 'upcoming') {
            $today = date("Y-m-d");
            $sqlmain .= " AND schedule.scheduledate >= '$today'";
        }

        ?>

        <tr>
            <td colspan="4">
                <center>
                    <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown" border="0">
                            <thead>
                                <tr>
                                    <th class="table-headin">


                                        Session Title

                                    </th>


                                    <th class="table-headin">

                                        Sheduled Date & Time

                                    </th>
                                    <th class="table-headin">

                                        Max num that can be booked

                                    </th>

                                    <th class="table-headin">

                                        Session Details

                                    </th>

                                    <th class="table-headin">

                                        Events

                                </tr>
                            </thead>
                            <tbody>

                                <?php


                                $result = $database->query($sqlmain);

                                if ($result->num_rows == 0) {
                                    echo '<tr>
                                    <td colspan="4">
                                    <br><br><br><br>
                                    <center>
                                    <img src="../img/notfound.svg" width="25%">
                                    
                                    <br>
                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We  couldnt find anything related to your keywords !</p>
                                    <a class="non-style-link" href="schedule.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Sessions &nbsp;</font></button>
                                    </a>
                                    </center>
                                    <br><br><br><br>
                                    </td>
                                    </tr>';
                                } else {
                                    for ($x = 0; $x < $result->num_rows; $x++) {
                                        $row = $result->fetch_assoc();
                                        $scheduleid = htmlspecialchars($row["scheduleid"]);
                                        $title = htmlspecialchars($row["title"]);
                                        $scheduledate = htmlspecialchars($row["scheduledate"]);
                                        $scheduletime = htmlspecialchars($row["scheduletime"]);
                                        $nop = htmlspecialchars($row["nop"]);
                                        echo '<tr>
                                        <td> &nbsp;' .
                                            substr($title, 0, 30)
                                            . '</td>
                                        
                                        <td style="text-align:center;">
                                            ' . substr($scheduledate, 0, 10) . ' ' . substr($scheduletime, 0, 5) . '
                                        </td>
                                        <td style="text-align:center;">
                                            ' . $nop . '
                                        </td>

                                        <td>
                                        <div style="display:flex;justify-content: center;">
                                            <a href="?action=view&id=' . urlencode($scheduleid) . '" class="non-style-link">
                                                <button class="btn-primary-soft btn button-icon btn-view" style="padding: 12px 40px;margin-top: 10px;">
                                                    <font class="tn-in-text">View</font>
                                                </button>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display:flex;justify-content: center;">
                                            <a href="?action=drop&id=' . urlencode($scheduleid) . '&name=' . urlencode(urlencode($title)) . '" class="non-style-link">
                                                <button class="btn-primary-soft btn button-icon btn-delete" style="padding: 12px 40px;margin-top: 10px;">
                                                    <font class="tn-in-text">Cancel Session</font>
                                                </button>
                                            </a>
                                        </div>
                                    </td>
                                    </tr>';
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

    if ($_GET) {
        $id = htmlspecialchars($_GET["id"], ENT_QUOTES, 'UTF-8');
        $action = htmlspecialchars($_GET["action"], ENT_QUOTES, 'UTF-8');
        if ($action == 'drop') {
            $nameget = htmlspecialchars($_GET["name"]);
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        <h2>Are you sure?</h2>
                        <a class="close" href="schedule.php">&times;</a>
                        <div class="content">
                            You want to delete this record<br>(' . substr($nameget, 0, 40) . ').
                            
                        </div>
                        <div style="display: flex;justify-content: center;">
                        <form action="delete-session.php" method="POST" class="non-style-link">
                            <input type="hidden" name="id" value="' . urlencode($id) . '">
                            <input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">
                            <button type="submit" class="btn-primary btn" style="margin: 10px; padding: 10px;">
                                <font class="tn-in-text">&nbsp;&nbsp;Yes&nbsp;&nbsp;</font>
                            </button>
                        </form>
                        <a href="schedule.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;No&nbsp;&nbsp;</font></button></a>

                        </div>
                    </center>
            </div>
            </div>
            ';
        } elseif ($action == 'view') {
            $sqlmain = "select schedule.scheduleid,schedule.title,doctor.docname,schedule.scheduledate,schedule.scheduletime,schedule.nop from schedule inner join doctor on schedule.docid=doctor.docid  where  schedule.scheduleid=$id";
            $result = $database->query($sqlmain);
            $row = $result->fetch_assoc();
            $docname = htmlspecialchars($row["docname"]);
            $title = htmlspecialchars($row["title"]);
            $scheduledate = htmlspecialchars($row["scheduledate"]);
            $scheduletime = htmlspecialchars($row["scheduletime"]);
            $nop = htmlspecialchars($row['nop']);


            $nop = $row['nop'];


            $sqlmain12 = "select * from appointment inner join patient on patient.pid=appointment.pid inner join schedule on schedule.scheduleid=appointment.scheduleid where schedule.scheduleid=$id;";
            $result12 = $database->query($sqlmain12);
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup" style="width: 70%; height: 100%;">
                    <center>
                        <h2></h2>
                        <a class="close" href="schedule.php">&times;</a>
                        <div class="content">
                            
                            
                        </div>
                        <div class="abc scroll" style="display: flex;justify-content: center; height: 100%;">
                        <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                        
                            <tr>
                                <td>
                                    <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">View Details.</p><br><br>
                                </td>
                            </tr>
                            
                            <tr>
                                
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Session Title: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    ' . $title . '<br><br>
                                </td>
                                
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Email" class="form-label">Doctor of this session: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                ' . $docname . '<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="nic" class="form-label">Scheduled Date: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                ' . $scheduledate . '<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Tele" class="form-label">Scheduled Time: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                ' . $scheduletime . '<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="spec" class="form-label"><b>Patients that Already registerd for this session:</b> (' . $result12->num_rows . "/" . $nop . ')</label>
                                    <br><br>
                                </td>
                            </tr>

                            
                            <tr>
                            <td colspan="4">
                                <center>
                                 <div class="abc scroll">
                                 <table width="100%" class="sub-table scrolldown" border="0">
                                 <thead>
                                 <tr>   
                                        <th class="table-headin">
                                             Patient ID
                                         </th>
                                         <th class="table-headin">
                                             Patient name
                                         </th>
                                         <th class="table-headin">
                                             
                                             Appointment number
                                             
                                         </th>
                                        
                                         
                                         <th class="table-headin">
                                             Patient Telephone
                                         </th>
                                         
                                 </thead>
                                 <tbody>';




            $result = $database->query($sqlmain12);

            if ($result->num_rows == 0) {
                echo '<tr>
                                             <td colspan="7">
                                             <br><br><br><br>
                                             <center>
                                             <img src="../img/notfound.svg" width="25%">
                                             
                                             <br>
                                             <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We  couldnt find anything related to your keywords !</p>
                                             <a class="non-style-link" href="appointment.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Appointments &nbsp;</font></button>
                                             </a>
                                             </center>
                                             <br><br><br><br>
                                             </td>
                                             </tr>';
            } else {
                for ($x = 0; $x < $result->num_rows; $x++) {
                    $row = $result->fetch_assoc();
                    $apponum = htmlspecialchars($row["apponum"]);
                    $pid = htmlspecialchars($row["pid"]);
                    $pname = htmlspecialchars($row["pname"]);
                    $ptel = htmlspecialchars($row["ptel"]);

                    echo '<tr style="text-align:center;">
                                                <td>
                                                ' . substr($pid, 0, 15) . '
                                                </td>
                                                 <td style="font-weight:600;padding:25px">' .

                        substr($pname, 0, 25)
                        . '</td >
                                                 <td style="text-align:center;font-size:23px;font-weight:500; color: var(--btnnicetext);">
                                                 ' . $apponum . '
                                                 
                                                 </td>
                                                 <td>
                                                 ' . substr($ptel, 0, 25) . '
                                                 </td>
                                                 
                                             </tr>';
                }
            }

            echo '</tbody>
                
                                 </table>
                                 </div>
                                 </center>
                            </td> 
                         </tr>

                        </table>
                        </div>
                    </center>
                    <br><br>
            </div>
            </div>
            ';
        }
    }

    ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.querySelector('.overlay');
            if (overlay) {
                document.body.style.overflow = 'hidden';

                // Ensure popup is fully opaque
                const popup = overlay.querySelector('.popup');
                if (popup) {
                    popup.style.opacity = '1';
                    popup.style.background = '#fff';
                }

                // Ensure tables are fully opaque
                const tables = overlay.querySelectorAll('.sub-table');
                tables.forEach(table => {
                    table.style.opacity = '1';
                    table.style.background = '#fff';
                });
            }
        });
    </script>
</body>

</html>