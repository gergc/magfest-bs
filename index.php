<?php
// BarcodeSystem for MAGFest 2.0
// Designed for MAGFest 9 and BEYOND! by gergc and LANJesus
// File: index.php
// Purpose: If the user is logged in, presents a menu. If not, starts the log in sequence.

	include('header.php');
	$auth = false;
	if(isset($_SESSION['login_auth'])){
		$auth = $_SESSION['login_auth'];
	}

	$failed = false;
	if(isset($_SESSION['failed'])){
        $failed = $_SESSION['failed'];
    }
	
	$error = false;
	if(isset($_SESSION['error'])){
		$error = $_SESSION['error'];
		unset($_SESSION['error']);
    }
	
	unset($_SESSION['scanned_badge']);
	unset($_SESSION['badgehash']);
	
	if($auth==false){
		echo '<form action="login.php" method="POST">';
		echo '<fieldset style="width: 350px; height: 55px;">';
		echo '<legend>System Login</legend>';
		echo 'Please scan your BarcodeSystem Login Badge';
		//echo '<br />If you do not have one, just scan your normal badge barcode and you will be registered.';
		echo '<div>';
		if($failed) { echo '<span style="color:red;font-weight:bold">Login Failed - Try Again</span>'; }
		echo '<input type="password" name="badge" autofocus />';
		echo '</div>';
		echo '</fieldset>';
		echo '</form>';
	}
	else{
		if($_SESSION['user_level'] >= 1) { echo ' <b>[ADMIN MODE]</b><br /><br />'; }
		if($_SESSION['user_level'] == 0) { echo ' <b>[USER MODE]</b><br /><br />'; }
		if($error!=false) { echo '<span style="color:red;font-weight:bold"><big>Error: '.$error.'</span></big><br /><br />'; }
		echo 'Welcome to the new BSMCAS! <span style="font-weight:bold"><br />To manage users inventory, scan the badge label.<br />To register a new badge, type badge number in.</span><br/>';
		echo '<br />';
		echo '<fieldset style="width: 500px; height: 55px;">';
        echo '<legend>Scan a Badge</legend>';
		echo '<form action="badge.php" method="POST">';
		echo '<input type="password" name="badge" autofocus />';
		echo '</form>';
		//echo '<button accesskey="r" onClick="parent.location=\'regbadge.php\'"><span class="uline">R</span>egister Badge</button>';
		//echo '<button accesskey="a" onClick="parent.location=\'itemadd.php\'"><span class="uline">A</span>dd Items</button>';
		//echo '<button accesskey="c" onClick="parent.location=\'itemcheck.php\'"><span class="uline">I</span>tem Check In/Out</button>';
		echo '-or-<br /><br />';
		echo '<button class="wide" accesskey="m" onClick="parent.location=\'magfest.php\'"><span class="uline">M</span>AGFest Inventory Management</button>';
		echo '</fieldset><br /><br /><br /><br />';
		
	    echo '<br /><br /><br /><br /><br /><br />';
		if($_SESSION['user_level'] >= 1) {
			echo '<fieldset style="width: 500px; height: 55px;">';
			echo '<legend>Admin Menu</legend>';
			echo '<button class="big" accesskey="p" onClick="parent.location=\'printers.php\'"><span class="uline">P</span>rinters</input>';
			echo '<button class="big" accesskey="u" onClick="parent.location=\'users.php\'"><span class="uline">U</span>sers</button>';
			echo '<button class="big" accesskey="e" onClick="parent.location=\'reports.php\'">R<span class="uline">e</span>ports</button>';
			echo '<button class="wide" accesskey="r" onClick="parent.location=\'category.php\'"><span class="uline">R</span>oom Management</button>';
			//echo '<button class="big" accesskey="d" onClick="parent.location=\'index.php\'"><span class="uline">D</span>ongs</button>';
			//echo '<button class="big" accesskey="r" onClick="parent.location=\'index.php\'">De<span class="uline">r</span>p</button>';
			//echo '<button class="big" accesskey="j" onClick="parent.location=\'index.php\'"><span class="uline">J</span>izz</button>';
			echo '</fieldset>';
		}
	}
?>

</body>
</html>
