<table width="93%" class="sub-table scrolldown" border="0">
    <thead>
        <tr>
            <th class="table-headin">Patient name</th>
            <th class="table-headin">Appointment number</th>
            <th class="table-headin">Doctor</th>
            <th class="table-headin">Session Title</th>
            <th class="table-headin" style="font-size:10px">Session Date & Time</th>
            <th class="table-headin">Appointment Date</th>
            <th class="table-headin">Events</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result->num_rows == 0) {
            echo '<tr>
                <td colspan="7">
                    <br><br><br><br>
                    <center>
                        <img src="../img/notfound.svg" width="25%">
                        <br>
                        <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We  couldnt find anything related to your keywords !</p>
                        <a class="non-style-link" href="appointment.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Appointments &nbsp;</button></a>
                    </center>
                    <br><br><br><br>
                </td>
            </tr>';
        } else {
            for ($x = 0; $x < $result->num_rows; $x++) {
                $row = $result->fetch_assoc();
                $appoid = $row["appoid"];
                $scheduleid = $row["scheduleid"];
                $title = $row["title"];
                $docname = $row["docname"];
                $scheduledate = $row["scheduledate"];
                $scheduletime = $row["scheduletime"];
                $pname = $row["pname"];
                $apponum = $row["apponum"];
                $appodate = $row["appodate"];
                
                echo '<tr>
                    <td style="font-weight:600;"> &nbsp;'.substr($pname, 0, 25).'</td>
                    <td style="text-align:center;font-size:23px;font-weight:500; color: var(--btnnicetext);">'.$apponum.'</td>
                    <td>'.substr($docname, 0, 25).'</td>
                    <td>'.substr($title, 0, 15).'</td>
                    <td style="text-align:center;font-size:12px;">'.substr($scheduledate, 0, 10).' <br>'.substr($scheduletime, 0, 5).'</td>
                    <td style="text-align:center;">'.$appodate.'</td>
                    <td>
                        <div style="display:flex;justify-content: center;">
                            <a href="?action=drop&id='.$appoid.'&name='.$pname.'&session='.$title.'&apponum='.$apponum.'" class="non-style-link">
                                <button class="btn-primary-soft btn button-icon btn-delete" style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;">
                                    <font class="tn-in-text">Cancel</font>
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