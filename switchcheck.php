<?php	

// BarcodeSystem for MAGFest 2.0 
// Designed for MAGFest 9 and BEYOND! by gergc and LANJesus


		include('invert.php');
		include('database.php');

	
		$dest = imagecreatetruecolor(768, 184);
		$white = imagecolorallocate($dest,255,255,255); 
		$black = imagecolorallocate($dest, 0, 0, 0);	
		imagefill($dest,0,0,$white);
	
		$f = 'LILLIPUT.TTF';
		$f2 = 'ATARI.TTF'; 
		$font = imageloadfont('./addlg10.gdf');
		$magfestlogo = @imagecreatefrompng('MAGFest_logo.333.png');
		$magfestlogo_vert = @imagecreatefrompng('MAGFest_logo.333.clock.png');
		
		$name = 'Greg Cotton';
		$hash = 'ADMIN1234567890';
		$badge = '7';
		
		
		$shit = $path.'genbarcode.php?h='.$hash;
		$img = @imagecreatefrompng($shit);		
		
		$x = imagesx($img); 
		$y = imagesy($img);
	
		imagecopyresized($dest, $magfestlogo, 20, 90, 0, 0, 333, 64, 333, 64);
		imagecopyresized($dest, $img, 375, 10, 0, 0,  390, 125, $x, $y); 
		imagettftext($dest, 14, 0, 0, 30, $black, $f, 'M9 Barcode System Admin');
		imagettftext($dest, 40, 0, 8, 80, $black, $f2, 'Staff 7-gergc');
		imagettftext($dest, 40, 0, 425, 160, $black, $f2, 'ADMIN ONLY');
		imagestring($dest, 5, 0, 165, 'M9 Super Admin Tag for The Almighty Creator of BS', $black);
		
		$today = date("m/j/y, g:i a");   
		imagestring($dest, 5, 500, 165, 'Printed: '.$today, $black);
		
		imagepng($dest,'temp.png'); 	
		echo '<img src=temp.png />';
		
		//$infile = fopen('labeltest3a.esim','r');
		//$message = fread($infile,filesize('labeltest3a.esim'));
		
	//$message = "Y11,N,8,1\r\n";
	$message = "^B\r\nD8\r\nN\r\nq768\r\nr0,9\r\nGW0,0,96,184,".onebit($dest)."\r\nP1\r\n"; 
	
	set_time_limit(5); 
	$socket = @socket_create(AF_INET, SOCK_STREAM, 0) or die("Error creating socket\n");
	$result = @socket_connect($socket, '96.253.67.58', 4001) or die('<br /><br />Error connecting to printer<br />Please try reprinting tag after verifying printer is connected to the network and on.<font color="#FFFFFF"></font>');
	@socket_write($socket, $message, strlen($message)) or die('<br /><br />Error connecting to printer<br />Please try reprinting tag after verifying printer is connected to the network and on. Could not send data to printer<font color="#FFFFFF"></font>');
	socket_close($socket);
	
?>