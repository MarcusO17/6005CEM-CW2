<?php 

	session_start();
	require_once("connection.php");
	require_once("modules/Logger.php");
	require_once("modules/Analytics.php");

	// Get user info before destroying session
	$userEmail = $_SESSION["user"] ?? null;
	$userType = $_SESSION["usertype"] ?? null;

	if ($userEmail && $userType) {
		// Initialize Logger and Analytics
		$logger = Logger::getInstance($database);
		$analytics = new Analytics($database);

		// Log the logout
		$logger->setUser($userEmail, $userType)
			   ->log(
				   Logger::CATEGORY_AUTH,
				   'LOGOUT',
				   [
					   'user_type' => $userType,
					   'ip_address' => $_SERVER['REMOTE_ADDR'],
					   'user_agent' => $_SERVER['HTTP_USER_AGENT'],
					   'session_duration' => isset($_SESSION['login_time']) ? 
						   time() - strtotime($_SESSION['login_time']) : null
				   ],
				   Logger::LEVEL_INFO
			   );

		// Track logout event
		$analytics->logUserEvent(
			'AUTH',
			'LOGOUT',
			ucfirst($userType) . ' Logout',
			1,
			[
				'user_type' => $userType,
				'logout_time' => date('Y-m-d H:i:s'),
				'session_duration' => isset($_SESSION['login_time']) ? 
					time() - strtotime($_SESSION['login_time']) : null
			]
		);
	}

	// Clear session
	$_SESSION = array();

	if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time()-86400, '/');
	}

	session_destroy();

	// redirecting the user to the login page
	header('Location: login.php?action=logout');

 ?>