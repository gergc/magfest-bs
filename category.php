<script type="text/javascript">
function showPopUp(el) {
var cvr = document.getElementById("cover")
var dlg = document.getElementById(el)
cvr.style.display = "block"
dlg.style.display = "block"
if (document.body.style.overflow = "hidden") {
	cvr.style.width = "1024"
	cvr.style.height = "100%"
	}
}
function closePopUp(el) {
var cvr = document.getElementById("cover")
var dlg = document.getElementById(el)
cvr.style.display = "none"
dlg.style.display = "none"
document.body.style.overflowY = "scroll"
}
</script>

<?php

// BarcodeSystem for MAGFest 2.0
// Designed for MAGFest 9 and BEYOND! by gergc and LANJesus
// File: category.php
// Purpose: Allows admins to add and modify rooms and their respective item types and descriptions
	
	include('header.php');

	//print_r($_POST);	
	$sorttype = 0;
	if(isset($_GET['sort']))
	{
		$sorttype = $_GET['sort'];
	}
	
	$addroom = false;
	if(isset($_POST['addroom']))
	{
		$addroom = true;
	}
	
	$addmagfestroom = false;
	if(isset($_POST['addmagfestroom']))
	{
		$addmagfestroom = true;
	}
	
	$magfestcat = false;
	if(isset($_POST['magfest_cat']))
	{
		$magfestcat = true;
	}

	$addcat = false;
	if(isset($_POST['addcat']))
	{
		$addcat = true;
	}

	$edittype = false;
	if(isset($_POST['edittype']))
	{
		$edittype = true;
	}

	if($_SESSION['login_auth'] == true && $_SESSION['user_level']==2) {

		if($sorttype==-1)		
			echo '<button class="big" onClick="parent.location=\'category.php?sort=0\'">Attendee Inventory</button><br /><br />';
		else
			echo '<button class="big" onClick="parent.location=\'category.php?sort=-1\'">MAGFest Inventory</button><br /><br />';
			
		//Input handler	
		if($addmagfestroom)
		{
			$roomname = $_POST['roomname'];
			if (ereg("[^A-Za-z0-9]", $roomname)) {
				echo '<big><red>Invalid Room Name: Please input only letters or numbers for the room name.</big></red>';
			}
			else {  
				$conn = connectDB();
				
				$q = "SELECT * FROM mag_names GROUP BY id DESC LIMIT 1";
				$res = mysql_query($q,$conn);
				$row = mysql_fetch_array($res);
				$nextid = $row['id']+1;
				
				$q = "INSERT INTO mag_names(name) VALUES ('$roomname')";
				$res = mysql_query($q,$conn);
				
				$nodetype = '$$Node$$';
				$q = "INSERT INTO mag_category(roomid, categoryname, categorytypes) VALUES ('$nextid','$nodetype','$nodetype')";
				$res = mysql_query($q,$conn);
				
				if(!$res)
					echo '<big><red>SQL Error</red></big>';
				else
					echo '<big>Added MAGFest-owned Inventory Room: '.$roomname.'</big><br />';	
				mysql_close($conn);
			}
		}
		
		if($addroom)
		{
			$roomname = $_POST['roomname'];
			if (ereg("[^A-Za-z0-9]", $roomname)) {
				echo '<big><red>Invalid Room Name: Please input only letters or numbers for the room name.</big></red>';
			}
			else {  
				$conn = connectDB();
				
				$q = "SELECT * FROM cat_names GROUP BY id DESC LIMIT 1";
				$res = mysql_query($q,$conn);
				$row = mysql_fetch_array($res);
				$nextid = $row['id']+1;
				
				$q = "INSERT INTO cat_names(name) VALUES ('$roomname')";
				$res = mysql_query($q,$conn);
				
				$nodetype = '$$Node$$';
				$q = "INSERT INTO category(roomid, categoryname, categorytypes) VALUES ('$nextid','$nodetype','$nodetype')";
				$res = mysql_query($q,$conn);
				
				if(!$res)
					echo '<big><red>SQL Error</red></big>';
				else
					echo '<big>Added Room: '.$roomname.'</big><br />';	
				mysql_close($conn);
			}
		}
		if($addcat)
		{
			$input = explode(';',$_POST['catdesc']);
			foreach($input as $key => $value)
			{
				$input[$key] = filter_var($value, FILTER_SANITIZE_STRING);
			}
			$catdesc = implode(';',$input);
			$conn = connectDB();
			$roomid = $_POST['catid'];
			$catname = $_POST['catname'];
			if(!$magfestcat)
				$q = "INSERT INTO category(roomid,categoryname,categorytypes) VALUES('$roomid','$catname','$catdesc')";
			else
				$q = "INSERT INTO mag_category(roomid,categoryname,categorytypes) VALUES('$roomid','$catname','$catdesc')";
			$res = mysql_query($q,$conn);
			if(!$res)
					echo '<big><red>SQL Error</red></big>';
			else
				if(!$magfestcat)
					echo '<big>Added Category: '.$catname.'</big><br />';
				else
					echo '<big>Added MAGFest-Owned Category: '.$catname.'</big><br />';
			mysql_close($conn);
		}

		if($edittype)
		{
			$input = explode(';',$_POST['typedesc']);
			foreach($input as $key => $value)
			{
					$input[$key] = filter_var($value, FILTER_SANITIZE_STRING);
			}
			$catdesc = implode(';',$input);
			$conn = connectDB();
			$catid = $_POST['typeid'];
			$roomid = $_POST['catid'];
			$catname = $_POST['catname'];
			if(!$magfestcat)
				$q = "REPLACE INTO category(id,roomid,categoryname,categorytypes) VALUES('$catid','$roomid','$catname','$catdesc')";
			else
				$q = "REPLACE INTO mag_category(id,roomid,categoryname,categorytypes) VALUES('$catid','$roomid','$catname','$catdesc')";
			
			$res = mysql_query($q,$conn);
			if(!$res)
				echo '<big><red>SQL Error</red></big>';
			else
				echo '<big>Edited Type Sucessfully</big><br />';
			mysql_close($conn);
		}
		$conn = connectDB();
		if($sorttype!=-1)
		{
			//list
			
			//Get list of rooms
			$q = "SELECT * FROM cat_names";		
			$res = mysql_query($q,$conn);
			//<OPTION VALUE="">select...		
			echo 'Display types from:<FORM NAME="nav"><DIV>
			<SELECT NAME="SelectURL" onChange=
			"document.location.href=
			document.nav.SelectURL.options[document.nav.SelectURL.selectedIndex].value">		
			<OPTION VALUE="category.php?sort=0"'; if($_GET['sort']==0) echo 'SELECTED';echo '>Show All</OPTION>';
			
			$c=0;
			while($row = mysql_fetch_row($res))
			{	
				$c++;
				echo '<OPTION VALUE="category.php?sort='.$c.'"'; if($_GET['sort']==$c) echo 'SELECTED';echo '>'.$row[1];
			}
			
			//MAGFest Items only
			//echo '<OPTION VALUE="category.php?sort=-1"'; if($_GET['sort']==-1) echo 'SELECTED';echo '>MAGFest Items</OPTION>';
			
			echo '</SELECT><DIV></FORM>';
		}

			echo '<fieldset style="width: 750px; height: 55px;">';
			if($sorttype==-1)
				echo "<legend>MAGFest-Owned Inventory Rooms and Item Types</legend>";					
			else
				echo "<legend>Attendee Rooms and Item Types</legend>";
			echo '<div><br />';
			echo '<table border class="list"> 
					<tr class="header"><td style="width: 70px">ID</td>
					<td>Room</td> 
					<td>Category</td>
					<td>Description Types</td>
					<td>Action</td>								
					</tr>';
			$toggle = true;
			
			if($sorttype == 0)
				$q = "SELECT category.id, category.roomid,name,categoryname,categorytypes FROM category,cat_names WHERE category.roomid=cat_names.id ORDER BY roomid ASC";
			else if($sorttype == -1)
				$q = "SELECT mag_category.id, mag_category.roomid,name,categoryname,categorytypes FROM mag_category,mag_names WHERE mag_category.roomid=mag_names.id ORDER BY roomid ASC";
			else
				$q = "SELECT category.id, category.roomid,name,categoryname,categorytypes FROM category,cat_names WHERE category.roomid=cat_names.id AND roomid = $sorttype ORDER BY roomid ASC";
			
			$res = mysql_query($q,$conn);
				
			$lastid = 0;
			$firstnode1 = true;
			$firstnode2 = true;
			
			while($row = mysql_fetch_row($res))
			{	
				$currentid = $row[1];
				if($row[3]=='$$Node$$') //Main node, skip
				{
						echo '<tr><td>'; //bgcolor="#DDDDDD"
						echo '<b>'.$currentid.'</b></td><td>';
						echo $row[2].'<td /><td /><b><div id="cover" style="display:none;position:absolute;left:0px;top:0px;width:100%;height:100%;background:gray;filter:alpha(Opacity=50);opacity:0.5;-moz-opacity:0.5;-khtml-opacity:0.5"></div>
						<div id="dialogC'.$row[0].'" style="display:none;left:200px;top:200px;width:600px;height:400px;position:absolute;z-index:100;background:white;padding:2px;font:10pt tahoma;border:1px solid gray"><br /><br />
						<h1>Adding Category to '.$row[2].' Room</h1>
						<h2>Category Name:</h2>
						<form autocomplete="off" action="'.$_SERVER['REQUEST_URI'].'" name="addcat" method="POST">
						<input name="catname" style="width: 600px" type="text" />
						<h2>Description: <br />Please put each description type in between semicolons (;) </h2>
						<input name="catdesc" style="width: 600px" type="text" />';
						
						if($sorttype==-1)
						{
							echo '<input name="magfest_cat" type="hidden" value="true" />';
						}
						
						echo '<input name="addcat" type="hidden" value="true" />						
						<input name="catid" type="hidden" value="'.$row[1].'" />						
						<br><input type="submit" value="Submit" /></form>
						<br><a href="#" onclick="closePopUp(\'dialogC'.$row[0].'\');">[Cancel]</a>
						</div>
						<a href="#" onclick="showPopUp(\'dialogC'.$row[0].'\');">Add Category to '.$row[2].'</a></b>
						<td /></tr>';
					continue;
				}

				echo '</td><td>';
				echo '&#187;<td>';	
				echo $row[2]; //roomid
				echo '</td><td>';
				echo $row[3]; //type
				echo '</td><td>';
				echo $row[4]; //typedescs
				echo '</td>';
				echo '<td>
				
				<div id="cover" style="display:none;position:absolute;left:0px;top:0px;width:100%;height:100%;background:gray;filter:alpha(Opacity=50);opacity:0.5;-moz-opacity:0.5;-khtml-opacity:0.5"></div>
				<div id="dialog'.$row[0].'" style="display:none;left:200px;top:200px;width:600px;height:400px;position:absolute;z-index:100;background:white;padding:2px;font:10pt tahoma;border:1px solid gray"><br /><br />
				<form autocomplete="off" action="'.$_SERVER['REQUEST_URI'].'" name="edittype" method="POST">
				<h1>Editing Type: "'.$row[3].'" in the '.$row[2].' Room</h1>
				<h2>Please put each description type in between semicolons (;) </h2>
				<br><input style="width: 600px" type="text" name="typedesc" value="'.$row[4].'" />
				<input name="typeid" type="hidden" value="'.$row[0].'" />
				<input name="catid" type="hidden" value="'.$row[1].'" />
				<input name="edittype" type="hidden" value="true" />
				<input name="catname" type="hidden" value="'.$row[3].'" />';
				if($sorttype==-1)
						{
							echo '<input name="magfest_cat" type="hidden" value="true" />';
						}
						echo'
				<br><input type="submit" value="Submit" /></form>
				<br><a href="#" onclick="closePopUp(\'dialog'.$row[0].'\');">[Cancel]</a>
				</div>
				<a href="#" onclick="showPopUp(\'dialog'.$row[0].'\');">Edit_Description</a>

				</td></tr>';
				$lastid = $row[1];	
				
			}
			$rows = mysql_num_rows($res);
			
			if($rows==0)
			{
				echo '</table><table border class="list"><tr><td>No Rooms Added</td></tr></table>';
			}
			
			echo '</table>'; 
			
			echo '</div><br /><br />';
			echo '';
			
		mysql_close($conn);
		
		if($sorttype==-1)
		{
			//Show page
			echo '<form autocomplete="off" action="'.$_SERVER['REQUEST_URI'].'" name="addroom" method="POST">';
			echo '<fieldset style="width: 750px; height: 55px;">';					
			echo "<legend>Add New Room for MAGFest Items</legend>";					
			echo '<div>';
			echo 'Room Name: <input type="text" name="roomname" /><br />';
			echo '<input type="hidden" name="addmagfestroom" value="true" />';
			echo '<input type="submit" value="Submit" />';													
			echo '</div>';
			echo '</form></fieldset></fieldset>';
		}
		else
		{
			//Show page
			echo '<form autocomplete="off" action="'.$_SERVER['REQUEST_URI'].'" name="addroom" method="POST">';
			echo '<fieldset style="width: 750px; height: 55px;">';					
			echo "<legend>Add New Room</legend>";					
			echo '<div>';
			echo 'Room Name: <input type="text" name="roomname" /><br />';
			echo '<input type="hidden" name="addroom" value="true" />';
			echo '<input type="submit" value="Submit" />';													
			echo '</div>';
			echo '</form></fieldset></fieldset>';
		}
		
		
	}
	else { echo 'Access Denied'; }
	

?>


</body>
</html>
