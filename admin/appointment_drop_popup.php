<div id="popup1" class="overlay">
    <div class="popup">
        <center>
            <h2>Are you sure?</h2>
            <a class="close" href="appointment.php">&times;</a>
            <div class="content">
                You want to delete this record<br><br>
                Patient Name: &nbsp;<b><?php echo substr($_GET["name"], 0, 40); ?></b><br>
                Appointment number &nbsp; : <b><?php echo substr($_GET["apponum"], 0, 40); ?></b><br><br>
            </div>
            <div style="display: flex;justify-content: center;">
                <form action="delete-appointment.php" method="POST" style="display: inline;">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <button type="submit" class="btn-primary btn" style="display: flex; justify-content: center; align-items: center; margin: 10px; padding: 10px;">
                        <font class="tn-in-text">&nbsp;Yes&nbsp;</font>
                    </button>
                </form>
                <a href="appointment.php" class="non-style-link">
                    <button class="btn-primary btn" style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;">
                        <font class="tn-in-text">&nbsp;&nbsp;No&nbsp;&nbsp;</font>
                    </button>
                </a>
            </div>
        </center>
    </div>
</div> 