<?php
// BarcodeSystem for MAGFest 2.0
// Designed for MAGFest 9 and BEYOND! by gergc and LANJesus
// File: users.php
// Purpose: Allows admins add users (and other admins) to badges for login.
	
	include('header.php');
		
	$userfunctions = 0;
	if(isset($_GET['func']))
	{
		$userfunctions = 1;
		$func = $_GET['func'];
		$targetbadge = $_GET['badge'];
	}
	
	
	$scanned_badge = 'null';
    if(isset($_POST['badge'])){
        $scanned_badge = $_POST['badge'];
    }

	$verify_step = 0;
    if(isset($_POST['verify_step1'])){
		$verify_step = 1;
    }
	if(isset($_POST['verify_step2'])){
		$verify_step = 2;
	}
	
	$setcat = false;
	if(isset($_GET['setcat'])){
		$setcat = $_GET['setcat'];
	}
	
	$setprint = false;
	if(isset($_GET['setprint'])){
		$setprint = $_GET['setprint'];
	}
	
	$error = false;
	if(isset($_SESSION['error'])){
		$error = $_SESSION['error'];
		unset($_SESSION['error']);
    }
	
	if($_SESSION['login_auth'] == true) {
		if($error!=false) { echo '<span style="color:red;font-weight:bold"><big>Error: '.$error.'</span></big><br /><br />'; }
		if($setcat!=false)
		{
			$conn = connectDB();
			$bid = $_SESSION['user_badge'];
			$q = "UPDATE users SET roomid = $setcat WHERE badgeid=$bid";
			$res = mysql_query($q,$conn);			
			mysql_close($conn);
			$redir = $_GET['request'];
			$_SESSION['roomid'] = $setcat;
			header("Location: $redir");
		}

		if($setprint!=false)
		{
			$conn = connectDB();
			$bid = $_SESSION['user_badge'];
			$q = "UPDATE users SET printerid = $setprint WHERE badgeid=$bid";
			$res = mysql_query($q,$conn);			
			mysql_close($conn);
			$redir = $_GET['request'];
			$_SESSION['printerid'] = $setprint;
			header("Location: $redir");
		}

		if($_SESSION['user_level'] < 1)
		{
		}
		else {
			
			if($userfunctions)
			{
				switch($func)
				{
					case 'upgrade': {
						$q = "UPDATE users SET access = 1 WHERE badgeid=$targetbadge";
						$res = mysql_query($q,$conn);			
						mysql_close($conn);			
						header('Location: users.php');							
					} break;
					
					case 'downgrade': {
						$q = "UPDATE users SET access = 0 WHERE badgeid=$targetbadge";
						$res = mysql_query($q,$conn);			
						mysql_close($conn);				
						header('Location: users.php');	
					} break;
					
					case 'reprint': {
						$q = "SELECT b.name,u.hash FROM users u,badges b WHERE b.badgeid = u.badgeid AND u.badgeid = $targetbadge";
						$res = mysql_query($q,$conn);
						$row = mysql_fetch_array($res);
						$name = $row['name'];
						$hash = $row['hash'];
						
						$_SESSION['adminprint'] = 1;
						$_SESSION['name'] = $name;
						$_SESSION['hash'] = $hash;						
						$_SESSION['scanned_badge'] = $targetbadge;
						header('Location: users.php');						
					} break;
					
					case 'remove': {
						$q = "DELETE FROM users WHERE badgeid=$targetbadge";
						$res = mysql_query($q,$conn);			
						mysql_close($conn);				
						header('Location: users.php');
					}break;
				}
			}
		
			if($verify_step==1)
			{
				//check input
				
				$scanned_badge = filter_var($scanned_badge, FILTER_SANITIZE_NUMBER_INT);
				
				if($scanned_badge=='')
				{
					$_SESSION['error'] = 'E14 Invalid badge.';
					header('Location: users.php');
					exit();
				}
				
				$scanned_badge = 'ADMIN'.$scanned_badge;
				
				$conn = connectDB();
				$q = "SELECT access,badgeid FROM users WHERE hash = '$scanned_badge'";
				$res = mysql_query($q,$conn);
				$row = mysql_fetch_array($res);
				$badgeid = $row['badgeid'];
				$access = $row['access'];
				if($access==0)
				{
					$_SESSION['error'] = 'E15 You do not have access to this function. Please try again and scan a valid admin badge.';
					header('Location: users.php');					
					exit();
				}
				else
				{
					echo '<form action="users.php" method="POST">';
					echo '<fieldset style="width: 350px; height: 55px;">';
					echo '<legend>Admin Verified</legend>';
					echo 'Please <b>TYPE</b> the staff badge number you want to assign as a new BS user.<br/>';
					echo '<div>';
					echo '<input type="password" name="badge" autofocus />';
					echo '<input type="hidden" name="verify_step2">';
					echo '</div>';
					echo '</fieldset>';
					echo '</form>';					
				}
			}
			else if($verify_step==2)
			{
				$conn = connectDB();
				if($scanned_badge=='')
				{
					$_SESSION['error'] = 'E14 Invalid badge.';
					header('Location: users.php');
					exit();
				}
				//Check if user already exists
				$q = "SELECT access,badgeid FROM users WHERE badgeid = '$scanned_badge'";
				$res = mysql_query($q,$conn);
				$row = mysql_fetch_array($res);
				//$badgeid = $row['badgeid'];
				$access = $row['access'];
				//echo "badgeid $badgeid access $access";

				//Check if badge exists
				$q = "SELECT badgeid FROM badges WHERE badgeid = '$scanned_badge'";
				$res2 = mysql_query($q,$conn);
				if(mysql_num_rows($res2)==0)
				{
					$_SESSION['error'] = "E19 Badge #$scanned_badge - <br />Badge does not exist for inventory...<br />Please register this badge before setting it as an admin.";
					header('Location: users.php');
					exit();
				}
				
				if($access!=null||mysql_num_rows($res)>0)
				{
					$_SESSION['error'] = "E16 Badge #$scanned_badge - User Already Exists in System.";
					header('Location: users.php');
					exit();
				}
				if($scanned_badge>300)
				{
					$_SESSION['error'] = "E17 Badge #$scanned_badge - Not A Staff Badge";
					header('Location: users.php');
					exit();
				}
				else
				{
					//User does not exist already, add the badge to the system after checking if that badge actually exists in Uber
					
					$uconn = connectUber();
					$q = "SELECT first_name, last_name, phone FROM Attendee WHERE badge_num =".$scanned_badge;
					$res = mysql_query($q,$uconn);
					$row = mysql_fetch_array($res);
					$name = $row['first_name'].' '.$row['last_name'];
					$phone = $row['phone'];
					if(mysql_num_rows($res)==0)
					{
						mysql_close($uconn);
						$_SESSION['error'] = 'E01 Staff Badge does not exist in Ubersystem.<br /> ';
						header('Location: users.php');
						exit();
					}
					else {
						$hashsrc = $scanned_badge.'SuperSecretHashSalt';
						$hashnum = abs(crc32($hashsrc));
						$hash = 'ADMIN'.$hashnum;
						
						$q = "INSERT INTO users(badgeid, access, hash, roomid, printerid) VALUES ('$scanned_badge','0','$hash','1','1')";
						$res = mysql_query($q,$conn);
						mysql_close($conn);
						
						$_SESSION['adminprint'] = 1;
						$_SESSION['name'] = $name;
						$_SESSION['hash'] = $hash;
						
						$_SESSION['scanned_badge'] = $scanned_badge;

						header('Location: users.php');
					}
					mysql_close($uconn);					
				}
			}
			else if($scanned_badge=='null')
			{
				if(isset($_SESSION['adminprint']))
				{
					include('netprint.php');
					unset($_SESSION['adminprint']);
				}
				echo '<fieldset style="width: 500px; height: 55px;">';
				echo "<legend>User List</legend>";
				echo '<div><br />';
				echo '<table border class="list">
					<tr class="header"><td>Badge ID</td>
					<td>Name</td><td>Access</td><td>Actions</td>
					</tr>';
		
				$conn = connectDB();
				$q = "SELECT access,users.badgeid,name FROM users,badges WHERE users.badgeid=badges.badgeid";
				$res = mysql_query($q,$conn);
				
				while($row = mysql_fetch_assoc($res))
				{
					
					$badgeid = $row['badgeid'];
					$access = $row['access'];
					switch($access)
					{
						case 0: $access_str = 'User'; break;
						case 1: $access_str = 'Admin'; break;
						case 2: $access_str = 'Admin*'; break;
					}
					$name = $row['name'];
					
					if($access==2) //If superuser, cannot delete or reprint badge label
					{
						echo '<tr><td>'.$badgeid.'</td><td>'.$name.'</td><td>'.$access_str.'</td><td>None</td></tr>';
					}
					else {
						echo '<tr><td>'.$badgeid.'</td><td>'.$name.'</td><td>'.$access_str.'</td><td>';
						
						if($access==0)
							echo "<button class=\"kindabig\" onClick=\"parent.location='users.php?func=upgrade&badge=$badgeid'\">Upgrade to Admin</button>";						
						if($access==1)
							echo "<button class=\"kindabig\" onClick=\"parent.location='users.php?func=downgrade&badge=$badgeid'\">Downgrade to User</button>";						
						
						echo "<button class=\"kindabig\" onClick=\"parent.location='users.php?func=reprint&badge=$badgeid'\">Reprint Admin Badge</button>";
						echo "<button class=\"kindabig\" onClick=\"parent.location='users.php?func=remove&badge=$badgeid'\">Remove User</button>";						
						echo'</td></tr>';
					}
				}
						
				echo '</table>';

				echo '<form action="users.php" method="POST">';
				echo '<fieldset style="width: 350px; height: 55px;">';
				echo '<legend>Add User</legend>';
				echo 'Please scan an admin badge to verify credentials.';
				echo '<div>';
				echo '<input type="password" name="badge" autofocus />';
				echo '<input type="hidden" name="verify_step1">';
				echo '</div>';
				echo '</fieldset>';
				echo '</form>';
				echo '</div>';
				echo '</fieldset>';
			}
			else {
			}
		}
	}
	

?>


</body>
</html>
