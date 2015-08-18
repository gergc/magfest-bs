<?php

// BarcodeSystem for MAGFest 2.0
// Designed for MAGFest 9 and BEYOND! by gergc and LANJesus
// File: invert.php
// Purpose: Flips the image for the Intermec PC4 printer. 


	function onebit(&$input) #returns binary string containing 1bit raw image data
	{
		$x = imagesx($input);
		$y = imagesy($input);
		if ($x % 8 != 0)
			die('width not multiple of 8');
		$out = array();
		for ($r = 0; $r < $y; $r++)
		{
			for ($i = 0; $i < $x; $i += 8)
			{
				array_push($out, chr(
					  (pixelbit($input, $i    , $r) ? 0x80 : 0)
					| (pixelbit($input, $i + 1, $r) ? 0x40 : 0)
					| (pixelbit($input, $i + 2, $r) ? 0x20 : 0)
					| (pixelbit($input, $i + 3, $r) ? 0x10 : 0)
					| (pixelbit($input, $i + 4, $r) ? 0x08 : 0)
					| (pixelbit($input, $i + 5, $r) ? 0x04 : 0)
					| (pixelbit($input, $i + 6, $r) ? 0x02 : 0)
					| (pixelbit($input, $i + 7, $r) ? 0x01 : 0)));
			}
		}
		return implode($out);
	}
	function pixelbit(&$image, $x, $y) #returns boolean - true is white
	{
		$pixel = imagecolorat($image, $x, $y);
		return (( ((($pixel & 0xff0000) >> 0x10) * 0.299)
			+ ((($pixel & 0x00ff00) >> 0x08) * 0.587)
			+ (( $pixel & 0x0000ff         ) * 0.114)) > 0x7f);
	}
?>
