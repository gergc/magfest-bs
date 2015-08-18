<?php

// BarcodeSystem for MAGFest 2.0
// Designed for MAGFest 9 and BEYOND! by gergc and LANJesus
// File: reports.php
// Purpose: Administrative reporting.
	
	$report = 0;
	if(isset($_GET['r']))
	{
		$report = $_GET['r'];
	}
	
	$items_show = 25;
	
	$page = $items_show;

	if(isset($_GET['p2']))
	{
		$page = $_GET['p2'];
	}

	$prevpage = 0;
	if(isset($_GET['p1']))
	{
		$prevpage = $_GET['p1'];
	}
	
	$checkouts = 0;
	if(isset($_GET['checkouts']))
	{
		$checkouts = 1;
	}
	
	
	include('header.php');

	if($_SESSION['login_auth'] == true) {
		echo '<big><b>BarcodeSystem Active Reporting</b></big><br />';
		echo '<i>Outside the Box, Promoting Synergy, Value-Added Reporting Tool</i>';
		
		echo '<br />';
		
		if($report==1)
			echo '<b><br /><a href="reports.php?r=1">Registered Badge List (Shows PINs, Verify Items Checked Out)</a></b>';
		else
			echo '<br /><a href="reports.php?r=1">Registered Badge List (Shows PINs, Verify Items Checked Out)</a>';
		if($report==2)
			echo '<b><br /><a href="reports.php?r=2">MAGFest Items - Checked Out</a></b>';
		else
			echo '<br /><a href="reports.php?r=2">MAGFest Items - Checked Out</a>';
		
		echo '<br /><br /><hr />';
		echo '<br />';
		
		$conn = connectDB();
		switch($report)
		{
			case 1:
			{	
				if(!$checkouts)
					echo '<a href=reports.php?'.$_SERVER['QUERY_STRING'].'&checkouts>Show badges only with items checked in</a>';
				else
				{
					$derp = $_SERVER['QUERY_STRING'];
					$q = str_replace('&checkouts', '', $derp);
					echo '<a href=reports.php?'.$q.'>Show badges with items checked in and out</a>';
				}
				
				if($page==0)
					$q = "SELECT b1.badgeid, b1.name, b1.number, b1.pin, SUM(IF(i.checked=1,1,0)) AS checks, COUNT(checked) AS total,b1.time FROM items i right outer join badges b1 ON b1.badgeid = i.badgeid GROUP BY b1.badgeid";
				else
					$q = "SELECT b1.badgeid, b1.name, b1.number, b1.pin, SUM(IF(i.checked=1,1,0)) AS checks, COUNT(checked) AS total,b1.time FROM items i right outer join badges b1 ON b1.badgeid = i.badgeid GROUP BY b1.badgeid LIMIT $prevpage,$items_show";

				$res = mysql_query($q,$conn);
				echo '<fieldset style="width: 750px; height: 55px;">';					
				
				if($checkouts)
					echo "<legend>Registered Badge List - Badges with Items Checked In</legend>";
				else
					echo "<legend>Registered Badge List - All Badges</legend>";
				$p2 = -1;
				if(isset($_GET['p2']))
				{
					$p2 = $_GET['p2'];
				}
				
				if($p2!=0)
					if($page!=$items_show)
						if($checkouts)
							echo '<a href="reports.php?r=1&p1='.($prevpage-$items_show).'&p2='.($page-$items_show).'&checkouts"> << Prev Page</a>  || ';				
						else
							echo '<a href="reports.php?r=1&p1='.($prevpage-$items_show).'&p2='.($page-$items_show).'"> << Prev Page</a>  || ';				
				$newprevpage = $page;
				$page = $newprevpage + $items_show;
				
				
				
				if($p2!=0)
					if($checkouts)
						echo '<a href="reports.php?r=1&p1='.$newprevpage.'&p2='.$page.'&checkouts">Next Page >></a>';				
					else 
						echo '<a href="reports.php?r=1&p1='.$newprevpage.'&p2='.$page.'">Next Page >></a>';				
				
				if($p2!=0)
					if($checkouts)
						echo '<br /><a href=reports.php?r=1&p2=0&checkouts>Show All</a>';
					else
						echo '<br /><a href=reports.php?r=1&p2=0>Show All</a>';
				
				if($p2==0)
					if($checkouts)
						echo '<br /><a href=reports.php?r=1&checkouts>Hide All</a>';
					else
						echo '<br /><a href=reports.php?r=1>Hide All</a>';
								
				echo '<div><br />';
				echo '<table border class="list"> 
						<tr class="header"><td>ID</td>
						<td>Name</td> 
						<td>Type</td>
						<td>Time of Reg</td>
						<td style="width: 70px">PIN</td>
						<td style="width: 70px">Total Items</td>
						<td style="width: 70px">Items In</td>								
						<td style="width: 70px">Items Out</td>								
						</tr>';
				while($row = mysql_fetch_array($res))
				{
				
					$badgeid = $row[0];
					$name = $row[1];
					$number = $row[2];
					$pin = $row[3];
					$checkeditems = $row[4];
					$totalitems = $row[5];
					$time = $row[6];
					$type = calcBadgeType($badgeid);
					
					if($checkouts)
						if($checkeditems==0)
							continue;
					echo '<tr><td>';
					echo "<a href=reports.php?r=3&b=$badgeid>".$badgeid.'</a>';
					echo '</td>';
					echo '<td>';
					echo $name.' '.$number;
					echo '</td>';
					echo '<td>';
					echo $type;
					echo '</td>';
					echo '<td>'.$time.'</td>';
					echo '<td>';
					if($_SESSION['user_level'] < 1)
					{
						echo '****';
					}
					else { echo $pin; }
					echo '</td>';	
					echo '<td>';
					echo $totalitems;
					echo '</td>';
					if($checkeditems>0)
					{
						echo '<td style="background-color: red;">'.$checkeditems.'</td>';
					}				
					else
					{
						echo '<td style="background-color: lightgreen;">'.$checkeditems.'</td>';
					}

					$checkedoutitems = $totalitems-$checkeditems;
					echo '<td>'.$checkedoutitems.'</td>';
					echo '</tr>';
				}

				break;
			}
			case 3:
			{				
				$badge = -1;
				if(isset($_GET['b']))
				{
					$badge = $_GET['b'];
				}
				
				$badge = filter_var($badge, FILTER_SANITIZE_NUMBER_INT);
				
				echo '<A HREF="javascript:history.go(-1)">Go Back To Badge List</A>';
				
				echo '<fieldset style="width: 750px; height: 55px;">';	
				echo "<legend>Badge Report - Item List - ".calcBadgeType($badge)." Badge #$badge</legend>";
				$q = "SELECT b.name,b.number,i.id,i.itemdesc,i.checked,c.categoryname,n.name,i.time FROM badges b,items i,category c,cat_names n WHERE b.badgeid=i.badgeid AND n.id = c.roomid AND c.id = i.itemtype AND b.badgeid=$badge";
				$res = mysql_query($q,$conn);
				
				if(!$res)
				{
					echo '<red>Error E20: Invalid Badge or SQL Error</red>';
					exit();
				}
				
				$c = 0;
				while($row = mysql_fetch_array($res))
				{
					$checked = $row['checked'];
					$c++;					
					if($c==1){
						echo '<big>Name: '.$row[0];
						echo '<br />Number: '.$row['number'].'<br /><br /><hr /></big>'; 
					}
					$itemnum = $row['id'];
					$roomname = $row['name'];
					$time = $row['time'];
					echo "<h3>$roomname - Item $itemnum</h3>";
					echo 'Initial Check-in Time: '.$time; 
					echo '<br />Item Type: '.$row['categoryname']; 
					echo '<br />Description: '.$row['itemdesc'].'<br />'; 
					if($checked)
						echo '<red>This item is checked in</red><hr />';
					else
						echo '<green>This item is checked out</green><hr />';
					
				}
				if(mysql_num_rows($res)==0)
				{
					echo '<big>--No Items--</big>';
				}
				
				echo '</fieldset>';
				break;
			}
			
		}
		mysql_close($conn);
	}
?>


</body>
</html>
