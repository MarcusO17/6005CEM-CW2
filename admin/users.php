<?php
session_start();
include('../session_handler.php');
if (isset($_SESSION["user"])) {
  if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'a') {
    header("location: ../login.php");
  }
} else {
  header("location: ../login.php");
}

include("../connection.php");

// Add superadmin check here
$admin_query = $database->prepare("SELECT isSuperAdmin FROM admin WHERE aemail=?");
$admin_query->bind_param("s", $_SESSION["user"]);
$admin_query->execute();
$admin_result = $admin_query->get_result();
$is_super_admin = $admin_result->fetch_assoc()['isSuperAdmin'] ?? false;
$admin_query->close();
if (!$is_super_admin) {
  header("location: index.php");
  exit();
}

if (isset($_POST['delete'])) {
  $email = $_POST['email'];
  $sql = $database->prepare("DELETE FROM webuser WHERE email=?");
  $sql->bind_param("s", $email);
  $sql->execute();

  // Delete from respective user table based on usertype
  if ($_POST['usertype'] == 'p') {
    $sql = $database->prepare("DELETE FROM patient WHERE pemail=?");
  } else if ($_POST['usertype'] == 'd') {
    $sql = $database->prepare("DELETE FROM doctor WHERE docemail=?");
  }
  $sql->bind_param("s", $email);
  $sql->execute();
}

if (isset($_POST['update'])) {
  $email = $_POST['email'];
  $newtype = $_POST['newtype'];
  $sql = $database->prepare("UPDATE webuser SET usertype=? WHERE email=?");
  $sql->bind_param("ss", $newtype, $email);
  $sql->execute();
}

if (isset($_POST['create'])) {
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $usertype = $_POST['usertype'];

  // Start transaction
  $database->begin_transaction();

  try {
    // Insert into webuser table
    $sql = $database->prepare("INSERT INTO webuser (email, usertype) VALUES (?, ?)");
    $sql->bind_param("ss", $email, $usertype);
    $sql->execute();

    // Insert into specific user table based on type
    if ($usertype == 'p') {
      $sql = $database->prepare("INSERT INTO patient (pemail, pname, ppassword, ptel) VALUES (?, ?, ?, ?)");
      $sql->bind_param("ssss", $email, $_POST['pname'], $password, $_POST['ptel']);
      $sql->execute();
    } else if ($usertype == 'd') {
      $sql = $database->prepare("INSERT INTO doctor (docemail, docname, docpassword, doctel, specialties) VALUES (?, ?, ?, ?, ?)");
      $sql->bind_param("ssssi", $email, $_POST['docname'], $password, $_POST['doctel'], $_POST['specialties']);
      $sql->execute();
    } else if ($usertype == 'a') {
      $sql = $database->prepare("INSERT INTO admin (aemail, apassword) VALUES (?, ?)");
      $sql->bind_param("ss", $email, $password);
      $sql->execute();
    }

    $database->commit();
    echo "<script>alert('User created successfully!');</script>";
  } catch (Exception $e) {
    $database->rollback();
    echo "<script>alert('Error creating user: " . $e->getMessage() . "');</script>";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/animations.css">
  <link rel="stylesheet" href="../css/main.css">
  <link rel="stylesheet" href="../css/admin.css">
  <title>Users Management</title>
  <style>
    .container {
      min-height: 100vh;
      display: flex;
    }

    .dash-body {
      flex: 1;
      background: #f3f6ff;
      padding: 20px;
    }

    .table-container {
      background: white;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .header-section {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding: 0 20px;
    }

    .action-buttons {
      display: flex;
      gap: 10px;
    }

    .user-table {
      width: 100%;
      border-collapse: collapse;
    }

    .user-table th,
    .user-table td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }

    .user-table th {
      background: #f8f9fa;
      font-weight: 600;
    }

    .copy-icon {
      cursor: pointer;
      opacity: 0.6;
      transition: opacity 0.2s;
      margin-left: 5px;
    }

    .copy-icon:hover {
      opacity: 1;
    }

    .action-cell {
      display: flex;
      gap: 8px;
      justify-content: flex-end;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
      background-color: #fefefe;
      margin: 15% auto;
      padding: 20px;
      border: 1px solid #888;
      width: 80%;
      max-width: 500px;
    }

    .close {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }

    .close:hover,
    .close:focus {
      color: black;
      text-decoration: none;
      cursor: pointer;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #333;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
      transition: border-color 0.2s;
    }

    .form-group input:focus,
    .form-group select:focus {
      border-color: #2196F3;
      outline: none;
      box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.1);
    }

    .modal-content {
      background-color: #fff;
      margin: 5% auto;
      padding: 25px;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      width: 90%;
      max-width: 500px;
      position: relative;
    }

    .modal-content h3 {
      margin: 0 0 25px 0;
      color: #333;
      font-size: 1.5rem;
    }

    .close {
      position: absolute;
      right: 20px;
      top: 15px;
      font-size: 24px;
      font-weight: 500;
      color: #666;
      transition: color 0.2s;
    }

    .close:hover {
      color: #333;
    }

    #createUserForm {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .btn-primary {
      background-color: #2196F3;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      transition: background-color 0.2s;
      margin-top: 10px;
    }

    .btn-primary:hover {
      background-color: #1976D2;
    }

    /* Add smooth transitions for the fields toggle */
    #patientFields,
    #doctorFields {
      transition: all 0.3s ease;
      overflow: hidden;
      margin-top: 10px;
    }

    /* Optional: Add a subtle separator between sections */
    #patientFields::before,
    #doctorFields::before {
      content: '';
      display: block;
      height: 1px;
      background: #eee;
      margin: 10px 0;
    }
  </style>
