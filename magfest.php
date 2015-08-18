<?php 

// BarcodeSystem for MAGFest 2.0
// Designed for MAGFest 9 and BEYOND! by gergc and LANJesus
// File: magfest.php
// Purpose: Allows users to manage MAGFest owned equipment and games
	
	include('header.php'); 

		
	$skip = false;
	
	
	$error = false;
	if(isset($_SESSION['error'])){
		$error = $_SESSION['error'];
		unset($_SESSION['error']);
    }
	
	if($error!=false) { echo '<span style="color:red;font-weight:bold"><big>Error: '.$error.'</span></big><br /><br />'; }
	
	$sorttype = 0;
	if(isset($_GET['sort']))
	{
		$sorttype = $_GET['sort'];
	}
	
	if($sorttype==0)
	{
		$sorttype = $_SESSION['roomid'];
	}
	
	$newitem = false;
	if(isset($_POST['newitem']))
	{
		$newitem = true;
	}
	
	//**************
	
	$auth = false;
	if(isset($_SESSION['login_auth'])){
		$auth = $_SESSION['login_auth'];
	}

	$roomid = 'null';
	if(isset($_POST['roomid'])){
		$roomid = $_POST['roomid'];
	}
	
	
	
	$printing = 0;
	if(isset($_SESSION['printing']))
	{
		$printing = 1;		
		unset($_SESSION['printing']);
	}
	
    $sub_reg = false;
        if(isset($_POST['regbadge'])){
                $sub_reg = true;
    }
	
	$checkchange = 0;
	if(isset($_POST['checkin'])||isset($_POST['checkout']))
	{
		$checkchange = 1;
	}
	
	$itemreprint = 0;
	if(isset($_POST['itemreprint']))
	{
		$itemreprint = $_POST['itemreprint'];
	}
	
	$override = 0;
	if(isset($_POST['override']))
	{
		$override = $_POST['override'];		
	}
	
	//print_r($_POST);
	if($auth) {
		if($newitem)
		{
			print_r($_POST);
			$conn = connectDB();
			$q = "SELECT * FROM mag_category,mag_names WHERE roomid = $sorttype AND mag_category.roomid = mag_names.id";
			$res = mysql_query($q,$conn);
			$c = 0;
			while($row = mysql_fetch_array($res))
			{	
				$c++;
				$roomid = $row[1];
				$roomid_str = $row[5];
				$names[$c] = $row[2];
				$types[$c] = $row[3];	
				$catid[$c] = $row[0];					
			}
			
			$itemnum = $_SESSION['items']+1;
			$itemdesc = array();
			
			$itemcount = 0;
			$totaldesccount = 0;
			
			foreach($types as $key => $value)
			{
				$types_ex = explode(';',$value);
				
							
				foreach($types_ex as $index => $val)
				{
					$cid = $catid[$key];
					$pkey = $cid.'-'.$index;
					
					if($_POST[$pkey]!='')
					{
						$totaldesccount = count($types_ex);	
						$itemcount++;
						$cid_ = $catid[$key];
						$roomname = $names[$key];
						$hashsrc = $itemnum.'-'.$cid;
						$hash = abs(crc32($hashsrc));
						$itemdata = $val.': '.$_POST[$pkey];
						array_push($itemdesc, $itemdata);
					}
				}
			}
			
			echo $itemcount.'*'.$totaldesccount;
		
			if($itemcount!=$totaldesccount||$itemcount==0&&$totaldesccount==0)
			{
				$_SESSION['newbadge'] = true;
				$_SESSION['error_check'] = 2;
				//$_SESSION['scanned_badge'] = $_POST['badge'];
				header("Location: magfest.php");
				exit();
			}
			
			//id, itemdesc, checked, hash, itemtype, roomtype
			
			$gluedesc = implode(' ',$itemdesc);
			
			//print_r($itemdesc);
			
			$q = "INSERT INTO mag_items(id,itemdesc,checked,hash,itemtype,roomtype) VALUES ('$itemnum','$gluedesc','1','$hash','$cid_','$roomid')";
			$res = mysql_query($q,$conn);
			
			unset($_SESSION['items']);
			mysql_close($conn);	
			
			if($res) { 
				$_SESSION['newbadge'] = true;
				//$_SESSION['scanned_badge'] = $_POST['badge'];				
				$_SESSION['roomid_print'] = $_POST['roomid'];				
				$_SESSION['magitemprint'] = 1;
				$_SESSION['itemnum'] = $itemnum;
				$_SESSION['itemdesc'] = $gluedesc;
				$_SESSION['itemtype'] = $roomname;
				$_SESSION['hash'] = $hash;
				$_SESSION['roomstr'] = $roomid_str;
				$_SESSION['printing'] = 1;
				header ("Location: magfest.php");  					
			}
			else { 
				$_SESSION['error'] = 'E13 Failed to Register Item - Please contact Greg or try again.';
				header('Location: index.php');
			 }
			
		}
		
		//New stuff********************************************************
		
		if($checkchange)
		{
			//Check for check out or in
			$checktype = -1;
			if(isset($_POST['checkin'])){
				$itemnum = $_POST['checkin'];
				$checktype = 1;
			}
			else if(isset($_POST['checkout'])){
					$itemnum = $_POST['checkout'];
					$checktype = 0;
			}
			
			//Check for step 2
			$step2 = false;
			if(isset($_POST['check_step2'])){
                $step2 = true;
			}
			
			if($step2){
				//Verify that label was scanned properly and change status in the DB if so. 1453673927
				$roomid = $_SESSION['roomid'];
				$conn = connectDB();
				$q = "SELECT hash FROM mag_items WHERE roomtype = $roomid AND id = $itemnum";
				$res = mysql_query($q,$conn);
				$row = mysql_fetch_array($res);
				$hash = $row['hash'];				
				$scanned_hash = $_POST['itemlabel'];
				
				//echo 'S: '.$roomid.' I: '.$itemnum;
				
				if($hash!=$scanned_hash)
				{					
					$_SESSION['newbadge'] = true;
					$_SESSION['error_check'] = 1;
					//$_SESSION['roomid'] = $roomid;
					header ("Location: magfest.php");  
				}
				else {
					$conn = connectDB();
					$q = "UPDATE mag_items SET checked = '".$checktype."' WHERE roomtype = $roomid AND id = $itemnum";
					//echo $q;
					$res = mysql_query($q,$conn);
					//$row = mysql_fetch_array($res);
					
					
					$_SESSION['newbadge'] = true;
					$_SESSION['error_check'] = 0;
					//$_SESSION['roomid'] = $roomid;
					header ("Location: magfest.php");  
				}
				
			}
			else {				
				//Prompt user for the label badge
				$conn = connectDB();
				$q = "SELECT * FROM mag_items,category WHERE mag_items.id = $itemnum AND category.id=mag_items.itemtype";
				$res = mysql_query($q,$conn);
				$row = mysql_fetch_array($res);
				$itemdesc = $row[1];
				$itemtype = $row[8];
				$roomid = $row[5];
				echo '<form action="magfest.php" method="POST">';
				echo '<fieldset style="width: 350px; height: 55px;">';
				if(!$checktype) 
				{
					echo '<legend>Verify Check-Out</legend>';
					echo '<input type="hidden" name="checkout" value="'.$itemnum.'"  />';
				}				
				else {
					echo '<legend>Verify Check-In</legend>';
					echo '<input type="hidden" name="checkin" value="'.$itemnum.'"  />';
				}
				echo '<span style="font-weight:bold">Please scan item label </span>and verify that the item matches the description and type below.';			
				echo '<div>';
				echo '<input type="password" name="itemlabel" autofocus />';
				echo '<input type="hidden" name="roomid" value="'.$roomid.'" />';
				echo '<input type="hidden" name="check_step2" />';
				echo '<br /><big><span style="font-weight:bold">';
				echo $itemtype.'<br /></span>';
				echo $itemdesc;
				echo '</big></div>';
				echo '</fieldset>';
				echo '</form>';
			}
		}
		else if($override==1){

			$roomid = $_POST['roomid'];
			$itemnum = $_POST['itemnum'];
			
			$conn = connectDB();
			//$q = "SELECT * FROM items WHERE badgeid = $scanned_badge AND id = $itemnum";
			//$q = "SELECT * FROM mag_items,mag_category WHERE mag_items.roomtype = $roomid AND mag_items.id = $itemnum AND mag_category.id=mag_items.itemtype";
			$q = "SELECT * FROM mag_items,mag_category,mag_names WHERE mag_items.roomtype = $roomid AND mag_items.id = $itemnum AND mag_category.id=mag_items.itemtype AND mag_names.id = mag_category.roomid";
			$res = mysql_query($q,$conn);
			$row = mysql_fetch_array($res);
			$itemdesc = $row[1];
			$itemid = $row[0];
			$itemtype = $row[8];
			$roomid = $row[5];
			$roomid_str = $row[11];
			//$badgetype = calcBadgeType($row[1]);
			
			echo '<form action="magfest.php" method="POST">';
			echo '<fieldset style="width: 350px; height: 55px;">';
			echo '<legend>Admin Override</legend>';				
			echo '<div>';
			echo 'Please scan admin badge for label reprint. <br />';			
			echo '<input type="password" name="adminbadge" autofocus />';
			echo '<input type="hidden" name="itemnum" value="'.$itemnum.'"  />';								
			echo '<input type="hidden" name="roomid" value="'.$roomid.'" />';
			echo '<input type="hidden" name="override" value="2" />';
			echo '<br /><big><span style="font-weight:bold">';
			echo $roomid_str.' Item #'.$itemid;
			echo '<br /> '.$itemtype.'<br /></span>';
			echo $itemdesc;
			echo '</big></div>';
			echo '</fieldset>';
			echo '</form>';
			$skip = true;
		}
		else if($override==2){
				//verify admin user
				$adminbadge = $_POST['adminbadge'];
				$roomid = $_POST['roomid'];	
				$itemnum = $_POST['itemnum'];
				$conn = connectDB();	
				$q = "SELECT access FROM users WHERE hash='".$adminbadge."'";
				
				$res = mysql_query($q,$conn);
				$row = mysql_fetch_array($res);				
				$access = $row['access'];
				mysql_close();
				
				if($access>=1){
					//admin verified - reprint label.
							$conn = connectDB();
							//$q = "SELECT * FROM items WHERE badgeid = $badge AND id = $itemnum";
							$q = "SELECT * FROM mag_items,mag_category,mag_names WHERE mag_items.roomtype = $roomid AND mag_items.id = $itemnum AND mag_category.id=mag_items.itemtype AND mag_names.id = mag_category.roomid";
							$res = mysql_query($q,$conn);
							$row = mysql_fetch_array($res);
							$itemdesc = $row[1];
							$itemid = $row[0];
							$itemtype = $row[8];
							$roomid_str = $row[11];
							$badgetype = calcBadgeType($row[1]);
						
							$_SESSION['newbadge'] = true;
							$_SESSION['roomid_print'] = $_POST['roomid'];				

							$_SESSION['magitemprint'] = 1;
							$_SESSION['itemnum'] = $itemnum;
							$_SESSION['itemdesc'] = $itemdesc;
							$_SESSION['itemtype'] = $itemtype;
							$_SESSION['roomstr'] = $roomid_str;
							$_SESSION['hash'] = $row[3];							
							$_SESSION['printing'] = 1;
							header ("Location: magfest.php"); 
										
				}
				else { $_SESSION['error'] = 'E12 Denied - Not an admin badge.'; header('Location: magfest.php'); }				
		}
		else if($override==3){ //No override
				$roomid = $_POST['roomid'];	
				$itemnum = $_POST['itemnum'];
				
				$conn = connectDB();
				$q = "SELECT hash FROM mag_items WHERE roomtype = $roomid AND id = $itemnum";
				$res = mysql_query($q,$conn);
				$row = mysql_fetch_array($res);
				$hash = $row[0];
				mysql_close();
			//	echo $_POST['hash'].' '.$hash;
				if($_POST['hash']==$hash)
				{
					$conn = connectDB();
					//$q = "SELECT * FROM items WHERE badgeid = $badge AND id = $itemnum";
					$q = "SELECT * FROM mag_items,mag_category WHERE mag_items.roomtype = $roomid AND mag_items.id = $itemnum AND mag_category.id=mag_items.itemtype";
					
					$res = mysql_query($q,$conn);
					$row = mysql_fetch_array($res);
					$itemdesc = $row[1];
					$itemid = $row[0];
					$itemtype = $row[8];
					//$badgetype = calcBadgeType($row[1]);
				
					$_SESSION['newbadge'] = true;
					$_SESSION['roomid'] = $_POST['roomid'];				

					$_SESSION['magitemprint'] = 1;
					$_SESSION['itemnum'] = $itemnum;
					$_SESSION['itemdesc'] = $itemdesc;
					$_SESSION['itemtype'] = $itemtype;
					$_SESSION['hash'] = $row[3];					
					$_SESSION['printing'] = 1;
					header ("Location: magfest.php"); 
				}
				else { 
				    $_SESSION['newbadge'] = true;
					$_SESSION['error_check'] = 1;
					$_SESSION['roomid'] = $roomid;
					//header ("Location: magfest.php"); 
				}
		}
		else if($itemreprint!=0){
				echo '<form action="magfest.php" method="POST" autocomplete="off">';
				echo '<fieldset style="width: 350px; height: 55px;">';
				echo '<legend>Item Label Reprint</legend>';
				echo 'Please verify the item by scanning the label. <br /><span style="font-weight:bold">If the label is too damaged, please contact an admin for override.</span>';
				echo '<div>'; 
				echo '<input type="password" name="hash" autofocus />';										
				echo '<input type="hidden" name="roomid" value="'.$_POST['roomid'].'" />';
				echo '<input type="hidden" name="itemnum" value="'.$_POST['itemreprint'].'" />';
				echo '<input type="hidden" name="override" value="3" />';
				echo '</form>';
				echo '<br /><form action="magfest.php" method="POST" autocomplete="off"><br /><br /><br /><input type="submit" value="Admin Override" /><input type="hidden" name="override" value="1" />';
				echo '<input type="hidden" name="roomid" value="'.$_POST['roomid'].'" />';
				echo '<input type="hidden" name="itemnum" value="'.$_POST['itemreprint'].'" />
				</form>';
				echo '</div>';
				echo '</fieldset>';

		}
			
			
			
			
			
			if(!$checkchange&&$itemreprint==0&&!$skip){
			//********** ORIGINAL START AFTER NEW ITEM
			
			//Select which room to display magfest items from
			if($sorttype==0)
				echo '<h1>MAGFest Inventory Management</h1>';
			
			$conn = connectDB();
			//Get list of rooms
			$q = "SELECT * FROM mag_names";		
			$res = mysql_query($q,$conn);
			//<OPTION VALUE="">select...		
			echo 'Change MAGFest inventory <br />display to show items from:<FORM NAME="nav"><DIV>
			<SELECT NAME="SelectURL" onChange=
			"document.location.href=document.nav.SelectURL.options[document.nav.SelectURL.selectedIndex].value">		
			<OPTION VALUE="magfest.php?sort=0"'; if($_GET['sort']==0) echo 'SELECTED';echo '>select...</OPTION>';
			
			$c=0;
			
			while($row = mysql_fetch_row($res))
			{
				$c++;
				echo '<OPTION VALUE="magfest.php?sort='.$c.'"'; if($_GET['sort']==$c) echo 'SELECTED';echo '>'.$row[1];
				$rooms[$c] = $row[1];
			}
			echo '</SELECT><DIV></FORM>';
			
			$roomid_str = "Invalid Sort ID";

			$q = "SELECT * FROM mag_category,mag_names WHERE roomid = $sorttype AND mag_category.roomid = mag_names.id";
			$res = mysql_query($q,$conn);
			$c = 0;
			while($row = mysql_fetch_array($res))
			{	
				$c++;
				$roomid = $row[1];
				$roomid_str = $row[5];
				$names[$c] = $row[2];
				$types[$c] = $row[3];	
				$catid[$c] = $row[0];
			}
			$_SESSION['badge'] = $roomid;
			mysql_close($conn);
			
			if($sorttype!=0){
				echo '<form autocomplete="off" action="'.$_SERVER['REQUEST_URI'].'" name="additem" method="POST">';
				echo '<fieldset style="width: 750px; height: 55px;">';					

				echo "<legend>Add New Item to ".$roomid_str." Inventory</legend>";					
				echo '<div>';
				echo 'Item Type: <select id="types" name="itemtype" class="form-select required" id="edit-field-data-loc-key" onChange="showHide(this)" ><br />';				
				echo '<option value="0">select...</option>';
				
				foreach($names as $key => $value)
				{
					if($value=='$$Node$$') continue;					
					echo "<option value=\"$key\">$value</option>";
				}
				echo '</select><br />'; 
				echo '<div id="typeHolder">';
				
					/*
					foreach($types_ as $key => $value)
					{
						$types_[$key] = filter_var($value, FILTER_SANITIZE_STRING);
						$types_[$key];												
					}*/
					
				foreach($types as $key => $value)
				{
					$types_ex = explode(';',$value);
				
					echo '<div id="'.$key.'"> 
						<span style="font-weight:bold">**Please answer all fields**</span><br />
						<input name="newitem" type="hidden" value="true">';
					foreach($types_ex as $index => $val)
					{
						$cid = $catid[$key];
						echo "<big>$val:</big><input type=\"text\" maxlength=\"200\" name=\"$cid-$index\" style=\"width:500px\" /><br />";
					}
						
					//echo '<big>Check Item In Now?</big><input type="checkbox" name="check_in1" CHECKED></input>
						echo '</div>'; 
					
				}
				echo '</div></form><br />';
				echo '<input type="submit" value="Submit" />';													
				echo '</div>';
			}
				$error_check = -1;
				if(isset($_SESSION['error_check'])){
					$error_check = $_SESSION['error_check'];
				}
				
				switch($error_check)
				{
					case -1: break;
					case 0: echo '<green><big>Item status changed successfully.</green></big>'; break;
					case 1: echo '<red><big>Wrong item scanned. <br />Please double check that the item matches the description.</red></big>'; break;
					case 2: echo '<red><big>E18 All fields not filled out,<br /> Please try again.</red></big><br />'; break;
				}
				unset($_SESSION['error_check']);
				
				
				if($printing==1)
				{ 
					
					//$_SESSION['scanned_badge'] = $scanned_badge;
					include('netprint.php');
					//unset($_SESSION['scanned_badge']);
				}
				
					//Display list
					if($sorttype!=-1&&$roomid_str!='Invalid Sort ID') {
						$conn = connectDB();
						$q = "SELECT * FROM mag_items,mag_category WHERE roomtype = $sorttype AND mag_items.itemtype=mag_category.id ORDER BY mag_items.id ASC";
						$res = mysql_query($q,$conn);						
						
						
						echo '<br /><fieldset style="width: 750px; height: 55px;">';
						echo "<legend>MAGFest Inventory - $roomid_str</legend>";					
						echo '<div><br />';
						echo '<table border class="list"> 
								<tr class="header"><td style="width: 70px">ID</td>
								<td >Type</td> 
								<td>Description</td>
								<td style="width: 40px">Status</td>
								<td style="width: 135px">Action</td>								
								</tr>';
						$toggle = true;
						
						while($row = mysql_fetch_row($res))
						{						
							$itemid = $row[0];
							$itemdesc = $row[1];
							$itemtype = $row[8];
							
							$bgcolor1 = '#FFFFFF';
							$bgcolor2 = '#DDDDDD';
							if($toggle) $bgcolor = $bgcolor1;
							else $bgcolor = $bgcolor2;		
							
							switch($row[2])
							{
								case 0: $checked = '<red>OUT</red>'; 
									$checkbutton = '<form action="magfest.php" method="POST"><button name="checkin" class ="styled">CheckIn</button>
									<input type="hidden" name="checkin" value="'.$itemid.'" />
									
									</form>'; 
									break;
								case 1: $checked = '<green>IN</green>'; 
									$checkbutton = '<form action="magfest.php" method="POST"><button name="checkout" class ="styled">CheckOut</button>
									<input type="hidden" name="checkout" value="'.$itemid.'" />
									
									</form>'; 
									break;
							}
							$reprint = '<form action="magfest.php" method="POST">
									<button name="reprint" class ="styled">Reprint</button>
									<input type="hidden" name="itemreprint" value="'.$itemid.'" />
									<input type="hidden" name="roomid" value="'.$sorttype.'" />
									</form>'; 
							echo '<tr bgcolor="'.$bgcolor.'"><td>'.$itemid.'</td><td>'.$itemtype.'</td><td>'.$itemdesc.'</td><td>'.$checked.'</td><td>'.$checkbutton.$reprint.'</td></tr>';							
							
							$toggle = !$toggle;
							
						$_SESSION['items'] = $row[0];

						}
						$rows = mysql_num_rows($res);

						
						if($rows==0)
						{
							echo '</table><table border class="list"><tr><td>No Items Added</td></tr></table>';
						}
						echo '</table>'; 
						
						echo '</div><br /><br />';
						echo '</fieldset>';
					}
				}
	}

?>


</body>
</html>
