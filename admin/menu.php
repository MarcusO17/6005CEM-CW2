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
                            <p class="profile-title">Administrator</p>
                            <p class="profile-subtitle"><?php echo $_SESSION["user"]; ?></p>
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
            <td class="menu-btn menu-icon-dashbord">
                <a href="index.php" class="non-style-link-menu">
                    <div><p class="menu-text">Dashboard</p></div>
                </a>
            </td>
        </tr>
        <tr class="menu-row">
            <td class="menu-btn menu-icon-doctor">
                <a href="doctors.php" class="non-style-link-menu">
                    <div><p class="menu-text">Doctors</p></div>
                </a>
            </td>
        </tr>
        <tr class="menu-row">
            <td class="menu-btn menu-icon-schedule">
                <a href="schedule.php" class="non-style-link-menu">
                    <div><p class="menu-text">Schedule</p></div>
                </a>
            </td>
        </tr>
        <tr class="menu-row">
            <td class="menu-btn menu-icon-appoinment">
                <a href="appointment.php" class="non-style-link-menu">
                    <div><p class="menu-text">Appointment</p></div>
                </a>
            </td>
        </tr>
        <tr class="menu-row">
            <td class="menu-btn menu-icon-patient">
                <a href="patient.php" class="non-style-link-menu">
                    <div><p class="menu-text">Patients</p></div>
                </a>
            </td>
        </tr>
        <?php if ($is_super_admin): ?>
            <tr class="menu-row">
                <td class="menu-btn menu-icon-patient">
                    <a href="users.php" class="non-style-link-menu">
                        <div><p class="menu-text">User Management</p></div>
                    </a>
                </td>
            </tr>
            <tr class="menu-row">
                <td class="menu-btn menu-icon-dashbord <?php echo $page == 'analytics' ? 'menu-active' : ''; ?>">
                    <a href="analytics.php" class="non-style-link-menu">
                        <div><p class="menu-text">Analytics</p></div>
                    </a>
                </td>
            </tr>
        <?php endif; ?>
    </table>
</div> 