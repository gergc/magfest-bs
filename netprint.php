<?php	

// BarcodeSystem for MAGFest 2.0
// Designed for MAGFest 9 and BEYOND! by gergc and LANJesus
// File: netprint.php
// Purpose: Facilitates the creation of the labels and sends the generated 
//			image to the appropriate printer via IP sockets.

	include('invert.php');
	
	//MAGFest logos (horiz. and vert.)
	$magfestlogo = @imagecreatefrompng('MAGFest_logo.333.png');
	$magfestlogo_vert = @imagecreatefrompng('MAGFest_logo.333.clock.png');

	//Initial image dimensions and color setup
	$dest = imagecreatetruecolor(768, 184);
	$white = imagecolorallocate($dest,255,255,255); 
	$black = imagecolorallocate($dest, 0, 0, 0);	
	imagefill($dest,0,0,$white);
	
	//Set fonts.
	$f = 'LILLIPUT.TTF';
	$f2 = 'ATARI.TTF'; 
	$font = imageloadfont('./addlg10.gdf');

	if(isset($_SESSION['itemprint'])){
		//Item Tag 
		$itemnum = $_SESSION['itemnum'];
		$itemdesc = $_SESSION['itemdesc'];
		$itemtype = $_SESSION['itemtype'];
		$hash = $_SESSION['hash'];
		$badge = $_SESSION['scanned_badge'];
		$roomstr = $_SESSION['roomstr'];
		
		unset($_SESSION['roomstr']);
		unset($_SESSION['itemprint']);
		unset($_SESSION['itemnum']);
		unset($_SESSION['itemdesc']);
		unset($_SESSION['itemtype']);
		unset($_SESSION['hash']);
			
		$shit = $path.'genbarcode.php?h='.$hash;
		$img = @imagecreatefrompng($shit);	
		
		$LINE_WIDTH = 80;
		$itemdesc = $itemdesc." ";
		$output = array();
		$c = 0;
		
		$output[0] = '';
		$output[1] = '';
		$output[2] = '';
		$output[3] = '';
		
		if(@strlen($itemdesc)>80)
		{
			while(strlen($itemdesc)>0)
			{
				$loc = strrpos(substr($itemdesc,0,$LINE_WIDTH)," ");
				$output[$c] = substr($itemdesc,0,$loc);
				$itemdesc = substr($itemdesc,$loc+1);
				$c++;
			}
			$x = imagesx($img);
			$y = imagesy($img);
			 
			imagecopyresized($dest, $magfestlogo_vert, 730, 10, 0, 0, (64/2), (333/2),  64, 333); 				
			imagecopyresized($dest, $img, 340, 15, 0, 0,  374, 100, $x, $y); 
			imagettftext($dest, 14, 0, 0, 25, $black, $f, 'M9 '.$roomstr.' Item Tag');
			imagettftext($dest, 24, 0, 0, 55, $black, $f2, calcBadgeType($badge).' '.$badge);
			imagettftext($dest, 24, 0, 0, 80, $black, $f2, 'Item Number: '.$itemnum);
			imagestring($dest, 5, 0, 90, 'Item Type: '.$itemtype, $black); 
			imagestring($dest, 5, 0, 107, 'Item Information:', $black);
			imagestring($dest, 5, 0, 125, $output[0], $black);
			imagestring($dest, 5, 0, 140, $output[1], $black);		
			imagestring($dest, 5, 0, 155, $output[2], $black);		
			imagestring($dest, 5, 0, 170, $output[3], $black);	
		}
		else {
			$x = imagesx($img);
			$y = imagesy($img);
			
			imagecopyresized($dest, $magfestlogo_vert, 730, 10, 0, 0, (64/2), (333/2),  64, 333); 				
			imagecopyresized($dest, $img, 340, 15, 0, 0,  374, 100, $x, $y); 
			imagettftext($dest, 14, 0, 0, 25, $black, $f, 'M9 '.$roomstr.' Item Tag');
			imagettftext($dest, 24, 0, 0, 55, $black, $f2, calcBadgeType($badge).' '.$badge);
			imagettftext($dest, 24, 0, 0, 80, $black, $f2, 'Item Number: '.$itemnum);
			imagestring($dest, 5, 0, 90, 'Item Type: '.$itemtype, $black); 
			imagestring($dest, 5, 0, 107, 'Item Information:', $black);
			imagestring($dest, 5, 0, 125, $itemdesc, $black);
		}
		
		$today = date("m/j/y, g:i a");   
		imagestring($dest, 5, 500, 170, 'Printed: '.$today, $black);
		//$half = (int)ceil(count($words = str_word_count($desc, 1)) / 2); 
		//$d1 = implode(' ', array_slice($words, 0, $half)); 
		//$d2 = implode(' ', array_slice($words, $half));  

	}
	else if(isset($_SESSION['magitemprint'])){
		//Item Tag
			
		$itemnum = $_SESSION['itemnum'];
		$itemdesc = $_SESSION['itemdesc'];
		$itemtype = $_SESSION['itemtype'];
		$hash = $_SESSION['hash'];
		$roomid = $_SESSION['roomid_print'];
		
		unset($_SESSION['itemprint']);
		unset($_SESSION['itemnum']);
		unset($_SESSION['itemdesc']);
		unset($_SESSION['itemtype']);
		unset($_SESSION['hash']);
		unset($_SESSION['magitemprint']);

		$shit = $path.'genbarcode.php?h='.$hash;
		$img = @imagecreatefrompng($shit);	
		
		$LINE_WIDTH = 80;
		$itemdesc = $itemdesc." ";
		$output = array();
		$c = 0;
		
		$output[0] = '';
		$output[1] = '';
		$output[2] = '';
		$output[3] = '';
		
		
		if(@strlen($itemdesc)>80)
		{
			while(strlen($itemdesc)>0)
			{
				$loc = strrpos(substr($itemdesc,0,$LINE_WIDTH)," ");
				$output[$c] = substr($itemdesc,0,$loc);
				$itemdesc = substr($itemdesc,$loc+1);
				$c++;
			}
			
			$x = imagesx($img);
			$y = imagesy($img);
			$roomstr = $_SESSION['roomstr'];
			imagecopyresized($dest, $magfestlogo_vert, 730, 10, 0, 0, (64/2), (333/2),  64, 333); 		
			imagecopyresized($dest, $img, 340, 26, 0, 0,  374, 100, $x, $y); 
			imagettftext($dest, 14, 0, 0, 25, $black, $f, 'M9 '.$roomstr.' Inventory Tag');
			imagettftext($dest, 24, 0, 0, 55, $black, $f2, '-MAGFest Owned Item-');
			imagettftext($dest, 24, 0, 0, 80, $black, $f2, 'Item Number: '.$itemnum);
			imagestring($dest, 5, 0, 90, 'Item Type: '.$itemtype, $black); 
			imagestring($dest, 5, 0, 107, 'Item Information:', $black);
			imagestring($dest, 5, 0, 125, $output[0], $black);
			imagestring($dest, 5, 0, 140, $output[1], $black);		
			imagestring($dest, 5, 0, 155, $output[2], $black);		
			imagestring($dest, 5, 0, 170, $output[3], $black);	

			$today = date("m/j/y, g:i a");   
			imagestring($dest, 5, 500, 170, 'Printed: '.$today, $black);
		}
		else {
			$x = imagesx($img);
			$y = imagesy($img);
			$roomstr = $_SESSION['roomstr'];
			imagecopyresized($dest, $magfestlogo_vert, 730, 10, 0, 0, (64/2), (333/2),  64, 333); 		
			imagecopyresized($dest, $img, 340, 26, 0, 0,  374, 100, $x, $y); 
			imagettftext($dest, 14, 0, 0, 25, $black, $f, 'M9 '.$roomstr.' Inventory Tag');
			imagettftext($dest, 24, 0, 0, 55, $black, $f2, '-MAGFest Owned Item-');
			imagettftext($dest, 24, 0, 0, 80, $black, $f2, 'Item Number: '.$itemnum);
			imagestring($dest, 5, 0, 90, 'Item Type: '.$itemtype, $black); 
			imagestring($dest, 5, 0, 107, 'Item Information:', $black);
			imagestring($dest, 5, 0, 125, $itemdesc, $black);

			$today = date("m/j/y, g:i a");   
			imagestring($dest, 5, 500, 170, 'Printed: '.$today, $black);
		}
	}
	else if(isset($_SESSION['badgeprint'])){
	
		$pin = $_SESSION['pin']; 
		$name = $_SESSION['name'];
		$hash = $_SESSION['hash'];
		$badge = $_SESSION['scanned_badge'];

		unset($_SESSION['pin']);
		unset($_SESSION['name']);
		unset($_SESSION['hash']);
		unset($_SESSION['badgeprint']);
		
		$shit = $path.'genbarcode.php?h='.$hash;
		$img = @imagecreatefrompng($shit);		
		
		$x = imagesx($img); 
		$y = imagesy($img);
	
		imagecopyresized($dest, $magfestlogo, 20, 90, 0, 0, 333, 64, 333, 64);
		imagecopyresized($dest, $img, 375, 10, 0, 0,  374, 140, $x, $y); 
		imagettftext($dest, 14, 0, 0, 30, $black, $f, 'M9 Badge Tag');
		imagettftext($dest, 40, 0, 8, 80, $black, $f2, calcShortBadgeType($badge).' '.$badge);
		imagestring($dest, 5, 0, 170, 'Portal Username: '.$badge.' Password: '.$pin, $black);
		
		$today = date("m/j/y, g:i a");   
		imagestring($dest, 5, 500, 170, 'Printed: '.$today, $black);
	}
	else if(isset($_SESSION['adminprint']))
	{
		$name = $_SESSION['name'];
		$hash = $_SESSION['hash'];
		$badge = $_SESSION['scanned_badge'];
		unset($_SESSION['name']);
		unset($_SESSION['hash']);
		unset($_SESSION['scanned_badge']);
		unset($_SESSION['adminprint']);
		
		$shit = $path.'genbarcode.php?h='.$hash;
		$img = @imagecreatefrompng($shit);		
		
		$x = imagesx($img); 
		$y = imagesy($img);
	
		imagecopyresized($dest, $magfestlogo, 20, 90, 0, 0, 333, 64, 333, 64);
		imagecopyresized($dest, $img, 375, 10, 0, 0,  390, 125, $x, $y); 
		imagettftext($dest, 14, 0, 0, 30, $black, $f, 'M9 Barcode System Admin');
		imagettftext($dest, 40, 0, 8, 80, $black, $f2, 'Staff '.$badge);
		imagettftext($dest, 40, 0, 425, 160, $black, $f2, 'ADMIN ONLY');
		imagestring($dest, 5, 0, 165, 'M9 BarcodeSystem 2.0 Admin Tag for '.$name, $black);
		
		$today = date("m/j/y, g:i a");
		imagestring($dest, 5, 500, 165, 'Printed: '.$today, $black);
	}
	
	//Set printer to 115200 baud rate.
	//$message = "Y11,N,8,1\r\n";

	$testlabel = 0;
	if(isset($_GET['test']))
	{
		$testlabel = 1;
		$ip = $_GET['test'];
	}
	
	if($testlabel)
	{
		$infile = fopen('labeltest3a.esim','r');
		$message = fread($infile,filesize('labeltest3a.esim'));	
		
		$file = @fsockopen ($ip, 4001, $errno, $errstr, 1);
		$status = 0;
		
		if (!$file) 
			$status = -1;  
		else 
			fwrite($file,$message);

		switch($status)
		{
			case -1: $_SESSION['teststatus'] = '<br /><red>Error Printing - Printer Offline</red><br />'; break;
			default: $_SESSION['teststatus'] = "<br /><green>Print Command Sent Successfully</green><br />"; break;
		}
		sleep(2);
		header('Location: printers.php');
		exit();
		
	}
	else {
	
		$pid = $_SESSION['printerid'];
		$printerid = $pid.'printerlock';
		
		
		//Check if the printer is locked. If it is, wait for it to become unlocked.
		while(true)
		{
			if($_SESSION[$printerid])
				sleep(2);
			else
				break;
		}
		
		$_SESSION[$printerid] = true;
		

		//Write image to drive.
		$img = $pid.'temp.png';
		imagepng($dest,$img); 	
	
		//Generate label print command with image data in the correct format.
		$message = "^B\r\nD8\r\nN\r\nq768\r\nr0,9\r\nGW0,0,96,184,".onebit($dest)."\r\nP1\r\n";
		
		//Get printer IP from DB, and print to the IP associated with user.		
		echo "<big>Now Printing:</big><br /><img src=$img width=384px height=92px />";
		
		$ip = $_SESSION['printerip'];
		$file = @fsockopen ($ip, 4001, $errno, $errstr, 1);
		$status = 0;
		
		if (!$file) 
			$status = -1;  
		else 
		//	fwrite($file,$message);

		switch($status)
		{
			case -1: echo '<br /><red>Error Printing - Printer Offline</red><br />'; break;
			default: echo "<br /><green>Print Command Sent Successfully</green><br />"; break;
		}
		//Unlock printer after 2 seconds to allow printer to print
		sleep(2);
		$_SESSION[$printerid] = false;
	}

?>
