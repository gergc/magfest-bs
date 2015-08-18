<?php
// BarcodeSystem for MAGFest 2.0
// Designed for MAGFest 9 and BEYOND! by gergc and LANJesus
// File: printers.php
// Purpose: Display available network printers. Add/delete/edit printer list.

	include('header.php');
	include('semaphore.php');
	
	$auth = false;
	if(isset($_SESSION['login_auth'])){
		$auth = $_SESSION['login_auth'];
	}

	$failed = false;
	if(isset($_SESSION['failed'])){
        $failed = $_SESSION['failed'];
    }
	
	$teststatus = '';
	if(isset($_SESSION['teststatus'])){
        $teststatus = $_SESSION['teststatus'];
    }

	
	if($auth==false || $_SESSION['user_level'] <= 1){
		echo 'access denied';
	}
	else {
	
		echo $teststatus;
		
		echo '<fieldset style="width: 750px; height: 55px;">';
		echo "<legend>Printer List</legend>";                                   
		echo '<div><br />';
		echo '<table border class="list">
		<tr class="header"><td>ID</td>
		<td>IP</td>
		<td>Location</td>
		<td style="width: 40px">Status</td>
		<td style="width: 40px">Action</td>
		</tr>';
		
		$conn = connectDB();
		$q = "SELECT * FROM printers,mag_names WHERE printers.roomid=mag_names.id";
		$res = mysql_query($q,$conn);
		$c = 0;
		while($row = mysql_fetch_array($res))
		{
			$c++;
			$id = $row[0];
			$ip = $row[1];
			$roomid = $row[6];
			$label = $row[4];

			
			$printerid = $id.'printerlock';
			
			//Check if the printer is locked. If it is, wait for it to become unlocked.
			while(true)
			{
				if($_SESSION[$printerid])
					sleep(2);
				else
					break;
			}
		
			$_SESSION[$printerid] = true;
		
		
			$message = "^ee\r\n";
			$file      = @fsockopen ($ip, 4001, $errno, $errstr, 1);
			$status    = 0;
			if (!$file) $status = -1;  
			else {
				fwrite($file,$message);
				sleep(1);
				$status = fread($file, 100);
				fclose($file);
			}
			
			usleep(250);
			$_SESSION[$printerid] = false;
			
			switch($status)
			{
				case '-1': $status_ = '<red>Offline</red>'; break;
				default: $status_ = "<green>Online ( Error $status)</green>"; break;
			}
			
			
			echo "<td>$id</td><td>$ip</td><td>$roomid Room (Label #$label)</td><td>$status_</td>";
			echo "<td><button class=\"big\" onClick=\"parent.location='netprint.php?test=$ip'\">Print Test Label</button>";
			echo "</td></tr>";
		}
		if($c==0)
			echo '</table><table border class="list"><tr><td>No Printers Added</td></tr></table>';
		
		
		echo '</table>';
		
		/*
		echo '<form autocomplete="off" action="printers.php" name="additem" method="POST">';
		echo '<fieldset style="width: 750px; height: 55px;">';
		echo "<legend>Add Printer</legend>";					
		echo '<div>';

		
		echo 'Printer Type: <select id="types" name="itemtype" class="form-select required" id="edit-field-data-loc-key" onChange="showHide(this)" autofocus ><br />';
		echo '<option value="0">select...</option>';
		echo '<option value="1">Intermec PC4</option>';
		echo '</select><br />'; 
		
		echo '<div id="typeHolder">';
		

		echo '<div id="1"> 
		<span style="font-weight:bold"></span><br />
		<big>Printer IP: </big>
		<input type="text" maxlength="140" name="IP" style="width:300px"   /><br />
		<big>Location:</big>';
		//**
		
		
		$q = "SELECT * FROM mag_names";
		$res = mysql_query($q,$conn);
		echo '<SELECT id="locations" name="printerloc" VALUE="">select...';
		
		
		$c=0;
				
		while($row = mysql_fetch_row($res))
		{			
			echo '<OPTION VALUE="'.$row[0].'"> '.$row[1].'</OPTION>';
		}
		echo '</SELECT>';
		
		
		//***
		
		
		//<input type="text" maxlength="140" name="location" style="width:600px"   />		
		echo '</div>';
		
		echo '</div>';
		
		echo '<input type="hidden" name="badge" value="" />';
		
		echo '<input type="submit" value="Submit" />';						
		echo '</div>';
		echo '</fieldset>';
		echo '</form>';
		*/
		echo '*Edit printers using MySQL DB Admin<br />';
		echo '*On the fly printer editing is a to-do feature for BS 2.5 (M10)';
		
		mysql_close($conn);
		
		
	}
	
?>

</body>
</html>
