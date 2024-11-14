<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css">
        
    <title>Prescriptions</title>
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

    if(isset($_SESSION["user"])) {
        if(($_SESSION["user"]) == "" || $_SESSION['usertype'] != 'p') {
            header("location: ../login.php");
            exit();
        } else {
            $useremail = $_SESSION["user"];
        }
    } else {
        header("location: ../login.php");
        exit();
    }

    include("../connection.php");
    $userrow = $database->query("SELECT * FROM patient WHERE pemail='$useremail'");
    $userfetch = $userrow->fetch_assoc();
    $userid = $userfetch["pid"];
    $username = $userfetch["pname"];

    // import EncryptionUtil
    require "../utils/encryption-util.php";
    use function Utils\decrypt;


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
                                <td style="padding:0;margin:0;">
                                    <p class="profile-title"><?php echo substr($username,0,13)  ?>..</p>
                                    <p class="profile-subtitle"><?php echo substr($useremail,0,22)  ?></p>
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
                    <td class="menu-btn menu-icon-home">
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Home</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-doctor">
                        <a href="doctors.php" class="non-style-link-menu"><div><p class="menu-text">All Doctors</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-session menu-icon-session-active menu-active">
                        <a href="prescriptions.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text">My Prescriptions</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-session">
                        <a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">Scheduled Sessions</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">My Bookings</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></a></div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="dash-body">
            <table border="0" width="100%" style="border-spacing: 0;margin:0;padding:0;margin-top:25px;">
                <tr>
                    <td width="13%">
                        <a href="patient.php"><button class="login-btn btn-primary-soft btn btn-icon-back" style="padding:11px 0;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <form action="" method="post" class="header-search">
                            <input type="search" name="search" class="input-text header-searchbar" placeholder="Search Medication" list="prescriptions">&nbsp;&nbsp;
                            <?php
                                echo '<datalist id="prescriptions">';
                                $list11 = $database->query("SELECT appointment_id, pid, medication, dosage, frequency, additional_notes FROM prescription WHERE pid = $userid;");

                                while ($row00 = $list11->fetch_assoc()) {
                                    $medication = decrypt($row00["medication"]);
                                    $dosage = decrypt($row00["dosage"]);
                                    $frequency = decrypt($row00["frequency"]);
                                    $additional_notes = decrypt($row00["additional_notes"]);
                                    
                                    $option_value = "$medication - $dosage - $frequency - $additional_notes";
                                    echo "<option value='$option_value'>";
                                }

                                echo '</datalist>';
                            ?>
                            <input type="submit" value="Search" class="login-btn btn-primary btn" style="padding: 10px 25px;">
                        </form>
                    </td>
                    <td width="15%" style="text-align: right;">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);margin: 0;">Today's Date</p>
                        <p class="heading-sub12" style="margin: 0;">
                            <?php 
                                date_default_timezone_set('Asia/Kolkata');
                                echo date('Y-m-d');
                            ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button class="btn-label" style="display: flex;justify-content: center;align-items: center;">
                            <img src="../img/calendar.svg" width="100%">
                        </button>
                    </td>
                </tr>
                <?php
                    $keyword = '';
                    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["search"])) {
                        // Sanitize input for HTML
                        $keyword = htmlspecialchars(trim($_POST["search"]), ENT_QUOTES, 'UTF-8');
                    
                        // Prepare the SQL query with wildcard search
                        $sqlmain = "SELECT * FROM prescription WHERE (medication LIKE ? OR additional_notes LIKE ?) AND pid = ?";
                        $stmt = $database->prepare($sqlmain);
                        $search_param = "%" . $database->real_escape_string($keyword) . "%";
                        $stmt->bind_param("ssi", $search_param, $search_param, $userid);
                        $stmt->execute();
                        $result = $stmt->get_result();
                    } else {
                        // Default query to fetch all prescriptions for the user
                        $sqlmain = "SELECT * FROM prescription WHERE pid = ?";
                        $stmt = $database->prepare($sqlmain);
                        $stmt->bind_param("i", $userid);
                        $stmt->execute();
                        $result = $stmt->get_result();
                    }
                ?>
                <tr>
                    <td colspan="4" style="padding-top:10px;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">My Prescriptions (<?php echo $result->num_rows; ?>)</p>
                    </td>
                </tr>

                <tr>
                   <td colspan="4">
                       <center>
                        <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown" border="0">
                        <thead>
                        <tr>
                            <th class="table-headin">Medication</th>
                            <th class="table-headin">Dosage</th>
                            <th class="table-headin">Frequency</th>
                            <th class="table-headin">Additional Notes</th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php
                                if ($result->num_rows == 0) {
                                    echo '<tr>
                                            <td colspan="4">
                                                <center>
                                                    <img src="../img/notfound.svg" width="25%">
                                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">No results found!</p>
                                                </center>
                                            </td>
                                          </tr>';
                                } else {
                                    while ($row = $result->fetch_assoc()) {
                                        $medication = decrypt($row["medication"]);
                                        $dosage = decrypt($row["dosage"]);
                                        $frequency = decrypt($row["frequency"]);
                                        $additional_notes = decrypt($row["additional_notes"]);
                                        
                                        echo "<tr>
                                                    <td>&nbsp;" . substr($medication, 0, 30) . "</td>
                                                    <td>" . substr($dosage, 0, 20) . "</td>
                                                    <td>" . substr($frequency, 0, 20) . "</td>
                                                    <td>
                                                    <div style='display:flex; justify-content:center;'>
                                                        <a href='?action=view&note=" . urlencode($additional_notes) . "' class='non-style-link'>
                                                            <button class='btn-primary-soft btn button-icon btn-view' style='padding-left: 40px; padding-top: 12px; padding-bottom: 12px; margin-top: 10px;'>
                                                                <font class='tn-in-text'>View</font>
                                                            </button>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>";
                                    }
                                }
                                ?>

                                <?php
                                if (isset($_GET["action"]) && $_GET["action"] == "view" && isset($_GET["note"])) {
                                    $additional_notes = htmlspecialchars($_GET["note"]);
                                
                                    echo '
                                    <div id="popup1" class="overlay">
                                        <div class="popup">
                                            <center>
                                                <a class="close" href="prescriptions.php">&times;</a>
                                                <div class="content">
                                                    <h2>Additional Notes</h2>
                                                    <p style="text-align: left; font-size: 18px;">' . nl2br($additional_notes) . '</p>
                                                </div>
                                            </center>
                                        </div>
                                    </div>';
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
