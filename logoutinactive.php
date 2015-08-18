<?php
// BarcodeSystem for MAGFest 2.0
// Designed for MAGFest 9 and BEYOND! by gergc and LANJesus
// File: logoutinactive.php
// Purpose: Destroy session data after inactive session is detected.
session_start();
session_destroy();
echo '<h1>Inactive Session Detected - Please Log In Again</h1>';
include('index.php');
//header ("Location: index.php"); 

?>