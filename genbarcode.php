<?php

// BarcodeSystem for MAGFest 2.0
// Designed for MAGFest 9 and BEYOND! by gergc and LANJesus
// File: genbarcode.php
// Purpose: Generates the barcodes. 

session_start();

// Including all required classes
require('class/BCGFont.php');
require('class/BCGColor.php');
require('class/BCGDrawing.php'); 


// Including the barcode technology
include('class/BCGcode128.barcode.php'); 

// The arguments are R, G, B for color.
$color_black = new BCGColor(0, 0, 0);
$color_white = new BCGColor(255, 255, 255); 

$code = new BCGcode128();
$code->setScale(10); // Resolution TEST
$code->setThickness(70); // Thickness
$code->setForegroundColor($color_black); // Color of bars
$code->setBackgroundColor($color_white); // Color of spaces
$code->setFont(0);
$code->parse($_GET['h']); // Text


/* Here is the list of the arguments
1 - Filename (empty : display on screen)
2 - Background color */
$drawing = new BCGDrawing('', $color_white);
$drawing->setBarcode($code);
$drawing->draw();

// Header that says it is an image (remove it if you save the barcode to a file)
header('Content-Type: image/png');

// Draw (or save) the image into PNG format.
$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);

?>
