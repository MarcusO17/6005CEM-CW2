<?php
// Add this at the top of the file
$page = $page ?? '';  // Initialize $page if not already set
?>
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
            <td class="menu-btn menu-icon-dashbord <?php echo $page == 'dashboard' ? 'menu-active' : ''; ?>">
                <a href="index.php" class="non-style-link-menu">
                    <div>
                        <p class="menu-text">Dashboard</p>
                    </div>
                </a>
            </td>
        </tr>
        <tr class="menu-row">
            <td class="menu-btn menu-icon-doctor <?php echo $page == 'doctors' ? 'menu-active' : ''; ?>">
                <a href="doctors.php" class="non-style-link-menu">
                    <div>
                        <p class="menu-text">Doctors</p>
                    </div>
                </a>
            </td>
        </tr>
        <tr class="menu-row">
            <td class="menu-btn menu-icon-schedule <?php echo $page == 'schedule' ? 'menu-active' : ''; ?>">
                <a href="schedule.php" class="non-style-link-menu">
                    <div>
                        <p class="menu-text">Schedule</p>
                    </div>
                </a>
            </td>
        </tr>
        <tr class="menu-row">
            <td class="menu-btn menu-icon-appoinment <?php echo $page == 'appointment' ? 'menu-active' : ''; ?>">
                <a href="appointment.php" class="non-style-link-menu">
                    <div>
                        <p class="menu-text">Appointment</p>
                    </div>
                </a>
            </td>
        </tr>
        <tr class="menu-row">
            <td class="menu-btn menu-icon-patient <?php echo $page == 'patient' ? 'menu-active' : ''; ?>">
                <a href="patient.php" class="non-style-link-menu">
                    <div>
                        <p class="menu-text">Patients</p>
                    </div>
                </a>
            </td>
        </tr>
        <?php if (isset($is_super_admin) && $is_super_admin): ?>
            <tr class="menu-row">
                <td class="menu-btn menu-icon-patient <?php echo $page == 'users' ? 'menu-active' : ''; ?>">
                    <a href="users.php" class="non-style-link-menu">
                        <div>
                            <p class="menu-text">User Management</p>
                        </div>
                    </a>
                </td>
            </tr>
            <tr class="menu-row">
                <td class="menu-btn menu-icon-dashbord <?php echo $page == 'analytics' ? 'menu-active' : ''; ?>">
                    <a href="analytics.php" class="non-style-link-menu">
                        <div>
                            <p class="menu-text">Analytics</p>
                        </div>
                    </a>
                </td>
            </tr>
        <?php endif; ?>
        <tr class="menu-row">
            <td class="menu-btn menu-icon-dashbord <?php echo $page == 'manage_analytics' ? 'menu-btn-active' : 'menu-btn'; ?>">
                <a href="manage_analytics_data.php" class="non-style-link-menu">
                    <div>
                        <p class="menu-text">Manage Analytics Data</p>
                    </div>
                </a>
            </td>
        </tr>
    </table>
</div>