</head>

<body>
  <div class="container">
    <?php
    $page = 'users'; // Set current page for menu highlighting
    include('menu.php');
    ?>
    <div class="dash-body">
      <div class="table-container">
        <div class="header-section">
          <h2>All Users</h2>
          <button onclick="showCreateForm()" class="btn-primary btn">Create User</button>
        </div>

        <table class="user-table">
          <thead>
            <tr>
              <th>Email</th>
              <th>Type</th>
              <th>Name</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $query = "SELECT w.email, w.usertype, 
                     CASE 
                        WHEN w.usertype = 'p' THEN p.pname
                        WHEN w.usertype = 'd' THEN d.docname
                        ELSE 'Admin'
                     END as name
                     FROM webuser w
                     LEFT JOIN patient p ON w.email = p.pemail
                     LEFT JOIN doctor d ON w.email = d.docemail
                     ORDER BY w.usertype, email";

            $result = $database->query($query);
            while ($row = $result->fetch_assoc()) {
              $email = $row['email'];
              $type = $row['usertype'] == 'p' ? 'Patient' : ($row['usertype'] == 'd' ? 'Doctor' : 'Admin');
              $name = $row['name'];
            ?>
              <tr>
                <td>
                  <?php echo $email; ?>
                  <img src="../img/info.svg" class="copy-icon" onclick="copyText('<?php echo $email; ?>')" title="Copy email">
                </td>
                <td><?php echo $type; ?></td>
                <td><?php echo $name; ?></td>
                <td class="action-cell">
                  <?php if ($email != $_SESSION["user"]): ?>
                    <button onclick="showUpdateForm('<?php echo $email; ?>')" class="btn-primary-soft btn">Change Type</button>
                    <button onclick="showDeleteConfirmation('<?php echo $email; ?>', '<?php echo $row['usertype']; ?>')" class="btn-primary-soft btn" style="background-color: #ff6b6b;">Delete</button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Create User Modal -->
  <div id="createModal" class="modal" style="display:none;">
    <div class="modal-content">
      <span class="close" onclick="closeCreateModal()">&times;</span>
      <h3>Create New User</h3>
      <form method="POST" id="createUserForm">
        <div class="form-group">
          <label>User Type</label>
          <select name="usertype" id="userTypeSelect" required onchange="toggleUserFields()">
            <option value="">Select Type</option>
            <option value="p">Patient</option>
            <option value="d">Doctor</option>
            <option value="a">Admin</option>
          </select>
        </div>

        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" required>
        </div>

        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" required>
        </div>

        <!-- Patient-specific fields -->
        <div id="patientFields" style="display:none;">
          <div class="form-group">
            <label>Patient Name</label>
            <input type="text" name="pname">
          </div>
          <div class="form-group">
            <label>Phone</label>
            <input type="text" name="ptel">
          </div>
        </div>

        <!-- Doctor-specific fields -->
        <div id="doctorFields" style="display:none;">
          <div class="form-group">
            <label>Doctor Name</label>
            <input type="text" name="docname">
          </div>
          <div class="form-group">
            <label>Phone</label>
            <input type="text" name="doctel">
          </div>
          <div class="form-group">
            <label>Specialties</label>
            <select name="specialties">
              <?php
              $spec_query = "SELECT * FROM specialties";
              $spec_result = $database->query($spec_query);
              while ($spec = $spec_result->fetch_assoc()) {
                echo "<option value='" . $spec['id'] . "'>" . $spec['sname'] . "</option>";
              }
              ?>
            </select>
          </div>
        </div>

        <button type="submit" name="create" class="btn-primary btn">Create User</button>
      </form>
    </div>
  </div>

  <!-- Update User Type Modal -->
  <div id="updateModal" class="modal" style="display:none;">
    <div class="modal-content">
      <span class="close">&times;</span>
      <form method="POST">
        <input type="hidden" id="updateEmail" name="email">
        <select name="newtype" required>
          <option value="p">Patient</option>
          <option value="d">Doctor</option>
          <option value="a">Admin</option>
        </select>
        <button type="submit" name="update" class="btn-primary btn">Update</button>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="modal" style="display:none;">
    <div class="modal-content">
      <span class="close" onclick="closeDeleteModal()">&times;</span>
      <h3>Confirm Delete User</h3>
      <div id="userDetails">
        <!-- User details will be populated here -->
      </div>
      <div style="display:flex;gap:10px;margin-top:20px;justify-content:flex-end;">
        <button onclick="closeDeleteModal()" class="btn-primary-soft btn">Cancel</button>
        <form method="POST" id="deleteForm" style="margin:0;">
          <input type="hidden" name="email" id="deleteEmail">
          <input type="hidden" name="usertype" id="deleteUserType">
          <button type="submit" name="delete" class="btn-primary-soft btn" style="background-color: #ff6b6b;">Delete</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    function showUpdateForm(email) {
      document.getElementById('updateEmail').value = email;
      document.getElementById('updateModal').style.display = "block";
    }

    // Close modal when clicking the X
    document.getElementsByClassName('close')[0].onclick = function() {
      document.getElementById('updateModal').style.display = "none";
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
      if (event.target == document.getElementById('updateModal')) {
        document.getElementById('updateModal').style.display = "none";
      }
      if (event.target == document.getElementById('createModal')) {
        closeCreateModal();
      }
      if (event.target == document.getElementById('deleteModal')) {
        closeDeleteModal();
      }
    }

    function showCreateForm() {
      document.getElementById('createModal').style.display = "block";
    }

    function closeCreateModal() {
      document.getElementById('createModal').style.display = "none";
      document.getElementById('createUserForm').reset();
      document.getElementById('patientFields').style.display = "none";
      document.getElementById('doctorFields').style.display = "none";
    }

    function toggleUserFields() {
      const userType = document.getElementById('userTypeSelect').value;
      document.getElementById('patientFields').style.display = userType === 'p' ? 'block' : 'none';
      document.getElementById('doctorFields').style.display = userType === 'd' ? 'block' : 'none';
    }

    function copyText(text) {
      navigator.clipboard.writeText(text).then(() => {
        alert('Email copied to clipboard!');
      }).catch(err => {
        console.error('Failed to copy text: ', err);
      });
    }

    async function showDeleteConfirmation(email, usertype) {
      console.log('showDeleteConfirmation called with:', email, usertype);
      try {
        const response = await fetch(`get_user_details.php?email=${encodeURIComponent(email)}&usertype=${encodeURIComponent(usertype)}`);
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const userData = await response.json();
        console.log('User data received:', userData);

        let detailsHTML = `
          <div class="user-details">
              <p><strong>Email:</strong> ${email}</p>
              <p><strong>Type:</strong> ${usertype === 'p' ? 'Patient' : (usertype === 'd' ? 'Doctor' : 'Admin')}</p>
          `;

        if (usertype === 'p') {
          detailsHTML += `
              <p><strong>Name:</strong> ${userData.pname}</p>
              <p><strong>Phone:</strong> ${userData.ptel}</p>
              <p><strong>Total Appointments:</strong> ${userData.appointment_count}</p>
          `;
        } else if (usertype === 'd') {
          detailsHTML += `
              <p><strong>Name:</strong> ${userData.docname}</p>
              <p><strong>Phone:</strong> ${userData.doctel}</p>
              <p><strong>Specialty:</strong> ${userData.specialty}</p>
              <p><strong>Total Patients:</strong> ${userData.patient_count}</p>
              <p><strong>Total Sessions:</strong> ${userData.session_count}</p>
          `;
        }

        detailsHTML += `
          <div class="warning-message" style="margin-top:15px;color:#ff6b6b;">
              <p><strong>Warning:</strong> This action cannot be undone. All associated data will be permanently deleted.</p>
          </div>
      </div>`;

        document.getElementById('userDetails').innerHTML = detailsHTML;
        document.getElementById('deleteEmail').value = email;
        document.getElementById('deleteUserType').value = usertype;
        document.getElementById('deleteModal').style.display = "block";
      } catch (error) {
        console.error('Error fetching user details:', error);
        alert('An error occurred while fetching user details. Please try again.');
      }
    }

    function closeDeleteModal() {
      document.getElementById('deleteModal').style.display = "none";
    }
  </script>
</body>

</html>