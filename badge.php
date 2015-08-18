<?php
// BarcodeSystem for MAGFest 2.0
// Designed for MAGFest 9 and BEYOND! by gergc and LANJesus
// File: badge.php
// Purpose: Registers badges, manages items on the badges.

	include('header.php');
	
	//print_r($_SESSION);
	
	$error = false;
	if(isset($_SESSION['error'])){
		$error = $_SESSION['error'];
		unset($_SESSION['error']);
    }
	
	if($error!=false) { echo '<span style="color:red;font-weight:bold"><big>Error: '.$error.'</span></big><br /><br />'; }
	
	$auth = false;
	if(isset($_SESSION['login_auth'])){
		$auth = $_SESSION['login_auth'];
	}

	$scanned_badge = 'null';
	if(isset($_POST['badge'])){
		$scanned_badge = $_POST['badge'];
	}
	else if(isset($_SESSION['scanned_badge'])){
		$scanned_badge = $_SESSION['scanned_badge'];
		unset($_SESSION['scanned_badge']);
	}
	
	$scanned_badge = filter_var($scanned_badge, FILTER_SANITIZE_STRING);

	$adding_item = false;
	if(isset($_POST['itemtype'])){
		$adding_item = true;
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
	
	$quickchange = 0;
	if(isset($_POST['edititem']))
	{
		$quickchange = 1;		
	}
	
	//If user is coming from the registration page OR item add: set the scanned badge up 
	//(remembered from the reg page) and unset the vars that control it.
	if(isset($_SESSION['newbadge'])){
		unset($_SESSION['scanned_badge']);
		unset($_SESSION['newbadge']);
	}
	
	if($auth == true) {
		
		if($quickchange)
		{
			$itemhash = $_POST['item'];
			$scanned_badge = $_POST['badge'];
			if($itemhash=='')
			{
				$_SESSION['newbadge'] = true;
				$_SESSION['error_check'] = 2;
				$_SESSION['scanned_badge'] = $scanned_badge;
				header("Location: badge.php");
				exit();
			}
			//Prompt user for the label badge
			$conn = connectDB();
			$q = "SELECT * FROM items,category,cat_names WHERE badgeid = $scanned_badge AND items.hash = $itemhash AND category.id=items.itemtype AND cat_names.id=category.roomid";
			$res = mysql_query($q,$conn);
			$row = mysql_fetch_array($res);
			
			if(mysql_num_rows($res)==0)
			{
				$_SESSION['newbadge'] = true;
				$_SESSION['error_check'] = 2;
				$_SESSION['scanned_badge'] = $scanned_badge;
				header("Location: badge.php");
				exit();
			}
			
			$itemdesc = $row[2];
			$itemtype = $row[9];
			$checktype = $row[4];
			$itemnum = $row[0];
			$itemroom = $row[12];
			
			echo '<form action="badge.php" method="POST">';
			echo '<fieldset style="width: 350px; height: 55px;">';
			if($checktype) 
			{
				echo '<legend>Verify Check-Out</legend><h2>';
				echo calcBadgeType($scanned_badge).' #'.$scanned_badge.' - Item #'.$itemnum;
				echo '<br />To cancel, press enter</h2>';				
				echo '<input type="hidden" name="checkout" value="'.$itemnum.'"  />';
			}				
			else {
				echo '<legend>Verify Check-In</legend><h2>';
				echo calcBadgeType($scanned_badge).' #'.$scanned_badge.' - Item #'.$itemnum;
				echo '<br />To cancel, press enter</h2>';				
				echo '<input type="hidden" name="checkin" value="'.$itemnum.'"  />';
			}
			echo '<span style="font-weight:bold">Please scan item label </span>and verify that the item matches the description and type below.';			
			echo '<div>';
			echo '<input type="password" name="itemlabel" autofocus />';
			echo '<input type="hidden" name="badge" value="'.$scanned_badge.'"autofocus />';
			echo '<input type="hidden" name="check_step2" autofocus />';
			echo '<br /><big><span style="font-weight:bold">';
			echo $itemroom.' - '.$itemtype.'<br /></span>';
			echo $itemdesc;
			echo '</big></div>';
			echo '</fieldset>';
			echo '</form>';
		}		
		else if($checkchange)
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
				$conn = connectDB();
				$q = "SELECT hash FROM items WHERE badgeid = $scanned_badge AND id = $itemnum";
				$res = mysql_query($q,$conn);
				$row = mysql_fetch_array($res);
				$hash = $row['hash'];				
				$scanned_hash = $_POST['itemlabel'];
				
				if($hash!=$scanned_hash)
				{					
					$_SESSION['newbadge'] = true;
					$_SESSION['error_check'] = 1;
					$_SESSION['scanned_badge'] = $scanned_badge;
					header ("Location: badge.php");  
				}
				else {
					$conn = connectDB();
					$q = "UPDATE items SET checked = '".$checktype."' WHERE badgeid = $scanned_badge AND id = $itemnum";
					$res = mysql_query($q,$conn);
					
					$_SESSION['newbadge'] = true;
					$_SESSION['error_check'] = 0;
					$_SESSION['scanned_badge'] = $scanned_badge;
					header ("Location: badge.php");  
				}
				
			}
			else {				
				//Prompt user for the label badge
				$conn = connectDB();
				$q = "SELECT * FROM items,category,cat_names WHERE badgeid = $scanned_badge AND items.id = $itemnum AND category.id=items.itemtype AND cat_names.id=category.roomid";
				$res = mysql_query($q,$conn);
				$row = mysql_fetch_array($res);
				$itemdesc = $row[2];
				$itemtype = $row[9];
				$itemroom = $row[12];
				
				echo '<form action="badge.php" method="POST">';
				echo '<fieldset style="width: 350px; height: 55px;">';
				if($checktype) 
				{
					echo '<legend>Verify Check-Out</legend><h2>';
					echo calcBadgeType($scanned_badge).' #'.$scanned_badge.' - Item #'.$itemnum;
					echo '<br />To cancel, press enter</h2>';				
					echo '<input type="hidden" name="checkout" value="'.$itemnum.'"  />';
				}				
				else {
					echo '<legend>Verify Check-In</legend><h2>';
					echo calcBadgeType($scanned_badge).' #'.$scanned_badge.' - Item #'.$itemnum;
					echo '<br />To cancel, press enter</h2>';				
					echo '<input type="hidden" name="checkin" value="'.$itemnum.'"  />';
				}
				echo '<span style="font-weight:bold">Please scan item label </span>and verify that the item matches the description and type below.';			
				echo '<div>';
				echo '<input type="password" name="itemlabel" autofocus />';
				echo '<input type="hidden" name="badge" value="'.$scanned_badge.'"autofocus />';
				echo '<input type="hidden" name="check_step2" autofocus />';
				echo '<br /><big><span style="font-weight:bold">';
				echo $itemroom.' - '.$itemtype.'<br /></span>';
				echo $itemdesc;
				echo '</big></div>';
				echo '</fieldset>';
				echo '</form>';
			}
		}
		else if($override==1){

			$badge = $_POST['badge'];
			$itemnum = $_POST['itemnum'];
			
			$conn = connectDB();			
			$q = "SELECT * FROM items,category,cat_names WHERE items.badgeid = $scanned_badge AND items.id = $itemnum AND category.id=items.itemtype AND category.roomid=cat_names.id";
			$res = mysql_query($q,$conn);
			$row = mysql_fetch_array($res);
			$itemdesc = $row[2];
			$itemid = $row[0];
			$itemtype = $row[9];
			$itemroom = $row[11];
			$badgetype = calcBadgeType($row[1]);
			
			echo '<form action="badge.php" method="POST">';
			echo '<fieldset style="width: 350px; height: 55px;">';
			echo '<legend>Admin Override</legend>';				
			echo '<div>';
			echo 'Please scan admin badge for label reprint. <br />';			
			echo '<input type="password" name="adminbadge" autofocus />';
			echo '<input type="hidden" name="itemnum" value="'.$itemnum.'"  />';								
			echo '<input type="hidden" name="badge" value="'.$badge.'" />';
			echo '<input type="hidden" name="override" value="2" />';
			echo '<br /><big><span style="font-weight:bold">';
			echo $badgetype.' #'.$badge.' Item #'.$itemid;
			echo '<br /> '.$itemtype.'<br /></span>';
			echo $itemroom.' - '.$itemdesc;
			echo '</big></div>';
			echo '</fieldset>';
			echo '</form>';
		}
		else if($override==2){
				//verify admin user
				$adminbadge = $_POST['adminbadge'];
				$badge = $_POST['badge'];	
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
					$q = "SELECT * FROM items,category,cat_names WHERE items.badgeid = $badge AND items.id = $itemnum AND category.id=items.itemtype AND category.roomid=cat_names.id";
					$res = mysql_query($q,$conn);
					$row = mysql_fetch_array($res);
					$itemdesc = $row[2];
					$itemid = $row[0];
					$itemtype = $row[9];
					$roomtype_str = $row[12];
					$badgetype = calcBadgeType($row[1]);
				
					$_SESSION['newbadge'] = true;
					$_SESSION['scanned_badge'] = $_POST['badge'];				

					$_SESSION['itemprint'] = 1;
					$_SESSION['itemnum'] = $itemnum;
					$_SESSION['itemdesc'] = $itemdesc;
					$_SESSION['itemtype'] = $itemtype;
					$_SESSION['roomstr'] = $roomtype_str;
					$_SESSION['hash'] = $row[5];
					
					$_SESSION['printing'] = 1;
					header ("Location: badge.php"); 
				}
				else { $_SESSION['error'] = 'E06 Denied - Not an admin badge.'; header('Location: badge.php'); }				
		}
		else if($override==3){ //No override
			$badge = $_POST['badge'];	
			$itemnum = $_POST['itemnum'];
			
			$conn = connectDB();
			$q = "SELECT hash FROM items WHERE badgeid = $badge AND id = $itemnum";
			$res = mysql_query($q,$conn);
			$row = mysql_fetch_array($res);
			$hash = $row[0];
			mysql_close();
			
			if($_POST['hash']==$hash)
			{
				$conn = connectDB();
				$q = "SELECT * FROM items,category,cat_names WHERE items.badgeid = $badge AND items.id = $itemnum AND category.id=items.itemtype AND category.roomid=cat_names.id";
				$res = mysql_query($q,$conn);
				$row = mysql_fetch_array($res);
				$itemdesc = $row[2];
				$itemid = $row[0];
				$itemtype = $row[9];
				$roomtype_str = $row[12];
				$badgetype = calcBadgeType($row[1]);
			
				$_SESSION['newbadge'] = true;
				$_SESSION['scanned_badge'] = $_POST['badge'];				

				$_SESSION['itemprint'] = 1;
				$_SESSION['itemnum'] = $itemnum;
				$_SESSION['itemdesc'] = $itemdesc;
				$_SESSION['itemtype'] = $itemtype;
				$_SESSION['roomstr'] = $roomtype_str;
				$_SESSION['hash'] = $row[5];
				
				$_SESSION['printing'] = 1;
				header ("Location: badge.php"); 
			}
			else { 
			   $_SESSION['newbadge'] = true;
				$_SESSION['error_check'] = 1;
				$_SESSION['scanned_badge'] = $badge;
				header ("Location: badge.php"); 
			}
		}
		else if($itemreprint!=0){
			echo '<form action="badge.php" method="POST" autocomplete="off">';
			echo '<fieldset style="width: 350px; height: 55px;">';
			echo '<legend>Item Label Reprint</legend>';
			echo 'Please verify the item by scanning the label. <br /><span style="font-weight:bold">If the label is too damaged or missing(verify item by serial# first if possible), please contact an admin for override.</span>';
			echo '<div>'; 
			echo '<input type="password" name="hash" autofocus />';										
			echo '<input type="hidden" name="badge" value="'.$_POST['badge'].'" />';
			echo '<input type="hidden" name="itemnum" value="'.$_POST['itemreprint'].'" />';
			echo '<input type="hidden" name="override" value="3" />';
			echo '</form>';
			echo '<br /><form action="badge.php" method="POST" autocomplete="off"><br /><br /><br /><input type="submit" value="Admin Override" /><input type="hidden" name="override" value="1" />';
			echo '<input type="hidden" name="badge" value="'.$_POST['badge'].'" />';
			echo '<input type="hidden" name="itemnum" value="'.$_POST['itemreprint'].'" /></form>';
			echo '</div>';
			echo '</fieldset>';
		}
		else if($adding_item)
		{
			//Check for the item to be added.
			//This function prints out a label and submits the item to the DB.
			$itemtype = $_POST['itemtype'];
			$itemdesc = '';

			if($itemtype==0)
			{
				$_SESSION['newbadge'] = true; 
				$_SESSION['scanned_badge'] = $_POST['badge'];	
				header("Location: badge.php");
			}
			else {
				$sorttype = $_SESSION['roomid'];
				$conn = connectDB();
				$q = "SELECT * FROM category,cat_names WHERE roomid = $sorttype AND category.roomid = cat_names.id";
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
					$totaldesccount = count($types_ex);
					foreach($types_ex as $index => $val)
					{
						
						$cid = $catid[$key];
						$pkey = $cid.'-'.$index;
							
						if($_POST[$pkey]!='')
						{
							$itemcount++;
							$itemname = $names[$key];
							$cid_ = $catid[$key];
							$itemdata = $val.': '.$_POST[$pkey];							
							array_push($itemdesc, $itemdata);
							$k = 'check'.$pkey;
							$checkeditem = $_POST[$k];							
						}
					}
				}

				if($itemcount!=$totaldesccount)
				{
					$_SESSION['newbadge'] = true;
					$_SESSION['error_check'] = 3;
					$_SESSION['scanned_badge'] = $_POST['badge'];
					header("Location: badge.php");
					exit();
				}

				$items = $_SESSION['items']+1;
				$gluedesc = implode(' ',$itemdesc);
				$badgenum = $_POST['badge'];
				$hash = abs(crc32($items.'-'.$badgenum));
				
				if($checkeditem=='on')
					$checked = 1;
				else
					$checked = 0;
				
				$time = date("m/j/y g:i a");
				
				$q = "INSERT INTO items(id,itemdesc,checked,hash,itemtype,badgeid,time) VALUES ('$itemnum','$gluedesc','$checked','$hash','$cid_','$badgenum','$time')";
				$res = mysql_query($q,$conn);
				
				unset($_SESSION['items']);
				mysql_close($conn);		
				
				if($res) { 
					$_SESSION['newbadge'] = true;
					$_SESSION['scanned_badge'] = $_POST['badge'];				
					
					$_SESSION['itemprint'] = 1;
					$_SESSION['itemnum'] = $items;
					$_SESSION['itemdesc'] = $gluedesc;
					$_SESSION['itemtype'] = $itemname;
					$_SESSION['hash'] = $hash;
					$_SESSION['roomstr'] = $roomid_str;
					$_SESSION['printing'] = 1;
					
					header ("Location: badge.php");  					
				}
				else { 
					$_SESSION['error'] = 'Failed to Register Item - Please contact Greg or try again.';
					header('Location: index.php');
				 }
			}
		} 	
		else if($scanned_badge!='null')
		{
			//User has just scanned the attendee's badge on the main page. Check for registration.
			//Filter input to make sure no one is like Ben, a leet h4x0R
			$scanned_badge = filter_var($scanned_badge, FILTER_VALIDATE_INT);			

			if($scanned_badge=='')
			{
				$_SESSION['error'] = 'E02 You done goof\'d up - badge is invalid. Please try again.';
				header('Location: index.php');
				exit();
			}
			
			$conn = connectDB();
			$q = "SELECT * FROM badges WHERE badgeid = $scanned_badge";
			$res = mysql_query($q,$conn);
			
			if(!$res)
			{
				$_SESSION['error'] = 'E03 You done goof\'d up - SQL Error. Please try again or go get Greg.';
				header('Location: index.php');
				exit();
			}
			
			//Check for errors, print if needed
			$error_check = -1;
			if(isset($_SESSION['error_check'])){
				$error_check = $_SESSION['error_check'];
			}
			
			switch($error_check)
			{
				case -1: break;
				case 0: echo '<green><big>Item status changed successfully.</green></big><br />'; break;
				case 1: echo '<red><big>E05 Wrong item scanned. <br />Please double check that the item matches the description.</red></big><br />'; break;
				case 2: echo '<red><big>E11 Item not belonging to this user,<br /> invalid item or no item scanned.  <br />Please try again.</red></big><br />'; break;
				case 3: echo '<red><big>E17 All fields not filled out,<br /> Please try again.</red></big><br />'; break;
				
			}
			unset($_SESSION['error_check']);
			
			if($printing==1)
			{ 
				$_SESSION['scanned_badge'] = $scanned_badge;
				include('netprint.php');
				unset($_SESSION['scanned_badge']);
			}
			
			if(isset($_SESSION['badgehash'])){
				$scanned_badge = $_SESSION['badgehash'];
				unset($_SESSION['badgehash']);
			}
			
			if($scanned_badge<5000)
			{
				//New badge check
				if(mysql_num_rows($res)==0) { 									  
					$uconn = connectUber();
					$q = "SELECT first_name, last_name, phone FROM Attendee WHERE badge_num =".$scanned_badge;
					$res = mysql_query($q,$uconn);
					$row = mysql_fetch_array($res);
					if(mysql_num_rows($res)==0)
					{
						$_SESSION['error'] = 'E04 Badge does not exist in Ubersystem.<br /> Please direct attendee to the reg desk to fix this problem.';
						header('Location: index.php' );
						exit();
					}
					$firstname = $row['first_name'];
					$lastname = $row['last_name'];
					$name = $firstname.' '.$lastname;
					$phone = $row['phone'];
					
					mysql_close($uconn);
					
					echo '<form action="badge.php" method="POST" autocomplete="off">';
					echo '<fieldset style="width: 350px; height: 55px;">';
					echo '<legend>New Registration</legend>';
					echo 'Please verify information.';
					echo '<div>'; 
					echo 'Name: <input type="text" name="name" value="'.$name.'"  />';
					echo 'Phone: <input type="text" name="phone" value="'.$phone.'" />';
					
					//Random pin between 0000 and 9999
					$pin = substr(mt_rand(10000,19999),1);
					
					echo 'This PIN is required for logging onto the MAGFest Network. Please notify attendee. <br />';
					echo 'Network PIN: <pin>'.$pin.'</pin><br />';
					echo '<input type="submit" value="Submit" autofocus />';
					echo '<input type="hidden" name="regbadge" value="'.$scanned_badge.'" />';
					echo '<input type="hidden" name="pin" value="'.$pin.'" />';
					echo '</div>';
					echo '</fieldset>';
					echo '</form>';
				}
				else {
					$_SESSION['error'] = 'E08 Badge already exists.<br /> Please scan the badge barcode to manage inventory.';
					header('Location: index.php' );
					exit();
				}
			}
			else { //If it's above 5000, it's a hash. Process hash
			
				//Check for hash existance
				$q = "SELECT * FROM badges WHERE hash = '$scanned_badge'";
				$res = mysql_query($q,$conn);
				if(!$res)
				{
					$_SESSION['error'] = 'E09 You done goof\'d up - SQL Error. Please try again or go get a super-admin.';
					header('Location: index.php');
					exit();
				}
				
				if(mysql_num_rows($res)==0) { 
					$_SESSION['error'] = 'E10 Badge does not exist. Please try again';
					header('Location: index.php'); 
					exit();
				}
				$_SESSION['badgehash'] = $scanned_badge;
				$row = mysql_fetch_array($res);
				$scanned_badge = $row[1];
				$pin = $row[4];
				
				//*******************
				$sorttype = $_SESSION['roomid'];
				//echo 'SORTTYPE: '.$sorttype;
				//$conn = connectDB();
				
				$q = "SELECT * FROM category,cat_names WHERE roomid = $sorttype AND category.roomid = cat_names.id";
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
				
				$q = "SELECT id FROM items WHERE checked=1 AND badgeid=$scanned_badge";
				$res1 = mysql_query($q,$conn);
				$q = "SELECT id FROM items WHERE checked=0 AND badgeid=$scanned_badge";
				$res2 = mysql_query($q,$conn);
				$q = "SELECT id FROM items WHERE badgeid=$scanned_badge";
				$res3 = mysql_query($q,$conn);
				
				echo '<b>User Statistics:</b> ';
				echo 'Items Checked In: '.mysql_num_rows($res1).' || Items Checked Out: '.mysql_num_rows($res2).' || Total Items: '.mysql_num_rows($res3).' || Login PIN: '.$pin;

				mysql_close($conn);	
				
				echo '<form action="badge.php" method="POST">';
				//echo '<fieldset style="width: 350px; height: 55px;">';
				echo '<fieldset style="width: 750px; height: 55px;">';
				echo '<legend>Modify Item</legend>';
				echo 'Scan item to check it in or out<br/>';
				echo '<div>';
				echo '<input type="password" name="item" autofocus />';
				echo '<input type="hidden" name="edititem" />';
				echo '<input type="hidden" name="badge" value="'.$scanned_badge.'" />';
				echo '</div>';
				//echo '</fieldset>';
				echo '</form><br /><br />';
				
				//*******************
				echo '<form autocomplete="off" action="badge.php" name="additem" method="POST">';
				echo '<fieldset style="width: 750px; height: 55px;">';
				$badgetype = calcBadgeType($scanned_badge);
				$defaultroom_str = $_SESSION['roomname'];
				echo "<legend>Add Item - $defaultroom_str - $badgetype #$scanned_badge</legend>";					
				echo '<div>';
				echo 'Item Type: <select id="types" name="itemtype" class="form-select required" id="edit-field-data-loc-key" onChange="showHide(this)" ><br />';				
				echo '<option value="0">select...</option>';
				
				foreach($names as $key => $value)
				{
					if($value=='$$Node$$') continue;					
					echo "<option value=\"$key\">$roomid_str - $value</option>";
				}
				echo '</select><br />'; 
				echo '<div id="typeHolder">';
				
					
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
						echo '<input type="hidden" name="badge" value="'.$scanned_badge.'" />';
						echo '<big>Check Item In Now?</big><input type="checkbox" name="check'.$cid.'-'.$index.'" CHECKED></input>';
						echo '</div>'; 
					
				}
				echo '</div></form><br />';
				echo '<input type="submit" value="Submit" />';													
				echo '</div><br /><br />';
			
					//echo '</fieldset>';
					//echo '</form><br />';
			
				//List items
				$conn = connectDB();
				$q = "SELECT name FROM badges WHERE badgeid = $scanned_badge";
				$res = mysql_query($q,$conn);
				$row = mysql_fetch_array($res);
				$name = $row['name'];
				
				
				
				if(mysql_num_rows($res)==0)
				{
					echo '<span style="color:red;font-weight:bold">Invalid badge or badge is not registered in this system, please register it first.</span>';
					echo '<a href="index.php"><h2>Go Back to Main Menu</h2></a>';
				}
				else {
					
					//$q = "SELECT * FROM items WHERE badgeid = $scanned_badge";
					$q = "SELECT i.id,i.itemdesc,c.categoryname,i.checked,i.time,n.name FROM items i,category c,cat_names n WHERE badgeid = $scanned_badge AND c.id=i.itemtype AND n.id = c.roomid ORDER BY i.id ASC";
					$res = mysql_query($q,$conn);
					
					
					echo '<fieldset style="width: 750px; height: 55px;">';					
					echo "<legend>$name's Items</legend>";
					echo '<div><br />';
					echo '<table border class="list"> 
							<tr class="header"><td>ID</td>
							<td>Type</td> 
							<td>Information</td>
							<td style="width: 40px">Status</td>
							<td style="width: 40px">Initial Checkin</td>
							<td style="width: 150px"></td>								
							</tr>';
					$toggle = true;
					
					while($row = mysql_fetch_row($res))
					{		
										
						$itemid = $row[0];
						$itemdesc = $row[1];
						$itemtype = $row[2];
						$itemroom = $row[5];
						$time = $row[4];
						$bgcolor1 = '#FFFFFF';
						$bgcolor2 = '#DDDDDD';
						if($toggle) $bgcolor = $bgcolor1;
						else $bgcolor = $bgcolor2;		
						
						switch($row[3])
						{
							case 0: $checked = '<red>OUT</red>'; 
								$checkbutton = '<form action="badge.php" method="POST"><button name="checkin" class ="styled">CheckIn</button>
								<input type="hidden" name="checkin" value="'.$itemid.'" />
								<input type="hidden" name="badge" value="'.$scanned_badge.'" />
								</form>'; 
								break;
							case 1: $checked = '<green>IN</green>'; 
								$checkbutton = '<form action="badge.php" method="POST"><button name="checkout" class ="styled">CheckOut</button>
								<input type="hidden" name="checkout" value="'.$itemid.'" />
								<input type="hidden" name="badge" value="'.$scanned_badge.'" />
								</form>'; 
								break;
						}
						$reprint = '<form action="badge.php" method="POST">
								<button name="reprint" class ="styled">Reprint</button>
								<input type="hidden" name="itemreprint" value="'.$itemid.'" />
								<input type="hidden" name="badge" value="'.$scanned_badge.'" />
								</form>'; 
						echo '<tr bgcolor="'.$bgcolor.'"><td>'.$itemid.'</td><td><b>'.$itemroom.'</b><br />'.$itemtype.'</td><td>'.$itemdesc.'</td><td>'.$checked.'</td><td>'.$time.'</td><td>'.$checkbutton.$reprint.'</td></tr>';							
						
						$toggle = !$toggle;
					}
					$rows = mysql_num_rows($res);
					
					$_SESSION['items'] = $rows;
					
					if($rows==0)
					{
						echo '</table><table border class="list"><tr><td>No Items Added</td></tr></table>';
					}
					echo '</table>'; 
					echo '<br /><br />';
					echo '</div></fieldset>';

					$_SESSION['scanned_badge'] = $scanned_badge;
				}
			}
			mysql_close($conn);

		}		
		else if($sub_reg){
			//Registration submitted. Insert into the DB
			$pin = $_POST['pin']; 
			$name = $_POST['name'];
			$phone = $_POST['phone'];
			$badge = $_POST['regbadge'];
			
			$hash = abs(crc32($badge.'-'.$pin));
			$conn = connectDB();
			
			$time = date("m/j/y g:i a"); 
			
			$q = "INSERT INTO badges(badgeid,name,number,pin,hash,time) VALUES('".$badge."','".$name."','".$phone."','".$pin."','".$hash."','".$time."')";
			$res = mysql_query($q,$conn);
			
			if($res) { 
				$_SESSION['newbadge'] = true;
				//$_SESSION['scanned_badge'] = $badge;
				
				//print stuff
				$_SESSION['scanned_badge'] = $badge;
				$_SESSION['badgehash'] = $hash;
				$_SESSION['badgeprint'] = 1;
				$_SESSION['pin'] = $pin;
				$_SESSION['name'] = $name;
				$_SESSION['hash'] = $hash;
				$_SESSION['printing'] = 1;				
				
				header ("Location: badge.php");
			}
			else { 
				$_SESSION['error'] = 'E07 Badge unable to be registered. Please try again or contact Greg';
				header('Location: index.php');
				exit();			
			} 
		}

	}
?>


</body>
</html>
