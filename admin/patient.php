<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/admin.css">

    <title>Patients</title>
    <style>
        /* Overlay animation */
        .overlay {
            opacity: 0;
            animation: fade-in 0.3s ease forwards;
        }

        /* Main popup container animation */
        .popup {
            opacity: 0;
            transform: scale(0.95) translateY(-10px);
            animation: popup-appear 0.4s cubic-bezier(0.2, 0.9, 0.3, 1.1) forwards;
        }

        /* Table content animation */
        .popup .sub-table {
            opacity: 0;
            transform: translateY(10px);
            animation: content-appear 0.4s ease forwards;
            animation-delay: 0.2s;
        }

        /* Animation keyframes */
        @keyframes fade-in {
            to {
                opacity: 1;
            }
        }

        @keyframes popup-appear {
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes content-appear {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <?php

    //learn from w3schools.com

    session_start();

    include('../session_handler.php');

    if (isset($_SESSION["user"])) {
        if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'a') {
            header("location: ../login.php");
        }
    } else {
        header("location: ../login.php");
    }

    include('../csrf_helper.php');

    //import database
    include("../connection.php");

    // import EncryptionUtil
    require "../utils/encryption-util.php";

    use function Utils\decrypt;


    ?>
    <div class="container">
        <?php
        $page = 'patient'; // Set current page for menu highlighting
        include('menu.php');
        ?>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr>
                    <td width="13%">

                        <a href="patient.php"><button class="login-btn btn-primary-soft btn btn-icon-back" style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px">
                                <font class="tn-in-text">Back</font>
                            </button></a>

                    </td>
                    <td>

                        <form action="" method="post" class="header-search">

                            <input type="search" name="search" class="input-text header-searchbar" placeholder="Search Patient name or Email" list="patient">&nbsp;&nbsp;

                            <?php
                            echo '<datalist id="patient">';
                            $list11 = $database->query("select  pname,pemail from patient;");

                            for ($y = 0; $y < $list11->num_rows; $y++) {
                                $row00 = $list11->fetch_assoc();
                                $d = $row00["pname"];
                                $c = $row00["pemail"];
                                echo "<option value='$d'><br/>";
                                echo "<option value='$c'><br/>";
                            };

                            echo ' </datalist>';
                            ?>


                            <input type="Submit" value="Search" class="login-btn btn-primary btn" style="padding-left: 25px;padding-right: 25px;padding-top: 10px;padding-bottom: 10px;">

                        </form>

                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php
                            date_default_timezone_set('Asia/Kolkata');

                            $date = date('Y-m-d');
                            echo $date;
                            ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button class="btn-label" style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>


                </tr>


                <tr>
                    <td colspan="4" style="padding-top:10px;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">All Patients (<?php echo $list11->num_rows; ?>)</p>
                    </td>

                </tr>
                <?php
                if ($_POST) {
                    // Sanitize the search input
                    $keyword = $_POST["search"];

                    // Prepare the SQL query with placeholders for secure binding
                    $sqlmain = "
                            SELECT * FROM patient 
                            WHERE pemail = ? 
                            OR pname = ? 
                            OR pname LIKE CONCAT(?, '%') 
                            OR pname LIKE CONCAT('%', ?) 
                            OR pname LIKE CONCAT('%', ?, '%')
                        ";

                    // Prepare the statement
                    $stmt = $database->prepare($sqlmain);

                    // Bind the same keyword to each placeholder
                    $stmt->bind_param("sssss", $keyword, $keyword, $keyword, $keyword, $keyword);

                    // Execute the statement
                    $stmt->execute();

                    // Get the result
                    $result = $stmt->get_result();

                    // Close the statement
                    $stmt->close();
                } else {
                    // Default query without search input
                    $sqlmain = "SELECT * FROM patient ORDER BY pid DESC";
                    $result = $database->query($sqlmain);
                }



                ?>

                <tr>
                    <td colspan="4">
                        <center>
                            <div class="abc scroll">
                                <table width="93%" class="sub-table scrolldown" style="border-spacing:0;">
                                    <thead>
                                        <tr>
                                            <th class="table-headin">


                                                Name

                                            </th>
                                            <th class="table-headin">


                                                NIC

                                            </th>
                                            <th class="table-headin">


                                                Telephone

                                            </th>
                                            <th class="table-headin">
                                                Email
                                            </th>
                                            <th class="table-headin">

                                                Date of Birth

                                            </th>
                                            <th class="table-headin">

                                                Events

                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php


                                        // $result= $database->query($sqlmain);

                                        if ($result->num_rows == 0) {
                                            echo '<tr>
                                    <td colspan="4">
                                    <br><br><br><br>
                                    <center>
                                    <img src="../img/notfound.svg" width="25%">
                                    
                                    <br>
                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We  couldnt find anything related to your keywords !</p>
                                    <a class="non-style-link" href="patient.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Patients &nbsp;</font></button>
                                    </a>
                                    </center>
                                    <br><br><br><br>
                                    </td>
                                    </tr>';
                                        } else {
                                            for ($x = 0; $x < $result->num_rows; $x++) {
                                                $row = $result->fetch_assoc();
                                                $pid = htmlspecialchars($row["pid"], ENT_QUOTES, 'UTF-8');
                                                $name = htmlspecialchars($row["pname"], ENT_QUOTES, 'UTF-8');
                                                $email = htmlspecialchars($row["pemail"], ENT_QUOTES, 'UTF-8');
                                                $nic = htmlspecialchars(decrypt($row["pnic"]), ENT_QUOTES, 'UTF-8');
                                                $dob = htmlspecialchars($row["pdob"], ENT_QUOTES, 'UTF-8');
                                                $tel = htmlspecialchars($row["ptel"], ENT_QUOTES, 'UTF-8');

                                                echo '<tr>
                                        <td> &nbsp;' .
                                                    substr($name, 0, 35)
                                                    . '</td>
                                        <td>
                                        ' . substr($nic, 0, 12) . '
                                        </td>
                                        <td>
                                            ' . substr($tel, 0, 10) . '
                                        </td>
                                        <td>
                                        ' . substr($email, 0, 30) . '
                                         </td>
                                        <td>
                                        ' . substr($dob, 0, 10) . '
                                        </td>
                                        <td >
                                        <div style="display:flex;justify-content: center;">
                                        
                                        <a href="?action=view&id=' . $pid . '" class="non-style-link"><button  class="btn-primary-soft btn button-icon btn-view"  style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">View</font></button></a>
                                       
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

        $id = $_GET["id"];
        $action = $_GET["action"];
        $sqlmain = "select * from patient where pid='$id'";
        $result = $database->query($sqlmain);
        $row = $result->fetch_assoc();
        $pid = htmlspecialchars($row["pid"], ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars($row["pname"], ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($row["pemail"], ENT_QUOTES, 'UTF-8');
        $nic = htmlspecialchars(decrypt($row["pnic"]), ENT_QUOTES, 'UTF-8');
        $dob = htmlspecialchars($row["pdob"], ENT_QUOTES, 'UTF-8');
        $tel = htmlspecialchars($row["ptel"], ENT_QUOTES, 'UTF-8');
        echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        <a class="close" href="patient.php">&times;</a>
                        <div class="content">

                        </div>
                        <div style="display: flex;justify-content: center;">
                        <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                        
                            <tr>
                                <td>
                                    <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">View Details.</p><br><br>
                                </td>
                            </tr>
                            <tr>
                                
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Patient ID: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    P-' . $id . '<br><br>
                                </td>
                                
                            </tr>
                            
                            <tr>
                                
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Name: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    ' . $name . '<br><br>
                                </td>
                                
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Email" class="form-label">Email: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                ' . $email . '<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="nic" class="form-label">NIC: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                ' . $nic . '<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Tele" class="form-label">Telephone: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                ' . $tele . '<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="spec" class="form-label">Address: </label>
                                    
                                </td>
                            </tr>
                            <tr>
                            <td class="label-td" colspan="2">
                            ' . $address . '<br><br>
                            </td>
                            </tr>
                            <tr>
                                
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Date of Birth: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    ' . $dob . '<br><br>
                                </td>
                                
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="patient.php"><input type="button" value="OK" class="login-btn btn-primary-soft btn" ></a>
                                
                                    
                                </td>
                
                            </tr>
                           

                        </table>
                        </div>
                    </center>
                    <br><br>
            </div>
            </div>
            ';
    };

    ?>
    </div>

</body>

</html>