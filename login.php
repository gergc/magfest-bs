<?php
// BarcodeSystem for MAGFest 2.0
// Designed for MAGFest 9 and BEYOND! by gergc and LANJesus
// File: login.php
// Purpose: Validates the user and sends the user back to the login page.

	include('header.php');

	//Authenticate with DB
	
	$badge = $_POST['badge'];
	
	$conn = connectDB();
	
	$q = "SELECT badgeid, access, roomid FROM users WHERE hash='".$badge."'";
	$res = mysql_query($q,$conn);
	$row = mysql_fetch_array($res);
	$badge = $row['badgeid'];
	$access = $row['access'];
	
	if(isset($row['badgeid']))
	{	
		$_SESSION['login_auth'] = true;
		$_SESSION['user_badge'] = $badge;
		$_SESSION['user_level'] = $access;
		$_SESSION['roomid'] = $roomid;
	}
	else {
		$_SESSION['login_auth'] = false;
		$_SESSION['failed'] = true;
	}
	header ("Location: index.php"); 

?>
