<html> 
<head>
<LINK href="style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="jQuery-1.4.2.min.js"></script>
<script type="text/javascript"> 
        $(function() 
        { 
            $("#typeHolder > div").hide(); 
            $("#types").change( function(){ 
                var $el= $('#' + $(this).val() ); 
                if ( $("#typeHolder > div:visible").length == 0) { 
                    $el.show('fast') 
                } 
                else{ 
                    $("#typeHolder > div:visible").hide('fast', function(){ $el.show('fast'); } ) 
                } 
            }); 
        }); 
    </script> 
</head> 
<title>MAGFest BS 2.0</title>  
<body> 
<h2>MAGFest BarcodeSystem 2.0 (BS/MCAS)</h2> 
<p>by gergc - last modified 
<?php

	//Checks if user is using Chrome. We require HTML5, and Chrome works great. So, BS2.0 will only be used on Chrome
	//$haystack = $_SERVER['HTTP_USER_AGENT'];
	//$needle = 'Chrome';
	//if (strlen(strstr($haystack,$needle))==0) {
	//	exit('<br /><h2>Please use Google Chrome. HTML5 Required</h2></body></html>');
	//}

	include('database.php');
	session_start();

	// set timeout period in seconds - 1 hour
	$inactive = 3600;

	// check to see if $_SESSION['timeout'] is set
	if(isset($_SESSION['timeout']) ) {
		$session_life = time() - $_SESSION['timeout'];
	if($session_life > $inactive)
	{ 	session_destroy(); header("Location: logoutinactive.php"); }
	}
	$_SESSION['timeout'] = time();
	
	
	global $moddate;
	echo $moddate.'</p>';
	$auth = false;
	if(isset($_SESSION['login_auth'])){
		$auth = $_SESSION['login_auth'];
	}
        if($auth==true){
		
        	//echo '<a href="logout.php">Logout ';
 	        //echo 'Staff '.$_SESSION['user_badge'];
		   //echo '</a> / <a href="index.php">Main Menu</a><br /><br />';
		   $userbadge = $_SESSION['user_badge'];
		   echo "<button class=\"big\" onClick=\"parent.location='logout.php'\">Logout Staff $userbadge</button>";
		   echo '<button class="big" onClick="parent.location=\'index.php\'">Main Menu</button><br />';
		$conn = connectDB();

		//Select printer
		$show = strpos($_SERVER['SCRIPT_NAME'],'index.php');		 
		//$show2 = strpos($_SERVER['SCRIPT_NAME'],'magfest.php');		 
		//$show3 = strpos($_SERVER['SCRIPT_NAME'],'badge.php');		 
		
		if($show){
			$currentbadge = $_SESSION['user_badge'];
			$q = "SELECT * FROM printers,mag_names,users WHERE printers.roomid=mag_names.id AND badgeid=$currentbadge";
			$res = mysql_query($q,$conn);
			//<OPTION VALUE="">select...		
			echo 'Printer Location:<FORM NAME="nav2"><DIV>
			<SELECT NAME="SelectURL2" onChange=
			"document.location.href=document.nav2.SelectURL2.options[document.nav2.SelectURL2.selectedIndex].value">';
			
			$c=0;
					
			while($row = mysql_fetch_row($res))
			{
				$printerroomid = $row[11];
				$label = $row[4];
				$_SESSION['printerid'] = $printerroomid;
				$c++;				
				echo '<OPTION VALUE="users.php?setprint='.$c.'&request='.$_SERVER['REQUEST_URI'].'"'; if($printerroomid==$c) echo 'SELECTED';echo '>'.$row[6].'(#'.$label.')';
				
				$printid = $c.'printerlock';
				$_SESSION[$printid] = false;
				
				//echo '<OPTION VALUE="" />'.$row[5].'';
				$printers[$c] = $row[11];
				if($printerroomid==$c) {
					$_SESSION['printerid'] = $row[0];
					$_SESSION['printername'] = $row[6];	
					$_SESSION['printerip'] = $row[1];
					$_SESSION['printerlabel'] = $label;
				}
			}
			echo '</SELECT></DIV></FORM>';
			
		}
		else {
			$label = $_SESSION['printerlabel'];
			echo '<h3>Active Printer:  '.$_SESSION['printername']." (#$label)</h3>";
		}
		
		
		//Select station
		$show = strpos($_SERVER['SCRIPT_NAME'],'index.php');		 
		$show2 = strpos($_SERVER['SCRIPT_NAME'],'magfest.php');		 
		$show3 = strpos($_SERVER['SCRIPT_NAME'],'badge.php');		 
		
		if($show){
			$currentbadge = $_SESSION['user_badge'];
			$q = "SELECT * FROM cat_names,users WHERE badgeid=$currentbadge";
			$res = mysql_query($q,$conn);
			//<OPTION VALUE="">select...		
			echo 'Check-In/Out Station Location:<FORM NAME="nav1"><DIV>
			<SELECT NAME="SelectURL1" onChange=
			"document.location.href=document.nav1.SelectURL1.options[document.nav1.SelectURL1.selectedIndex].value">					';
			
			$c=0;
					
			while($row = mysql_fetch_row($res))
			{
				$activeroomid = $row[5];
				$_SESSION['roomid'] = $activeroomid;
				$c++;
				echo '<OPTION VALUE="users.php?setcat='.$c.'&request='.$_SERVER['REQUEST_URI'].'"'; if($activeroomid==$c) echo 'SELECTED';echo '>'.$row[1];
				$rooms[$c] = $row[1];
				if($activeroomid==$c)
					$_SESSION['roomname'] = $row[1];
			}
			echo '</SELECT></DIV></FORM>';
			
		}
		else {
			if($show3)
			{
				echo '<h3>Active Station: '.$_SESSION['roomname'].' Room.</h3>';
			}
		}
	}
?>
