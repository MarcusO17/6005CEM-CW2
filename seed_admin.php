<?php
// Import required files
include("connection.php");
require "utils/encryption-util.php";

use function Utils\encrypt;

// Admin credentials
$email = 'admin@edoc.com';
$password = '123QWEqwe!';

// Hash password using Argon2id (same as create-account.php)
$hashedpassword = password_hash($password, PASSWORD_ARGON2ID, ['memory_cost' => 19456, 'time_cost' => 2, 'threads' => 1]);

try {
  // Start transaction
  $database->begin_transaction();

  // Delete existing admin account if exists
  $database->query("DELETE FROM admin WHERE aemail = 'admin@edoc.com'");
  $database->query("DELETE FROM webuser WHERE email = 'admin@edoc.com'");

  // Insert into admin table
  $stmt = $database->prepare("INSERT INTO admin (aemail, apassword) VALUES (?, ?)");
  $stmt->bind_param("ss", $email, $hashedpassword);
  $stmt->execute();

  // Insert into webuser table
  $stmt = $database->prepare("INSERT INTO webuser (email, usertype) VALUES (?, 'a')");
  $stmt->bind_param("s", $email);
  $stmt->execute();

  // Commit transaction
  $database->commit();

  echo "Admin account created successfully!\n";
  echo "Email: admin@edoc.com\n";
  echo "Password: 123QWEqwe!\n";
} catch (Exception $e) {
  // Rollback transaction on error
  $database->rollback();
  echo "Error creating admin account: " . $e->getMessage() . "\n";
}
